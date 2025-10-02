<?php

namespace WP_GPT_RAG_Chat;

/**
 * Sitemap Parser and Indexer
 * Handles XML sitemap parsing and fallback page suggestions
 */
class Sitemap {
    
    private $openai;
    private $pinecone;
    private $settings;
    
    public function __construct() {
        $this->settings = Settings::get_settings();
        $this->openai = new OpenAI();
        $this->pinecone = new Pinecone();
    }
    
    /**
     * Parse XML sitemap and extract URLs
     */
    public function parse_sitemap($sitemap_url) {
        $sitemap_urls = [];
        
        // Handle both local file paths and URLs
        if (filter_var($sitemap_url, FILTER_VALIDATE_URL)) {
            $xml_content = @file_get_contents($sitemap_url);
        } else {
            // Try as local file path
            $xml_content = @file_get_contents(ABSPATH . $sitemap_url);
        }
        
        if ($xml_content === false) {
            throw new \Exception('Failed to load sitemap from: ' . $sitemap_url);
        }
        
        // Suppress XML parsing warnings
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml_content);
        libxml_clear_errors();
        
        if ($xml === false) {
            throw new \Exception('Failed to parse sitemap XML');
        }
        
        // Handle sitemap index (contains multiple sitemaps)
        if (isset($xml->sitemap)) {
            foreach ($xml->sitemap as $sitemap) {
                $sub_sitemap_url = (string) $sitemap->loc;
                $sitemap_urls = array_merge($sitemap_urls, $this->parse_sitemap($sub_sitemap_url));
            }
        }
        
        // Handle regular sitemap (contains URLs)
        if (isset($xml->url)) {
            foreach ($xml->url as $url) {
                $sitemap_urls[] = [
                    'url' => (string) $url->loc,
                    'lastmod' => isset($url->lastmod) ? (string) $url->lastmod : null,
                    'priority' => isset($url->priority) ? (float) $url->priority : 0.5,
                    'changefreq' => isset($url->changefreq) ? (string) $url->changefreq : null
                ];
            }
        }
        
        return $sitemap_urls;
    }
    
    /**
     * Fetch page metadata (title, description, content snippet)
     */
    public function fetch_page_metadata($url) {
        // For internal WordPress URLs, use post ID
        $post_id = url_to_postid($url);
        
        if ($post_id) {
            $post = get_post($post_id);
            if ($post) {
                return [
                    'title' => get_the_title($post_id),
                    'description' => get_the_excerpt($post_id) ?: wp_trim_words($post->post_content, 30),
                    'content_snippet' => wp_trim_words($post->post_content, 100),
                    'post_id' => $post_id
                ];
            }
        }
        
        // For external URLs or if post not found, fetch via HTTP
        $response = wp_remote_get($url, ['timeout' => 10]);
        
        if (is_wp_error($response)) {
            return [
                'title' => parse_url($url, PHP_URL_PATH),
                'description' => '',
                'content_snippet' => ''
            ];
        }
        
        $html = wp_remote_retrieve_body($response);
        
        // Extract title
        preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $title_matches);
        $title = isset($title_matches[1]) ? html_entity_decode(strip_tags($title_matches[1])) : '';
        
        // Extract meta description
        preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $desc_matches);
        $description = isset($desc_matches[1]) ? html_entity_decode(strip_tags($desc_matches[1])) : '';
        
        // Extract content snippet from body
        preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $body_matches);
        $body_text = isset($body_matches[1]) ? strip_tags($body_matches[1]) : '';
        $content_snippet = wp_trim_words($body_text, 100);
        
        return [
            'title' => $title ?: basename(parse_url($url, PHP_URL_PATH)),
            'description' => $description,
            'content_snippet' => $content_snippet
        ];
    }
    
    /**
     * Index sitemap URLs into database
     */
    public function index_sitemap($sitemap_url) {
        global $wpdb;
        $table = $wpdb->prefix . 'wp_gpt_rag_sitemap_urls';
        
        try {
            // Parse sitemap
            $urls = $this->parse_sitemap($sitemap_url);
            
            $indexed_count = 0;
            $failed_count = 0;
            
            foreach ($urls as $url_data) {
                try {
                    // Check if already indexed
                    $existing = $wpdb->get_var($wpdb->prepare(
                        "SELECT id FROM {$table} WHERE url = %s",
                        $url_data['url']
                    ));
                    
                    // Fetch page metadata
                    $metadata = $this->fetch_page_metadata($url_data['url']);
                    
                    // Create searchable text (title + description)
                    $searchable_text = $metadata['title'] . '. ' . $metadata['description'];
                    
                    // Create embedding for semantic search
                    $embedding = null;
                    if (!empty($searchable_text)) {
                        try {
                            $embeddings = $this->openai->create_embeddings([$searchable_text]);
                            $embedding = !empty($embeddings[0]) ? wp_json_encode($embeddings[0]) : null;
                        } catch (\Exception $e) {
                            error_log('Failed to create embedding for: ' . $url_data['url'] . ' - ' . $e->getMessage());
                        }
                    }
                    
                    $data = [
                        'url' => $url_data['url'],
                        'title' => $metadata['title'],
                        'description' => $metadata['description'],
                        'content_snippet' => $metadata['content_snippet'],
                        'post_id' => $metadata['post_id'] ?? null,
                        'priority' => $url_data['priority'],
                        'changefreq' => $url_data['changefreq'],
                        'lastmod' => $url_data['lastmod'],
                        'embedding' => $embedding,
                        'indexed_at' => current_time('mysql')
                    ];
                    
                    if ($existing) {
                        // Update existing
                        $wpdb->update($table, $data, ['id' => $existing]);
                    } else {
                        // Insert new
                        $wpdb->insert($table, $data);
                    }
                    
                    $indexed_count++;
                    
                } catch (\Exception $e) {
                    error_log('Failed to index URL: ' . $url_data['url'] . ' - ' . $e->getMessage());
                    $failed_count++;
                }
            }
            
            return [
                'success' => true,
                'total' => count($urls),
                'indexed' => $indexed_count,
                'failed' => $failed_count
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Index sitemap URLs in batches
     */
    public function index_sitemap_batch($sitemap_url, $offset = 0, $limit = 5) {
        global $wpdb;
        $table = $wpdb->prefix . 'wp_gpt_rag_sitemap_urls';
        
        try {
            // Get or parse sitemap URLs
            $cached_urls = get_transient('wp_gpt_rag_sitemap_urls_cache');
            
            if ($cached_urls === false) {
                // Parse sitemap and cache for 1 hour
                $urls = $this->parse_sitemap($sitemap_url);
                set_transient('wp_gpt_rag_sitemap_urls_cache', $urls, HOUR_IN_SECONDS);
            } else {
                $urls = $cached_urls;
            }
            
            $total = count($urls);
            $batch = array_slice($urls, $offset, $limit);
            
            $indexed_count = 0;
            $failed_count = 0;
            
            foreach ($batch as $url_data) {
                try {
                    // Check if already indexed
                    $existing = $wpdb->get_var($wpdb->prepare(
                        "SELECT id FROM {$table} WHERE url = %s",
                        $url_data['url']
                    ));
                    
                    // Fetch page metadata
                    $metadata = $this->fetch_page_metadata($url_data['url']);
                    
                    // Create searchable text (title + description)
                    $searchable_text = $metadata['title'] . '. ' . $metadata['description'];
                    
                    // Create embedding for semantic search
                    $embedding = null;
                    if (!empty($searchable_text)) {
                        try {
                            $embeddings = $this->openai->create_embeddings([$searchable_text]);
                            $embedding = !empty($embeddings[0]) ? wp_json_encode($embeddings[0]) : null;
                        } catch (\Exception $e) {
                            error_log('Failed to create embedding for: ' . $url_data['url'] . ' - ' . $e->getMessage());
                        }
                    }
                    
                    $data = [
                        'url' => $url_data['url'],
                        'title' => $metadata['title'],
                        'description' => $metadata['description'],
                        'content_snippet' => $metadata['content_snippet'],
                        'post_id' => $metadata['post_id'] ?? null,
                        'priority' => $url_data['priority'],
                        'changefreq' => $url_data['changefreq'],
                        'lastmod' => $url_data['lastmod'],
                        'embedding' => $embedding,
                        'indexed_at' => current_time('mysql')
                    ];
                    
                    if ($existing) {
                        // Update existing
                        $wpdb->update($table, $data, ['id' => $existing]);
                    } else {
                        // Insert new
                        $wpdb->insert($table, $data);
                    }
                    
                    $indexed_count++;
                    
                } catch (\Exception $e) {
                    error_log('Failed to index URL: ' . $url_data['url'] . ' - ' . $e->getMessage());
                    $failed_count++;
                }
            }
            
            return [
                'success' => true,
                'total' => $total,
                'processed' => count($batch),
                'indexed' => $indexed_count,
                'failed' => $failed_count,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Search sitemap URLs for relevant pages (fallback when RAG returns no results)
     */
    public function search_relevant_pages($query, $limit = 5) {
        global $wpdb;
        $table = $wpdb->prefix . 'wp_gpt_rag_sitemap_urls';
        
        // Create embedding for the query
        try {
            $query_embeddings = $this->openai->create_embeddings([$query]);
            $query_embedding = $query_embeddings[0];
        } catch (\Exception $e) {
            error_log('Failed to create query embedding: ' . $e->getMessage());
            // Fallback to keyword search
            return $this->keyword_search($query, $limit);
        }
        
        // Get all URLs with embeddings
        $urls = $wpdb->get_results("SELECT * FROM {$table} WHERE embedding IS NOT NULL");
        
        if (empty($urls)) {
            return [];
        }
        
        // Calculate similarity scores
        $scored_urls = [];
        foreach ($urls as $url) {
            $url_embedding = json_decode($url->embedding, true);
            if (!$url_embedding) continue;
            
            // Calculate cosine similarity
            $similarity = $this->cosine_similarity($query_embedding, $url_embedding);
            
            $scored_urls[] = [
                'url' => $url->url,
                'title' => $url->title,
                'description' => $url->description,
                'similarity' => $similarity,
                'priority' => $url->priority
            ];
        }
        
        // Sort by similarity score
        usort($scored_urls, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        // Return top N results
        return array_slice($scored_urls, 0, $limit);
    }
    
    /**
     * Fallback keyword search (when embeddings fail)
     */
    private function keyword_search($query, $limit = 5) {
        global $wpdb;
        $table = $wpdb->prefix . 'wp_gpt_rag_sitemap_urls';
        
        $search_term = '%' . $wpdb->esc_like($query) . '%';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT url, title, description, priority 
             FROM {$table} 
             WHERE title LIKE %s OR description LIKE %s 
             ORDER BY priority DESC, title ASC 
             LIMIT %d",
            $search_term,
            $search_term,
            $limit
        ));
        
        return array_map(function($row) {
            return [
                'url' => $row->url,
                'title' => $row->title,
                'description' => $row->description,
                'similarity' => 0.5, // Default score for keyword match
                'priority' => $row->priority
            ];
        }, $results);
    }
    
    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosine_similarity($vec1, $vec2) {
        $dot_product = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;
        
        for ($i = 0; $i < count($vec1); $i++) {
            $dot_product += $vec1[$i] * $vec2[$i];
            $magnitude1 += $vec1[$i] * $vec1[$i];
            $magnitude2 += $vec2[$i] * $vec2[$i];
        }
        
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);
        
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }
        
        return $dot_product / ($magnitude1 * $magnitude2);
    }
    
    /**
     * Get total indexed URLs count
     */
    public function get_indexed_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'wp_gpt_rag_sitemap_urls';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }
    
    /**
     * Clear all indexed sitemap URLs
     */
    public function clear_sitemap_index() {
        global $wpdb;
        $table = $wpdb->prefix . 'wp_gpt_rag_sitemap_urls';
        
        return $wpdb->query("TRUNCATE TABLE {$table}");
    }
}


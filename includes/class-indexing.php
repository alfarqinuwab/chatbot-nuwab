<?php

namespace WP_GPT_RAG_Chat;

/**
 * Indexing system class
 */
class Indexing {
    
    /**
     * Chunking instance
     */
    private $chunking;
    
    /**
     * OpenAI instance
     */
    private $openai;
    
    /**
     * Pinecone instance
     */
    private $pinecone;
    
    /**
     * Settings instance
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->chunking = new Chunking();
        $this->openai = new OpenAI();
        $this->pinecone = new Pinecone();
        $this->settings = Settings::get_settings();
    }
    
    /**
     * Index a single post
     */
    public function index_post($post_id, $force = false) {
        $post = get_post($post_id);
        if (!$post) {
            throw new \Exception(__('Post not found.', 'wp-gpt-rag-chat'));
        }
        
        // Check if post should be included
        $include = get_post_meta($post_id, '_wp_gpt_rag_chat_include', true);
        if ($include === '') {
            // Default to include for new posts
            update_post_meta($post_id, '_wp_gpt_rag_chat_include', true);
            $include = true;
        }
        
        if (!$include) {
            // Remove existing vectors if any
            $this->delete_post_vectors($post_id);
            return ['message' => __('Post excluded from indexing.', 'wp-gpt-rag-chat')];
        }
        
        // Check if post is published
        if (!in_array($post->post_status, ['publish', 'private'])) {
            return ['message' => __('Post is not published.', 'wp-gpt-rag-chat')];
        }
        
        // Get existing vectors
        $existing_vectors = $this->get_post_vectors($post_id);
        
        // Quick check: if post hasn't been modified since last indexing and force is false, skip
        if (!$force && !empty($existing_vectors)) {
            $last_indexed = get_post_meta($post_id, '_wp_gpt_rag_chat_last_indexed', true);
            if ($last_indexed && strtotime($last_indexed) >= strtotime($post->post_modified)) {
                return [
                    'message' => __('Post content unchanged since last indexing.', 'wp-gpt-rag-chat'),
                    'added' => 0,
                    'updated' => 0,
                    'removed' => 0,
                    'skipped' => count($existing_vectors)
                ];
            }
        }
        
        // Chunk the content
        $chunks = $this->chunking->chunk_post($post_id);
        
        if (empty($chunks)) {
            return ['message' => __('No content to index.', 'wp-gpt-rag-chat')];
        }
        
        // Check for changes (idempotency)
        $vectors_to_update = [];
        $vectors_to_add = [];
        
        foreach ($chunks as $chunk) {
            $vector_id = $this->pinecone->generate_vector_id($post_id, $chunk['chunk_index']);
            $content_hash = $chunk['content_hash'];
            
            // Check if vector exists and if content has changed
            $existing_vector = $this->find_existing_vector($existing_vectors, $vector_id);
            
            if ($existing_vector && !$force) {
                // Check if content hash has changed
                if ($existing_vector['content_hash'] === $content_hash) {
                    // Content unchanged, skip
                    continue;
                }
            }
            
            // Vector needs to be updated or added
            $vectors_to_update[] = [
                'id' => $vector_id,
                'chunk' => $chunk,
                'action' => $existing_vector ? 'update' : 'add'
            ];
        }
        
        // Remove vectors for chunks that no longer exist
        $vectors_to_remove = [];
        $current_chunk_indices = array_column($chunks, 'chunk_index');
        
        foreach ($existing_vectors as $existing_vector) {
            $parsed_id = $this->pinecone->parse_vector_id($existing_vector['vector_id']);
            if ($parsed_id && !in_array($parsed_id['chunk_index'], $current_chunk_indices)) {
                $vectors_to_remove[] = $existing_vector['vector_id'];
            }
        }
        
        // Process vectors in batches
        $results = [
            'added' => 0,
            'updated' => 0,
            'removed' => 0,
            'skipped' => 0
        ];
        
        if (!empty($vectors_to_remove)) {
            $this->pinecone->delete_vectors($vectors_to_remove);
            $results['removed'] = count($vectors_to_remove);
        }
        
        if (!empty($vectors_to_update)) {
            $batch_size = 10; // Process in small batches to avoid API limits
            $batches = array_chunk($vectors_to_update, $batch_size);
            
            foreach ($batches as $batch) {
                $batch_results = $this->process_vector_batch($batch);
                $results['added'] += $batch_results['added'];
                $results['updated'] += $batch_results['updated'];
            }
        }
        
        $results['skipped'] = count($chunks) - count($vectors_to_update);
        
        // Mark post as indexed if any vectors were processed
        if ($results['added'] > 0 || $results['updated'] > 0) {
            update_post_meta($post_id, '_wp_gpt_rag_chat_indexed', '1');
            update_post_meta($post_id, '_wp_gpt_rag_chat_last_indexed', current_time('mysql'));
        }
        
        return $results;
    }
    
    /**
     * Process a batch of vectors
     */
    private function process_vector_batch($batch) {
        $results = ['added' => 0, 'updated' => 0];
        
        // Extract content for embedding
        $contents = array_column($batch, 'chunk');
        $contents = array_column($contents, 'content');
        
        // Create embeddings
        $embeddings = $this->openai->create_embeddings($contents);
        
        // Prepare vectors for Pinecone
        $vectors = [];
        foreach ($batch as $index => $vector_data) {
            $chunk = $vector_data['chunk'];
            $embedding = $embeddings[$index];
            
            $vectors[] = [
                'id' => $vector_data['id'],
                'values' => $embedding,
                'metadata' => $this->pinecone->create_vector_metadata($chunk)
            ];
        }
        
        // Upsert to Pinecone
        $this->pinecone->upsert_vectors($vectors);
        
        // Update local database
        foreach ($batch as $vector_data) {
            $this->update_local_vector_record($vector_data['id'], $vector_data['chunk']);
            
            if ($vector_data['action'] === 'add') {
                $results['added']++;
            } else {
                $results['updated']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Find existing vector by ID
     */
    private function find_existing_vector($existing_vectors, $vector_id) {
        foreach ($existing_vectors as $vector) {
            if ($vector['vector_id'] === $vector_id) {
                return $vector;
            }
        }
        return null;
    }
    
    /**
     * Update local vector record
     */
    private function update_local_vector_record($vector_id, $chunk) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $parsed_id = $this->pinecone->parse_vector_id($vector_id);
        if (!$parsed_id) {
            return;
        }
        
        $wpdb->replace(
            $vectors_table,
            [
                'post_id' => $parsed_id['post_id'],
                'chunk_index' => $parsed_id['chunk_index'],
                'content_hash' => $chunk['content_hash'],
                'vector_id' => $vector_id,
                'updated_at' => current_time('mysql')
            ],
            [
                '%d',
                '%d',
                '%s',
                '%s',
                '%s'
            ]
        );
    }
    
    /**
     * Get vectors for a post from local database
     */
    private function get_post_vectors($post_id) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$vectors_table} WHERE post_id = %d ORDER BY chunk_index",
            $post_id
        ), ARRAY_A);
    }
    
    /**
     * Delete vectors for a post
     */
    public function delete_post_vectors($post_id) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        // Get vector IDs to delete from Pinecone
        $vector_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT vector_id FROM {$vectors_table} WHERE post_id = %d",
            $post_id
        ));
        
        // Delete from Pinecone
        if (!empty($vector_ids)) {
            $this->pinecone->delete_vectors($vector_ids);
        }
        
        // Delete from local database
        $wpdb->delete(
            $vectors_table,
            ['post_id' => $post_id],
            ['%d']
        );
        
        return count($vector_ids);
    }
    
    /**
     * Reindex a post (force update)
     */
    public function reindex_post($post_id) {
        return $this->index_post($post_id, true);
    }
    
    /**
     * Bulk action on posts
     */
    public function bulk_action($action, $post_ids) {
        $results = [
            'processed' => 0,
            'errors' => []
        ];
        
        foreach ($post_ids as $post_id) {
            try {
                switch ($action) {
                    case 'include':
                        update_post_meta($post_id, '_wp_gpt_rag_chat_include', true);
                        $results['processed']++;
                        break;
                        
                    case 'exclude':
                        update_post_meta($post_id, '_wp_gpt_rag_chat_include', false);
                        $this->delete_post_vectors($post_id);
                        $results['processed']++;
                        break;
                        
                    case 'reindex':
                        $this->reindex_post($post_id);
                        $results['processed']++;
                        break;
                }
            } catch (\Exception $e) {
                $results['errors'][] = sprintf(
                    __('Post %d: %s', 'wp-gpt-rag-chat'),
                    $post_id,
                    $e->getMessage()
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Index all content
     */
    public function index_all_content($limit = 50, $offset = 0, $post_type = '') {
        $query_args = [
            'numberposts' => $limit,
            'offset' => $offset,
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ];
        
        // Add post type filter if specified
        if ($post_type && $post_type !== 'all') {
            $query_args['post_type'] = $post_type;
        } else {
            $query_args['post_type'] = get_post_types(['public' => true]);
        }
        
        $posts = get_posts($query_args);
        
        $results = [
            'processed' => 0,
            'total' => count($posts),
            'errors' => [],
            'indexed_post_ids' => []
        ];
        
        foreach ($posts as $post) {
            try {
                $index_result = $this->index_post($post->ID);
                $results['processed']++;
                
                // Track successfully indexed posts for real-time updates
                if (!empty($index_result['added']) || !empty($index_result['updated'])) {
                    $results['indexed_post_ids'][] = $post->ID;
                }
            } catch (\Exception $e) {
                $results['errors'][] = sprintf(
                    __('Post %d (%s): %s', 'wp-gpt-rag-chat'),
                    $post->ID,
                    $post->post_title,
                    $e->getMessage()
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Index a single post
     */
    public function index_single_post($post_type = '') {
        $query_args = [
            'numberposts' => 1,
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ];
        
        // Add post type filter if specified
        if ($post_type && $post_type !== 'all') {
            $query_args['post_type'] = $post_type;
        } else {
            $query_args['post_type'] = get_post_types(['public' => true]);
        }
        
        $posts = get_posts($query_args);
        
        $results = [
            'processed' => 0,
            'total' => count($posts),
            'errors' => [],
            'indexed_post_ids' => []
        ];
        
        if (!empty($posts)) {
            $post = $posts[0];
            try {
                $index_result = $this->index_post($post->ID);
                $results['processed'] = 1;
                
                // Track successfully indexed post for real-time updates
                if (!empty($index_result['added']) || !empty($index_result['updated'])) {
                    $results['indexed_post_ids'][] = $post->ID;
                }
            } catch (\Exception $e) {
                $results['errors'][] = sprintf(__('Failed to index post: %s - %s', 'wp-gpt-rag-chat'), $post->post_title, $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Reindex changed content
     */
    public function reindex_changed_content($limit = 50, $offset = 0, $post_type = '') {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        // Build post type filter
        if ($post_type && $post_type !== 'all') {
            $post_type_filter = "'" . esc_sql($post_type) . "'";
        } else {
            $post_type_filter = "'" . implode("','", get_post_types(['public' => true])) . "'";
        }
        
        // Get posts that have been modified since last indexing
        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT p.ID, p.post_title, p.post_modified
            FROM {$wpdb->posts} p
            LEFT JOIN {$vectors_table} v ON p.ID = v.post_id
            WHERE p.post_status IN ('publish', 'private')
            AND p.post_type IN ({$post_type_filter})
            AND (v.updated_at IS NULL OR p.post_modified > v.updated_at)
            AND p.ID IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_wp_gpt_rag_chat_include' 
                AND meta_value = '1'
            )
            ORDER BY p.post_modified DESC
            LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        $results = [
            'processed' => 0,
            'total' => count($posts),
            'errors' => []
        ];
        
        foreach ($posts as $post) {
            try {
                $this->reindex_post($post->ID);
                $results['processed']++;
            } catch (\Exception $e) {
                $results['errors'][] = sprintf(
                    __('Post %d (%s): %s', 'wp-gpt-rag-chat'),
                    $post->ID,
                    $post->post_title,
                    $e->getMessage()
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Clear all vectors
     */
    public function clear_all_vectors() {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        // Get all vector IDs
        $vector_ids = $wpdb->get_col("SELECT vector_id FROM {$vectors_table}");
        
        // Delete from Pinecone
        if (!empty($vector_ids)) {
            $this->pinecone->delete_vectors($vector_ids);
        }
        
        // Clear local database
        $wpdb->query("TRUNCATE TABLE {$vectors_table}");
        
        return count($vector_ids);
    }
    
    /**
     * Get indexing statistics
     */
    public function get_indexing_stats() {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_vectors,
                COUNT(DISTINCT post_id) as total_posts,
                COUNT(CASE WHEN updated_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as recent_activity
            FROM {$vectors_table}
        ");
        
        return [
            'total_vectors' => intval($stats->total_vectors ?? 0),
            'total_posts' => intval($stats->total_posts ?? 0),
            'recent_activity' => intval($stats->recent_activity ?? 0)
        ];
    }
    
    /**
     * Generate XML Sitemap for all indexable content
     */
    public function generate_xml_sitemap($post_types = ['post', 'page']) {
        if ($post_types === 'all' || empty($post_types)) {
            $post_types = get_post_types(['public' => true]);
            unset($post_types['attachment']); // Exclude attachments
        }
        
        $query_args = [
            'numberposts' => -1,
            'post_type' => $post_types,
            'post_status' => ['publish', 'private'],
            'orderby' => 'modified',
            'order' => 'DESC'
        ];
        
        $posts = get_posts($query_args);
        
        // Generate XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        
        foreach ($posts as $post) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars(get_permalink($post->ID)));
            $url->addChild('lastmod', get_the_modified_date('c', $post->ID));
            
            // Determine priority based on post type
            $priority = $post->post_type === 'page' ? '0.8' : '0.6';
            if ($post->ID === get_option('page_on_front')) {
                $priority = '1.0'; // Homepage gets highest priority
            }
            $url->addChild('priority', $priority);
            
            // Change frequency based on post type
            $changefreq = $post->post_type === 'post' ? 'weekly' : 'monthly';
            $url->addChild('changefreq', $changefreq);
        }
        
        return [
            'xml' => $xml->asXML(),
            'count' => count($posts),
            'post_types' => array_values($post_types)
        ];
    }
    
    /**
     * Save sitemap to file
     */
    public function save_sitemap_to_file($xml_content) {
        $upload_dir = wp_upload_dir();
        $sitemap_dir = $upload_dir['basedir'] . '/wp-gpt-rag-chat-sitemaps';
        
        // Create directory if it doesn't exist
        if (!file_exists($sitemap_dir)) {
            wp_mkdir_p($sitemap_dir);
        }
        
        $filename = 'sitemap-' . date('Y-m-d-His') . '.xml';
        $filepath = $sitemap_dir . '/' . $filename;
        
        $result = file_put_contents($filepath, $xml_content);
        
        if ($result === false) {
            throw new \Exception(__('Failed to write sitemap file.', 'wp-gpt-rag-chat'));
        }
        
        return [
            'filepath' => $filepath,
            'url' => $upload_dir['baseurl'] . '/wp-gpt-rag-chat-sitemaps/' . $filename,
            'filename' => $filename
        ];
    }
    
    /**
     * Get all content URLs from sitemap structure
     */
    public function get_all_indexable_content() {
        $post_types = get_post_types(['public' => true]);
        unset($post_types['attachment']);
        
        $query_args = [
            'numberposts' => -1,
            'post_type' => $post_types,
            'post_status' => ['publish', 'private'],
            'fields' => 'ids'
        ];
        
        $post_ids = get_posts($query_args);
        
        $content_list = [];
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            $is_indexed = $this->is_post_indexed($post_id);
            
            $content_list[] = [
                'id' => $post_id,
                'title' => get_the_title($post_id),
                'url' => get_permalink($post_id),
                'type' => $post->post_type,
                'modified' => get_the_modified_date('Y-m-d H:i:s', $post_id),
                'indexed' => $is_indexed,
                'status' => $post->post_status
            ];
        }
        
        return $content_list;
    }
    
    /**
     * Check if a post is indexed
     */
    private function is_post_indexed($post_id) {
        global $wpdb;
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$vectors_table} WHERE post_id = %d",
            $post_id
        ));
        
        return $count > 0;
    }
}

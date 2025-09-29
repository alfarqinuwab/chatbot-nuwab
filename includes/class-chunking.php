<?php

namespace WP_GPT_RAG_Chat;

/**
 * Content chunking class
 */
class Chunking {
    
    /**
     * Settings instance
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = Settings::get_settings();
    }
    
    /**
     * Chunk post content
     */
    public function chunk_post($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            throw new \Exception(__('Post not found.', 'wp-gpt-rag-chat'));
        }
        
        // Get post content
        $content = $this->extract_post_content($post);
        
        if (empty($content)) {
            return [];
        }
        
        // Chunk the content
        $chunks = $this->chunk_text($content);
        
        // Add metadata to each chunk
        $chunks_with_metadata = [];
        foreach ($chunks as $index => $chunk) {
            $chunks_with_metadata[] = [
                'content' => $chunk,
                'post_id' => $post_id,
                'chunk_index' => $index,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'post_url' => get_permalink($post_id),
                'post_date' => $post->post_date,
                'content_hash' => $this->calculate_content_hash($chunk),
            ];
        }
        
        return $chunks_with_metadata;
    }
    
    /**
     * Extract content from post
     */
    private function extract_post_content($post) {
        $content = '';
        
        // Add title
        if (!empty($post->post_title)) {
            $content .= $post->post_title . "\n\n";
        }
        
        // Add main content
        $main_content = apply_filters('the_content', $post->post_content);
        $main_content = wp_strip_all_tags($main_content);
        $main_content = trim($main_content);
        
        if (!empty($main_content)) {
            $content .= $main_content . "\n\n";
        }
        
        // Add excerpt if available
        if (!empty($post->post_excerpt)) {
            $content .= $post->post_excerpt . "\n\n";
        }
        
        // Add custom fields (if configured)
        $custom_fields = $this->get_custom_fields($post->ID);
        if (!empty($custom_fields)) {
            $content .= $custom_fields . "\n\n";
        }
        
        // Add meta description if available
        $meta_description = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
        if (empty($meta_description)) {
            $meta_description = get_post_meta($post->ID, '_aioseo_description', true);
        }
        
        if (!empty($meta_description)) {
            $content .= $meta_description . "\n\n";
        }
        
        // Clean up content
        $content = $this->clean_content($content);
        
        return $content;
    }
    
    /**
     * Get custom fields content
     */
    private function get_custom_fields($post_id) {
        $custom_fields = get_post_custom($post_id);
        $content = '';
        
        // Fields to include (configurable)
        $include_fields = apply_filters('wp_gpt_rag_chat_include_fields', [
            'description',
            'summary',
            'content',
            'text',
            'body',
        ]);
        
        foreach ($custom_fields as $key => $values) {
            // Skip private fields
            if (strpos($key, '_') === 0) {
                continue;
            }
            
            // Skip if not in include list
            if (!in_array(strtolower($key), $include_fields)) {
                continue;
            }
            
            foreach ($values as $value) {
                $value = wp_strip_all_tags($value);
                $value = trim($value);
                
                if (!empty($value) && strlen($value) > 10) {
                    $content .= $key . ': ' . $value . "\n\n";
                }
            }
        }
        
        return $content;
    }
    
    /**
     * Clean content
     */
    private function clean_content($content) {
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove empty lines
        $content = preg_replace('/\n\s*\n/', "\n", $content);
        
        // Trim
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Chunk text into smaller pieces
     */
    private function chunk_text($text) {
        $chunk_size = $this->settings['chunk_size'];
        $chunk_overlap = $this->settings['chunk_overlap'];
        
        if (strlen($text) <= $chunk_size) {
            return [$text];
        }
        
        $chunks = [];
        $start = 0;
        
        while ($start < strlen($text)) {
            $end = $start + $chunk_size;
            
            // If this is not the last chunk, try to break at a sentence or word boundary
            if ($end < strlen($text)) {
                $chunk = substr($text, $start, $chunk_size);
                
                // Try to break at sentence boundary
                $sentence_end = strrpos($chunk, '. ');
                if ($sentence_end !== false && $sentence_end > $chunk_size * 0.7) {
                    $end = $start + $sentence_end + 1;
                } else {
                    // Try to break at word boundary
                    $word_end = strrpos($chunk, ' ');
                    if ($word_end !== false && $word_end > $chunk_size * 0.8) {
                        $end = $start + $word_end;
                    }
                }
            }
            
            $chunk = substr($text, $start, $end - $start);
            $chunk = trim($chunk);
            
            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }
            
            // Move start position with overlap
            $start = $end - $chunk_overlap;
            
            // Ensure we don't go backwards
            if ($start <= 0) {
                $start = $end;
            }
        }
        
        return $chunks;
    }
    
    /**
     * Calculate content hash
     */
    public function calculate_content_hash($content) {
        return hash('sha256', $content);
    }
    
    /**
     * Get chunk statistics
     */
    public function get_chunk_stats($post_id) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_chunks,
                MIN(chunk_index) as min_chunk,
                MAX(chunk_index) as max_chunk,
                AVG(LENGTH(content)) as avg_chunk_length
            FROM {$vectors_table} 
            WHERE post_id = %d",
            $post_id
        ));
        
        return [
            'total_chunks' => intval($stats->total_chunks ?? 0),
            'min_chunk' => intval($stats->min_chunk ?? 0),
            'max_chunk' => intval($stats->max_chunk ?? 0),
            'avg_chunk_length' => intval($stats->avg_chunk_length ?? 0),
        ];
    }
    
    /**
     * Validate chunk settings
     */
    public function validate_settings() {
        $errors = [];
        
        if ($this->settings['chunk_size'] < 100) {
            $errors[] = __('Chunk size must be at least 100 characters.', 'wp-gpt-rag-chat');
        }
        
        if ($this->settings['chunk_size'] > 3000) {
            $errors[] = __('Chunk size should not exceed 3000 characters.', 'wp-gpt-rag-chat');
        }
        
        if ($this->settings['chunk_overlap'] < 0) {
            $errors[] = __('Chunk overlap cannot be negative.', 'wp-gpt-rag-chat');
        }
        
        if ($this->settings['chunk_overlap'] >= $this->settings['chunk_size']) {
            $errors[] = __('Chunk overlap must be less than chunk size.', 'wp-gpt-rag-chat');
        }
        
        return $errors;
    }
    
    /**
     * Test chunking with sample content
     */
    public function test_chunking($sample_content = null) {
        if ($sample_content === null) {
            $sample_content = "This is a sample content for testing the chunking functionality. " . 
                            "It should be split into multiple chunks based on the configured chunk size and overlap. " .
                            "The chunking algorithm tries to break at sentence or word boundaries when possible. " .
                            "This ensures that the chunks are meaningful and don't cut off in the middle of a sentence. " .
                            "The overlap between chunks helps maintain context and continuity. " .
                            "This is particularly important for retrieval-augmented generation where we want to provide " .
                            "relevant context to the language model. The chunking process should handle various types of content " .
                            "including long paragraphs, lists, and structured text. It should also preserve the meaning " .
                            "and context of the original content while creating manageable pieces for embedding.";
        }
        
        $chunks = $this->chunk_text($sample_content);
        
        return [
            'original_length' => strlen($sample_content),
            'chunk_count' => count($chunks),
            'chunks' => $chunks,
            'settings' => [
                'chunk_size' => $this->settings['chunk_size'],
                'chunk_overlap' => $this->settings['chunk_overlap'],
            ]
        ];
    }
}

<?php

namespace WP_GPT_RAG_Chat;

/**
 * Pinecone API integration class
 */
class Pinecone {
    
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
     * Get Pinecone API base URL
     */
    private function get_api_base_url() {
        if (empty($this->settings['pinecone_host'])) {
            throw new \Exception(__('Pinecone host URL is not configured.', 'wp-gpt-rag-chat'));
        }
        
        // Remove trailing slash if present
        return rtrim($this->settings['pinecone_host'], '/');
    }
    
    /**
     * Upsert vectors to Pinecone
     */
    public function upsert_vectors($vectors) {
        if (empty($this->settings['pinecone_api_key'])) {
            throw new \Exception(__('Pinecone API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        if (empty($vectors) || !is_array($vectors)) {
            throw new \Exception(__('Invalid vectors data.', 'wp-gpt-rag-chat'));
        }
        
        // Validate vectors
        foreach ($vectors as $vector) {
            if (!isset($vector['id']) || !isset($vector['values']) || !isset($vector['metadata'])) {
                throw new \Exception(__('Invalid vector format. Each vector must have id, values, and metadata.', 'wp-gpt-rag-chat'));
            }
            
            if (!is_array($vector['values'])) {
                throw new \Exception(__('Vector values must be an array.', 'wp-gpt-rag-chat'));
            }
        }
        
        $response = $this->make_request('/vectors/upsert', [
            'vectors' => $vectors
        ]);
        
        if (!isset($response['upsertedCount'])) {
            throw new \Exception(__('Invalid response from Pinecone API.', 'wp-gpt-rag-chat'));
        }
        
        return $response;
    }
    
    /**
     * Query vectors from Pinecone
     */
    public function query_vectors($query_vector, $top_k = null, $filter = null) {
        if (empty($this->settings['pinecone_api_key'])) {
            throw new \Exception(__('Pinecone API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        if (empty($query_vector) || !is_array($query_vector)) {
            throw new \Exception(__('Invalid query vector.', 'wp-gpt-rag-chat'));
        }
        
        $top_k = $top_k ?? $this->settings['top_k'];
        
        $data = [
            'vector' => $query_vector,
            'topK' => $top_k,
            'includeMetadata' => true,
            'includeValues' => false
        ];
        
        if ($filter) {
            $data['filter'] = $filter;
        }
        
        $response = $this->make_request('/query', $data);
        
        if (!isset($response['matches'])) {
            throw new \Exception(__('Invalid response from Pinecone API.', 'wp-gpt-rag-chat'));
        }
        
        // Filter by similarity threshold
        $threshold = $this->settings['similarity_threshold'];
        $filtered_matches = array_filter($response['matches'], function($match) use ($threshold) {
            return $match['score'] >= $threshold;
        });
        
        return [
            'matches' => array_values($filtered_matches),
            'total_matches' => count($response['matches']),
            'filtered_matches' => count($filtered_matches)
        ];
    }
    
    /**
     * Delete vectors from Pinecone
     */
    public function delete_vectors($vector_ids) {
        if (empty($this->settings['pinecone_api_key'])) {
            throw new \Exception(__('Pinecone API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        if (empty($vector_ids) || !is_array($vector_ids)) {
            throw new \Exception(__('Invalid vector IDs.', 'wp-gpt-rag-chat'));
        }
        
        $response = $this->make_request('/vectors/delete', [
            'ids' => $vector_ids
        ]);
        
        return $response;
    }
    
    /**
     * Delete vectors by filter
     */
    public function delete_vectors_by_filter($filter) {
        if (empty($this->settings['pinecone_api_key'])) {
            throw new \Exception(__('Pinecone API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        if (empty($filter)) {
            throw new \Exception(__('Filter is required for delete operation.', 'wp-gpt-rag-chat'));
        }
        
        $response = $this->make_request('/vectors/delete', [
            'filter' => $filter
        ]);
        
        return $response;
    }
    
    /**
     * Get index statistics
     */
    public function get_index_stats() {
        if (empty($this->settings['pinecone_api_key'])) {
            throw new \Exception(__('Pinecone API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        $response = $this->make_get_request('/describe_index_stats');
        
        return $response;
    }
    
    /**
     * Make GET HTTP request to Pinecone API
     */
    private function make_get_request($endpoint) {
        $url = $this->get_api_base_url() . $endpoint;
        
        $headers = [
            'Api-Key' => $this->settings['pinecone_api_key'],
            'Content-Type' => 'application/json',
            'User-Agent' => 'Nuwab-AI-Assistant/' . WP_GPT_RAG_CHAT_VERSION
        ];
        
        $args = [
            'method' => 'GET',
            'headers' => $headers,
            'timeout' => 60,
            'sslverify' => true
        ];
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = sprintf(
                __('Pinecone API request failed: %s', 'wp-gpt-rag-chat'),
                $response->get_error_message()
            );
            Error_Logger::log_pinecone_error($error_message, ['url' => $url, 'args' => $args]);
            throw new \Exception($error_message);
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_message = $this->parse_error_response($body, $status_code);
            Error_Logger::log_pinecone_error($error_message, ['status_code' => $status_code, 'body' => $body]);
            throw new \Exception($error_message);
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = __('Invalid JSON response from Pinecone API.', 'wp-gpt-rag-chat');
            Error_Logger::log_pinecone_error($error_message, ['body' => $body, 'json_error' => json_last_error_msg()]);
            throw new \Exception($error_message);
        }
        
        return $decoded;
    }
    
    /**
     * Make HTTP request to Pinecone API
     */
    private function make_request($endpoint, $data) {
        $url = $this->get_api_base_url() . $endpoint;
        
        $headers = [
            'Api-Key' => $this->settings['pinecone_api_key'],
            'Content-Type' => 'application/json',
            'User-Agent' => 'Nuwab-AI-Assistant/' . WP_GPT_RAG_CHAT_VERSION
        ];
        
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($data),
            'timeout' => 60,
            'sslverify' => true
        ];
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = sprintf(
                __('Pinecone API request failed: %s', 'wp-gpt-rag-chat'),
                $response->get_error_message()
            );
            Error_Logger::log_pinecone_error($error_message, ['url' => $url, 'args' => $args]);
            throw new \Exception($error_message);
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_message = $this->parse_error_response($body, $status_code);
            Error_Logger::log_pinecone_error($error_message, ['status_code' => $status_code, 'body' => $body]);
            throw new \Exception($error_message);
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = __('Invalid JSON response from Pinecone API.', 'wp-gpt-rag-chat');
            Error_Logger::log_pinecone_error($error_message, ['body' => $body, 'json_error' => json_last_error_msg()]);
            throw new \Exception($error_message);
        }
        
        return $decoded;
    }
    
    /**
     * Parse error response from Pinecone API
     */
    private function parse_error_response($body, $status_code) {
        $decoded = json_decode($body, true);
        
        if (isset($decoded['message'])) {
            return sprintf(
                __('Pinecone API error (%d): %s', 'wp-gpt-rag-chat'),
                $status_code,
                $decoded['message']
            );
        }
        
        return sprintf(
            __('Pinecone API error (%d): %s', 'wp-gpt-rag-chat'),
            $status_code,
            $body
        );
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        try {
            $stats = $this->get_index_stats();
            
            if (!isset($stats['totalVectorCount'])) {
                throw new \Exception(__('Invalid response from Pinecone API.', 'wp-gpt-rag-chat'));
            }
            
            return [
                'success' => true,
                'message' => __('Pinecone API connection successful.', 'wp-gpt-rag-chat'),
                'total_vectors' => $stats['totalVectorCount'],
                'dimension' => $stats['dimension'] ?? 'Unknown'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate vector ID
     */
    public function generate_vector_id($post_id, $chunk_index) {
        return 'wp_post_' . $post_id . '_chunk_' . $chunk_index;
    }
    
    /**
     * Parse vector ID
     */
    public function parse_vector_id($vector_id) {
        if (preg_match('/^wp_post_(\d+)_chunk_(\d+)$/', $vector_id, $matches)) {
            return [
                'post_id' => intval($matches[1]),
                'chunk_index' => intval($matches[2])
            ];
        }
        
        return null;
    }
    
    /**
     * Create metadata for vector
     */
    public function create_vector_metadata($chunk_data) {
        return [
            'post_id' => $chunk_data['post_id'],
            'chunk_index' => $chunk_data['chunk_index'],
            'post_title' => $chunk_data['post_title'],
            'post_type' => $chunk_data['post_type'],
            'post_url' => $chunk_data['post_url'],
            'post_date' => $chunk_data['post_date'],
            'content_hash' => $chunk_data['content_hash'],
            'content_length' => strlen($chunk_data['content']),
            'indexed_at' => current_time('mysql')
        ];
    }
    
    /**
     * Clear all vectors for a post
     */
    public function clear_post_vectors($post_id) {
        $filter = [
            'post_id' => ['$eq' => $post_id]
        ];
        
        return $this->delete_vectors_by_filter($filter);
    }
    
    /**
     * Get vectors for a post
     */
    public function get_post_vectors($post_id) {
        $filter = [
            'post_id' => ['$eq' => $post_id]
        ];
        
        // Query with a dummy vector to get all vectors for the post
        $dummy_vector = array_fill(0, 1536, 0); // Assuming 1536 dimensions
        
        $response = $this->query_vectors($dummy_vector, 10000, $filter);
        
        return $response['matches'];
    }
    
    /**
     * Validate API key format
     */
    public function validate_api_key($api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        // Pinecone API keys are typically 36 characters long (UUID format)
        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $api_key)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate environment format
     */
    public function validate_environment($environment) {
        if (empty($environment)) {
            return false;
        }
        
        // Pinecone environments are typically in format like "us-west1-gcp"
        if (!preg_match('/^[a-z0-9-]+$/', $environment)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate index name format
     */
    public function validate_index_name($index_name) {
        if (empty($index_name)) {
            return false;
        }
        
        // Pinecone index names can contain letters, numbers, and hyphens
        if (!preg_match('/^[a-z0-9-]+$/', $index_name)) {
            return false;
        }
        
        return true;
    }
}

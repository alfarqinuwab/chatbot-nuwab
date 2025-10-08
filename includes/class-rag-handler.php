<?php

namespace WP_GPT_RAG_Chat;

/**
 * RAG Handler
 * Main class for Retrieval-Augmented Generation functionality
 */
class RAG_Handler {
    
    private $settings;
    private $openai;
    private $vector_db;
    private $analytics;
    
    public function __construct() {
        $this->settings = Settings::get_settings();
        $this->openai = new OpenAI();
        $this->vector_db = new Vector_DB();
        $this->analytics = new Analytics();
    }
    
    /**
     * Retrieve sources for a query
     */
    public function retrieve_sources($query, $limit = 5) {
        try {
            // Check if RAG is enabled
            if (empty($this->settings['enable_rag'])) {
                return [];
            }
            
            // Generate embedding for the query
            $embeddings = $this->openai->create_embeddings([$query]);
            if (empty($embeddings) || !isset($embeddings[0])) {
                error_log('WP GPT RAG Chat: Failed to generate embedding for query');
                return [];
            }
            $query_embedding = $embeddings[0];
            
            // Search for similar vectors
            $search_result = $this->vector_db->search($query_embedding, $limit);
            
            if (empty($search_result) || empty($search_result['matches'])) {
                return [];
            }
            
            // Convert to sources format and fetch content from local database
            $sources = [];
            foreach ($search_result['matches'] as $match) {
                $post_id = $match['metadata']['post_id'] ?? null;
                $chunk_index = $match['metadata']['chunk_index'] ?? 0;
                
                // Fetch content from local database
                $content = $this->get_content_from_local_db($post_id, $chunk_index);
                
                $sources[] = [
                    'content' => $content,
                    'post_id' => $post_id,
                    'score' => $match['score'] ?? 0,
                    'metadata' => $match['metadata'] ?? []
                ];
            }
            
            return $sources;
            
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat: Error retrieving sources - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get content from local database
     */
    private function get_content_from_local_db($post_id, $chunk_index) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $result = $wpdb->get_var($wpdb->prepare("
            SELECT content 
            FROM $vectors_table 
            WHERE post_id = %d AND chunk_index = %d
        ", $post_id, $chunk_index));
        
        return $result ?: '';
    }
    
    /**
     * Process a query with RAG
     */
    public function process_query($query, $context = []) {
        try {
            // Retrieve relevant sources
            $sources = $this->retrieve_sources($query);
            
            if (empty($sources)) {
                return [
                    'response' => null,
                    'sources' => [],
                    'metadata' => [
                        'rag_enabled' => true,
                        'sources_found' => 0,
                        'fallback_to_training' => true
                    ]
                ];
            }
            
            // Build context from sources
            $context_text = $this->build_context_from_sources($sources);
            
            // Generate response using OpenAI with context
            $messages = [
                ['role' => 'system', 'content' => 'You are a helpful assistant. Use the provided context to answer questions accurately.'],
                ['role' => 'user', 'content' => $query]
            ];
            $response = $this->openai->generate_chat_completion($messages, $context_text);
            
            return [
                'response' => $response,
                'sources' => $sources,
                'metadata' => [
                    'rag_enabled' => true,
                    'sources_found' => count($sources),
                    'fallback_to_training' => false
                ]
            ];
            
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat: Error processing query - ' . $e->getMessage());
            return [
                'response' => null,
                'sources' => [],
                'metadata' => [
                    'rag_enabled' => true,
                    'error' => $e->getMessage(),
                    'fallback_to_training' => true
                ]
            ];
        }
    }
    
    /**
     * Build context text from sources
     */
    private function build_context_from_sources($sources) {
        $context_parts = [];
        
        foreach ($sources as $source) {
            if (!empty($source['content'])) {
                $context_parts[] = $source['content'];
            }
        }
        
        return implode("\n\n", $context_parts);
    }
    
    /**
     * Check if RAG is properly configured
     */
    public function is_configured() {
        return !empty($this->settings['enable_rag']) && 
               !empty($this->settings['openai_api_key']) && 
               !empty($this->settings['pinecone_api_key']);
    }
    
    /**
     * Get RAG status
     */
    public function get_status() {
        return [
            'enabled' => !empty($this->settings['enable_rag']),
            'openai_configured' => !empty($this->settings['openai_api_key']),
            'pinecone_configured' => !empty($this->settings['pinecone_api_key']),
            'vector_count' => $this->get_vector_count()
        ];
    }
    
    /**
     * Get total vector count
     */
    private function get_vector_count() {
        try {
            $stats = $this->vector_db->get_stats();
            return $stats['total_vectors'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

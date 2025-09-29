<?php

namespace WP_GPT_RAG_Chat;

/**
 * OpenAI API integration class
 */
class OpenAI {
    
    /**
     * OpenAI API base URL
     */
    const API_BASE_URL = 'https://api.openai.com/v1';
    
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
     * Create embeddings for text
     */
    public function create_embeddings($texts) {
        if (empty($this->settings['openai_api_key'])) {
            throw new \Exception(__('OpenAI API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        if (!is_array($texts)) {
            $texts = [$texts];
        }
        
        // Validate texts
        foreach ($texts as $text) {
            if (empty($text) || strlen($text) > 8000) {
                throw new \Exception(__('Text must be between 1 and 8000 characters.', 'wp-gpt-rag-chat'));
            }
        }
        
        $response = $this->make_request('/embeddings', [
            'model' => $this->settings['embedding_model'],
            'input' => $texts,
            'encoding_format' => 'float'
        ]);
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            throw new \Exception(__('Invalid response from OpenAI API.', 'wp-gpt-rag-chat'));
        }
        
        $embeddings = [];
        foreach ($response['data'] as $item) {
            if (!isset($item['embedding']) || !is_array($item['embedding'])) {
                throw new \Exception(__('Invalid embedding data from OpenAI API.', 'wp-gpt-rag-chat'));
            }
            $embeddings[] = $item['embedding'];
        }
        
        return $embeddings;
    }
    
    /**
     * Generate chat completion
     */
    public function generate_chat_completion($messages, $context = '') {
        if (empty($this->settings['openai_api_key'])) {
            throw new \Exception(__('OpenAI API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        // Prepare system message with context
        $system_message = $this->build_system_message($context);
        
        // Add system message to the beginning
        array_unshift($messages, [
            'role' => 'system',
            'content' => $system_message
        ]);
        
        $response = $this->make_request('/chat/completions', [
            'model' => $this->settings['gpt_model'],
            'messages' => $messages,
            'max_tokens' => $this->settings['max_tokens'],
            'temperature' => $this->settings['temperature'],
            'stream' => false
        ]);
        
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new \Exception(__('Invalid response from OpenAI API.', 'wp-gpt-rag-chat'));
        }
        
        return $response['choices'][0]['message']['content'];
    }
    
    /**
     * Build system message with context
     */
    private function build_system_message($context) {
        $base_message = __("You are a helpful AI assistant that answers questions based on the provided context from a WordPress website. Use the context to provide accurate, helpful, and relevant answers. If the context doesn't contain enough information to answer the question, say so clearly. Always be honest about the limitations of your knowledge based on the provided context.", 'wp-gpt-rag-chat');
        
        if (!empty($context)) {
            $base_message .= "\n\n" . __('Context from the website:', 'wp-gpt-rag-chat') . "\n\n" . $context;
        }
        
        return $base_message;
    }
    
    /**
     * Make HTTP request to OpenAI API
     */
    private function make_request($endpoint, $data) {
        $url = self::API_BASE_URL . $endpoint;
        
        $headers = [
            'Authorization' => 'Bearer ' . $this->settings['openai_api_key'],
            'Content-Type' => 'application/json',
            'User-Agent' => 'WP-GPT-RAG-Chat/' . WP_GPT_RAG_CHAT_VERSION
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
            throw new \Exception(sprintf(
                __('OpenAI API request failed: %s', 'wp-gpt-rag-chat'),
                $response->get_error_message()
            ));
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_message = $this->parse_error_response($body, $status_code);
            throw new \Exception($error_message);
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(__('Invalid JSON response from OpenAI API.', 'wp-gpt-rag-chat'));
        }
        
        return $decoded;
    }
    
    /**
     * Parse error response from OpenAI API
     */
    private function parse_error_response($body, $status_code) {
        $decoded = json_decode($body, true);
        
        if (isset($decoded['error']['message'])) {
            return sprintf(
                __('OpenAI API error (%d): %s', 'wp-gpt-rag-chat'),
                $status_code,
                $decoded['error']['message']
            );
        }
        
        return sprintf(
            __('OpenAI API error (%d): %s', 'wp-gpt-rag-chat'),
            $status_code,
            $body
        );
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        try {
            // Test with a simple embedding request
            $embeddings = $this->create_embeddings(['Test connection']);
            
            if (empty($embeddings) || !is_array($embeddings[0])) {
                throw new \Exception(__('Invalid embedding response.', 'wp-gpt-rag-chat'));
            }
            
            return [
                'success' => true,
                'message' => __('OpenAI API connection successful.', 'wp-gpt-rag-chat'),
                'embedding_dimensions' => count($embeddings[0])
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get model information
     */
    public function get_model_info() {
        $models = [
            'gpt-4' => [
                'name' => 'GPT-4',
                'max_tokens' => 8192,
                'context_length' => 8192,
                'description' => __('Most capable model, best for complex tasks', 'wp-gpt-rag-chat')
            ],
            'gpt-4-turbo' => [
                'name' => 'GPT-4 Turbo',
                'max_tokens' => 4096,
                'context_length' => 128000,
                'description' => __('Faster and cheaper than GPT-4, larger context', 'wp-gpt-rag-chat')
            ],
            'gpt-3.5-turbo' => [
                'name' => 'GPT-3.5 Turbo',
                'max_tokens' => 4096,
                'context_length' => 16384,
                'description' => __('Fast and cost-effective for most tasks', 'wp-gpt-rag-chat')
            ]
        ];
        
        $embedding_models = [
            'text-embedding-3-large' => [
                'name' => 'text-embedding-3-large',
                'dimensions' => 3072,
                'description' => __('Highest quality embeddings, 3072 dimensions', 'wp-gpt-rag-chat')
            ],
            'text-embedding-3-small' => [
                'name' => 'text-embedding-3-small',
                'dimensions' => 1536,
                'description' => __('Good quality embeddings, 1536 dimensions', 'wp-gpt-rag-chat')
            ],
            'text-embedding-ada-002' => [
                'name' => 'text-embedding-ada-002',
                'dimensions' => 1536,
                'description' => __('Legacy model, 1536 dimensions', 'wp-gpt-rag-chat')
            ]
        ];
        
        return [
            'chat_models' => $models,
            'embedding_models' => $embedding_models
        ];
    }
    
    /**
     * Estimate token usage
     */
    public function estimate_tokens($text) {
        // Rough estimation: 1 token ≈ 4 characters for English text
        return ceil(strlen($text) / 4);
    }
    
    /**
     * Get usage statistics
     */
    public function get_usage_stats() {
        try {
            $response = $this->make_request('/usage', []);
            
            if (isset($response['data'])) {
                return $response['data'];
            }
            
            return null;
        } catch (\Exception $e) {
            // Usage API might not be available or accessible
            return null;
        }
    }
    
    /**
     * Validate API key format
     */
    public function validate_api_key($api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        // OpenAI API keys start with 'sk-' and can have various formats:
        // - Legacy: sk-[48 chars] (51 total)
        // - Project: sk-proj-[more chars] (varies)
        // - Organization: sk-[org]-[more chars] (varies)
        if (!preg_match('/^sk-[a-zA-Z0-9\-_]{20,}$/', $api_key)) {
            return false;
        }
        
        return true;
    }
}

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
        
        // Detect language from the last user message
        $user_language = $this->detect_language($messages);
        
        // Prepare system message with context and language instruction
        $system_message = $this->build_system_message($context, $user_language);
        
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
     * Build system message with context and language instruction
     */
    private function build_system_message($context, $language = 'en') {
        $mode = $this->settings['response_mode'] ?? 'hybrid';
        
        // Language instruction
        $language_instruction = $this->get_language_instruction($language);
        
        if ($mode === 'openai') {
            $message = __("You are a friendly AI assistant for a WordPress website. Provide clear, helpful answers using your general knowledge. When relevant, mention that visitors can find more details on the site.", 'wp-gpt-rag-chat');
            $custom_prompt = trim($this->settings['system_prompt'] ?? '');
            $base_message = $custom_prompt !== '' ? $custom_prompt : $message;
            return $base_message . "\n\n" . $language_instruction;
        }
        
        if ($mode === 'knowledge_base') {
            $message = __("You are a helpful AI assistant that must answer strictly using the provided context from the WordPress knowledge base. If the context doesn't contain enough information to answer the question, say so clearly. Do not invent information that isn't present in the context.", 'wp-gpt-rag-chat');
            
            $message .= "\n\n" . __('IMPORTANT: When you see links in format 🔗 [Title](URL) in the context, preserve them at the end of your response exactly as they appear. Keep the separator lines (━━━━) if multiple sources are provided.', 'wp-gpt-rag-chat');
            
            $message .= "\n\n" . $language_instruction;
            
            if (!empty($context)) {
                $message .= "\n\n" . __('Context from the website:', 'wp-gpt-rag-chat') . "\n\n" . $context;
            }
            
            return $message;
        }
        
        // Hybrid mode (default)
        $default_hybrid = __("You are a helpful AI assistant for a WordPress website. When context is provided, prioritise it for accurate, relevant answers. If the context is missing or insufficient, you may rely on your general knowledge while noting any uncertainties.", 'wp-gpt-rag-chat');
        $custom_prompt = trim($this->settings['system_prompt'] ?? '');
        $message = $custom_prompt !== '' ? $custom_prompt : $default_hybrid;
        
        $message .= "\n\n" . __('IMPORTANT: When you see links in format 🔗 [Title](URL) in the context, preserve them at the end of your response exactly as they appear. Keep the separator lines (━━━━) if multiple sources are provided.', 'wp-gpt-rag-chat');
        
        $message .= "\n\n" . $language_instruction;
        
        if (!empty($context)) {
            $message .= "\n\n" . __('Context from the website:', 'wp-gpt-rag-chat') . "\n\n" . $context;
        }
        
        return $message;
    }
    
    /**
     * Detect language from messages
     */
    private function detect_language($messages) {
        // Get the last user message
        $last_user_message = '';
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if ($messages[$i]['role'] === 'user') {
                $last_user_message = $messages[$i]['content'];
                break;
            }
        }
        
        if (empty($last_user_message)) {
            return 'en';
        }
        
        // Simple Arabic detection: check for Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $last_user_message)) {
            return 'ar';
        }
        
        return 'en';
    }
    
    /**
     * Get language-specific instruction
     */
    private function get_language_instruction($language) {
        if ($language === 'ar') {
            return "IMPORTANT: The user is asking in Arabic. You MUST respond ONLY in Arabic language. Write your entire response in clear, professional Arabic.";
        } else {
            return "IMPORTANT: The user is asking in English. You MUST respond ONLY in English language. Write your entire response in clear, professional English.";
        }
    }
    
    /**
     * Make HTTP request to OpenAI API
     */
    private function make_request($endpoint, $data) {
        $url = self::API_BASE_URL . $endpoint;
        
        $headers = [
            'Authorization' => 'Bearer ' . $this->settings['openai_api_key'],
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
                __('OpenAI API request failed: %s', 'wp-gpt-rag-chat'),
                $response->get_error_message()
            );
            Error_Logger::log_openai_error($error_message, ['url' => $url, 'args' => $args]);
            throw new \Exception($error_message);
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_message = $this->parse_error_response($body, $status_code);
            Error_Logger::log_openai_error($error_message, ['status_code' => $status_code, 'body' => $body]);
            throw new \Exception($error_message);
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = __('Invalid JSON response from OpenAI API.', 'wp-gpt-rag-chat');
            Error_Logger::log_openai_error($error_message, ['body' => $body, 'json_error' => json_last_error_msg()]);
            throw new \Exception($error_message);
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

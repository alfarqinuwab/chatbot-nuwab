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
        
        // Single source of truth for embedding model & dimensions:
        // Prefer the Pinecone-side dropdown `embedding_dimensions` (or legacy `pinecone_dimensions`).
        // Map it to the correct OpenAI model and include the explicit dimensions when supported.
        $embedding_dimensions_setting = $this->settings['embedding_dimensions'] ?? ($this->settings['pinecone_dimensions'] ?? '1536');

        $model_mapping = [
            '512' => ['model' => 'text-embedding-3-small', 'dimensions' => 512],
            '1536' => ['model' => 'text-embedding-3-small', 'dimensions' => 1536],
            '3072' => ['model' => 'text-embedding-3-large', 'dimensions' => 3072],
            '1536_ada' => ['model' => 'text-embedding-ada-002', 'dimensions' => 1536],
        ];

        // Default request payload
        $embedding_request = [
            'model' => $this->settings['embedding_model'] ?? 'text-embedding-3-small',
            'input' => $texts,
            'encoding_format' => 'float'
        ];

        if (isset($model_mapping[$embedding_dimensions_setting])) {
            $selected = $model_mapping[$embedding_dimensions_setting];
            $embedding_request['model'] = $selected['model'];
            // OpenAI TE-3 models support an explicit dimensions parameter
            if (in_array($selected['model'], ['text-embedding-3-small', 'text-embedding-3-large'], true)) {
                $embedding_request['dimensions'] = $selected['dimensions'];
            }
        }

        // Make request
        $response = $this->make_request('/embeddings', $embedding_request);
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            throw new \Exception(__('Invalid response from OpenAI API.', 'wp-gpt-rag-chat'));
        }
        
        // Track API usage for embeddings
        $tokens_used = $response['usage']['total_tokens'] ?? null;
        $cost = null;
        if ($tokens_used) {
            // Embedding pricing: $0.0001/1K tokens for text-embedding-3-small, $0.00013/1K for text-embedding-3-large
            $model = $embedding_request['model'];
            if (strpos($model, 'text-embedding-3-large') !== false) {
                $cost = $tokens_used * 0.00013 / 1000;
            } else {
                $cost = $tokens_used * 0.0001 / 1000;
            }
        }
        
        // Track the usage
        \WP_GPT_RAG_Chat\API_Usage_Tracker::track_openai_usage(
            '/embeddings',
            $tokens_used,
            $cost,
            [
                'model' => $embedding_request['model'],
                'input_count' => count($texts),
                'dimensions' => $embedding_request['dimensions'] ?? 'default',
                'total_tokens' => $tokens_used
            ]
        );
        
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
    public function generate_chat_completion($messages, $context = '', $detected_language = null) {
        if (empty($this->settings['openai_api_key'])) {
            throw new \Exception(__('OpenAI API key is not configured.', 'wp-gpt-rag-chat'));
        }
        
        // Use detected language from frontend, or fallback to server-side detection
        $user_language = $detected_language ?: $this->detect_language($messages);
        
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
        
        // Track API usage
        $tokens_used = $response['usage']['total_tokens'] ?? null;
        $prompt_tokens = $response['usage']['prompt_tokens'] ?? 0;
        $completion_tokens = $response['usage']['completion_tokens'] ?? 0;
        
        // Calculate approximate cost (GPT-4 pricing as of 2024)
        $cost = null;
        if ($tokens_used) {
            $model = $this->settings['gpt_model'];
            if (strpos($model, 'gpt-4') !== false) {
                // GPT-4 pricing: $0.03/1K prompt tokens, $0.06/1K completion tokens
                $cost = ($prompt_tokens * 0.03 / 1000) + ($completion_tokens * 0.06 / 1000);
            } elseif (strpos($model, 'gpt-3.5') !== false) {
                // GPT-3.5 pricing: $0.0015/1K prompt tokens, $0.002/1K completion tokens
                $cost = ($prompt_tokens * 0.0015 / 1000) + ($completion_tokens * 0.002 / 1000);
            }
        }
        
        // Track the usage
        \WP_GPT_RAG_Chat\API_Usage_Tracker::track_openai_usage(
            '/chat/completions',
            $tokens_used,
            $cost,
            [
                'model' => $this->settings['gpt_model'],
                'prompt_tokens' => $prompt_tokens,
                'completion_tokens' => $completion_tokens,
                'total_tokens' => $tokens_used,
                'max_tokens' => $this->settings['max_tokens'],
                'temperature' => $this->settings['temperature']
            ]
        );
        
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
            $message = __("You are an official AI assistant representing the Council of Representatives (مجلس النواب) of the Kingdom of Bahrain. You must answer strictly using the provided context from the Council's official website. If the context doesn't contain enough information to answer the question, inform the visitor that this information is not currently available on the website. Never invent information or use knowledge from external sources.", 'wp-gpt-rag-chat');
            
            $message .= "\n\n" . __('IMPORTANT INSTRUCTIONS:', 'wp-gpt-rag-chat');
            $message .= "\n" . __('1. READ the full context carefully before answering.', 'wp-gpt-rag-chat');
            $message .= "\n" . __('2. EXTRACT relevant information from the context and present it in a clear, helpful way.', 'wp-gpt-rag-chat');
            $message .= "\n" . __('3. DO NOT just list titles or headings - explain the actual content, details, and key information.', 'wp-gpt-rag-chat');
            $message .= "\n" . __('4. Write in a professional, respectful tone suitable for a parliamentary institution.', 'wp-gpt-rag-chat');
            $message .= "\n" . __('5. Provide complete, informative sentences that answer the visitor\'s question thoroughly.', 'wp-gpt-rag-chat');
            $message .= "\n" . __('6. STRICTLY use only the information in the context - do not add external information.', 'wp-gpt-rag-chat');
            $message .= "\n" . __('7. When you see links in format 🔗 [Title](URL), preserve them at the end of your response.', 'wp-gpt-rag-chat');
            $message .= "\n" . __('8. Keep separator lines (━━━━) between different sources if multiple are provided.', 'wp-gpt-rag-chat');
            
            $message .= "\n\n" . $language_instruction;
            
            if (!empty($context)) {
                $message .= "\n\n" . __('Context from the website:', 'wp-gpt-rag-chat') . "\n\n" . $context;
            }
            
            return $message;
        }
        
        // Hybrid mode (default) - Customized for Council of Representatives
        $default_hybrid = __("You are an official AI assistant working FOR the Council of Representatives (مجلس النواب) of the Kingdom of Bahrain. You represent the Council directly.

TONE & PERSPECTIVE:
- Speak as 'we' and 'our' (e.g., 'نوفر', 'لدينا', 'يمكننا مساعدتك') - NOT as a third party talking ABOUT the council
- Be helpful, professional, and welcoming
- Act as if you ARE part of the Council's team assisting visitors
- ALWAYS use 'مجلس النواب' not 'البرلمان'

CRITICAL RULES:
1. For SPECIFIC questions: Answer STRICTLY based on the context content provided below.
2. For GENERAL questions (like What can you help with or What topics are available): 
   - SCAN the context below for page titles and links
   - Present them as available topics in a helpful organized way
   - Use the actual titles and URLs provided
   - Do NOT say information is not available when titles and links ARE in the context
3. NEVER provide information about other countries parliaments or external sources.
4. Stay within the boundaries of Council activities sessions legislation members committees services as documented on THIS website.
5. Be helpful and welcoming - if you see available topics in context present them positively.", 'wp-gpt-rag-chat');
        $custom_prompt = trim($this->settings['system_prompt'] ?? '');
        $message = $custom_prompt !== '' ? $custom_prompt : $default_hybrid;
        
        $message .= "\n\n" . __('IMPORTANT INSTRUCTIONS:', 'wp-gpt-rag-chat');
        $message .= "\n" . __('1. READ and UNDERSTAND the full context provided below before answering.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('2. SYNTHESIZE the information from the context into a clear, coherent, and meaningful answer.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('3. DO NOT just repeat titles or headings - explain the actual content, details, and key information.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('4. Write in a professional, respectful tone suitable for a parliamentary institution.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('5. Answer in complete sentences and well-structured paragraphs that are easy to understand.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('6. For general questions about capabilities, ONLY mention topics that appear in the provided context or website structure.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('7. If specific information is not in the context, acknowledge this professionally without inventing details.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('8. When you see links in format 🔗 [Title](URL), preserve them at the end of your response exactly as they appear.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('9. Keep separator lines (━━━━) between different sources if multiple are provided.', 'wp-gpt-rag-chat');
        
        $message .= "\n\n" . __('EXAMPLE - How to handle general questions:', 'wp-gpt-rag-chat');
        $message .= "\n" . __('User: "ماهي المواضيع التي يمكنك مساعدتي فيها؟"', 'wp-gpt-rag-chat');
        $message .= "\n" . __('Response: Brief intro, then all links flowing together on same paragraph separated by spaces.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('CRITICAL: Put all links in ONE continuous line (they will wrap naturally). Use spaces between links.', 'wp-gpt-rag-chat');
        $message .= "\n" . __('Example: "يمكنك تصفح:\n\n🔗 [رابط 1](url1) 🔗 [رابط 2](url2) 🔗 [رابط 3](url3) 🔗 [رابط 4](url4) 🔗 [رابط 5](url5)"', 'wp-gpt-rag-chat');
        $message .= "\n" . __('All links on SAME line (no line breaks between them). They will wrap naturally based on screen width.', 'wp-gpt-rag-chat');
        
        $message .= "\n\n" . $language_instruction;
        
        if (!empty($context)) {
            $message .= "\n\n" . __('Context from the website (IMPORTANT - Extract page titles and URLs from here):', 'wp-gpt-rag-chat') . "\n\n" . $context;
        } else {
            $message .= "\n\n" . __('Note: No specific indexed content was found. Politely inform the visitor that you need more specific information to help them better, and encourage them to ask about specific topics.', 'wp-gpt-rag-chat');
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
        
        // Enhanced Arabic detection: check for Arabic characters and common Arabic words
        $arabic_pattern = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';
        $arabic_words = ['من', 'هو', 'ما', 'متى', 'أين', 'كيف', 'لماذا', 'هل', 'التي', 'الذي', 'التي', 'هذه', 'ذلك', 'هذا', 'التي', 'التي', 'التي'];
        
        // Check for Arabic characters
        if (preg_match($arabic_pattern, $last_user_message)) {
            error_log('WP GPT RAG Chat: Detected Arabic language for query: ' . substr($last_user_message, 0, 100));
            return 'ar';
        }
        
        // Check for common Arabic words (fallback)
        foreach ($arabic_words as $word) {
            if (strpos($last_user_message, $word) !== false) {
                error_log('WP GPT RAG Chat: Detected Arabic language via word match for query: ' . substr($last_user_message, 0, 100));
                return 'ar';
            }
        }
        
        error_log('WP GPT RAG Chat: Detected English language for query: ' . substr($last_user_message, 0, 100));
        return 'en';
    }
    
    /**
     * Get language-specific instruction
     */
    private function get_language_instruction($language) {
        if ($language === 'ar') {
            return "CRITICAL LANGUAGE REQUIREMENT: The user's question is in Arabic. You MUST respond EXCLUSIVELY in Arabic language. Do not use any English words or phrases. Write your entire response in clear, professional Arabic suitable for official parliamentary communication. Use first-person perspective (نوفر، لدينا، يمكننا). If you cannot find specific information in the provided context, respond in Arabic: 'عذراً، هذه المعلومات غير متوفرة حالياً. يمكنك التواصل معنا للحصول على مزيد من المعلومات أو تصفح الأقسام الأخرى المتاحة.'";
        } else {
            return "CRITICAL LANGUAGE REQUIREMENT: The user's question is in English. You MUST respond EXCLUSIVELY in English language. Do not use any Arabic words or phrases. Write your entire response in clear, professional English suitable for official parliamentary communication. Use first-person perspective (we provide, we can help). If you cannot find specific information in the provided context, respond in English: 'I apologize, but this specific information is not currently available. Please feel free to contact us for further assistance or explore other available sections.'";
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

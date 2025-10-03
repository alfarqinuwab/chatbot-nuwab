<?php

namespace WP_GPT_RAG_Chat;

/**
 * Settings management class
 */
class Settings {
    
    /**
     * Settings option name
     */
    const OPTION_NAME = 'wp_gpt_rag_chat_settings';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_wp_gpt_rag_chat_save_settings', [$this, 'ajax_save_settings']);
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'wp_gpt_rag_chat_settings',
            self::OPTION_NAME,
            [$this, 'sanitize_settings']
        );
        
        // OpenAI API Section
        add_settings_section(
            'wp_gpt_rag_chat_openai',
            '',
            [$this, 'empty_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'openai_api_key',
            __('OpenAI API Key', 'wp-gpt-rag-chat'),
            [$this, 'api_key_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_openai',
            ['field' => 'openai_api_key']
        );
        
        // Models Section
        add_settings_section(
            'wp_gpt_rag_chat_models',
            '',
            [$this, 'empty_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'embedding_model',
            __('Embedding Model', 'wp-gpt-rag-chat'),
            [$this, 'select_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_models',
            [
                'field' => 'embedding_model',
                'options' => [
                    'text-embedding-3-large' => __('text-embedding-3-large (Best Quality)', 'wp-gpt-rag-chat'),
                    'text-embedding-3-small' => __('text-embedding-3-small (Good Quality)', 'wp-gpt-rag-chat'),
                    'text-embedding-ada-002' => __('text-embedding-ada-002 (Legacy)', 'wp-gpt-rag-chat'),
                ]
            ]
        );
        
        add_settings_field(
            'gpt_model',
            __('Chat Model', 'wp-gpt-rag-chat'),
            [$this, 'select_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_models',
            [
                'field' => 'gpt_model',
                'options' => [
                    'gpt-4' => __('GPT-4 (Best Quality)', 'wp-gpt-rag-chat'),
                    'gpt-4-turbo' => __('GPT-4 Turbo (Fast & Good)', 'wp-gpt-rag-chat'),
                    'gpt-3.5-turbo' => __('GPT-3.5 Turbo (Fast & Cheap)', 'wp-gpt-rag-chat'),
                ]
            ]
        );
        
        // Generation Parameters Section
        add_settings_section(
            'wp_gpt_rag_chat_generation',
            '',
            [$this, 'empty_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'max_tokens',
            __('Max Tokens', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_generation',
            [
                'field' => 'max_tokens',
                'min' => 100,
                'max' => 4000,
                'step' => 50
            ]
        );
        
        add_settings_field(
            'temperature',
            __('Temperature', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_generation',
            [
                'field' => 'temperature',
                'min' => 0,
                'max' => 2,
                'step' => 0.1
            ]
        );
        
        // Pinecone Settings Section
        add_settings_section(
            'wp_gpt_rag_chat_pinecone',
            __('Pinecone Configuration', 'wp-gpt-rag-chat'),
            [$this, 'pinecone_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'pinecone_api_key',
            __('Pinecone API Key', 'wp-gpt-rag-chat'),
            [$this, 'api_key_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_pinecone',
            ['field' => 'pinecone_api_key']
        );
        
        add_settings_field(
            'pinecone_host',
            __('Pinecone Host URL', 'wp-gpt-rag-chat'),
            [$this, 'text_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_pinecone',
            ['field' => 'pinecone_host']
        );
        
        add_settings_field(
            'pinecone_index_name',
            __('Pinecone Index Name', 'wp-gpt-rag-chat'),
            [$this, 'text_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_pinecone',
            ['field' => 'pinecone_index_name']
        );
        
        // Retrieval Settings Section
        add_settings_section(
            'wp_gpt_rag_chat_retrieval',
            '',
            [$this, 'empty_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'top_k',
            __('Top K Results', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_retrieval',
            [
                'field' => 'top_k',
                'min' => 1,
                'max' => 20,
                'step' => 1
            ]
        );
        
        add_settings_field(
            'similarity_threshold',
            __('Similarity Threshold', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_retrieval',
            [
                'field' => 'similarity_threshold',
                'min' => 0,
                'max' => 1,
                'step' => 0.1
            ]
        );
        
        // Chunking Settings Section
        add_settings_section(
            'wp_gpt_rag_chat_chunking',
            '',
            [$this, 'empty_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'chunk_size',
            __('Chunk Size (characters)', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_chunking',
            [
                'field' => 'chunk_size',
                'min' => 500,
                'max' => 2000,
                'step' => 50
            ]
        );
        
        add_settings_field(
            'chunk_overlap',
            __('Chunk Overlap (characters)', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_chunking',
            [
                'field' => 'chunk_overlap',
                'min' => 50,
                'max' => 500,
                'step' => 25
            ]
        );
        
        // Privacy Settings Section
        add_settings_section(
            'wp_gpt_rag_chat_privacy',
            '',
            [$this, 'empty_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'log_retention_days',
            __('Log Retention (days)', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_privacy',
            [
                'field' => 'log_retention_days',
                'min' => 1,
                'max' => 365,
                'step' => 1
            ]
        );
        
        add_settings_field(
            'anonymize_ips',
            __('Anonymize IP Addresses', 'wp-gpt-rag-chat'),
            [$this, 'checkbox_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_privacy',
            ['field' => 'anonymize_ips']
        );
        
        add_settings_field(
            'require_consent',
            __('Require Privacy Consent', 'wp-gpt-rag-chat'),
            [$this, 'checkbox_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_privacy',
            ['field' => 'require_consent']
        );
        
        add_settings_field(
            'enable_pii_masking',
            __('Enable PII Masking', 'wp-gpt-rag-chat'),
            [$this, 'checkbox_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_privacy',
            [
                'field' => 'enable_pii_masking',
                'description' => __('Automatically mask emails, phone numbers, and credit cards in logged conversations.', 'wp-gpt-rag-chat')
            ]
        );
        
        // Auto-Indexing Settings Section
        add_settings_section(
            'wp_gpt_rag_chat_auto_indexing',
            '',
            [$this, 'empty_section_callback'],
            'wp_gpt_rag_chat_settings'
        );
        
        add_settings_field(
            'enable_auto_indexing',
            __('Enable Auto-Indexing', 'wp-gpt-rag-chat'),
            [$this, 'checkbox_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_auto_indexing',
            [
                'field' => 'enable_auto_indexing',
                'description' => __('Automatically index posts to Pinecone when they are saved or published.', 'wp-gpt-rag-chat')
            ]
        );
        
        add_settings_field(
            'auto_index_post_types',
            __('Auto-Index Post Types', 'wp-gpt-rag-chat'),
            [$this, 'multi_checkbox_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_auto_indexing',
            [
                'field' => 'auto_index_post_types',
                'description' => __('Select which post types should be automatically indexed.', 'wp-gpt-rag-chat')
            ]
        );
        
        add_settings_field(
            'auto_index_delay',
            __('Indexing Delay (seconds)', 'wp-gpt-rag-chat'),
            [$this, 'number_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_auto_indexing',
            [
                'field' => 'auto_index_delay',
                'min' => 10,
                'max' => 600,
                'step' => 10,
                'description' => __('Time to wait before indexing (prevents indexing during rapid edits).', 'wp-gpt-rag-chat')
            ]
        );

        add_settings_field(
            'response_mode',
            __('Response Source', 'wp-gpt-rag-chat'),
            [$this, 'select_field_callback'],
            'wp_gpt_rag_chat_settings',
            'wp_gpt_rag_chat_privacy',
            [
                'field' => 'response_mode',
                'options' => [
                    'openai' => __('OpenAI (Generative AI)', 'wp-gpt-rag-chat'),
                    'knowledge_base' => __('Knowledge Base Only (Indexed Content)', 'wp-gpt-rag-chat'),
                    'hybrid' => __('Hybrid (AI with Knowledge Base Context)', 'wp-gpt-rag-chat'),
                ]
            ]
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        // OpenAI settings
        $sanitized['openai_api_key'] = sanitize_text_field($input['openai_api_key'] ?? '');
        $sanitized['openai_environment'] = sanitize_text_field($input['openai_environment'] ?? 'openai');
        $sanitized['chat_model'] = sanitize_text_field($input['chat_model'] ?? 'gpt-4.1');
        $sanitized['openai_vision'] = isset($input['openai_vision']) ? (bool) $input['openai_vision'] : false;
        $sanitized['max_tokens'] = intval($input['max_tokens'] ?? 1024);
        $sanitized['temperature'] = floatval($input['temperature'] ?? 0.8);
        
        // Pinecone settings
        $sanitized['pinecone_name'] = sanitize_text_field($input['pinecone_name'] ?? 'Pinecone');
        $sanitized['pinecone_type'] = sanitize_text_field($input['pinecone_type'] ?? 'pinecone');
        $sanitized['pinecone_api_key'] = sanitize_text_field($input['pinecone_api_key'] ?? '');
        $sanitized['pinecone_host'] = esc_url_raw($input['pinecone_host'] ?? '');
        $sanitized['pinecone_namespace'] = sanitize_text_field($input['pinecone_namespace'] ?? '');
        $sanitized['pinecone_dimensions'] = intval($input['pinecone_dimensions'] ?? '');
        $sanitized['embedding_dimensions'] = sanitize_text_field($input['embedding_dimensions'] ?? '1536');
        $sanitized['pinecone_env_id'] = sanitize_text_field($input['pinecone_env_id'] ?? '');
        $sanitized['pinecone_score_threshold'] = floatval($input['pinecone_score_threshold'] ?? 0.7);
        
        // Chatbot behavior settings
        $sanitized['system_prompt'] = sanitize_textarea_field($input['system_prompt'] ?? 'You are a helpful AI assistant. Answer questions based on the provided context.');
        
        // Indexing settings
        $sanitized['post_types'] = isset($input['post_types']) ? array_map('sanitize_text_field', $input['post_types']) : ['post', 'page'];
        $sanitized['auto_sync'] = isset($input['auto_sync']) ? (bool) $input['auto_sync'] : true;
        
        // Chat settings
        $sanitized['enable_chatbot'] = isset($input['enable_chatbot']) ? (bool) $input['enable_chatbot'] : true;
        $sanitized['chat_visibility'] = sanitize_text_field($input['chat_visibility'] ?? 'everyone');
        $sanitized['widget_placement'] = sanitize_text_field($input['widget_placement'] ?? 'floating');
        $sanitized['greeting_text'] = sanitize_text_field($input['greeting_text'] ?? 'Hello! How can I help you today?');
        $sanitized['enable_history'] = isset($input['enable_history']) ? (bool) $input['enable_history'] : true;
        $sanitized['max_conversation_length'] = intval($input['max_conversation_length'] ?? 10);
        $sanitized['allow_anonymous'] = isset($input['allow_anonymous']) ? (bool) $input['allow_anonymous'] : true;
        $sanitized['response_mode'] = sanitize_text_field($input['response_mode'] ?? 'hybrid');
        
        // Advanced settings
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? (bool) $input['debug_mode'] : false;
        $sanitized['logging_level'] = sanitize_text_field($input['logging_level'] ?? 'error');
        $sanitized['embedding_model'] = sanitize_text_field($input['embedding_model'] ?? 'text-embedding-3-small');
        $sanitized['maintenance_mode'] = isset($input['maintenance_mode']) ? (bool) $input['maintenance_mode'] : false;
        
        // Legacy settings (for backward compatibility)
        // Note: embedding_model is set above, don't override it here
        $sanitized['gpt_model'] = sanitize_text_field($input['gpt_model'] ?? 'gpt-4');
        $sanitized['pinecone_index_name'] = sanitize_text_field($input['pinecone_index_name'] ?? '');
        $sanitized['top_k'] = intval($input['top_k'] ?? 5);
        $sanitized['similarity_threshold'] = floatval($input['similarity_threshold'] ?? 0.7);
        $sanitized['chunk_size'] = intval($input['chunk_size'] ?? 1400);
        $sanitized['chunk_overlap'] = intval($input['chunk_overlap'] ?? 150);
        $sanitized['log_retention_days'] = intval($input['log_retention_days'] ?? 30);
        $sanitized['anonymize_ips'] = isset($input['anonymize_ips']) ? (bool) $input['anonymize_ips'] : false;
        $sanitized['require_consent'] = isset($input['require_consent']) ? (bool) $input['require_consent'] : true;
        $sanitized['enable_pii_masking'] = isset($input['enable_pii_masking']) ? (bool) $input['enable_pii_masking'] : true;
        
        // Validate ranges
        $sanitized['max_tokens'] = max(1, min(32768, $sanitized['max_tokens']));
        $sanitized['temperature'] = max(0, min(2, $sanitized['temperature']));
        $sanitized['pinecone_dimensions'] = max(1, min(2048, $sanitized['pinecone_dimensions']));
        $sanitized['pinecone_score_threshold'] = max(0, min(1, $sanitized['pinecone_score_threshold']));
        $sanitized['max_conversation_length'] = max(1, min(50, $sanitized['max_conversation_length']));
        $sanitized['top_k'] = max(1, min(20, $sanitized['top_k']));
        $sanitized['similarity_threshold'] = max(0, min(1, $sanitized['similarity_threshold']));
        $sanitized['chunk_size'] = max(500, min(2000, $sanitized['chunk_size']));
        $sanitized['chunk_overlap'] = max(50, min(500, $sanitized['chunk_overlap']));
        $sanitized['log_retention_days'] = max(1, min(365, $sanitized['log_retention_days']));
        
        // Add redirect with success message after settings are saved
        add_action('admin_init', [$this, 'redirect_after_save'], 20);
        
        return $sanitized;
    }
    
    /**
     * AJAX handler for saving settings
     */
    public function ajax_save_settings() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'wp_gpt_rag_chat_settings_nonce')) {
            wp_send_json_error([
                'message' => __('Security check failed. Please refresh the page and try again.', 'wp-gpt-rag-chat')
            ]);
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('You do not have permission to save settings.', 'wp-gpt-rag-chat')
            ]);
        }
        
        // Get and sanitize the settings data
        $input = $_POST['settings'] ?? [];
        $sanitized_settings = $this->sanitize_settings($input);
        
        // Get current settings to compare
        $current_settings = get_option(self::OPTION_NAME, []);
        
        // Save the settings
        $result = update_option(self::OPTION_NAME, $sanitized_settings);
        
        if ($result) {
            wp_send_json_success([
                'message' => __('Settings saved successfully!', 'wp-gpt-rag-chat'),
                'settings' => $sanitized_settings
            ]);
        } else {
            // Check if settings are actually different
            if ($current_settings === $sanitized_settings) {
                wp_send_json_success([
                    'message' => __('Settings are already up to date.', 'wp-gpt-rag-chat'),
                    'settings' => $sanitized_settings
                ]);
            } else {
                wp_send_json_error([
                    'message' => __('Failed to save settings. Please try again.', 'wp-gpt-rag-chat')
                ]);
            }
        }
    }
    
    /**
     * Redirect after settings save with success message (legacy method for non-AJAX)
     */
    public function redirect_after_save() {
        // Only redirect if we're on the settings page and settings were just saved
        if (isset($_POST['option_page']) && $_POST['option_page'] === 'wp_gpt_rag_chat_settings') {
            $redirect_url = add_query_arg([
                'page' => 'wp-gpt-rag-chat-settings',
                'status' => 'success',
                'message' => urlencode(__('Settings saved successfully!', 'wp-gpt-rag-chat'))
            ], admin_url('admin.php'));
            
            wp_redirect($redirect_url);
            exit;
        }
    }
    
    /**
     * Get settings
     */
    public static function get_settings() {
        $defaults = [
            // OpenAI settings
            'openai_api_key' => '',
            'openai_environment' => 'openai',
            'chat_model' => 'gpt-4.1',
            'openai_vision' => false,
            'max_tokens' => 1024,
            'temperature' => 0.8,
            
            // Pinecone settings
            'pinecone_name' => 'Pinecone',
            'pinecone_type' => 'pinecone',
            'pinecone_api_key' => '',
            'pinecone_host' => '',
            'pinecone_namespace' => '',
            'pinecone_dimensions' => '',
            'embedding_dimensions' => '1536',
            'pinecone_env_id' => '',
            'pinecone_score_threshold' => 0.7,
            
            // Chatbot behavior settings
            'system_prompt' => 'You are a helpful AI assistant. Answer questions based on the provided context.',
            
            // Indexing settings
            'post_types' => ['post', 'page'],
            'auto_sync' => true,
            
            // Chat settings
            'enable_chatbot' => true,
            'chat_visibility' => 'everyone',
            'widget_placement' => 'floating',
            'greeting_text' => 'Hello! How can I help you today?',
            'enable_history' => true,
            'max_conversation_length' => 10,
            'allow_anonymous' => true,
            'response_mode' => 'hybrid',
            
            // Advanced settings
            'debug_mode' => false,
            'logging_level' => 'error',
            'embedding_model' => 'text-embedding-3-small',
            
            // Legacy settings (for backward compatibility)
            'gpt_model' => 'gpt-4',
            'pinecone_index_name' => '',
            'top_k' => 5,
            'similarity_threshold' => 0.7,
            'chunk_size' => 1400,
            'chunk_overlap' => 150,
            'log_retention_days' => 30,
            'anonymize_ips' => false,
            'require_consent' => true,
            'enable_pii_masking' => true,
            
            // RAG Improvements settings
            'enable_query_expansion' => true,
            'enable_reranking' => true,
            'enable_few_shot' => true,
            'few_shot_examples_count' => 5,
            
            // Sitemap fallback settings
            'enable_sitemap_fallback' => true,
            'sitemap_url' => 'sitemap.xml',
            'sitemap_suggestions_count' => 5,
            
            // Auto-indexing settings
            'enable_auto_indexing' => true,
            'auto_index_post_types' => ['post', 'page'],
            'auto_index_delay' => 30,
        ];
        
        $settings = get_option(self::OPTION_NAME, []);
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * Section callbacks
     */
    public function empty_section_callback() {
        // Empty callback for tabbed interface
    }
    
    public function openai_section_callback() {
        echo '<p>' . esc_html__('Configure your OpenAI API settings for embeddings and chat generation.', 'wp-gpt-rag-chat') . '</p>';
    }
    
    public function pinecone_section_callback() {
        echo '<p>' . esc_html__('Configure your Pinecone vector database settings for storing and retrieving embeddings.', 'wp-gpt-rag-chat') . '</p>';
    }
    
    public function chunking_section_callback() {
        echo '<p>' . esc_html__('Configure how content is split into chunks for embedding.', 'wp-gpt-rag-chat') . '</p>';
    }
    
    public function privacy_section_callback() {
        echo '<p>' . esc_html__('Configure privacy and logging settings for user interactions.', 'wp-gpt-rag-chat') . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public function api_key_field_callback($args) {
        $settings = self::get_settings();
        $value = $settings[$args['field']] ?? '';
        $masked_value = $value ? str_repeat('*', strlen($value) - 4) . substr($value, -4) : '';
        
        echo '<input type="password" id="' . esc_attr($args['field']) . '" name="' . self::OPTION_NAME . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
        if ($value) {
            echo '<p class="description">' . esc_html__('Current key ends with: ', 'wp-gpt-rag-chat') . esc_html($masked_value) . '</p>';
        }
    }
    
    public function text_field_callback($args) {
        $settings = self::get_settings();
        $value = $settings[$args['field']] ?? '';
        
        echo '<input type="text" id="' . esc_attr($args['field']) . '" name="' . self::OPTION_NAME . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
        
        // Add help text for specific fields
        if ($args['field'] === 'pinecone_host') {
            echo '<p class="description">' . esc_html__('Your Pinecone index host URL (e.g., https://your-index-12345.svc.pinecone.io)', 'wp-gpt-rag-chat') . '</p>';
        } elseif ($args['field'] === 'pinecone_index_name') {
            echo '<p class="description">' . esc_html__('The name of your Pinecone index (must already exist)', 'wp-gpt-rag-chat') . '</p>';
        }
    }
    
    public function number_field_callback($args) {
        $settings = self::get_settings();
        $value = $settings[$args['field']] ?? '';
        $min = $args['min'] ?? '';
        $max = $args['max'] ?? '';
        $step = $args['step'] ?? '';
        
        echo '<input type="number" id="' . esc_attr($args['field']) . '" name="' . self::OPTION_NAME . '[' . esc_attr($args['field']) . ']" value="' . esc_attr($value) . '" class="small-text"';
        if ($min !== '') echo ' min="' . esc_attr($min) . '"';
        if ($max !== '') echo ' max="' . esc_attr($max) . '"';
        if ($step !== '') echo ' step="' . esc_attr($step) . '"';
        echo ' />';
    }
    
    public function select_field_callback($args) {
        $settings = self::get_settings();
        $value = $settings[$args['field']] ?? '';
        $options = $args['options'] ?? [];
        
        echo '<select id="' . esc_attr($args['field']) . '" name="' . self::OPTION_NAME . '[' . esc_attr($args['field']) . ']">';
        foreach ($options as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '"' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
        }
        echo '</select>';
    }
    
    public function checkbox_field_callback($args) {
        $settings = self::get_settings();
        $value = $settings[$args['field']] ?? false;
        
        echo '<input type="checkbox" id="' . esc_attr($args['field']) . '" name="' . self::OPTION_NAME . '[' . esc_attr($args['field']) . ']" value="1"' . checked($value, true, false) . ' />';
        echo '<label for="' . esc_attr($args['field']) . '">' . esc_html__('Enable', 'wp-gpt-rag-chat') . '</label>';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Multi-checkbox field callback
     */
    public function multi_checkbox_field_callback($args) {
        $settings = self::get_settings();
        $selected = $settings[$args['field']] ?? [];
        
        // Get all public post types
        $post_types = get_post_types(['public' => true], 'objects');
        
        echo '<fieldset>';
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected) ? 'checked' : '';
            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="' . self::OPTION_NAME . '[' . esc_attr($args['field']) . '][]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' />';
            echo ' ' . esc_html($post_type->label) . ' <code>(' . esc_html($post_type->name) . ')</code>';
            echo '</label>';
        }
        echo '</fieldset>';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
}

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
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        // OpenAI settings
        $sanitized['openai_api_key'] = sanitize_text_field($input['openai_api_key'] ?? '');
        $sanitized['embedding_model'] = sanitize_text_field($input['embedding_model'] ?? 'text-embedding-3-large');
        $sanitized['gpt_model'] = sanitize_text_field($input['gpt_model'] ?? 'gpt-4');
        $sanitized['max_tokens'] = intval($input['max_tokens'] ?? 1000);
        $sanitized['temperature'] = floatval($input['temperature'] ?? 0.7);
        
        // Pinecone settings
        $sanitized['pinecone_api_key'] = sanitize_text_field($input['pinecone_api_key'] ?? '');
        $sanitized['pinecone_host'] = esc_url_raw($input['pinecone_host'] ?? '');
        $sanitized['pinecone_index_name'] = sanitize_text_field($input['pinecone_index_name'] ?? '');
        $sanitized['top_k'] = intval($input['top_k'] ?? 5);
        $sanitized['similarity_threshold'] = floatval($input['similarity_threshold'] ?? 0.7);
        
        // Chunking settings
        $sanitized['chunk_size'] = intval($input['chunk_size'] ?? 1400);
        $sanitized['chunk_overlap'] = intval($input['chunk_overlap'] ?? 150);
        
        // Privacy settings
        $sanitized['log_retention_days'] = intval($input['log_retention_days'] ?? 30);
        $sanitized['anonymize_ips'] = isset($input['anonymize_ips']) ? (bool) $input['anonymize_ips'] : false;
        $sanitized['require_consent'] = isset($input['require_consent']) ? (bool) $input['require_consent'] : true;
        
        // Validate ranges
        $sanitized['max_tokens'] = max(100, min(4000, $sanitized['max_tokens']));
        $sanitized['temperature'] = max(0, min(2, $sanitized['temperature']));
        $sanitized['top_k'] = max(1, min(20, $sanitized['top_k']));
        $sanitized['similarity_threshold'] = max(0, min(1, $sanitized['similarity_threshold']));
        $sanitized['chunk_size'] = max(500, min(2000, $sanitized['chunk_size']));
        $sanitized['chunk_overlap'] = max(50, min(500, $sanitized['chunk_overlap']));
        $sanitized['log_retention_days'] = max(1, min(365, $sanitized['log_retention_days']));
        
        return $sanitized;
    }
    
    /**
     * Get settings
     */
    public static function get_settings() {
        $defaults = [
            'openai_api_key' => '',
            'pinecone_api_key' => '',
            'pinecone_host' => '',
            'pinecone_index_name' => '',
            'embedding_model' => 'text-embedding-3-large',
            'gpt_model' => 'gpt-4',
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'top_k' => 5,
            'similarity_threshold' => 0.7,
            'chunk_size' => 1400,
            'chunk_overlap' => 150,
            'log_retention_days' => 30,
            'anonymize_ips' => false,
            'require_consent' => true,
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
    }
}

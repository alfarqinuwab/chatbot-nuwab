<?php

namespace WP_GPT_RAG_Chat;

/**
 * Chat functionality class
 */
class Chat {
    
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
        $this->openai = new OpenAI();
        $this->pinecone = new Pinecone();
        $this->settings = Settings::get_settings();
    }
    
    /**
     * Process a chat query
     */
    public function process_query($query, $conversation_history = []) {
        // Create embedding for the query
        $query_embedding = $this->openai->create_embeddings([$query])[0];
        
        // Retrieve relevant context from Pinecone
        $context = $this->retrieve_context($query_embedding);
        
        // Build conversation messages
        $messages = $this->build_conversation_messages($conversation_history, $query);
        
        // Generate response
        $response = $this->openai->generate_chat_completion($messages, $context);
        
        return $response;
    }
    
    /**
     * Retrieve relevant context from Pinecone
     */
    private function retrieve_context($query_embedding) {
        try {
            $results = $this->pinecone->query_vectors($query_embedding);
            
            if (empty($results['matches'])) {
                return '';
            }
            
            $context_parts = [];
            foreach ($results['matches'] as $match) {
                $metadata = $match['metadata'];
                $context_parts[] = sprintf(
                    "Source: %s (%s)\nURL: %s\nContent: %s",
                    $metadata['post_title'],
                    $metadata['post_type'],
                    $metadata['post_url'],
                    $this->get_chunk_content($metadata['post_id'], $metadata['chunk_index'])
                );
            }
            
            return implode("\n\n---\n\n", $context_parts);
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error retrieving context: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Get chunk content from local database
     */
    private function get_chunk_content($post_id, $chunk_index) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $vector = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$vectors_table} WHERE post_id = %d AND chunk_index = %d",
            $post_id,
            $chunk_index
        ));
        
        if (!$vector) {
            return '';
        }
        
        // Get the actual content from the post
        $post = get_post($post_id);
        if (!$post) {
            return '';
        }
        
        $chunking = new Chunking();
        $chunks = $chunking->chunk_post($post_id);
        
        foreach ($chunks as $chunk) {
            if ($chunk['chunk_index'] === $chunk_index) {
                return $chunk['content'];
            }
        }
        
        return '';
    }
    
    /**
     * Build conversation messages
     */
    private function build_conversation_messages($conversation_history, $current_query) {
        $messages = [];
        
        // Add conversation history
        foreach ($conversation_history as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }
        
        // Add current query
        $messages[] = [
            'role' => 'user',
            'content' => $current_query
        ];
        
        return $messages;
    }
    
    /**
     * Get chat widget HTML
     */
    public function get_chat_widget_html() {
        $settings = $this->settings;
        
        if (empty($settings['openai_api_key']) || empty($settings['pinecone_api_key'])) {
            return '<div class="wp-gpt-rag-chat-error">' . 
                   __('Chat is not available. Please contact the administrator.', 'wp-gpt-rag-chat') . 
                   '</div>';
        }
        
        ob_start();
        ?>
        <div id="wp-gpt-rag-chat-widget" class="wp-gpt-rag-chat-widget">
            <div class="wp-gpt-rag-chat-header">
                <h3><?php esc_html_e('Ask a Question', 'wp-gpt-rag-chat'); ?></h3>
                <button type="button" class="wp-gpt-rag-chat-toggle" aria-label="<?php esc_attr_e('Toggle chat', 'wp-gpt-rag-chat'); ?>">
                    <span class="wp-gpt-rag-chat-icon">×</span>
                </button>
            </div>
            
            <div class="wp-gpt-rag-chat-body">
                <div class="wp-gpt-rag-chat-messages" id="wp-gpt-rag-chat-messages">
                    <div class="wp-gpt-rag-chat-message wp-gpt-rag-chat-message-system">
                        <div class="wp-gpt-rag-chat-message-content">
                            <?php esc_html_e('Hello! I can help you find information from this website. What would you like to know?', 'wp-gpt-rag-chat'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="wp-gpt-rag-chat-input-container">
                    <?php if ($settings['require_consent']): ?>
                    <div class="wp-gpt-rag-chat-consent">
                        <label>
                            <input type="checkbox" id="wp-gpt-rag-chat-consent" />
                            <?php esc_html_e('I agree to the privacy policy and understand that my query will be processed.', 'wp-gpt-rag-chat'); ?>
                        </label>
                    </div>
                    <?php endif; ?>
                    
                    <div class="wp-gpt-rag-chat-input-wrapper">
                        <textarea 
                            id="wp-gpt-rag-chat-input" 
                            placeholder="<?php esc_attr_e('Type your question here...', 'wp-gpt-rag-chat'); ?>"
                            rows="3"
                        ></textarea>
                        <button type="button" id="wp-gpt-rag-chat-send" class="wp-gpt-rag-chat-send-button">
                            <?php esc_html_e('Send', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="wp-gpt-rag-chat-footer">
                <small>
                    <?php esc_html_e('Powered by AI. Responses are based on website content.', 'wp-gpt-rag-chat'); ?>
                </small>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Add chat widget to content
     */
    public function add_chat_widget_to_content($content) {
        // Only add to single posts/pages
        if (!is_singular()) {
            return $content;
        }
        
        // Check if chat is enabled for this post type
        $post_type = get_post_type();
        $enabled_post_types = apply_filters('wp_gpt_rag_chat_enabled_post_types', ['post', 'page']);
        
        if (!in_array($post_type, $enabled_post_types)) {
            return $content;
        }
        
        // Check if chat is enabled for this specific post
        $chat_enabled = get_post_meta(get_the_ID(), '_wp_gpt_rag_chat_enabled', true);
        if ($chat_enabled === '0') {
            return $content;
        }
        
        // Add chat widget
        $chat_widget = $this->get_chat_widget_html();
        
        return $content . $chat_widget;
    }
    
    /**
     * Register shortcode
     */
    public function register_shortcode() {
        add_shortcode('wp_gpt_rag_chat', [$this, 'shortcode_callback']);
    }
    
    /**
     * Shortcode callback
     */
    public function shortcode_callback($atts) {
        $atts = shortcode_atts([
            'enabled' => '1',
            'position' => 'bottom'
        ], $atts);
        
        if ($atts['enabled'] === '0') {
            return '';
        }
        
        return $this->get_chat_widget_html();
    }
    
    /**
     * Get conversation history from session
     */
    private function get_conversation_history() {
        if (!session_id()) {
            session_start();
        }
        
        return $_SESSION['wp_gpt_rag_chat_history'] ?? [];
    }
    
    /**
     * Save conversation history to session
     */
    private function save_conversation_history($history) {
        if (!session_id()) {
            session_start();
        }
        
        $_SESSION['wp_gpt_rag_chat_history'] = $history;
    }
    
    /**
     * Clear conversation history
     */
    public function clear_conversation_history() {
        if (!session_id()) {
            session_start();
        }
        
        unset($_SESSION['wp_gpt_rag_chat_history']);
    }
    
    /**
     * Get chat statistics
     */
    public static function get_chat_stats() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_queries,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as queries_24h,
                COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as queries_7d
            FROM {$logs_table}
        ");
        
        return [
            'total_queries' => intval($stats->total_queries ?? 0),
            'unique_users' => intval($stats->unique_users ?? 0),
            'queries_24h' => intval($stats->queries_24h ?? 0),
            'queries_7d' => intval($stats->queries_7d ?? 0)
        ];
    }
}

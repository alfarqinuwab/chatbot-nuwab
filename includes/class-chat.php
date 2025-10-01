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
        $response_mode = $this->settings['response_mode'] ?? 'hybrid';
        $context = '';
        $query_embedding = null;
        
        try {
            if ($response_mode !== 'openai') {
                // Create embedding for the query
                $query_embedding = $this->openai->create_embeddings([$query])[0];
                
                // Retrieve relevant context from Pinecone
                $context = $this->retrieve_context($query_embedding);
            }
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: ' . $e->getMessage());
            $context = '';
        }
        
        if ($response_mode === 'knowledge_base') {
            return $this->generate_knowledge_base_response($query, $context);
        }
        
        // Build conversation messages
        $messages = $this->build_conversation_messages($conversation_history, $query);
        
        // Generate response
        $response = $this->openai->generate_chat_completion($messages, $context);
        
        return $response;
    }

    /**
     * Generate response using knowledge base only
     */
    private function generate_knowledge_base_response($query, $context) {
        if (empty($context)) {
            return __('عذراً، لم أجد معلومات ذات صلة في قاعدة المعرفة للإجابة على هذا السؤال حالياً.', 'wp-gpt-rag-chat');
        }
        
        return sprintf(
            "%s\n\n%s\n\n%s",
            __('سؤال المستخدم:', 'wp-gpt-rag-chat') . ' ' . $query,
            __('المعلومات ذات الصلة من قاعدة المعرفة:', 'wp-gpt-rag-chat'),
            $context
        );
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
        
        // Check if chat is enabled
        if (empty($settings['enable_chatbot'])) {
            return '';
        }
        
        // Check visibility settings
        if (!$this->should_display_chat()) {
            return '';
        }
        
        if (empty($settings['openai_api_key']) || empty($settings['pinecone_api_key'])) {
            return '<div class="wp-gpt-rag-chat-error" style="font-family: \'Tajawal\', sans-serif; direction: rtl; text-align: right;">' . 
                   'المحادثة غير متاحة حالياً. يرجى التواصل مع المسؤول.' . 
                   '</div>';
        }
        
        ob_start();
        ?>
        <div id="wp-gpt-rag-chat-widget" class="wp-gpt-rag-chat-widget">
            <!-- Floating Button (Collapsed State) -->
            <div class="wp-gpt-rag-chat-fab">
                <div class="wp-gpt-rag-chat-fab-bubble" aria-live="polite"></div>
                <button type="button" class="wp-gpt-rag-chat-fab-button" aria-label="فتح المحادثة">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/avatar_small.png' ); ?>" alt="فتح المحادثة" class="wp-gpt-rag-chat-fab-avatar" />
                </button>
            </div>
            
            <!-- Chat Window (Expanded State) -->
            <div class="wp-gpt-rag-chat-window">
                <div class="wp-gpt-rag-chat-header">
                    <h3 class="wp-gpt-rag-chat-header-title">
                        <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/avatar_small.png' ); ?>" alt="AI Avatar" class="wp-gpt-rag-chat-header-avatar" />
                        مساعدك الذكي
                    </h3>
                    <button type="button" class="wp-gpt-rag-chat-toggle" aria-label="إغلاق المحادثة">
                        <span class="wp-gpt-rag-chat-icon">×</span>
                    </button>
                </div>
            
            <div class="wp-gpt-rag-chat-body">
                <div class="wp-gpt-rag-chat-messages" id="wp-gpt-rag-chat-messages">
                    <div class="wp-gpt-rag-chat-message wp-gpt-rag-chat-message-system">
                        <div class="wp-gpt-rag-chat-message-content">
                            مرحباً! يمكنني مساعدتك في إيجاد المعلومات من هذا الموقع. كيف يمكنني مساعدتك؟
                        </div>
                    </div>
                </div>
                
                <div class="wp-gpt-rag-chat-input-container">
                    <div class="wp-gpt-rag-chat-input-wrapper">
                        <input 
                            type="text"
                            id="wp-gpt-rag-chat-input" 
                            placeholder="اكتب سؤالك هنا..."
                        />
                        <button type="button" id="wp-gpt-rag-chat-send" class="wp-gpt-rag-chat-send-button" aria-label="إرسال">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            
                <div class="wp-gpt-rag-chat-footer">
                    <small>
                        مدعوم بالذكاء الاصطناعي. الردود بناءً على محتوى الموقع.
                    </small>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Check if chat should be displayed based on visibility settings
     */
    private function should_display_chat() {
        $settings = $this->settings;
        $chat_visibility = $settings['chat_visibility'] ?? 'everyone';
        $is_user_logged_in = is_user_logged_in();
        
        switch ($chat_visibility) {
            case 'logged_in_only':
                // Show only to logged-in users
                return $is_user_logged_in;
                
            case 'visitors_only':
                // Show only to non-logged-in users (visitors)
                return !$is_user_logged_in;
                
            case 'everyone':
            default:
                // Show to everyone
                return true;
        }
    }
    
    /**
     * Render floating chat widget in footer (appears on all pages)
     */
    public function render_floating_chat_widget() {
        // Don't show in admin area
        if (is_admin()) {
            return;
        }
        
        // Output the chat widget
        echo $this->get_chat_widget_html();
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

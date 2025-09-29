<?php

namespace WP_GPT_RAG_Chat;

/**
 * Main plugin class
 */
class Plugin {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        // Load dependencies only when needed
        add_action('init', [$this, 'load_dependencies'], 1);
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_wp_gpt_rag_chat_query', [$this, 'handle_chat_query']);
        add_action('wp_ajax_nopriv_wp_gpt_rag_chat_query', [$this, 'handle_chat_query']);
        add_action('wp_ajax_wp_gpt_rag_chat_bulk_action', [$this, 'handle_bulk_action']);
        add_action('wp_ajax_wp_gpt_rag_chat_reindex', [$this, 'handle_reindex']);
        add_action('wp_ajax_wp_gpt_rag_chat_toggle_include', [$this, 'handle_toggle_include']);
        add_action('wp_ajax_wp_gpt_rag_chat_test_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_wp_gpt_rag_chat_test_chunking', [$this, 'handle_test_chunking']);
        add_action('wp_ajax_wp_gpt_rag_chat_bulk_index', [$this, 'handle_bulk_index']);
        add_action('wp_ajax_wp_gpt_rag_chat_clear_vectors', [$this, 'handle_clear_vectors']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_post_status', [$this, 'handle_get_post_status']);
        add_action('wp_ajax_wp_gpt_rag_chat_cleanup_logs', [$this, 'handle_cleanup_logs']);
        
        // WP-Cron hooks
        add_action('wp_gpt_rag_chat_index_content', [$this, 'cron_index_content']);
        add_action('wp_gpt_rag_chat_cleanup_logs', [$this, 'cron_cleanup_logs']);
        
        // Post hooks
        add_action('save_post', [$this, 'handle_post_save'], 10, 2);
        add_action('delete_post', [$this, 'handle_post_delete']);
        
        // Admin notices
        add_action('admin_notices', [$this, 'admin_notices']);
    }
    
    /**
     * Load plugin dependencies
     */
    public function load_dependencies() {
        $files = [
            'includes/class-admin.php',
            'includes/class-settings.php',
            'includes/class-metabox.php',
            'includes/class-chunking.php',
            'includes/class-openai.php',
            'includes/class-pinecone.php',
            'includes/class-indexing.php',
            'includes/class-chat.php',
            'includes/class-privacy.php',
            'includes/class-logger.php'
        ];
        
        foreach ($files as $file) {
            $file_path = WP_GPT_RAG_CHAT_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize components
        new Admin();
        new Metabox();
        new Chat();
        new Privacy();
        
        // Initialize settings for admin
        if (is_admin()) {
            new Settings();
        }
        
        // Initialize chat widget
        $this->init_chat_widget();
    }
    
    /**
     * Initialize chat widget
     */
    private function init_chat_widget() {
        $chat = new Chat();
        $privacy = new Privacy();
        
        // Add chat widget to content
        add_filter('the_content', [$chat, 'add_chat_widget_to_content']);
        
        // Register shortcode
        $chat->register_shortcode();
        
        // Add privacy policy content
        add_filter('the_content', [$privacy, 'add_privacy_policy_content']);
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        add_menu_page(
            __('GPT RAG Chat', 'wp-gpt-rag-chat'),
            __('GPT RAG Chat', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat',
            [$this, 'admin_page'],
            'dashicons-format-chat',
            30
        );
        
        add_submenu_page(
            'wp-gpt-rag-chat',
            __('Settings', 'wp-gpt-rag-chat'),
            __('Settings', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-settings',
            [$this, 'settings_page']
        );
        
        add_submenu_page(
            'wp-gpt-rag-chat',
            __('Indexing', 'wp-gpt-rag-chat'),
            __('Indexing', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-indexing',
            [$this, 'indexing_page']
        );
        
        add_submenu_page(
            'wp-gpt-rag-chat',
            __('Logs', 'wp-gpt-rag-chat'),
            __('Logs', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-logs',
            [$this, 'logs_page']
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Settings page callback
     */
    public function settings_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    /**
     * Indexing page callback
     */
    public function indexing_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/indexing-page.php';
    }
    
    /**
     * Logs page callback
     */
    public function logs_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/logs-page.php';
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'wp-gpt-rag-chat-frontend',
            WP_GPT_RAG_CHAT_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            WP_GPT_RAG_CHAT_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wp-gpt-rag-chat-frontend',
            WP_GPT_RAG_CHAT_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            WP_GPT_RAG_CHAT_VERSION
        );
        
        wp_localize_script('wp-gpt-rag-chat-frontend', 'wpGptRagChat', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_gpt_rag_chat_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'wp-gpt-rag-chat'),
                'error' => __('An error occurred. Please try again.', 'wp-gpt-rag-chat'),
                'consentRequired' => __('Please accept the privacy policy to continue.', 'wp-gpt-rag-chat'),
            ]
        ]);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wp-gpt-rag-chat') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'wp-gpt-rag-chat-admin',
            WP_GPT_RAG_CHAT_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            WP_GPT_RAG_CHAT_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wp-gpt-rag-chat-admin',
            WP_GPT_RAG_CHAT_PLUGIN_URL . 'assets/css/admin.css',
            [],
            WP_GPT_RAG_CHAT_VERSION
        );
        
        wp_localize_script('wp-gpt-rag-chat-admin', 'wpGptRagChatAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_gpt_rag_chat_admin_nonce'),
            'strings' => [
                'confirmBulkAction' => __('Are you sure you want to perform this action?', 'wp-gpt-rag-chat'),
                'processing' => __('Processing...', 'wp-gpt-rag-chat'),
                'success' => __('Action completed successfully.', 'wp-gpt-rag-chat'),
                'error' => __('An error occurred. Please try again.', 'wp-gpt-rag-chat'),
            ]
        ]);
    }
    
    /**
     * Handle chat query AJAX request
     */
    public function handle_chat_query() {
        check_ajax_referer('wp_gpt_rag_chat_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        $consent = isset($_POST['consent']) ? (bool) $_POST['consent'] : false;
        
        if (empty($query)) {
            wp_send_json_error(['message' => __('Query is required.', 'wp-gpt-rag-chat')]);
        }
        
        if (!$consent) {
            wp_send_json_error(['message' => __('Privacy consent is required.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $chat = new Chat();
            $response = $chat->process_query($query);
            
            // Log the interaction
            $logger = new Logger();
            $logger->log_interaction($query, $response, get_current_user_id());
            
            wp_send_json_success(['response' => $response]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle bulk action AJAX request
     */
    public function handle_bulk_action() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        $post_ids = array_map('intval', $_POST['post_ids'] ?? []);
        
        if (empty($action) || empty($post_ids)) {
            wp_send_json_error(['message' => __('Invalid parameters.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $indexing = new Indexing();
            $result = $indexing->bulk_action($action, $post_ids);
            
            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle reindex AJAX request
     */
    public function handle_reindex() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $indexing = new Indexing();
            $result = $indexing->reindex_post($post_id);
            
            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle toggle include AJAX request
     */
    public function handle_toggle_include() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $include = isset($_POST['include']) ? (bool) $_POST['include'] : false;
        
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'wp-gpt-rag-chat')]);
        }
        
        update_post_meta($post_id, '_wp_gpt_rag_chat_include', $include);
        
        wp_send_json_success(['include' => $include]);
    }
    
    /**
     * Handle post save
     */
    public function handle_post_save($post_id, $post) {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        if (!in_array($post->post_status, ['publish', 'private'])) {
            return;
        }
        
        $include = get_post_meta($post_id, '_wp_gpt_rag_chat_include', true);
        if ($include === '') {
            // Default to include for new posts
            update_post_meta($post_id, '_wp_gpt_rag_chat_include', true);
            $include = true;
        }
        
        if ($include) {
            // Schedule indexing
            wp_schedule_single_event(time() + 30, 'wp_gpt_rag_chat_index_content', [$post_id]);
        }
    }
    
    /**
     * Handle post delete
     */
    public function handle_post_delete($post_id) {
        try {
            $indexing = new Indexing();
            $indexing->delete_post_vectors($post_id);
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error deleting vectors for post ' . $post_id . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Handle test connection AJAX request
     */
    public function handle_test_connection() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $openai = new OpenAI();
            $pinecone = new Pinecone();
            
            $openai_result = $openai->test_connection();
            $pinecone_result = $pinecone->test_connection();
            
            if ($openai_result['success'] && $pinecone_result['success']) {
                $message = __('Both OpenAI and Pinecone connections are working correctly.', 'wp-gpt-rag-chat');
                wp_send_json_success(['message' => $message]);
            } else {
                $errors = [];
                if (!$openai_result['success']) {
                    $errors[] = 'OpenAI: ' . $openai_result['message'];
                }
                if (!$pinecone_result['success']) {
                    $errors[] = 'Pinecone: ' . $pinecone_result['message'];
                }
                wp_send_json_error(['message' => implode('; ', $errors)]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle test chunking AJAX request
     */
    public function handle_test_chunking() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $chunking = new Chunking();
            $result = $chunking->test_chunking();
            
            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle bulk index AJAX request
     */
    public function handle_bulk_index() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $offset = intval($_POST['offset'] ?? 0);
        
        try {
            $indexing = new Indexing();
            
            switch ($action) {
                case 'index_all':
                    $result = $indexing->index_all_content(10, $offset);
                    break;
                case 'reindex_changed':
                    $result = $indexing->reindex_changed_content(10, $offset);
                    break;
                default:
                    wp_send_json_error(['message' => __('Invalid action.', 'wp-gpt-rag-chat')]);
            }
            
            $total_posts = wp_count_posts();
            $total_posts = $total_posts->publish + $total_posts->private;
            
            wp_send_json_success([
                'processed' => $result['processed'],
                'total' => $total_posts,
                'completed' => ($offset + 10) >= $total_posts,
                'errors' => $result['errors']
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle clear vectors AJAX request
     */
    public function handle_clear_vectors() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $indexing = new Indexing();
            $deleted = $indexing->clear_all_vectors();
            
            wp_send_json_success(['message' => sprintf(__('Cleared %d vectors.', 'wp-gpt-rag-chat'), $deleted)]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle get post status AJAX request
     */
    public function handle_get_post_status() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'wp-gpt-rag-chat')]);
        }
        
        $status = Admin::get_post_indexing_status($post_id);
        
        wp_send_json_success($status);
    }
    
    /**
     * Handle cleanup logs AJAX request
     */
    public function handle_cleanup_logs() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $logger = new Logger();
            $deleted = $logger->cleanup_old_logs();
            
            wp_send_json_success(['message' => sprintf(__('Cleaned up %d log entries.', 'wp-gpt-rag-chat'), $deleted)]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Cron job to index content
     */
    public function cron_index_content($post_id) {
        try {
            $indexing = new Indexing();
            $indexing->index_post($post_id);
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error indexing post ' . $post_id . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Cron job to cleanup logs
     */
    public function cron_cleanup_logs() {
        try {
            $logger = new Logger();
            $logger->cleanup_old_logs();
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error cleaning up logs: ' . $e->getMessage());
        }
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        $settings = get_option('wp_gpt_rag_chat_settings', []);
        
        if (empty($settings['openai_api_key']) || empty($settings['pinecone_api_key']) || empty($settings['pinecone_host'])) {
            echo '<div class="notice notice-warning"><p>';
            echo sprintf(
                __('WP GPT RAG Chat needs to be configured. <a href="%s">Go to settings</a>.', 'wp-gpt-rag-chat'),
                admin_url('admin.php?page=wp-gpt-rag-chat-settings')
            );
            echo '</p></div>';
        }
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        try {
            // Create database tables
            self::create_tables();
            
            // Schedule cron events
            if (function_exists('wp_next_scheduled') && !wp_next_scheduled('wp_gpt_rag_chat_cleanup_logs')) {
                wp_schedule_event(time(), 'daily', 'wp_gpt_rag_chat_cleanup_logs');
            }
            
            // Set default options
            $default_settings = [
                'openai_api_key' => '',
                'pinecone_api_key' => '',
                'pinecone_host' => '',
                'pinecone_index_name' => '',
                'embedding_model' => 'text-embedding-3-large',
                'chunk_size' => 1400,
                'chunk_overlap' => 150,
                'top_k' => 5,
                'similarity_threshold' => 0.7,
                'gpt_model' => 'gpt-4',
                'max_tokens' => 1000,
                'temperature' => 0.7,
                'log_retention_days' => 30,
                'anonymize_ips' => false,
                'require_consent' => true,
            ];
            
            if (function_exists('add_option')) {
                add_option('wp_gpt_rag_chat_settings', $default_settings);
            }
            
            // Migrate old pinecone_environment to pinecone_host format
            self::migrate_pinecone_settings();
            
            // Flush rewrite rules
            if (function_exists('flush_rewrite_rules')) {
                flush_rewrite_rules();
            }
            
        } catch (\Exception $e) {
            // Log error but don't prevent activation
            error_log('WP GPT RAG Chat activation error: ' . $e->getMessage());
        }
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        try {
            // Clear scheduled events
            if (function_exists('wp_clear_scheduled_hook')) {
                wp_clear_scheduled_hook('wp_gpt_rag_chat_index_content');
                wp_clear_scheduled_hook('wp_gpt_rag_chat_cleanup_logs');
            }
            
            // Flush rewrite rules
            if (function_exists('flush_rewrite_rules')) {
                flush_rewrite_rules();
            }
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat deactivation error: ' . $e->getMessage());
        }
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        try {
            // Remove database tables
            global $wpdb;
            
            if (isset($wpdb) && method_exists($wpdb, 'query')) {
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wp_gpt_rag_chat_logs");
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wp_gpt_rag_chat_vectors");
                
                // Remove post meta
                $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wp_gpt_rag_chat_%'");
            }
            
            // Remove options
            if (function_exists('delete_option')) {
                delete_option('wp_gpt_rag_chat_settings');
                delete_option('wp_gpt_rag_chat_version');
            }
            
            // Clear scheduled events
            if (function_exists('wp_clear_scheduled_hook')) {
                wp_clear_scheduled_hook('wp_gpt_rag_chat_index_content');
                wp_clear_scheduled_hook('wp_gpt_rag_chat_cleanup_logs');
            }
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat uninstall error: ' . $e->getMessage());
        }
    }
    
    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;
        
        if (!isset($wpdb) || !method_exists($wpdb, 'get_charset_collate')) {
            return;
        }
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Logs table
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        $logs_sql = "CREATE TABLE $logs_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            query text NOT NULL,
            response text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Vectors table
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        $vectors_sql = "CREATE TABLE $vectors_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            chunk_index int(11) NOT NULL,
            content_hash varchar(64) NOT NULL,
            vector_id varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY vector_id (vector_id),
            KEY post_id (post_id),
            KEY content_hash (content_hash)
        ) $charset_collate;";
        
        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            if (function_exists('dbDelta')) {
                dbDelta($logs_sql);
                dbDelta($vectors_sql);
            }
        }
    }
    
    /**
     * Migrate old Pinecone environment setting to new host URL format
     */
    private static function migrate_pinecone_settings() {
        try {
            $settings = get_option('wp_gpt_rag_chat_settings', []);
            
            // If we have old environment setting but no host URL
            if (!empty($settings['pinecone_environment']) && empty($settings['pinecone_host']) && !empty($settings['pinecone_index_name'])) {
                // Convert old format to new host URL
                $host_url = 'https://' . $settings['pinecone_index_name'] . '-' . $settings['pinecone_environment'] . '.svc.pinecone.io';
                $settings['pinecone_host'] = $host_url;
                
                // Remove old environment setting
                unset($settings['pinecone_environment']);
                
                // Update the settings
                if (function_exists('update_option')) {
                    update_option('wp_gpt_rag_chat_settings', $settings);
                }
            }
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat migration error: ' . $e->getMessage());
        }
    }
}

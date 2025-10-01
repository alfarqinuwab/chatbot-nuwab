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
        add_action('wp_ajax_wp_gpt_rag_chat_get_indexed_items', [$this, 'handle_get_indexed_items']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_post_count', [$this, 'handle_get_post_count']);
        add_action('wp_ajax_wp_gpt_rag_chat_remove_from_index', [$this, 'handle_remove_from_index']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_stats', [$this, 'handle_get_stats']);
        add_action('wp_ajax_wp_gpt_rag_chat_import_csv', [$this, 'handle_import_csv']);
        add_action('wp_ajax_wp_gpt_rag_chat_import_pdf', [$this, 'handle_import_pdf']);
        add_action('wp_ajax_wp_gpt_rag_chat_extract_pdf_text', [$this, 'handle_extract_pdf_text']);
        add_action('wp_ajax_wp_gpt_rag_chat_generate_chunk_title', [$this, 'handle_generate_chunk_title']);
        add_action('wp_ajax_wp_gpt_rag_chat_create_chunk_embedding', [$this, 'handle_create_chunk_embedding']);
        
        // WP-Cron hooks
        add_action('wp_gpt_rag_chat_index_content', [$this, 'cron_index_content']);
        add_action('wp_gpt_rag_chat_cleanup_logs', [$this, 'cron_cleanup_logs']);
        
        // Post hooks
        add_action('save_post', [$this, 'handle_post_save'], 10, 2);
        add_action('delete_post', [$this, 'handle_post_delete']);
        
        // Register custom post type for imports
        add_action('init', [$this, 'register_import_post_type']);
        
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
        // Main GPT RAG Chat Menu
        add_menu_page(
            __('GPT RAG Chat Dashboard', 'wp-gpt-rag-chat'),
            __('GPT RAG Chat', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-dashboard',
            [$this, 'dashboard_page'],
            'dashicons-format-chat',
            30
        );
        
        // Dashboard submenu (same as main page)
        add_submenu_page(
            'wp-gpt-rag-chat-dashboard',
            __('Dashboard', 'wp-gpt-rag-chat'),
            __('Dashboard', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-dashboard',
            [$this, 'dashboard_page']
        );
        
        // Settings submenu
        add_submenu_page(
            'wp-gpt-rag-chat-dashboard',
            __('Settings', 'wp-gpt-rag-chat'),
            __('Settings', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-settings',
            [$this, 'settings_page']
        );
        
        // Indexing submenu
        add_submenu_page(
            'wp-gpt-rag-chat-dashboard',
            __('Indexing', 'wp-gpt-rag-chat'),
            __('Indexing', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-indexing',
            [$this, 'indexing_page']
        );
        
        // Analytics Overview submenu
        add_submenu_page(
            'wp-gpt-rag-chat-dashboard',
            __('Analytics Overview', 'wp-gpt-rag-chat'),
            __('Analytics Overview', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-analytics',
            [$this, 'analytics_page']
        );
        
        // Chat Logs submenu
        add_submenu_page(
            'wp-gpt-rag-chat-dashboard',
            __('Chat Logs', 'wp-gpt-rag-chat'),
            __('Chat Logs', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-logs',
            [$this, 'logs_page']
        );
        
        // User Analytics submenu
        add_submenu_page(
            'wp-gpt-rag-chat-dashboard',
            __('User Analytics', 'wp-gpt-rag-chat'),
            __('User Analytics', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-user-analytics',
            [$this, 'user_analytics_page']
        );
        
        // Export Data submenu
        add_submenu_page(
            'wp-gpt-rag-chat-dashboard',
            __('Export Data', 'wp-gpt-rag-chat'),
            __('Export Data', 'wp-gpt-rag-chat'),
            'manage_options',
            'wp-gpt-rag-chat-export',
            [$this, 'export_page']
        );
    }
    
    /**
     * Dashboard page callback
     */
    public function dashboard_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/dashboard-page.php';
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
     * Analytics page callback
     */
    public function analytics_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/analytics-page.php';
    }
    
    /**
     * Logs page callback
     */
    public function logs_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/logs-page.php';
    }
    
    /**
     * User Analytics page callback
     */
    public function user_analytics_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/user-analytics-page.php';
    }
    
    /**
     * Export page callback
     */
    public function export_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/export-page.php';
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
        // Enqueue for plugin pages
        if (strpos($hook, 'wp-gpt-rag-chat') !== false) {
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
            
            // Enqueue Chart.js for analytics pages
            if (strpos($hook, 'analytics') !== false || strpos($hook, 'logs') !== false) {
                wp_enqueue_script(
                    'chart-js',
                    'https://cdn.jsdelivr.net/npm/chart.js',
                    [],
                    '3.9.1',
                    true
                );
            }
        }
        
        // Enqueue enhanced settings CSS for WordPress core settings pages
        if (strpos($hook, 'options-') !== false || strpos($hook, 'settings') !== false) {
            wp_enqueue_style(
                'wp-gpt-rag-chat-admin-settings',
                WP_GPT_RAG_CHAT_PLUGIN_URL . 'assets/css/admin-settings.css',
                [],
                WP_GPT_RAG_CHAT_VERSION
            );
        }
        
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
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        
        try {
            $indexing = new Indexing();
            
            switch ($action) {
                case 'index_all':
                    $result = $indexing->index_all_content(10, $offset, $post_type);
                    break;
                case 'index_single':
                    $result = $indexing->index_single_post($post_type);
                    break;
                case 'reindex_changed':
                    $result = $indexing->reindex_changed_content(10, $offset, $post_type);
                    break;
                default:
                    wp_send_json_error(['message' => __('Invalid action.', 'wp-gpt-rag-chat')]);
            }
            
            // Calculate total posts based on selected post type and action
            if ($action === 'index_single') {
                $total_posts = 1; // Single post action always processes 1 post
            } else {
                if ($post_type && $post_type !== 'all') {
                    $post_counts = wp_count_posts($post_type);
                    $total_posts = $post_counts->publish + $post_counts->private;
                } else {
                    $total_posts = wp_count_posts();
                    $total_posts = $total_posts->publish + $total_posts->private;
                }
            }
            
            // Get newly indexed items for real-time table updates
            $newly_indexed = [];
            if (!empty($result['indexed_post_ids'])) {
                foreach ($result['indexed_post_ids'] as $post_id) {
                    $post = get_post($post_id);
                    if ($post) {
                        $newly_indexed[] = [
                            'id' => $post_id,
                            'title' => $post->post_title,
                            'type' => $post->post_type,
                            'edit_url' => get_edit_post_link($post_id)
                        ];
                    }
                }
            }
            
            wp_send_json_success([
                'processed' => $result['processed'],
                'total' => $total_posts,
                'completed' => ($action === 'index_single') ? true : (($offset + 10) >= $total_posts),
                'errors' => $result['errors'],
                'newly_indexed' => $newly_indexed
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
        // Don't show notice on settings page since we have a custom one there
        if (isset($_GET['page']) && strpos($_GET['page'], 'wp-gpt-rag-chat') !== false) {
            return;
        }
        
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
    
    /**
     * Handle get indexed items AJAX request
     */
    public function handle_get_indexed_items() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to access this data.', 'wp-gpt-rag-chat')]);
        }
        
        global $wpdb;
        
        // Get all public post types that can be indexed
        $indexable_post_types = get_post_types(['public' => true], 'names');
        $post_type_placeholders = implode(',', array_fill(0, count($indexable_post_types), '%s'));
        
        // Get posts that are in the index queue
        $indexed_posts = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT p.ID, p.post_title, p.post_type, p.post_modified
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_status = 'publish'
            AND p.post_type IN ($post_type_placeholders)
            AND pm.meta_key = '_wp_gpt_rag_chat_indexed'
            AND pm.meta_value = '1'
            ORDER BY p.post_modified DESC
            LIMIT 50
        ", $indexable_post_types));
        
        $items = [];
        foreach ($indexed_posts as $post_data) {
            $status = WP_GPT_RAG_Chat\Admin::get_post_indexing_status($post_data->ID);
            $post_modified = get_post_modified_time('Y/m/d H:i:s', false, $post_data);
            $indexed_time = $status['last_updated'] ? date('Y/m/d H:i:s', strtotime($status['last_updated'])) : null;
            
            $items[] = [
                'id' => $post_data->ID,
                'title' => $post_data->post_title,
                'type' => $post_data->post_type,
                'modified' => $post_modified,
                'indexed_time' => $indexed_time,
                'edit_url' => get_edit_post_link($post_data->ID),
                'vector_count' => $status['vector_count'],
                'status' => $status
            ];
        }
        
        wp_send_json_success(['items' => $items]);
    }
    
    /**
     * Handle get post count AJAX request
     */
    public function handle_get_post_count() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to access this data.', 'wp-gpt-rag-chat')]);
        }
        
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        
        // Count posts that are marked for inclusion
        $args = [
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ]
            ],
            'fields' => 'ids',
            'posts_per_page' => -1
        ];
        
        // Add post type filter if specified
        if ($post_type && $post_type !== 'all') {
            $args['post_type'] = $post_type;
        } else {
            $args['post_type'] = get_post_types(['public' => true]);
        }
        
        $posts = get_posts($args);
        $count = count($posts);
        
        wp_send_json_success(['count' => $count]);
    }
    
    /**
     * Handle remove from index AJAX request
     */
    public function handle_remove_from_index() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $indexing = new Indexing();
            $deleted_count = $indexing->delete_post_vectors($post_id);
            
            // Remove the indexed meta
            delete_post_meta($post_id, '_wp_gpt_rag_chat_indexed');
            delete_post_meta($post_id, '_wp_gpt_rag_chat_last_indexed');
            
            wp_send_json_success([
                'message' => sprintf(__('Successfully removed %d vectors from index and Pinecone.', 'wp-gpt-rag-chat'), $deleted_count),
                'deleted_count' => $deleted_count
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle get stats AJAX request
     */
    public function handle_get_stats() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $stats = Admin::get_indexing_stats();
            
            wp_send_json_success([
                'total_vectors' => $stats['total_vectors'],
                'total_posts' => $stats['total_posts'],
                'recent_activity' => $stats['recent_activity'],
                'by_post_type' => $stats['by_post_type']
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle CSV import AJAX request
     */
    public function handle_import_csv() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to import data.', 'wp-gpt-rag-chat')]);
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('No file uploaded or upload error occurred.', 'wp-gpt-rag-chat')]);
        }
        
        $file = $_FILES['file'];
        
        // Validate CSV file
        if ($file['type'] !== 'text/csv' && !str_ends_with(strtolower($file['name']), '.csv')) {
            wp_send_json_error(['message' => __('Please upload a valid CSV file.', 'wp-gpt-rag-chat')]);
        }
        
        $title_column = intval($_POST['title_column'] ?? -1);
        $content_column = intval($_POST['content_column'] ?? -1);
        
        if ($title_column < 0 || $content_column < 0) {
            wp_send_json_error(['message' => __('Please select title and content columns for CSV import.', 'wp-gpt-rag-chat')]);
        }
        
        $upload_dir = wp_upload_dir();
        $import_dir = $upload_dir['basedir'] . '/wp-gpt-rag-chat-imports/';
        
        // Create import directory if it doesn't exist
        if (!file_exists($import_dir)) {
            wp_mkdir_p($import_dir);
        }
        
        // Generate unique filename
        $filename = sanitize_file_name($file['name']);
        $filename = time() . '_' . $filename;
        $file_path = $import_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_send_json_error(['message' => __('Failed to save uploaded file.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $imported_count = $this->import_csv_data($file_path, $title_column, $content_column);
            
            // Clean up uploaded file
            unlink($file_path);
            
            wp_send_json_success([
                'imported' => $imported_count,
                'message' => sprintf(__('%d items imported successfully.', 'wp-gpt-rag-chat'), $imported_count)
            ]);
            
        } catch (\Exception $e) {
            // Clean up uploaded file on error
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle PDF import AJAX request
     */
    public function handle_import_pdf() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to import data.', 'wp-gpt-rag-chat')]);
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('No file uploaded or upload error occurred.', 'wp-gpt-rag-chat')]);
        }
        
        $file = $_FILES['file'];
        
        // Validate PDF file
        if ($file['type'] !== 'application/pdf' && !str_ends_with(strtolower($file['name']), '.pdf')) {
            wp_send_json_error(['message' => __('Please upload a valid PDF file.', 'wp-gpt-rag-chat')]);
        }
        
        $extract_images = sanitize_text_field($_POST['extract_images'] ?? '0') === '1';
        $preserve_formatting = sanitize_text_field($_POST['preserve_formatting'] ?? '0') === '1';
        $extracted_sections = json_decode(stripslashes($_POST['extracted_sections'] ?? '[]'), true);
        
        $upload_dir = wp_upload_dir();
        $import_dir = $upload_dir['basedir'] . '/wp-gpt-rag-chat-imports/';
        
        // Create import directory if it doesn't exist
        if (!file_exists($import_dir)) {
            wp_mkdir_p($import_dir);
        }
        
        // Generate unique filename
        $filename = sanitize_file_name($file['name']);
        $filename = time() . '_' . $filename;
        $file_path = $import_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_send_json_error(['message' => __('Failed to save uploaded file.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $imported_count = $this->import_pdf_data($file_path, $extract_images, $preserve_formatting, '', $extracted_sections);
            
            // Clean up uploaded file
            unlink($file_path);
            
            wp_send_json_success([
                'imported' => $imported_count,
                'message' => sprintf(__('%d items imported successfully.', 'wp-gpt-rag-chat'), $imported_count)
            ]);
            
        } catch (\Exception $e) {
            // Clean up uploaded file on error
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle PDF text extraction for preview
     */
    public function handle_extract_pdf_text() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to extract PDF text.', 'wp-gpt-rag-chat')]);
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('No file uploaded or upload error occurred.', 'wp-gpt-rag-chat')]);
        }
        
        $file = $_FILES['file'];
        
        // Validate PDF file
        if ($file['type'] !== 'application/pdf' && !str_ends_with(strtolower($file['name']), '.pdf')) {
            wp_send_json_error(['message' => __('Please upload a valid PDF file.', 'wp-gpt-rag-chat')]);
        }
        
        $upload_dir = wp_upload_dir();
        $import_dir = $upload_dir['basedir'] . '/wp-gpt-rag-chat-imports/';
        
        // Create import directory if it doesn't exist
        if (!file_exists($import_dir)) {
            wp_mkdir_p($import_dir);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = 'pdf_preview_' . uniqid() . '.' . $file_extension;
        $file_path = $import_dir . $unique_filename;
        
        try {
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                throw new \Exception(__('Failed to save uploaded file.', 'wp-gpt-rag-chat'));
            }
            
            // Extract text from PDF
            $sections = $this->extract_pdf_text($file_path, true);
            
            // Clean up uploaded file
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Format sections for preview
            $preview_text = '';
            foreach ($sections as $section) {
                $preview_text .= "=== " . $section['title'] . " (Page " . $section['page'] . ") ===\n\n";
                $preview_text .= $section['content'] . "\n\n";
            }
            
            wp_send_json_success([
                'text' => $preview_text,
                'sections' => $sections,
                'section_count' => count($sections)
            ]);
            
        } catch (\Exception $e) {
            // Clean up uploaded file on error
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle chunk title generation using OpenAI
     */
    public function handle_generate_chunk_title() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to generate titles.', 'wp-gpt-rag-chat')]);
        }
        
        $chunk_content = sanitize_textarea_field($_POST['chunk_content'] ?? '');
        $chunk_index = intval($_POST['chunk_index'] ?? 0);
        
        if (empty($chunk_content)) {
            wp_send_json_error(['message' => __('No chunk content provided.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            // Get OpenAI settings
            $settings = \WP_GPT_RAG_Chat\Settings::get_settings();
            $api_key = $settings['openai_api_key'] ?? '';
            
            if (empty($api_key)) {
                wp_send_json_error(['message' => __('OpenAI API key not configured.', 'wp-gpt-rag-chat')]);
            }
            
            // Prepare the prompt for title generation
            $prompt = "Generate a concise, descriptive title for the following text content. The title should capture the main topic or key information:\n\n" . $chunk_content;
            
            // Make API call to OpenAI
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'model' => $settings['openai_model'] ?? 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 50,
                    'temperature' => 0.7,
                ]),
                'timeout' => 30,
            ]);
            
            if (is_wp_error($response)) {
                throw new \Exception('OpenAI API request failed: ' . $response->get_error_message());
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['error'])) {
                throw new \Exception('OpenAI API error: ' . $data['error']['message']);
            }
            
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response from OpenAI API');
            }
            
            $generated_title = trim($data['choices'][0]['message']['content']);
            
            // Clean up the title (remove quotes if present)
            $generated_title = trim($generated_title, '"\'');
            
            wp_send_json_success([
                'title' => $generated_title,
                'chunk_index' => $chunk_index
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle chunk embedding creation and storage in Pinecone
     */
    public function handle_create_chunk_embedding() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to create embeddings.', 'wp-gpt-rag-chat')]);
        }
        
        $chunk_title = sanitize_text_field($_POST['chunk_title'] ?? '');
        $chunk_content = sanitize_textarea_field($_POST['chunk_content'] ?? '');
        $chunk_index = intval($_POST['chunk_index'] ?? 0);
        
        if (empty($chunk_title) || empty($chunk_content)) {
            wp_send_json_error(['message' => __('Chunk title and content are required.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            // Get settings
            $settings = \WP_GPT_RAG_Chat\Settings::get_settings();
            $api_key = $settings['openai_api_key'] ?? '';
            
            if (empty($api_key)) {
                wp_send_json_error(['message' => __('OpenAI API key not configured.', 'wp-gpt-rag-chat')]);
            }
            
            // Create embedding using OpenAI
            $embedding = $this->create_embedding($chunk_content, $api_key, $settings);
            
            if (empty($embedding)) {
                throw new \Exception('Failed to create embedding');
            }
            
            // Store in Pinecone
            $pinecone_result = $this->store_embedding_in_pinecone($embedding, $chunk_title, $chunk_content, $settings);
            
            if (!$pinecone_result) {
                throw new \Exception('Failed to store embedding in Pinecone');
            }
            
            // Create a post record for tracking
            $post_id = wp_insert_post([
                'post_title' => $chunk_title,
                'post_content' => $chunk_content,
                'post_status' => 'publish',
                'post_type' => 'wp_gpt_rag_import',
                'meta_input' => [
                    '_wp_gpt_rag_chat_include' => '1',
                    '_wp_gpt_rag_chat_imported' => '1',
                    '_wp_gpt_rag_chat_import_type' => 'pdf_chunk',
                    '_wp_gpt_rag_chat_pinecone_id' => $pinecone_result['id'] ?? '',
                    '_wp_gpt_rag_chat_embedding_model' => $settings['embedding_model'] ?? 'text-embedding-3-small',
                    '_wp_gpt_rag_chat_chunk_index' => $chunk_index
                ]
            ]);
            
            wp_send_json_success([
                'message' => 'Embedding created and stored successfully',
                'post_id' => $post_id,
                'chunk_index' => $chunk_index
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Create embedding using OpenAI API
     */
    private function create_embedding($text, $api_key, $settings) {
        // Handle embedding dimensions dropdown selection (PRIORITY)
        $embedding_dimensions_setting = $settings['embedding_dimensions'] ?? $settings['pinecone_dimensions'] ?? '512';
        
        // Map dropdown values to model and dimensions
        $model_mapping = [
            '512' => ['model' => 'text-embedding-3-small', 'dimensions' => 512],
            '1536' => ['model' => 'text-embedding-3-small', 'dimensions' => 1536],
            '3072' => ['model' => 'text-embedding-3-large', 'dimensions' => 3072],
            '1536_ada' => ['model' => 'text-embedding-ada-002', 'dimensions' => 1536],
        ];
        
        // Prepare embedding request with dropdown selection taking priority
        if (isset($model_mapping[$embedding_dimensions_setting])) {
            $selected_config = $model_mapping[$embedding_dimensions_setting];
            $embedding_request = [
                'model' => $selected_config['model'],
                'input' => $text,
                'dimensions' => $selected_config['dimensions'],
            ];
        } else {
            // Fallback to original logic if dropdown setting is not found
            $embedding_request = [
                'model' => $settings['embedding_model'] ?? 'text-embedding-3-small',
                'input' => $text,
            ];
            
            $model = $settings['embedding_model'] ?? 'text-embedding-3-small';
            if (in_array($model, ['text-embedding-3-small', 'text-embedding-3-large'])) {
                $embedding_request['dimensions'] = intval($embedding_dimensions_setting);
            }
        }
        
        // Debug logging
        error_log('OpenAI Embedding Debug - Selected Setting: ' . $embedding_dimensions_setting);
        error_log('OpenAI Embedding Debug - Model: ' . $embedding_request['model']);
        error_log('OpenAI Embedding Debug - Dimensions: ' . ($embedding_request['dimensions'] ?? 'default'));
        error_log('OpenAI Embedding Debug - Request: ' . json_encode($embedding_request));
        
        $response = wp_remote_post('https://api.openai.com/v1/embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($embedding_request),
            'timeout' => 30,
        ]);
        
        if (is_wp_error($response)) {
            throw new \Exception('OpenAI embedding API request failed: ' . $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Debug logging for response
        error_log('OpenAI Embedding Debug - Response Code: ' . wp_remote_retrieve_response_code($response));
        error_log('OpenAI Embedding Debug - Response Body: ' . $body);
        
        if (isset($data['error'])) {
            throw new \Exception('OpenAI embedding API error: ' . $data['error']['message']);
        }
        
        if (!isset($data['data'][0]['embedding'])) {
            throw new \Exception('Invalid response from OpenAI embedding API');
        }
        
        $embedding = $data['data'][0]['embedding'];
        error_log('OpenAI Embedding Debug - Embedding Dimensions: ' . count($embedding));
        
        return $embedding;
    }
    
    /**
     * Store embedding in Pinecone using the existing Pinecone class
     */
    private function store_embedding_in_pinecone($embedding, $title, $content, $settings) {
        // Use the existing Pinecone class
        $pinecone = new \WP_GPT_RAG_Chat\Pinecone();
        
        // Generate unique ID for the vector
        $vector_id = 'pdf_chunk_' . uniqid() . '_' . time();
        
        // Prepare metadata
        $metadata = [
            'title' => $title,
            'content' => $content,
            'type' => 'pdf_chunk',
            'created_at' => current_time('mysql'),
            'source' => 'pdf_import'
        ];
        
        // Prepare the vector for upsert
        $vector = [
            'id' => $vector_id,
            'values' => $embedding,
            'metadata' => $metadata
        ];
        
        // Debug logging
        error_log('Pinecone Debug - Using existing Pinecone class');
        error_log('Pinecone Debug - Vector ID: ' . $vector_id);
        error_log('Pinecone Debug - Vector Data: ' . json_encode($vector));
        
        try {
            // Use the existing upsert_vectors method
            $result = $pinecone->upsert_vectors([$vector]);
            
            // Debug logging for result
            error_log('Pinecone Debug - Upsert Result: ' . print_r($result, true));
            
            return [
                'id' => $vector_id,
                'upsertedCount' => $result['upsertedCount'] ?? 0
            ];
            
        } catch (\Exception $e) {
            error_log('Pinecone Debug - Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Import CSV data
     */
    private function import_csv_data($file_path, $title_column, $content_column) {
        $imported_count = 0;
        
        if (($handle = fopen($file_path, 'r')) !== FALSE) {
            $headers = fgetcsv($handle); // Skip header row
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) > max($title_column, $content_column)) {
                    $title = trim($data[$title_column] ?? '');
                    $content = trim($data[$content_column] ?? '');
                    
                    if (!empty($title) && !empty($content)) {
                        // Create a custom post for the imported data
                        $post_id = wp_insert_post([
                            'post_title' => $title,
                            'post_content' => $content,
                            'post_status' => 'publish',
                            'post_type' => 'wp_gpt_rag_import',
                            'meta_input' => [
                                '_wp_gpt_rag_chat_include' => '1',
                                '_wp_gpt_rag_chat_imported' => '1',
                                '_wp_gpt_rag_chat_import_type' => 'csv'
                            ]
                        ]);
                        
                        if ($post_id && !is_wp_error($post_id)) {
                            $imported_count++;
                            
                            // Index the post immediately
                            $indexing = new \WP_GPT_RAG_Chat\Indexing();
                            $indexing->index_post($post_id);
                        }
                    }
                }
            }
            fclose($handle);
        }
        
        return $imported_count;
    }
    
    /**
     * Import PDF data
     */
    private function import_pdf_data($file_path, $extract_images = false, $preserve_formatting = true, $extracted_text = '', $extracted_sections = []) {
        $imported_count = 0;
        $filename = basename($file_path);
        $title = 'Imported PDF: ' . pathinfo($filename, PATHINFO_FILENAME);
        
        try {
            // Use provided sections if available, otherwise extract
            if (!empty($extracted_sections) && is_array($extracted_sections)) {
                $sections = $extracted_sections;
            } elseif (!empty($extracted_text)) {
                // If extracted_text is a string, convert to sections format
                if (is_string($extracted_text)) {
                    $sections = [[
                        'title' => 'Document Content',
                        'content' => $extracted_text,
                        'page' => 1,
                        'section_number' => 1
                    ]];
                } else {
                    $sections = $extracted_text;
                }
            } else {
                // Try to extract text using PDFParser if available
                $sections = $this->extract_pdf_text($file_path, $preserve_formatting);
                
                if (empty($sections)) {
                    $sections = [[
                        'title' => 'Document Content',
                        'content' => sprintf(
                            __('PDF file imported: %s. No text could be extracted from this PDF.', 'wp-gpt-rag-chat'),
                            $filename
                        ),
                        'page' => 1,
                        'section_number' => 1
                    ]];
                }
            }
            
            // If no sections provided, create a fallback
            if (empty($sections)) {
                $sections = [[
                    'title' => 'Document Content',
                    'content' => sprintf(
                        __('PDF file imported: %s. No text could be extracted from this PDF.', 'wp-gpt-rag-chat'),
                        $filename
                    ),
                    'page' => 1,
                    'section_number' => 1
                ]];
            }
            
            // Create main post for the PDF
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => sprintf(__('PDF document with %d sections', 'wp-gpt-rag-chat'), count($sections)),
                'post_status' => 'publish',
                'post_type' => 'wp_gpt_rag_import',
                'meta_input' => [
                    '_wp_gpt_rag_chat_include' => '1',
                    '_wp_gpt_rag_chat_imported' => '1',
                    '_wp_gpt_rag_chat_import_type' => 'pdf',
                    '_wp_gpt_rag_chat_import_file' => $filename,
                    '_wp_gpt_rag_chat_extract_images' => $extract_images ? '1' : '0',
                    '_wp_gpt_rag_chat_preserve_formatting' => $preserve_formatting ? '1' : '0',
                    '_wp_gpt_rag_chat_sections' => $sections
                ]
            ]);
            
            // Create individual posts for each section
            foreach ($sections as $section) {
                $section_post_id = wp_insert_post([
                    'post_title' => $section['title'],
                    'post_content' => $section['content'],
                    'post_status' => 'publish',
                    'post_type' => 'wp_gpt_rag_import',
                    'meta_input' => [
                        '_wp_gpt_rag_chat_include' => '1',
                        '_wp_gpt_rag_chat_imported' => '1',
                        '_wp_gpt_rag_chat_import_type' => 'pdf_section',
                        '_wp_gpt_rag_chat_parent_pdf' => $post_id,
                        '_wp_gpt_rag_chat_section_number' => $section['section_number'],
                        '_wp_gpt_rag_chat_page_number' => $section['page'],
                        '_wp_gpt_rag_chat_original_file' => $filename
                    ]
                ]);
                
                $imported_count++;
            }
            
            if ($post_id && !is_wp_error($post_id)) {
                $imported_count++;
                
                // Index the post immediately
                $indexing = new \WP_GPT_RAG_Chat\Indexing();
                $indexing->index_post($post_id);
            }
            
        } catch (\Exception $e) {
            // Fallback: create a post with error message
            $content = sprintf(
                __('PDF file imported: %s. Error extracting text: %s', 'wp-gpt-rag-chat'),
                $filename,
                $e->getMessage()
            );
            
            $post_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'wp_gpt_rag_import',
                'meta_input' => [
                    '_wp_gpt_rag_chat_include' => '1',
                    '_wp_gpt_rag_chat_imported' => '1',
                    '_wp_gpt_rag_chat_import_type' => 'pdf',
                    '_wp_gpt_rag_chat_import_file' => $filename,
                    '_wp_gpt_rag_chat_extraction_error' => $e->getMessage()
                ]
            ]);
            
            if ($post_id && !is_wp_error($post_id)) {
                $imported_count++;
            }
        }
        
        return $imported_count;
    }
    
    /**
     * Extract text from PDF file
     */
    private function extract_pdf_text($file_path, $preserve_formatting = true) {
        // Include PDFParser autoloader if available
        $autoloader_path = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'vendor/pdfparser/autoload.php';
        if (file_exists($autoloader_path)) {
            require_once $autoloader_path;
        }
        
        // Check if PDFParser is available
        if (class_exists('\Smalot\PdfParser\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file_path);
                
                // Extract text by pages for better sectioning
                $pages = $pdf->getPages();
                $sections = [];
                $current_section = '';
                $section_count = 0;
                
                foreach ($pages as $page_num => $page) {
                    $page_text = $page->getText();
                    $page_text = preg_replace('/\s+/', ' ', trim($page_text));
                    
                    if (empty($page_text)) continue;
                    
                    // Check if we should start a new section
                    $should_start_new_section = $this->should_start_new_section($page_text, $current_section);
                    
                    if ($should_start_new_section && !empty($current_section)) {
                        // Save current section
                        $sections[] = [
                            'title' => $this->extract_section_title($current_section),
                            'content' => trim($current_section),
                            'page' => $page_num + 1,
                            'section_number' => ++$section_count
                        ];
                        $current_section = '';
                    }
                    
                    $current_section .= ' ' . $page_text;
                    
                    // If section gets too long, split it
                    if (strlen($current_section) > 2000) {
                        $sections[] = [
                            'title' => $this->extract_section_title($current_section),
                            'content' => trim($current_section),
                            'page' => $page_num + 1,
                            'section_number' => ++$section_count
                        ];
                        $current_section = '';
                    }
                }
                
                // Add the last section if it exists
                if (!empty($current_section)) {
                    $sections[] = [
                        'title' => $this->extract_section_title($current_section),
                        'content' => trim($current_section),
                        'page' => count($pages),
                        'section_number' => ++$section_count
                    ];
                }
                
                // If no sections were created, create one from all text
                if (empty($sections)) {
                    $full_text = $pdf->getText();
                    $full_text = preg_replace('/\s+/', ' ', trim($full_text));
                    $sections[] = [
                        'title' => 'Document Content',
                        'content' => $full_text,
                        'page' => 1,
                        'section_number' => 1
                    ];
                }
                
                return $sections;
            } catch (\Exception $e) {
                throw new \Exception('PDF parsing failed: ' . $e->getMessage());
            }
        } else {
            // Fallback: try to use pdftotext if available
            if (function_exists('shell_exec') && !empty(shell_exec('which pdftotext'))) {
                $output_file = tempnam(sys_get_temp_dir(), 'pdf_text_');
                $command = sprintf('pdftotext "%s" "%s"', escapeshellarg($file_path), escapeshellarg($output_file));
                
                shell_exec($command);
                
                if (file_exists($output_file)) {
                    $text = file_get_contents($output_file);
                    unlink($output_file);
                    
                    if ($preserve_formatting) {
                        $text = preg_replace('/\s+/', ' ', $text);
                        $text = trim($text);
                    } else {
                        $text = preg_replace('/\s+/', ' ', $text);
                        $text = trim($text);
                    }
                    
                    return $text;
                }
            }
            
            throw new \Exception('PDF text extraction not available. Please install PDFParser library or pdftotext utility.');
        }
    }
    
    /**
     * Determine if a new section should start based on content patterns
     */
    private function should_start_new_section($page_text, $current_section) {
        // Check for common section indicators
        $section_indicators = [
            '/^[A-Z][A-Z\s]{10,}$/',  // ALL CAPS headings
            '/^\d+\.\s+[A-Z]/',       // Numbered sections (1. Title)
            '/^[IVX]+\.\s+[A-Z]/',    // Roman numerals (I. Title)
            '/^Chapter\s+\d+/i',      // Chapter headings
            '/^Section\s+\d+/i',      // Section headings
            '/^[A-Z][a-z]+\s+[A-Z]/', // Title case headings
        ];
        
        $first_line = trim(explode("\n", $page_text)[0]);
        
        foreach ($section_indicators as $pattern) {
            if (preg_match($pattern, $first_line)) {
                return true;
            }
        }
        
        // Check if current section is getting too long
        if (strlen($current_section) > 1500) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Extract a meaningful title from section content
     */
    private function extract_section_title($content) {
        $lines = explode("\n", trim($content));
        $first_line = trim($lines[0]);
        
        // If first line looks like a title, use it
        if (strlen($first_line) > 5 && strlen($first_line) < 100) {
            // Clean up the title
            $title = preg_replace('/^\d+\.\s*/', '', $first_line); // Remove numbering
            $title = preg_replace('/^[IVX]+\.\s*/', '', $title);   // Remove roman numerals
            $title = trim($title);
            
            if (!empty($title)) {
                return $title;
            }
        }
        
        // Generate a title from the first few words
        $words = explode(' ', trim($content));
        $title_words = array_slice($words, 0, 6);
        $title = implode(' ', $title_words);
        
        if (strlen($title) > 50) {
            $title = substr($title, 0, 47) . '...';
        }
        
        return $title ?: 'Document Section';
    }
    
    /**
     * Register custom post type for imported data
     */
    public function register_import_post_type() {
        register_post_type('wp_gpt_rag_import', [
            'label' => __('Imported Content', 'wp-gpt-rag-chat'),
            'labels' => [
                'name' => __('Imported Content', 'wp-gpt-rag-chat'),
                'singular_name' => __('Imported Item', 'wp-gpt-rag-chat'),
                'add_new' => __('Add New Import', 'wp-gpt-rag-chat'),
                'add_new_item' => __('Add New Imported Item', 'wp-gpt-rag-chat'),
                'edit_item' => __('Edit Imported Item', 'wp-gpt-rag-chat'),
                'new_item' => __('New Imported Item', 'wp-gpt-rag-chat'),
                'view_item' => __('View Imported Item', 'wp-gpt-rag-chat'),
                'search_items' => __('Search Imported Content', 'wp-gpt-rag-chat'),
                'not_found' => __('No imported content found', 'wp-gpt-rag-chat'),
                'not_found_in_trash' => __('No imported content found in trash', 'wp-gpt-rag-chat'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'custom-fields'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'manage_options',
                'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options',
                'delete_posts' => 'manage_options',
                'delete_others_posts' => 'manage_options',
                'read_private_posts' => 'manage_options',
                'edit_post' => 'manage_options',
                'delete_post' => 'manage_options',
                'read_post' => 'manage_options',
            ],
        ]);
    }
}

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
        
        // Initialize audit trail system
        add_action('init', [$this, 'init_audit_trail'], 2);
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
        add_action('wp_ajax_wp_gpt_rag_chat_get_queue_status', [$this, 'handle_get_queue_status']);
        add_action('wp_ajax_wp_gpt_rag_chat_clear_queue', [$this, 'handle_clear_queue']);
        add_action('wp_ajax_wp_gpt_rag_chat_retry_failed', [$this, 'handle_retry_failed']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_logs', [$this, 'handle_get_logs']);
        add_action('wp_ajax_wp_gpt_rag_chat_clear_logs', [$this, 'handle_clear_logs']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_queue_items', [$this, 'handle_get_queue_items']);
        add_action('wp_ajax_wp_gpt_rag_chat_process_queue', [$this, 'handle_process_queue']);
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
        add_action('wp_ajax_wp_gpt_rag_chat_rate_response', [$this, 'handle_rate_response']);
        add_action('wp_ajax_nopriv_wp_gpt_rag_chat_rate_response', [$this, 'handle_rate_response']);
        add_action('wp_ajax_wp_gpt_rag_chat_add_tags', [$this, 'handle_add_tags']);
        add_action('wp_ajax_wp_gpt_rag_chat_link_source', [$this, 'handle_link_source']);
        add_action('wp_ajax_wp_gpt_rag_chat_search_content', [$this, 'handle_search_content']);
        add_action('wp_ajax_wp_gpt_rag_chat_resolve_gap', [$this, 'handle_resolve_gap']);
        add_action('wp_ajax_wp_gpt_rag_chat_index_sitemap', [$this, 'handle_index_sitemap']);
        add_action('wp_ajax_wp_gpt_rag_chat_index_sitemap_batch', [$this, 'handle_index_sitemap_batch']);
        add_action('wp_ajax_wp_gpt_rag_chat_clear_sitemap', [$this, 'handle_clear_sitemap']);
        add_action('wp_ajax_wp_gpt_rag_chat_generate_sitemap', [$this, 'handle_generate_sitemap']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_indexable_content', [$this, 'handle_get_indexable_content']);
        // Manual post search/indexing helpers
        add_action('wp_ajax_wp_gpt_rag_chat_get_post_by_id', [$this, 'handle_get_post_by_id']);
        add_action('wp_ajax_wp_gpt_rag_chat_enqueue_post', [$this, 'handle_enqueue_post']);
        add_action('wp_ajax_wp_gpt_rag_chat_reindex_post_now', [$this, 'handle_reindex_post_now']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_user_sessions', [$this, 'handle_get_user_sessions']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_geographic_data', [$this, 'handle_get_geographic_data']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_user_activity', [$this, 'handle_get_user_activity']);
        add_action('wp_ajax_wp_gpt_rag_emergency_stop', [$this, 'handle_emergency_stop_ajax']);
        add_action('wp_ajax_wp_gpt_rag_resume_indexing', [$this, 'handle_resume_indexing_ajax']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_next_batch', [$this, 'handle_get_next_batch']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_error_context', [$this, 'handle_get_error_context']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_usage_context', [$this, 'handle_get_usage_context']);
        
        // Persistent indexing AJAX actions
        add_action('wp_ajax_wp_gpt_rag_chat_start_persistent_indexing', [$this, 'handle_start_persistent_indexing']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_indexing_status', [$this, 'handle_get_indexing_status']);
        add_action('wp_ajax_wp_gpt_rag_chat_cancel_persistent_indexing', [$this, 'handle_cancel_persistent_indexing']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_persistent_pending_posts', [$this, 'handle_get_persistent_pending_posts']);
        add_action('wp_ajax_wp_gpt_rag_chat_clear_newly_indexed', [$this, 'handle_clear_newly_indexed']);
        
        // Cron hooks for persistent indexing
        add_action('wp_gpt_rag_chat_process_indexing_batch', ['WP_GPT_RAG_Chat\Persistent_Indexing', 'process_batch']);
        add_action('wp_gpt_rag_chat_cleanup_indexing_state', ['WP_GPT_RAG_Chat\Persistent_Indexing', 'cleanup_indexing_state']);
        add_action('wp_ajax_wp_gpt_rag_chat_start_export', [$this, 'handle_start_export']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_export_history', [$this, 'handle_get_export_history']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_diagnostics_data', [$this, 'handle_get_diagnostics_data']);
        add_action('wp_ajax_wp_gpt_rag_chat_get_process_status', [$this, 'handle_get_process_status']);
        
        // WP-Cron hooks
        add_action('wp_gpt_rag_chat_index_content', [$this, 'cron_index_content']);
        add_action('wp_gpt_rag_chat_cleanup_logs', [$this, 'cron_cleanup_logs']);
        add_action('wp_gpt_rag_chat_process_queue_batch', [$this, 'cron_process_queue_batch']);
        
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
            'includes/class-indexing-queue.php',
            'includes/class-chat.php',
            'includes/class-privacy.php',
            'includes/class-logger.php',
            'includes/class-analytics.php',
            'includes/class-migration.php',
            'includes/class-sitemap.php',
            'includes/RAG_Improvements.php',
            'includes/class-rag-handler.php',
            'includes/class-vector-db.php',
            'includes/class-emergency-stop.php',
            'includes/class-import-protection.php',
            'includes/class-error-logger.php',
            'includes/class-api-usage-tracker.php',
            'includes/class-persistent-indexing.php',
            'includes/class-rbac.php',
            'includes/class-audit-trail.php',
            'includes/class-audit-logger.php'
        ];
        
        foreach ($files as $file) {
            $file_path = WP_GPT_RAG_CHAT_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Initialize audit trail system
     */
    public function init_audit_trail() {
        // Initialize audit logger
        Audit_Logger::init();
        
        // Create audit trail table if it doesn't exist
        $audit_trail = new Audit_Trail();
        $audit_trail->create_table();
        
        // Hook into WordPress events for automatic logging
        add_action('wp_login', [$this, 'log_user_login'], 10, 2);
        add_action('wp_logout', [$this, 'log_user_logout']);
        add_action('wp_login_failed', [$this, 'log_login_failed']);
        add_action('set_user_role', [$this, 'log_role_change'], 10, 3);
    }
    
    /**
     * Log user login
     */
    public function log_user_login($user_login, $user) {
        Audit_Logger::log_login($user->ID);
    }
    
    /**
     * Log user logout
     */
    public function log_user_logout() {
        Audit_Logger::log_logout();
    }
    
    /**
     * Log failed login
     */
    public function log_login_failed($username) {
        Audit_Logger::log_security_event('failed_login', sprintf(__('Failed login attempt for user: %s', 'wp-gpt-rag-chat'), $username), ['username' => $username]);
    }
    
    /**
     * Log role change
     */
    public function log_role_change($user_id, $role, $old_roles) {
        $old_role = !empty($old_roles) ? $old_roles[0] : 'none';
        Audit_Logger::log_role_change($user_id, $old_role, $role);
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Run database migrations if needed
        if (is_admin()) {
            $this->check_and_run_migrations();
        }
        
        // Initialize components
        new Admin();
        new Metabox();
        new Chat();
        new Privacy();
        
        // Initialize RBAC system
        new RBAC();
        
        // Initialize settings for admin
        if (is_admin()) {
            new Settings();
        }
        
        // Initialize chat widget
        $this->init_chat_widget();
    }
    
    /**
     * Check and run database migrations
     */
    private function check_and_run_migrations() {
        $current_version = get_option('wp_gpt_rag_chat_db_version', '1.0.0');
        $plugin_version = WP_GPT_RAG_CHAT_VERSION;
        
        // Run migrations if needed
        if (version_compare($current_version, '2.4.0', '<')) {
            if (class_exists('\WP_GPT_RAG_Chat\Migration')) {
                Migration::run_migrations();
            }
        }
    }
    
    /**
     * Initialize chat widget
     */
    private function init_chat_widget() {
        $chat = new Chat();
        $privacy = new Privacy();
        
        // Add floating chat widget to footer (appears on all pages)
        add_action('wp_footer', [$chat, 'render_floating_chat_widget']);
        
        // Register shortcode (for manual placement if needed)
        $chat->register_shortcode();
        
        // Add privacy policy content
        add_filter('the_content', [$privacy, 'add_privacy_policy_content']);
    }
    
    /**
     * Add admin menu with role-based access control
     */
    public function admin_menu() {
        // For administrators, show full menu without RBAC restrictions
        if (current_user_can('manage_options')) {
            // Main Nuwab AI Assistant Menu
            add_menu_page(
                __('Nuwab AI Assistant Dashboard', 'wp-gpt-rag-chat'),
                __('Nuwab AI Assistant', 'wp-gpt-rag-chat'),
                'manage_options',
                'wp-gpt-rag-chat-dashboard',
                [$this, 'dashboard_page'],
                'dashicons-format-chat',
                30
            );
            
            // Dashboard submenu
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
            
            // Analytics & Logs submenu
            add_submenu_page(
                'wp-gpt-rag-chat-dashboard',
                __('Analytics & Logs', 'wp-gpt-rag-chat'),
                __('Analytics & Logs', 'wp-gpt-rag-chat'),
                'manage_options',
                'wp-gpt-rag-chat-analytics',
                [$this, 'analytics_page']
            );
            
            // Diagnostics submenu
            add_submenu_page(
                'wp-gpt-rag-chat-dashboard',
                __('Diagnostics', 'wp-gpt-rag-chat'),
                __('Diagnostics', 'wp-gpt-rag-chat'),
                'manage_options',
                'wp-gpt-rag-chat-diagnostics',
                [$this, 'diagnostics_page']
            );
            
            // Cron Status submenu
            add_submenu_page(
                'wp-gpt-rag-chat-dashboard',
                __('Cron Status', 'wp-gpt-rag-chat'),
                __('Cron Status', 'wp-gpt-rag-chat'),
                'manage_options',
                'wp-gpt-rag-chat-cron-status',
                [$this, 'cron_status_page']
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
            
            // Audit Trail submenu
            add_submenu_page(
                'wp-gpt-rag-chat-dashboard',
                __('Audit Trail', 'wp-gpt-rag-chat'),
                __('Audit Trail', 'wp-gpt-rag-chat'),
                'manage_options',
                'wp-gpt-rag-chat-audit-trail',
                [$this, 'audit_trail_page']
            );
            
            // About Plugin submenu
            add_submenu_page(
                'wp-gpt-rag-chat-dashboard',
                __('About Plugin', 'wp-gpt-rag-chat'),
                __('About Plugin', 'wp-gpt-rag-chat'),
                'manage_options',
                'wp-gpt-rag-chat-about',
                [$this, 'about_page']
            );
            
            // Hidden submenus (for direct access)
            add_submenu_page(
                null, // Hidden from menu
                __('View Conversation', 'wp-gpt-rag-chat'),
                '',
                'manage_options',
                'wp-gpt-rag-chat-conversation',
                [$this, 'conversation_view_page']
            );
        } else {
            // For non-administrators, check if they are editors (Log Viewers)
            if (!RBAC::is_log_viewer() || RBAC::is_aims_manager()) {
                return; // No access for other roles or if already an administrator
            }
            
            // Get user role display name
            $user_role = RBAC::get_user_role_display();
            
            // Main Nuwab AI Assistant Menu (Editor access only)
            add_menu_page(
                __('Nuwab AI Assistant Dashboard', 'wp-gpt-rag-chat'),
                __('Nuwab AI Assistant', 'wp-gpt-rag-chat'),
                'edit_posts', // Use standard WordPress editor capability
                'wp-gpt-rag-chat-dashboard-editor',
                [$this, 'dashboard_page'],
                'dashicons-format-chat',
                30
            );
            
            // Dashboard submenu (Editor access)
            add_submenu_page(
                'wp-gpt-rag-chat-dashboard-editor',
                __('Dashboard', 'wp-gpt-rag-chat'),
                __('Dashboard', 'wp-gpt-rag-chat'),
                'edit_posts',
                'wp-gpt-rag-chat-dashboard-editor',
                [$this, 'dashboard_page']
            );
            
            // Analytics & Logs submenu (Editor access)
            add_submenu_page(
                'wp-gpt-rag-chat-dashboard-editor',
                __('Analytics & Logs', 'wp-gpt-rag-chat'),
                __('Analytics & Logs', 'wp-gpt-rag-chat'),
                'edit_posts',
                'wp-gpt-rag-chat-analytics-editor',
                [$this, 'analytics_page']
            );
            
            // Editors only get Dashboard and Analytics & Logs
            // No additional menu items for editors
            
            // AIMS Manager only menu items (for administrators)
            if (RBAC::is_aims_manager()) {
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
                
                // Analytics & Logs submenu is now available to all authorized users above
                
                // Diagnostics submenu
                add_submenu_page(
                    'wp-gpt-rag-chat-dashboard',
                    __('Diagnostics', 'wp-gpt-rag-chat'),
                    __('Diagnostics', 'wp-gpt-rag-chat'),
                    'manage_options',
                    'wp-gpt-rag-chat-diagnostics',
                    [$this, 'diagnostics_page']
                );
                
                // Cron Status submenu
                add_submenu_page(
                    'wp-gpt-rag-chat-dashboard',
                    __('Cron Status', 'wp-gpt-rag-chat'),
                    __('Cron Status', 'wp-gpt-rag-chat'),
                    'manage_options',
                    'wp-gpt-rag-chat-cron-status',
                    [$this, 'cron_status_page']
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
                
                // About Plugin submenu
                add_submenu_page(
                    'wp-gpt-rag-chat-dashboard',
                    __('About Plugin', 'wp-gpt-rag-chat'),
                    __('About Plugin', 'wp-gpt-rag-chat'),
                    'manage_options',
                    'wp-gpt-rag-chat-about',
                    [$this, 'about_page']
                );
            }
            
            // Duplicate editor menu removed - editors already have access above
            
            // Hidden submenus (for direct access)
            add_submenu_page(
                null, // Hidden from menu
                __('View Conversation', 'wp-gpt-rag-chat'),
                '',
                'wp_gpt_rag_view_logs',
                'wp-gpt-rag-chat-conversation',
                [$this, 'conversation_view_page']
            );
        }
    }
    
    /**
     * Dashboard page callback
     */
    public function dashboard_page() {
        // Emergency fix: No permission checks at all
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
        // Emergency fix: No permission checks at all
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/analytics-page.php';
    }
    
    /**
     * Diagnostics page callback
     */
    public function diagnostics_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/diagnostics-page.php';
    }
    
    /**
     * Cron Status page callback
     */
    public function cron_status_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/cron-status-page.php';
    }
    
    /**
     * Conversation view page callback
     */
    public function conversation_view_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/conversation-view.php';
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
     * About page callback
     */
    public function about_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/about-page.php';
    }
    
    /**
     * Audit Trail page callback
     */
    public function audit_trail_page() {
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/audit-trail-page.php';
    }
    
    /**
     * Logs page callback for Log Viewer role
     */
    public function logs_page() {
        // Emergency fix: No permission checks at all
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
        
        // Enqueue FontAwesome
        wp_enqueue_style(
            'fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            [],
            '6.4.0'
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
            wp_enqueue_media(); // Enable WordPress media library
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
            
            // Enqueue plugin-specific admin styles
            wp_enqueue_style(
                'wp-gpt-rag-chat-cor-admin-style',
                WP_GPT_RAG_CHAT_PLUGIN_URL . 'assets/css/cor-admin-style.css',
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
            'emergencyStopActive' => Emergency_Stop::is_active(),
            'strings' => [
                'confirmBulkAction' => __('Are you sure you want to perform this action?', 'wp-gpt-rag-chat'),
                'processing' => __('Processing...', 'wp-gpt-rag-chat'),
                'success' => __('Action completed successfully.', 'wp-gpt-rag-chat'),
                'error' => __('An error occurred. Please try again.', 'wp-gpt-rag-chat'),
            ]
        ]);
        
        // Add inline script for emergency stop functions
        wp_add_inline_script('wp-gpt-rag-chat-admin', '
            function wpGptRagEmergencyStop() {
                if (!confirm("⚠️ EMERGENCY STOP\\n\\nThis will immediately:\\n• Stop all indexing\\n• Clear all queued jobs\\n• Prevent new indexing\\n\\nAre you sure?")) {
                    return;
                }
                jQuery.post(wpGptRagChatAdmin.ajaxUrl, {
                    action: "wp_gpt_rag_emergency_stop",
                    nonce: wpGptRagChatAdmin.nonce
                }, function(response) {
                    if (response.success) {
                        alert("✅ Emergency stop activated!\\n\\n" + response.data.message);
                        location.reload();
                    } else {
                        alert("❌ Error: " + (response.data ? response.data.message : "Unknown error"));
                    }
                });
            }
            
            function wpGptRagResumeIndexing() {
                if (!confirm("▶ Resume Indexing\\n\\nThis will re-enable automatic indexing.\\n\\nContinue?")) {
                    return;
                }
                jQuery.post(wpGptRagChatAdmin.ajaxUrl, {
                    action: "wp_gpt_rag_resume_indexing",
                    nonce: wpGptRagChatAdmin.nonce
                }, function(response) {
                    if (response.success) {
                        alert("✅ Indexing resumed!\\n\\n" + response.data.message);
                        location.reload();
                    } else {
                        alert("❌ Error: " + (response.data ? response.data.message : "Unknown error"));
                    }
                });
            }
        ');
    }
    
    /**
     * Handle chat query AJAX request
     */
    public function handle_chat_query() {
        check_ajax_referer('wp_gpt_rag_chat_nonce', 'nonce');
        
        // Check chat visibility settings
        $settings = Settings::get_settings();
        $chat_visibility = $settings['chat_visibility'] ?? 'everyone';
        $is_user_logged_in = is_user_logged_in();
        
        // Validate user has permission to use chat based on visibility settings
        $can_use_chat = false;
        switch ($chat_visibility) {
            case 'logged_in_only':
                $can_use_chat = $is_user_logged_in;
                break;
            case 'visitors_only':
                $can_use_chat = !$is_user_logged_in;
                break;
            case 'everyone':
            default:
                $can_use_chat = true;
                break;
        }
        
        if (!$can_use_chat) {
            wp_send_json_error(['message' => __('You do not have permission to use the chat.', 'wp-gpt-rag-chat')]);
        }
        
        // Check if chatbot is enabled
        if (empty($settings['enable_chatbot'])) {
            wp_send_json_error(['message' => __('Chat is currently disabled.', 'wp-gpt-rag-chat')]);
        }
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        $chat_id = sanitize_text_field($_POST['chat_id'] ?? '');
        $turn_number = intval($_POST['turn_number'] ?? 1);
        $detected_language = sanitize_text_field($_POST['detected_language'] ?? '');
        
        if (empty($query)) {
            wp_send_json_error(['message' => __('Query is required.', 'wp-gpt-rag-chat')]);
        }
        
        // Generate chat_id if not provided
        if (empty($chat_id)) {
            $analytics = new Analytics();
            $chat_id = $analytics->generate_chat_id();
        }
        
        try {
            $start_time = microtime(true);
            
            $chat = new Chat();
            $response = $chat->process_query($query, [], $detected_language);
            $rag_metadata = $chat->get_last_rag_metadata();
            
            $latency = round((microtime(true) - $start_time) * 1000); // milliseconds
            
            // Log the interaction with Analytics
            $analytics = new Analytics();
            
            // Log user message with query variations
            $user_log_data = [
                'chat_id' => $chat_id,
                'turn_number' => $turn_number,
                'role' => 'user',
                'content' => $query,
                'user_id' => get_current_user_id()
            ];
            
            // Add RAG metadata to user log
            if (!empty($rag_metadata)) {
                $user_log_data['rag_metadata'] = wp_json_encode($rag_metadata);
            }
            
            $user_log_id = $analytics->log_interaction($user_log_data);
            
            // Log assistant response
            $assistant_log_id = $analytics->log_interaction([
                'chat_id' => $chat_id,
                'turn_number' => $turn_number,
                'role' => 'assistant',
                'content' => $response,
                'response_latency' => $latency,
                'model_used' => $settings['gpt_model'] ?? 'gpt-4',
                'user_id' => get_current_user_id()
            ]);
            
            wp_send_json_success([
                'response' => $response,
                'chat_id' => $chat_id,
                'log_id' => $assistant_log_id,
                'latency' => $latency
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle rating response AJAX request
     */
    public function handle_rate_response() {
        check_ajax_referer('wp_gpt_rag_chat_nonce', 'nonce');
        
        $log_id = intval($_POST['log_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 0);
        
        if (empty($log_id) || !in_array($rating, [1, -1])) {
            wp_send_json_error(['message' => __('Invalid parameters.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $analytics = new Analytics();
            $result = $analytics->update_rating($log_id, $rating);
            
            if ($result) {
                wp_send_json_success(['message' => __('Rating saved.', 'wp-gpt-rag-chat')]);
            } else {
                wp_send_json_error(['message' => __('Failed to save rating.', 'wp-gpt-rag-chat')]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle add tags AJAX request
     */
    public function handle_add_tags() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $log_id = intval($_POST['log_id'] ?? 0);
        $tags = sanitize_text_field($_POST['tags'] ?? '');
        
        if (empty($log_id) || empty($tags)) {
            wp_send_json_error(['message' => __('Invalid parameters.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $analytics = new Analytics();
            $result = $analytics->add_tags($log_id, $tags);
            
            if ($result) {
                wp_send_json_success(['message' => __('Tags added.', 'wp-gpt-rag-chat')]);
            } else {
                wp_send_json_error(['message' => __('Failed to add tags.', 'wp-gpt-rag-chat')]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle link source AJAX request
     */
    public function handle_link_source() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $log_id = intval($_POST['log_id'] ?? 0);
        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        $source_id = intval($_POST['source_id'] ?? 0);
        $reindex = isset($_POST['reindex']) ? (bool) $_POST['reindex'] : false;
        
        if (empty($log_id) || empty($source_type) || empty($source_id)) {
            wp_send_json_error(['message' => __('Invalid parameters.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            global $wpdb;
            $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
            
            // Get current sources
            $current_sources_json = $wpdb->get_var($wpdb->prepare(
                "SELECT rag_sources FROM {$logs_table} WHERE id = %d",
                $log_id
            ));
            
            $sources = $current_sources_json ? json_decode($current_sources_json, true) : [];
            
            // Get source details
            if ($source_type === 'post') {
                $post = get_post($source_id);
                if (!$post) {
                    wp_send_json_error(['message' => __('Post not found.', 'wp-gpt-rag-chat')]);
                }
                
                $sources[] = [
                    'type' => 'post',
                    'id' => $source_id,
                    'title' => $post->post_title,
                    'url' => get_permalink($source_id),
                    'post_type' => $post->post_type,
                    'manually_linked' => true,
                    'linked_at' => current_time('mysql')
                ];
            } elseif ($source_type === 'attachment') {
                $attachment = get_post($source_id);
                if (!$attachment || $attachment->post_type !== 'attachment') {
                    wp_send_json_error(['message' => __('Attachment not found.', 'wp-gpt-rag-chat')]);
                }
                
                $sources[] = [
                    'type' => 'attachment',
                    'id' => $source_id,
                    'title' => $attachment->post_title,
                    'url' => wp_get_attachment_url($source_id),
                    'mime_type' => get_post_mime_type($source_id),
                    'manually_linked' => true,
                    'linked_at' => current_time('mysql')
                ];
            }
            
            // Update log entry
            $result = $wpdb->update(
                $logs_table,
                [
                    'rag_sources' => wp_json_encode($sources),
                    'sources_count' => count($sources)
                ],
                ['id' => $log_id],
                ['%s', '%d'],
                ['%d']
            );
            
            // ALWAYS check and index if needed (not just when checkbox is checked)
            $indexing_status = ['indexed' => false, 'message' => ''];
            $is_already_indexed = $this->is_post_indexed($source_id);
            
            try {
                $indexing = new Indexing();
                
                // If not indexed OR user requested re-index
                if (!$is_already_indexed || $reindex) {
                    if ($source_type === 'post') {
                        $indexing->index_post($source_id, true);
                    } elseif ($source_type === 'attachment') {
                        $indexing->index_post($source_id, true);
                    }
                    
                    $indexing_status['indexed'] = true;
                    $indexing_status['message'] = $is_already_indexed 
                        ? __('Source re-indexed.', 'wp-gpt-rag-chat')
                        : __('Source auto-indexed.', 'wp-gpt-rag-chat');
                    
                    error_log('WP GPT RAG Chat: Auto-indexed linked source #' . $source_id);
                } else {
                    $indexing_status['message'] = __('Already indexed.', 'wp-gpt-rag-chat');
                }
            } catch (\Exception $e) {
                error_log('WP GPT RAG Chat: Auto-index error: ' . $e->getMessage());
                $indexing_status['message'] = __('Indexing failed: ', 'wp-gpt-rag-chat') . $e->getMessage();
            }
            
            if ($result !== false) {
                $message = __('Source linked successfully.', 'wp-gpt-rag-chat');
                if (!empty($indexing_status['message'])) {
                    $message .= ' (' . $indexing_status['message'] . ')';
                }
                
                wp_send_json_success([
                    'message' => $message,
                    'indexing' => $indexing_status,
                    'sources_count' => count($sources)
                ]);
            } else {
                wp_send_json_error(['message' => __('Failed to link source.', 'wp-gpt-rag-chat')]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle search content AJAX request
     */
    public function handle_search_content() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $search = sanitize_text_field($_POST['search'] ?? '');
        $post_type = sanitize_text_field($_POST['post_type'] ?? 'any');
        
        if (empty($search)) {
            wp_send_json_error(['message' => __('Search query is required.', 'wp-gpt-rag-chat')]);
        }
        
        $results = [];
        
        // Handle attachment/PDF search separately
        if ($post_type === 'attachment') {
            $attachment_args = [
                's' => $search,
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'post_mime_type' => 'application/pdf',
                'posts_per_page' => 100
            ];
            
            $attachments = get_posts($attachment_args);
            
            foreach ($attachments as $attachment) {
                $results[] = [
                    'id' => $attachment->ID,
                    'title' => $attachment->post_title,
                    'type' => 'pdf',
                    'url' => wp_get_attachment_url($attachment->ID),
                    'excerpt' => __('PDF Document', 'wp-gpt-rag-chat'),
                    'is_indexed' => $this->is_post_indexed($attachment->ID)
                ];
            }
        } else {
            // Search posts (all public post types if 'any')
            $post_types_to_search = $post_type === 'any' 
                ? get_post_types(['public' => true], 'names')
                : [$post_type];
            
            // Remove attachment from the list
            $post_types_to_search = array_diff($post_types_to_search, ['attachment']);
            
            $args = [
                's' => $search,
                'post_type' => $post_types_to_search,
                'post_status' => ['publish', 'private'],
                'posts_per_page' => 100,
                'orderby' => 'relevance'
            ];
            
            $posts = get_posts($args);
            
            foreach ($posts as $post) {
                $results[] = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'type' => $post->post_type,
                    'url' => get_permalink($post->ID),
                    'excerpt' => wp_trim_words($post->post_content, 20),
                    'is_indexed' => $this->is_post_indexed($post->ID)
                ];
            }
        }
        
        wp_send_json_success(['results' => $results]);
    }
    
    /**
     * Handle resolve content gap AJAX request
     */
    public function handle_resolve_gap() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $gap_id = intval($_POST['gap_id'] ?? 0);
        
        if (empty($gap_id)) {
            wp_send_json_error(['message' => __('Invalid gap ID.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $rag_improvements = new RAG_Improvements();
            $result = $rag_improvements->resolve_content_gap($gap_id);
            
            if ($result) {
                wp_send_json_success([
                    'message' => __('Content gap marked as resolved.', 'wp-gpt-rag-chat')
                ]);
            } else {
                wp_send_json_error(['message' => __('Failed to resolve content gap.', 'wp-gpt-rag-chat')]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle sitemap indexing AJAX request
     */
    public function handle_index_sitemap() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $sitemap_url = sanitize_text_field($_POST['sitemap_url'] ?? '');
        
        if (empty($sitemap_url)) {
            $settings = Settings::get_settings();
            $sitemap_url = $settings['sitemap_url'] ?? 'sitemap.xml';
        }
        
        try {
            $sitemap = new Sitemap();
            $result = $sitemap->index_sitemap($sitemap_url);
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => sprintf(
                        __('Successfully indexed %d URLs (%d failed).', 'wp-gpt-rag-chat'),
                        $result['indexed'],
                        $result['failed']
                    ),
                    'indexed' => $result['indexed'],
                    'failed' => $result['failed'],
                    'total' => $result['total']
                ]);
            } else {
                wp_send_json_error(['message' => $result['error'] ?? __('Failed to index sitemap.', 'wp-gpt-rag-chat')]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle batch sitemap indexing AJAX request
     */
    public function handle_index_sitemap_batch() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        // CHECK FOR EMERGENCY STOP FLAG
        if (get_transient('wp_gpt_rag_emergency_stop')) {
            wp_send_json_error([
                'message' => __('⛔ INDEXING BLOCKED: Emergency stop is active.', 'wp-gpt-rag-chat'),
                'emergency_stop' => true
            ]);
        }
        
        $sitemap_url = sanitize_text_field($_POST['sitemap_url'] ?? '');
        $offset = intval($_POST['offset'] ?? 0);
        
        if (empty($sitemap_url)) {
            wp_send_json_error(['message' => __('Sitemap URL is required.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $sitemap = new Sitemap();
            $result = $sitemap->index_sitemap_batch($sitemap_url, $offset, 5);
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => sprintf(
                        __('Indexed %d of %d URLs', 'wp-gpt-rag-chat'),
                        $offset + $result['processed'],
                        $result['total']
                    ),
                    'total' => $result['total'],
                    'processed' => $result['processed'],
                    'indexed' => $result['indexed'],
                    'failed' => $result['failed'],
                    'offset' => $offset,
                    'has_more' => $result['has_more']
                ]);
            } else {
                wp_send_json_error(['message' => $result['error']]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle clear sitemap index AJAX request
     */
    public function handle_clear_sitemap() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $sitemap = new Sitemap();
            $result = $sitemap->clear_sitemap_index();
            
            wp_send_json_success([
                'message' => __('Sitemap index cleared successfully.', 'wp-gpt-rag-chat')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle sitemap generation AJAX request
     */
    public function handle_generate_sitemap() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $post_types = $_POST['post_types'] ?? 'all';
        $download = isset($_POST['download']) && $_POST['download'] === 'true';
        
        try {
            $indexing = new Indexing();
            
            // Generate sitemap
            $result = $indexing->generate_xml_sitemap($post_types);
            
            if ($download) {
                // Save to file and return download URL
                $file_info = $indexing->save_sitemap_to_file($result['xml']);
                
                wp_send_json_success([
                    'message' => sprintf(__('Sitemap generated with %d URLs.', 'wp-gpt-rag-chat'), $result['count']),
                    'count' => $result['count'],
                    'post_types' => $result['post_types'],
                    'download_url' => $file_info['url'],
                    'filename' => $file_info['filename']
                ]);
            } else {
                // Return XML content directly
                wp_send_json_success([
                    'message' => sprintf(__('Sitemap generated with %d URLs.', 'wp-gpt-rag-chat'), $result['count']),
                    'count' => $result['count'],
                    'post_types' => $result['post_types'],
                    'xml' => $result['xml']
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle get indexable content AJAX request
     */
    public function handle_get_indexable_content() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $indexing = new Indexing();
            $content_list = $indexing->get_all_indexable_content();
            
            // Count indexed vs unindexed
            $indexed_count = count(array_filter($content_list, function($item) {
                return $item['indexed'];
            }));
            $unindexed_count = count($content_list) - $indexed_count;
            
            wp_send_json_success([
                'content' => $content_list,
                'total' => count($content_list),
                'indexed' => $indexed_count,
                'unindexed' => $unindexed_count
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle get user sessions AJAX request
     */
    public function handle_get_user_sessions() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $analytics = new Analytics();
            $sessions = $analytics->get_user_sessions(20);
            
            $html = '';
            if (empty($sessions)) {
                $html = '<tr><td colspan="5">' . __('No user sessions found.', 'wp-gpt-rag-chat') . '</td></tr>';
            } else {
                foreach ($sessions as $session) {
                    $html .= '<tr>';
                    $html .= '<td>' . esc_html($session->display_name) . '</td>';
                    $html .= '<td>' . esc_html($session->user_type) . '</td>';
                    $html .= '<td>' . esc_html($session->sessions) . '</td>';
                    $html .= '<td>' . esc_html($session->queries) . '</td>';
                    $html .= '<td>' . esc_html(date('Y-m-d H:i', strtotime($session->last_activity))) . '</td>';
                    $html .= '</tr>';
                }
            }
            
            wp_send_json_success(['html' => $html]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle get geographic data AJAX request
     */
    public function handle_get_geographic_data() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $analytics = new Analytics();
            $distribution = $analytics->get_geographic_distribution();
            
            $html = '';
            if (empty($distribution)) {
                $html = '<tr><td colspan="3">' . __('No geographic data available.', 'wp-gpt-rag-chat') . '</td></tr>';
            } else {
                foreach ($distribution as $item) {
                    $html .= '<tr>';
                    $html .= '<td>' . esc_html($item['region']) . '</td>';
                    $html .= '<td>' . esc_html($item['users']) . '</td>';
                    $html .= '<td>' . esc_html($item['percentage']) . '%</td>';
                    $html .= '</tr>';
                }
            }
            
            wp_send_json_success(['html' => $html]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle get user activity AJAX request
     */
    public function handle_get_user_activity() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $days = intval($_POST['days'] ?? 7);
        
        try {
            $analytics = new Analytics();
            $activity = $analytics->get_user_activity($days);
            
            wp_send_json_success(['activity' => $activity]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Check if post is indexed in Pinecone
     */
    private function is_post_indexed($post_id) {
        global $wpdb;
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$vectors_table} WHERE post_id = %d",
            $post_id
        ));
        
        return $count > 0;
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
        // ⚠️ EMERGENCY STOP CHECK - MUST BE FIRST!
        if (get_transient('wp_gpt_rag_emergency_stop')) {
            return; // Block all indexing when emergency stop is active
        }
        
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        if (!in_array($post->post_status, ['publish', 'private'])) {
            return;
        }
        
        // Get settings
        $settings = Settings::get_settings();
        
        // Check if auto-indexing is enabled
        if (!$settings['enable_auto_indexing']) {
            return;
        }
        
        // Check if this post type should be auto-indexed
        $auto_index_post_types = $settings['auto_index_post_types'] ?? ['post', 'page'];
        if (!in_array($post->post_type, $auto_index_post_types)) {
            return;
        }
        
        // Add post to indexing queue instead of using wp_postmeta
        // Get indexing delay from settings
        $delay = $settings['auto_index_delay'] ?? 30;
        
        // Add to queue with priority (higher priority for auto-indexing)
        if (Indexing_Queue::add_to_queue($post_id, 10)) {
            // Schedule queue processing with configured delay
            wp_schedule_single_event(time() + $delay, 'wp_gpt_rag_chat_process_queue_batch');
        }
    }
    
    /**
     * Handle post delete
     */
    public function handle_post_delete($post_id) {
        try {
            // Remove from indexing queue
            Indexing_Queue::remove_from_queue($post_id);
            
            // Delete vectors
            $indexing = new Indexing();
            $indexing->delete_post_vectors($post_id);
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error deleting vectors for post ' . $post_id . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Add posts to indexing queue
     */
    private function add_posts_to_queue($post_type, $batch_size, $offset) {
        // Get allowed post types from settings
        $settings = Settings::get_settings();
        $allowed_post_types = $settings['post_types'] ?? ['post', 'page'];

        // Base query args for eligible posts
        $base_query_args = [
            'post_status' => ['publish', 'private']
        ];

        // Add post type filter if specified
        if ($post_type && $post_type !== 'all') {
            $base_query_args['post_type'] = $post_type;
        } else {
            $base_query_args['post_type'] = $allowed_post_types;
        }

        // Get total eligible posts count (ids only for efficiency)
        $count_args = $base_query_args;
        $count_args['numberposts'] = -1;
        $count_args['fields'] = 'ids';
        $eligible_ids = get_posts($count_args);
        $eligible_total = is_array($eligible_ids) ? count($eligible_ids) : 0;

        // Get only the next batch based on offset and batch size
        $batch_args = $base_query_args;
        $batch_args['numberposts'] = $batch_size;
        $batch_args['offset'] = $offset;
        $batch_posts = get_posts($batch_args);

        // Filter out posts already pending or processing to avoid duplicates
        $filtered_posts = [];
        foreach ($batch_posts as $post) {
            $status_row = Indexing_Queue::get_post_queue_status($post->ID);
            if ($status_row && in_array($status_row->status, ['pending','processing','completed'], true)) {
                continue;
            }
            $filtered_posts[] = $post;
        }

        $added_count = 0;
        $post_ids = [];

        error_log('WP GPT RAG Chat: Enqueuing batch - size ' . $batch_size . ', offset ' . $offset . ', eligible total ' . $eligible_total);

        foreach ($filtered_posts as $post) {
            if (Indexing_Queue::add_to_queue($post->ID)) {
                $added_count++;
                $post_ids[] = $post->ID;
            }
        }

        return [
            'processed' => $added_count,
            'total' => $eligible_total,
            'errors' => [],
            'indexed_post_ids' => $post_ids,
            'message' => sprintf(__('Added %d posts to indexing queue.', 'wp-gpt-rag-chat'), $added_count)
        ];
    }
    
    /**
     * Add single post to indexing queue
     */
    private function add_single_post_to_queue($post_type) {
        // Get allowed post types from settings
        $settings = Settings::get_settings();
        $allowed_post_types = $settings['post_types'] ?? ['post', 'page'];
        
        $query_args = [
            'numberposts' => 1,
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ],
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];
        
        // Add post type filter if specified
        if ($post_type && $post_type !== 'all') {
            $query_args['post_type'] = $post_type;
        } else {
            $query_args['post_type'] = $allowed_post_types;
        }
        
        $posts = get_posts($query_args);
        
        if (!empty($posts)) {
            $post = $posts[0];
            if (Indexing_Queue::add_to_queue($post->ID)) {
                return [
                    'processed' => 1,
                    'total' => 1,
                    'errors' => [],
                    'indexed_post_ids' => [$post->ID],
                    'message' => sprintf(__('Added post "%s" to indexing queue.', 'wp-gpt-rag-chat'), $post->post_title)
                ];
            }
        }
        
        return [
            'processed' => 0,
            'total' => 0,
            'errors' => [__('No posts found to add to queue.', 'wp-gpt-rag-chat')],
            'indexed_post_ids' => [],
            'message' => __('No posts found to add to queue.', 'wp-gpt-rag-chat')
        ];
    }
    
    /**
     * Add changed posts to indexing queue
     */
    private function add_changed_posts_to_queue($post_type, $batch_size, $offset) {
        global $wpdb;
        
        // Get allowed post types from settings
        $settings = Settings::get_settings();
        $allowed_post_types = $settings['post_types'] ?? ['post', 'page'];
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        // Build post type filter
        if ($post_type && $post_type !== 'all') {
            $post_type_filter = "'" . esc_sql($post_type) . "'";
        } else {
            $post_type_filter = "'" . implode("','", $allowed_post_types) . "'";
        }
        
        // Get ALL posts that have been modified since last indexing
        $posts = $wpdb->get_results(
            "SELECT DISTINCT p.ID, p.post_title, p.post_modified
            FROM {$wpdb->posts} p
            LEFT JOIN {$vectors_table} v ON p.ID = v.post_id
            WHERE p.post_status IN ('publish', 'private')
            AND p.post_type IN ($post_type_filter)
            AND (v.post_id IS NULL OR p.post_modified > v.updated_at)
            ORDER BY p.post_modified DESC"
        );
        
        $added_count = 0;
        $post_ids = [];
        
        foreach ($posts as $post) {
            if (Indexing_Queue::add_to_queue($post->ID)) {
                $added_count++;
                $post_ids[] = $post->ID;
            }
        }
        
        return [
            'processed' => $added_count,
            'total' => count($posts),
            'errors' => [],
            'indexed_post_ids' => $post_ids,
            'message' => sprintf(__('Added %d changed posts to indexing queue.', 'wp-gpt-rag-chat'), $added_count)
        ];
    }
    
    /**
     * Handle test connection AJAX request
     */
    public function handle_test_connection() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $connection_type = sanitize_text_field($_POST['connection_type'] ?? '');
        
        try {
            if ($connection_type === 'openai') {
                $openai = new OpenAI();
                $result = $openai->test_connection();
                
                if ($result['success']) {
                    wp_send_json_success(['message' => __('OpenAI API connection successful', 'wp-gpt-rag-chat')]);
                } else {
                    wp_send_json_error(['message' => 'OpenAI: ' . $result['message']]);
                }
                
            } elseif ($connection_type === 'pinecone') {
                $pinecone = new Pinecone();
                $result = $pinecone->test_connection();
                
                if ($result['success']) {
                    wp_send_json_success(['message' => __('Pinecone API connection successful', 'wp-gpt-rag-chat')]);
                } else {
                    wp_send_json_error(['message' => 'Pinecone: ' . $result['message']]);
                }
                
            } elseif ($connection_type === 'wordpress') {
                // Simple WordPress functionality test
                $test_post = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
                
                if (!empty($test_post)) {
                    wp_send_json_success(['message' => __('WordPress database connection successful', 'wp-gpt-rag-chat')]);
                } else {
                    wp_send_json_error(['message' => __('WordPress database connection failed', 'wp-gpt-rag-chat')]);
                }
                
            } else {
                // Default behavior - test both OpenAI and Pinecone
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
        // Increase memory limit and execution time for bulk operations
        ini_set('memory_limit', '512M');
        set_time_limit(300); // 5 minutes
        
        error_log('WP GPT RAG Chat: handle_bulk_index called');
        
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        // CHECK FOR EMERGENCY STOP FLAG
        if (get_transient('wp_gpt_rag_emergency_stop')) {
            wp_send_json_error([
                'message' => __('⛔ INDEXING BLOCKED: Emergency stop is active. Go to the indexing page to re-enable.', 'wp-gpt-rag-chat'),
                'emergency_stop' => true
            ]);
        }
        
        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $offset = intval($_POST['offset'] ?? 0);
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        $batch_size = intval($_POST['batch_size'] ?? 10);
        
        // Ensure batch size is reasonable (1-50)
        $batch_size = max(1, min(50, $batch_size));
        
        // Log the request for debugging
        error_log("WP GPT RAG Chat: Bulk index request - Action: {$action}, Offset: {$offset}, Post Type: {$post_type}, Batch Size: {$batch_size}");
        
        // Initialize indexing class
        $indexing = new Indexing();
        
        try {
            switch ($action) {
                case 'index_all':
                    // Add posts to queue instead of processing immediately
                    $result = $this->add_posts_to_queue($post_type, $batch_size, $offset);
                    break;
                case 'index_single':
                    // Add single post to queue
                    $result = $this->add_single_post_to_queue($post_type);
                    break;
                case 'reindex_changed':
                    // Add changed posts to queue
                    $result = $this->add_changed_posts_to_queue($post_type, $batch_size, $offset);
                    break;
                default:
                    wp_send_json_error(['message' => __('Invalid action.', 'wp-gpt-rag-chat')]);
            }
            
            // Calculate total posts based on actual posts added to queue
            if ($action === 'index_single') {
                $total_posts = 1; // Single post action always processes 1 post
            } else {
                // Use the actual number of posts added to the queue
                $total_posts = $result['total'];
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
            
            // Get updated stats
            $stats = $indexing->get_indexing_stats();
            
            // If nothing was processed and we have errors, report as an error response
            if (empty($result['processed']) && !empty($result['errors'])) {
                $message = is_array($result['errors']) ? implode("\n", $result['errors']) : (string) $result['errors'];
                wp_send_json_error([
                    'message' => $message,
                    'processed' => 0,
                    'total' => $total_posts,
                    'total_posts' => $total_posts,
                    'errors' => $result['errors'],
                    'stats' => $stats
                ]);
            }
            
            wp_send_json_success([
                'processed' => $result['processed'],
                'total' => $total_posts,
                'total_posts' => $total_posts,
                'completed' => ($action === 'index_single') ? true : (($offset + 10) >= $total_posts),
                'errors' => $result['errors'],
                'newly_indexed' => $newly_indexed,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Exception in handle_bulk_index: ' . $e->getMessage());
            error_log('WP GPT RAG Chat: Exception trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => $e->getMessage()]);
        } catch (\Error $e) {
            error_log('WP GPT RAG Chat: Fatal error in handle_bulk_index: ' . $e->getMessage());
            error_log('WP GPT RAG Chat: Error trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => 'Fatal error: ' . $e->getMessage()]);
        }
    }

    /**
     * Provide the next batch of posts (IDs and titles) that would be indexed
     */
    public function handle_get_next_batch() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }

        $offset = intval($_POST['offset'] ?? 0);
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        $limit = 10; // match processing batch size

        $query_args = [
            'numberposts' => $limit,
            'offset' => $offset,
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ],
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];

        if ($post_type && $post_type !== 'all') {
            $query_args['post_type'] = $post_type;
        } else {
            $query_args['post_type'] = get_post_types(['public' => true]);
        }

        $posts = get_posts($query_args);
        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'edit_url' => get_edit_post_link($post->ID)
            ];
        }

        wp_send_json_success(['items' => $items]);
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
     * Handle emergency stop AJAX
     */
    public function handle_emergency_stop_ajax() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        Emergency_Stop::activate();
        $cleared = Emergency_Stop::get_cron_count();
        
        wp_send_json_success([
            'message' => 'Emergency stop activated. All indexing stopped.',
            'cleared' => $cleared
        ]);
    }
    
    /**
     * Handle resume indexing AJAX
     */
    public function handle_resume_indexing_ajax() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        Emergency_Stop::deactivate();
        Import_Protection::deactivate();
        
        wp_send_json_success([
            'message' => 'Indexing resumed. Auto-indexing will work normally now.'
        ]);
    }
    
    /**
     * Cron job to index content
     */
    public function cron_index_content($post_id) {
        // ⚠️ EMERGENCY STOP CHECK - Block execution if emergency stop is active
        if (get_transient('wp_gpt_rag_emergency_stop')) {
            error_log('WP GPT RAG Chat: Cron indexing blocked for post ' . $post_id . ' - Emergency stop active');
            return;
        }
        
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
     * Cron job to process indexing queue batch
     */
    public function cron_process_queue_batch() {
        try {
            // Get next batch of pending items from queue
            $queue_items = Indexing_Queue::get_next_batch(3); // Process 3 items at a time
            
            if (empty($queue_items)) {
                return; // No items to process
            }
            
            $indexing = new Indexing();
            
            foreach ($queue_items as $item) {
                // Mark as processing
                Indexing_Queue::mark_processing($item->post_id);
                
                try {
                    // Index the post
                    $result = $indexing->index_post($item->post_id);
                    
                    // Check if indexing was successful (no exception thrown means success)
                    if (isset($result['added']) || isset($result['updated']) || isset($result['message'])) {
                        // Mark as completed
                        Indexing_Queue::mark_completed($item->post_id);
                    } else {
                        // Mark as failed
                        Indexing_Queue::mark_failed($item->post_id, 'Indexing failed - no result returned');
                    }
                } catch (\Exception $e) {
                    // Mark as failed
                    Indexing_Queue::mark_failed($item->post_id, $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error processing queue batch: ' . $e->getMessage());
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
            
            // Run database migrations
            if (class_exists('\WP_GPT_RAG_Chat\Migration')) {
                Migration::run_migrations();
            }
            
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
                'enable_pii_masking' => true,
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
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wp_gpt_rag_sitemap_urls");
                $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wp_gpt_rag_content_gaps");
                
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
            chat_id varchar(64) NOT NULL,
            turn_number int(11) NOT NULL DEFAULT 1,
            role enum('user','assistant') NOT NULL DEFAULT 'user',
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            content text NOT NULL,
            response_latency int(11) DEFAULT NULL COMMENT 'Response time in milliseconds',
            sources_count int(11) DEFAULT 0,
            rag_sources longtext DEFAULT NULL COMMENT 'JSON array of sources',
            rating tinyint(1) DEFAULT NULL COMMENT '1 for thumbs up, -1 for thumbs down',
            tags varchar(500) DEFAULT NULL COMMENT 'Comma-separated tags',
            model_used varchar(100) DEFAULT NULL,
            tokens_used int(11) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY chat_id (chat_id),
            KEY user_id (user_id),
            KEY role (role),
            KEY created_at (created_at),
            KEY rating (rating),
            KEY model_used (model_used)
        ) $charset_collate;";
        
        // Vectors table
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        $vectors_sql = "CREATE TABLE $vectors_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            chunk_index int(11) NOT NULL,
            content_hash varchar(64) NOT NULL,
            content LONGTEXT DEFAULT NULL COMMENT 'Actual chunk content text',
            vector_id varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY vector_id (vector_id),
            KEY post_id (post_id),
            KEY content_hash (content_hash)
        ) $charset_collate;";
        
        // Sitemap URLs table (for fallback suggestions)
        $sitemap_table = $wpdb->prefix . 'wp_gpt_rag_sitemap_urls';
        $sitemap_sql = "CREATE TABLE $sitemap_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url varchar(2048) NOT NULL,
            title varchar(500) DEFAULT NULL,
            description text DEFAULT NULL,
            content_snippet text DEFAULT NULL,
            post_id bigint(20) DEFAULT NULL,
            priority decimal(2,1) DEFAULT 0.5,
            changefreq varchar(20) DEFAULT NULL,
            lastmod datetime DEFAULT NULL,
            embedding longtext DEFAULT NULL COMMENT 'JSON array of embedding vector',
            indexed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY url (url(191)),
            KEY post_id (post_id),
            KEY priority (priority),
            KEY indexed_at (indexed_at)
        ) $charset_collate;";
        
        // Content gaps table
        $gaps_table = $wpdb->prefix . 'wp_gpt_rag_content_gaps';
        $gaps_sql = "CREATE TABLE $gaps_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            query text NOT NULL,
            gap_reason enum('no_sources_found','low_similarity','no_answer_response') NOT NULL,
            frequency int(11) DEFAULT 1,
            last_seen datetime DEFAULT CURRENT_TIMESTAMP,
            status enum('open','resolved','ignored') DEFAULT 'open',
            resolved_at datetime DEFAULT NULL,
            resolved_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY gap_reason (gap_reason),
            KEY status (status),
            KEY last_seen (last_seen)
        ) $charset_collate;";
        
        // Indexing queue table
        $queue_table = $wpdb->prefix . 'wp_gpt_rag_indexing_queue';
        $queue_sql = "CREATE TABLE $queue_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_type varchar(20) NOT NULL,
            post_title varchar(500) NOT NULL,
            status enum('pending','processing','completed','failed') DEFAULT 'pending',
            priority int(11) DEFAULT 0,
            attempts int(11) DEFAULT 0,
            max_attempts int(11) DEFAULT 3,
            error_message text DEFAULT NULL,
            scheduled_at datetime DEFAULT CURRENT_TIMESTAMP,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_id (post_id),
            KEY status (status),
            KEY post_type (post_type),
            KEY priority (priority),
            KEY scheduled_at (scheduled_at),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            if (function_exists('dbDelta')) {
                dbDelta($logs_sql);
                dbDelta($vectors_sql);
                dbDelta($sitemap_sql);
                dbDelta($gaps_sql);
                dbDelta($queue_sql);
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
     * Handle get indexed items AJAX request with server-side pagination
     */
    public function handle_get_indexed_items() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to access this data.', 'wp-gpt-rag-chat')]);
        }
        
        global $wpdb;
        
        // Get pagination parameters
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = max(1, min(100, intval($_POST['per_page'] ?? 20))); // Limit to 100 max
        $offset = ($page - 1) * $per_page;
        
        // Get allowed post types from settings
        $settings = Settings::get_settings();
        $allowed_post_types = $settings['post_types'] ?? ['post', 'page'];
        $post_type_placeholders = implode(',', array_fill(0, count($allowed_post_types), '%s'));
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        $queue_table = $wpdb->prefix . 'wp_gpt_rag_indexing_queue';
        
        // First, get total count for pagination
        $total_count = $wpdb->get_var($wpdb->prepare(
            "
            SELECT COUNT(DISTINCT p.ID) + (
                SELECT COUNT(DISTINCT q.post_id) 
                FROM {$queue_table} q 
                WHERE q.status = 'pending'
                AND q.post_id NOT IN (
                    SELECT DISTINCT post_id FROM {$vectors_table}
                )
            )
            FROM {$wpdb->posts} p
            INNER JOIN {$vectors_table} lv ON p.ID = lv.post_id
            WHERE p.post_status IN ('publish', 'private')
            AND p.post_type IN ($post_type_placeholders)
            ",
            $allowed_post_types
        ));
        
        // Get indexed posts with pagination
        $indexed_posts = $wpdb->get_results($wpdb->prepare(
            "
            SELECT p.ID, p.post_title, p.post_type, p.post_modified, lv.indexed_at
            FROM {$wpdb->posts} p
            INNER JOIN (
                SELECT post_id, MAX(updated_at) AS indexed_at
                FROM {$vectors_table}
                GROUP BY post_id
            ) lv ON p.ID = lv.post_id
            WHERE p.post_status IN ('publish', 'private')
            AND p.post_type IN ($post_type_placeholders)
            ORDER BY lv.indexed_at DESC
            LIMIT %d OFFSET %d
            ",
            array_merge($allowed_post_types, [$per_page, $offset])
        ));
        
        $items = [];
        $indexed_post_ids = [];
        
        // Add already indexed posts
        foreach ($indexed_posts as $post_data) {
            $indexed_time = $post_data->indexed_at ? date('Y/m/d H:i:s', strtotime($post_data->indexed_at)) : null;
            $indexed_post_ids[] = $post_data->ID;
            
            $items[] = [
                'id' => $post_data->ID,
                'title' => $post_data->post_title,
                'type' => $post_data->post_type,
                'modified' => $post_data->post_modified,
                'indexed_time' => $indexed_time,
                'edit_url' => get_edit_post_link($post_data->ID),
                'pending' => false,
                'status' => 'ok'
            ];
        }
        
        // If we have space in this page, add pending items
        $remaining_slots = $per_page - count($items);
        if ($remaining_slots > 0) {
            $queue_items = Indexing_Queue::get_queue_items($remaining_slots, 0, 'pending');
            
            foreach ($queue_items as $queue_item) {
                // Skip if already indexed
                if (in_array($queue_item->post_id, $indexed_post_ids)) {
                    continue;
                }
                
                $items[] = [
                    'id' => $queue_item->post_id,
                    'title' => $queue_item->post_title,
                    'type' => $queue_item->post_type,
                    'modified' => $queue_item->created_at,
                    'indexed_time' => null,
                    'edit_url' => get_edit_post_link($queue_item->post_id),
                    'pending' => true,
                    'status' => 'pending'
                ];
            }
        }
        
        // Calculate pagination info
        $total_pages = ceil($total_count / $per_page);
        
        wp_send_json_success([
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_items' => $total_count,
                'total_pages' => $total_pages,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1
            ]
        ]);
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
    
    /**
     * Handle get logs AJAX request
     */
    public function handle_get_logs() {
        // Check permissions
        if (!RBAC::can_view_logs()) {
            wp_send_json_error(['message' => __('You do not have permission to view logs.', 'wp-gpt-rag-chat')]);
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wp_gpt_rag_chat_logs')) {
            wp_send_json_error(['message' => __('Security check failed.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            // Get recent logs (last 100 entries)
            $logs = $this->get_recent_logs(100);
            $stats = $this->get_log_stats();
            
            wp_send_json_success([
                'logs' => $logs,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle clear logs AJAX request
     */
    public function handle_clear_logs() {
        // Check permissions - only AIMS Manager can clear logs
        if (!RBAC::is_aims_manager()) {
            wp_send_json_error(['message' => __('You do not have permission to clear logs.', 'wp-gpt-rag-chat')]);
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wp_gpt_rag_chat_logs')) {
            wp_send_json_error(['message' => __('Security check failed.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $this->clear_all_logs();
            wp_send_json_success(['message' => __('Logs cleared successfully.', 'wp-gpt-rag-chat')]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Get recent logs
     */
    private function get_recent_logs($limit = 100) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
        
        $formatted_logs = [];
        foreach ($logs as $log) {
            $formatted_logs[] = [
                'timestamp' => date('Y-m-d H:i:s', strtotime($log->created_at)),
                'level' => $log->level,
                'message' => $log->message,
                'context' => $log->context
            ];
        }
        
        return $formatted_logs;
    }
    
    /**
     * Get log statistics
     */
    private function get_log_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as error,
                SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warning,
                SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as info
            FROM {$table_name}"
        );
        
        return [
            'total' => (int) $stats->total,
            'error' => (int) $stats->error,
            'warning' => (int) $stats->warning,
            'info' => (int) $stats->info
        ];
    }
    
    /**
     * Clear all logs
     */
    private function clear_all_logs() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $result = $wpdb->query("DELETE FROM {$table_name}");
        
        if ($result === false) {
            throw new Exception(__('Failed to clear logs.', 'wp-gpt-rag-chat'));
        }
    }
    
    /**
     * Handle get error context AJAX request
     */
    public function handle_get_error_context() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        // Emergency fix: No permission checks
        
        $error_id = intval($_POST['error_id']);
        
        if (!$error_id) {
            wp_send_json_error(['message' => __('Invalid error ID', 'wp-gpt-rag-chat')]);
        }
        
        global $wpdb;
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        
        $error = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$errors_table} WHERE id = %d",
            $error_id
        ));
        
        if (!$error) {
            wp_send_json_error(['message' => __('Error not found', 'wp-gpt-rag-chat')]);
        }
        
        $context = $error->context ? json_decode($error->context, true) : [];
        
        $formatted_context = "Error ID: {$error->id}\n";
        $formatted_context .= "Type: {$error->error_type}\n";
        $formatted_context .= "Service: {$error->api_service}\n";
        $formatted_context .= "Message: {$error->error_message}\n";
        $formatted_context .= "Time: {$error->created_at}\n";
        $formatted_context .= "User ID: " . ($error->user_id ?: 'Guest') . "\n";
        $formatted_context .= "IP: {$error->ip_address}\n\n";
        
        if (!empty($context)) {
            $formatted_context .= "Additional Context:\n";
            $formatted_context .= json_encode($context, JSON_PRETTY_PRINT);
        } else {
            $formatted_context .= "No additional context available.";
        }
        
        wp_send_json_success(['context' => $formatted_context]);
    }
    
    /**
     * Handle get usage context AJAX request
     */
    public function handle_get_usage_context() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        // Emergency fix: No permission checks
        
        $usage_id = intval($_POST['usage_id']);
        
        if (!$usage_id) {
            wp_send_json_error(['message' => __('Invalid usage ID', 'wp-gpt-rag-chat')]);
        }
        
        global $wpdb;
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
        $usage = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$usage_table} WHERE id = %d",
            $usage_id
        ));
        
        if (!$usage) {
            wp_send_json_error(['message' => __('Usage record not found', 'wp-gpt-rag-chat')]);
        }
        
        $context = $usage->context ? json_decode($usage->context, true) : [];
        
        $formatted_context = "Usage ID: {$usage->id}\n";
        $formatted_context .= "Service: {$usage->api_service}\n";
        $formatted_context .= "Endpoint: {$usage->endpoint}\n";
        $formatted_context .= "Tokens Used: " . ($usage->tokens_used ?: 'N/A') . "\n";
        $formatted_context .= "Cost: " . ($usage->cost ? '$' . number_format($usage->cost, 4) : 'N/A') . "\n";
        $formatted_context .= "Time: {$usage->created_at}\n";
        $formatted_context .= "User ID: " . ($usage->user_id ?: 'Guest') . "\n";
        $formatted_context .= "IP: {$usage->ip_address}\n\n";
        
        if (!empty($context)) {
            $formatted_context .= "Additional Context:\n";
            $formatted_context .= json_encode($context, JSON_PRETTY_PRINT);
        } else {
            $formatted_context .= "No additional context available.";
        }
        
        wp_send_json_success(['context' => $formatted_context]);
    }
    
    /**
     * Handle export start AJAX request
     */
    public function handle_start_export() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to export data.', 'wp-gpt-rag-chat')]);
        }
        
        $export_type = sanitize_text_field($_POST['export_type'] ?? '');
        $filters = $_POST['filters'] ?? [];
        
        try {
            $analytics = new \WP_GPT_RAG_Chat\Analytics();
            
            switch ($export_type) {
                case 'chat-logs':
                    $result = $analytics->export_to_csv($filters);
                    break;
                    
                case 'user-analytics':
                    $result = $this->export_user_analytics($filters);
                    break;
                    
                case 'indexing-data':
                    $result = $this->export_indexing_data($filters);
                    break;
                    
                case 'settings':
                    $result = $this->export_settings($filters);
                    break;
                    
                default:
                    wp_send_json_error(['message' => __('Invalid export type.', 'wp-gpt-rag-chat')]);
            }
            
            if ($result && isset($result['file_url'])) {
                // Save export record to history
                $record_count = 0;
                if (isset($result['record_count'])) {
                    $record_count = $result['record_count'];
                }
                
                $this->save_export_record(
                    $export_type,
                    $result['file_url'],
                    $result['file_path'] ?? '',
                    $record_count
                );
                
                wp_send_json_success([
                    'message' => __('Export completed successfully.', 'wp-gpt-rag-chat'),
                    'download_url' => $result['file_url']
                ]);
            } else {
                wp_send_json_error(['message' => __('Export failed. Please try again.', 'wp-gpt-rag-chat')]);
            }
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle export history AJAX request
     */
    public function handle_get_export_history() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to view export history.', 'wp-gpt-rag-chat')]);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_gpt_rag_chat_export_history';
        
        // Get export history
        $exports = $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 20"
        );
        
        if (empty($exports)) {
            $html = '<tr><td colspan="6" style="text-align: center; color: #646970;">' . __('No export history available.', 'wp-gpt-rag-chat') . '</td></tr>';
        } else {
            $html = '';
            foreach ($exports as $export) {
                $file_size = $export->file_size ? size_format($export->file_size) : '-';
                $download_link = $export->file_url ? '<a href="' . esc_url($export->file_url) . '" class="button button-small">' . __('Download', 'wp-gpt-rag-chat') . '</a>' : '-';
                
                $html .= sprintf(
                    '<tr>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%d</td>
                        <td>%s</td>
                        <td><span class="status-completed">%s</span></td>
                        <td>%s</td>
                    </tr>',
                    esc_html($export->export_type),
                    esc_html(date('Y-m-d H:i:s', strtotime($export->created_at))),
                    intval($export->record_count),
                    esc_html($file_size),
                    __('Completed', 'wp-gpt-rag-chat'),
                    $download_link
                );
            }
        }
        
        wp_send_json_success(['html' => $html]);
    }
    
    /**
     * Save export record to history
     */
    private function save_export_record($export_type, $file_url, $file_path, $record_count = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_gpt_rag_chat_export_history';
        
        $file_size = 0;
        if ($file_path && file_exists($file_path)) {
            $file_size = filesize($file_path);
        }
        
        $wpdb->insert(
            $table_name,
            [
                'export_type' => $export_type,
                'file_url' => $file_url,
                'file_path' => $file_path,
                'file_size' => $file_size,
                'record_count' => $record_count,
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%d', '%d', '%s']
        );
    }
    
    /**
     * Export user analytics data
     */
    private function export_user_analytics($filters) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Build query based on filters
        $where_conditions = ['1=1'];
        $query_params = [];
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = 'created_at >= %s';
            $query_params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = 'created_at <= %s';
            $query_params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT * FROM {$logs_table} WHERE {$where_clause} ORDER BY created_at DESC";
        
        if (!empty($query_params)) {
            $logs = $wpdb->get_results($wpdb->prepare($query, $query_params));
        } else {
            $logs = $wpdb->get_results($query);
        }
        
        // Create CSV content
        $csv_content = "ID,User ID,IP Address,Chat ID,Role,Content,Created At,Response Latency,Sources Count,Rating,Tags\n";
        
        foreach ($logs as $log) {
            $csv_content .= sprintf(
                "%d,%d,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->user_id ?: '',
                $log->ip_address ?: '',
                $log->chat_id ?: '',
                $log->role ?: '',
                '"' . str_replace('"', '""', $log->content ?: $log->query ?: $log->response ?: '') . '"',
                $log->created_at ?: '',
                $log->response_latency ?: '',
                $log->sources_count ?: '',
                $log->rating ?: '',
                '"' . str_replace('"', '""', $log->tags ?: '') . '"'
            );
        }
        
        // Save to temporary file
        $upload_dir = wp_upload_dir();
        $filename = 'user-analytics-export-' . date('Y-m-d-H-i-s') . '.csv';
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        if (file_put_contents($file_path, $csv_content) === false) {
            throw new \Exception(__('Failed to create export file.', 'wp-gpt-rag-chat'));
        }
        
        return [
            'file_url' => $upload_dir['url'] . '/' . $filename,
            'file_path' => $file_path,
            'record_count' => count($logs)
        ];
    }
    
    /**
     * Export indexing data
     */
    private function export_indexing_data($filters) {
        global $wpdb;
        
        $posts_table = $wpdb->prefix . 'posts';
        
        $query = "SELECT ID, post_title, post_type, post_status, post_modified 
                  FROM {$posts_table} 
                  WHERE post_type IN ('post', 'page') 
                  AND post_status = 'publish'
                  ORDER BY post_modified DESC";
        
        $posts = $wpdb->get_results($query);
        
        // Create CSV content
        $csv_content = "Post ID,Title,Type,Status,Last Modified,Indexed\n";
        
        foreach ($posts as $post) {
            $indexed = get_post_meta($post->ID, '_wp_gpt_rag_chat_indexed', true) ? 'Yes' : 'No';
            $csv_content .= sprintf(
                "%d,%s,%s,%s,%s,%s\n",
                $post->ID,
                '"' . str_replace('"', '""', $post->post_title) . '"',
                $post->post_type,
                $post->post_status,
                $post->post_modified,
                $indexed
            );
        }
        
        // Save to temporary file
        $upload_dir = wp_upload_dir();
        $filename = 'indexing-data-export-' . date('Y-m-d-H-i-s') . '.csv';
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        if (file_put_contents($file_path, $csv_content) === false) {
            throw new \Exception(__('Failed to create export file.', 'wp-gpt-rag-chat'));
        }
        
        return [
            'file_url' => $upload_dir['url'] . '/' . $filename,
            'file_path' => $file_path,
            'record_count' => count($posts)
        ];
    }
    
    /**
     * Export settings data
     */
    private function export_settings($filters) {
        $settings = \WP_GPT_RAG_Chat\Settings::get_settings();
        
        // Create CSV content
        $csv_content = "Setting,Value\n";
        
        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $csv_content .= sprintf(
                "%s,%s\n",
                $key,
                '"' . str_replace('"', '""', $value) . '"'
            );
        }
        
        // Save to temporary file
        $upload_dir = wp_upload_dir();
        $filename = 'settings-export-' . date('Y-m-d-H-i-s') . '.csv';
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        if (file_put_contents($file_path, $csv_content) === false) {
            throw new \Exception(__('Failed to create export file.', 'wp-gpt-rag-chat'));
        }
        
        return [
            'file_url' => $upload_dir['url'] . '/' . $filename,
            'file_path' => $file_path,
            'record_count' => count($settings)
        ];
    }
    
    /**
     * Handle diagnostics data AJAX request
     */
    public function handle_get_diagnostics_data() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to access diagnostics data.', 'wp-gpt-rag-chat')]);
        }
        
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
        try {
            // Active chats (last 24 hours)
            $active_chats = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT chat_id) FROM {$logs_table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d HOUR)",
                24
            ));
            
            // API calls today
            $api_calls_today = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$usage_table} WHERE DATE(created_at) = CURDATE()"
            ));
            
            // Error rate (last 24 hours)
            $total_requests = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$logs_table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d HOUR)",
                24
            ));
            
            $error_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$errors_table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d HOUR)",
                24
            ));
            
            $error_rate = $total_requests > 0 ? round(($error_count / $total_requests) * 100, 2) : 0;
            
            // Average response time (last 24 hours)
            $avg_response_time = $wpdb->get_var($wpdb->prepare(
                "SELECT AVG(response_latency) FROM {$logs_table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d HOUR) AND response_latency > 0",
                24
            ));
            
            // Indexed content count
            $indexed_content = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type IN ('post', 'page')"
            );
            
            wp_send_json_success([
                'active_chats' => intval($active_chats),
                'api_calls_today' => intval($api_calls_today),
                'error_rate' => $error_rate,
                'avg_response_time' => intval($avg_response_time),
                'indexed_content' => intval($indexed_content)
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle process status AJAX request
     */
    public function handle_get_process_status() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('You do not have permission to access process status.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            // Check if emergency stop is active
            $emergency_active = \WP_GPT_RAG_Chat\Emergency_Stop::is_active();
            
            // Check if indexing is running (simplified check)
            $indexing_running = wp_next_scheduled('wp_gpt_rag_chat_index_content');
            
            // Check if cleanup is scheduled
            $cleanup_scheduled = wp_next_scheduled('wp_gpt_rag_chat_cleanup_logs');
            
            wp_send_json_success([
                'indexing' => [
                    'status' => $indexing_running ? 'running' : 'idle',
                    'text' => $indexing_running ? __('Running', 'wp-gpt-rag-chat') : __('Idle', 'wp-gpt-rag-chat')
                ],
                'cleanup' => [
                    'status' => $cleanup_scheduled ? 'scheduled' : 'idle',
                    'text' => $cleanup_scheduled ? __('Scheduled', 'wp-gpt-rag-chat') : __('Idle', 'wp-gpt-rag-chat')
                ],
                'emergency' => [
                    'status' => $emergency_active ? 'stopped' : 'running',
                    'text' => $emergency_active ? __('Active', 'wp-gpt-rag-chat') : __('Normal', 'wp-gpt-rag-chat')
                ],
                'background' => [
                    'status' => 'running',
                    'text' => __('Active', 'wp-gpt-rag-chat')
                ]
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle start persistent indexing AJAX request
     */
    public function handle_start_persistent_indexing() {
        error_log('WP GPT RAG Chat: handle_start_persistent_indexing called');
        
        try {
            check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
            
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
            }
            
            $action = sanitize_text_field($_POST['action_type'] ?? 'index_all');
            $post_type = sanitize_text_field($_POST['post_type'] ?? 'all');
            
            // Check if Persistent_Indexing class exists
            if (!class_exists('WP_GPT_RAG_Chat\Persistent_Indexing')) {
                error_log('WP GPT RAG Chat: Persistent_Indexing class not found in start method');
                wp_send_json_error(['message' => __('Persistent indexing not available.', 'wp-gpt-rag-chat')]);
            }
            
            // Get total posts count
            $indexing = new Indexing();
            $total_posts = $indexing->get_total_posts_count($post_type);
            
            error_log("WP GPT RAG Chat: Starting persistent indexing - Action: {$action}, Post Type: {$post_type}, Total Posts: {$total_posts}");
            
            // Start persistent indexing
            $state = Persistent_Indexing::start_indexing($action, $post_type, $total_posts);
            
            wp_send_json_success([
                'message' => __('Persistent indexing started successfully.', 'wp-gpt-rag-chat'),
                'state' => $state
            ]);
            
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error in handle_start_persistent_indexing: ' . $e->getMessage());
            error_log('WP GPT RAG Chat: Error trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => $e->getMessage()]);
        } catch (\Error $e) {
            error_log('WP GPT RAG Chat: Fatal error in handle_start_persistent_indexing: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Fatal error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle get indexing status AJAX request
     */
    public function handle_get_indexing_status() {
        // Add error logging
        error_log('WP GPT RAG Chat: handle_get_indexing_status called');
        
        try {
            check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
            
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
            }
            
            // Check if Persistent_Indexing class exists
            if (!class_exists('WP_GPT_RAG_Chat\Persistent_Indexing')) {
                error_log('WP GPT RAG Chat: Persistent_Indexing class not found');
                wp_send_json_success([
                    'status' => 'idle',
                    'message' => __('Persistent indexing not available.', 'wp-gpt-rag-chat'),
                    'is_running' => false
                ]);
            }
            
            $state = Persistent_Indexing::get_indexing_state();
            
            if (!$state) {
                wp_send_json_success([
                    'status' => 'idle',
                    'message' => __('No indexing in progress.', 'wp-gpt-rag-chat'),
                    'is_running' => false
                ]);
            }
            
            $progress_percentage = Persistent_Indexing::get_progress_percentage();
            $progress_text = Persistent_Indexing::get_progress_text();
            
            wp_send_json_success([
                'status' => $state['status'],
                'state' => $state,
                'progress_percentage' => $progress_percentage,
                'progress_text' => $progress_text,
                'is_running' => $state['status'] === 'running'
            ]);
            
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error in handle_get_indexing_status: ' . $e->getMessage());
            error_log('WP GPT RAG Chat: Error trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => $e->getMessage()]);
        } catch (\Error $e) {
            error_log('WP GPT RAG Chat: Fatal error in handle_get_indexing_status: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Fatal error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle cancel persistent indexing AJAX request
     */
    public function handle_cancel_persistent_indexing() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        try {
            $state = Persistent_Indexing::cancel_indexing();
            
            wp_send_json_success([
                'message' => __('Indexing cancelled successfully.', 'wp-gpt-rag-chat'),
                'state' => $state
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle get persistent pending posts AJAX request
     */
    public function handle_get_persistent_pending_posts() {
        error_log('WP GPT RAG Chat: handle_get_persistent_pending_posts called');
        
        try {
            check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
            
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
            }
            
            $post_type = sanitize_text_field($_POST['post_type'] ?? 'all');
            $offset = intval($_POST['offset'] ?? 0);
            $limit = intval($_POST['limit'] ?? 10);
            
            // Use the new indexing queue system instead of old persistent indexing
            $queue_items = Indexing_Queue::get_queue_items($limit, $offset, 'pending', $post_type);
            
            $items = [];
            foreach ($queue_items as $item) {
                $items[] = [
                    'id' => $item->post_id,
                    'title' => $item->post_title,
                    'type' => $item->post_type,
                    'edit_url' => get_edit_post_link($item->post_id)
                ];
            }
            
            wp_send_json_success([
                'items' => $items,
                'count' => count($items)
            ]);
            
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error in handle_get_persistent_pending_posts: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        } catch (\Error $e) {
            error_log('WP GPT RAG Chat: Fatal error in handle_get_persistent_pending_posts: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Fatal error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle clear newly indexed AJAX request
     */
    public function handle_clear_newly_indexed() {
        error_log('WP GPT RAG Chat: handle_clear_newly_indexed called');
        
        try {
            check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
            
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
            }
            
            // Check if Persistent_Indexing class exists
            if (!class_exists('WP_GPT_RAG_Chat\Persistent_Indexing')) {
                error_log('WP GPT RAG Chat: Persistent_Indexing class not found in clear newly indexed method');
                wp_send_json_error(['message' => __('Persistent indexing not available.', 'wp-gpt-rag-chat')]);
            }
            
            $state = Persistent_Indexing::get_indexing_state();
            if ($state && isset($state['newly_indexed'])) {
                $state['newly_indexed'] = []; // Clear the newly indexed items
                set_transient(Persistent_Indexing::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
                error_log('WP GPT RAG Chat: Cleared newly indexed items from state');
            }
            
            wp_send_json_success(['message' => __('Newly indexed items cleared.', 'wp-gpt-rag-chat')]);
            
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error in handle_clear_newly_indexed: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        } catch (\Error $e) {
            error_log('WP GPT RAG Chat: Fatal error in handle_clear_newly_indexed: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Fatal error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle get queue status AJAX request
     */
    public function handle_get_queue_status() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        $stats = Indexing_Queue::get_queue_stats($post_type);
        
        wp_send_json_success($stats);
    }
    
    /**
     * Handle clear queue AJAX request
     */
    public function handle_clear_queue() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $status = sanitize_text_field($_POST['status'] ?? 'all');
        
        if ($status === 'completed') {
            $result = Indexing_Queue::clear_completed();
            $message = __('Completed items cleared from queue.', 'wp-gpt-rag-chat');
        } else {
            $result = Indexing_Queue::clear_all();
            $message = __('All items cleared from queue.', 'wp-gpt-rag-chat');
        }
        
        wp_send_json_success(['message' => $message, 'cleared' => $result]);
    }
    
    /**
     * Handle retry failed AJAX request
     */
    public function handle_retry_failed() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        global $wpdb;
        $table = Indexing_Queue::get_table_name();
        
        $result = $wpdb->update(
            $table,
            [
                'status' => 'pending',
                'attempts' => 0,
                'error_message' => null,
                'scheduled_at' => current_time('mysql')
            ],
            ['status' => 'failed'],
            ['%s', '%d', '%s', '%s'],
            ['%s']
        );
        
        wp_send_json_success([
            'message' => sprintf(__('Retried %d failed items.', 'wp-gpt-rag-chat'), $result),
            'retried' => $result
        ]);
    }
    
    /**
     * Handle get queue items AJAX request
     */
    public function handle_get_queue_items() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $limit = intval($_POST['limit'] ?? 20);
        $offset = intval($_POST['offset'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        
        $items = Indexing_Queue::get_queue_items($limit, $offset, $status, $post_type);
        
        wp_send_json_success($items);
    }
    
    /**
     * Handle process queue AJAX request
     */
    public function handle_process_queue() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        
        $batch_size = intval($_POST['batch_size'] ?? 3);
        $post_type = sanitize_text_field($_POST['post_type'] ?? '');
        
        try {
            $indexing = new Indexing();
            $processed = 0;
            $errors = [];
            
            // Get next batch from queue
            $queue_items = Indexing_Queue::get_next_batch($batch_size, $post_type);
            
            foreach ($queue_items as $item) {
                try {
                    // Mark as processing
                    Indexing_Queue::mark_processing($item->post_id);
                    
                    // Process the post
                    $result = $indexing->index_post($item->post_id, true);
                    
                    // Mark as completed
                    Indexing_Queue::mark_completed($item->post_id);
                    $processed++;
                    
                } catch (\Exception $e) {
                    // Mark as failed
                    Indexing_Queue::mark_failed($item->post_id, $e->getMessage());
                    $errors[] = sprintf(__('Post %d: %s', 'wp-gpt-rag-chat'), $item->post_id, $e->getMessage());
                }
            }
            
            wp_send_json_success([
                'message' => sprintf(__('Processed %d items from queue.', 'wp-gpt-rag-chat'), $processed),
                'processed' => $processed,
                'errors' => $errors,
                'remaining' => Indexing_Queue::get_queue_stats($post_type)['pending']
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Get post details by ID (admin helper)
     */
    public function handle_get_post_by_id() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid post ID', 'wp-gpt-rag-chat')]);
        }
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(['message' => __('Post not found', 'wp-gpt-rag-chat')]);
        }
        wp_send_json_success([
            'id' => $post->ID,
            'title' => $post->post_title,
            'type' => $post->post_type,
            'status' => $post->post_status,
            'url' => get_permalink($post->ID),
            'view_url' => get_permalink($post->ID),
            'edit_url' => get_edit_post_link($post->ID),
            'is_indexed' => $this->is_post_indexed($post->ID)
        ]);
    }

    /**
     * Enqueue a specific post to indexing queue
     */
    public function handle_enqueue_post() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid post ID', 'wp-gpt-rag-chat')]);
        }
        $ok = Indexing_Queue::add_to_queue($post_id);
        if ($ok) {
            wp_send_json_success(['message' => sprintf(__('Post %d added to queue.', 'wp-gpt-rag-chat'), $post_id)]);
        }
        wp_send_json_error(['message' => __('Could not add to queue (maybe already pending/completed).', 'wp-gpt-rag-chat')]);
    }

    /**
     * Reindex a specific post immediately (one-off)
     */
    public function handle_reindex_post_now() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wp-gpt-rag-chat')]);
        }
        $post_id = intval($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error(['message' => __('Invalid post ID', 'wp-gpt-rag-chat')]);
        }
        try {
            $indexing = new Indexing();
            $res = $indexing->reindex_post($post_id);
            wp_send_json_success([
                'message' => sprintf(__('Reindexed post %d. Added: %d, Updated: %d, Removed: %d, Skipped: %d', 'wp-gpt-rag-chat'),
                    $post_id, intval($res['added'] ?? 0), intval($res['updated'] ?? 0), intval($res['removed'] ?? 0), intval($res['skipped'] ?? 0)
                )
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

}

<?php

namespace WP_GPT_RAG_Chat;

/**
 * Role-Based Access Control (RBAC) for Nuwab AI Assistant
 * 
 * Implements AIMS Manager (admin) and Log Viewer (editor) roles
 */
class RBAC {
    
    /**
     * Initialize RBAC system
     */
    public function __construct() {
        add_action('init', [$this, 'add_custom_capabilities']);
        add_action('admin_init', [$this, 'check_user_permissions']);
    }
    
    /**
     * Add custom capabilities for AIMS roles
     */
    public function add_custom_capabilities() {
        // Get administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            // AIMS Manager capabilities (full access)
            $admin_role->add_cap('wp_gpt_rag_aims_manager');
            $admin_role->add_cap('wp_gpt_rag_full_access');
            $admin_role->add_cap('wp_gpt_rag_view_logs');
            $admin_role->add_cap('wp_gpt_rag_manage_settings');
            $admin_role->add_cap('wp_gpt_rag_manage_indexing');
            $admin_role->add_cap('wp_gpt_rag_manage_analytics');
            $admin_role->add_cap('wp_gpt_rag_manage_diagnostics');
            $admin_role->add_cap('wp_gpt_rag_manage_export');
            $admin_role->add_cap('wp_gpt_rag_manage_about');
        }
        
        // Get editor role
        $editor_role = get_role('editor');
        if ($editor_role) {
            // Log Viewer capabilities (limited access)
            $editor_role->add_cap('wp_gpt_rag_log_viewer');
            $editor_role->add_cap('wp_gpt_rag_view_logs');
        }
        
        // Also add capabilities to other roles that might need them
        $author_role = get_role('author');
        if ($author_role) {
            $author_role->add_cap('wp_gpt_rag_view_logs');
        }
        
        $contributor_role = get_role('contributor');
        if ($contributor_role) {
            $contributor_role->add_cap('wp_gpt_rag_view_logs');
        }
    }
    
    /**
     * Check if current user is AIMS Manager
     */
    public static function is_aims_manager() {
        return current_user_can('wp_gpt_rag_aims_manager') || current_user_can('manage_options') || current_user_can('administrator');
    }
    
    /**
     * Check if current user is Log Viewer
     */
    public static function is_log_viewer() {
        return current_user_can('wp_gpt_rag_log_viewer') || current_user_can('wp_gpt_rag_view_logs') || current_user_can('edit_posts');
    }
    
    /**
     * Check if current user has full access
     */
    public static function has_full_access() {
        return self::is_aims_manager();
    }
    
    /**
     * Check if current user can view logs
     */
    public static function can_view_logs() {
        return self::is_aims_manager() || self::is_log_viewer() || current_user_can('manage_options') || current_user_can('edit_posts');
    }
    
    /**
     * Check if current user can manage settings
     */
    public static function can_manage_settings() {
        return self::is_aims_manager();
    }
    
    /**
     * Check if current user can manage indexing
     */
    public static function can_manage_indexing() {
        return self::is_aims_manager();
    }
    
    /**
     * Check if current user can manage analytics
     */
    public static function can_manage_analytics() {
        return self::is_aims_manager() || self::is_log_viewer();
    }
    
    /**
     * Check if current user can manage diagnostics
     */
    public static function can_manage_diagnostics() {
        return self::is_aims_manager();
    }
    
    /**
     * Check if current user can manage export
     */
    public static function can_manage_export() {
        return self::is_aims_manager();
    }
    
    /**
     * Check if current user can view about page
     */
    public static function can_view_about() {
        return self::is_aims_manager();
    }
    
    /**
     * Get user role display name
     */
    public static function get_user_role_display() {
        if (current_user_can('manage_options') || current_user_can('administrator')) {
            return __('Administrator', 'wp-gpt-rag-chat');
        } elseif (self::is_aims_manager()) {
            return __('AIMS Manager', 'wp-gpt-rag-chat');
        } elseif (self::is_log_viewer()) {
            return __('Log Viewer', 'wp-gpt-rag-chat');
        } else {
            return __('No Access', 'wp-gpt-rag-chat');
        }
    }
    
    /**
     * Check user permissions and redirect if needed
     */
    public function check_user_permissions() {
        // Only check on plugin admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'wp-gpt-rag-chat') !== 0) {
            return;
        }
        
        $current_page = $_GET['page'];
        
        // Check permissions based on page
        switch ($current_page) {
            case 'wp-gpt-rag-chat-dashboard':
                if (!self::can_view_logs()) {
                    wp_die(__('You do not have permission to access this page.', 'wp-gpt-rag-chat'));
                }
                break;
                
            case 'wp-gpt-rag-chat-settings':
            case 'wp-gpt-rag-chat-indexing':
            case 'wp-gpt-rag-chat-analytics':
            case 'wp-gpt-rag-chat-diagnostics':
            case 'wp-gpt-rag-chat-cron-status':
            case 'wp-gpt-rag-chat-user-analytics':
            case 'wp-gpt-rag-chat-export':
            case 'wp-gpt-rag-chat-about':
                if (!self::can_manage_settings()) {
                    wp_die(__('You do not have permission to access this page.', 'wp-gpt-rag-chat'));
                }
                break;
                
            case 'wp-gpt-rag-chat-logs':
                if (!self::can_view_logs()) {
                    wp_die(__('You do not have permission to access this page.', 'wp-gpt-rag-chat'));
                }
                break;
        }
    }
    
    /**
     * Get menu items based on user role
     */
    public static function get_allowed_menu_items() {
        $menu_items = [];
        
        // Dashboard - available to all authorized users
        if (self::can_view_logs()) {
            $menu_items['dashboard'] = [
                'title' => __('Dashboard', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-dashboard',
                'capability' => 'wp_gpt_rag_view_logs'
            ];
        }
        
        // AIMS Manager only items
        if (self::is_aims_manager()) {
            $menu_items['settings'] = [
                'title' => __('Settings', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-settings',
                'capability' => 'wp_gpt_rag_manage_settings'
            ];
            
            $menu_items['indexing'] = [
                'title' => __('Indexing', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-indexing',
                'capability' => 'wp_gpt_rag_manage_indexing'
            ];
            
            $menu_items['analytics'] = [
                'title' => __('Analytics & Logs', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-analytics',
                'capability' => 'wp_gpt_rag_manage_analytics'
            ];
            
            $menu_items['diagnostics'] = [
                'title' => __('Diagnostics', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-diagnostics',
                'capability' => 'wp_gpt_rag_manage_diagnostics'
            ];
            
            $menu_items['cron-status'] = [
                'title' => __('Cron Status', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-cron-status',
                'capability' => 'wp_gpt_rag_manage_diagnostics'
            ];
            
            $menu_items['user-analytics'] = [
                'title' => __('User Analytics', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-user-analytics',
                'capability' => 'wp_gpt_rag_manage_analytics'
            ];
            
            $menu_items['export'] = [
                'title' => __('Export Data', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-export',
                'capability' => 'wp_gpt_rag_manage_export'
            ];
            
            $menu_items['about'] = [
                'title' => __('About Plugin', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-about',
                'capability' => 'wp_gpt_rag_manage_about'
            ];
        }
        
        // Log Viewer specific items
        if (self::is_log_viewer() && !self::is_aims_manager()) {
            $menu_items['logs'] = [
                'title' => __('View Logs', 'wp-gpt-rag-chat'),
                'page' => 'wp-gpt-rag-chat-logs',
                'capability' => 'wp_gpt_rag_view_logs'
            ];
        }
        
        return $menu_items;
    }
}

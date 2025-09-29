<?php

namespace WP_GPT_RAG_Chat;

/**
 * Admin functionality class
 */
class Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', [$this, 'init']);
        add_action('admin_notices', [$this, 'admin_notices']);
    }
    
    /**
     * Initialize admin functionality
     */
    public function init() {
        // Add bulk actions to post list
        add_filter('bulk_actions-edit-post', [$this, 'add_bulk_actions']);
        add_filter('bulk_actions-edit-page', [$this, 'add_bulk_actions']);
        add_action('handle_bulk_actions-edit-post', [$this, 'handle_bulk_actions'], 10, 3);
        add_action('handle_bulk_actions-edit-page', [$this, 'handle_bulk_actions'], 10, 3);
        
        // Add custom post type support
        $this->add_cpt_support();
    }
    
    /**
     * Add bulk actions to post lists
     */
    public function add_bulk_actions($bulk_actions) {
        $bulk_actions['wp_gpt_rag_chat_include'] = __('Include in RAG Chat', 'wp-gpt-rag-chat');
        $bulk_actions['wp_gpt_rag_chat_exclude'] = __('Exclude from RAG Chat', 'wp-gpt-rag-chat');
        $bulk_actions['wp_gpt_rag_chat_reindex'] = __('Force Reindex', 'wp-gpt-rag-chat');
        
        return $bulk_actions;
    }
    
    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        if (!in_array($doaction, ['wp_gpt_rag_chat_include', 'wp_gpt_rag_chat_exclude', 'wp_gpt_rag_chat_reindex'])) {
            return $redirect_to;
        }
        
        if (!current_user_can('edit_posts')) {
            return $redirect_to;
        }
        
        $processed = 0;
        $errors = [];
        
        foreach ($post_ids as $post_id) {
            try {
                switch ($doaction) {
                    case 'wp_gpt_rag_chat_include':
                        update_post_meta($post_id, '_wp_gpt_rag_chat_include', true);
                        $processed++;
                        break;
                        
                    case 'wp_gpt_rag_chat_exclude':
                        update_post_meta($post_id, '_wp_gpt_rag_chat_include', false);
                        $processed++;
                        break;
                        
                    case 'wp_gpt_rag_chat_reindex':
                        $indexing = new Indexing();
                        $indexing->reindex_post($post_id);
                        $processed++;
                        break;
                }
            } catch (Exception $e) {
                $errors[] = sprintf(__('Error processing post %d: %s', 'wp-gpt-rag-chat'), $post_id, $e->getMessage());
            }
        }
        
        // Add query args for admin notice
        $redirect_to = add_query_arg([
            'wp_gpt_rag_chat_processed' => $processed,
            'wp_gpt_rag_chat_errors' => count($errors)
        ], $redirect_to);
        
        return $redirect_to;
    }
    
    /**
     * Add custom post type support
     */
    private function add_cpt_support() {
        $post_types = get_post_types(['public' => true], 'names');
        
        foreach ($post_types as $post_type) {
            if ($post_type === 'attachment') {
                continue;
            }
            
            add_filter("bulk_actions-edit-{$post_type}", [$this, 'add_bulk_actions']);
            add_action("handle_bulk_actions-edit-{$post_type}", [$this, 'handle_bulk_actions'], 10, 3);
        }
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        if (isset($_GET['wp_gpt_rag_chat_processed'])) {
            $processed = intval($_GET['wp_gpt_rag_chat_processed']);
            $errors = intval($_GET['wp_gpt_rag_chat_errors'] ?? 0);
            
            if ($processed > 0) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . sprintf(_n(
                    '%d post processed successfully.',
                    '%d posts processed successfully.',
                    $processed,
                    'wp-gpt-rag-chat'
                ), $processed) . '</p>';
                echo '</div>';
            }
            
            if ($errors > 0) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . sprintf(_n(
                    '%d error occurred.',
                    '%d errors occurred.',
                    $errors,
                    'wp-gpt-rag-chat'
                ), $errors) . '</p>';
                echo '</div>';
            }
        }
        
        // Check if settings are configured
        $settings = Settings::get_settings();
        if (empty($settings['openai_api_key']) || empty($settings['pinecone_api_key'])) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>';
            echo sprintf(
                __('WP GPT RAG Chat needs to be configured. <a href="%s">Go to settings</a>.', 'wp-gpt-rag-chat'),
                admin_url('admin.php?page=wp-gpt-rag-chat-settings')
            );
            echo '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Get post indexing status
     */
    public static function get_post_indexing_status($post_id) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$vectors_table} WHERE post_id = %d",
            $post_id
        ));
        
        $include = get_post_meta($post_id, '_wp_gpt_rag_chat_include', true);
        
        return [
            'include' => $include === '' ? true : (bool) $include,
            'vector_count' => intval($count),
            'last_updated' => $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(updated_at) FROM {$vectors_table} WHERE post_id = %d",
                $post_id
            ))
        ];
    }
    
    /**
     * Get indexing statistics
     */
    public static function get_indexing_stats() {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $stats = [
            'total_vectors' => $wpdb->get_var("SELECT COUNT(*) FROM {$vectors_table}"),
            'total_posts' => $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$vectors_table}"),
            'recent_activity' => $wpdb->get_var("SELECT COUNT(*) FROM {$vectors_table} WHERE updated_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"),
        ];
        
        // Get post type breakdown
        $post_types = $wpdb->get_results("
            SELECT p.post_type, COUNT(v.id) as vector_count
            FROM {$vectors_table} v
            JOIN {$wpdb->posts} p ON v.post_id = p.ID
            GROUP BY p.post_type
        ");
        
        $stats['by_post_type'] = [];
        foreach ($post_types as $pt) {
            $stats['by_post_type'][$pt->post_type] = intval($pt->vector_count);
        }
        
        return $stats;
    }
}

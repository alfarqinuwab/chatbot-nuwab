<?php

namespace WP_GPT_RAG_Chat;

/**
 * Emergency Stop System
 * Provides safeguards to prevent runaway indexing
 */
class Emergency_Stop {
    
    const TRANSIENT_KEY = 'wp_gpt_rag_emergency_stop';
    const CRON_LIMIT_WARNING = 100;
    const CRON_LIMIT_AUTO_STOP = 500;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add admin notice for high cron count
        add_action('admin_notices', [$this, 'cron_warning_notice']);
        
        // Auto-stop if cron jobs exceed limit
        add_action('admin_init', [$this, 'check_cron_limit']);
        
        // Add emergency stop button to admin bar
        add_action('admin_bar_menu', [$this, 'add_emergency_stop_button'], 999);
        
        // Handle emergency stop AJAX
        add_action('wp_ajax_wp_gpt_rag_emergency_stop', [$this, 'handle_emergency_stop']);
        add_action('wp_ajax_wp_gpt_rag_resume_indexing', [$this, 'handle_resume_indexing']);
    }
    
    /**
     * Check if emergency stop is active
     */
    public static function is_active() {
        return (bool) get_transient(self::TRANSIENT_KEY);
    }
    
    /**
     * Activate emergency stop
     */
    public static function activate($duration = HOUR_IN_SECONDS) {
        set_transient(self::TRANSIENT_KEY, true, $duration);
        
        // Clear all scheduled indexing cron jobs
        self::clear_all_cron_jobs();
        
        // Log the event
        error_log('WP GPT RAG Chat: Emergency stop activated');
    }
    
    /**
     * Deactivate emergency stop
     */
    public static function deactivate() {
        delete_transient(self::TRANSIENT_KEY);
        error_log('WP GPT RAG Chat: Emergency stop deactivated');
    }
    
    /**
     * Clear all scheduled indexing cron jobs
     */
    public static function clear_all_cron_jobs() {
        $crons = _get_cron_array();
        $cleared = 0;
        
        if (!empty($crons)) {
            foreach ($crons as $timestamp => $cron) {
                if (!empty($cron)) {
                    foreach ($cron as $hook => $events) {
                        if ($hook === 'wp_gpt_rag_chat_index_content') {
                            foreach ($events as $event) {
                                $args = isset($event['args']) ? $event['args'] : [];
                                wp_unschedule_event($timestamp, $hook, $args);
                                $cleared++;
                            }
                        }
                    }
                }
            }
        }
        
        return $cleared;
    }
    
    /**
     * Get count of scheduled indexing cron jobs
     */
    public static function get_cron_count() {
        $crons = _get_cron_array();
        $count = 0;
        
        if (!empty($crons)) {
            foreach ($crons as $timestamp => $cron) {
                if (!empty($cron)) {
                    foreach ($cron as $hook => $events) {
                        if ($hook === 'wp_gpt_rag_chat_index_content') {
                            $count += count($events);
                        }
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Check cron job limit and auto-stop if needed
     */
    public function check_cron_limit() {
        // Only check on admin pages
        if (!is_admin()) {
            return;
        }
        
        $count = self::get_cron_count();
        
        // Auto-stop if limit exceeded
        if ($count >= self::CRON_LIMIT_AUTO_STOP && !self::is_active()) {
            self::activate();
            
            // Set admin notice
            set_transient('wp_gpt_rag_auto_stop_notice', $count, 60);
        }
    }
    
    /**
     * Display admin warning for high cron count
     */
    public function cron_warning_notice() {
        // Check if auto-stopped
        $auto_stop_count = get_transient('wp_gpt_rag_auto_stop_notice');
        if ($auto_stop_count) {
            ?>
            <div class="notice notice-error is-dismissible">
                <h3>🚨 WP GPT RAG Chat - Emergency Stop Activated</h3>
                <p>
                    <strong>Automatic emergency stop triggered!</strong> 
                    The system detected <strong><?php echo number_format($auto_stop_count); ?> queued indexing jobs</strong> 
                    and automatically stopped all indexing to prevent system overload.
                </p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button button-primary">
                        Go to Indexing Page
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-settings'); ?>" class="button">
                        Check Settings
                    </a>
                </p>
            </div>
            <?php
            return;
        }
        
        // Warning for high cron count
        if (self::is_active()) {
            $screen = get_current_screen();
            if ($screen && strpos($screen->id, 'wp-gpt-rag-chat') !== false) {
                ?>
                <div class="notice notice-warning">
                    <h3>⚠️ Emergency Stop Active</h3>
                    <p>All indexing is currently stopped. Click "Resume Indexing" to continue.</p>
                </div>
                <?php
            }
        } else {
            $count = self::get_cron_count();
            if ($count >= self::CRON_LIMIT_WARNING) {
                $screen = get_current_screen();
                if ($screen && strpos($screen->id, 'wp-gpt-rag-chat') !== false) {
                    ?>
                    <div class="notice notice-warning">
                        <h3>⚠️ High Indexing Queue</h3>
                        <p>
                            There are <strong><?php echo number_format($count); ?> indexing jobs</strong> queued. 
                            This may slow down your site. Consider using the Emergency Stop button if needed.
                        </p>
                    </div>
                    <?php
                }
            }
        }
    }
    
    /**
     * Add emergency stop button to admin bar
     */
    public function add_emergency_stop_button($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $is_active = self::is_active();
        $count = self::get_cron_count();
        
        $wp_admin_bar->add_node([
            'id'    => 'wp_gpt_rag_emergency',
            'title' => $is_active 
                ? '<span style="color: #00a32a;">● RAG Indexing: STOPPED</span>' 
                : ($count > self::CRON_LIMIT_WARNING 
                    ? '<span style="color: #dba617;">⚠ RAG: ' . $count . ' jobs</span>'
                    : '<span>RAG Indexing</span>'),
            'href'  => admin_url('admin.php?page=wp-gpt-rag-chat-indexing'),
        ]);
        
        if ($is_active) {
            $wp_admin_bar->add_node([
                'id'     => 'wp_gpt_rag_resume',
                'parent' => 'wp_gpt_rag_emergency',
                'title'  => '▶ Resume Indexing',
                'href'   => '#',
                'meta'   => [
                    'onclick' => 'wpGptRagResumeIndexing(); return false;',
                ],
            ]);
        } else {
            $wp_admin_bar->add_node([
                'id'     => 'wp_gpt_rag_stop',
                'parent' => 'wp_gpt_rag_emergency',
                'title'  => '🛑 Emergency Stop',
                'href'   => '#',
                'meta'   => [
                    'onclick' => 'wpGptRagEmergencyStop(); return false;',
                ],
            ]);
        }
        
        $wp_admin_bar->add_node([
            'id'     => 'wp_gpt_rag_monitor',
            'parent' => 'wp_gpt_rag_emergency',
            'title'  => '📊 Live Monitor',
            'href'   => plugins_url('LIVE-MONITOR.php', dirname(__FILE__) . '/../chatbot-nuwab.php'),
        ]);
    }
    
    /**
     * Handle emergency stop AJAX
     */
    public function handle_emergency_stop() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        $cleared = self::activate();
        
        wp_send_json_success([
            'message' => 'Emergency stop activated. ' . $cleared . ' jobs cleared.',
            'cleared' => $cleared
        ]);
    }
    
    /**
     * Handle resume indexing AJAX
     */
    public function handle_resume_indexing() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        self::deactivate();
        
        wp_send_json_success([
            'message' => 'Indexing resumed. Auto-indexing will work normally now.'
        ]);
    }
}

// Initialize
new Emergency_Stop();


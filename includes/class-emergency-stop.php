<?php

namespace WP_GPT_RAG_Chat;

/**
 * Emergency Stop System
 * Provides safeguards to prevent runaway indexing
 */
class Emergency_Stop {
    
    const TRANSIENT_KEY = 'wp_gpt_rag_emergency_stop';
    const ACK_TRANSIENT_KEY = 'wp_gpt_rag_emergency_stop_ack';
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
        add_action('wp_ajax_wp_gpt_rag_confirm_stop', [$this, 'handle_confirm_stop']);
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
        // Clear any previous acknowledgement so notice shows again for a new stop
        delete_transient(self::ACK_TRANSIENT_KEY);
        
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
        // Clear acknowledgement once indexing is resumed
        delete_transient(self::ACK_TRANSIENT_KEY);
        error_log('WP GPT RAG Chat: Emergency stop deactivated');
    }

    /**
     * Check if the current emergency stop has been acknowledged by an admin
     */
    public static function is_acknowledged() {
        return (bool) get_transient(self::ACK_TRANSIENT_KEY);
    }

    /**
     * Acknowledge the emergency stop (hide the notice until resume)
     */
    public static function acknowledge($duration = WEEK_IN_SECONDS) {
        set_transient(self::ACK_TRANSIENT_KEY, true, $duration);
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
            // If acknowledged, don't show the banner again until resume
            if (self::is_acknowledged()) {
                return;
            }
            $screen = get_current_screen();
            // Only show on indexing page, not on settings page
            if ($screen && strpos($screen->id, 'wp-gpt-rag-chat') !== false && strpos($screen->id, 'settings') === false) {
                ?>
                <div class="notice notice-warning" style="border-left-color: #d63638; background: #fcf0f1; padding: 25px 20px; margin: 20px 0;">
                    <h3 style="color: #d63638; margin-top: 0; margin-bottom: 15px;">⚠️ Emergency Stop Active</h3>
                    <p style="margin-bottom: 20px; font-size: 16px;">All indexing is currently stopped. Choose an action below:</p>
                    <div style="margin-top: 20px; padding-top: 10px;">
                        <button type="button" id="resume-indexing-btn" class="button button-primary" style="background: #00a32a; border-color: #00a32a; margin-right: 15px; padding: 8px 16px; font-size: 14px;">
                            <span class="dashicons dashicons-controls-play" style="margin-top: 3px;"></span>
                            Resume Indexing
                        </button>
                        <button type="button" id="confirm-stop-btn" class="button button-secondary" style="background: #d63638; border-color: #d63638; color: white; padding: 8px 16px; font-size: 14px;">
                            <span class="dashicons dashicons-yes" style="margin-top: 3px;"></span>
                            Confirm Stop
                        </button>
                    </div>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    // Resume Indexing button
                    $('#resume-indexing-btn').on('click', function() {
                        if (!confirm('Are you sure you want to resume indexing? This will re-enable all indexing operations.')) {
                            return;
                        }
                        
                        $(this).prop('disabled', true).text('Resuming...');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wp_gpt_rag_resume_indexing',
                                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    location.reload();
                                } else {
                                    alert('Error: ' + (response.data.message || 'Failed to resume indexing'));
                                    $('#resume-indexing-btn').prop('disabled', false).html('<span class="dashicons dashicons-controls-play" style="margin-top: 3px;"></span> Resume Indexing');
                                }
                            },
                            error: function() {
                                alert('Error: Failed to resume indexing. Please try again.');
                                $('#resume-indexing-btn').prop('disabled', false).html('<span class="dashicons dashicons-controls-play" style="margin-top: 3px;"></span> Resume Indexing');
                            }
                        });
                    });
                    
                    // Confirm Stop button
                    $('#confirm-stop-btn').on('click', function() {
                        if (!confirm('Are you sure you want to confirm the emergency stop? This will keep indexing disabled until manually resumed.')) {
                            return;
                        }
                        
                        var $btn = $(this);
                        $btn.prop('disabled', true).text('Confirming...');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'wp_gpt_rag_confirm_stop',
                                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
                            },
                            success: function(response) {
                                // Hide the notice after confirmation
                                $('.notice.notice-warning').fadeOut();
                                
                                // Show success message
                                $('<div class="notice notice-success" style="margin-top: 10px;"><p><strong>Emergency stop confirmed.</strong> Indexing will remain disabled until manually resumed.</p></div>')
                                    .insertAfter('.wrap h1:eq(0)')
                                    .delay(3000)
                                    .fadeOut();
                            },
                            error: function() {
                                alert('Failed to confirm stop. Please try again.');
                                $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes" style="margin-top: 3px;"></span> Confirm Stop');
                            }
                        });
                    });
                });
                </script>
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

    /**
     * Handle confirm stop (acknowledge) AJAX
     */
    public function handle_confirm_stop() {
        check_ajax_referer('wp_gpt_rag_chat_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }
        
        // Mark acknowledged so the banner doesn't show again
        self::acknowledge();
        
        wp_send_json_success([
            'message' => 'Emergency stop acknowledged. The notice will stay hidden until indexing is resumed.'
        ]);
    }
}

// Initialize
new Emergency_Stop();


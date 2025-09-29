<?php

namespace WP_GPT_RAG_Chat;

/**
 * Privacy and compliance class
 */
class Privacy {
    
    /**
     * Settings instance
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = Settings::get_settings();
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_gpt_rag_chat_cleanup_logs', [$this, 'cleanup_old_logs']);
        add_action('wp_privacy_personal_data_exporters', [$this, 'register_data_exporter']);
        add_action('wp_privacy_personal_data_erasers', [$this, 'register_data_eraser']);
        add_filter('wp_privacy_personal_data_export_page', [$this, 'export_personal_data'], 10, 7);
        add_filter('wp_privacy_personal_data_erase_page', [$this, 'erase_personal_data'], 10, 7);
    }
    
    /**
     * Anonymize IP address
     */
    public function anonymize_ip($ip_address) {
        if (!$this->settings['anonymize_ips']) {
            return $ip_address;
        }
        
        // IPv4: Remove last octet
        if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip_address);
            $parts[3] = '0';
            return implode('.', $parts);
        }
        
        // IPv6: Remove last 4 groups
        if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip_address);
            $parts = array_slice($parts, 0, -4);
            $parts = array_pad($parts, 8, '0');
            return implode(':', $parts);
        }
        
        return $ip_address;
    }
    
    /**
     * Get user IP address
     */
    public function get_user_ip() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $this->anonymize_ip($ip);
                }
            }
        }
        
        return $this->anonymize_ip($_SERVER['REMOTE_ADDR'] ?? '');
    }
    
    /**
     * Check if user has given consent
     */
    public function has_user_consent($user_id = null) {
        if (!$this->settings['require_consent']) {
            return true;
        }
        
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        if ($user_id > 0) {
            return get_user_meta($user_id, '_wp_gpt_rag_chat_consent', true) === '1';
        }
        
        // For non-logged-in users, check session
        if (!session_id()) {
            session_start();
        }
        
        return $_SESSION['wp_gpt_rag_chat_consent'] ?? false;
    }
    
    /**
     * Set user consent
     */
    public function set_user_consent($user_id, $consent) {
        if ($user_id > 0) {
            update_user_meta($user_id, '_wp_gpt_rag_chat_consent', $consent ? '1' : '0');
        } else {
            if (!session_id()) {
                session_start();
            }
            $_SESSION['wp_gpt_rag_chat_consent'] = $consent;
        }
    }
    
    /**
     * Cleanup old logs
     */
    public function cleanup_old_logs() {
        global $wpdb;
        
        $retention_days = $this->settings['log_retention_days'];
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$logs_table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        if ($deleted > 0) {
            error_log("WP GPT RAG Chat: Cleaned up {$deleted} old log entries");
        }
    }
    
    /**
     * Register data exporter
     */
    public function register_data_exporter($exporters) {
        $exporters['wp-gpt-rag-chat'] = [
            'exporter_friendly_name' => __('GPT RAG Chat Data', 'wp-gpt-rag-chat'),
            'callback' => [$this, 'export_personal_data']
        ];
        
        return $exporters;
    }
    
    /**
     * Register data eraser
     */
    public function register_data_eraser($erasers) {
        $erasers['wp-gpt-rag-chat'] = [
            'eraser_friendly_name' => __('GPT RAG Chat Data', 'wp-gpt-rag-chat'),
            'callback' => [$this, 'erase_personal_data']
        ];
        
        return $erasers;
    }
    
    /**
     * Export personal data
     */
    public function export_personal_data($email_address, $page = 1, $exporters = [], $request = null, $send_as_email = false, $exporter_index = 0, $max_items_per_batch = 500) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Get user by email
        $user = get_user_by('email', $email_address);
        if (!$user) {
            return [
                'data' => [],
                'done' => true
            ];
        }
        
        // Get logs for this user
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$logs_table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $user->ID,
            $max_items_per_batch,
            ($page - 1) * $max_items_per_batch
        ));
        
        $export_data = [];
        
        foreach ($logs as $log) {
            $export_data[] = [
                'group_id' => 'wp-gpt-rag-chat',
                'group_label' => __('GPT RAG Chat Data', 'wp-gpt-rag-chat'),
                'group_description' => __('Chat interactions and queries', 'wp-gpt-rag-chat'),
                'item_id' => 'chat-log-' . $log->id,
                'data' => [
                    [
                        'name' => __('Query', 'wp-gpt-rag-chat'),
                        'value' => $log->query
                    ],
                    [
                        'name' => __('Response', 'wp-gpt-rag-chat'),
                        'value' => $log->response
                    ],
                    [
                        'name' => __('Date', 'wp-gpt-rag-chat'),
                        'value' => $log->created_at
                    ],
                    [
                        'name' => __('IP Address', 'wp-gpt-rag-chat'),
                        'value' => $log->ip_address
                    ]
                ]
            ];
        }
        
        return [
            'data' => $export_data,
            'done' => count($logs) < $max_items_per_batch
        ];
    }
    
    /**
     * Erase personal data
     */
    public function erase_personal_data($email_address, $page = 1, $erasers = [], $request = null, $send_as_email = false, $eraser_index = 0, $max_items_per_batch = 500) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Get user by email
        $user = get_user_by('email', $email_address);
        if (!$user) {
            return [
                'items_removed' => 0,
                'items_retained' => 0,
                'messages' => [],
                'done' => true
            ];
        }
        
        // Count total logs
        $total_logs = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$logs_table} WHERE user_id = %d",
            $user->ID
        ));
        
        // Delete logs
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$logs_table} WHERE user_id = %d",
            $user->ID
        ));
        
        // Remove consent
        delete_user_meta($user->ID, '_wp_gpt_rag_chat_consent');
        
        return [
            'items_removed' => $deleted,
            'items_retained' => 0,
            'messages' => [
                sprintf(
                    __('Removed %d chat log entries for user %s.', 'wp-gpt-rag-chat'),
                    $deleted,
                    $email_address
                )
            ],
            'done' => true
        ];
    }
    
    /**
     * Get privacy policy text
     */
    public function get_privacy_policy_text() {
        $text = __('This website uses AI-powered chat functionality to help answer your questions. When you use the chat feature:', 'wp-gpt-rag-chat') . "\n\n";
        
        $text .= __('• Your questions and our responses are logged for quality improvement purposes', 'wp-gpt-rag-chat') . "\n";
        $text .= __('• Your IP address may be logged (anonymized for privacy)', 'wp-gpt-rag-chat') . "\n";
        $text .= __('• Your data is processed by third-party AI services (OpenAI and Pinecone)', 'wp-gpt-rag-chat') . "\n";
        $text .= __('• Logs are automatically deleted after a specified retention period', 'wp-gpt-rag-chat') . "\n";
        $text .= __('• You can request deletion of your data at any time', 'wp-gpt-rag-chat') . "\n\n";
        
        $text .= __('By using the chat feature, you consent to this data processing. If you do not agree, please do not use the chat functionality.', 'wp-gpt-rag-chat');
        
        return $text;
    }
    
    /**
     * Add privacy policy content
     */
    public function add_privacy_policy_content($content) {
        if (is_admin() || !is_page('privacy-policy')) {
            return $content;
        }
        
        $privacy_text = $this->get_privacy_policy_text();
        
        $content .= "\n\n<h2>" . __('AI Chat Functionality', 'wp-gpt-rag-chat') . "</h2>\n";
        $content .= "<p>" . nl2br(esc_html($privacy_text)) . "</p>\n";
        
        return $content;
    }
    
    /**
     * Get data retention information
     */
    public function get_data_retention_info() {
        return [
            'log_retention_days' => $this->settings['log_retention_days'],
            'anonymize_ips' => $this->settings['anonymize_ips'],
            'require_consent' => $this->settings['require_consent'],
            'data_processing_purposes' => [
                __('Quality improvement of chat responses', 'wp-gpt-rag-chat'),
                __('Analytics and usage statistics', 'wp-gpt-rag-chat'),
                __('Debugging and technical support', 'wp-gpt-rag-chat')
            ]
        ];
    }
    
    /**
     * Check GDPR compliance
     */
    public function check_gdpr_compliance() {
        $issues = [];
        
        if ($this->settings['require_consent'] && empty($this->settings['privacy_policy_url'])) {
            $issues[] = __('Privacy policy URL should be configured when consent is required.', 'wp-gpt-rag-chat');
        }
        
        if ($this->settings['log_retention_days'] > 365) {
            $issues[] = __('Log retention period is longer than recommended for GDPR compliance.', 'wp-gpt-rag-chat');
        }
        
        if (!$this->settings['anonymize_ips']) {
            $issues[] = __('IP addresses are not anonymized. Consider enabling this for better privacy.', 'wp-gpt-rag-chat');
        }
        
        return $issues;
    }
    
    /**
     * Generate privacy report
     */
    public function generate_privacy_report() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_logs,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT ip_address) as unique_ips,
                MIN(created_at) as oldest_log,
                MAX(created_at) as newest_log
            FROM {$logs_table}
        ");
        
        return [
            'total_logs' => intval($stats->total_logs ?? 0),
            'unique_users' => intval($stats->unique_users ?? 0),
            'unique_ips' => intval($stats->unique_ips ?? 0),
            'oldest_log' => $stats->oldest_log,
            'newest_log' => $stats->newest_log,
            'retention_settings' => $this->get_data_retention_info(),
            'compliance_issues' => $this->check_gdpr_compliance()
        ];
    }
}

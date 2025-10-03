<?php

namespace WP_GPT_RAG_Chat;

/**
 * Error Logging System for API failures and invalid responses
 */
class Error_Logger {
    
    /**
     * Log API error
     */
    public static function log_api_error($error_type, $api_service, $error_message, $context = []) {
        global $wpdb;
        
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        
        // Ensure table exists
        self::ensure_errors_table();
        
        $error_data = [
            'error_type' => $error_type,
            'api_service' => $api_service,
            'error_message' => $error_message,
            'context' => wp_json_encode($context),
            'user_id' => get_current_user_id(),
            'ip_address' => self::get_user_ip(),
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $errors_table,
            $error_data,
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );
        
        if ($result === false) {
            error_log('WP GPT RAG Chat: Failed to log error - ' . $wpdb->last_error);
            return false;
        }
        
        // Log to file
        self::log_to_file($error_type, $api_service, $error_message, $context);
        
        // Check for alert threshold
        self::check_alert_threshold();
        
        return $wpdb->insert_id;
    }
    
    /**
     * Log OpenAI API error
     */
    public static function log_openai_error($error_message, $context = []) {
        return self::log_api_error('api_failure', 'openai', $error_message, $context);
    }
    
    /**
     * Log Pinecone API error
     */
    public static function log_pinecone_error($error_message, $context = []) {
        return self::log_api_error('api_failure', 'pinecone', $error_message, $context);
    }
    
    /**
     * Log invalid response error
     */
    public static function log_invalid_response($error_message, $context = []) {
        return self::log_api_error('invalid_response', 'system', $error_message, $context);
    }
    
    /**
     * Log rate limit error
     */
    public static function log_rate_limit($api_service, $error_message, $context = []) {
        return self::log_api_error('rate_limit', $api_service, $error_message, $context);
    }
    
    /**
     * Log authentication error
     */
    public static function log_auth_error($api_service, $error_message, $context = []) {
        return self::log_api_error('authentication', $api_service, $error_message, $context);
    }
    
    /**
     * Get error logs with filters
     */
    public static function get_error_logs($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 50,
            'offset' => 0,
            'date_from' => null,
            'date_to' => null,
            'error_type' => null,
            'api_service' => null,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        
        $where_conditions = ['1=1'];
        $where_values = [];
        
        if ($args['date_from']) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if ($args['date_to']) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = $args['date_to'];
        }
        
        if ($args['error_type']) {
            $where_conditions[] = 'error_type = %s';
            $where_values[] = $args['error_type'];
        }
        
        if ($args['api_service']) {
            $where_conditions[] = 'api_service = %s';
            $where_values[] = $args['api_service'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $query = "SELECT * FROM {$errors_table} WHERE {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get error logs count
     */
    public static function get_error_logs_count($args = []) {
        global $wpdb;
        
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        
        $where_conditions = ['1=1'];
        $where_values = [];
        
        if (isset($args['date_from'])) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (isset($args['date_to'])) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = $args['date_to'];
        }
        
        if (isset($args['error_type'])) {
            $where_conditions[] = 'error_type = %s';
            $where_values[] = $args['error_type'];
        }
        
        if (isset($args['api_service'])) {
            $where_conditions[] = 'api_service = %s';
            $where_values[] = $args['api_service'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT COUNT(*) FROM {$errors_table} WHERE {$where_clause}";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Get error statistics
     */
    public static function get_error_stats($days = 30) {
        global $wpdb;
        
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        
        // Ensure table exists
        self::ensure_errors_table();
        
        // Total errors
        $total_errors = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$errors_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Errors by type
        $errors_by_type = $wpdb->get_results($wpdb->prepare(
            "SELECT error_type, COUNT(*) as count
            FROM {$errors_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY error_type
            ORDER BY count DESC",
            $days
        ));
        
        // Errors by service
        $errors_by_service = $wpdb->get_results($wpdb->prepare(
            "SELECT api_service, COUNT(*) as count
            FROM {$errors_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY api_service
            ORDER BY count DESC",
            $days
        ));
        
        // Recent errors (last 24 hours)
        $recent_errors = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$errors_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Error trend (daily)
        $error_trend = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as errors
            FROM {$errors_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC",
            $days
        ));
        
        return [
            'total_errors' => (int) $total_errors,
            'recent_errors' => (int) $recent_errors,
            'errors_by_type' => $errors_by_type,
            'errors_by_service' => $errors_by_service,
            'error_trend' => $error_trend
        ];
    }
    
    /**
     * Cleanup old error logs
     */
    public static function cleanup_old_errors($days = 30) {
        global $wpdb;
        
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$errors_table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        return $deleted;
    }
    
    /**
     * Ensure errors table exists
     */
    private static function ensure_errors_table() {
        global $wpdb;
        
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$errors_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            error_type varchar(50) NOT NULL,
            api_service varchar(50) NOT NULL,
            error_message text NOT NULL,
            context longtext DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY error_type (error_type),
            KEY api_service (api_service),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get user IP address
     */
    private static function get_user_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Log error to file
     */
    private static function log_to_file($error_type, $api_service, $error_message, $context = []) {
        $log_dir = WP_CONTENT_DIR . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        $log_file = $log_dir . '/chatbot_errors.log';
        
        $log_entry = sprintf(
            "[%s] %s - %s: %s | Context: %s | IP: %s | User: %d\n",
            current_time('Y-m-d H:i:s'),
            strtoupper($error_type),
            strtoupper($api_service),
            $error_message,
            wp_json_encode($context),
            self::get_user_ip(),
            get_current_user_id()
        );
        
        // Write to file
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Check if error threshold exceeded and send alert
     */
    private static function check_alert_threshold() {
        global $wpdb;
        
        $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
        $threshold = 10; // 10 failures in 1 hour
        $time_window = 3600; // 1 hour in seconds
        
        // Count errors in the last hour
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$errors_table} 
             WHERE created_at >= %s AND error_type = 'api_failure'",
            date('Y-m-d H:i:s', time() - $time_window)
        ));
        
        if ($count >= $threshold) {
            // Check if we already sent an alert recently (within 1 hour)
            $last_alert = get_transient('wp_gpt_rag_error_alert_sent');
            if (!$last_alert) {
                self::send_admin_alert($count);
                set_transient('wp_gpt_rag_error_alert_sent', true, $time_window);
            }
        }
    }
    
    /**
     * Send admin email alert
     */
    private static function send_admin_alert($error_count) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();
        
        $subject = sprintf(
            '[%s] Nuwab AI Assistant - High API Error Rate Alert',
            $site_name
        );
        
        $message = sprintf(
            "Dear Administrator,\n\n" .
            "The Nuwab AI Assistant plugin has detected a high number of API errors on your website.\n\n" .
            "Error Details:\n" .
            "- Total API failures in the last hour: %d\n" .
            "- Threshold exceeded: 10 failures per hour\n" .
            "- Site: %s\n" .
            "- Time: %s\n\n" .
            "Please check the following:\n" .
            "1. Verify your OpenAI API key is valid and has sufficient credits\n" .
            "2. Check your Pinecone API key and connection\n" .
            "3. Review the error logs in WordPress admin\n" .
            "4. Check server connectivity and firewall settings\n\n" .
            "You can view detailed error logs at:\n" .
            "%s/wp-admin/admin.php?page=wp-gpt-rag-chat-analytics&tab=error-logs\n\n" .
            "This is an automated alert from the Nuwab AI Assistant plugin.\n\n" .
            "Best regards,\n" .
            "Nuwab AI Assistant System",
            $error_count,
            $site_name,
            current_time('Y-m-d H:i:s'),
            $site_url
        );
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>'
        ];
        
        wp_mail($admin_email, $subject, $message, $headers);
        
        // Also log the alert
        error_log('Nuwab AI Assistant: Admin alert sent - ' . $error_count . ' API errors in last hour');
    }
}

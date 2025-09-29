<?php

namespace WP_GPT_RAG_Chat;

/**
 * Logging class
 */
class Logger {
    
    /**
     * Privacy instance
     */
    private $privacy;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->privacy = new Privacy();
    }
    
    /**
     * Log chat interaction
     */
    public function log_interaction($query, $response, $user_id = null) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $data = [
            'user_id' => $user_id ?: get_current_user_id(),
            'ip_address' => $this->privacy->get_user_ip(),
            'query' => $query,
            'response' => $response,
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $logs_table,
            $data,
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%s'
            ]
        );
        
        if ($result === false) {
            error_log('WP GPT RAG Chat: Failed to log interaction - ' . $wpdb->last_error);
        }
        
        return $result !== false;
    }
    
    /**
     * Log indexing activity
     */
    public function log_indexing_activity($post_id, $action, $details = []) {
        $log_data = [
            'timestamp' => current_time('mysql'),
            'post_id' => $post_id,
            'action' => $action,
            'details' => $details,
            'user_id' => get_current_user_id()
        ];
        
        error_log('WP GPT RAG Chat Indexing: ' . wp_json_encode($log_data));
    }
    
    /**
     * Log API errors
     */
    public function log_api_error($service, $error_message, $context = []) {
        $log_data = [
            'timestamp' => current_time('mysql'),
            'service' => $service,
            'error' => $error_message,
            'context' => $context,
            'user_id' => get_current_user_id()
        ];
        
        error_log('WP GPT RAG Chat API Error: ' . wp_json_encode($log_data));
    }
    
    /**
     * Log performance metrics
     */
    public function log_performance_metrics($operation, $duration, $details = []) {
        $log_data = [
            'timestamp' => current_time('mysql'),
            'operation' => $operation,
            'duration_ms' => $duration,
            'details' => $details
        ];
        
        // Only log if duration is significant
        if ($duration > 1000) { // 1 second
            error_log('WP GPT RAG Chat Performance: ' . wp_json_encode($log_data));
        }
    }
    
    /**
     * Get chat logs
     */
    public function get_chat_logs($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 50,
            'offset' => 0,
            'user_id' => null,
            'date_from' => null,
            'date_to' => null,
            'search' => null
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $where_conditions = ['1=1'];
        $where_values = [];
        
        if ($args['user_id']) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }
        
        if ($args['date_from']) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if ($args['date_to']) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = $args['date_to'];
        }
        
        if ($args['search']) {
            $where_conditions[] = '(query LIKE %s OR response LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT * FROM {$logs_table} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get chat statistics
     */
    public function get_chat_statistics($period = '7d') {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $date_condition = $this->get_date_condition($period);
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_queries,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT ip_address) as unique_ips,
                AVG(LENGTH(query)) as avg_query_length,
                AVG(LENGTH(response)) as avg_response_length,
                MIN(created_at) as first_query,
                MAX(created_at) as last_query
            FROM {$logs_table} 
            WHERE {$date_condition}",
            $this->get_date_value($period)
        ));
        
        return [
            'total_queries' => intval($stats->total_queries ?? 0),
            'unique_users' => intval($stats->unique_users ?? 0),
            'unique_ips' => intval($stats->unique_ips ?? 0),
            'avg_query_length' => intval($stats->avg_query_length ?? 0),
            'avg_response_length' => intval($stats->avg_response_length ?? 0),
            'first_query' => $stats->first_query,
            'last_query' => $stats->last_query
        ];
    }
    
    /**
     * Get daily usage statistics
     */
    public function get_daily_usage_stats($days = 30) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as queries,
                COUNT(DISTINCT user_id) as unique_users
            FROM {$logs_table} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC",
            $days
        ));
        
        return $stats;
    }
    
    /**
     * Get popular queries
     */
    public function get_popular_queries($limit = 10) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $queries = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                query,
                COUNT(*) as frequency,
                MAX(created_at) as last_asked
            FROM {$logs_table} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY query
            ORDER BY frequency DESC
            LIMIT %d",
            $limit
        ));
        
        return $queries;
    }
    
    /**
     * Get date condition for SQL queries
     */
    private function get_date_condition($period) {
        switch ($period) {
            case '1d':
                return 'created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
            case '7d':
                return 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            case '30d':
                return 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
            case '90d':
                return 'created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
            default:
                return '1=1';
        }
    }
    
    /**
     * Get date value for SQL queries
     */
    private function get_date_value($period) {
        // This method is used for prepared statements
        // The actual date logic is handled in get_date_condition
        return null;
    }
    
    /**
     * Export logs to CSV
     */
    public function export_logs_to_csv($args = []) {
        $logs = $this->get_chat_logs($args);
        
        $filename = 'wp-gpt-rag-chat-logs-' . date('Y-m-d-H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'ID',
            'User ID',
            'IP Address',
            'Query',
            'Response',
            'Created At'
        ]);
        
        // CSV data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log->id,
                $log->user_id,
                $log->ip_address,
                $log->query,
                $log->response,
                $log->created_at
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Clean up old logs
     */
    public function cleanup_old_logs($days = null) {
        global $wpdb;
        
        if ($days === null) {
            $settings = Settings::get_settings();
            $days = $settings['log_retention_days'];
        }
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$logs_table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        return $deleted;
    }
    
    /**
     * Get log file path for debugging
     */
    public function get_log_file_path() {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/wp-gpt-rag-chat-debug.log';
    }
    
    /**
     * Write debug log
     */
    public function write_debug_log($message, $context = []) {
        if (!WP_DEBUG) {
            return;
        }
        
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'message' => $message,
            'context' => $context
        ];
        
        $log_file = $this->get_log_file_path();
        file_put_contents($log_file, wp_json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
}

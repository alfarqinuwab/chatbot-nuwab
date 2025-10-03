<?php

namespace WP_GPT_RAG_Chat;

/**
 * API Usage Tracker for OpenAI and Pinecone limits monitoring
 */
class API_Usage_Tracker {
    
    /**
     * Track API usage
     */
    public static function track_usage($api_service, $endpoint, $tokens_used = null, $cost = null, $context = []) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
        // Ensure table exists
        self::ensure_usage_table();
        
        $usage_data = [
            'api_service' => $api_service,
            'endpoint' => $endpoint,
            'tokens_used' => $tokens_used,
            'cost' => $cost,
            'context' => wp_json_encode($context),
            'user_id' => get_current_user_id(),
            'ip_address' => self::get_user_ip(),
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $usage_table,
            $usage_data,
            ['%s', '%s', '%d', '%f', '%s', '%d', '%s', '%s']
        );
        
        if ($result === false) {
            error_log('WP GPT RAG Chat: Failed to track API usage - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Track OpenAI usage
     */
    public static function track_openai_usage($endpoint, $tokens_used = null, $cost = null, $context = []) {
        return self::track_usage('openai', $endpoint, $tokens_used, $cost, $context);
    }
    
    /**
     * Track Pinecone usage
     */
    public static function track_pinecone_usage($endpoint, $tokens_used = null, $cost = null, $context = []) {
        return self::track_usage('pinecone', $endpoint, $tokens_used, $cost, $context);
    }
    
    /**
     * Get API usage reports
     */
    public static function get_usage_reports($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 50,
            'offset' => 0,
            'date_from' => null,
            'date_to' => null,
            'api_service' => null,
            'endpoint' => null,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
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
        
        if ($args['api_service']) {
            $where_conditions[] = 'api_service = %s';
            $where_values[] = $args['api_service'];
        }
        
        if ($args['endpoint']) {
            $where_conditions[] = 'endpoint = %s';
            $where_values[] = $args['endpoint'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $query = "SELECT * FROM {$usage_table} WHERE {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get usage statistics
     */
    public static function get_usage_stats($days = 30) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
        // Ensure table exists
        self::ensure_usage_table();
        
        // Total API calls
        $total_calls = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Total tokens used
        $total_tokens = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(tokens_used) FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            AND tokens_used IS NOT NULL",
            $days
        ));
        
        // Total cost
        $total_cost = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(cost) FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            AND cost IS NOT NULL",
            $days
        ));
        
        // Usage by service
        $usage_by_service = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                api_service,
                COUNT(*) as calls,
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost,
                AVG(tokens_used) as avg_tokens_per_call
            FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY api_service
            ORDER BY calls DESC",
            $days
        ));
        
        // Usage by endpoint
        $usage_by_endpoint = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                api_service,
                endpoint,
                COUNT(*) as calls,
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost
            FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY api_service, endpoint
            ORDER BY calls DESC
            LIMIT 20",
            $days
        ));
        
        // Daily usage trend
        $daily_usage = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as calls,
                SUM(tokens_used) as tokens,
                SUM(cost) as cost
            FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC",
            $days
        ));
        
        // Hourly usage pattern
        $hourly_usage = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                HOUR(created_at) as hour,
                COUNT(*) as calls,
                AVG(tokens_used) as avg_tokens
            FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY HOUR(created_at)
            ORDER BY hour ASC",
            $days
        ));
        
        // Rate limit warnings (if we track them)
        $rate_limit_warnings = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$usage_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            AND context LIKE '%rate_limit%'",
            $days
        ));
        
        return [
            'total_calls' => (int) $total_calls,
            'total_tokens' => (int) $total_tokens,
            'total_cost' => (float) $total_cost,
            'usage_by_service' => $usage_by_service,
            'usage_by_endpoint' => $usage_by_endpoint,
            'daily_usage' => $daily_usage,
            'hourly_usage' => $hourly_usage,
            'rate_limit_warnings' => (int) $rate_limit_warnings
        ];
    }
    
    /**
     * Get current usage limits status
     */
    public static function get_limits_status() {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
        // Today's usage
        $today_usage = $wpdb->get_results(
            "SELECT 
                api_service,
                COUNT(*) as calls,
                SUM(tokens_used) as tokens
            FROM {$usage_table}
            WHERE DATE(created_at) = CURDATE()
            GROUP BY api_service"
        );
        
        // This month's usage
        $month_usage = $wpdb->get_results(
            "SELECT 
                api_service,
                COUNT(*) as calls,
                SUM(tokens_used) as tokens,
                SUM(cost) as cost
            FROM {$usage_table}
            WHERE YEAR(created_at) = YEAR(NOW()) 
            AND MONTH(created_at) = MONTH(NOW())
            GROUP BY api_service"
        );
        
        // Estimated limits (these would be configured in settings)
        $estimated_limits = [
            'openai' => [
                'daily_tokens' => 200000, // Example limit
                'monthly_cost' => 50.00,  // Example limit
                'requests_per_minute' => 60
            ],
            'pinecone' => [
                'daily_queries' => 10000, // Example limit
                'monthly_cost' => 25.00   // Example limit
            ]
        ];
        
        $status = [];
        
        foreach ($today_usage as $usage) {
            $service = $usage->api_service;
            $limits = $estimated_limits[$service] ?? [];
            
            $status[$service] = [
                'today' => [
                    'calls' => (int) $usage->calls,
                    'tokens' => (int) $usage->tokens,
                    'limit_reached' => false
                ],
                'month' => [
                    'calls' => 0,
                    'tokens' => 0,
                    'cost' => 0.0,
                    'limit_reached' => false
                ],
                'limits' => $limits
            ];
            
            // Check daily limits
            if (isset($limits['daily_tokens']) && $usage->tokens >= $limits['daily_tokens']) {
                $status[$service]['today']['limit_reached'] = true;
            }
        }
        
        foreach ($month_usage as $usage) {
            $service = $usage->api_service;
            if (!isset($status[$service])) {
                $status[$service] = [
                    'today' => ['calls' => 0, 'tokens' => 0, 'limit_reached' => false],
                    'month' => ['calls' => 0, 'tokens' => 0, 'cost' => 0.0, 'limit_reached' => false],
                    'limits' => $estimated_limits[$service] ?? []
                ];
            }
            
            $status[$service]['month'] = [
                'calls' => (int) $usage->calls,
                'tokens' => (int) $usage->tokens,
                'cost' => (float) $usage->cost,
                'limit_reached' => false
            ];
            
            // Check monthly limits
            $limits = $status[$service]['limits'];
            if (isset($limits['monthly_cost']) && $usage->cost >= $limits['monthly_cost']) {
                $status[$service]['month']['limit_reached'] = true;
            }
        }
        
        return $status;
    }
    
    /**
     * Cleanup old usage logs
     */
    public static function cleanup_old_usage($days = 90) {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$usage_table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        return $deleted;
    }
    
    /**
     * Get usage reports count
     */
    public static function get_usage_reports_count($args = []) {
        global $wpdb;
        
        $defaults = [
            'date_from' => null,
            'date_to' => null,
            'api_service' => null,
            'endpoint' => null
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        
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
        
        if (isset($args['api_service'])) {
            $where_conditions[] = 'api_service = %s';
            $where_values[] = $args['api_service'];
        }
        
        if (isset($args['endpoint'])) {
            $where_conditions[] = 'endpoint = %s';
            $where_values[] = $args['endpoint'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT COUNT(*) FROM {$usage_table} WHERE {$where_clause}";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Ensure usage table exists
     */
    private static function ensure_usage_table() {
        global $wpdb;
        
        $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$usage_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            api_service varchar(50) NOT NULL,
            endpoint varchar(100) NOT NULL,
            tokens_used int(11) DEFAULT NULL,
            cost decimal(10,4) DEFAULT NULL,
            context longtext DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY api_service (api_service),
            KEY endpoint (endpoint),
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
}

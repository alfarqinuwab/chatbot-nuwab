<?php

namespace WP_GPT_RAG_Chat;

/**
 * Analytics and Logging class for AI improvements
 */
class Analytics {
    
    /**
     * Privacy instance
     */
    private $privacy;
    
    /**
     * Settings instance
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->privacy = new Privacy();
        $this->settings = Settings::get_settings();
    }
    
    /**
     * Generate unique chat ID
     */
    public function generate_chat_id() {
        return 'chat_' . wp_generate_uuid4();
    }
    
    /**
     * Mask PII in content (emails, phones)
     */
    public function mask_pii($content) {
        if (empty($this->settings['enable_pii_masking'])) {
            return $content;
        }
        
        // Mask emails
        $content = preg_replace('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', '[EMAIL_MASKED]', $content);
        
        // Mask phone numbers (various formats)
        $content = preg_replace('/(\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', '[PHONE_MASKED]', $content);
        
        // Mask credit cards (simple pattern)
        $content = preg_replace('/\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/', '[CARD_MASKED]', $content);
        
        return $content;
    }
    
    /**
     * Log chat interaction with enhanced tracking
     */
    public function log_interaction($data) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Mask PII before storing
        $content = $this->mask_pii($data['content']);
        
        $log_data = [
            'chat_id' => $data['chat_id'],
            'turn_number' => $data['turn_number'] ?? 1,
            'role' => $data['role'], // 'user' or 'assistant'
            'user_id' => $data['user_id'] ?? get_current_user_id(),
            'ip_address' => $this->privacy->get_user_ip(),
            'content' => $content,
            'response_latency' => $data['response_latency'] ?? null,
            'sources_count' => $data['sources_count'] ?? 0,
            'rag_sources' => isset($data['rag_sources']) ? wp_json_encode($data['rag_sources']) : null,
            'rating' => $data['rating'] ?? null,
            'tags' => $data['tags'] ?? null,
            'model_used' => $data['model_used'] ?? null,
            'tokens_used' => $data['tokens_used'] ?? null,
            'rag_metadata' => $data['rag_metadata'] ?? null,
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $logs_table,
            $log_data,
            [
                '%s', // chat_id
                '%d', // turn_number
                '%s', // role
                '%d', // user_id
                '%s', // ip_address
                '%s', // content
                '%d', // response_latency
                '%d', // sources_count
                '%s', // rag_sources
                '%d', // rating
                '%s', // tags
                '%s', // model_used
                '%d', // tokens_used
                '%s', // rag_metadata
                '%s'  // created_at
            ]
        );
        
        if ($result === false) {
            error_log('WP GPT RAG Chat: Failed to log interaction - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update rating for a log entry
     */
    public function update_rating($log_id, $rating) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $result = $wpdb->update(
            $logs_table,
            ['rating' => $rating],
            ['id' => $log_id],
            ['%d'],
            ['%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Add tags to a log entry
     */
    public function add_tags($log_id, $tags) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Get existing tags
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT tags FROM {$logs_table} WHERE id = %d",
            $log_id
        ));
        
        $existing_tags = $existing ? explode(',', $existing) : [];
        $new_tags = is_array($tags) ? $tags : explode(',', $tags);
        
        $all_tags = array_unique(array_merge($existing_tags, $new_tags));
        $tags_string = implode(',', array_filter($all_tags));
        
        $result = $wpdb->update(
            $logs_table,
            ['tags' => $tags_string],
            ['id' => $log_id],
            ['%s'],
            ['%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Get conversation by chat_id
     */
    public function get_conversation($chat_id) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$logs_table} WHERE chat_id = %s ORDER BY turn_number ASC, created_at ASC",
            $chat_id
        ));
    }
    
    /**
     * Get logs with filters
     */
    public function get_logs($args = []) {
        global $wpdb;
        
        $defaults = [
            'limit' => 50,
            'offset' => 0,
            'date_from' => null,
            'date_to' => null,
            'role' => null,
            'search' => null,
            'tags' => null,
            'model' => null,
            'rating' => null,
            'user_id' => null,
            'orderby' => 'id',
            'order' => 'DESC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
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
        
        if ($args['role']) {
            $where_conditions[] = 'role = %s';
            $where_values[] = $args['role'];
        }
        
        if ($args['search']) {
            $where_conditions[] = 'content LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        if ($args['tags']) {
            $where_conditions[] = 'tags LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like($args['tags']) . '%';
        }
        
        if ($args['model']) {
            $where_conditions[] = 'model_used = %s';
            $where_values[] = $args['model'];
        }
        
        if ($args['rating'] !== null) {
            $where_conditions[] = 'rating = %d';
            $where_values[] = $args['rating'];
        }
        
        if ($args['user_id']) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $args['user_id'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $query = "SELECT * FROM {$logs_table} WHERE {$where_clause} ORDER BY {$orderby} LIMIT %d OFFSET %d";
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get total count for filters
     */
    public function get_logs_count($args = []) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
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
        
        if (isset($args['role'])) {
            $where_conditions[] = 'role = %s';
            $where_values[] = $args['role'];
        }
        
        if (isset($args['search'])) {
            $where_conditions[] = 'content LIKE %s';
            $where_values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT COUNT(*) FROM {$logs_table} WHERE {$where_clause}";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Export logs to CSV
     */
    public function export_to_csv($args = []) {
        $logs = $this->get_logs(array_merge($args, ['limit' => 9999999]));
        
        $filename = 'chat-logs-' . date('Y-m-d-His') . '.csv';
        
        // Create CSV content
        $csv_content = '';
        
        // CSV headers
        $csv_content .= '"ID","Chat ID","Turn","Role","User","Content (120 chars)","Latency (ms)","Sources","Rating","Tags","Model","Tokens","Created At"' . "\n";
        
        // CSV data
        foreach ($logs as $log) {
            $user = $log->user_id ? get_userdata($log->user_id)->display_name : 'Guest';
            $csv_content .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $log->id,
                $log->chat_id,
                $log->turn_number,
                $log->role,
                str_replace('"', '""', $user),
                str_replace('"', '""', mb_substr($log->content, 0, 120)),
                $log->response_latency,
                $log->sources_count,
                $log->rating,
                str_replace('"', '""', $log->tags),
                $log->model_used,
                $log->tokens_used,
                $log->created_at
            );
        }
        
        // Save to temporary file
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        if (file_put_contents($file_path, $csv_content) === false) {
            throw new Exception(__('Failed to create export file.', 'wp-gpt-rag-chat'));
        }
        
        return [
            'file_url' => $upload_dir['url'] . '/' . $filename,
            'file_path' => $file_path,
            'record_count' => count($logs)
        ];
    }
    
    /**
     * Get dashboard KPIs
     */
    public function get_kpis($days = 30) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Conversations per day
        $conversations_per_day = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(DISTINCT chat_id) as conversations
            FROM {$logs_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC",
            $days
        ));
        
        // Average turns per conversation
        $avg_turns = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(turn_count) FROM (
                SELECT COUNT(*) as turn_count
                FROM {$logs_table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY chat_id
            ) as subquery",
            $days
        ));
        
        // Average latency
        $avg_latency = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(response_latency)
            FROM {$logs_table}
            WHERE role = 'assistant' AND response_latency IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Rating distribution
        $rating_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(CASE WHEN rating = 1 THEN 1 END) as thumbs_up,
                COUNT(CASE WHEN rating = -1 THEN 1 END) as thumbs_down,
                COUNT(CASE WHEN rating IS NOT NULL THEN 1 END) as total_rated
            FROM {$logs_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Token usage by model
        $token_usage = $wpdb->get_results($wpdb->prepare(
            "SELECT model_used, SUM(tokens_used) as total_tokens, COUNT(*) as calls
            FROM {$logs_table}
            WHERE role = 'assistant' AND tokens_used IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY model_used",
            $days
        ));
        
        // Top search intents (simple word frequency)
        $top_queries = $wpdb->get_results($wpdb->prepare(
            "SELECT content, COUNT(*) as frequency
            FROM {$logs_table}
            WHERE role = 'user'
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY content
            ORDER BY frequency DESC
            LIMIT 10",
            $days
        ));
        
        return [
            'conversations_per_day' => $conversations_per_day,
            'avg_turns_per_conversation' => round($avg_turns, 2),
            'avg_latency_ms' => round($avg_latency),
            'thumbs_up' => (int) $rating_stats->thumbs_up,
            'thumbs_down' => (int) $rating_stats->thumbs_down,
            'total_rated' => (int) $rating_stats->total_rated,
            'satisfaction_rate' => $rating_stats->total_rated > 0 
                ? round(($rating_stats->thumbs_up / $rating_stats->total_rated) * 100, 1)
                : 0,
            'token_usage' => $token_usage,
            'top_queries' => $top_queries
        ];
    }
    
    /**
     * Cleanup old logs based on retention policy
     */
    public function cleanup_old_logs() {
        global $wpdb;
        
        $retention_days = $this->settings['log_retention_days'] ?? 90;
        
        if ($retention_days <= 0) {
            return 0; // Retention disabled
        }
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$logs_table} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
        
        return $deleted;
    }
    
    /**
     * Get user statistics
     */
    public function get_user_stats($days = 30) {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Total unique users
        $total_users = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT CASE 
                WHEN user_id > 0 THEN user_id
                ELSE ip_address
            END) as total_users
            FROM {$logs_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Logged in users
        $logged_in_users = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) as logged_in_users
            FROM {$logs_table}
            WHERE user_id > 0
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Anonymous users
        $anonymous_users = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT ip_address) as anonymous_users
            FROM {$logs_table}
            WHERE user_id = 0
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Returning users (users with more than one session)
        $returning_users = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM (
                SELECT CASE 
                    WHEN user_id > 0 THEN user_id
                    ELSE ip_address
                END as user_identifier,
                COUNT(DISTINCT chat_id) as sessions
                FROM {$logs_table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY user_identifier
                HAVING sessions > 1
            ) as returning_users",
            $days
        ));
        
        // Average queries per user
        $total_queries = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$logs_table}
            WHERE role = 'user'
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        $avg_queries_per_user = $total_users > 0 ? round($total_queries / $total_users, 1) : 0;
        
        // Most active hour
        $most_active_hour = $wpdb->get_var($wpdb->prepare(
            "SELECT HOUR(created_at) as hour
            FROM {$logs_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY hour
            ORDER BY COUNT(*) DESC
            LIMIT 1",
            $days
        ));
        
        // Retention rate
        $retention_rate = $total_users > 0 ? round(($returning_users / $total_users) * 100, 1) : 0;
        
        return [
            'total_users' => (int) $total_users,
            'logged_in_users' => (int) $logged_in_users,
            'anonymous_users' => (int) $anonymous_users,
            'returning_users' => (int) $returning_users,
            'avg_queries_per_user' => $avg_queries_per_user,
            'avg_session_duration' => 'N/A', // Would need session tracking
            'most_active_hour' => $most_active_hour ? sprintf('%02d:00', $most_active_hour) : 'N/A',
            'retention_rate' => $retention_rate
        ];
    }
    
    /**
     * Get user sessions
     */
    public function get_user_sessions($limit = 20) {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $sessions = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                CASE WHEN user_id > 0 THEN user_id ELSE 0 END as user_id,
                CASE WHEN user_id > 0 THEN 'Logged In' ELSE 'Anonymous' END as user_type,
                COUNT(DISTINCT chat_id) as sessions,
                COUNT(CASE WHEN role = 'user' THEN 1 END) as queries,
                MAX(created_at) as last_activity
            FROM {$logs_table}
            GROUP BY user_id, ip_address
            ORDER BY last_activity DESC
            LIMIT %d",
            $limit
        ));
        
        // Enrich with user data
        foreach ($sessions as $session) {
            if ($session->user_id > 0) {
                $user = get_userdata($session->user_id);
                $session->display_name = $user ? $user->display_name : 'Unknown User';
            } else {
                $session->display_name = 'Guest';
            }
        }
        
        return $sessions;
    }
    
    /**
     * Get user activity over time
     */
    public function get_user_activity($days = 7) {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $activity = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(DISTINCT CASE WHEN user_id > 0 THEN user_id END) as logged_in_users,
                COUNT(DISTINCT CASE WHEN user_id = 0 THEN ip_address END) as anonymous_users
            FROM {$logs_table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC",
            $days
        ));
        
        return $activity;
    }
    
    /**
     * Get geographic distribution (based on IP - simplified)
     */
    public function get_geographic_distribution() {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Get unique IPs
        $ips = $wpdb->get_results(
            "SELECT DISTINCT ip_address, COUNT(*) as count
            FROM {$logs_table}
            WHERE ip_address IS NOT NULL
            GROUP BY ip_address
            ORDER BY count DESC
            LIMIT 10"
        );
        
        $total = array_sum(array_column($ips, 'count'));
        
        // Simplified - just show IP ranges
        $distribution = [];
        foreach ($ips as $ip) {
            // Extract first two octets as "region"
            $parts = explode('.', $ip->ip_address);
            $region = isset($parts[0]) && isset($parts[1]) 
                ? $parts[0] . '.' . $parts[1] . '.x.x' 
                : 'Unknown';
            
            if (!isset($distribution[$region])) {
                $distribution[$region] = 0;
            }
            $distribution[$region] += $ip->count;
        }
        
        $result = [];
        foreach ($distribution as $region => $count) {
            $result[] = [
                'region' => $region,
                'users' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0
            ];
        }
        
        // Sort by count
        usort($result, function($a, $b) {
            return $b['users'] - $a['users'];
        });
        
        return $result;
    }
}


<?php
/**
 * Audit Trail System
 * 
 * Tracks all user actions, system changes, and important events
 * for compliance and monitoring purposes.
 */

namespace WP_GPT_RAG_Chat;

if (!defined('ABSPATH')) {
    exit;
}

class Audit_Trail {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wp_gpt_rag_audit_trail';
    }
    
    /**
     * Create audit trail table
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            user_login varchar(60) NOT NULL,
            user_role varchar(50) NOT NULL,
            action varchar(100) NOT NULL,
            object_type varchar(50) NOT NULL,
            object_id varchar(100) DEFAULT NULL,
            description text NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text,
            metadata longtext,
            severity enum('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            status varchar(20) DEFAULT 'success',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_type (object_type),
            KEY severity (severity),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Log an audit trail entry
     */
    public function log($action, $object_type, $object_id = null, $description = '', $metadata = [], $severity = 'medium', $status = 'success') {
        global $wpdb;
        
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $user_login = $current_user->user_login;
        $user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'guest';
        
        // Get IP address
        $ip_address = $this->get_client_ip();
        
        // Get user agent
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        // Prepare metadata
        $metadata_json = !empty($metadata) ? wp_json_encode($metadata) : null;
        
        $result = $wpdb->insert(
            $this->table_name,
            [
                'user_id' => $user_id,
                'user_login' => $user_login,
                'user_role' => $user_role,
                'action' => $action,
                'object_type' => $object_type,
                'object_id' => $object_id,
                'description' => $description,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'metadata' => $metadata_json,
                'severity' => $severity,
                'status' => $status,
                'created_at' => current_time('mysql')
            ],
            [
                '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
            ]
        );
        
        if ($result === false) {
            error_log('WP GPT RAG Chat: Failed to log audit trail entry');
        }
        
        return $result;
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
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
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * Get audit trail entries
     */
    public function get_entries($limit = 100, $offset = 0, $filters = []) {
        global $wpdb;
        
        $where_conditions = ['1=1'];
        $where_values = [];
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $where_conditions[] = 'action = %s';
            $where_values[] = $filters['action'];
        }
        
        if (!empty($filters['object_type'])) {
            $where_conditions[] = 'object_type = %s';
            $where_values[] = $filters['object_type'];
        }
        
        if (!empty($filters['severity'])) {
            $where_conditions[] = 'severity = %s';
            $where_values[] = $filters['severity'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            array_merge($where_values, [$limit, $offset])
        );
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get audit trail statistics
     */
    public function get_statistics($days = 30) {
        global $wpdb;
        
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = [];
        
        // Total entries
        $stats['total_entries'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE created_at >= %s",
            $date_from
        ));
        
        // Entries by action
        $stats['by_action'] = $wpdb->get_results($wpdb->prepare(
            "SELECT action, COUNT(*) as count FROM {$this->table_name} WHERE created_at >= %s GROUP BY action ORDER BY count DESC",
            $date_from
        ));
        
        // Entries by severity
        $stats['by_severity'] = $wpdb->get_results($wpdb->prepare(
            "SELECT severity, COUNT(*) as count FROM {$this->table_name} WHERE created_at >= %s GROUP BY severity ORDER BY count DESC",
            $date_from
        ));
        
        // Entries by user
        $stats['by_user'] = $wpdb->get_results($wpdb->prepare(
            "SELECT user_login, user_role, COUNT(*) as count FROM {$this->table_name} WHERE created_at >= %s GROUP BY user_id, user_login, user_role ORDER BY count DESC LIMIT 10",
            $date_from
        ));
        
        // Recent entries
        $stats['recent_entries'] = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE created_at >= %s ORDER BY created_at DESC LIMIT 10",
            $date_from
        ));
        
        return $stats;
    }
    
    /**
     * Clean up old audit trail entries
     */
    public function cleanup($days = 365) {
        global $wpdb;
        
        $date_cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE created_at < %s",
            $date_cutoff
        ));
        
        return $deleted;
    }
    
    /**
     * Export audit trail data
     */
    public function export($format = 'csv', $filters = []) {
        $entries = $this->get_entries(10000, 0, $filters); // Get up to 10,000 entries
        
        if ($format === 'csv') {
            return $this->export_csv($entries);
        } elseif ($format === 'json') {
            return $this->export_json($entries);
        }
        
        return false;
    }
    
    /**
     * Export to CSV
     */
    private function export_csv($entries) {
        $filename = 'audit_trail_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'ID', 'User ID', 'User Login', 'User Role', 'Action', 'Object Type', 'Object ID',
            'Description', 'IP Address', 'User Agent', 'Metadata', 'Severity', 'Status', 'Created At'
        ]);
        
        // CSV data
        foreach ($entries as $entry) {
            fputcsv($output, [
                $entry->id,
                $entry->user_id,
                $entry->user_login,
                $entry->user_role,
                $entry->action,
                $entry->object_type,
                $entry->object_id,
                $entry->description,
                $entry->ip_address,
                $entry->user_agent,
                $entry->metadata,
                $entry->severity,
                $entry->status,
                $entry->created_at
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export to JSON
     */
    private function export_json($entries) {
        $filename = 'audit_trail_' . date('Y-m-d_H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo wp_json_encode($entries, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Get audit trail actions
     */
    public static function get_actions() {
        return [
            'login' => 'User Login',
            'logout' => 'User Logout',
            'settings_update' => 'Settings Updated',
            'settings_reset' => 'Settings Reset',
            'content_index' => 'Content Indexed',
            'content_unindex' => 'Content Unindexed',
            'export_data' => 'Data Exported',
            'import_data' => 'Data Imported',
            'chat_start' => 'Chat Started',
            'chat_end' => 'Chat Ended',
            'api_call' => 'API Call Made',
            'error_occurred' => 'Error Occurred',
            'permission_denied' => 'Permission Denied',
            'system_backup' => 'System Backup',
            'system_restore' => 'System Restore',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_deleted' => 'User Deleted',
            'role_changed' => 'Role Changed',
            'security_scan' => 'Security Scan',
            'audit_export' => 'Audit Trail Exported',
            'audit_cleanup' => 'Audit Trail Cleaned'
        ];
    }
    
    /**
     * Get object types
     */
    public static function get_object_types() {
        return [
            'user' => 'User',
            'settings' => 'Settings',
            'post' => 'Post',
            'page' => 'Page',
            'chat' => 'Chat',
            'api' => 'API',
            'system' => 'System',
            'security' => 'Security',
            'audit' => 'Audit Trail',
            'export' => 'Export',
            'import' => 'Import'
        ];
    }
}

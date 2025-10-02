<?php

namespace WP_GPT_RAG_Chat;

/**
 * Database Migration Class
 * Handles schema updates and data migrations
 */
class Migration {
    
    /**
     * Run all pending migrations
     */
    public static function run_migrations() {
        $current_version = get_option('wp_gpt_rag_chat_db_version', '1.0.0');
        
        // Migration to version 2.0.0 (Analytics schema)
        if (version_compare($current_version, '2.0.0', '<')) {
            self::migrate_to_2_0_0();
            update_option('wp_gpt_rag_chat_db_version', '2.0.0');
        }
        
        // Migration to version 2.1.0 (RAG metadata)
        if (version_compare($current_version, '2.1.0', '<')) {
            self::migrate_to_2_1_0();
            update_option('wp_gpt_rag_chat_db_version', '2.1.0');
        }
    }
    
    /**
     * Migrate to 2.1.0 - Add RAG metadata column
     */
    private static function migrate_to_2_1_0() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Check if column already exists
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
        $column_names = wp_list_pluck($columns, 'Field');
        
        if (!in_array('rag_metadata', $column_names)) {
            error_log('WP GPT RAG Chat: Adding rag_metadata column');
            $wpdb->query("ALTER TABLE {$logs_table} 
                ADD COLUMN rag_metadata LONGTEXT DEFAULT NULL AFTER tokens_used
            ");
        }
        
        return true;
    }
    
    /**
     * Migrate from old schema to new analytics schema
     */
    private static function migrate_to_2_0_0() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Check if old columns exist
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
        $column_names = wp_list_pluck($columns, 'Field');
        
        $has_old_schema = in_array('query', $column_names) || in_array('response', $column_names);
        $has_new_schema = in_array('content', $column_names) && in_array('role', $column_names);
        
        if (!$has_old_schema && $has_new_schema) {
            // Already migrated
            return true;
        }
        
        if ($has_old_schema && !$has_new_schema) {
            // Need to migrate from old to new schema
            error_log('WP GPT RAG Chat: Starting migration to 2.0.0');
            
            // Step 1: Add new columns
            $wpdb->query("ALTER TABLE {$logs_table} 
                ADD COLUMN IF NOT EXISTS chat_id VARCHAR(64) DEFAULT '' AFTER id,
                ADD COLUMN IF NOT EXISTS turn_number INT(11) DEFAULT 1 AFTER chat_id,
                ADD COLUMN IF NOT EXISTS role ENUM('user','assistant') DEFAULT 'user' AFTER turn_number,
                ADD COLUMN IF NOT EXISTS content TEXT AFTER ip_address,
                ADD COLUMN IF NOT EXISTS response_latency INT(11) DEFAULT NULL AFTER content,
                ADD COLUMN IF NOT EXISTS sources_count INT(11) DEFAULT 0 AFTER response_latency,
                ADD COLUMN IF NOT EXISTS rag_sources LONGTEXT DEFAULT NULL AFTER sources_count,
                ADD COLUMN IF NOT EXISTS rating TINYINT(1) DEFAULT NULL AFTER rag_sources,
                ADD COLUMN IF NOT EXISTS tags VARCHAR(500) DEFAULT NULL AFTER rating,
                ADD COLUMN IF NOT EXISTS model_used VARCHAR(100) DEFAULT NULL AFTER tags,
                ADD COLUMN IF NOT EXISTS tokens_used INT(11) DEFAULT NULL AFTER model_used
            ");
            
            // Step 2: Migrate old data
            // Generate unique chat_id for each old conversation
            $old_logs = $wpdb->get_results("SELECT * FROM {$logs_table} WHERE chat_id = '' OR chat_id IS NULL ORDER BY created_at ASC");
            
            foreach ($old_logs as $log) {
                // Generate chat_id based on timestamp and user
                $chat_id = 'chat_legacy_' . md5($log->user_id . '_' . $log->created_at);
                
                // If we have 'query' column, it's a user message
                if (isset($log->query) && !empty($log->query)) {
                    $wpdb->update(
                        $logs_table,
                        [
                            'chat_id' => $chat_id,
                            'turn_number' => 1,
                            'role' => 'user',
                            'content' => $log->query
                        ],
                        ['id' => $log->id]
                    );
                    
                    // If there's a response, create assistant entry
                    if (isset($log->response) && !empty($log->response)) {
                        $wpdb->insert(
                            $logs_table,
                            [
                                'chat_id' => $chat_id,
                                'turn_number' => 1,
                                'role' => 'assistant',
                                'user_id' => $log->user_id,
                                'ip_address' => $log->ip_address,
                                'content' => $log->response,
                                'created_at' => $log->created_at
                            ]
                        );
                    }
                }
            }
            
            // Step 3: Add indexes
            $wpdb->query("ALTER TABLE {$logs_table} 
                ADD INDEX IF NOT EXISTS idx_chat_id (chat_id),
                ADD INDEX IF NOT EXISTS idx_role (role),
                ADD INDEX IF NOT EXISTS idx_rating (rating),
                ADD INDEX IF NOT EXISTS idx_model_used (model_used)
            ");
            
            // Step 4: Drop old columns (optional - keep them for safety initially)
            // $wpdb->query("ALTER TABLE {$logs_table} DROP COLUMN IF EXISTS query, DROP COLUMN IF EXISTS response");
            
            error_log('WP GPT RAG Chat: Migration to 2.0.0 completed');
            return true;
        }
        
        if ($has_old_schema && $has_new_schema) {
            // Both schemas exist - migrate remaining old data
            $wpdb->query("
                UPDATE {$logs_table} 
                SET content = query, role = 'user' 
                WHERE (content IS NULL OR content = '') AND query IS NOT NULL AND query != ''
            ");
            
            // Note: Old response data can't be easily migrated without losing the separation
            // Recommend manual cleanup or keeping old columns
            return true;
        }
        
        return true;
    }
    
    /**
     * Check database health and compatibility
     */
    public static function check_database_health() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$logs_table}'") === $logs_table;
        
        if (!$table_exists) {
            return [
                'status' => 'error',
                'message' => 'Logs table does not exist. Please deactivate and reactivate the plugin.'
            ];
        }
        
        // Check required columns
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
        $column_names = wp_list_pluck($columns, 'Field');
        
        $required_columns = ['chat_id', 'turn_number', 'role', 'content', 'created_at'];
        $missing_columns = array_diff($required_columns, $column_names);
        
        if (!empty($missing_columns)) {
            return [
                'status' => 'warning',
                'message' => 'Database schema needs updating. Missing columns: ' . implode(', ', $missing_columns)
            ];
        }
        
        return [
            'status' => 'ok',
            'message' => 'Database schema is up to date'
        ];
    }
}


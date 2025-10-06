<?php

namespace WP_GPT_RAG_Chat;

/**
 * Indexing Queue Management Class
 */
class Indexing_Queue {
    
    /**
     * Get the queue table name
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'wp_gpt_rag_indexing_queue';
    }
    
    /**
     * Add a post to the indexing queue
     */
    public static function add_to_queue($post_id, $priority = 0) {
        global $wpdb;
        
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        $table = self::get_table_name();
        
        // Check if post is already in queue
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id, status FROM $table WHERE post_id = %d",
            $post_id
        ));
        
        if ($existing) {
            // If already completed, don't re-enqueue by default
            if ($existing->status === 'completed') {
                return $existing->id;
            }
            // If already pending or processing, keep as-is (avoid duplicating work)
            if (in_array($existing->status, ['pending', 'processing'], true)) {
                return $existing->id;
            }
            // If previously failed, reset and retry
            $wpdb->update(
                $table,
                [
                    'status' => 'pending',
                    'priority' => $priority,
                    'attempts' => 0,
                    'error_message' => null,
                    'scheduled_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ],
                ['post_id' => $post_id],
                ['%s', '%d', '%d', '%s', '%s', '%s'],
                ['%d']
            );
            return $existing->id;
        }
        
        // Insert new entry
        $result = $wpdb->insert(
            $table,
            [
                'post_id' => $post_id,
                'post_type' => $post->post_type,
                'post_title' => $post->post_title,
                'status' => 'pending',
                'priority' => $priority,
                'scheduled_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s']
        );
        
        // Debug logging
        if ($result === false) {
            error_log('WP GPT RAG Chat: Failed to insert into queue table. Error: ' . $wpdb->last_error);
            error_log('WP GPT RAG Chat: Insert data: ' . print_r([
                'post_id' => $post_id,
                'post_type' => $post->post_type,
                'post_title' => $post->post_title,
                'status' => 'pending',
                'priority' => $priority,
                'scheduled_at' => current_time('mysql')
            ], true));
        } else {
            error_log('WP GPT RAG Chat: Successfully inserted post ' . $post_id . ' into queue table with ID: ' . $wpdb->insert_id);
        }
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Add multiple posts to the queue
     */
    public static function add_posts_to_queue($post_ids, $priority = 0) {
        $added_count = 0;
        foreach ($post_ids as $post_id) {
            if (self::add_to_queue($post_id, $priority)) {
                $added_count++;
            }
        }
        return $added_count;
    }
    
    /**
     * Get next batch of posts to process
     */
    public static function get_next_batch($limit = 10, $post_type = '') {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $where_conditions = ["status = 'pending'"];
        $where_values = [];
        
        if ($post_type && $post_type !== 'all') {
            $where_conditions[] = "post_type = %s";
            $where_values[] = $post_type;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT * FROM $table WHERE $where_clause ORDER BY priority DESC, scheduled_at ASC LIMIT %d";
        $where_values[] = $limit;
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        } else {
            $sql = $wpdb->prepare($sql, $limit);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Mark a post as processing
     */
    public static function mark_processing($post_id) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        return $wpdb->update(
            $table,
            [
                'status' => 'processing',
                'started_at' => current_time('mysql'),
                'attempts' => $wpdb->get_var($wpdb->prepare("SELECT attempts FROM $table WHERE post_id = %d", $post_id)) + 1
            ],
            ['post_id' => $post_id],
            ['%s', '%s', '%d'],
            ['%d']
        );
    }
    
    /**
     * Mark a post as completed
     */
    public static function mark_completed($post_id) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        return $wpdb->update(
            $table,
            [
                'status' => 'completed',
                'completed_at' => current_time('mysql'),
                'error_message' => null
            ],
            ['post_id' => $post_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * Mark a post as failed
     */
    public static function mark_failed($post_id, $error_message = '') {
        global $wpdb;
        
        $table = self::get_table_name();
        
        // Check if we should retry or mark as permanently failed
        $attempts = $wpdb->get_var($wpdb->prepare("SELECT attempts FROM $table WHERE post_id = %d", $post_id));
        $max_attempts = $wpdb->get_var($wpdb->prepare("SELECT max_attempts FROM $table WHERE post_id = %d", $post_id));
        
        $status = ($attempts >= $max_attempts) ? 'failed' : 'pending';
        
        return $wpdb->update(
            $table,
            [
                'status' => $status,
                'error_message' => $error_message,
                'scheduled_at' => current_time('mysql') // Reschedule for retry
            ],
            ['post_id' => $post_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * Remove a post from the queue
     */
    public static function remove_from_queue($post_id) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        return $wpdb->delete(
            $table,
            ['post_id' => $post_id],
            ['%d']
        );
    }
    
    /**
     * Get queue statistics
     */
    public static function get_queue_stats($post_type = '') {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $where_conditions = [];
        $where_values = [];
        
        if ($post_type && $post_type !== 'all') {
            $where_conditions[] = "post_type = %s";
            $where_values[] = $post_type;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM $table 
                $where_clause
                GROUP BY status";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $results = $wpdb->get_results($sql);
        
        $stats = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
            'total' => 0
        ];
        
        foreach ($results as $result) {
            $stats[$result->status] = (int) $result->count;
            $stats['total'] += (int) $result->count;
        }
        
        return $stats;
    }
    
    /**
     * Get queue items for display
     */
    public static function get_queue_items($limit = 20, $offset = 0, $status = '', $post_type = '') {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $where_conditions = [];
        $where_values = [];
        
        if ($status && $status !== 'all') {
            $where_conditions[] = "status = %s";
            $where_values[] = $status;
        }
        
        if ($post_type && $post_type !== 'all') {
            $where_conditions[] = "post_type = %s";
            $where_values[] = $post_type;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT * FROM $table 
                $where_clause
                ORDER BY priority DESC, created_at DESC 
                LIMIT %d OFFSET %d";
        
        $where_values[] = $limit;
        $where_values[] = $offset;
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        } else {
            $sql = $wpdb->prepare($sql, $limit, $offset);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Clear completed items from queue
     */
    public static function clear_completed() {
        global $wpdb;
        
        $table = self::get_table_name();
        
        return $wpdb->delete(
            $table,
            ['status' => 'completed'],
            ['%s']
        );
    }
    
    /**
     * Clear all items from queue
     */
    public static function clear_all() {
        global $wpdb;
        
        $table = self::get_table_name();
        
        return $wpdb->query("TRUNCATE TABLE $table");
    }
    
    /**
     * Check if a post is in the queue
     */
    public static function is_in_queue($post_id) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM $table WHERE post_id = %d",
            $post_id
        ));
        
        return $status !== null;
    }
    
    /**
     * Get queue status for a specific post
     */
    public static function get_post_queue_status($post_id) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE post_id = %d",
            $post_id
        ));
    }
}

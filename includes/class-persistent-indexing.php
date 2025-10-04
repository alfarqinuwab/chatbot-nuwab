<?php

namespace WP_GPT_RAG_Chat;

/**
 * Persistent Indexing Manager
 * Handles background indexing that continues even when user navigates away
 */
class Persistent_Indexing {
    
    const INDEXING_STATE_KEY = 'wp_gpt_rag_chat_indexing_state';
    const INDEXING_QUEUE_KEY = 'wp_gpt_rag_chat_indexing_queue';
    const INDEXING_PROGRESS_KEY = 'wp_gpt_rag_chat_indexing_progress';
    
    /**
     * Start persistent indexing
     */
    public static function start_indexing($action, $post_type = 'all', $total_posts = 0) {
        $state = [
            'action' => $action,
            'post_type' => $post_type,
            'total_posts' => $total_posts,
            'processed' => 0,
            'current_offset' => 0,
            'batch_size' => 10, // Start with 10, can be reduced on errors
            'started_at' => current_time('mysql'),
            'status' => 'running',
            'user_id' => get_current_user_id()
        ];
        
        set_transient(self::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
        
        // Schedule the first batch
        self::schedule_next_batch();
        
        return $state;
    }
    
    /**
     * Get current indexing state
     */
    public static function get_indexing_state() {
        return get_transient(self::INDEXING_STATE_KEY) ?: null;
    }
    
    /**
     * Update indexing progress
     */
    public static function update_progress($processed, $current_offset) {
        $state = self::get_indexing_state();
        if (!$state) {
            return false;
        }
        
        $state['processed'] += $processed;
        $state['current_offset'] = $current_offset;
        $state['last_updated'] = current_time('mysql');
        
        set_transient(self::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
        
        return $state;
    }
    
    /**
     * Complete indexing
     */
    public static function complete_indexing() {
        $state = self::get_indexing_state();
        if (!$state) {
            return false;
        }
        
        $state['status'] = 'completed';
        $state['completed_at'] = current_time('mysql');
        
        set_transient(self::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
        
        // UNSCHEDULE ALL cron jobs when completed
        self::unschedule_all_batches();
        
        // Clean up after 5 minutes
        wp_schedule_single_event(time() + 300, 'wp_gpt_rag_chat_cleanup_indexing_state');
        
        return $state;
    }
    
    /**
     * Cancel indexing
     */
    public static function cancel_indexing() {
        $state = self::get_indexing_state();
        if (!$state) {
            return false;
        }
        
        $state['status'] = 'cancelled';
        $state['cancelled_at'] = current_time('mysql');
        
        set_transient(self::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
        
        // UNSCHEDULE ALL cron jobs to stop background processing
        self::unschedule_all_batches();
        
        // Clean up after 5 minutes
        wp_schedule_single_event(time() + 300, 'wp_gpt_rag_chat_cleanup_indexing_state');
        
        return $state;
    }
    
    /**
     * Check if indexing is in progress
     */
    public static function is_indexing_in_progress() {
        $state = self::get_indexing_state();
        return $state && $state['status'] === 'running';
    }
    
    /**
     * Schedule next batch for processing
     */
    public static function schedule_next_batch() {
        // Schedule the batch processing with a small delay
        wp_schedule_single_event(time() + 2, 'wp_gpt_rag_chat_process_indexing_batch');
    }
    
    /**
     * Unschedule ALL batch processing events
     */
    public static function unschedule_all_batches() {
        // Clear all scheduled events for this hook
        $count = 0;
        while ($timestamp = wp_next_scheduled('wp_gpt_rag_chat_process_indexing_batch')) {
            wp_unschedule_event($timestamp, 'wp_gpt_rag_chat_process_indexing_batch');
            $count++;
            error_log('WP GPT RAG Chat: Unscheduled batch at timestamp ' . $timestamp);
            
            // Safety break after 100 iterations
            if ($count > 100) {
                break;
            }
        }
        
        // Also clear using wp_clear_scheduled_hook for safety
        wp_clear_scheduled_hook('wp_gpt_rag_chat_process_indexing_batch');
        error_log('WP GPT RAG Chat: Cleared all scheduled indexing batch hooks (total: ' . $count . ')');
    }
    
    /**
     * Process a batch of indexing
     */
    public static function process_batch() {
        // CRITICAL: Always get fresh state at the beginning
        $state = self::get_indexing_state();
        
        // Stop immediately if no state or not running
        if (!$state || $state['status'] !== 'running') {
            error_log('WP GPT RAG Chat: process_batch stopped - status is ' . ($state ? $state['status'] : 'no state'));
            // Unschedule any remaining cron jobs
            self::unschedule_all_batches();
            return;
        }
        
        // Check if we should continue
        if ($state['current_offset'] >= $state['total_posts']) {
            self::complete_indexing();
            return;
        }
        
        try {
            // Process the batch
            $indexing = new Indexing();
            $batch_size = $state['batch_size'] ?? 10;
            $result = $indexing->index_all_content($batch_size, $state['current_offset'], $state['post_type']);
            
            // Update progress
            $processed = $result['processed'] ?? 0;
            $new_offset = $state['current_offset'] + $processed;
            
            // Get newly indexed items for real-time updates
            $newly_indexed = [];
            if (!empty($result['indexed_post_ids'])) {
                foreach ($result['indexed_post_ids'] as $post_id) {
                    $post = get_post($post_id);
                    if ($post) {
                        $newly_indexed[] = [
                            'id' => $post_id,
                            'title' => $post->post_title,
                            'type' => $post->post_type,
                            'edit_url' => get_edit_post_link($post_id)
                        ];
                    }
                }
            }
            
            // Reset batch size to 10 on successful batch (if it was reduced)
            if ($state['batch_size'] < 10) {
                $state['batch_size'] = 10;
                error_log('WP GPT RAG Chat: Resetting batch size to 10 after successful batch');
            }
            
            // Update progress (this will get fresh state and update it)
            $state = self::update_progress($processed, $new_offset);
            
            // NOW add newly indexed items to the updated state
            if ($state) {
                $state['newly_indexed'] = $newly_indexed;
                $state['last_batch_processed'] = $processed;
                $state['last_batch_time'] = current_time('mysql');
                
                // Save the updated state with newly_indexed
                set_transient(self::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
                
                if (!empty($newly_indexed)) {
                    error_log('WP GPT RAG Chat: Added ' . count($newly_indexed) . ' newly indexed items to state: ' . print_r(array_column($newly_indexed, 'id'), true));
                }
            }
            
            // Schedule next batch if there's more to process
            if ($new_offset < $state['total_posts'] && $processed > 0) {
                self::schedule_next_batch();
            } else {
                self::complete_indexing();
            }
            
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Batch processing error: ' . $e->getMessage());
            
            // Check if it's a 500 error and we can reduce batch size
            if (strpos($e->getMessage(), '500') !== false && $state['batch_size'] > 1) {
                // Reduce batch size and retry
                $state['batch_size'] = max(1, $state['batch_size'] - 5);
                error_log('WP GPT RAG Chat: Reducing batch size to ' . $state['batch_size'] . ' due to 500 error');
                
                // Update state and schedule retry
                set_transient(self::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
                self::schedule_next_batch();
            } else {
                // Mark as error and stop
                $state['status'] = 'error';
                $state['error'] = $e->getMessage();
                $state['error_at'] = current_time('mysql');
                set_transient(self::INDEXING_STATE_KEY, $state, HOUR_IN_SECONDS * 2);
                
                // UNSCHEDULE ALL cron jobs on error
                self::unschedule_all_batches();
            }
        }
    }
    
    /**
     * Clean up indexing state
     */
    public static function cleanup_indexing_state() {
        delete_transient(self::INDEXING_STATE_KEY);
        delete_transient(self::INDEXING_QUEUE_KEY);
        delete_transient(self::INDEXING_PROGRESS_KEY);
    }
    
    /**
     * Get indexing progress percentage
     */
    public static function get_progress_percentage() {
        $state = self::get_indexing_state();
        if (!$state || $state['total_posts'] == 0) {
            return 0;
        }
        
        return min(100, round(($state['processed'] / $state['total_posts']) * 100, 2));
    }
    
    /**
     * Get formatted progress text
     */
    public static function get_progress_text() {
        $state = self::get_indexing_state();
        if (!$state) {
            return '';
        }
        
        $percentage = self::get_progress_percentage();
        return sprintf(
            __('Indexing... %d / %d (%s%%)', 'wp-gpt-rag-chat'),
            $state['processed'],
            $state['total_posts'],
            $percentage
        );
    }
    
    /**
     * Get next batch of posts for pending display
     */
    public static function get_next_batch_for_pending($limit = 10, $offset = 0, $post_type = '') {
        $query_args = [
            'numberposts' => $limit,
            'offset' => $offset,
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ],
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];
        
        // Add post type filter if specified
        if ($post_type && $post_type !== 'all') {
            $query_args['post_type'] = $post_type;
        } else {
            $query_args['post_type'] = get_post_types(['public' => true]);
        }
        
        $posts = get_posts($query_args);
        
        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'edit_url' => get_edit_post_link($post->ID)
            ];
        }
        
        return $items;
    }
    
    /**
     * Get all posts that should be indexed (for pending display)
     */
    public static function get_all_posts_for_pending($post_type = '') {
        $query_args = [
            'numberposts' => -1,
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ],
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ];
        
        // Add post type filter if specified
        if ($post_type && $post_type !== 'all') {
            $query_args['post_type'] = $post_type;
        } else {
            $query_args['post_type'] = get_post_types(['public' => true]);
        }
        
        $posts = get_posts($query_args);
        
        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'edit_url' => get_edit_post_link($post->ID)
            ];
        }
        
        return $items;
    }
}

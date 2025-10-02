<?php

namespace WP_GPT_RAG_Chat;

/**
 * Import Protection System
 * Automatically detects bulk imports and prevents auto-indexing during import
 */
class Import_Protection {
    
    const TRANSIENT_KEY = 'wp_gpt_rag_import_protection';
    const IMPORT_THRESHOLD = 10; // Number of posts in short time to trigger protection
    const TIME_WINDOW = 60; // seconds
    
    private static $post_count = 0;
    private static $start_time = null;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Monitor post creation
        add_action('wp_insert_post', [$this, 'monitor_post_creation'], 1, 3);
        
        // Add admin notice
        add_action('admin_notices', [$this, 'import_protection_notice']);
    }
    
    /**
     * Check if import protection is active
     */
    public static function is_active() {
        return (bool) get_transient(self::TRANSIENT_KEY);
    }
    
    /**
     * Activate import protection
     */
    public static function activate($duration = HOUR_IN_SECONDS) {
        set_transient(self::TRANSIENT_KEY, [
            'activated_at' => time(),
            'post_count' => self::$post_count
        ], $duration);
        
        // Also activate emergency stop
        Emergency_Stop::activate($duration);
        
        error_log('WP GPT RAG Chat: Import protection activated - detected bulk import');
    }
    
    /**
     * Deactivate import protection
     */
    public static function deactivate() {
        delete_transient(self::TRANSIENT_KEY);
        error_log('WP GPT RAG Chat: Import protection deactivated');
    }
    
    /**
     * Monitor post creation for bulk imports
     */
    public function monitor_post_creation($post_id, $post, $update) {
        // Skip if already active
        if (self::is_active()) {
            return;
        }
        
        // Skip revisions and autosaves
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Skip if it's an update (not a new post)
        if ($update) {
            return;
        }
        
        // Initialize tracking
        if (self::$start_time === null) {
            self::$start_time = time();
            self::$post_count = 0;
        }
        
        // Reset if time window expired
        if (time() - self::$start_time > self::TIME_WINDOW) {
            self::$start_time = time();
            self::$post_count = 0;
        }
        
        // Increment counter
        self::$post_count++;
        
        // Check if threshold exceeded (bulk import detected)
        if (self::$post_count >= self::IMPORT_THRESHOLD) {
            self::activate();
            
            // Set notice
            set_transient('wp_gpt_rag_import_detected', self::$post_count, 300);
        }
    }
    
    /**
     * Display import protection notice
     */
    public function import_protection_notice() {
        $import_count = get_transient('wp_gpt_rag_import_detected');
        
        if ($import_count) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <h3>🛡️ WP GPT RAG Chat - Import Protection Activated</h3>
                <p>
                    <strong>Bulk import detected!</strong> The system detected <strong><?php echo number_format($import_count); ?> posts</strong> 
                    being created rapidly and automatically stopped indexing to prevent system overload.
                </p>
                <p>
                    <strong>What this means:</strong> Your imported posts will NOT be automatically indexed. 
                    This is intentional to prevent your server from being overwhelmed.
                </p>
                <p>
                    <strong>To index your imported content:</strong>
                </p>
                <ol style="margin-left: 20px;">
                    <li>Go to the <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>"><strong>Content Indexing</strong></a> page</li>
                    <li>Review which posts you want to index</li>
                    <li>Use the "Sync All" button to index in controlled batches</li>
                </ol>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button button-primary">
                        Go to Indexing Page
                    </a>
                    <button type="button" class="button" onclick="wpGptRagResumeIndexing();">
                        Resume Auto-Indexing
                    </button>
                </p>
            </div>
            <?php
        }
    }
}

// Initialize
new Import_Protection();


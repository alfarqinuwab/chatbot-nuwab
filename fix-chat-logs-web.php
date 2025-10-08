<?php
/**
 * Web-based fix script for chat logs display issue
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/fix-chat-logs-web.php
 */

// Load WordPress
require_once('../../../wp-config.php');
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat Logs Fix Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        h1 { color: #23282d; }
        h2 { color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .button:hover { background: #005a87; }
        .button.danger { background: #dc3232; }
        .button.danger:hover { background: #a00; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Chat Logs Fix Tool</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                global $wpdb;
                $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
                
                switch ($_POST['action']) {
                    case 'diagnose':
                        echo "<h2>📊 Diagnosis Results</h2>\n";
                        
                        // Check table exists
                        $exists = $wpdb->get_var("SHOW TABLES LIKE '$logs_table'");
                        if ($exists) {
                            echo "<p class='success'>✅ Chat logs table exists: $logs_table</p>\n";
                        } else {
                            echo "<p class='error'>❌ Chat logs table missing: $logs_table</p>\n";
                            break;
                        }
                        
                        // Check total records
                        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table");
                        echo "<p class='info'>📈 Total records in table: $total_records</p>\n";
                        
                        // Check recent records
                        $recent_logs = $wpdb->get_results("SELECT id, chat_id, role, user_id, content, created_at FROM $logs_table ORDER BY created_at DESC LIMIT 5");
                        if ($recent_logs) {
                            echo "<p class='info'>📝 Recent records:</p>\n";
                            echo "<pre>";
                            foreach ($recent_logs as $log) {
                                $content_preview = substr($log->content, 0, 50) . '...';
                                echo "ID: {$log->id}, Role: {$log->role}, User ID: {$log->user_id}, Content: $content_preview, Created: {$log->created_at}\n";
                            }
                            echo "</pre>";
                        }
                        
                        // Check today's records
                        $today = date('Y-m-d');
                        $today_logs = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE DATE(created_at) = '$today'");
                        echo "<p class='info'>📅 Records created today: $today_logs</p>\n";
                        
                        // Test analytics
                        if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
                            $analytics = new WP_GPT_RAG_Chat\Analytics();
                            $analytics_count = $analytics->get_logs_count();
                            echo "<p class='success'>✅ Analytics class working - Count: $analytics_count</p>\n";
                        } else {
                            echo "<p class='error'>❌ Analytics class not found</p>\n";
                        }
                        break;
                        
                    case 'fix':
                        echo "<h2>🔧 Fix Results</h2>\n";
                        
                        // Check table exists
                        $exists = $wpdb->get_var("SHOW TABLES LIKE '$logs_table'");
                        if (!$exists) {
                            echo "<p class='error'>❌ Chat logs table missing. Please activate the plugin first.</p>\n";
                            break;
                        }
                        
                        // Fix NULL created_at values
                        $null_dates = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
                        if ($null_dates > 0) {
                            $result = $wpdb->query("UPDATE $logs_table SET created_at = NOW() WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
                            echo "<p class='success'>✅ Fixed $null_dates records with NULL created_at</p>\n";
                        } else {
                            echo "<p class='info'>ℹ️ No NULL created_at values found</p>\n";
                        }
                        
                        // Fix NULL user_id values
                        $null_users = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE user_id IS NULL");
                        if ($null_users > 0) {
                            $result = $wpdb->query("UPDATE $logs_table SET user_id = 0 WHERE user_id IS NULL");
                            echo "<p class='success'>✅ Fixed $null_users records with NULL user_id</p>\n";
                        } else {
                            echo "<p class='info'>ℹ️ No NULL user_id values found</p>\n";
                        }
                        
                        // Add indexes
                        $indexes = [
                            'idx_chat_id' => 'chat_id',
                            'idx_role' => 'role',
                            'idx_created_at' => 'created_at',
                            'idx_user_id' => 'user_id'
                        ];
                        
                        foreach ($indexes as $index_name => $column) {
                            $result = $wpdb->query("ALTER TABLE $logs_table ADD INDEX IF NOT EXISTS $index_name ($column)");
                            if ($result !== false) {
                                echo "<p class='success'>✅ Added index: $index_name</p>\n";
                            } else {
                                echo "<p class='warning'>⚠️ Index $index_name might already exist</p>\n";
                            }
                        }
                        
                        // Clear caches
                        if (function_exists('wp_cache_flush')) {
                            wp_cache_flush();
                            echo "<p class='success'>✅ WordPress cache cleared</p>\n";
                        }
                        
                        echo "<p class='success'>🎉 Fix completed! Try chatting on the frontend now.</p>\n";
                        break;
                        
                    case 'test':
                        echo "<h2>🧪 Test Results</h2>\n";
                        
                        // Test log insertion
                        $test_data = [
                            'chat_id' => 'test_web_' . time(),
                            'turn_number' => 1,
                            'role' => 'user',
                            'user_id' => 0,
                            'ip_address' => '127.0.0.1',
                            'content' => 'Test message from web fix tool - ' . date('Y-m-d H:i:s'),
                            'created_at' => current_time('mysql')
                        ];
                        
                        $insert_result = $wpdb->insert($logs_table, $test_data);
                        if ($insert_result) {
                            $insert_id = $wpdb->insert_id;
                            echo "<p class='success'>✅ Test log inserted with ID: $insert_id</p>\n";
                            
                            // Verify it can be retrieved
                            $retrieved = $wpdb->get_row($wpdb->prepare("SELECT * FROM $logs_table WHERE id = %d", $insert_id));
                            if ($retrieved) {
                                echo "<p class='success'>✅ Test log retrieved successfully</p>\n";
                                echo "<p class='info'>Content: " . substr($retrieved->content, 0, 50) . "...</p>\n";
                                
                                // Clean up
                                $wpdb->delete($logs_table, ['id' => $insert_id]);
                                echo "<p class='success'>✅ Test log cleaned up</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ Failed to insert test log: " . $wpdb->last_error . "</p>\n";
                        }
                        break;
                        
                    case 'clear':
                        echo "<h2>🗑️ Clear Results</h2>\n";
                        
                        // Clear all logs (with confirmation)
                        if (isset($_POST['confirm_clear']) && $_POST['confirm_clear'] === 'yes') {
                            $deleted = $wpdb->query("DELETE FROM $logs_table");
                            echo "<p class='success'>✅ Deleted $deleted chat log records</p>\n";
                        } else {
                            echo "<p class='error'>❌ Clear operation cancelled - confirmation required</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Available Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="diagnose">
            <button type="submit" class="button">📊 Diagnose Issue</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="fix">
            <button type="submit" class="button">🔧 Fix Issues</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test">
            <button type="submit" class="button">🧪 Test Log Insertion</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="clear">
            <input type="hidden" name="confirm_clear" value="yes">
            <button type="submit" class="button danger" onclick="return confirm('Are you sure you want to delete ALL chat logs? This cannot be undone!')">🗑️ Clear All Logs</button>
        </form>
        
        <h2>📋 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-analytics" class="button">📈 View Analytics Page</a>
            <a href="/wp-admin/plugins.php" class="button">🔌 Plugin Management</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat Test</a>
        </p>
        
        <h2>ℹ️ Instructions</h2>
        <ol>
            <li><strong>Diagnose Issue:</strong> Check what's wrong with the chat logs</li>
            <li><strong>Fix Issues:</strong> Automatically fix common problems</li>
            <li><strong>Test Log Insertion:</strong> Verify new logs can be created</li>
            <li><strong>Clear All Logs:</strong> Remove all chat logs (use with caution)</li>
        </ol>
        
        <h2>🔍 Common Issues</h2>
        <ul>
            <li><strong>New logs not showing:</strong> Usually fixed by running "Fix Issues"</li>
            <li><strong>NULL timestamps:</strong> Fixed automatically by the fix tool</li>
            <li><strong>Missing indexes:</strong> Added automatically for better performance</li>
            <li><strong>Cache issues:</strong> Cleared automatically during fix</li>
        </ul>
        
        <p><strong>Current User:</strong> <?php echo wp_get_current_user()->user_login; ?> (ID: <?php echo get_current_user_id(); ?>)</p>
        <p><strong>WordPress Time:</strong> <?php echo current_time('mysql'); ?></p>
        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>

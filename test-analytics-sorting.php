<?php
/**
 * Test analytics sorting fix
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/test-analytics-sorting.php
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
    <title>Test Analytics Sorting</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        h1 { color: #23282d; }
        h2 { color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f1f1f1; }
        .highlight { background: #fff3cd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test Analytics Sorting Fix</h1>
        
        <?php
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Test 1: Raw database query (sorted by ID DESC)
        echo "<h2>1. Raw Database Query (Sorted by ID DESC)</h2>\n";
        $raw_logs = $wpdb->get_results("
            SELECT id, chat_id, role, user_id, content, created_at, ip_address
            FROM $logs_table 
            ORDER BY id DESC 
            LIMIT 20
        ");
        
        echo "<table>\n";
        echo "<tr><th>ID</th><th>Chat ID</th><th>Role</th><th>User ID</th><th>IP</th><th>Created At</th><th>Content Preview</th></tr>\n";
        foreach ($raw_logs as $log) {
            $content_preview = substr($log->content, 0, 30) . '...';
            echo "<tr>";
            echo "<td>{$log->id}</td>";
            echo "<td>" . substr($log->chat_id, 0, 20) . "...</td>";
            echo "<td>{$log->role}</td>";
            echo "<td>{$log->user_id}</td>";
            echo "<td>{$log->ip_address}</td>";
            echo "<td>{$log->created_at}</td>";
            echo "<td>$content_preview</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
        
        // Test 2: Analytics class results (should now match)
        echo "<h2>2. Analytics Class Results (Should Match Above)</h2>\n";
        if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
            $analytics = new WP_GPT_RAG_Chat\Analytics();
            $logs = $analytics->get_logs(['limit' => 20]);
            
            echo "<table>\n";
            echo "<tr><th>ID</th><th>Chat ID</th><th>Role</th><th>User ID</th><th>IP</th><th>Created At</th><th>Content Preview</th></tr>\n";
            foreach ($logs as $log) {
                $content_preview = substr($log->content, 0, 30) . '...';
                echo "<tr>";
                echo "<td>{$log->id}</td>";
                echo "<td>" . substr($log->chat_id, 0, 20) . "...</td>";
                echo "<td>{$log->role}</td>";
                echo "<td>{$log->user_id}</td>";
                echo "<td>{$log->ip_address}</td>";
                echo "<td>{$log->created_at}</td>";
                echo "<td>$content_preview</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
            
            // Compare first few IDs
            echo "<h3>3. Comparison</h3>\n";
            $raw_ids = array_column($raw_logs, 'id');
            $analytics_ids = array_column($logs, 'id');
            
            if ($raw_ids === $analytics_ids) {
                echo "<p class='success'>✅ SUCCESS: Analytics class now returns the same order as raw database query!</p>\n";
            } else {
                echo "<p class='error'>❌ MISMATCH: Analytics class still returns different order</p>\n";
                echo "<p class='info'>Raw DB IDs: " . implode(', ', array_slice($raw_ids, 0, 5)) . "...</p>\n";
                echo "<p class='info'>Analytics IDs: " . implode(', ', array_slice($analytics_ids, 0, 5)) . "...</p>\n";
            }
            
        } else {
            echo "<p class='error'>❌ Analytics class not found</p>\n";
        }
        
        // Test 3: Check if new logs appear at the top
        echo "<h2>4. Check for New Logs</h2>\n";
        $recent_logs = $wpdb->get_results("
            SELECT id, content, created_at
            FROM $logs_table 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY id DESC 
            LIMIT 5
        ");
        
        if (!empty($recent_logs)) {
            echo "<p class='success'>✅ Found " . count($recent_logs) . " recent logs (last hour):</p>\n";
            echo "<ul>\n";
            foreach ($recent_logs as $log) {
                echo "<li>ID {$log->id}: " . substr($log->content, 0, 50) . "... ({$log->created_at})</li>\n";
            }
            echo "</ul>\n";
        } else {
            echo "<p class='warning'>⚠️ No logs found in the last hour</p>\n";
        }
        ?>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-analytics" class="button" target="_blank">📈 View Analytics Page</a>
            <a href="/" class="button" target="_blank">🌐 Test Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

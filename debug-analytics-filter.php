<?php
/**
 * Debug analytics filtering issue
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/debug-analytics-filter.php
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
    <title>Analytics Filter Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        h1 { color: #23282d; }
        h2 { color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .button:hover { background: #005a87; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f1f1f1; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Analytics Filter Debug Tool</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                global $wpdb;
                $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
                
                switch ($_POST['action']) {
                    case 'compare':
                        echo "<h2>📊 Compare Recent vs All Logs</h2>\n";
                        
                        // Get all logs ordered by created_at DESC
                        $all_logs = $wpdb->get_results("
                            SELECT id, chat_id, role, user_id, content, created_at, ip_address
                            FROM $logs_table 
                            ORDER BY created_at DESC 
                            LIMIT 20
                        ");
                        
                        echo "<h3>All Logs (Last 20):</h3>\n";
                        echo "<table>\n";
                        echo "<tr><th>ID</th><th>Chat ID</th><th>Role</th><th>User ID</th><th>IP</th><th>Created At</th><th>Content Preview</th></tr>\n";
                        foreach ($all_logs as $log) {
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
                        
                        // Test analytics class with different filters
                        if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
                            $analytics = new WP_GPT_RAG_Chat\Analytics();
                            
                            echo "<h3>Analytics Class Results:</h3>\n";
                            
                            // No filters
                            $no_filter = $analytics->get_logs(['limit' => 10]);
                            echo "<p class='info'>📈 No filters: " . count($no_filter) . " records</p>\n";
                            
                            // Today's logs
                            $today = date('Y-m-d');
                            $today_logs = $analytics->get_logs(['date_from' => $today, 'limit' => 10]);
                            echo "<p class='info'>📅 Today's logs: " . count($today_logs) . " records</p>\n";
                            
                            // Recent logs (last 24 hours)
                            $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
                            $recent_logs = $analytics->get_logs(['date_from' => $yesterday, 'limit' => 10]);
                            echo "<p class='info'>⏰ Last 24 hours: " . count($recent_logs) . " records</p>\n";
                            
                            // Show sample from analytics
                            if (!empty($no_filter)) {
                                echo "<h4>Sample from Analytics (No Filter):</h4>\n";
                                echo "<pre>";
                                $sample = $no_filter[0];
                                echo "ID: {$sample->id}\n";
                                echo "Role: {$sample->role}\n";
                                echo "User ID: {$sample->user_id}\n";
                                echo "Created: {$sample->created_at}\n";
                                echo "Content: " . substr($sample->content, 0, 50) . "...\n";
                                echo "</pre>";
                            }
                        }
                        break;
                        
                    case 'check_dates':
                        echo "<h2>📅 Date Analysis</h2>\n";
                        
                        // Check date ranges
                        $date_ranges = $wpdb->get_results("
                            SELECT 
                                DATE(created_at) as date,
                                COUNT(*) as count
                            FROM $logs_table 
                            GROUP BY DATE(created_at) 
                            ORDER BY date DESC 
                            LIMIT 10
                        ");
                        
                        echo "<h3>Logs by Date:</h3>\n";
                        echo "<table>\n";
                        echo "<tr><th>Date</th><th>Count</th></tr>\n";
                        foreach ($date_ranges as $range) {
                            $is_today = $range->date === date('Y-m-d') ? ' (TODAY)' : '';
                            echo "<tr><td>{$range->date}$is_today</td><td>{$range->count}</td></tr>\n";
                        }
                        echo "</table>\n";
                        
                        // Check for future dates
                        $future_logs = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM $logs_table 
                            WHERE created_at > NOW()
                        ");
                        echo "<p class='warning'>⚠️ Logs with future dates: $future_logs</p>\n";
                        
                        // Check for very old dates
                        $old_logs = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM $logs_table 
                            WHERE created_at < '2024-01-01'
                        ");
                        echo "<p class='info'>ℹ️ Logs before 2024: $old_logs</p>\n";
                        break;
                        
                    case 'fix_dates':
                        echo "<h2>🔧 Fix Date Issues</h2>\n";
                        
                        // Fix future dates to current time
                        $future_count = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM $logs_table 
                            WHERE created_at > NOW()
                        ");
                        
                        if ($future_count > 0) {
                            $result = $wpdb->query("
                                UPDATE $logs_table 
                                SET created_at = NOW() 
                                WHERE created_at > NOW()
                            ");
                            echo "<p class='success'>✅ Fixed $future_count logs with future dates</p>\n";
                        } else {
                            echo "<p class='info'>ℹ️ No future dates found</p>\n";
                        }
                        
                        // Update very old dates to current time
                        $old_count = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM $logs_table 
                            WHERE created_at < '2024-01-01'
                        ");
                        
                        if ($old_count > 0) {
                            $result = $wpdb->query("
                                UPDATE $logs_table 
                                SET created_at = NOW() 
                                WHERE created_at < '2024-01-01'
                            ");
                            echo "<p class='success'>✅ Fixed $old_count logs with very old dates</p>\n";
                        } else {
                            echo "<p class='info'>ℹ️ No very old dates found</p>\n";
                        }
                        
                        // Clear cache
                        if (function_exists('wp_cache_flush')) {
                            wp_cache_flush();
                            echo "<p class='success'>✅ Cache cleared</p>\n";
                        }
                        break;
                        
                    case 'test_new_log':
                        echo "<h2>🧪 Test New Log Creation</h2>\n";
                        
                        // Create a test log with current timestamp
                        $test_data = [
                            'chat_id' => 'test_analytics_' . time(),
                            'turn_number' => 1,
                            'role' => 'user',
                            'user_id' => 0,
                            'ip_address' => '127.0.0.1',
                            'content' => 'Test message for analytics debug - ' . date('Y-m-d H:i:s'),
                            'created_at' => current_time('mysql')
                        ];
                        
                        $insert_result = $wpdb->insert($logs_table, $test_data);
                        if ($insert_result) {
                            $insert_id = $wpdb->insert_id;
                            echo "<p class='success'>✅ Test log created with ID: $insert_id</p>\n";
                            
                            // Test if analytics can find it
                            if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
                                $analytics = new WP_GPT_RAG_Chat\Analytics();
                                $found_logs = $analytics->get_logs(['limit' => 5]);
                                
                                $found = false;
                                foreach ($found_logs as $log) {
                                    if ($log->id == $insert_id) {
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if ($found) {
                                    echo "<p class='success'>✅ Analytics can find the new log</p>\n";
                                } else {
                                    echo "<p class='error'>❌ Analytics cannot find the new log</p>\n";
                                }
                            }
                            
                            // Clean up
                            $wpdb->delete($logs_table, ['id' => $insert_id]);
                            echo "<p class='success'>✅ Test log cleaned up</p>\n";
                        } else {
                            echo "<p class='error'>❌ Failed to create test log: " . $wpdb->last_error . "</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Available Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="compare">
            <button type="submit" class="button">📊 Compare Recent vs All Logs</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_dates">
            <button type="submit" class="button">📅 Check Date Issues</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="fix_dates">
            <button type="submit" class="button">🔧 Fix Date Issues</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_new_log">
            <button type="submit" class="button">🧪 Test New Log</button>
        </form>
        
        <h2>🔍 What This Tool Does</h2>
        <ul>
            <li><strong>Compare Recent vs All Logs:</strong> Shows all recent logs and compares with what analytics returns</li>
            <li><strong>Check Date Issues:</strong> Analyzes date patterns to find future/old dates</li>
            <li><strong>Fix Date Issues:</strong> Corrects future dates and very old dates to current time</li>
            <li><strong>Test New Log:</strong> Creates a test log and verifies analytics can find it</li>
        </ul>
        
        <h2>🎯 Most Likely Issue</h2>
        <p>Based on your screenshots, the issue is likely that:</p>
        <ol>
            <li>New logs are being created with current timestamps</li>
            <li>Old imported logs have future dates (2025-10-08)</li>
            <li>The analytics page might be filtering by date or showing only the "newest" logs</li>
            <li>Since the imported logs have future dates, they appear "newer" than your actual new logs</li>
        </ol>
        
        <p><strong>Solution:</strong> Run "Check Date Issues" first, then "Fix Date Issues" to correct the future dates.</p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>

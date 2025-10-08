<?php
/**
 * Deep analytics debugging tool
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/deep-analytics-debug.php
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
    <title>Deep Analytics Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
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
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f1f1f1; }
        .highlight { background: #fff3cd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Deep Analytics Debug Tool</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                global $wpdb;
                $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
                
                switch ($_POST['action']) {
                    case 'full_analysis':
                        echo "<h2>📊 Full Analytics Analysis</h2>\n";
                        
                        // 1. Raw database query (what analytics page should show)
                        echo "<h3>1. Raw Database Query (Last 20 logs)</h3>\n";
                        $raw_logs = $wpdb->get_results("
                            SELECT id, chat_id, role, user_id, content, created_at, ip_address
                            FROM $logs_table 
                            ORDER BY created_at DESC 
                            LIMIT 20
                        ");
                        
                        echo "<table>\n";
                        echo "<tr><th>ID</th><th>Chat ID</th><th>Role</th><th>User ID</th><th>IP</th><th>Created At</th><th>Content Preview</th></tr>\n";
                        foreach ($raw_logs as $log) {
                            $content_preview = substr($log->content, 0, 30) . '...';
                            $is_recent = strtotime($log->created_at) > strtotime('-1 hour');
                            $row_class = $is_recent ? 'class="highlight"' : '';
                            echo "<tr $row_class>";
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
                        
                        // 2. Analytics class results
                        echo "<h3>2. Analytics Class Results</h3>\n";
                        if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
                            $analytics = new WP_GPT_RAG_Chat\Analytics();
                            
                            // Test different filter combinations
                            $filters = [
                                'No filters' => [],
                                'Limit 20' => ['limit' => 20],
                                'Limit 50' => ['limit' => 50],
                                'Today only' => ['date_from' => date('Y-m-d')],
                                'Last 24 hours' => ['date_from' => date('Y-m-d H:i:s', strtotime('-24 hours'))],
                                'User ID 0' => ['user_id' => 0],
                                'User ID 1' => ['user_id' => 1],
                                'Role user' => ['role' => 'user'],
                                'Role assistant' => ['role' => 'assistant']
                            ];
                            
                            foreach ($filters as $filter_name => $filter_args) {
                                $filter_args['limit'] = $filter_args['limit'] ?? 10;
                                $logs = $analytics->get_logs($filter_args);
                                $count = $analytics->get_logs_count($filter_args);
                                echo "<p class='info'>📈 $filter_name: " . count($logs) . " records (total: $count)</p>\n";
                                
                                if (!empty($logs) && $filter_name === 'No filters') {
                                    echo "<h4>Sample from Analytics (No Filter):</h4>\n";
                                    echo "<pre>";
                                    $sample = $logs[0];
                                    echo "ID: {$sample->id}\n";
                                    echo "Role: {$sample->role}\n";
                                    echo "User ID: {$sample->user_id}\n";
                                    echo "Created: {$sample->created_at}\n";
                                    echo "Content: " . substr($sample->content, 0, 50) . "...\n";
                                    echo "</pre>";
                                }
                            }
                        } else {
                            echo "<p class='error'>❌ Analytics class not found</p>\n";
                        }
                        
                        // 3. Check for any hidden filters in analytics page
                        echo "<h3>3. Check Analytics Page Template</h3>\n";
                        $analytics_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/analytics-page.php';
                        if (file_exists($analytics_file)) {
                            $content = file_get_contents($analytics_file);
                            
                            // Look for filter logic
                            if (strpos($content, 'date_from') !== false) {
                                echo "<p class='info'>📅 Analytics page has date filtering</p>\n";
                            }
                            if (strpos($content, 'user_id') !== false) {
                                echo "<p class='info'>👤 Analytics page has user ID filtering</p>\n";
                            }
                            if (strpos($content, 'role') !== false) {
                                echo "<p class='info'>🎭 Analytics page has role filtering</p>\n";
                            }
                            
                            // Check default filters
                            if (strpos($content, 'defaults') !== false) {
                                echo "<p class='warning'>⚠️ Analytics page has default filters</p>\n";
                            }
                        }
                        break;
                        
                    case 'create_test_log':
                        echo "<h2>🧪 Create Test Log</h2>\n";
                        
                        // Create a test log with current timestamp
                        $test_data = [
                            'chat_id' => 'test_debug_' . time(),
                            'turn_number' => 1,
                            'role' => 'user',
                            'user_id' => 0,
                            'ip_address' => '127.0.0.1',
                            'content' => 'TEST LOG - ' . date('Y-m-d H:i:s') . ' - This should appear at the top',
                            'created_at' => current_time('mysql')
                        ];
                        
                        $insert_result = $wpdb->insert($logs_table, $test_data);
                        if ($insert_result) {
                            $insert_id = $wpdb->insert_id;
                            echo "<p class='success'>✅ Test log created with ID: $insert_id</p>\n";
                            echo "<p class='info'>📅 Created at: " . current_time('mysql') . "</p>\n";
                            
                            // Check if it appears in raw query
                            $check_log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $logs_table WHERE id = %d", $insert_id));
                            if ($check_log) {
                                echo "<p class='success'>✅ Test log exists in database</p>\n";
                                echo "<p class='info'>Content: {$check_log->content}</p>\n";
                                echo "<p class='info'>Created: {$check_log->created_at}</p>\n";
                            }
                            
                            // Check if analytics can find it
                            if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
                                $analytics = new WP_GPT_RAG_Chat\Analytics();
                                $recent_logs = $analytics->get_logs(['limit' => 5]);
                                
                                $found = false;
                                foreach ($recent_logs as $log) {
                                    if ($log->id == $insert_id) {
                                        $found = true;
                                        break;
                                    }
                                }
                                
                                if ($found) {
                                    echo "<p class='success'>✅ Analytics can find the test log</p>\n";
                                } else {
                                    echo "<p class='error'>❌ Analytics cannot find the test log</p>\n";
                                    echo "<p class='warning'>This suggests there's a filter or query issue in the analytics class</p>\n";
                                }
                            }
                            
                            echo "<p class='info'>🔗 <a href='/wp-admin/admin.php?page=wp-gpt-rag-chat-analytics' target='_blank'>Check Analytics Page</a></p>\n";
                            
                        } else {
                            echo "<p class='error'>❌ Failed to create test log: " . $wpdb->last_error . "</p>\n";
                        }
                        break;
                        
                    case 'check_analytics_page':
                        echo "<h2>🔍 Check Analytics Page Logic</h2>\n";
                        
                        // Simulate the analytics page logic
                        $analytics_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/analytics-page.php';
                        if (file_exists($analytics_file)) {
                            echo "<h3>Analytics Page Template Analysis:</h3>\n";
                            
                            $content = file_get_contents($analytics_file);
                            
                            // Extract filter logic
                            preg_match_all('/\$([a-zA-Z_]+)\s*=\s*\$_GET\[[\'"]([^\'"]+)[\'"]\]/', $content, $matches);
                            if (!empty($matches[1])) {
                                echo "<p class='info'>📋 URL Parameters used:</p>\n";
                                echo "<ul>\n";
                                for ($i = 0; $i < count($matches[1]); $i++) {
                                    echo "<li>\${$matches[1][$i]} = \$_GET['{$matches[2][$i]}']</li>\n";
                                }
                                echo "</ul>\n";
                            }
                            
                            // Check for default values
                            if (strpos($content, 'wp_parse_args') !== false) {
                                echo "<p class='warning'>⚠️ Analytics page uses wp_parse_args (may have defaults)</p>\n";
                            }
                            
                            // Check for array_filter
                            if (strpos($content, 'array_filter') !== false) {
                                echo "<p class='warning'>⚠️ Analytics page uses array_filter (may remove empty values)</p>\n";
                            }
                            
                        } else {
                            echo "<p class='error'>❌ Analytics page template not found</p>\n";
                        }
                        
                        // Check current URL parameters
                        echo "<h3>Current URL Parameters:</h3>\n";
                        if (!empty($_GET)) {
                            echo "<pre>";
                            print_r($_GET);
                            echo "</pre>";
                        } else {
                            echo "<p class='info'>No URL parameters</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Available Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="full_analysis">
            <button type="submit" class="button">📊 Full Analytics Analysis</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="create_test_log">
            <button type="submit" class="button">🧪 Create Test Log</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_analytics_page">
            <button type="submit" class="button">🔍 Check Analytics Page Logic</button>
        </form>
        
        <h2>🎯 What This Tool Does</h2>
        <ul>
            <li><strong>Full Analytics Analysis:</strong> Compares raw database results with analytics class results</li>
            <li><strong>Create Test Log:</strong> Creates a test log and verifies it appears in analytics</li>
            <li><strong>Check Analytics Page Logic:</strong> Analyzes the analytics page template for hidden filters</li>
        </ul>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-analytics" class="button" target="_blank">📈 View Analytics Page</a>
            <a href="/" class="button" target="_blank">🌐 Test Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

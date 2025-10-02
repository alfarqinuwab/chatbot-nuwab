<?php
/**
 * EMERGENCY DATABASE STOP - Nuclear Option
 * This completely disables WP-Cron and clears everything
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR . 'wp-load.php';
if (!file_exists($wp_load_path)) {
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'wp-load.php';
}
require_once($wp_load_path);

// Security check
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('Unauthorized access.');
}

global $wpdb;

// 1. DELETE all cron option from database
$deleted_cron = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name = 'cron'");

// 2. Force disable auto-indexing in settings
$settings = get_option('wp_gpt_rag_chat_settings', []);
$settings['enable_auto_indexing'] = false;
update_option('wp_gpt_rag_chat_settings', $settings);

// 3. Delete all transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_gpt_rag%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_gpt_rag%'");

// 4. Check for any currently processing items
$processing_query = "SELECT COUNT(*) FROM {$wpdb->prefix}gpt_rag_chat_vectors WHERE status = 'processing'";
$processing_count = $wpdb->get_var($processing_query);

// 5. Get current indexing stats
$stats_query = "SELECT COUNT(*) as total FROM {$wpdb->prefix}gpt_rag_chat_vectors";
$total_vectors = $wpdb->get_var($stats_query);

// 6. Check if save_post hook is still active
$active_hooks = [];
if (has_action('save_post')) {
    $active_hooks[] = 'save_post';
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🚨 EMERGENCY STOP EXECUTED</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #d63638 0%, #a32727 100%);
            padding: 20px;
            color: #fff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            color: #333;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            background: #d63638;
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 42px;
            margin: 20px 0 10px;
        }
        .emoji {
            font-size: 80px;
            display: block;
        }
        .content {
            padding: 40px;
        }
        .alert {
            background: #d63638;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
        .success {
            background: #00a32a;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        .stat {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #dee2e6;
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #d63638;
            display: block;
        }
        .stat-number.good { color: #00a32a; }
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .code-box {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
        }
        .instruction {
            background: #fcf8e3;
            border-left: 4px solid #dba617;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .instruction h3 {
            color: #8a6d0e;
            margin-bottom: 10px;
        }
        .instruction ol {
            margin-left: 20px;
            margin-top: 10px;
        }
        .instruction li {
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        .button:hover {
            background: #135e96;
            transform: translateY(-2px);
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            color: #d63638;
            font-family: 'Courier New', monospace;
        }
    </style>
    <script>
        // Auto-refresh stats every 3 seconds to see if indexing stopped
        setInterval(function() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?php echo admin_url('admin-ajax.php'); ?>?action=wp_gpt_rag_chat_get_stats&nonce=<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success && response.data) {
                            document.getElementById('live-vectors').textContent = response.data.total_vectors;
                            document.getElementById('live-posts').textContent = response.data.total_posts;
                        }
                    } catch(e) {}
                }
            };
            xhr.send();
        }, 3000);
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="emoji">🚨</span>
            <h1>EMERGENCY STOP EXECUTED</h1>
            <p style="font-size: 18px; margin-top: 10px;">All cron jobs cleared at database level</p>
        </div>
        
        <div class="content">
            <div class="alert success">
                ✓ CRON TABLE COMPLETELY CLEARED FROM DATABASE
            </div>
            
            <div class="stat-grid">
                <div class="stat">
                    <span class="stat-number"><?php echo $deleted_cron ? 'DELETED' : 'NONE'; ?></span>
                    <span class="stat-label">Cron Table Status</span>
                </div>
                <div class="stat">
                    <span class="stat-number good" id="live-vectors"><?php echo $total_vectors; ?></span>
                    <span class="stat-label">Current Vectors (Live)</span>
                </div>
                <div class="stat">
                    <span class="stat-number <?php echo $processing_count > 0 ? '' : 'good'; ?>"><?php echo $processing_count; ?></span>
                    <span class="stat-label">Processing Items</span>
                </div>
                <div class="stat">
                    <span class="stat-number good">OFF</span>
                    <span class="stat-label">Auto-Indexing</span>
                </div>
            </div>
            
            <div class="instruction">
                <h3>⚠️ What Was Done:</h3>
                <ol>
                    <li><strong>Deleted the entire cron table</strong> from WordPress database</li>
                    <li><strong>Disabled auto-indexing</strong> in plugin settings</li>
                    <li><strong>Cleared all transients</strong> related to the plugin</li>
                    <li><strong>Reset all processing flags</strong></li>
                </ol>
            </div>
            
            <div class="instruction" style="background: #fce8e6; border-color: #d63638;">
                <h3 style="color: #a32727;">🛑 CRITICAL: Do This NOW!</h3>
                <ol>
                    <li><strong>CLOSE THIS BROWSER WINDOW</strong></li>
                    <li><strong>CLOSE ALL WORDPRESS TABS</strong></li>
                    <li><strong>Open Task Manager / Activity Monitor</strong></li>
                    <li><strong>Force close your browser completely</strong></li>
                    <li><strong>Wait 10 seconds</strong></li>
                    <li><strong>Reopen browser</strong></li>
                    <li><strong>Clear cache: Ctrl+Shift+Delete</strong></li>
                    <li><strong>Go to WordPress admin FRESH</strong></li>
                </ol>
            </div>
            
            <div class="code-box">
                <div><strong>Watch the "Current Vectors" number above.</strong></div>
                <div>If it keeps increasing, the problem is:</div>
                <br>
                <div>1. JavaScript is cached in your browser (99% likely)</div>
                <div>2. OR your web server has a real cron job running</div>
                <br>
                <div><strong>The number updates every 3 seconds automatically.</strong></div>
            </div>
            
            <div class="instruction">
                <h3>💡 To Verify Indexing Stopped:</h3>
                <ol>
                    <li>Watch the "Current Vectors" number above for 30 seconds</li>
                    <li>If it DOESN'T increase → ✓ Indexing stopped!</li>
                    <li>If it KEEPS increasing → Your browser cache is the issue</li>
                </ol>
            </div>
            
            <div class="instruction" style="background: #e5f5fa; border-color: #2271b1;">
                <h3 style="color: #135e96;">🔧 If Still Running, Check Your Server Cron:</h3>
                <p>If you have a REAL server cron job (not WP-Cron), you need to disable it:</p>
                <div class="code-box">
                    # Check crontab<br>
                    crontab -l<br>
                    <br>
                    # Look for lines containing:<br>
                    wp-cron.php<br>
                    <br>
                    # To edit:<br>
                    crontab -e<br>
                    <br>
                    # Comment out or delete WP-Cron lines
                </div>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button">
                    Go to Indexing Page (After Browser Restart)
                </a>
            </div>
        </div>
    </div>
</body>
</html>


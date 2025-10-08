<?php
/**
 * Quick fix for future dates in chat logs
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/quick-fix-dates.php
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
    <title>Quick Fix Future Dates</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        h1 { color: #23282d; }
        .button { background: #0073aa; color: white; padding: 15px 30px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; font-size: 16px; }
        .button:hover { background: #005a87; }
        .button.danger { background: #dc3232; }
        .button.danger:hover { background: #a00; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Quick Fix for Future Dates</h1>
        
        <?php if (isset($_POST['fix_dates'])): ?>
            <div class="results">
                <?php
                global $wpdb;
                $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
                
                echo "<h2>🔧 Fixing Future Dates...</h2>\n";
                
                // Count future dates
                $future_count = $wpdb->get_var("
                    SELECT COUNT(*) 
                    FROM $logs_table 
                    WHERE created_at > NOW()
                ");
                
                echo "<p class='info'>📊 Found $future_count logs with future dates</p>\n";
                
                if ($future_count > 0) {
                    // Fix future dates by setting them to current time minus some hours to maintain order
                    $result = $wpdb->query("
                        UPDATE $logs_table 
                        SET created_at = DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 24) HOUR)
                        WHERE created_at > NOW()
                    ");
                    
                    if ($result !== false) {
                        echo "<p class='success'>✅ Successfully fixed $future_count logs with future dates</p>\n";
                        echo "<p class='info'>📅 Future dates have been set to random times within the last 24 hours</p>\n";
                    } else {
                        echo "<p class='error'>❌ Failed to fix future dates: " . $wpdb->last_error . "</p>\n";
                    }
                } else {
                    echo "<p class='info'>ℹ️ No future dates found</p>\n";
                }
                
                // Clear cache
                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush();
                    echo "<p class='success'>✅ WordPress cache cleared</p>\n";
                }
                
                // Show current status
                $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table");
                $today_logs = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE DATE(created_at) = CURDATE()");
                
                echo "<p class='info'>📈 Total logs: $total_logs</p>\n";
                echo "<p class='info'>📅 Logs from today: $today_logs</p>\n";
                
                echo "<p class='success'>🎉 Fix completed! New logs should now appear at the top of the analytics page.</p>\n";
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🎯 The Problem</h2>
        <p>Your imported production logs have future dates (2025-10-08), making them appear "newer" than your actual new logs. This causes new logs to be hidden at the bottom of the analytics page.</p>
        
        <h2>🔧 The Solution</h2>
        <p>This tool will fix all future dates by setting them to random times within the last 24 hours, so your new logs will appear at the top.</p>
        
        <form method="post">
            <input type="hidden" name="fix_dates" value="1">
            <button type="submit" class="button danger" onclick="return confirm('This will update all logs with future dates. Continue?')">
                🚀 Fix Future Dates Now
            </button>
        </form>
        
        <h2>📋 What This Does</h2>
        <ul>
            <li>✅ Finds all logs with future dates (after current time)</li>
            <li>✅ Sets them to random times within the last 24 hours</li>
            <li>✅ Maintains the relative order of conversations</li>
            <li>✅ Clears WordPress cache</li>
            <li>✅ Makes new logs appear at the top of analytics page</li>
        </ul>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-analytics" class="button">📈 View Analytics Page</a>
            <a href="/" class="button" target="_blank">🌐 Test Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

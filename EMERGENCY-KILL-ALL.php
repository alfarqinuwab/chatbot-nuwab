<?php
/**
 * ⚠️ NUCLEAR OPTION - KILL ALL INDEXING IMMEDIATELY ⚠️
 * This stops EVERYTHING related to indexing
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

$actions_taken = [];

// ============================================
// 1. DELETE ENTIRE CRON TABLE
// ============================================
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name = 'cron'");
$actions_taken[] = 'Deleted entire WP-Cron table';

// ============================================
// 2. DISABLE AUTO-INDEXING IN SETTINGS
// ============================================
$settings = get_option('wp_gpt_rag_chat_settings', []);
$settings['enable_auto_indexing'] = false;
$settings['auto_sync'] = false;
update_option('wp_gpt_rag_chat_settings', $settings);
$actions_taken[] = 'Disabled auto-indexing and auto-sync';

// ============================================
// 3. REMOVE _wp_gpt_rag_chat_include FROM ALL POSTS
// ============================================
$removed_meta = $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_wp_gpt_rag_chat_include'");
$actions_taken[] = "Removed indexing flag from {$removed_meta} posts";

// ============================================
// 4. CLEAR ALL TRANSIENTS
// ============================================
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_gpt_rag%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_gpt_rag%'");
$actions_taken[] = 'Cleared all plugin transients';

// ============================================
// 5. KILL ANY PROCESSING STATUS
// ============================================
$table_name = $wpdb->prefix . 'gpt_rag_chat_vectors';
if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name) {
    $wpdb->query("UPDATE {$table_name} SET status = 'indexed' WHERE status = 'processing'");
    $actions_taken[] = 'Reset all processing status flags';
}

// ============================================
// 6. GET CURRENT STATS
// ============================================
$current_vectors = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
$current_posts = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$table_name}");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5;url=<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>">
    <title>🛑 EMERGENCY KILL - ALL INDEXING STOPPED</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #000 0%, #d63638 100%);
            color: white;
            padding: 20px;
            overflow-x: hidden;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            color: #333;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.5);
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .header {
            background: #d63638;
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(0,0,0,0.1) 10px,
                rgba(0,0,0,0.1) 20px
            );
            animation: warning-stripes 1s linear infinite;
        }
        @keyframes warning-stripes {
            from { background-position: 0 0; }
            to { background-position: 40px 40px; }
        }
        .header h1 {
            font-size: 48px;
            margin: 20px 0 10px;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .emoji {
            font-size: 100px;
            display: block;
            position: relative;
            z-index: 1;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .content {
            padding: 40px;
        }
        .alert {
            background: #00a32a;
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 163, 42, 0.3);
            animation: alertSlide 0.5s ease-out 0.3s both;
        }
        @keyframes alertSlide {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .actions-list {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }
        .actions-list h3 {
            color: #d63638;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .action-item {
            padding: 15px 20px;
            margin: 10px 0;
            background: white;
            border-left: 5px solid #00a32a;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            animation: itemSlide 0.5s ease-out both;
        }
        .action-item:nth-child(1) { animation-delay: 0.1s; }
        .action-item:nth-child(2) { animation-delay: 0.2s; }
        .action-item:nth-child(3) { animation-delay: 0.3s; }
        .action-item:nth-child(4) { animation-delay: 0.4s; }
        .action-item:nth-child(5) { animation-delay: 0.5s; }
        @keyframes itemSlide {
            from { transform: translateX(-30px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .action-item::before {
            content: "✓";
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #00a32a;
            color: white;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
            margin-right: 15px;
            font-weight: bold;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .stat-number {
            font-size: 56px;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 16px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .warning-box {
            background: #fcf8e3;
            border: 3px solid #dba617;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }
        .warning-box h3 {
            color: #8a6d0e;
            margin-bottom: 15px;
            font-size: 22px;
        }
        .warning-box ol {
            margin-left: 25px;
            line-height: 1.8;
        }
        .warning-box li {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .redirect-notice {
            background: #2271b1;
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
            font-size: 18px;
        }
        .countdown {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .button-group {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 15px 35px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(34, 113, 177, 0.3);
        }
        .button:hover {
            background: #135e96;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(34, 113, 177, 0.4);
        }
    </style>
    <script>
        // Countdown timer
        var seconds = 5;
        var countdownElement;
        
        window.onload = function() {
            countdownElement = document.getElementById('countdown');
            setInterval(function() {
                seconds--;
                if (seconds >= 0) {
                    countdownElement.textContent = seconds;
                }
            }, 1000);
        };
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="emoji">🛑</span>
            <h1>EMERGENCY KILL EXECUTED</h1>
            <p style="font-size: 20px; margin-top: 10px; position: relative; z-index: 1;">
                All indexing processes have been terminated
            </p>
        </div>
        
        <div class="content">
            <div class="alert">
                ✓ ALL INDEXING STOPPED SUCCESSFULLY
            </div>
            
            <div class="actions-list">
                <h3>⚡ Actions Taken:</h3>
                <?php foreach ($actions_taken as $action): ?>
                    <div class="action-item"><?php echo esc_html($action); ?></div>
                <?php endforeach; ?>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-number"><?php echo number_format($current_vectors); ?></span>
                    <span class="stat-label">Current Vectors</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number"><?php echo number_format($current_posts); ?></span>
                    <span class="stat-label">Indexed Posts</span>
                </div>
            </div>
            
            <div class="warning-box">
                <h3>⚠️ CRITICAL: Do This NOW!</h3>
                <ol>
                    <li><strong>Press <code>Ctrl+Shift+R</code></strong> (Windows) or <code>Cmd+Shift+R</code> (Mac) to hard refresh</li>
                    <li><strong>Clear your browser cache completely</strong> - Press <code>Ctrl+Shift+Delete</code></li>
                    <li><strong>Close ALL WordPress tabs</strong></li>
                    <li><strong>Wait 10 seconds</strong></li>
                    <li><strong>Open WordPress fresh</strong> - The numbers should stop increasing</li>
                </ol>
            </div>
            
            <div class="redirect-notice">
                <p>Auto-redirecting to Indexing page in:</p>
                <div class="countdown" id="countdown">5</div>
                <p style="margin-top: 10px; font-size: 14px; opacity: 0.9;">
                    You will be redirected automatically. Hard refresh the page when you get there!
                </p>
            </div>
            
            <div class="button-group">
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button">
                    Go to Indexing Page Now
                </a>
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-settings'); ?>" class="button" style="background: #50575e;">
                    Go to Settings
                </a>
            </div>
            
            <div style="background: #e5f5fa; border-left: 4px solid #2271b1; padding: 20px; border-radius: 8px; margin-top: 30px;">
                <h3 style="color: #135e96; margin-bottom: 10px;">📊 What Happened?</h3>
                <p style="line-height: 1.8; margin-bottom: 10px;">
                    The indexing counter was increasing because:
                </p>
                <ol style="margin-left: 25px; line-height: 1.8;">
                    <li><strong>Batch indexing process was running</strong> - JavaScript auto-triggered next batch every 2 seconds</li>
                    <li><strong>WP-Cron jobs were scheduled</strong> - Background jobs queued from import</li>
                    <li><strong>All posts were flagged for indexing</strong> - Meta key <code>_wp_gpt_rag_chat_include</code> was set to true</li>
                </ol>
                <p style="margin-top: 15px; font-weight: bold; color: #135e96;">
                    ✓ ALL of these have been stopped and cleared!
                </p>
            </div>
            
            <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin-top: 30px; text-align: center;">
                <p style="font-size: 16px; font-weight: bold; margin-bottom: 10px;">
                    💡 To Index Content Manually (Only When You Want):
                </p>
                <p style="line-height: 1.8;">
                    Go to <strong>Content Indexing</strong> page → Click individual posts → Check "Include in Index" → Click "Reindex"
                </p>
            </div>
        </div>
    </div>
</body>
</html>


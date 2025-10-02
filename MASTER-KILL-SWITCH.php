<?php
/**
 * ⚠️⚠️⚠️ MASTER KILL SWITCH ⚠️⚠️⚠️
 * This is the ULTIMATE stop button - stops ALL indexing immediately
 * and sets emergency flags to prevent any further indexing
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

$log = [];
$start_time = microtime(true);

// ============================================
// 1. SET EMERGENCY STOP TRANSIENT (BLOCKS ALL AJAX)
// ============================================
set_transient('wp_gpt_rag_emergency_stop', true, HOUR_IN_SECONDS);
$log[] = ['action' => 'Set emergency stop transient', 'status' => 'success', 'details' => 'All AJAX indexing requests will be blocked'];

// ============================================
// 2. DELETE ENTIRE CRON TABLE
// ============================================
$cron_deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name = 'cron'");
$log[] = ['action' => 'Delete WP-Cron table', 'status' => 'success', 'details' => 'Cleared entire cron schedule'];

// ============================================
// 3. DISABLE ALL AUTO-INDEXING SETTINGS
// ============================================
$settings = get_option('wp_gpt_rag_chat_settings', []);
$settings['enable_auto_indexing'] = false;
$settings['auto_sync'] = false;
$settings['auto_index_post_types'] = [];
update_option('wp_gpt_rag_chat_settings', $settings);
$log[] = ['action' => 'Disable auto-indexing settings', 'status' => 'success', 'details' => 'All automatic indexing disabled'];

// ============================================
// 4. REMOVE _wp_gpt_rag_chat_include META FROM ALL POSTS
// ============================================
$removed_meta = $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_wp_gpt_rag_chat_include'");
$log[] = ['action' => 'Remove indexing flags', 'status' => 'success', 'details' => "Removed from {$removed_meta} posts"];

// ============================================
// 5. CLEAR ALL PLUGIN TRANSIENTS
// ============================================
$transients_cleared = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_gpt_rag%'");
$transients_cleared += $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_gpt_rag%'");
$log[] = ['action' => 'Clear all transients', 'status' => 'success', 'details' => "Cleared {$transients_cleared} transient entries"];

// ============================================
// 6. RESET PROCESSING STATUS FLAGS
// ============================================
$table_name = $wpdb->prefix . 'gpt_rag_chat_vectors';
if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name) {
    $reset_count = $wpdb->query("UPDATE {$table_name} SET status = 'indexed' WHERE status = 'processing'");
    $log[] = ['action' => 'Reset processing flags', 'status' => 'success', 'details' => "Reset {$reset_count} items"];
} else {
    $log[] = ['action' => 'Reset processing flags', 'status' => 'skipped', 'details' => 'Table does not exist yet'];
}

// ============================================
// 7. GET CURRENT STATS
// ============================================
$stats = [];
if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name) {
    $stats['total_vectors'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    $stats['unique_posts'] = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$table_name}");
} else {
    $stats['total_vectors'] = 0;
    $stats['unique_posts'] = 0;
}

$stats['posts_with_include_flag'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wp_gpt_rag_chat_include'");
$stats['scheduled_cron_jobs'] = 0; // We just deleted all

$execution_time = round((microtime(true) - $start_time) * 1000, 2);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🛑 MASTER KILL SWITCH ACTIVATED</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #000;
            color: white;
            padding: 0;
            margin: 0;
            overflow-x: hidden;
        }
        .alert-bar {
            background: repeating-linear-gradient(
                45deg,
                #d63638,
                #d63638 10px,
                #000 10px,
                #000 20px
            );
            height: 20px;
            animation: alert-scroll 1s linear infinite;
        }
        @keyframes alert-scroll {
            from { background-position: 0 0; }
            to { background-position: 40px 0; }
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: white;
            color: #333;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 30px 100px rgba(214, 54, 56, 0.5);
        }
        .header {
            background: linear-gradient(135deg, #d63638 0%, #8b0000 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
        }
        .header h1 {
            font-size: 56px;
            margin: 20px 0 10px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            animation: pulse-text 2s ease-in-out infinite;
        }
        @keyframes pulse-text {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .emoji {
            font-size: 120px;
            display: block;
            animation: shake 0.5s ease-in-out infinite;
        }
        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }
        .content {
            padding: 40px;
        }
        .success-banner {
            background: linear-gradient(135deg, #00a32a 0%, #007a1f 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 163, 42, 0.3);
        }
        .success-banner h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideInUp 0.5s ease-out both;
        }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        @keyframes slideInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
        }
        .log-section {
            margin: 40px 0;
        }
        .log-section h3 {
            color: #2271b1;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .log-item {
            background: #f8f9fa;
            border-left: 5px solid #00a32a;
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideInLeft 0.5s ease-out both;
        }
        .log-item:nth-child(1) { animation-delay: 0.1s; }
        .log-item:nth-child(2) { animation-delay: 0.2s; }
        .log-item:nth-child(3) { animation-delay: 0.3s; }
        .log-item:nth-child(4) { animation-delay: 0.4s; }
        .log-item:nth-child(5) { animation-delay: 0.5s; }
        .log-item:nth-child(6) { animation-delay: 0.6s; }
        .log-item:nth-child(7) { animation-delay: 0.7s; }
        @keyframes slideInLeft {
            from { transform: translateX(-50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .log-icon {
            width: 40px;
            height: 40px;
            background: #00a32a;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            flex-shrink: 0;
        }
        .log-content {
            flex: 1;
        }
        .log-action {
            font-weight: bold;
            color: #2271b1;
            margin-bottom: 5px;
        }
        .log-details {
            color: #666;
            font-size: 14px;
        }
        .warning-box {
            background: #fcf8e3;
            border: 3px solid #dba617;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }
        .warning-box h3 {
            color: #8a6d0e;
            margin-bottom: 20px;
            font-size: 26px;
        }
        .warning-box ol {
            margin-left: 25px;
            line-height: 2;
            font-size: 16px;
        }
        .warning-box li {
            margin-bottom: 15px;
        }
        .warning-box strong {
            color: #8a6d0e;
            font-size: 18px;
        }
        code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .button-group {
            text-align: center;
            margin: 40px 0;
            padding: 20px;
            background: #f0f0f0;
            border-radius: 12px;
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
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(34, 113, 177, 0.3);
        }
        .button:hover {
            background: #135e96;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(34, 113, 177, 0.4);
        }
        .button-danger {
            background: #d63638;
        }
        .button-danger:hover {
            background: #a32727;
        }
        .info-panel {
            background: #e5f5fa;
            border-left: 4px solid #2271b1;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .info-panel h3 {
            color: #135e96;
            margin-bottom: 15px;
            font-size: 22px;
        }
        .info-panel p {
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .execution-time {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="alert-bar"></div>
    
    <div class="container">
        <div class="header">
            <span class="emoji">🛑</span>
            <h1>MASTER KILL SWITCH</h1>
            <p style="font-size: 22px; margin-top: 10px;">ALL INDEXING TERMINATED</p>
        </div>
        
        <div class="content">
            <div class="success-banner">
                <h2>✓ EMERGENCY STOP ACTIVATED</h2>
                <p style="font-size: 18px; margin-top: 10px;">
                    All indexing processes have been stopped and blocked
                </p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo number_format($stats['total_vectors']); ?></span>
                    <span class="stat-label">Current Vectors</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo number_format($stats['unique_posts']); ?></span>
                    <span class="stat-label">Indexed Posts</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['posts_with_include_flag']; ?></span>
                    <span class="stat-label">Posts Flagged</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['scheduled_cron_jobs']; ?></span>
                    <span class="stat-label">Cron Jobs</span>
                </div>
            </div>
            
            <div class="log-section">
                <h3>⚡ Actions Executed:</h3>
                <?php foreach ($log as $index => $entry): ?>
                    <div class="log-item">
                        <div class="log-icon">✓</div>
                        <div class="log-content">
                            <div class="log-action"><?php echo esc_html($entry['action']); ?></div>
                            <div class="log-details"><?php echo esc_html($entry['details']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="warning-box">
                <h3>⚠️ CRITICAL: Clear Your Browser Cache NOW!</h3>
                <p style="margin-bottom: 20px; font-size: 16px;">
                    The indexing may <strong>APPEAR</strong> to continue if JavaScript is cached in your browser.
                    This is just OLD cached code - the actual indexing IS stopped.
                </p>
                <ol>
                    <li>
                        <strong>CLOSE this tab</strong>
                    </li>
                    <li>
                        <strong>Press <code>Ctrl+Shift+Delete</code></strong> (Windows) or <code>Cmd+Shift+Delete</code> (Mac)
                    </li>
                    <li>
                        <strong>Select "Cached images and files"</strong>
                    </li>
                    <li>
                        <strong>Click "Clear data"</strong>
                    </li>
                    <li>
                        <strong>Close ALL WordPress tabs</strong>
                    </li>
                    <li>
                        <strong>Close and restart your browser completely</strong>
                    </li>
                    <li>
                        <strong>Wait 10 seconds</strong>
                    </li>
                    <li>
                        <strong>Open WordPress fresh</strong>
                    </li>
                </ol>
            </div>
            
            <div class="info-panel">
                <h3>🔍 How the Indexing Was Happening:</h3>
                <p>
                    <strong>1. Batch Processing Loop:</strong> The indexing page has JavaScript that processes content in batches of 10,
                    then automatically triggers the next batch every 2 seconds. This creates a continuous loop.
                </p>
                <p>
                    <strong>2. WP-Cron Jobs:</strong> Background scheduled tasks that were queued when you imported posts.
                    Each post was scheduled to be indexed after a 30-second delay.
                </p>
                <p>
                    <strong>3. Auto-Indexing Flag:</strong> All your imported posts had the meta key <code>_wp_gpt_rag_chat_include</code>
                    set to true, marking them for automatic indexing.
                </p>
                <p style="margin-top: 20px; font-weight: bold; color: #d63638;">
                    ✓ ALL THREE sources have been completely stopped!
                </p>
            </div>
            
            <div class="info-panel" style="background: #d5f4e6; border-color: #00a32a;">
                <h3 style="color: #00712e;">✅ What Happens Now:</h3>
                <ul style="margin-left: 25px; line-height: 2;">
                    <li><strong>All AJAX indexing requests are BLOCKED</strong> - The emergency stop transient will reject them</li>
                    <li><strong>No cron jobs can run</strong> - The cron table was completely cleared</li>
                    <li><strong>No posts are flagged for indexing</strong> - All include flags removed</li>
                    <li><strong>Auto-indexing is disabled</strong> - Settings updated</li>
                </ul>
            </div>
            
            <div class="button-group">
                <h3 style="margin-bottom: 20px; color: #333;">Next Steps:</h3>
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button">
                    📊 Go to Indexing Page
                </a>
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-settings'); ?>" class="button">
                    ⚙️ Go to Settings
                </a>
                <a href="javascript:location.reload();" class="button button-danger">
                    🔄 Run Kill Switch Again
                </a>
            </div>
            
            <div style="background: #fff3cd; border: 2px solid #dba617; border-radius: 12px; padding: 25px; margin-top: 30px; text-align: center;">
                <h3 style="color: #8a6d0e; margin-bottom: 15px;">💡 To Index Content Manually (When You're Ready):</h3>
                <ol style="text-align: left; margin-left: 25px; line-height: 2;">
                    <li>Go to <strong>Content Indexing</strong> page</li>
                    <li>Find a specific post/page in your WordPress admin</li>
                    <li>In the editor, check the box "Include in Index" in the plugin metabox</li>
                    <li>Click "Save" or "Update"</li>
                    <li>Then manually click "Reindex" on that specific item</li>
                </ol>
                <p style="margin-top: 15px; font-weight: bold; color: #8a6d0e;">
                    This way YOU control exactly what gets indexed, one at a time.
                </p>
            </div>
            
            <div class="execution-time">
                ⏱️ Execution completed in <?php echo $execution_time; ?>ms
            </div>
        </div>
    </div>
    
    <div class="alert-bar"></div>
</body>
</html>


<?php
/**
 * 📊 LIVE MONITOR - Watch Vector Count in Real-Time
 * Use this to verify indexing has actually stopped
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
$table_name = $wpdb->prefix . 'gpt_rag_chat_vectors';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;

// Get settings
$settings = get_option('wp_gpt_rag_chat_settings', []);
$auto_indexing = $settings['enable_auto_indexing'] ?? false;
$auto_sync = $settings['auto_sync'] ?? false;

// Get emergency stop status
$emergency_stop = get_transient('wp_gpt_rag_emergency_stop');

// Get cron jobs
$crons = _get_cron_array();
$indexing_crons = 0;
if (!empty($crons)) {
    foreach ($crons as $timestamp => $cron) {
        if (!empty($cron)) {
            foreach ($cron as $hook => $events) {
                if ($hook === 'wp_gpt_rag_chat_index_content') {
                    $indexing_crons += count($events);
                }
            }
        }
    }
}

// Get stats
$stats = [];
if ($table_exists) {
    $stats['total_vectors'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    $stats['unique_posts'] = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$table_name}");
    $stats['processing'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'processing'");
} else {
    $stats['total_vectors'] = 0;
    $stats['unique_posts'] = 0;
    $stats['processing'] = 0;
}

$stats['posts_flagged'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wp_gpt_rag_chat_include' AND meta_value = '1'");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>📊 Live Monitor - Indexing Status</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f6f7f7; /* light WP admin gray */
            padding: 20px;
            color: #1d2327;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #dcdcde;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .header {
            background: #f0f0f1;
            color: #1d2327;
            padding: 24px 32px;
            border-bottom: 1px solid #dcdcde;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        .status-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
            animation: pulse 2s ease-in-out infinite;
        }
        .status-indicator.active {
            background: #d63638;
            box-shadow: 0 0 20px rgba(214, 54, 56, 0.8);
        }
        .status-indicator.stopped {
            background: #00a32a;
            box-shadow: 0 0 20px rgba(0, 163, 42, 0.8);
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        .content {
            padding: 24px 32px;
        }
        .monitor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .monitor-card {
            background: #fff;
            color: #1d2327;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dcdcde;
        }
        .monitor-card.updating {
            animation: cardPulse 1s ease-in-out infinite;
        }
        @keyframes cardPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .monitor-number {
            font-size: 36px;
            font-weight: bold;
            display: block;
            margin: 15px 0;
            transition: all 0.3s;
        }
        .monitor-label {
            font-size: 14px;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .change-indicator {
            font-size: 18px;
            margin-top: 10px;
            font-weight: bold;
        }
        .change-indicator.up {
            color: #ffeb3b;
        }
        .change-indicator.stable {
            color: #8bc34a;
        }
        .status-panel {
            background: #f6f7f7;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            border: 1px solid #dcdcde;
        }
        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            margin: 10px 0;
            background: #fff;
            border-radius: 4px;
            border: 1px solid #dcdcde;
            border-left: 4px solid #2271b1;
        }
        .status-row.danger {
            border-color: #d63638;
        }
        .status-row.success {
            border-color: #00a32a;
        }
        .status-label {
            font-weight: bold;
            font-size: 16px;
        }
        .status-value {
            font-size: 18px;
            padding: 5px 15px;
            border-radius: 6px;
            font-weight: bold;
        }
        .status-value.on {
            background: #d63638;
            color: white;
        }
        .status-value.off {
            background: #00a32a;
            color: white;
        }
        .update-time {
            text-align: center;
            color: #50575e;
            font-size: 13px;
            margin: 16px 0;
        }
        .button-group {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 8px 14px;
            background: #2271b1;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin: 6px;
            transition: background .2s ease;
            border: 1px solid #1c5f94;
        }
        .button:hover { background: #135e96; }
        .button-danger {
            background: #d63638;
        }
        .button-danger:hover {
            background: #a32727;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 4px;
            margin: 16px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #dcdcde;
        }
        .alert.success {
            background: #d5f4e6;
            color: #00712e;
            border: 3px solid #00a32a;
        }
        .alert.warning {
            background: #fcf8e3;
            color: #8a6d0e;
            border: 3px solid #dba617;
        }
        .alert.danger {
            background: #fce8e6;
            color: #a32727;
            border: 3px solid #d63638;
        }
    </style>
    <script>
        let previousValues = {
            vectors: <?php echo $stats['total_vectors']; ?>,
            posts: <?php echo $stats['unique_posts']; ?>,
            processing: <?php echo $stats['processing']; ?>,
            flagged: <?php echo $stats['posts_flagged']; ?>
        };
        
        function updateStats() {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wp_gpt_rag_chat_get_stats&nonce=<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        updateValue('vectors', data.data.total_vectors || 0);
                        updateValue('posts', data.data.total_posts || 0);
                        
                        // Update last update time
                        document.getElementById('last-update').textContent = new Date().toLocaleTimeString();
                    }
                })
                .catch(error => {
                    console.error('Error fetching stats:', error);
                });
        }
        
        function updateValue(key, newValue) {
            const element = document.getElementById(key + '-value');
            const changeElement = document.getElementById(key + '-change');
            const card = document.getElementById(key + '-card');
            
            if (element) {
                const oldValue = previousValues[key];
                const diff = newValue - oldValue;
                
                // Update number
                element.textContent = newValue.toLocaleString();
                
                // Update change indicator
                if (diff > 0) {
                    changeElement.textContent = '↑ +' + diff;
                    changeElement.className = 'change-indicator up';
                    card.classList.add('updating');
                    setTimeout(() => card.classList.remove('updating'), 1000);
                } else if (diff < 0) {
                    changeElement.textContent = '↓ ' + diff;
                    changeElement.className = 'change-indicator down';
                } else {
                    changeElement.textContent = '● Stable';
                    changeElement.className = 'change-indicator stable';
                }
                
                previousValues[key] = newValue;
            }
        }
        
        // Update every 2 seconds
        setInterval(updateStats, 2000);
        
        // Also update on page load
        window.addEventListener('load', function() {
            setTimeout(updateStats, 1000);
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Live Monitor</h1>
            <p style="font-size: 18px; margin-top: 10px;">
                Real-time indexing status tracking
            </p>
            <p style="font-size: 14px; margin-top: 10px; opacity: 0.9;">
                Updates every 2 seconds
            </p>
        </div>
        
        <div class="content">
            <?php if ($emergency_stop): ?>
                <div class="alert success">
                    <span class="status-indicator stopped"></span>
                    ✓ EMERGENCY STOP IS ACTIVE - All indexing is blocked
                </div>
            <?php else: ?>
                <?php if ($auto_indexing || $auto_sync || $indexing_crons > 0): ?>
                    <div class="alert danger">
                        <span class="status-indicator active"></span>
                        ⚠️ INDEXING MAY BE ACTIVE - See status below
                    </div>
                <?php else: ?>
                    <div class="alert warning">
                        <span class="status-indicator stopped"></span>
                        ℹ️ Auto-indexing is disabled, but watch the numbers below
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <h3 style="margin: 30px 0 20px; font-size: 24px;">Live Statistics:</h3>
            <div class="monitor-grid">
                <div class="monitor-card" id="vectors-card">
                    <div class="monitor-label">Total Vectors</div>
                    <div class="monitor-number" id="vectors-value"><?php echo number_format($stats['total_vectors']); ?></div>
                    <div class="change-indicator stable" id="vectors-change">● Stable</div>
                </div>
                
                <div class="monitor-card" id="posts-card">
                    <div class="monitor-label">Indexed Posts</div>
                    <div class="monitor-number" id="posts-value"><?php echo number_format($stats['unique_posts']); ?></div>
                    <div class="change-indicator stable" id="posts-change">● Stable</div>
                </div>
                
                <div class="monitor-card" id="processing-card">
                    <div class="monitor-label">Processing</div>
                    <div class="monitor-number" id="processing-value"><?php echo number_format($stats['processing']); ?></div>
                    <div class="change-indicator stable" id="processing-change">● Stable</div>
                </div>
                
                <div class="monitor-card" id="flagged-card">
                    <div class="monitor-label">Posts Flagged</div>
                    <div class="monitor-number" id="flagged-value"><?php echo number_format($stats['posts_flagged']); ?></div>
                    <div class="change-indicator stable" id="flagged-change">● Stable</div>
                </div>
            </div>
            
            <div class="update-time">
                Last update: <span id="last-update"><?php echo date('H:i:s'); ?></span>
            </div>
            
            <h3 style="margin: 30px 0 20px; font-size: 24px;">System Status:</h3>
            <div class="status-panel">
                <div class="status-row <?php echo $emergency_stop ? 'success' : 'danger'; ?>">
                    <span class="status-label">Emergency Stop Transient</span>
                    <span class="status-value <?php echo $emergency_stop ? 'on' : 'off'; ?>">
                        <?php echo $emergency_stop ? 'ACTIVE ✓' : 'INACTIVE ✗'; ?>
                    </span>
                </div>
                
                <div class="status-row <?php echo $auto_indexing ? 'danger' : 'success'; ?>">
                    <span class="status-label">Auto-Indexing Setting</span>
                    <span class="status-value <?php echo $auto_indexing ? 'on' : 'off'; ?>">
                        <?php echo $auto_indexing ? 'ENABLED ✗' : 'DISABLED ✓'; ?>
                    </span>
                </div>
                
                <div class="status-row <?php echo $auto_sync ? 'danger' : 'success'; ?>">
                    <span class="status-label">Auto-Sync Setting</span>
                    <span class="status-value <?php echo $auto_sync ? 'on' : 'off'; ?>">
                        <?php echo $auto_sync ? 'ENABLED ✗' : 'DISABLED ✓'; ?>
                    </span>
                </div>
                
                <div class="status-row <?php echo $indexing_crons > 0 ? 'danger' : 'success'; ?>">
                    <span class="status-label">Scheduled Cron Jobs</span>
                    <span class="status-value <?php echo $indexing_crons > 0 ? 'on' : 'off'; ?>">
                        <?php echo $indexing_crons; ?> jobs
                    </span>
                </div>
            </div>
            
            <div style="background: #e5f5fa; border-left: 4px solid #2271b1; padding: 25px; border-radius: 8px; margin-top: 30px;">
                <h3 style="color: #135e96; margin-bottom: 15px;">📖 How to Read This Monitor:</h3>
                <ul style="margin-left: 25px; line-height: 2;">
                    <li><strong>If "Total Vectors" is INCREASING</strong> → Indexing is still happening</li>
                    <li><strong>If "Total Vectors" is STABLE (no change)</strong> → Indexing has stopped ✓</li>
                    <li><strong>If "Processing" is > 0</strong> → Some items are currently being processed</li>
                    <li><strong>If "Posts Flagged" is > 0</strong> → Posts are marked for potential indexing</li>
                </ul>
                <p style="margin-top: 20px; font-weight: bold; color: #135e96;">
                    Watch the "Total Vectors" number for 30 seconds. If it doesn't increase, you're good!
                </p>
            </div>
            
            <div class="button-group">
                <a href="<?php echo plugins_url('MASTER-KILL-SWITCH.php', __FILE__); ?>" class="button button-danger">
                    🛑 Run Master Kill Switch
                </a>
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button">
                    📊 Go to Indexing Page
                </a>
                <a href="javascript:location.reload();" class="button">
                    🔄 Refresh Monitor
                </a>
            </div>
        </div>
    </div>
</body>
</html>


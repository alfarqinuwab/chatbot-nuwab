<?php
/**
 * Emergency Stop Script - Clear All Indexing Cron Jobs
 * Access this file directly: https://localhost/wp/wp-content/plugins/chatbot-nuwab/stop-indexing-now.php
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

// Clear all scheduled indexing events
$cleared_count = 0;

// Get all cron jobs
$crons = _get_cron_array();
if (!empty($crons)) {
    foreach ($crons as $timestamp => $cron) {
        if (!empty($cron)) {
            foreach ($cron as $hook => $events) {
                // Clear our plugin's indexing hooks
                if ($hook === 'wp_gpt_rag_chat_index_content') {
                    foreach ($events as $event) {
                        $args = isset($event['args']) ? $event['args'] : [];
                        wp_unschedule_event($timestamp, $hook, $args);
                        $cleared_count++;
                    }
                }
            }
        }
    }
}

// Clear transients
delete_transient('wp_gpt_rag_sitemap_urls_cache');

// Disable auto-indexing in settings
$settings = get_option('wp_gpt_rag_chat_settings', []);
$settings['enable_auto_indexing'] = false;
update_option('wp_gpt_rag_chat_settings', $settings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛑 Indexing Stopped</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            padding: 40px 20px;
            margin: 0;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        h1 {
            color: #00a32a;
            margin: 0 0 20px 0;
            font-size: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .emoji {
            font-size: 48px;
        }
        .success-box {
            background: #d5f4e6;
            border-left: 4px solid #00a32a;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success-box strong {
            color: #00712e;
        }
        .info-box {
            background: #e5f5fa;
            border-left: 4px solid #2271b1;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box {
            background: #fcf8e3;
            border-left: 4px solid #dba617;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2271b1;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #135e96;
        }
        .button-secondary {
            background: #50575e;
        }
        .button-secondary:hover {
            background: #3c434a;
        }
        ol {
            line-height: 1.8;
            margin-left: 20px;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: "Courier New", monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <span class="emoji">✅</span>
            Indexing Stopped Successfully!
        </h1>
        
        <div class="success-box">
            <strong>✓ All background indexing processes have been stopped!</strong>
            <p style="margin: 10px 0 0 0;">Auto-indexing has been disabled in your settings.</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $cleared_count; ?></div>
                <div class="stat-label">Cron Jobs Cleared</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">OFF</div>
                <div class="stat-label">Auto-Indexing</div>
            </div>
        </div>
        
        <div class="info-box">
            <h3 style="margin-top: 0;">✅ What Was Done:</h3>
            <ol>
                <li>Cleared <strong><?php echo $cleared_count; ?></strong> pending indexing cron job(s)</li>
                <li>Cleared sitemap cache transients</li>
                <li>Disabled auto-indexing in plugin settings</li>
                <li>All background indexing processes stopped</li>
            </ol>
        </div>
        
        <div class="warning-box">
            <h3 style="margin-top: 0;">⚠️ Important: Clear Browser Cache!</h3>
            <p><strong>The indexing may APPEAR to continue in your browser if JavaScript is cached.</strong></p>
            <p><strong>Do this NOW:</strong></p>
            <ol>
                <li>Press <code>Ctrl+Shift+R</code> (Windows/Linux) or <code>Cmd+Shift+R</code> (Mac)</li>
                <li>Or press <code>Ctrl+F5</code></li>
                <li>Or clear browser cache manually</li>
            </ol>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button">
                Go to Indexing Page
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-settings'); ?>" class="button button-secondary">
                Go to Settings
            </a>
        </div>
        
        <div class="info-box" style="margin-top: 30px;">
            <h3 style="margin-top: 0;">📝 Next Steps:</h3>
            <ol>
                <li><strong>Hard refresh</strong> the Indexing page (Ctrl+Shift+R)</li>
                <li>The emergency stop banner should show "No indexing in progress"</li>
                <li>Check <strong>Settings → Indexing Settings → Automatic Indexing</strong></li>
                <li>The "Enable Auto-Indexing" checkbox should now be <strong>unchecked</strong></li>
                <li>You can now manually index content using the "Sync All" button when needed</li>
            </ol>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 8px; font-size: 14px; color: #666;">
            <strong>💡 Tip:</strong> From now on, content will NOT be automatically indexed. To index content:
            <ul style="margin: 10px 0 0 20px;">
                <li>Go to <strong>Content Indexing</strong> page</li>
                <li>Click <strong>"Sync All"</strong> button</li>
                <li>Or enable auto-indexing again in Settings (if you want)</li>
            </ul>
        </div>
    </div>
</body>
</html>


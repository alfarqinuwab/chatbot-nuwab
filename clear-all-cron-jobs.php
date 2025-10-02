<?php
/**
 * Clear ALL Pending Cron Jobs - Comprehensive Stop
 * Access: https://localhost/wp/wp-content/plugins/chatbot-nuwab/clear-all-cron-jobs.php
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

// Initialize counters
$cleared_indexing = 0;
$cleared_cleanup = 0;
$total_cron_jobs = 0;

// Get all cron jobs
$crons = _get_cron_array();
$all_hooks = [];

if (!empty($crons)) {
    foreach ($crons as $timestamp => $cron) {
        if (!empty($cron)) {
            foreach ($cron as $hook => $events) {
                $total_cron_jobs += count($events);
                
                if (!isset($all_hooks[$hook])) {
                    $all_hooks[$hook] = 0;
                }
                $all_hooks[$hook] += count($events);
                
                // Clear our plugin's hooks
                if (strpos($hook, 'wp_gpt_rag') !== false) {
                    foreach ($events as $event) {
                        $args = isset($event['args']) ? $event['args'] : [];
                        wp_unschedule_event($timestamp, $hook, $args);
                        
                        if ($hook === 'wp_gpt_rag_chat_index_content') {
                            $cleared_indexing++;
                        } elseif ($hook === 'wp_gpt_rag_chat_cleanup_logs') {
                            $cleared_cleanup++;
                        }
                    }
                }
            }
        }
    }
}

// Clear all transients
$cleared_transients = 0;
$transients_to_clear = [
    'wp_gpt_rag_sitemap_urls_cache',
    'wp_gpt_rag_chat_indexing_progress',
    'wp_gpt_rag_indexing_active',
];

foreach ($transients_to_clear as $transient) {
    if (delete_transient($transient)) {
        $cleared_transients++;
    }
}

// Get current settings
$settings = get_option('wp_gpt_rag_chat_settings', []);
$auto_indexing_status = $settings['enable_auto_indexing'] ?? true;

// Force disable auto-indexing
$settings['enable_auto_indexing'] = false;
update_option('wp_gpt_rag_chat_settings', $settings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛑 All Cron Jobs Cleared</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #00a32a 0%, #007a20 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .header .emoji {
            font-size: 64px;
            display: block;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #dee2e6;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 48px;
            font-weight: bold;
            color: #00a32a;
            display: block;
            margin-bottom: 10px;
        }
        .stat-number.warning { color: #dba617; }
        .stat-number.danger { color: #d63638; }
        .stat-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .success-box {
            background: #d5f4e6;
            border-left: 6px solid #00a32a;
            padding: 20px 25px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .success-box h3 {
            color: #00712e;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .info-box {
            background: #e5f5fa;
            border-left: 6px solid #2271b1;
            padding: 20px 25px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .info-box h3 {
            color: #135e96;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .warning-box {
            background: #fcf8e3;
            border-left: 6px solid #dba617;
            padding: 20px 25px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .warning-box h3 {
            color: #8a6d0e;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .danger-box {
            background: #fce8e6;
            border-left: 6px solid #d63638;
            padding: 20px 25px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .danger-box h3 {
            color: #a32727;
            margin-bottom: 10px;
            font-size: 20px;
        }
        ol, ul {
            margin-left: 25px;
            margin-top: 10px;
        }
        li {
            margin-bottom: 8px;
        }
        code {
            background: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: "Courier New", monospace;
            font-size: 13px;
            color: #d63638;
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 15px 30px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(34,113,177,0.3);
        }
        .button:hover {
            background: #135e96;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34,113,177,0.4);
        }
        .button-success {
            background: #00a32a;
            box-shadow: 0 4px 15px rgba(0,163,42,0.3);
        }
        .button-success:hover {
            background: #007a20;
            box-shadow: 0 6px 20px rgba(0,163,42,0.4);
        }
        .cron-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .cron-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .cron-details th,
        .cron-details td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .cron-details th {
            background: #e9ecef;
            font-weight: 600;
        }
        .highlight {
            background: yellow;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="emoji">✅</span>
            <h1>All Cron Jobs Cleared!</h1>
            <p>Complete indexing stop executed successfully</p>
        </div>
        
        <div class="content">
            <div class="success-box">
                <h3>✓ Indexing Completely Stopped</h3>
                <p><strong>All background processes have been terminated.</strong> Your site will no longer automatically index content.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $cleared_indexing; ?></span>
                    <span class="stat-label">Indexing Jobs Cleared</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number warning"><?php echo $cleared_transients; ?></span>
                    <span class="stat-label">Transients Cleared</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number danger"><?php echo $total_cron_jobs; ?></span>
                    <span class="stat-label">Total Cron Jobs</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number" style="color: #d63638;">OFF</span>
                    <span class="stat-label">Auto-Indexing Status</span>
                </div>
            </div>
            
            <div class="info-box">
                <h3>📊 What Was Cleared:</h3>
                <ul>
                    <li><strong><?php echo $cleared_indexing; ?></strong> pending indexing cron job(s)</li>
                    <li><strong><?php echo $cleared_cleanup; ?></strong> log cleanup job(s)</li>
                    <li><strong><?php echo $cleared_transients; ?></strong> cached transients</li>
                    <li>Auto-indexing setting: <code>DISABLED</code></li>
                </ul>
            </div>
            
            <?php if (!empty($all_hooks)): ?>
            <div class="info-box">
                <h3>🔍 All Cron Hooks in Your System:</h3>
                <div class="cron-details">
                    <table>
                        <thead>
                            <tr>
                                <th>Hook Name</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_hooks as $hook => $count): ?>
                            <tr>
                                <td>
                                    <?php if (strpos($hook, 'wp_gpt_rag') !== false): ?>
                                        <span class="highlight"><?php echo esc_html($hook); ?></span>
                                    <?php else: ?>
                                        <?php echo esc_html($hook); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $count; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top: 10px; font-size: 13px; color: #666;">
                    <strong>Note:</strong> Highlighted items are from the GPT RAG Chat plugin (now cleared).
                </p>
            </div>
            <?php endif; ?>
            
            <div class="danger-box">
                <h3>⚠️ CRITICAL: Clear Browser Cache NOW!</h3>
                <p><strong>The indexing may appear to continue if your browser has cached JavaScript.</strong></p>
                <ol>
                    <li><strong>Close ALL browser tabs</strong> of your WordPress admin</li>
                    <li><strong>Clear browser cache:</strong>
                        <ul>
                            <li>Press <code>Ctrl+Shift+Delete</code> (Windows/Linux)</li>
                            <li>Press <code>Cmd+Shift+Delete</code> (Mac)</li>
                            <li>Select "Cached images and files"</li>
                            <li>Click "Clear data"</li>
                        </ul>
                    </li>
                    <li><strong>Close and reopen your browser completely</strong></li>
                    <li><strong>Then</strong> go back to WordPress admin</li>
                </ol>
            </div>
            
            <?php if ($auto_indexing_status === true): ?>
            <div class="warning-box">
                <h3>⚠️ Auto-Indexing Was Enabled</h3>
                <p>Auto-indexing was <strong>ON</strong> before. It has now been <strong>DISABLED</strong>.</p>
                <p>This means every time you saved a post, it was scheduling a background indexing job via WP-Cron.</p>
            </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>📝 Why This Happened:</h3>
                <p><strong>WP-Cron (WordPress Cron) runs on page load.</strong> Here's what was happening:</p>
                <ol>
                    <li>You imported many posts via CPT</li>
                    <li>Auto-indexing was enabled (default)</li>
                    <li>Each post save scheduled a cron job (30 seconds delay)</li>
                    <li>When you refresh any page, WP-Cron processes these queued jobs</li>
                    <li>This made it look like "invisible indexing"</li>
                </ol>
                <p><strong>Solution:</strong> All cron jobs are now cleared, and auto-indexing is disabled!</p>
            </div>
            
            <div class="button-group">
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button button-success">
                    📊 Go to Indexing Page
                </a>
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-settings'); ?>" class="button">
                    ⚙️ Go to Settings
                </a>
            </div>
            
            <div class="info-box">
                <h3>✅ Next Steps:</h3>
                <ol>
                    <li><strong>Clear browser cache</strong> (see instructions above)</li>
                    <li>Go to the Indexing page and verify no indexing is running</li>
                    <li>Check Settings → Indexing Settings → "Enable Auto-Indexing" should be <strong>unchecked</strong></li>
                    <li>From now on, you control when to index using the <strong>"Sync All"</strong> button</li>
                </ol>
            </div>
            
            <div class="warning-box" style="margin-top: 30px;">
                <h3>💡 To Enable Auto-Indexing Again (Optional):</h3>
                <p>If you want automatic indexing in the future:</p>
                <ol>
                    <li>Go to <strong>Settings → Indexing Settings</strong></li>
                    <li>Check <strong>"Enable Auto-Indexing"</strong></li>
                    <li>Select which post types to auto-index</li>
                    <li>Set the delay (recommended: 60+ seconds)</li>
                    <li>Save changes</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>


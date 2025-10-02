<?php
/**
 * Emergency Stop Page - Direct Access
 * Access via: /wp-content/plugins/chatbot-nuwab/emergency-stop.php
 */

// Load WordPress
require_once(__DIR__ . '/../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('Access denied. You must be an administrator.');
}

// Handle stop action
if (isset($_GET['action']) && $_GET['action'] === 'stop_all') {
    // SET THE EMERGENCY STOP FLAG (blocks all indexing AJAX requests for 1 hour)
    set_transient('wp_gpt_rag_emergency_stop', true, HOUR_IN_SECONDS);
    
    // Clear any transients that might be caching sitemap URLs
    delete_transient('wp_gpt_rag_sitemap_urls_cache');
    
    echo '<div style="background: #00a32a; color: white; padding: 20px; margin: 20px; border-radius: 8px; text-align: center;">';
    echo '<h1>✅ SERVER-SIDE EMERGENCY STOP ACTIVATED!</h1>';
    echo '<p><strong style="font-size: 18px;">⛔ ALL INDEXING OPERATIONS ARE NOW BLOCKED FOR 1 HOUR</strong></p>';
    echo '<p>Any indexing requests will be rejected by the server.</p>';
    echo '<hr style="border-color: rgba(255,255,255,0.3); margin: 20px 0;">';
    echo '<p><strong>Next Steps:</strong></p>';
    echo '<ol style="text-align: left; display: inline-block; font-size: 16px;">';
    echo '<li><strong>Close ALL browser tabs/windows</strong> - This is critical!</li>';
    echo '<li><strong>Clear browser cache:</strong> Press Ctrl+Shift+Delete, select "Cached images and files", click Clear</li>';
    echo '<li><strong>Close and restart your browser completely</strong></li>';
    echo '<li><strong>Open a fresh browser window</strong></li>';
    echo '<li><strong>Go to the indexing page</strong> - You will see "Emergency Stop Active" message</li>';
    echo '<li><strong>Click "Resume Indexing"</strong> button when ready to re-enable indexing</li>';
    echo '</ol>';
    echo '<p style="margin-top: 20px;"><a href="' . admin_url('admin.php?page=wp-gpt-rag-chat-indexing') . '" style="background: white; color: #00a32a; padding: 15px 30px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; font-size: 16px;">Go to Indexing Page</a></p>';
    echo '</div>';
    exit;
}

// Handle resume action
if (isset($_GET['action']) && $_GET['action'] === 'resume') {
    // CLEAR THE EMERGENCY STOP FLAG
    delete_transient('wp_gpt_rag_emergency_stop');
    
    echo '<div style="background: #2271b1; color: white; padding: 20px; margin: 20px; border-radius: 8px; text-align: center;">';
    echo '<h1>✅ Indexing Re-Enabled!</h1>';
    echo '<p>You can now use the indexing functions normally.</p>';
    echo '<p><a href="' . admin_url('admin.php?page=wp-gpt-rag-chat-indexing') . '" style="background: white; color: #2271b1; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px; font-weight: bold;">Go to Indexing Page</a></p>';
    echo '</div>';
    exit;
}

// Check current status
$emergency_stop_active = get_transient('wp_gpt_rag_emergency_stop');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Emergency Stop - Chatbot Indexing</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f0f0f0;
            padding: 0;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d63638;
            text-align: center;
            margin-bottom: 30px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .info {
            background: #e7f3ff;
            border: 2px solid #2271b1;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .stop-btn {
            background: #d63638;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            width: 100%;
            text-align: center;
            text-decoration: none;
        }
        .stop-btn:hover {
            background: #b32d2e;
        }
        .steps {
            text-align: left;
            margin: 20px 0;
        }
        .steps li {
            padding: 10px 0;
            font-size: 16px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #2271b1;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛑 Emergency Stop - Indexing Control</h1>
        
        <?php if ($emergency_stop_active): ?>
        <div style="background: #d63638; color: white; padding: 20px; border-radius: 4px; margin-bottom: 30px;">
            <h2 style="margin-top: 0; color: white;">⛔ EMERGENCY STOP IS ACTIVE</h2>
            <p><strong style="font-size: 18px;">ALL INDEXING IS CURRENTLY BLOCKED</strong></p>
            <p>The server will reject any indexing requests. This will automatically expire in 1 hour, or you can manually re-enable below.</p>
        </div>
        <?php else: ?>
        <div class="warning">
            <h2 style="margin-top: 0;">⚠️ Current Status</h2>
            <p><strong>Emergency stop is NOT active. If indexing is still running, click the button below to activate server-side blocking.</strong></p>
        </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>How to Stop Indexing:</h3>
            <ol class="steps">
                <li><strong>Click the button below</strong> to clear server-side cache</li>
                <li><strong>Close ALL browser tabs</strong> completely</li>
                <li><strong>Clear browser cache:</strong>
                    <ul>
                        <li>Press <code>Ctrl+Shift+Delete</code> (Windows) or <code>Cmd+Shift+Delete</code> (Mac)</li>
                        <li>Select "Cached images and files"</li>
                        <li>Click "Clear data"</li>
                    </ul>
                </li>
                <li><strong>Restart your browser</strong></li>
                <li><strong>Open the indexing page</strong> in a fresh window</li>
            </ol>
        </div>
        
        <?php if ($emergency_stop_active): ?>
        <a href="?action=resume" class="stop-btn" style="background: #00a32a;" onclick="return confirm('This will re-enable indexing. Continue?');">
            ✅ RESUME INDEXING (Re-Enable)
        </a>
        <?php else: ?>
        <a href="?action=stop_all" class="stop-btn" onclick="return confirm('This will BLOCK ALL INDEXING at the server level. Continue?');">
            🛑 ACTIVATE EMERGENCY STOP (Block All Indexing)
        </a>
        <?php endif; ?>
        
        <div class="back-link">
            <p><a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>">← Back to Indexing Page</a></p>
        </div>
    </div>
</body>
</html>


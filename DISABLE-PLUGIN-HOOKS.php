<?php
/**
 * ⚠️ DISABLE ALL PLUGIN HOOKS TEMPORARILY ⚠️
 * This removes all WordPress hooks that could trigger indexing
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

global $wp_filter;

$removed_hooks = [];

// Get all hooks and check for our plugin
foreach ($wp_filter as $hook_name => $hook) {
    if (isset($hook->callbacks)) {
        foreach ($hook->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback_key => $callback_data) {
                // Check if this is our plugin's callback
                if (is_array($callback_data['function'])) {
                    $class_name = is_object($callback_data['function'][0]) ? get_class($callback_data['function'][0]) : $callback_data['function'][0];
                    if (strpos($class_name, 'WP_GPT_RAG_Chat') !== false) {
                        remove_action($hook_name, $callback_data['function'], $priority);
                        $removed_hooks[] = $hook_name . ' → ' . $class_name . '::' . $callback_data['function'][1];
                    }
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔌 Plugin Hooks Disabled</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #d63638;
            margin-bottom: 20px;
            font-size: 36px;
        }
        .alert {
            background: #00a32a;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
        }
        .hooks-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        .hook-item {
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-left: 3px solid #d63638;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 5px 0;
        }
        .info {
            background: #e5f5fa;
            border-left: 4px solid #2271b1;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 Plugin Hooks Disabled</h1>
        
        <div class="alert">
            ✓ Removed <?php echo count($removed_hooks); ?> WordPress hooks from the plugin
        </div>
        
        <div class="info">
            <p><strong>Note:</strong> This only affects the current page load. When you refresh, hooks will be re-registered.</p>
            <p>This is useful to temporarily stop any automatic triggers.</p>
        </div>
        
        <?php if (count($removed_hooks) > 0): ?>
            <h3>Removed Hooks:</h3>
            <div class="hooks-list">
                <?php foreach ($removed_hooks as $hook): ?>
                    <div class="hook-item"><?php echo esc_html($hook); ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No active hooks found (this is good - means nothing is running).</p>
        <?php endif; ?>
        
        <div style="text-align: center;">
            <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button">
                Go to Indexing Page
            </a>
        </div>
    </div>
</body>
</html>


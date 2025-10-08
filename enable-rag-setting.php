<?php
/**
 * Enable RAG setting
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/enable-rag-setting.php
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
    <title>Enable RAG Setting</title>
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
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Enable RAG Setting</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'check_current':
                        echo "<h2>📋 Current Settings Status</h2>\n";
                        
                        $settings = get_option('wp_gpt_rag_chat_settings', []);
                        
                        echo "<h3>Current Settings:</h3>\n";
                        echo "<pre>";
                        print_r($settings);
                        echo "</pre>\n";
                        
                        if (isset($settings['enable_rag'])) {
                            echo "<p class='success'>✅ enable_rag setting exists: " . ($settings['enable_rag'] ? 'Enabled' : 'Disabled') . "</p>\n";
                        } else {
                            echo "<p class='warning'>⚠️ enable_rag setting does NOT exist in current settings</p>\n";
                        }
                        break;
                        
                    case 'add_enable_rag':
                        echo "<h2>🔧 Adding enable_rag Setting</h2>\n";
                        
                        $settings = get_option('wp_gpt_rag_chat_settings', []);
                        
                        // Add enable_rag setting
                        $settings['enable_rag'] = true;
                        
                        // Save settings
                        $result = update_option('wp_gpt_rag_chat_settings', $settings);
                        
                        if ($result) {
                            echo "<p class='success'>✅ Successfully added enable_rag = true to settings!</p>\n";
                            echo "<p class='info'>RAG system should now be enabled.</p>\n";
                        } else {
                            echo "<p class='error'>❌ Failed to update settings</p>\n";
                        }
                        break;
                        
                    case 'test_rag_status':
                        echo "<h2>🧪 Test RAG Status</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\Settings')) {
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            
                            echo "<h3>Settings from Settings class:</h3>\n";
                            echo "<pre>";
                            print_r($settings);
                            echo "</pre>\n";
                            
                            if (isset($settings['enable_rag'])) {
                                echo "<p class='success'>✅ enable_rag setting found: " . ($settings['enable_rag'] ? 'Enabled' : 'Disabled') . "</p>\n";
                            } else {
                                echo "<p class='warning'>⚠️ enable_rag setting still not found in Settings class</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ Settings class not found</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_current">
            <button type="submit" class="button">📋 Check Current Settings</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="add_enable_rag">
            <button type="submit" class="button">🔧 Add enable_rag Setting</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_rag_status">
            <button type="submit" class="button">🧪 Test RAG Status</button>
        </form>
        
        <h2>📍 How to Enable RAG in WordPress Admin</h2>
        
        <h3>Method 1: WordPress Admin Settings</h3>
        <ol>
            <li>Go to <strong>WordPress Admin Dashboard</strong></li>
            <li>Navigate to <strong>Settings</strong> → <strong>WP GPT RAG Chat</strong></li>
            <li>Look for <strong>RAG Settings</strong> or <strong>General Settings</strong> tab</li>
            <li>Find <strong>Enable RAG</strong> checkbox (if it exists)</li>
            <li>Check/Enable the RAG option</li>
            <li>Click <strong>Save Settings</strong></li>
        </ol>
        
        <h3>Method 2: Direct Database Update</h3>
        <p>If the setting doesn't exist in the admin interface, use the tool above to add it directly.</p>
        
        <h3>Method 3: For Online Plugin</h3>
        <p>Apply the same steps to your online WordPress installation:</p>
        <ol>
            <li>Access your online WordPress admin</li>
            <li>Go to <strong>Settings</strong> → <strong>WP GPT RAG Chat</strong></li>
            <li>Enable RAG in the same way</li>
            <li>Or use the database update method if needed</li>
        </ol>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-settings" class="button" target="_blank">⚙️ Plugin Settings</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/check-rag-settings.php" class="button" target="_blank">🧪 Check RAG Settings</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-system.php" class="button" target="_blank">🧪 Test RAG System</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

<?php
/**
 * Simple test to verify admin menu should be visible
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Simple Admin Menu Test</h2>\n";

// Check if we're in admin
if (is_admin()) {
    echo "<p><strong>Status:</strong> In WordPress Admin</p>\n";
} else {
    echo "<p><strong>Status:</strong> NOT in WordPress Admin</p>\n";
}

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User:</h3>\n";
echo "<p><strong>User:</strong> " . $current_user->user_login . "</p>\n";
echo "<p><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</p>\n";

// Check manage_options capability
if (current_user_can('manage_options')) {
    echo "<p><strong>manage_options:</strong> ✅ YES - Menu should be visible</p>\n";
} else {
    echo "<p><strong>manage_options:</strong> ❌ NO - Menu will be hidden</p>\n";
}

// Check if plugin is active
if (is_plugin_active('chatbot-nuwab-2/wp-gpt-rag-chat.php')) {
    echo "<p><strong>Plugin Status:</strong> ✅ ACTIVE</p>\n";
} else {
    echo "<p><strong>Plugin Status:</strong> ❌ INACTIVE</p>\n";
}

// Check if our plugin class exists
if (class_exists('WP_GPT_RAG_Chat\Plugin')) {
    echo "<p><strong>Plugin Class:</strong> ✅ EXISTS</p>\n";
} else {
    echo "<p><strong>Plugin Class:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>If you see ✅ for all checks, the menu should be visible</li>\n";
echo "<li>If any ❌, fix that issue first</li>\n";
echo "<li>Try refreshing the WordPress admin page</li>\n";
echo "<li>Check if the menu appears in the left sidebar</li>\n";
echo "</ol>\n";

echo "<h3>Manual Menu Check:</h3>\n";
echo "<p>Try accessing the menu directly:</p>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-settings') . "' target='_blank'>Settings</a></li>\n";
echo "</ul>\n";

echo "<h3>If Still Not Working:</h3>\n";
echo "<ol>\n";
echo "<li>Deactivate the plugin</li>\n";
echo "<li>Reactivate the plugin</li>\n";
echo "<li>Clear any caching</li>\n";
echo "<li>Check for JavaScript errors in browser console</li>\n";
echo "</ol>\n";
?>

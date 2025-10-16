<?php
/**
 * Test script to verify editor access to plugin menu
 * 
 * This script tests the RBAC system for editor users
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Testing Editor Access to Plugin Menu</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User ID:</strong> " . $current_user->ID . "</li>\n";
echo "<li><strong>Username:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Email:</strong> " . $current_user->user_email . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "</ul>\n";

// Check capabilities
echo "<h3>Capability Checks:</h3>\n";
echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>administrator:</strong> " . (current_user_can('administrator') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check if RBAC class exists and test methods
if (class_exists('WP_GPT_RAG_Chat\RBAC')) {
    echo "<h3>RBAC Class Checks:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>get_user_role_display():</strong> " . WP_GPT_RAG_Chat\RBAC::get_user_role_display() . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>ERROR:</strong> RBAC class not found!</p>\n";
}

// Check if plugin is active
echo "<h3>Plugin Status:</h3>\n";
if (is_plugin_active('chatbot-nuwab-2/wp-gpt-rag-chat.php')) {
    echo "<p><strong>Plugin Status:</strong> ACTIVE</p>\n";
} else {
    echo "<p><strong>Plugin Status:</strong> INACTIVE</p>\n";
}

// Test menu visibility logic
echo "<h3>Menu Visibility Logic:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Administrator Path:</strong> Should see full menu</p>\n";
} else {
    echo "<p><strong>Non-Administrator Path:</strong> Checking RBAC permissions...</p>\n";
    if (WP_GPT_RAG_Chat\RBAC::can_view_logs()) {
        echo "<p><strong>RBAC Check:</strong> ✅ PASSED - Menu should be visible</p>\n";
    } else {
        echo "<p><strong>RBAC Check:</strong> ❌ FAILED - Menu will be hidden</p>\n";
    }
}

echo "<h3>Expected Menu for Editor:</h3>\n";
echo "<ul>\n";
echo "<li>Nuwab AI Assistant (Main Menu)</li>\n";
echo "<li>├── Dashboard (Basic overview)</li>\n";
echo "<li>└── View Logs (System logs, read-only)</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Login as an Editor user</li>\n";
echo "<li>Go to WordPress Admin Dashboard</li>\n";
echo "<li>Look for 'Nuwab AI Assistant' in the left sidebar</li>\n";
echo "<li>Click on it to see the submenu items</li>\n";
echo "<li>Verify only Dashboard and View Logs are visible</li>\n";
echo "</ol>\n";

echo "<h3>Direct Access URLs for Editor:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-logs') . "' target='_blank'>View Logs</a></li>\n";
echo "</ul>\n";

echo "<h3>If Editor Still Can't See Menu:</h3>\n";
echo "<ol>\n";
echo "<li>Deactivate and reactivate the plugin</li>\n";
echo "<li>Clear any caching</li>\n";
echo "<li>Check if the editor user has the 'editor' role</li>\n";
echo "<li>Verify the RBAC capabilities were added</li>\n";
echo "</ol>\n";

echo "<h3>Debug Information:</h3>\n";
echo "<p><strong>Current User Role:</strong> " . (in_array('editor', $current_user->roles) ? 'Editor' : 'Not Editor') . "</p>\n";
echo "<p><strong>Can Edit Posts:</strong> " . (current_user_can('edit_posts') ? 'Yes' : 'No') . "</p>\n";
echo "<p><strong>Can Manage Options:</strong> " . (current_user_can('manage_options') ? 'Yes' : 'No') . "</p>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If the RBAC system is working correctly for editors:</p>\n";
echo "<ul>\n";
echo "<li>Editors should see the Nuwab AI Assistant menu</li>\n";
echo "<li>Only Dashboard and View Logs should be visible</li>\n";
echo "<li>All other menu items should be hidden</li>\n";
echo "<li>Direct access to restricted pages should be denied</li>\n";
echo "</ul>\n";
?>


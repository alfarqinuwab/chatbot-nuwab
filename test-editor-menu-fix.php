<?php
/**
 * Test script to verify editor menu fix
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Test Editor Menu Fix</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Logged In:</strong> " . (is_user_logged_in() ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check capabilities
echo "<h3>Capability Checks:</h3>\n";
echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_view_logs:</strong> " . (current_user_can('wp_gpt_rag_view_logs') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check RBAC methods
if (class_exists('WP_GPT_RAG_Chat\RBAC')) {
    echo "<h3>RBAC Method Results:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>RBAC Class:</strong> ❌ NOT FOUND</p>\n";
}

// Test menu visibility logic
echo "<h3>Menu Visibility Logic:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Administrator Path:</strong> Should see full admin menu</p>\n";
} else {
    echo "<p><strong>Non-Administrator Path:</strong> Checking RBAC permissions...</p>\n";
    if (WP_GPT_RAG_Chat\RBAC::is_log_viewer() && !WP_GPT_RAG_Chat\RBAC::is_aims_manager()) {
        echo "<p><strong>Editor Menu:</strong> ✅ SHOULD BE VISIBLE</p>\n";
    } else {
        echo "<p><strong>Editor Menu:</strong> ❌ WILL NOT BE VISIBLE</p>\n";
    }
}

echo "<h3>Test Direct Access URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard-editor') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=logs') . "' target='_blank'>Analytics - Logs Tab</a></li>\n";
echo "</ul>\n";

echo "<h3>Expected Results for Editor:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>manage_options:</strong> NO (not admin)</li>\n";
echo "<li>✅ <strong>edit_posts:</strong> YES (editor role)</li>\n";
echo "<li>✅ <strong>is_log_viewer():</strong> YES (should work)</li>\n";
echo "<li>✅ <strong>is_aims_manager():</strong> NO (not admin)</li>\n";
echo "<li>✅ <strong>Editor Menu:</strong> SHOULD BE VISIBLE</li>\n";
echo "</ul>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<p><strong>Problem:</strong> Admin menu was registered with 'manage_options' capability</p>\n";
echo "<p><strong>Solution:</strong> Created separate menu for editors with 'edit_posts' capability</p>\n";
echo "<p><strong>Result:</strong> Editors now have their own menu with proper permissions</p>\n";

echo "<h3>Menu Structure for Editors:</h3>\n";
echo "<ul>\n";
echo "<li>Nuwab AI Assistant (Main Menu - Editor Version)</li>\n";
echo "<li>├── Dashboard (Editor Dashboard)</li>\n";
echo "<li>└── Analytics & Logs (Full Analytics Access)</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Login as an Editor user</li>\n";
echo "<li>Go to WordPress Admin</li>\n";
echo "<li>Look for 'Nuwab AI Assistant' menu (should be visible)</li>\n";
echo "<li>Click on it to see submenu items</li>\n";
echo "<li>Click on 'Analytics & Logs' to test access</li>\n";
echo "</ol>\n";

echo "<h3>If Still Not Working:</h3>\n";
echo "<ol>\n";
echo "<li>Clear browser cache</li>\n";
echo "<li>Logout and login again</li>\n";
echo "<li>Check if user has 'editor' role</li>\n";
echo "<li>Verify RBAC capabilities were added</li>\n";
echo "<li>Try accessing the direct URLs above</li>\n";
echo "</ol>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If the fix is working correctly, editors should see their own 'Nuwab AI Assistant' menu with Dashboard and Analytics & Logs options.</p>\n";
?>

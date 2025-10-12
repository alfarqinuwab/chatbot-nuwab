<?php
/**
 * Test script to verify analytics page access through WordPress admin
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Test Analytics Access Through WordPress Admin</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Logged In:</strong> " . (is_user_logged_in() ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test the new permission logic
echo "<h3>New Permission Logic Test:</h3>\n";
$is_logged_in = is_user_logged_in();
echo "<p><strong>is_user_logged_in():</strong> " . ($is_logged_in ? 'YES' : 'NO') . "</p>\n";

if ($is_logged_in) {
    echo "<p><strong>Access Decision:</strong> ✅ ALLOWED (user is logged in)</p>\n";
} else {
    echo "<p><strong>Access Decision:</strong> ❌ DENIED (user not logged in)</p>\n";
}

echo "<h3>Test Direct Access URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs (Main)</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=logs') . "' target='_blank'>Analytics - Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=dashboard') . "' target='_blank'>Analytics - Dashboard Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=error-logs') . "' target='_blank'>Analytics - Error Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=api-usage') . "' target='_blank'>Analytics - API Usage Tab</a></li>\n";
echo "</ul>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>User Logged In:</strong> YES (should be logged in)</li>\n";
echo "<li>✅ <strong>Access Decision:</strong> ALLOWED (should work)</li>\n";
echo "<li>✅ <strong>All URLs:</strong> Should load without permission errors</li>\n";
echo "</ul>\n";

echo "<h3>If Still Getting Permission Error:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Clear Browser Cache:</strong> Hard refresh (Ctrl+F5)</li>\n";
echo "<li><strong>Logout/Login:</strong> Refresh your WordPress session</li>\n";
echo "<li><strong>Check Plugin Status:</strong> Ensure plugin is active</li>\n";
echo "<li><strong>Check WordPress:</strong> Verify you're in wp-admin</li>\n";
echo "<li><strong>Try Different Browser:</strong> Test in incognito/private mode</li>\n";
echo "</ol>\n";

echo "<h3>Debug Information:</h3>\n";
echo "<ul>\n";
echo "<li><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</li>\n";
echo "<li><strong>Plugin Active:</strong> " . (is_plugin_active('chatbot-nuwab-2/wp-gpt-rag-chat.php') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>Admin URL:</strong> " . admin_url() . "</li>\n";
echo "<li><strong>Current URL:</strong> " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unknown') . "</li>\n";
echo "</ul>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<p><strong>Old Logic:</strong> Complex permission checks with manage_options and edit_posts</p>\n";
echo "<p><strong>New Logic:</strong> Simple check - only require user to be logged in</p>\n";
echo "<p><strong>Result:</strong> Any logged-in user can access analytics page</p>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If you're logged in, the analytics page should now be accessible through WordPress admin.</p>\n";
echo "<p>Try clicking the links above to test access.</p>\n";
?>

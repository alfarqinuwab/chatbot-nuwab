<?php
/**
 * Simple test for analytics page access
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Simple Analytics Access Test</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User:</h3>\n";
echo "<p><strong>User:</strong> " . $current_user->user_login . "</p>\n";
echo "<p><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</p>\n";

// Test the simplified permission logic
echo "<h3>Simplified Permission Test:</h3>\n";
$has_admin = current_user_can('manage_options');
$has_editor = current_user_can('edit_posts');

echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . ($has_admin ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . ($has_editor ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test the condition
$will_deny = !$has_admin && !$has_editor;
echo "<p><strong>Will Deny Access:</strong> " . ($will_deny ? 'YES ❌' : 'NO ✅') . "</p>\n";

echo "<h3>Test Direct Access:</h3>\n";
echo "<p><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs Page</a></p>\n";

echo "<h3>Expected Results for Editor:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>manage_options:</strong> NO (not admin)</li>\n";
echo "<li>✅ <strong>edit_posts:</strong> YES (editor role)</li>\n";
echo "<li>✅ <strong>Will Deny Access:</strong> NO (should allow)</li>\n";
echo "</ul>\n";

echo "<h3>If Still Not Working:</h3>\n";
echo "<ol>\n";
echo "<li>Clear browser cache</li>\n";
echo "<li>Logout and login again</li>\n";
echo "<li>Check if user has 'editor' role</li>\n";
echo "<li>Try accessing the page directly</li>\n";
echo "</ol>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<p><strong>Simplified Logic:</strong> Only check manage_options OR edit_posts</p>\n";
echo "<p><strong>No RBAC Dependencies:</strong> Removed complex RBAC checks</p>\n";
echo "<p><strong>Direct Access:</strong> If user has edit_posts, allow access</p>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>This simplified approach should work for all editor users.</p>\n";
?>

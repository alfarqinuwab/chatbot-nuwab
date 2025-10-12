<?php
/**
 * Test script to verify analytics page permission fix
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Testing Analytics Page Permission Fix</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "</ul>\n";

// Test the new permission logic
echo "<h3>Permission Logic Test:</h3>\n";
$has_admin_access = current_user_can('manage_options');
$has_editor_access = current_user_can('edit_posts');
$has_rbac_access = WP_GPT_RAG_Chat\RBAC::can_view_logs();

echo "<ul>\n";
echo "<li><strong>Admin Access (manage_options):</strong> " . ($has_admin_access ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>Editor Access (edit_posts):</strong> " . ($has_editor_access ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>RBAC Access (can_view_logs):</strong> " . ($has_rbac_access ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test the condition
$should_allow = $has_admin_access || $has_editor_access || $has_rbac_access;
echo "<p><strong>Should Allow Access:</strong> " . ($should_allow ? 'YES ✅' : 'NO ❌') . "</p>\n";

// Test the actual condition that will be used
$will_deny = !$has_admin_access && !$has_editor_access && !$has_rbac_access;
echo "<p><strong>Will Deny Access:</strong> " . ($will_deny ? 'YES ❌' : 'NO ✅') . "</p>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Login as an Editor user</li>\n";
echo "<li>Go to WordPress Admin</li>\n";
echo "<li>Click on 'Nuwab AI Assistant' menu</li>\n";
echo "<li>Click on 'Analytics & Logs' submenu</li>\n";
echo "<li>Verify the page loads without permission errors</li>\n";
echo "<li>Test all tabs: Logs, Dashboard, Error Logs, API Usage</li>\n";
echo "</ol>\n";

echo "<h3>Direct Access Test:</h3>\n";
echo "<p>Try accessing the analytics page directly:</p>\n";
echo "<p><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs Page</a></p>\n";

echo "<h3>Expected Results for Editor:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Admin Access:</strong> NO (not administrator)</li>\n";
echo "<li>✅ <strong>Editor Access:</strong> YES (has editor role)</li>\n";
echo "<li>✅ <strong>RBAC Access:</strong> YES (should work)</li>\n";
echo "<li>✅ <strong>Should Allow Access:</strong> YES</li>\n";
echo "<li>✅ <strong>Will Deny Access:</strong> NO</li>\n";
echo "</ul>\n";

echo "<h3>If Still Getting Permission Error:</h3>\n";
echo "<ol>\n";
echo "<li>Run the debug script: <a href='debug-analytics-permissions.php' target='_blank'>debug-analytics-permissions.php</a></li>\n";
echo "<li>Run the capability reset: <a href='reset-rbac-capabilities.php' target='_blank'>reset-rbac-capabilities.php</a></li>\n";
echo "<li>Check if user has 'editor' role in Users → Your Profile</li>\n";
echo "<li>Clear any caching</li>\n";
echo "<li>Refresh the page</li>\n";
echo "</ol>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<p><strong>Old Logic:</strong> Complex condition that might fail</p>\n";
echo "<p><strong>New Logic:</strong> Simple OR condition - allow if user has ANY of:</p>\n";
echo "<ul>\n";
echo "<li>Administrator access (manage_options)</li>\n";
echo "<li>Editor access (edit_posts)</li>\n";
echo "<li>RBAC access (can_view_logs)</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If the fix is working correctly, editors should be able to access the Analytics & Logs page without permission errors.</p>\n";
?>

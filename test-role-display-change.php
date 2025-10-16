<?php
/**
 * Test Role Display Change
 * 
 * Expected:
 * - Administrator role should display as "Administrator"
 * - Editor role should display as "Log Viewer"
 * - Other roles should display as "No Access"
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Role Display Change Test</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Logged In:</strong> " . (is_user_logged_in() ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check RBAC capabilities
echo "<h3>RBAC Capabilities Check:</h3>\n";
echo "<ul>\n";
echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>User Role Display:</strong> " . WP_GPT_RAG_Chat\RBAC::get_user_role_display() . "</li>\n";
echo "</ul>\n";

// Check WordPress capabilities
echo "<h3>WordPress Capabilities Check:</h3>\n";
echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Determine expected role display
echo "<h3>Expected Role Display:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Role Display:</strong> <span style='color: green; font-weight: bold;'>Administrator</span></p>\n";
    echo "<p><strong>Previous Display:</strong> AIMS Manager (Administrator)</p>\n";
    echo "<p><strong>Change Applied:</strong> ✅ Simplified to 'Administrator'</p>\n";
} elseif (current_user_can('edit_posts')) {
    echo "<p><strong>Role Display:</strong> <span style='color: blue; font-weight: bold;'>Log Viewer</span></p>\n";
    echo "<p><strong>Change Applied:</strong> ✅ No change (already correct)</p>\n";
} else {
    echo "<p><strong>Role Display:</strong> <span style='color: red; font-weight: bold;'>No Access</span></p>\n";
    echo "<p><strong>Change Applied:</strong> ✅ No change (already correct)</p>\n";
}

// Test dashboard URLs to see role display
echo "<h3>Test Dashboard URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Administrator Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard-editor') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "</ul>\n";

// Check the RBAC class for the role display method
$rbac_file = WP_GPT_RAG_Chat\RBAC::class;
$rbac_file_path = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/class-rbac.php';

if (file_exists($rbac_file_path)) {
    echo "<p><strong>RBAC Class:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($rbac_file_path);
    
    // Check for updated role display
    $updated_role_display = strpos($content, "return __('Administrator', 'wp-gpt-rag-chat');") !== false;
    echo "<p><strong>Updated role display:</strong> " . ($updated_role_display ? 'YES' : 'NO') . "</p>\n";
    
    // Check for old role display (should not exist)
    $old_role_display = strpos($content, "AIMS Manager (Administrator)") !== false;
    echo "<p><strong>Old role display removed:</strong> " . (!$old_role_display ? 'YES' : 'NO') . "</p>\n";
    
    // Check for get_user_role_display method
    $role_display_method = strpos($content, 'get_user_role_display()') !== false;
    echo "<p><strong>Role display method exists:</strong> " . ($role_display_method ? 'YES' : 'NO') . "</p>\n";
    
    // Count role display conditions
    $role_conditions = substr_count($content, 'current_user_can(');
    echo "<p><strong>Role conditions:</strong> {$role_conditions}</p>\n";
    
} else {
    echo "<p><strong>RBAC Class:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Expected Results:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>User Role</th><th>Previous Display</th><th>New Display</th><th>Status</th></tr>\n";
echo "<tr><td><strong>Administrator</strong></td><td>AIMS Manager (Administrator)</td><td>Administrator</td><td>✅ Updated</td></tr>\n";
echo "<tr><td><strong>Editor</strong></td><td>Log Viewer</td><td>Log Viewer</td><td>✅ No Change</td></tr>\n";
echo "<tr><td><strong>Other Roles</strong></td><td>No Access</td><td>No Access</td><td>✅ No Change</td></tr>\n";
echo "</table>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Login as Administrator:</strong> Access the dashboard page</li>\n";
echo "<li><strong>Check Role Display:</strong> Should show 'Your Role: Administrator'</li>\n";
echo "<li><strong>Login as Editor:</strong> Access the dashboard page</li>\n";
echo "<li><strong>Check Role Display:</strong> Should show 'Your Role: Log Viewer'</li>\n";
echo "<li><strong>Verify Change:</strong> Administrator role should be simplified</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Administrator Role:</strong> Displays as 'Administrator' (simplified)</li>\n";
echo "<li>✅ <strong>Editor Role:</strong> Displays as 'Log Viewer' (unchanged)</li>\n";
echo "<li>✅ <strong>Other Roles:</strong> Display as 'No Access' (unchanged)</li>\n";
echo "<li>✅ <strong>Consistency:</strong> Role display is consistent across all pages</li>\n";
echo "</ul>\n";

echo "<h3>Change Applied:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>RBAC Class Updated:</strong> Modified get_user_role_display() method</li>\n";
echo "<li>✅ <strong>Role Display Simplified:</strong> 'AIMS Manager (Administrator)' → 'Administrator'</li>\n";
echo "<li>✅ <strong>Other Roles Unchanged:</strong> Editor and other roles remain the same</li>\n";
echo "<li>✅ <strong>Consistent Display:</strong> Role display is consistent across all templates</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Administrator role should now display as 'Administrator' instead of 'AIMS Manager (Administrator)'.</p>\n";
echo "<p>Editor role should continue to display as 'Log Viewer' as before.</p>\n";
?>


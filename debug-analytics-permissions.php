<?php
/**
 * Debug script to check analytics page permissions
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Debug Analytics Page Permissions</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User ID:</strong> " . $current_user->ID . "</li>\n";
echo "<li><strong>Username:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Email:</strong> " . $current_user->user_email . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "</ul>\n";

// Check all relevant capabilities
echo "<h3>Capability Checks:</h3>\n";
echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_view_logs:</strong> " . (current_user_can('wp_gpt_rag_view_logs') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_log_viewer:</strong> " . (current_user_can('wp_gpt_rag_log_viewer') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check RBAC methods
if (class_exists('WP_GPT_RAG_Chat\RBAC')) {
    echo "<h3>RBAC Method Results:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>can_manage_analytics():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_manage_analytics() ? 'YES' : 'NO') . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>ERROR:</strong> RBAC class not found!</p>\n";
}

// Test the permission logic manually
echo "<h3>Permission Logic Test:</h3>\n";
$has_manage_options = current_user_can('manage_options');
$has_view_logs = WP_GPT_RAG_Chat\RBAC::can_view_logs();
$has_edit_posts = current_user_can('edit_posts');

echo "<ul>\n";
echo "<li><strong>current_user_can('manage_options'):</strong> " . ($has_manage_options ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>RBAC::can_view_logs():</strong> " . ($has_view_logs ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>current_user_can('edit_posts'):</strong> " . ($has_edit_posts ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test the actual condition
$should_allow = $has_manage_options || $has_view_logs || $has_edit_posts;
echo "<p><strong>Should Allow Access:</strong> " . ($should_allow ? 'YES' : 'NO') . "</p>\n";

// Test the current condition logic
$current_condition = !$has_manage_options && !$has_view_logs && !$has_edit_posts;
echo "<p><strong>Current Condition (will deny if true):</strong> " . ($current_condition ? 'YES - WILL DENY' : 'NO - WILL ALLOW') . "</p>\n";

echo "<h3>Debugging Steps:</h3>\n";
echo "<ol>\n";
echo "<li>If 'Should Allow Access' is NO, the user doesn't have proper permissions</li>\n";
echo "<li>If 'Current Condition' is YES, the permission check will fail</li>\n";
echo "<li>Check if the user has the 'editor' role</li>\n";
echo "<li>Verify RBAC capabilities were added</li>\n";
echo "<li>Try running the capability reset script</li>\n";
echo "</ol>\n";

echo "<h3>Quick Fixes:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Run Capability Reset:</strong> <a href='reset-rbac-capabilities.php' target='_blank'>reset-rbac-capabilities.php</a></li>\n";
echo "<li><strong>Check User Role:</strong> Go to Users → Your Profile and verify role</li>\n";
echo "<li><strong>Test Direct Access:</strong> <a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics Page</a></li>\n";
echo "</ol>\n";

echo "<h3>Expected Results for Editor:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>manage_options:</strong> NO (not admin)</li>\n";
echo "<li>✅ <strong>edit_posts:</strong> YES (editor role)</li>\n";
echo "<li>✅ <strong>wp_gpt_rag_view_logs:</strong> YES (should be added)</li>\n";
echo "<li>✅ <strong>can_view_logs():</strong> YES (should work)</li>\n";
echo "<li>✅ <strong>Should Allow Access:</strong> YES</li>\n";
echo "<li>✅ <strong>Current Condition:</strong> NO - WILL ALLOW</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If all checks show the expected results, the analytics page should be accessible.</p>\n";
?>


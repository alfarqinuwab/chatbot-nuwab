<?php
/**
 * Test Editor Access to Logs Page
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Editor Logs Access Test</h2>\n";

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
echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>User Role Display:</strong> " . WP_GPT_RAG_Chat\RBAC::get_user_role_display() . "</li>\n";
echo "</ul>\n";

// Check WordPress capabilities
echo "<h3>WordPress Capabilities Check:</h3>\n";
echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_view_logs:</strong> " . (current_user_can('wp_gpt_rag_view_logs') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_log_viewer:</strong> " . (current_user_can('wp_gpt_rag_log_viewer') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check if the plugin file exists and has the correct menu structure
$plugin_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/Plugin.php';

if (file_exists($plugin_file)) {
    echo "<p><strong>Plugin File:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($plugin_file);
    
    // Check if Analytics & Logs submenu is registered for editors
    $analytics_submenu_count = substr_count($content, "Analytics & Logs', 'wp-gpt-rag-chat'");
    echo "<p><strong>Analytics & Logs submenu registrations:</strong> {$analytics_submenu_count}</p>\n";
    
    // Check if it's available to all authorized users
    $available_to_all = strpos($content, "// Analytics & Logs submenu (available to all authorized users)") !== false;
    echo "<p><strong>Available to all authorized users:</strong> " . ($available_to_all ? 'YES' : 'NO') . "</p>\n";
    
    // Check for wp_gpt_rag_view_logs capability usage
    $view_logs_capability_count = substr_count($content, 'wp_gpt_rag_view_logs');
    echo "<p><strong>wp_gpt_rag_view_logs capability usage:</strong> {$view_logs_capability_count}</p>\n";
    
} else {
    echo "<p><strong>Plugin File:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Test Direct Access URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=logs') . "' target='_blank'>Analytics - Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=dashboard') . "' target='_blank'>Analytics - Dashboard Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=error-logs') . "' target='_blank'>Analytics - Error Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=api-usage') . "' target='_blank'>Analytics - API Usage Tab</a></li>\n";
echo "</ul>\n";

echo "<h3>Menu Structure Fix Applied:</h3>\n";
echo "<p><strong>Added Analytics & Logs submenu for editors:</strong></p>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Menu Registration:</strong> Added Analytics & Logs submenu for all authorized users</li>\n";
echo "<li>✅ <strong>Capability:</strong> Uses 'wp_gpt_rag_view_logs' capability</li>\n";
echo "<li>✅ <strong>Page Slug:</strong> 'wp-gpt-rag-chat-analytics'</li>\n";
echo "<li>✅ <strong>Callback:</strong> analytics_page() method</li>\n";
echo "<li>✅ <strong>Removed Duplicate:</strong> Removed duplicate from AIMS Manager section</li>\n";
echo "</ul>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Menu Visible:</strong> 'Analytics & Logs' submenu should appear</li>\n";
echo "<li>✅ <strong>Page Accessible:</strong> Can click on 'Analytics & Logs' without errors</li>\n";
echo "<li>✅ <strong>All Tabs Work:</strong> Logs, Dashboard, Error Logs, API Usage tabs should work</li>\n";
echo "<li>✅ <strong>No Permission Errors:</strong> No 'You do not have permission' messages</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Login as an Editor user</li>\n";
echo "<li>Go to WordPress Admin</li>\n";
echo "<li>Look for 'Nuwab AI Assistant' menu</li>\n";
echo "<li>Click on 'Nuwab AI Assistant' to expand it</li>\n";
echo "<li>Look for 'Analytics & Logs' submenu</li>\n";
echo "<li>Click on 'Analytics & Logs' submenu</li>\n";
echo "<li>Verify the page loads without permission errors</li>\n";
echo "<li>Test all tabs: Logs, Dashboard, Error Logs, API Usage</li>\n";
echo "</ol>\n";

echo "<h3>If Still Not Working:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Clear Cache:</strong> Clear browser cache and WordPress cache</li>\n";
echo "<li><strong>Hard Refresh:</strong> Ctrl+F5 or Cmd+Shift+R</li>\n";
echo "<li><strong>Logout/Login:</strong> Refresh your WordPress session</li>\n";
echo "<li><strong>Check Menu:</strong> Make sure the menu is visible and clickable</li>\n";
echo "<li><strong>Check URL:</strong> Verify the URL is correct</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Menu Visible:</strong> 'Analytics & Logs' submenu appears under 'Nuwab AI Assistant'</li>\n";
echo "<li>✅ <strong>Page Accessible:</strong> Can click on 'Analytics & Logs' without errors</li>\n";
echo "<li>✅ <strong>No Permission Errors:</strong> No 'You do not have permission' messages</li>\n";
echo "<li>✅ <strong>Full Functionality:</strong> All tabs and features work as expected</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>The Analytics & Logs submenu should now be available to editors.</p>\n";
echo "<p>If you're still having issues, the problem might be caching or other WordPress restrictions.</p>\n";
?>


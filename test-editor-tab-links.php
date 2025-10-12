<?php
/**
 * Test Editor Tab Links
 * 
 * Expected:
 * - Editor tab links should use wp-gpt-rag-chat-analytics-editor
 * - Administrator tab links should use wp-gpt-rag-chat-analytics
 * - All internal links should be role-appropriate
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Editor Tab Links Test</h2>\n";

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

// Determine expected page parameter
echo "<h3>Expected Page Parameter:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Page Parameter:</strong> <span style='color: green; font-weight: bold;'>wp-gpt-rag-chat-analytics</span></p>\n";
    echo "<p><strong>User Type:</strong> Administrator</p>\n";
} elseif (current_user_can('edit_posts')) {
    echo "<p><strong>Page Parameter:</strong> <span style='color: blue; font-weight: bold;'>wp-gpt-rag-chat-analytics-editor</span></p>\n";
    echo "<p><strong>User Type:</strong> Editor</p>\n";
} else {
    echo "<p><strong>Page Parameter:</strong> <span style='color: red; font-weight: bold;'>No Access</span></p>\n";
    echo "<p><strong>User Type:</strong> Other Role</p>\n";
}

// Test tab URLs
echo "<h3>Test Tab URLs:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<ul>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=logs') . "' target='_blank'>Administrator - Logs Tab</a></li>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=dashboard') . "' target='_blank'>Administrator - Dashboard Tab</a></li>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=error-logs') . "' target='_blank'>Administrator - Error Logs Tab</a></li>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=api-usage') . "' target='_blank'>Administrator - API Usage Tab</a></li>\n";
    echo "</ul>\n";
} elseif (current_user_can('edit_posts')) {
    echo "<ul>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics-editor&tab=logs') . "' target='_blank'>Editor - Logs Tab</a></li>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics-editor&tab=dashboard') . "' target='_blank'>Editor - Dashboard Tab</a></li>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics-editor&tab=error-logs') . "' target='_blank'>Editor - Error Logs Tab</a></li>\n";
    echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics-editor&tab=api-usage') . "' target='_blank'>Editor - API Usage Tab</a></li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>No Access:</strong> Cannot access analytics pages</p>\n";
}

// Check the analytics page template for dynamic page parameter
$analytics_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/analytics-page.php';

if (file_exists($analytics_file)) {
    echo "<p><strong>Analytics Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($analytics_file);
    
    // Check for dynamic page parameter
    $dynamic_page = strpos($content, '$current_page = \'wp-gpt-rag-chat-analytics\';') !== false;
    echo "<p><strong>Dynamic page parameter:</strong> " . ($dynamic_page ? 'YES' : 'NO') . "</p>\n";
    
    // Check for role-based page selection
    $role_based_selection = strpos($content, 'if (RBAC::is_log_viewer() && !RBAC::is_aims_manager())') !== false;
    echo "<p><strong>Role-based page selection:</strong> " . ($role_based_selection ? 'YES' : 'NO') . "</p>\n";
    
    // Check for dynamic tab links
    $dynamic_tab_links = strpos($content, 'echo esc_attr($current_page);') !== false;
    echo "<p><strong>Dynamic tab links:</strong> " . ($dynamic_tab_links ? 'YES' : 'NO') . "</p>\n";
    
    // Check for updated form inputs
    $updated_form_inputs = strpos($content, 'value="<?php echo esc_attr($current_page); ?>"') !== false;
    echo "<p><strong>Updated form inputs:</strong> " . ($updated_form_inputs ? 'YES' : 'NO') . "</p>\n";
    
    // Check for updated clear filter links
    $updated_clear_links = strpos($content, 'href="?page=<?php echo esc_attr($current_page); ?>&tab=') !== false;
    echo "<p><strong>Updated clear filter links:</strong> " . ($updated_clear_links ? 'YES' : 'NO') . "</p>\n";
    
    // Count hardcoded page references
    $hardcoded_references = substr_count($content, 'wp-gpt-rag-chat-analytics');
    echo "<p><strong>Hardcoded page references:</strong> {$hardcoded_references}</p>\n";
    
    // Count dynamic page references
    $dynamic_references = substr_count($content, '$current_page');
    echo "<p><strong>Dynamic page references:</strong> {$dynamic_references}</p>\n";
    
} else {
    echo "<p><strong>Analytics Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Dynamic Page Parameter:</strong> Uses correct page based on user role</li>\n";
echo "<li>✅ <strong>Tab Links:</strong> All tab links use role-appropriate page parameter</li>\n";
echo "<li>✅ <strong>Form Inputs:</strong> All form hidden inputs use dynamic page parameter</li>\n";
echo "<li>✅ <strong>Clear Filter Links:</strong> All clear filter links use dynamic page parameter</li>\n";
echo "<li>✅ <strong>No Hardcoded Links:</strong> No hardcoded page references</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Login as Editor:</strong> Access the analytics page</li>\n";
echo "<li><strong>Check Tab Links:</strong> Click on each tab (Logs, Dashboard, Error Logs, API Usage)</li>\n";
echo "<li><strong>Verify URLs:</strong> URLs should contain 'wp-gpt-rag-chat-analytics-editor'</li>\n";
echo "<li><strong>Test Filters:</strong> Use filter forms and clear filter buttons</li>\n";
echo "<li><strong>Check Navigation:</strong> All navigation should work correctly</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Correct URLs:</strong> Editor URLs use 'wp-gpt-rag-chat-analytics-editor'</li>\n";
echo "<li>✅ <strong>Working Navigation:</strong> All tabs and links work correctly</li>\n";
echo "<li>✅ <strong>No Errors:</strong> No permission errors or broken links</li>\n";
echo "<li>✅ <strong>Consistent Experience:</strong> All functionality works as expected</li>\n";
echo "</ul>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Dynamic Page Parameter:</strong> Added role-based page parameter selection</li>\n";
echo "<li>✅ <strong>Updated Tab Links:</strong> All tab links now use dynamic page parameter</li>\n";
echo "<li>✅ <strong>Updated Form Inputs:</strong> All form hidden inputs use dynamic page parameter</li>\n";
echo "<li>✅ <strong>Updated Clear Links:</strong> All clear filter links use dynamic page parameter</li>\n";
echo "<li>✅ <strong>Role Separation:</strong> Editors and administrators use different page parameters</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Editor tab links should now use the correct editor-specific URLs.</p>\n";
echo "<p>All navigation and functionality should work correctly for both editors and administrators.</p>\n";
?>

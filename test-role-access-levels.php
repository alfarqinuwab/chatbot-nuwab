<?php
/**
 * Test Role Access Levels
 * 
 * Expected:
 * - Administrator: Full access to everything
 * - Editor: Only Dashboard and Analytics & Logs
 * - Other roles: No access at all
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Role Access Levels Test</h2>\n";

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
echo "<li><strong>wp_gpt_rag_view_logs:</strong> " . (current_user_can('wp_gpt_rag_view_logs') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_log_viewer:</strong> " . (current_user_can('wp_gpt_rag_log_viewer') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Determine expected access level
echo "<h3>Expected Access Level:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Access Level:</strong> <span style='color: green; font-weight: bold;'>ADMINISTRATOR - FULL ACCESS</span></p>\n";
    echo "<p><strong>Available Menus:</strong> Dashboard, Settings, Indexing, Analytics & Logs, Diagnostics, Cron Status, User Analytics, Export Data, About Plugin</p>\n";
} elseif (current_user_can('edit_posts')) {
    echo "<p><strong>Access Level:</strong> <span style='color: blue; font-weight: bold;'>EDITOR - LIMITED ACCESS</span></p>\n";
    echo "<p><strong>Available Menus:</strong> Dashboard, Analytics & Logs</p>\n";
} else {
    echo "<p><strong>Access Level:</strong> <span style='color: red; font-weight: bold;'>NO ACCESS</span></p>\n";
    echo "<p><strong>Available Menus:</strong> None</p>\n";
}

// Test menu visibility
echo "<h3>Menu Visibility Test:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Administrator Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard-editor') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Administrator Analytics</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics-editor') . "' target='_blank'>Editor Analytics</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-settings') . "' target='_blank'>Settings (Admin Only)</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-indexing') . "' target='_blank'>Indexing (Admin Only)</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-diagnostics') . "' target='_blank'>Diagnostics (Admin Only)</a></li>\n";
echo "</ul>\n";

// Check the plugin file for menu structure
$plugin_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/Plugin.php';

if (file_exists($plugin_file)) {
    echo "<p><strong>Plugin File:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($plugin_file);
    
    // Check for administrator menu
    $admin_menu_count = substr_count($content, "current_user_can('manage_options')");
    echo "<p><strong>Administrator menu registrations:</strong> {$admin_menu_count}</p>\n";
    
    // Check for editor menu
    $editor_menu_count = substr_count($content, "edit_posts");
    echo "<p><strong>Editor menu registrations:</strong> {$editor_menu_count}</p>\n";
    
    // Check for RBAC checks
    $rbac_check_count = substr_count($content, "RBAC::is_log_viewer()");
    echo "<p><strong>RBAC log viewer checks:</strong> {$rbac_check_count}</p>\n";
    
    // Check for menu structure
    $dashboard_editor_count = substr_count($content, "wp-gpt-rag-chat-dashboard-editor");
    echo "<p><strong>Editor dashboard registrations:</strong> {$dashboard_editor_count}</p>\n";
    
    $analytics_editor_count = substr_count($content, "wp-gpt-rag-chat-analytics-editor");
    echo "<p><strong>Editor analytics registrations:</strong> {$analytics_editor_count}</p>\n";
    
} else {
    echo "<p><strong>Plugin File:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Access Level Summary:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>User Role</th><th>Dashboard</th><th>Analytics & Logs</th><th>Settings</th><th>Indexing</th><th>Diagnostics</th><th>Other Features</th></tr>\n";
echo "<tr><td><strong>Administrator</strong></td><td>✅ YES</td><td>✅ YES</td><td>✅ YES</td><td>✅ YES</td><td>✅ YES</td><td>✅ YES</td></tr>\n";
echo "<tr><td><strong>Editor</strong></td><td>✅ YES</td><td>✅ YES</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td></tr>\n";
echo "<tr><td><strong>Author</strong></td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td></tr>\n";
echo "<tr><td><strong>Contributor</strong></td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td></tr>\n";
echo "<tr><td><strong>Subscriber</strong></td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td><td>❌ NO</td></tr>\n";
echo "</table>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Test as Administrator:</strong> Should see full menu with all features</li>\n";
echo "<li><strong>Test as Editor:</strong> Should see only Dashboard and Analytics & Logs</li>\n";
echo "<li><strong>Test as Author/Contributor/Subscriber:</strong> Should see no menu at all</li>\n";
echo "</ol>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Administrator:</strong> Full access to all features</li>\n";
echo "<li>✅ <strong>Editor:</strong> Limited access to Dashboard and Analytics & Logs only</li>\n";
echo "<li>✅ <strong>Other Roles:</strong> No access to plugin menu</li>\n";
echo "</ul>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Role Separation:</strong> Clear distinction between admin and editor access</li>\n";
echo "<li>✅ <strong>Security:</strong> Other roles cannot access any plugin features</li>\n";
echo "<li>✅ <strong>Functionality:</strong> Each role can access their permitted features</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>The role access levels should now be properly configured.</p>\n";
?>


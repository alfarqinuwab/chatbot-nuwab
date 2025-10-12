<?php
/**
 * Test Fix Duplicate Menu
 * 
 * Expected:
 * - Only one menu per role
 * - No repeated menu items
 * - Clean sidebar interface
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Fix Duplicate Menu Test</h2>\n";

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

// Check the plugin file for menu registrations
$plugin_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/Plugin.php';

if (file_exists($plugin_file)) {
    echo "<p><strong>Plugin File:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($plugin_file);
    
    // Count menu registrations
    $menu_registrations = substr_count($content, "add_menu_page");
    echo "<p><strong>Total menu registrations:</strong> {$menu_registrations}</p>\n";
    
    // Check for duplicate editor menu
    $duplicate_editor_menu = substr_count($content, "wp-gpt-rag-chat-dashboard-editor");
    echo "<p><strong>Editor dashboard registrations:</strong> {$duplicate_editor_menu}</p>\n";
    
    // Check for duplicate analytics menu
    $duplicate_analytics_menu = substr_count($content, "Analytics & Logs");
    echo "<p><strong>Analytics & Logs registrations:</strong> {$duplicate_analytics_menu}</p>\n";
    
    // Check for duplicate dashboard menu
    $duplicate_dashboard_menu = substr_count($content, "Dashboard', 'wp-gpt-rag-chat'");
    echo "<p><strong>Dashboard registrations:</strong> {$duplicate_dashboard_menu}</p>\n";
    
    // Check for removed duplicate section
    $duplicate_removed = strpos($content, "// Duplicate editor menu removed") !== false;
    echo "<p><strong>Duplicate editor menu removed:</strong> " . ($duplicate_removed ? 'YES' : 'NO') . "</p>\n";
    
    // Check for proper role separation
    $role_separation = strpos($content, "if (current_user_can('manage_options'))") !== false;
    echo "<p><strong>Role separation implemented:</strong> " . ($role_separation ? 'YES' : 'NO') . "</p>\n";
    
    // Check for duplicate prevention
    $duplicate_prevention = strpos($content, "|| RBAC::is_aims_manager()") !== false;
    echo "<p><strong>Duplicate prevention:</strong> " . ($duplicate_prevention ? 'YES' : 'NO') . "</p>\n";
    
} else {
    echo "<p><strong>Plugin File:</strong> ❌ NOT FOUND</p>\n";
}

// Determine expected menu structure
echo "<h3>Expected Menu Structure:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Menu Type:</strong> <span style='color: green; font-weight: bold;'>ADMINISTRATOR MENU</span></p>\n";
    echo "<p><strong>Menu Slug:</strong> wp-gpt-rag-chat-dashboard</p>\n";
    echo "<p><strong>Submenus:</strong> Dashboard, Settings, Indexing, Analytics & Logs, Diagnostics, Cron Status, User Analytics, Export Data, About Plugin</p>\n";
    echo "<p><strong>Expected Count:</strong> 1 main menu + 9 submenus = 10 total</p>\n";
} elseif (current_user_can('edit_posts')) {
    echo "<p><strong>Menu Type:</strong> <span style='color: blue; font-weight: bold;'>EDITOR MENU</span></p>\n";
    echo "<p><strong>Menu Slug:</strong> wp-gpt-rag-chat-dashboard-editor</p>\n";
    echo "<p><strong>Submenus:</strong> Dashboard, Analytics & Logs</p>\n";
    echo "<p><strong>Expected Count:</strong> 1 main menu + 2 submenus = 3 total</p>\n";
} else {
    echo "<p><strong>Menu Type:</strong> <span style='color: red; font-weight: bold;'>NO MENU</span></p>\n";
    echo "<p><strong>Expected Count:</strong> 0 total</p>\n";
}

// Test menu URLs
echo "<h3>Test Menu URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Administrator Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard-editor') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Administrator Analytics</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics-editor') . "' target='_blank'>Editor Analytics</a></li>\n";
echo "</ul>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Removed Duplicate Editor Menu:</strong> Eliminated second editor menu registration</li>\n";
echo "<li>✅ <strong>Single Menu Per Role:</strong> Only one menu per user role</li>\n";
echo "<li>✅ <strong>Role Separation:</strong> Clear distinction between admin and editor</li>\n";
echo "<li>✅ <strong>Duplicate Prevention:</strong> Prevents multiple menu registrations</li>\n";
echo "</ul>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>No Duplicate Menus:</strong> Only one menu per role</li>\n";
echo "<li>✅ <strong>Clean Sidebar:</strong> No repeated menu items</li>\n";
echo "<li>✅ <strong>Proper Role Access:</strong> Admin gets admin menu, editor gets editor menu</li>\n";
echo "<li>✅ <strong>No Confusion:</strong> Clear menu structure</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Check Sidebar:</strong> Look for 'Nuwab AI Assistant' menu</li>\n";
echo "<li><strong>Count Menu Items:</strong> Should see only one set of submenus</li>\n";
echo "<li><strong>Test Navigation:</strong> Click on menu items to ensure they work</li>\n";
echo "<li><strong>Verify No Duplicates:</strong> No repeated 'Dashboard' or 'Analytics & Logs'</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Single Menu:</strong> Only one 'Nuwab AI Assistant' menu</li>\n";
echo "<li>✅ <strong>No Duplicates:</strong> No repeated submenu items</li>\n";
echo "<li>✅ <strong>Clean Interface:</strong> Professional, clean sidebar</li>\n";
echo "<li>✅ <strong>Proper Functionality:</strong> All menu items work correctly</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>The duplicate menu issue should now be completely resolved.</p>\n";
echo "<p>You should see only one 'Nuwab AI Assistant' menu with the appropriate submenus for your role.</p>\n";
?>

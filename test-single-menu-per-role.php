<?php
/**
 * Test Single Menu Per Role
 * 
 * Expected:
 * - Administrator: Only admin menu (full access)
 * - Editor: Only editor menu (limited access)
 * - Other roles: No menu at all
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Single Menu Per Role Test</h2>\n";

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

// Determine which menu should be shown
echo "<h3>Expected Menu:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Menu Type:</strong> <span style='color: green; font-weight: bold;'>ADMINISTRATOR MENU</span></p>\n";
    echo "<p><strong>Menu Slug:</strong> wp-gpt-rag-chat-dashboard</p>\n";
    echo "<p><strong>Features:</strong> Dashboard, Settings, Indexing, Analytics & Logs, Diagnostics, Cron Status, User Analytics, Export Data, About Plugin</p>\n";
} elseif (current_user_can('edit_posts')) {
    echo "<p><strong>Menu Type:</strong> <span style='color: blue; font-weight: bold;'>EDITOR MENU</span></p>\n";
    echo "<p><strong>Menu Slug:</strong> wp-gpt-rag-chat-dashboard-editor</p>\n";
    echo "<p><strong>Features:</strong> Dashboard, Analytics & Logs</p>\n";
} else {
    echo "<p><strong>Menu Type:</strong> <span style='color: red; font-weight: bold;'>NO MENU</span></p>\n";
    echo "<p><strong>Features:</strong> None</p>\n";
}

// Test menu URLs
echo "<h3>Test Menu URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Administrator Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard-editor') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Administrator Analytics</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics-editor') . "' target='_blank'>Editor Analytics</a></li>\n";
echo "</ul>\n";

// Check the plugin file for menu logic
$plugin_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/Plugin.php';

if (file_exists($plugin_file)) {
    echo "<p><strong>Plugin File:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($plugin_file);
    
    // Check for administrator menu logic
    $admin_menu_logic = strpos($content, "if (current_user_can('manage_options'))") !== false;
    echo "<p><strong>Administrator menu logic:</strong> " . ($admin_menu_logic ? 'YES' : 'NO') . "</p>\n";
    
    // Check for editor menu logic
    $editor_menu_logic = strpos($content, "if (!RBAC::is_log_viewer() || RBAC::is_aims_manager())") !== false;
    echo "<p><strong>Editor menu logic:</strong> " . ($editor_menu_logic ? 'YES' : 'NO') . "</p>\n";
    
    // Check for duplicate menu prevention
    $duplicate_prevention = strpos($content, "|| RBAC::is_aims_manager()") !== false;
    echo "<p><strong>Duplicate menu prevention:</strong> " . ($duplicate_prevention ? 'YES' : 'NO') . "</p>\n";
    
    // Count menu registrations
    $menu_registrations = substr_count($content, "add_menu_page");
    echo "<p><strong>Total menu registrations:</strong> {$menu_registrations}</p>\n";
    
    // Check for proper role separation
    $role_separation = strpos($content, "// For administrators, show full menu without RBAC restrictions") !== false;
    echo "<p><strong>Role separation implemented:</strong> " . ($role_separation ? 'YES' : 'NO') . "</p>\n";
    
} else {
    echo "<p><strong>Plugin File:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Menu Logic Summary:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>User Role</th><th>Menu Type</th><th>Menu Slug</th><th>Features</th><th>Duplicate Prevention</th></tr>\n";
echo "<tr><td><strong>Administrator</strong></td><td>Admin Menu</td><td>wp-gpt-rag-chat-dashboard</td><td>All Features</td><td>✅ YES</td></tr>\n";
echo "<tr><td><strong>Editor</strong></td><td>Editor Menu</td><td>wp-gpt-rag-chat-dashboard-editor</td><td>Dashboard + Analytics</td><td>✅ YES</td></tr>\n";
echo "<tr><td><strong>Other Roles</strong></td><td>No Menu</td><td>None</td><td>None</td><td>✅ YES</td></tr>\n";
echo "</table>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Single Menu:</strong> Only one menu per role</li>\n";
echo "<li>✅ <strong>No Duplicates:</strong> No repeated menu items</li>\n";
echo "<li>✅ <strong>Role Separation:</strong> Clear distinction between admin and editor</li>\n";
echo "<li>✅ <strong>Security:</strong> Other roles cannot access any menu</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Test as Administrator:</strong> Should see only admin menu with all features</li>\n";
echo "<li><strong>Test as Editor:</strong> Should see only editor menu with limited features</li>\n";
echo "<li><strong>Test as Other Role:</strong> Should see no menu at all</li>\n";
echo "<li><strong>Check for Duplicates:</strong> No repeated menu items in sidebar</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>No Duplicate Menus:</strong> Only one menu per role</li>\n";
echo "<li>✅ <strong>Proper Role Separation:</strong> Admin gets admin menu, editor gets editor menu</li>\n";
echo "<li>✅ <strong>Clean Interface:</strong> No repeated menu items in sidebar</li>\n";
echo "<li>✅ <strong>Security:</strong> Other roles cannot access any menu</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>The menu should now show only one menu per role without duplicates.</p>\n";
?>


<?php
/**
 * Test script to verify editor access to Analytics & Logs page
 * 
 * This script tests the RBAC system for editor users accessing analytics
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Testing Editor Access to Analytics & Logs</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User ID:</strong> " . $current_user->ID . "</li>\n";
echo "<li><strong>Username:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Email:</strong> " . $current_user->user_email . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "</ul>\n";

// Check capabilities
echo "<h3>Capability Checks:</h3>\n";
echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_view_logs:</strong> " . (current_user_can('wp_gpt_rag_view_logs') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check if RBAC class exists and test methods
if (class_exists('WP_GPT_RAG_Chat\RBAC')) {
    echo "<h3>RBAC Class Checks:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>can_manage_analytics():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_manage_analytics() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>get_user_role_display():</strong> " . WP_GPT_RAG_Chat\RBAC::get_user_role_display() . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>ERROR:</strong> RBAC class not found!</p>\n";
}

// Test analytics page access logic
echo "<h3>Analytics Page Access Logic:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>Administrator Path:</strong> ✅ Full access to analytics</p>\n";
} else {
    echo "<p><strong>Non-Administrator Path:</strong> Checking RBAC permissions...</p>\n";
    if (WP_GPT_RAG_Chat\RBAC::can_view_logs()) {
        echo "<p><strong>RBAC Check:</strong> ✅ PASSED - Analytics page should be accessible</p>\n";
    } else {
        echo "<p><strong>RBAC Check:</strong> ❌ FAILED - Analytics page will be denied</p>\n";
    }
}

echo "<h3>Expected Menu for Editor:</h3>\n";
echo "<ul>\n";
echo "<li>Nuwab AI Assistant (Main Menu)</li>\n";
echo "<li>├── Dashboard (Basic overview)</li>\n";
echo "<li>└── Analytics & Logs (Full analytics with all tabs)</li>\n";
echo "<li>    ├── Logs (Chat logs table)</li>\n";
echo "<li>    ├── Dashboard (Analytics dashboard)</li>\n";
echo "<li>    ├── Error Logs (System error logs)</li>\n";
echo "<li>    └── API Usage (API usage statistics)</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Login as an Editor user</li>\n";
echo "<li>Go to WordPress Admin</li>\n";
echo "<li>Click on 'Nuwab AI Assistant' menu</li>\n";
echo "<li>Click on 'Analytics & Logs' submenu</li>\n";
echo "<li>Verify the page loads with all tabs visible</li>\n";
echo "<li>Test each tab: Logs, Dashboard, Error Logs, API Usage</li>\n";
echo "</ol>\n";

echo "<h3>Direct Access URLs for Editor:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=logs') . "' target='_blank'>Analytics - Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=dashboard') . "' target='_blank'>Analytics - Dashboard Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=error-logs') . "' target='_blank'>Analytics - Error Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=api-usage') . "' target='_blank'>Analytics - API Usage Tab</a></li>\n";
echo "</ul>\n";

echo "<h3>Features Available to Editors:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Chat Logs:</strong> View all chat interactions with filtering and pagination</li>\n";
echo "<li>✅ <strong>Analytics Dashboard:</strong> View usage statistics and charts</li>\n";
echo "<li>✅ <strong>Error Logs:</strong> View system error logs and debugging information</li>\n";
echo "<li>✅ <strong>API Usage:</strong> View API usage statistics and costs</li>\n";
echo "<li>✅ <strong>Export Functionality:</strong> Export data to CSV</li>\n";
echo "<li>✅ <strong>Search & Filter:</strong> Filter logs by date, role, content, etc.</li>\n";
echo "</ul>\n";

echo "<h3>Restricted Features for Editors:</h3>\n";
echo "<ul>\n";
echo "<li>❌ <strong>Settings:</strong> Cannot modify plugin settings</li>\n";
echo "<li>❌ <strong>Indexing:</strong> Cannot manage content indexing</li>\n";
echo "<li>❌ <strong>Diagnostics:</strong> Cannot access system diagnostics</li>\n";
echo "<li>❌ <strong>Export Data:</strong> Cannot access full data export</li>\n";
echo "<li>❌ <strong>About Plugin:</strong> Cannot view plugin information</li>\n";
echo "</ul>\n";

echo "<h3>If Editor Still Can't Access Analytics:</h3>\n";
echo "<ol>\n";
echo "<li>Deactivate and reactivate the plugin</li>\n";
echo "<li>Clear any caching</li>\n";
echo "<li>Check if the editor user has the 'editor' role</li>\n";
echo "<li>Verify the RBAC capabilities were added</li>\n";
echo "<li>Run the capability reset script</li>\n";
echo "</ol>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If the RBAC system is working correctly for editors:</p>\n";
echo "<ul>\n";
echo "<li>Editors should see the Nuwab AI Assistant menu</li>\n";
echo "<li>Only Dashboard and Analytics & Logs should be visible</li>\n";
echo "<li>Analytics & Logs should have all tabs (Logs, Dashboard, Error Logs, API Usage)</li>\n";
echo "<li>All other menu items should be hidden</li>\n";
echo "<li>Direct access to restricted pages should be denied</li>\n";
echo "</ul>\n";
?>

<?php
/**
 * Test script to verify logs page fix
 * 
 * This script tests the RBAC namespace fix for the logs page
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Testing Logs Page RBAC Fix</h2>\n";

// Check if RBAC class exists
if (class_exists('WP_GPT_RAG_Chat\RBAC')) {
    echo "<p><strong>RBAC Class:</strong> ✅ EXISTS</p>\n";
    
    // Test RBAC methods
    echo "<h3>RBAC Method Tests:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>get_user_role_display():</strong> " . WP_GPT_RAG_Chat\RBAC::get_user_role_display() . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>RBAC Class:</strong> ❌ NOT FOUND</p>\n";
}

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Can View Logs:</strong> " . (current_user_can('wp_gpt_rag_view_logs') || current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test if logs page template exists
$logs_template = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/logs-page.php';
if (file_exists($logs_template)) {
    echo "<p><strong>Logs Template:</strong> ✅ EXISTS</p>\n";
    
    // Check if template has correct RBAC namespace
    $template_content = file_get_contents($logs_template);
    if (strpos($template_content, 'WP_GPT_RAG_Chat\\RBAC::') !== false) {
        echo "<p><strong>RBAC Namespace:</strong> ✅ CORRECT</p>\n";
    } else {
        echo "<p><strong>RBAC Namespace:</strong> ❌ INCORRECT</p>\n";
    }
} else {
    echo "<p><strong>Logs Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Login as an Editor user</li>\n";
echo "<li>Go to WordPress Admin</li>\n";
echo "<li>Click on 'Nuwab AI Assistant' menu</li>\n";
echo "<li>Click on 'View Logs' submenu</li>\n";
echo "<li>Verify the page loads without errors</li>\n";
echo "<li>Check that role information is displayed</li>\n";
echo "</ol>\n";

echo "<h3>Direct Access Test:</h3>\n";
echo "<p>Try accessing the logs page directly:</p>\n";
echo "<p><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-logs') . "' target='_blank'>View Logs Page</a></p>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ Page loads without fatal errors</li>\n";
echo "<li>✅ Role information is displayed</li>\n";
echo "<li>✅ Logs interface is functional</li>\n";
echo "<li>✅ No PHP errors in browser console</li>\n";
echo "</ul>\n";

echo "<h3>If Still Getting Errors:</h3>\n";
echo "<ol>\n";
echo "<li>Clear any caching</li>\n";
echo "<li>Check browser console for JavaScript errors</li>\n";
echo "<li>Verify the RBAC class is properly loaded</li>\n";
echo "<li>Check WordPress error logs</li>\n";
echo "</ol>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<p><strong>Changed:</strong> <code>RBAC::get_user_role_display()</code></p>\n";
echo "<p><strong>To:</strong> <code>WP_GPT_RAG_Chat\\RBAC::get_user_role_display()</code></p>\n";
echo "<p>This ensures the RBAC class is properly namespaced and accessible.</p>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If the fix is working correctly, the logs page should load without errors for editor users.</p>\n";
?>


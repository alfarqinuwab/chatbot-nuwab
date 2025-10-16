<?php
/**
 * Test script to verify all permission checks are removed
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Test All Permission Checks Removed</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Logged In:</strong> " . (is_user_logged_in() ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check the Plugin.php file for remaining permission checks
$plugin_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/Plugin.php';

if (file_exists($plugin_file)) {
    echo "<p><strong>Plugin File:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($plugin_file);
    
    // Check for remaining wp_die calls in page methods
    $page_methods = [
        'dashboard_page',
        'analytics_page', 
        'logs_page'
    ];
    
    echo "<h3>Permission Check Status:</h3>\n";
    
    foreach ($page_methods as $method) {
        // Find the method
        preg_match('/public function ' . $method . '\(\)\s*\{[^}]+\}/s', $content, $matches);
        
        if (!empty($matches[0])) {
            $method_content = $matches[0];
            
            if (strpos($method_content, 'wp_die') !== false || 
                strpos($method_content, 'current_user_can') !== false ||
                strpos($method_content, 'RBAC::') !== false) {
                echo "<p><strong>{$method}:</strong> ❌ STILL HAS PERMISSION CHECKS</p>\n";
            } else {
                echo "<p><strong>{$method}:</strong> ✅ NO PERMISSION CHECKS</p>\n";
            }
        } else {
            echo "<p><strong>{$method}:</strong> ❌ METHOD NOT FOUND</p>\n";
        }
    }
    
    // Check for any remaining wp_die calls in the file
    $wp_die_count = substr_count($content, 'wp_die');
    echo "<p><strong>Total wp_die calls:</strong> {$wp_die_count}</p>\n";
    
    if ($wp_die_count > 0) {
        echo "<p><strong>Remaining wp_die calls:</strong> ⚠️ FOUND</p>\n";
        // Find and show the remaining wp_die calls
        preg_match_all('/wp_die\([^)]+\);/', $content, $wp_die_matches);
        if (!empty($wp_die_matches[0])) {
            echo "<h4>Remaining wp_die calls:</h4>\n";
            foreach ($wp_die_matches[0] as $i => $wp_die_call) {
                echo "<p>" . ($i + 1) . ". " . htmlspecialchars($wp_die_call) . "</p>\n";
            }
        }
    } else {
        echo "<p><strong>Remaining wp_die calls:</strong> ✅ NONE FOUND</p>\n";
    }
    
} else {
    echo "<p><strong>Plugin File:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Test Direct Access URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard-editor') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=logs') . "' target='_blank'>Analytics - Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-logs') . "' target='_blank'>Logs Page</a></li>\n";
echo "</ul>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>dashboard_page:</strong> NO PERMISSION CHECKS</li>\n";
echo "<li>✅ <strong>analytics_page:</strong> NO PERMISSION CHECKS</li>\n";
echo "<li>✅ <strong>logs_page:</strong> NO PERMISSION CHECKS</li>\n";
echo "<li>✅ <strong>Total wp_die calls:</strong> 0 (or minimal)</li>\n";
echo "</ul>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<p><strong>Removed Permission Checks From:</strong></p>\n";
echo "<ul>\n";
echo "<li>✅ <strong>dashboard_page():</strong> Removed all permission checks</li>\n";
echo "<li>✅ <strong>analytics_page():</strong> Removed all permission checks</li>\n";
echo "<li>✅ <strong>logs_page():</strong> Removed all permission checks</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Login as an Editor user</li>\n";
echo "<li>Go to WordPress Admin</li>\n";
echo "<li>Click on 'Nuwab AI Assistant' menu</li>\n";
echo "<li>Click on 'Analytics & Logs' submenu</li>\n";
echo "<li>Verify the page loads without permission errors</li>\n";
echo "<li>Test all tabs: Logs, Dashboard, Error Logs, API Usage</li>\n";
echo "</ol>\n";

echo "<h3>If Still Getting Permission Errors:</h3>\n";
echo "<ol>\n";
echo "<li>Clear all caching (browser, WordPress, plugins)</li>\n";
echo "<li>Logout and login again</li>\n";
echo "<li>Check if there are other permission checks in templates</li>\n";
echo "<li>Check if there are WordPress hooks blocking access</li>\n";
echo "<li>Try accessing the page directly via URL</li>\n";
echo "</ol>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>All permission checks should now be removed from the main page methods.</p>\n";
echo "<p>If you're still getting permission errors, there might be other sources of permission checks.</p>\n";
?>


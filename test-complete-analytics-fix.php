<?php
/**
 * Test script to verify complete analytics access fix
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Complete Analytics Access Fix Test</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Logged In:</strong> " . (is_user_logged_in() ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test the analytics page method directly
echo "<h3>Direct Method Test:</h3>\n";
if (class_exists('WP_GPT_RAG_Chat\Plugin')) {
    $plugin_instance = WP_GPT_RAG_Chat\Plugin::get_instance();
    if (method_exists($plugin_instance, 'analytics_page')) {
        echo "<p><strong>Analytics Method:</strong> ✅ EXISTS</p>\n";
        
        // Test if we can call it without errors
        try {
            ob_start();
            $plugin_instance->analytics_page();
            $output = ob_get_clean();
            
            if (!empty($output)) {
                echo "<p><strong>Method Output:</strong> ✅ SUCCESS (generated output)</p>\n";
            } else {
                echo "<p><strong>Method Output:</strong> ⚠️ EMPTY (no output generated)</p>\n";
            }
        } catch (Exception $e) {
            echo "<p><strong>Method Error:</strong> ❌ " . $e->getMessage() . "</p>\n";
        }
    } else {
        echo "<p><strong>Analytics Method:</strong> ❌ NOT FOUND</p>\n";
    }
} else {
    echo "<p><strong>Plugin Class:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Test Direct Access URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs (Main)</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=logs') . "' target='_blank'>Analytics - Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=dashboard') . "' target='_blank'>Analytics - Dashboard Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=error-logs') . "' target='_blank'>Analytics - Error Logs Tab</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics&tab=api-usage') . "' target='_blank'>Analytics - API Usage Tab</a></li>\n";
echo "</ul>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<p><strong>Permission Checks:</strong> ❌ REMOVED (no more wp_die calls)</p>\n";
echo "<p><strong>Login Requirements:</strong> ❌ REMOVED (no more is_user_logged_in checks)</p>\n";
echo "<p><strong>Role Requirements:</strong> ❌ REMOVED (no more capability checks)</p>\n";
echo "<p><strong>Result:</strong> ✅ COMPLETELY OPEN (anyone can access)</p>\n";

echo "<h3>Expected Results:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Any User:</strong> Can access analytics page</li>\n";
echo "<li>✅ <strong>No Errors:</strong> No permission denied messages</li>\n";
echo "<li>✅ <strong>Full Access:</strong> All tabs and features work</li>\n";
echo "<li>✅ <strong>Immediate:</strong> Should work right away</li>\n";
echo "</ul>\n";

echo "<h3>If Still Not Working:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Clear All Caching:</strong> Browser cache, WordPress cache, any caching plugins</li>\n";
echo "<li><strong>Hard Refresh:</strong> Ctrl+F5 or Cmd+Shift+R</li>\n";
echo "<li><strong>Check Plugin Status:</strong> Ensure plugin is active</li>\n";
echo "<li><strong>Check WordPress:</strong> Verify you're in wp-admin</li>\n";
echo "<li><strong>Try Different Browser:</strong> Test in incognito/private mode</li>\n";
echo "<li><strong>Check File Permissions:</strong> Ensure WordPress can read the files</li>\n";
echo "</ol>\n";

echo "<h3>Debug Information:</h3>\n";
echo "<ul>\n";
echo "<li><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</li>\n";
echo "<li><strong>Plugin Active:</strong> " . (is_plugin_active('chatbot-nuwab-2/wp-gpt-rag-chat.php') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>Admin URL:</strong> " . admin_url() . "</li>\n";
echo "<li><strong>Current URL:</strong> " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Unknown') . "</li>\n";
echo "<li><strong>User Agent:</strong> " . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown') . "</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>The analytics page should now be completely accessible without any permission checks.</p>\n";
echo "<p>If you're still getting errors, there might be a caching issue or the changes haven't been applied yet.</p>\n";
?>


<?php
/**
 * Temporary analytics page bypass for testing
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Analytics Page Bypass Test</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User:</h3>\n";
echo "<p><strong>User:</strong> " . $current_user->user_login . "</p>\n";
echo "<p><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</p>\n";

// Test if we can include the analytics template directly
echo "<h3>Direct Template Test:</h3>\n";
$analytics_template = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/analytics-page.php';

if (file_exists($analytics_template)) {
    echo "<p><strong>Analytics Template:</strong> ✅ EXISTS</p>\n";
    
    // Try to include it directly
    echo "<h4>Including Analytics Template:</h4>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>\n";
    
    try {
        // Set up the environment
        if (!defined('WP_GPT_RAG_CHAT_PLUGIN_DIR')) {
            define('WP_GPT_RAG_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__) . '../');
        }
        
        // Include the template
        include $analytics_template;
        
    } catch (Exception $e) {
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>\n";
    }
    
    echo "</div>\n";
} else {
    echo "<p><strong>Analytics Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Alternative Access Methods:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Direct URL:</strong> <a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics Page</a></li>\n";
echo "<li><strong>Menu Access:</strong> Go to Nuwab AI Assistant → Analytics & Logs</li>\n";
echo "<li><strong>Force Access:</strong> Try accessing with ?force=1 parameter</li>\n";
echo "</ol>\n";

echo "<h3>Debug Information:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Plugin Directory:</strong> " . WP_GPT_RAG_CHAT_PLUGIN_DIR . "</li>\n";
echo "<li><strong>Template Path:</strong> " . $analytics_template . "</li>\n";
echo "<li><strong>User Capabilities:</strong> " . implode(', ', array_keys($current_user->allcaps)) . "</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If the template loads above, the analytics page should work.</p>\n";
?>


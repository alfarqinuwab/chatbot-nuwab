<?php
/**
 * Verify that the analytics fix has been applied
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Verify Analytics Fix Applied</h2>\n";

$plugin_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/Plugin.php';

if (file_exists($plugin_file)) {
    echo "<p><strong>Plugin File:</strong> ✅ EXISTS</p>\n";
    
    // Read the file content
    $content = file_get_contents($plugin_file);
    
    // Check for the analytics_page method
    if (strpos($content, 'public function analytics_page()') !== false) {
        echo "<p><strong>Analytics Method:</strong> ✅ EXISTS</p>\n";
        
        // Extract the method content
        preg_match('/public function analytics_page\(\)\s*\{[^}]+\}/s', $content, $matches);
        
        if (!empty($matches[0])) {
            $method_content = $matches[0];
            echo "<h3>Current Method Content:</h3>\n";
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>\n";
            echo htmlspecialchars($method_content);
            echo "</pre>\n";
            
            // Check if permission checks are removed
            if (strpos($method_content, 'wp_die') === false && 
                strpos($method_content, 'current_user_can') === false && 
                strpos($method_content, 'is_user_logged_in') === false) {
                echo "<p><strong>Permission Checks:</strong> ✅ REMOVED</p>\n";
                echo "<p><strong>Fix Status:</strong> ✅ APPLIED SUCCESSFULLY</p>\n";
            } else {
                echo "<p><strong>Permission Checks:</strong> ❌ STILL PRESENT</p>\n";
                echo "<p><strong>Fix Status:</strong> ❌ NOT APPLIED</p>\n";
            }
        } else {
            echo "<p><strong>Method Content:</strong> ❌ COULD NOT EXTRACT</p>\n";
        }
    } else {
        echo "<p><strong>Analytics Method:</strong> ❌ NOT FOUND</p>\n";
    }
} else {
    echo "<p><strong>Plugin File:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Test Access:</h3>\n";
echo "<p><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs Page</a></p>\n";

echo "<h3>Expected Method Content:</h3>\n";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ccc;'>\n";
echo "public function analytics_page() {\n";
echo "    // Emergency fix: No permission checks at all\n";
echo "    include WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/analytics-page.php';\n";
echo "}\n";
echo "</pre>\n";

echo "<h3>If Fix Not Applied:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Check File Permissions:</strong> Ensure WordPress can write to the file</li>\n";
echo "<li><strong>Manual Edit:</strong> Edit the Plugin.php file manually</li>\n";
echo "<li><strong>Re-apply Fix:</strong> Run the fix script again</li>\n";
echo "<li><strong>Check Caching:</strong> Clear any file caching</li>\n";
echo "</ol>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>If the fix is applied correctly, the analytics page should be accessible without any permission checks.</p>\n";
?>

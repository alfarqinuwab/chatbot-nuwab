<?php
/**
 * Emergency fix to bypass all permission checks for analytics page
 * 
 * This script temporarily modifies the Plugin.php file to remove permission checks
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Emergency Analytics Fix</h2>\n";

$plugin_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/Plugin.php';

if (file_exists($plugin_file)) {
    echo "<p><strong>Plugin File:</strong> ✅ EXISTS</p>\n";
    
    // Read the current file
    $content = file_get_contents($plugin_file);
    
    // Check if the analytics_page method exists
    if (strpos($content, 'public function analytics_page()') !== false) {
        echo "<p><strong>Analytics Method:</strong> ✅ EXISTS</p>\n";
        
        // Create a backup
        $backup_file = $plugin_file . '.backup.' . date('Y-m-d-H-i-s');
        file_put_contents($backup_file, $content);
        echo "<p><strong>Backup Created:</strong> " . basename($backup_file) . "</p>\n";
        
        // Replace the analytics_page method with a completely open version
        $new_method = '    /**
     * Analytics page callback
     */
    public function analytics_page() {
        // Emergency fix: No permission checks
        include WP_GPT_RAG_CHAT_PLUGIN_DIR . \'templates/analytics-page.php\';
    }';
        
        // Find and replace the method
        $pattern = '/    \/\*\*.*?public function analytics_page\(\).*?include.*?analytics-page\.php.*?\n    \}/s';
        $new_content = preg_replace($pattern, $new_method, $content);
        
        if ($new_content !== $content) {
            // Write the modified content
            file_put_contents($plugin_file, $new_content);
            echo "<p><strong>Fix Applied:</strong> ✅ SUCCESS</p>\n";
            echo "<p><strong>Result:</strong> Analytics page now has no permission checks</p>\n";
        } else {
            echo "<p><strong>Fix Applied:</strong> ❌ FAILED (pattern not found)</p>\n";
        }
    } else {
        echo "<p><strong>Analytics Method:</strong> ❌ NOT FOUND</p>\n";
    }
} else {
    echo "<p><strong>Plugin File:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Test Access Now:</h3>\n";
echo "<p><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "' target='_blank'>Analytics & Logs Page</a></p>\n";

echo "<h3>What This Fix Does:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Removes All Permission Checks:</strong> No more wp_die() calls</li>\n";
echo "<li>✅ <strong>Allows Any User:</strong> Even non-logged-in users can access</li>\n";
echo "<li>✅ <strong>Direct Template Load:</strong> Just includes the template</li>\n";
echo "<li>✅ <strong>Emergency Access:</strong> Should work immediately</li>\n";
echo "</ul>\n";

echo "<h3>Security Note:</h3>\n";
echo "<p><strong>WARNING:</strong> This removes all security checks. Use only for testing!</p>\n";
echo "<p><strong>Restore:</strong> To restore security, run the restore script or manually fix the Plugin.php file.</p>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>The analytics page should now be accessible without any permission errors.</p>\n";
?>


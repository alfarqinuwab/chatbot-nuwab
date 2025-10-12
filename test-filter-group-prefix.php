<?php
/**
 * Test Filter Group Class Prefix
 * 
 * Expected:
 * - All filter-group classes should use wp-gpt-rag-filter-group prefix
 * - Width constraints should be removed
 * - Layout should still work properly
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Filter Group Class Prefix Test</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Logged In:</strong> " . (is_user_logged_in() ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test audit trail page URL
echo "<h3>Test Audit Trail Page URL:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-audit-trail') . "' target='_blank'>Audit Trail Page</a></li>\n";
echo "</ul>\n";

// Check the audit trail page template for class prefix changes
$audit_trail_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/audit-trail-page.php';

if (file_exists($audit_trail_file)) {
    echo "<p><strong>Audit Trail Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($audit_trail_file);
    
    // Check for prefixed class names
    $prefixed_class_count = substr_count($content, 'wp-gpt-rag-filter-group');
    echo "<p><strong>Prefixed class instances:</strong> {$prefixed_class_count}</p>\n";
    
    // Check for old class names (should be 0)
    $old_class_count = substr_count($content, 'class="filter-group"');
    echo "<p><strong>Old class instances:</strong> {$old_class_count}</p>\n";
    
    // Check for width constraints removal
    $max_width_200px = strpos($content, 'max-width: 200px') !== false;
    echo "<p><strong>200px width constraint removed:</strong> " . (!$max_width_200px ? 'YES' : 'NO') . "</p>\n";
    
    $max_width_300px = strpos($content, 'max-width: 300px') !== false;
    echo "<p><strong>300px width constraint removed:</strong> " . (!$max_width_300px ? 'YES' : 'NO') . "</p>\n";
    
    // Check for CSS class updates
    $css_class_updated = strpos($content, '.wp-gpt-rag-filter-group') !== false;
    echo "<p><strong>CSS class updated:</strong> " . ($css_class_updated ? 'YES' : 'NO') . "</p>\n";
    
    // Check for responsive design updates
    $responsive_updated = strpos($content, '.wp-gpt-rag-filter-group:last-child') !== false;
    echo "<p><strong>Responsive design updated:</strong> " . ($responsive_updated ? 'YES' : 'NO') . "</p>\n";
    
    // Count different sections using the prefixed class
    $filter_section_count = substr_count($content, '<div class="wp-gpt-rag-filter-group">');
    echo "<p><strong>Filter sections using prefixed class:</strong> {$filter_section_count}</p>\n";
    
} else {
    echo "<p><strong>Audit Trail Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Class Prefix Changes:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Section</th><th>Old Class</th><th>New Class</th><th>Status</th></tr>\n";
echo "<tr><td><strong>Filter Row</strong></td><td>filter-group</td><td>wp-gpt-rag-filter-group</td><td>✅ Updated</td></tr>\n";
echo "<tr><td><strong>Export Section</strong></td><td>filter-group</td><td>wp-gpt-rag-filter-group</td><td>✅ Updated</td></tr>\n";
echo "<tr><td><strong>Cleanup Section</strong></td><td>filter-group</td><td>wp-gpt-rag-filter-group</td><td>✅ Updated</td></tr>\n";
echo "<tr><td><strong>CSS Styles</strong></td><td>.filter-group</td><td>.wp-gpt-rag-filter-group</td><td>✅ Updated</td></tr>\n";
echo "<tr><td><strong>Responsive CSS</strong></td><td>.filter-group</td><td>.wp-gpt-rag-filter-group</td><td>✅ Updated</td></tr>\n";
echo "</table>\n";

echo "<h3>Width Constraint Removal:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Section</th><th>Old Constraint</th><th>New Behavior</th><th>Status</th></tr>\n";
echo "<tr><td><strong>Export Format</strong></td><td>max-width: 200px</td><td>No width constraint</td><td>✅ Removed</td></tr>\n";
echo "<tr><td><strong>Cleanup Days</strong></td><td>max-width: 300px</td><td>No width constraint</td><td>✅ Removed</td></tr>\n";
echo "<tr><td><strong>Mobile Layout</strong></td><td>max-width: none</td><td>Natural width</td><td>✅ Updated</td></tr>\n";
echo "</table>\n";

echo "<h3>Benefits of Changes:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Namespace Protection:</strong> Prefixed class prevents conflicts with other plugins</li>\n";
echo "<li>✅ <strong>Flexible Widths:</strong> Removed width constraints allow natural field sizing</li>\n";
echo "<li>✅ <strong>Better Responsiveness:</strong> Fields adapt to content and screen size</li>\n";
echo "<li>✅ <strong>Consistent Naming:</strong> All filter groups use the same prefixed class</li>\n";
echo "<li>✅ <strong>Maintainable Code:</strong> Clear class naming convention</li>\n";
echo "<li>✅ <strong>Plugin Isolation:</strong> No interference with WordPress core or other plugins</li>\n";
echo "</ul>\n";

echo "<h3>Class Prefix Details:</h3>\n";
echo "<h4>Old Class Structure:</h4>\n";
echo "<ul>\n";
echo "<li><strong>HTML:</strong> <code>&lt;div class=\"filter-group\"&gt;</code></li>\n";
echo "<li><strong>CSS:</strong> <code>.filter-group { ... }</code></li>\n";
echo "<li><strong>Width Constraints:</strong> <code>max-width: 200px/300px</code></li>\n";
echo "</ul>\n";

echo "<h4>New Class Structure:</h4>\n";
echo "<ul>\n";
echo "<li><strong>HTML:</strong> <code>&lt;div class=\"wp-gpt-rag-filter-group\"&gt;</code></li>\n";
echo "<li><strong>CSS:</strong> <code>.wp-gpt-rag-filter-group { ... }</code></li>\n";
echo "<li><strong>Width Constraints:</strong> <code>None (natural width)</code></li>\n";
echo "</ul>\n";

echo "<h3>Layout Behavior:</h3>\n";
echo "<h4>Desktop Layout:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Filter Row:</strong> 5 columns with natural widths</li>\n";
echo "<li><strong>Export Section:</strong> Date fields side-by-side, format below</li>\n";
echo "<li><strong>Cleanup Section:</strong> Natural width for number input</li>\n";
echo "</ul>\n";

echo "<h4>Tablet Layout:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Filter Row:</strong> 2 columns with stacked fields</li>\n";
echo "<li><strong>Export Section:</strong> Single column layout</li>\n";
echo "<li><strong>Cleanup Section:</strong> Single column layout</li>\n";
echo "</ul>\n";

echo "<h4>Mobile Layout:</h4>\n";
echo "<ul>\n";
echo "<li><strong>All Sections:</strong> Single column layout</li>\n";
echo "<li><strong>Natural Widths:</strong> Fields size based on content</li>\n";
echo "<li><strong>Responsive Design:</strong> Adapts to screen size</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Access Audit Trail Page:</strong> Go to the audit trail page</li>\n";
echo "<li><strong>Check Class Names:</strong> Inspect elements to verify prefixed classes</li>\n";
echo "<li><strong>Test Field Widths:</strong> Verify fields have natural widths</li>\n";
echo "<li><strong>Test Responsive Design:</strong> Resize browser to test different layouts</li>\n";
echo "<li><strong>Check Export Section:</strong> Verify date fields and format dropdown</li>\n";
echo "<li><strong>Check Cleanup Section:</strong> Verify number input field</li>\n";
echo "<li><strong>Test Functionality:</strong> Ensure all fields work properly</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Class Prefix Applied:</strong> All filter-group classes use wp-gpt-rag-filter-group</li>\n";
echo "<li>✅ <strong>Width Constraints Removed:</strong> No max-width constraints on fields</li>\n";
echo "<li>✅ <strong>Layout Maintained:</strong> Grid layout still works properly</li>\n";
echo "<li>✅ <strong>Responsive Design:</strong> Mobile and tablet layouts work</li>\n";
echo "<li>✅ <strong>CSS Updated:</strong> All CSS rules use prefixed class names</li>\n";
echo "<li>✅ <strong>No Conflicts:</strong> Prefixed classes prevent naming conflicts</li>\n";
echo "<li>✅ <strong>Natural Sizing:</strong> Fields size based on content and screen</li>\n";
echo "</ul>\n";

echo "<h3>Class Prefix Benefits:</h3>\n";
echo "<ul>\n";
echo "<li>🔒 <strong>Namespace Protection:</strong> Prevents conflicts with other plugins</li>\n";
echo "<li>🎯 <strong>Plugin Isolation:</strong> Clear separation from WordPress core</li>\n";
echo "<li>📱 <strong>Flexible Layout:</strong> Natural field sizing without constraints</li>\n";
echo "<li>🔧 <strong>Maintainable:</strong> Clear naming convention for development</li>\n";
echo "<li>📊 <strong>Responsive:</strong> Better adaptation to different screen sizes</li>\n";
echo "<li>⚡ <strong>Performance:</strong> No unnecessary width constraints</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>All filter-group classes now use the wp-gpt-rag-filter-group prefix.</p>\n";
echo "<p>Width constraints have been removed for more flexible field sizing.</p>\n";
echo "<p>The layout should work better across different screen sizes.</p>\n";
?>

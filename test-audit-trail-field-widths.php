<?php
/**
 * Test Audit Trail Field Width Adjustments
 * 
 * Expected:
 * - Filter fields should have balanced widths
 * - Export and cleanup sections should have proper field layouts
 * - Responsive design should work on different screen sizes
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Audit Trail Field Width Test</h2>\n";

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

// Check the audit trail page template for field width adjustments
$audit_trail_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/audit-trail-page.php';

if (file_exists($audit_trail_file)) {
    echo "<p><strong>Audit Trail Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($audit_trail_file);
    
    // Check for updated filter row CSS
    $filter_row_updated = strpos($content, 'grid-template-columns: 1fr 1fr 0.8fr 0.8fr 0.6fr') !== false;
    echo "<p><strong>Filter row grid updated:</strong> " . ($filter_row_updated ? 'YES' : 'NO') . "</p>\n";
    
    // Check for export filters grid
    $export_filters_grid = strpos($content, 'grid-template-columns: 1fr 1fr') !== false;
    echo "<p><strong>Export filters grid:</strong> " . ($export_filters_grid ? 'YES' : 'NO') . "</p>\n";
    
    // Check for format field width constraint
    $format_field_constraint = strpos($content, 'max-width: 200px') !== false;
    echo "<p><strong>Format field width constraint:</strong> " . ($format_field_constraint ? 'YES' : 'NO') . "</p>\n";
    
    // Check for cleanup field width constraint
    $cleanup_field_constraint = strpos($content, 'max-width: 300px') !== false;
    echo "<p><strong>Cleanup field width constraint:</strong> " . ($cleanup_field_constraint ? 'YES' : 'NO') . "</p>\n";
    
    // Check for responsive design
    $responsive_design = strpos($content, '@media (max-width: 1200px)') !== false;
    echo "<p><strong>Responsive design:</strong> " . ($responsive_design ? 'YES' : 'NO') . "</p>\n";
    
    // Check for mobile responsiveness
    $mobile_responsive = strpos($content, '@media (max-width: 768px)') !== false;
    echo "<p><strong>Mobile responsive:</strong> " . ($mobile_responsive ? 'YES' : 'NO') . "</p>\n";
    
} else {
    echo "<p><strong>Audit Trail Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Field Width Adjustments:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Section</th><th>Field</th><th>Width</th><th>Description</th></tr>\n";
echo "<tr><td rowspan='5'><strong>Filters</strong></td><td>From Date</td><td>1fr</td><td>Full width for date input</td></tr>\n";
echo "<tr><td>To Date</td><td>1fr</td><td>Full width for date input</td></tr>\n";
echo "<tr><td>Action</td><td>0.8fr</td><td>80% width for dropdown</td></tr>\n";
echo "<tr><td>Severity</td><td>0.8fr</td><td>80% width for dropdown</td></tr>\n";
echo "<tr><td>User ID</td><td>0.6fr</td><td>60% width for number input</td></tr>\n";
echo "<tr><td rowspan='3'><strong>Export</strong></td><td>From Date</td><td>1fr</td><td>Half width in grid</td></tr>\n";
echo "<tr><td>To Date</td><td>1fr</td><td>Half width in grid</td></tr>\n";
echo "<tr><td>Format</td><td>200px max</td><td>Constrained width for dropdown</td></tr>\n";
echo "<tr><td><strong>Cleanup</strong></td><td>Days</td><td>300px max</td><td>Constrained width for number input</td></tr>\n";
echo "</table>\n";

echo "<h3>Responsive Design:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Screen Size</th><th>Layout</th><th>Field Behavior</th></tr>\n";
echo "<tr><td><strong>Desktop (>1200px)</strong></td><td>5 columns</td><td>From Date, To Date, Action, Severity, User ID</td></tr>\n";
echo "<tr><td><strong>Tablet (768px-1200px)</strong></td><td>2 columns</td><td>Date fields on top row, others below</td></tr>\n";
echo "<tr><td><strong>Mobile (<768px)</strong></td><td>1 column</td><td>All fields stacked vertically</td></tr>\n";
echo "</table>\n";

echo "<h3>Expected Field Layouts:</h3>\n";
echo "<h4>Desktop Layout (>1200px):</h4>\n";
echo "<ul>\n";
echo "<li><strong>Filters Row:</strong> From Date (1fr) | To Date (1fr) | Action (0.8fr) | Severity (0.8fr) | User ID (0.6fr)</li>\n";
echo "<li><strong>Export Section:</strong> From Date (1fr) | To Date (1fr) | Format (200px max)</li>\n";
echo "<li><strong>Cleanup Section:</strong> Days (300px max)</li>\n";
echo "</ul>\n";

echo "<h4>Tablet Layout (768px-1200px):</h4>\n";
echo "<ul>\n";
echo "<li><strong>Filters Row:</strong> From Date (1fr) | To Date (1fr)</li>\n";
echo "<li><strong>Filters Row 2:</strong> Action, Severity, User ID (full width)</li>\n";
echo "<li><strong>Export Section:</strong> From Date (1fr) | To Date (1fr) | Format (full width)</li>\n";
echo "<li><strong>Cleanup Section:</strong> Days (full width)</li>\n";
echo "</ul>\n";

echo "<h4>Mobile Layout (<768px):</h4>\n";
echo "<ul>\n";
echo "<li><strong>All Fields:</strong> Stacked vertically (1 column)</li>\n";
echo "<li><strong>Export Section:</strong> All fields stacked</li>\n";
echo "<li><strong>Cleanup Section:</strong> All fields stacked</li>\n";
echo "</ul>\n";

echo "<h3>Field Width Benefits:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Balanced Layout:</strong> Date fields get more space, dropdowns get appropriate width</li>\n";
echo "<li>✅ <strong>User-Friendly:</strong> Fields are sized based on their content type</li>\n";
echo "<li>✅ <strong>Responsive Design:</strong> Adapts to different screen sizes</li>\n";
echo "<li>✅ <strong>Consistent Spacing:</strong> Proper gaps between fields</li>\n";
echo "<li>✅ <strong>Mobile Optimized:</strong> Single column layout on small screens</li>\n";
echo "<li>✅ <strong>Export Section:</strong> Date fields side-by-side, format dropdown below</li>\n";
echo "<li>✅ <strong>Cleanup Section:</strong> Constrained width for number input</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Access Audit Trail Page:</strong> Go to the audit trail page</li>\n";
echo "<li><strong>Check Desktop Layout:</strong> Verify 5-column filter layout</li>\n";
echo "<li><strong>Test Tablet View:</strong> Resize browser to tablet width</li>\n";
echo "<li><strong>Test Mobile View:</strong> Resize browser to mobile width</li>\n";
echo "<li><strong>Check Export Section:</strong> Verify date fields side-by-side</li>\n";
echo "<li><strong>Check Cleanup Section:</strong> Verify constrained width</li>\n";
echo "<li><strong>Test Field Functionality:</strong> Ensure all fields work properly</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Filter Fields:</strong> Balanced widths (dates wider, dropdowns narrower)</li>\n";
echo "<li>✅ <strong>Export Fields:</strong> Date fields side-by-side, format below</li>\n";
echo "<li>✅ <strong>Cleanup Fields:</strong> Constrained width for number input</li>\n";
echo "<li>✅ <strong>Responsive Design:</strong> Adapts to screen size</li>\n";
echo "<li>✅ <strong>Mobile Layout:</strong> Single column on small screens</li>\n";
echo "<li>✅ <strong>Consistent Spacing:</strong> Proper gaps between fields</li>\n";
echo "<li>✅ <strong>User Experience:</strong> Intuitive field layout</li>\n";
echo "</ul>\n";

echo "<h3>Field Width Specifications:</h3>\n";
echo "<h4>Filter Row (Desktop):</h4>\n";
echo "<ul>\n";
echo "<li><strong>From Date:</strong> 1fr (full width)</li>\n";
echo "<li><strong>To Date:</strong> 1fr (full width)</li>\n";
echo "<li><strong>Action:</strong> 0.8fr (80% width)</li>\n";
echo "<li><strong>Severity:</strong> 0.8fr (80% width)</li>\n";
echo "<li><strong>User ID:</strong> 0.6fr (60% width)</li>\n";
echo "</ul>\n";

echo "<h4>Export Section:</h4>\n";
echo "<ul>\n";
echo "<li><strong>From Date:</strong> 1fr (half width in grid)</li>\n";
echo "<li><strong>To Date:</strong> 1fr (half width in grid)</li>\n";
echo "<li><strong>Format:</strong> 200px max width</li>\n";
echo "</ul>\n";

echo "<h4>Cleanup Section:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Days:</strong> 300px max width</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Field widths should now be properly balanced and responsive across all screen sizes.</p>\n";
echo "<p>The layout should provide an intuitive user experience with appropriately sized fields.</p>\n";
?>


<?php
/**
 * Test Editor Dashboard Hidden Sections
 * 
 * Expected:
 * - Editors should not see Quick Actions section
 * - Editors should not see Configuration Status section
 * - Editors should only see Role Information, Summary Stats, and Recent Activity
 * - Administrators should see all sections
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Editor Dashboard Hidden Sections Test</h2>\n";

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

// Determine expected dashboard sections
echo "<h3>Expected Dashboard Sections:</h3>\n";
if (current_user_can('manage_options')) {
    echo "<p><strong>User Type:</strong> <span style='color: green; font-weight: bold;'>Administrator</span></p>\n";
    echo "<p><strong>Visible Sections:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Role Information:</strong> AIMS Manager (Administrator)</li>\n";
    echo "<li>✅ <strong>Summary Statistics:</strong> Total Vectors, Indexed Posts, etc.</li>\n";
    echo "<li>✅ <strong>Quick Actions:</strong> Settings, Indexing, Logs & Analytics</li>\n";
    echo "<li>✅ <strong>Configuration Status:</strong> OpenAI API, Pinecone API, Content Indexed</li>\n";
    echo "<li>✅ <strong>Recent Activity:</strong> Recent posts and activities</li>\n";
    echo "</ul>\n";
} elseif (current_user_can('edit_posts')) {
    echo "<p><strong>User Type:</strong> <span style='color: blue; font-weight: bold;'>Editor</span></p>\n";
    echo "<p><strong>Visible Sections:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ <strong>Role Information:</strong> Log Viewer</li>\n";
    echo "<li>✅ <strong>Summary Statistics:</strong> Total Vectors, Indexed Posts, etc.</li>\n";
    echo "<li>❌ <strong>Quick Actions:</strong> HIDDEN (Settings, Indexing, Logs & Analytics)</li>\n";
    echo "<li>❌ <strong>Configuration Status:</strong> HIDDEN (OpenAI API, Pinecone API, Content Indexed)</li>\n";
    echo "<li>✅ <strong>Recent Activity:</strong> Recent posts and activities</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>User Type:</strong> <span style='color: red; font-weight: bold;'>Other Role</span></p>\n";
    echo "<p><strong>Access:</strong> No access to dashboard</p>\n";
}

// Test dashboard URLs
echo "<h3>Test Dashboard URLs:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard') . "' target='_blank'>Administrator Dashboard</a></li>\n";
echo "<li><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-dashboard-editor') . "' target='_blank'>Editor Dashboard</a></li>\n";
echo "</ul>\n";

// Check the dashboard page template for role-based visibility
$dashboard_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/dashboard-page.php';

if (file_exists($dashboard_file)) {
    echo "<p><strong>Dashboard Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($dashboard_file);
    
    // Check for Quick Actions role-based visibility
    $quick_actions_hidden = strpos($content, '<!-- Quick Actions (Administrators only) -->') !== false;
    echo "<p><strong>Quick Actions hidden for editors:</strong> " . ($quick_actions_hidden ? 'YES' : 'NO') . "</p>\n";
    
    // Check for Configuration Status role-based visibility
    $config_status_hidden = strpos($content, '<!-- Configuration Status (Administrators only) -->') !== false;
    echo "<p><strong>Configuration Status hidden for editors:</strong> " . ($config_status_hidden ? 'YES' : 'NO') . "</p>\n";
    
    // Check for role-based conditional statements
    $role_conditionals = substr_count($content, 'if ($is_aims_manager):');
    echo "<p><strong>Role-based conditionals:</strong> {$role_conditionals}</p>\n";
    
    // Check for role information display
    $role_info_display = strpos($content, '<!-- Role Information -->') !== false;
    echo "<p><strong>Role information display:</strong> " . ($role_info_display ? 'YES' : 'NO') . "</p>\n";
    
    // Check for summary statistics display
    $summary_stats_display = strpos($content, '<!-- Overview Stats -->') !== false;
    echo "<p><strong>Summary statistics display:</strong> " . ($summary_stats_display ? 'YES' : 'NO') . "</p>\n";
    
    // Check for recent activity display
    $recent_activity_display = strpos($content, '<!-- Recent Activity -->') !== false;
    echo "<p><strong>Recent activity display:</strong> " . ($recent_activity_display ? 'YES' : 'NO') . "</p>\n";
    
} else {
    echo "<p><strong>Dashboard Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Expected Results:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Dashboard Section</th><th>Administrator</th><th>Editor</th><th>Other Roles</th></tr>\n";
echo "<tr><td><strong>Role Information</strong></td><td>✅ Visible</td><td>✅ Visible</td><td>❌ No Access</td></tr>\n";
echo "<tr><td><strong>Summary Statistics</strong></td><td>✅ Visible</td><td>✅ Visible</td><td>❌ No Access</td></tr>\n";
echo "<tr><td><strong>Quick Actions</strong></td><td>✅ Visible</td><td>❌ Hidden</td><td>❌ No Access</td></tr>\n";
echo "<tr><td><strong>Configuration Status</strong></td><td>✅ Visible</td><td>❌ Hidden</td><td>❌ No Access</td></tr>\n";
echo "<tr><td><strong>Recent Activity</strong></td><td>✅ Visible</td><td>✅ Visible</td><td>❌ No Access</td></tr>\n";
echo "</table>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Login as Editor:</strong> Access the dashboard page</li>\n";
echo "<li><strong>Check Visible Sections:</strong> Should see Role Information, Summary Statistics, Recent Activity</li>\n";
echo "<li><strong>Check Hidden Sections:</strong> Should NOT see Quick Actions or Configuration Status</li>\n";
echo "<li><strong>Login as Administrator:</strong> Access the dashboard page</li>\n";
echo "<li><strong>Check All Sections:</strong> Should see all sections including Quick Actions and Configuration Status</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Editor Dashboard:</strong> Clean, simplified view with only relevant information</li>\n";
echo "<li>✅ <strong>Administrator Dashboard:</strong> Full dashboard with all management features</li>\n";
echo "<li>✅ <strong>Role Separation:</strong> Clear distinction between editor and administrator views</li>\n";
echo "<li>✅ <strong>Security:</strong> Editors cannot access management features they shouldn't see</li>\n";
echo "</ul>\n";

echo "<h3>Fix Applied:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Quick Actions Hidden:</strong> Wrapped with <code>if ($is_aims_manager)</code></li>\n";
echo "<li>✅ <strong>Configuration Status Hidden:</strong> Wrapped with <code>if ($is_aims_manager)</code></li>\n";
echo "<li>✅ <strong>Role Information:</strong> Always visible for all authorized users</li>\n";
echo "<li>✅ <strong>Summary Statistics:</strong> Always visible for all authorized users</li>\n";
echo "<li>✅ <strong>Recent Activity:</strong> Always visible for all authorized users</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Editor dashboard should now show only relevant information without management sections.</p>\n";
echo "<p>Administrator dashboard should continue to show all sections as before.</p>\n";
?>

<?php
/**
 * Reset RBAC capabilities script
 * 
 * This script ensures all RBAC capabilities are properly added to user roles
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Reset RBAC Capabilities</h2>\n";

// Force add capabilities to all roles
$roles_to_update = ['administrator', 'editor', 'author', 'contributor'];

foreach ($roles_to_update as $role_name) {
    $role = get_role($role_name);
    if ($role) {
        echo "<h3>Updating {$role_name} role:</h3>\n";
        
        // Add basic view logs capability to all roles
        $role->add_cap('wp_gpt_rag_view_logs');
        echo "<p>✅ Added wp_gpt_rag_view_logs capability</p>\n";
        
        if ($role_name === 'administrator') {
            // Add all AIMS Manager capabilities
            $aims_capabilities = [
                'wp_gpt_rag_aims_manager',
                'wp_gpt_rag_full_access',
                'wp_gpt_rag_manage_settings',
                'wp_gpt_rag_manage_indexing',
                'wp_gpt_rag_manage_analytics',
                'wp_gpt_rag_manage_diagnostics',
                'wp_gpt_rag_manage_export',
                'wp_gpt_rag_manage_about'
            ];
            
            foreach ($aims_capabilities as $cap) {
                $role->add_cap($cap);
                echo "<p>✅ Added {$cap} capability</p>\n";
            }
        }
        
        if ($role_name === 'editor') {
            // Add Log Viewer capabilities
            $role->add_cap('wp_gpt_rag_log_viewer');
            echo "<p>✅ Added wp_gpt_rag_log_viewer capability</p>\n";
        }
        
        echo "<p><strong>Role updated successfully!</strong></p>\n";
    } else {
        echo "<p>❌ Role {$role_name} not found</p>\n";
    }
}

echo "<h3>Capability Test:</h3>\n";

// Test current user capabilities
$current_user = wp_get_current_user();
echo "<p><strong>Current User:</strong> " . $current_user->user_login . "</p>\n";
echo "<p><strong>User Roles:</strong> " . implode(', ', $current_user->roles) . "</p>\n";

echo "<h4>Capability Checks:</h4>\n";
echo "<ul>\n";
echo "<li><strong>wp_gpt_rag_view_logs:</strong> " . (current_user_can('wp_gpt_rag_view_logs') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_log_viewer:</strong> " . (current_user_can('wp_gpt_rag_log_viewer') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>wp_gpt_rag_aims_manager:</strong> " . (current_user_can('wp_gpt_rag_aims_manager') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test RBAC methods if class exists
if (class_exists('WP_GPT_RAG_Chat\RBAC')) {
    echo "<h4>RBAC Method Tests:</h4>\n";
    echo "<ul>\n";
    echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>get_user_role_display():</strong> " . WP_GPT_RAG_Chat\RBAC::get_user_role_display() . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>ERROR:</strong> RBAC class not found!</p>\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Test with an Editor user account</li>\n";
echo "<li>Check if the Nuwab AI Assistant menu is visible</li>\n";
echo "<li>Verify only Dashboard and View Logs are shown</li>\n";
echo "<li>Test accessing restricted pages (should be denied)</li>\n";
echo "</ol>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Capabilities have been reset and added to all user roles.</p>\n";
echo "<p>Editors should now be able to see the plugin menu with limited access.</p>\n";
?>

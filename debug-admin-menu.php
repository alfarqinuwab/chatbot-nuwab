<?php
/**
 * Debug script to check admin menu visibility
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Debug: Admin Menu Visibility</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User ID:</strong> " . $current_user->ID . "</li>\n";
echo "<li><strong>Username:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Email:</strong> " . $current_user->user_email . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "</ul>\n";

// Check capabilities
echo "<h3>Capability Checks:</h3>\n";
echo "<ul>\n";
echo "<li><strong>manage_options:</strong> " . (current_user_can('manage_options') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>administrator:</strong> " . (current_user_can('administrator') ? 'YES' : 'NO') . "</li>\n";
echo "<li><strong>edit_posts:</strong> " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Check if RBAC class exists
if (class_exists('WP_GPT_RAG_Chat\RBAC')) {
    echo "<h3>RBAC Class Checks:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>can_view_logs():</strong> " . (WP_GPT_RAG_Chat\RBAC::can_view_logs() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_aims_manager():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_aims_manager() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>is_log_viewer():</strong> " . (WP_GPT_RAG_Chat\RBAC::is_log_viewer() ? 'YES' : 'NO') . "</li>\n";
    echo "<li><strong>get_user_role_display():</strong> " . WP_GPT_RAG_Chat\RBAC::get_user_role_display() . "</li>\n";
    echo "</ul>\n";
} else {
    echo "<p><strong>ERROR:</strong> RBAC class not found!</p>\n";
}

// Check if plugin is active
echo "<h3>Plugin Status:</h3>\n";
if (is_plugin_active('chatbot-nuwab-2/wp-gpt-rag-chat.php')) {
    echo "<p><strong>Plugin Status:</strong> ACTIVE</p>\n";
} else {
    echo "<p><strong>Plugin Status:</strong> INACTIVE</p>\n";
}

// Check admin menu hook
echo "<h3>Admin Menu Hook:</h3>\n";
global $wp_filter;
if (isset($wp_filter['admin_menu'])) {
    echo "<p><strong>admin_menu hook:</strong> Registered</p>\n";
    $callbacks = $wp_filter['admin_menu']->callbacks;
    echo "<p><strong>Number of callbacks:</strong> " . count($callbacks) . "</p>\n";
} else {
    echo "<p><strong>admin_menu hook:</strong> NOT REGISTERED</p>\n";
}

// Check if our plugin's admin menu method exists
if (class_exists('WP_GPT_RAG_Chat\Plugin')) {
    $plugin_instance = WP_GPT_RAG_Chat\Plugin::get_instance();
    if (method_exists($plugin_instance, 'admin_menu')) {
        echo "<p><strong>Plugin admin_menu method:</strong> EXISTS</p>\n";
    } else {
        echo "<p><strong>Plugin admin_menu method:</strong> NOT FOUND</p>\n";
    }
} else {
    echo "<p><strong>ERROR:</strong> Plugin class not found!</p>\n";
}

echo "<h3>Recommendations:</h3>\n";
echo "<ol>\n";
echo "<li>If plugin is inactive, activate it</li>\n";
echo "<li>If RBAC class not found, check file paths</li>\n";
echo "<li>If capabilities are missing, check role assignments</li>\n";
echo "<li>Try deactivating and reactivating the plugin</li>\n";
echo "</ol>\n";
?>


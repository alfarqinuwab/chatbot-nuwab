<?php
/**
 * Fix script for login issues after database import
 * Run this from your WordPress root directory
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h2>WordPress Login Fix Script</h2>\n";

global $wpdb;

// 1. Fix site URLs
echo "<h3>1. Fixing Site URLs</h3>\n";
$local_url = 'http://localhost/wp'; // Change this to your local URL

$wpdb->update(
    $wpdb->options,
    ['option_value' => $local_url],
    ['option_name' => 'home']
);

$wpdb->update(
    $wpdb->options,
    ['option_value' => $local_url],
    ['option_name' => 'siteurl']
);

echo "✅ Updated site URLs to: $local_url\n";

// 2. Fix admin user password (if needed)
echo "<h3>2. Fixing Admin User</h3>\n";
$admin_user = get_user_by('login', 'admin');
if (!$admin_user) {
    $admin_user = get_user_by('id', 1);
}

if ($admin_user) {
    // Set a simple password for testing
    wp_set_password('admin123', $admin_user->ID);
    echo "✅ Reset password for user: {$admin_user->user_login}\n";
    echo "New password: admin123\n";
} else {
    echo "❌ No admin user found\n";
}

// 3. Ensure admin capabilities
echo "<h3>3. Fixing Admin Capabilities</h3>\n";
if ($admin_user) {
    $admin_user->set_role('administrator');
    echo "✅ Set administrator role for user: {$admin_user->user_login}\n";
}

// 4. Clear any cached data
echo "<h3>4. Clearing Cache</h3>\n";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✅ Cleared WordPress cache\n";
}

// 5. Fix plugin tables (if missing)
echo "<h3>5. Checking Plugin Tables</h3>\n";
$plugin_tables = [
    'wp_gpt_rag_chat_vectors',
    'wp_gpt_rag_chat_analytics', 
    'wp_gpt_rag_chat_logs',
    'wp_gpt_rag_indexing_queue'
];

foreach ($plugin_tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if (!$exists) {
        echo "❌ Missing table: $table_name\n";
        echo "   Run: wp plugin deactivate chatbot-nuwab-2 && wp plugin activate chatbot-nuwab-2\n";
    } else {
        echo "✅ Table exists: $table_name\n";
    }
}

// 6. Reset plugin settings (if needed)
echo "<h3>6. Plugin Settings</h3>\n";
$plugin_options = $wpdb->get_results("
    SELECT option_name 
    FROM {$wpdb->prefix}options 
    WHERE option_name LIKE '%wp_gpt_rag_chat%'
");

if (empty($plugin_options)) {
    echo "❌ No plugin settings found. Plugin may need to be reactivated.\n";
} else {
    echo "✅ Found " . count($plugin_options) . " plugin settings\n";
}

// 7. Test login
echo "<h3>7. Testing Login</h3>\n";
$test_user = wp_authenticate('admin', 'admin123');
if (is_wp_error($test_user)) {
    echo "❌ Login test failed: " . $test_user->get_error_message() . "\n";
} else {
    echo "✅ Login test successful for user: {$test_user->user_login}\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "1. Try logging in with username: admin, password: admin123\n";
echo "2. If still not working, check your wp-config.php database settings\n";
echo "3. Make sure your local server is running (Apache/Nginx + MySQL)\n";
echo "4. Check that your database contains the imported tables\n";
echo "5. If plugin features don't work, reactivate the plugin\n";
?>

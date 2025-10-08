<?php
/**
 * Debug script for login issues after database import
 * Run this from your WordPress root directory
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h2>WordPress Login Debug Report</h2>\n";

// 1. Check database connection
echo "<h3>1. Database Connection</h3>\n";
global $wpdb;
if ($wpdb->last_error) {
    echo "❌ Database Error: " . $wpdb->last_error . "\n";
} else {
    echo "✅ Database connection successful\n";
}

// 2. Check table prefix
echo "<h3>2. Table Prefix</h3>\n";
echo "Current prefix: " . $wpdb->prefix . "\n";

// 3. Check if core WordPress tables exist
echo "<h3>3. Core WordPress Tables</h3>\n";
$core_tables = ['users', 'usermeta', 'options', 'posts', 'postmeta'];
foreach ($core_tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "✅ $table_name exists ($count records)\n";
    } else {
        echo "❌ $table_name missing\n";
    }
}

// 4. Check site URLs
echo "<h3>4. Site URLs</h3>\n";
$home_url = get_option('home');
$site_url = get_option('siteurl');
echo "Home URL: $home_url\n";
echo "Site URL: $site_url\n";

// 5. Check users
echo "<h3>5. Users</h3>\n";
$users = $wpdb->get_results("SELECT ID, user_login, user_email, user_status FROM {$wpdb->prefix}users LIMIT 5");
if ($users) {
    foreach ($users as $user) {
        echo "User ID: {$user->ID}, Login: {$user->user_login}, Email: {$user->user_email}, Status: {$user->user_status}\n";
    }
} else {
    echo "❌ No users found\n";
}

// 6. Check admin user capabilities
echo "<h3>6. Admin User Capabilities</h3>\n";
$admin_users = $wpdb->get_results("
    SELECT u.ID, u.user_login, um.meta_value as capabilities 
    FROM {$wpdb->prefix}users u 
    LEFT JOIN {$wpdb->prefix}usermeta um ON u.ID = um.user_id AND um.meta_key = '{$wpdb->prefix}capabilities'
    WHERE u.ID = 1
");
if ($admin_users) {
    foreach ($admin_users as $admin) {
        echo "Admin ID: {$admin->ID}, Login: {$admin->user_login}\n";
        if ($admin->capabilities) {
            $caps = maybe_unserialize($admin->capabilities);
            if (isset($caps['administrator'])) {
                echo "✅ Has administrator capability\n";
            } else {
                echo "❌ Missing administrator capability\n";
            }
        } else {
            echo "❌ No capabilities found\n";
        }
    }
}

// 7. Check plugin tables
echo "<h3>7. Plugin Tables</h3>\n";
$plugin_tables = [
    'wp_gpt_rag_chat_vectors',
    'wp_gpt_rag_chat_analytics', 
    'wp_gpt_rag_chat_logs',
    'wp_gpt_rag_indexing_queue'
];

foreach ($plugin_tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "✅ $table_name exists ($count records)\n";
    } else {
        echo "❌ $table_name missing\n";
    }
}

// 8. Check plugin settings
echo "<h3>8. Plugin Settings</h3>\n";
$plugin_options = $wpdb->get_results("
    SELECT option_name, option_value 
    FROM {$wpdb->prefix}options 
    WHERE option_name LIKE '%wp_gpt_rag_chat%' 
    ORDER BY option_name
");

if ($plugin_options) {
    foreach ($plugin_options as $option) {
        echo "Setting: {$option->option_name}\n";
    }
} else {
    echo "❌ No plugin settings found\n";
}

// 9. Test login functionality
echo "<h3>9. Login Test</h3>\n";
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    echo "✅ User is logged in: {$current_user->user_login} (ID: {$current_user->ID})\n";
} else {
    echo "❌ No user is currently logged in\n";
}

// 10. Check for common issues
echo "<h3>10. Common Issues Check</h3>\n";

// Check if wp-config.php has correct database settings
echo "Database Name: " . DB_NAME . "\n";
echo "Database Host: " . DB_HOST . "\n";
echo "Database User: " . DB_USER . "\n";

// Check if .htaccess exists
if (file_exists('.htaccess')) {
    echo "✅ .htaccess file exists\n";
} else {
    echo "❌ .htaccess file missing\n";
}

echo "<h3>Recommendations:</h3>\n";
echo "1. If users table is empty, import wp_users and wp_usermeta tables\n";
echo "2. If URLs are wrong, update wp_options table with correct URLs\n";
echo "3. If capabilities are missing, run: wp user update 1 --role=administrator\n";
echo "4. If plugin tables are missing, reactivate the plugin\n";
echo "5. Check wp-config.php for correct database credentials\n";
?>

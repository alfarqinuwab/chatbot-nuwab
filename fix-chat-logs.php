<?php
/**
 * Fix script for chat logs display issue
 * Run this from your WordPress root directory
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h2>Chat Logs Fix Script</h2>\n";

global $wpdb;

// 1. Check and fix table structure
echo "<h3>1. Checking Table Structure</h3>\n";
$logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';

// Ensure the table has all required columns
$required_columns = [
    'id' => 'bigint(20) NOT NULL AUTO_INCREMENT',
    'chat_id' => 'varchar(255) NOT NULL',
    'turn_number' => 'int(11) DEFAULT 1',
    'role' => 'varchar(50) NOT NULL',
    'user_id' => 'bigint(20) DEFAULT 0',
    'ip_address' => 'varchar(45) DEFAULT NULL',
    'content' => 'longtext NOT NULL',
    'response_latency' => 'int(11) DEFAULT NULL',
    'sources_count' => 'int(11) DEFAULT 0',
    'rag_sources' => 'longtext DEFAULT NULL',
    'rating' => 'int(11) DEFAULT NULL',
    'tags' => 'varchar(255) DEFAULT NULL',
    'model_used' => 'varchar(100) DEFAULT NULL',
    'tokens_used' => 'int(11) DEFAULT NULL',
    'rag_metadata' => 'longtext DEFAULT NULL',
    'created_at' => 'datetime NOT NULL'
];

$existing_columns = $wpdb->get_col("DESCRIBE $logs_table");
$missing_columns = [];

foreach ($required_columns as $column => $definition) {
    if (!in_array($column, $existing_columns)) {
        $missing_columns[$column] = $definition;
    }
}

if (!empty($missing_columns)) {
    echo "Adding missing columns:\n";
    foreach ($missing_columns as $column => $definition) {
        $sql = "ALTER TABLE $logs_table ADD COLUMN $column $definition";
        if ($column === 'id') {
            $sql .= ", ADD PRIMARY KEY (id)";
        }
        $result = $wpdb->query($sql);
        if ($result !== false) {
            echo "✅ Added column: $column\n";
        } else {
            echo "❌ Failed to add column: $column - " . $wpdb->last_error . "\n";
        }
    }
} else {
    echo "✅ All required columns exist\n";
}

// 2. Add indexes for better performance
echo "<h3>2. Adding Indexes</h3>\n";
$indexes = [
    'idx_chat_id' => 'chat_id',
    'idx_role' => 'role',
    'idx_rating' => 'rating',
    'idx_model_used' => 'model_used',
    'idx_created_at' => 'created_at',
    'idx_user_id' => 'user_id'
];

foreach ($indexes as $index_name => $column) {
    $result = $wpdb->query("ALTER TABLE $logs_table ADD INDEX IF NOT EXISTS $index_name ($column)");
    if ($result !== false) {
        echo "✅ Added index: $index_name\n";
    } else {
        echo "⚠️ Index $index_name might already exist\n";
    }
}

// 3. Fix any NULL created_at values
echo "<h3>3. Fixing NULL created_at Values</h3>\n";
$null_dates = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
if ($null_dates > 0) {
    $result = $wpdb->query("UPDATE $logs_table SET created_at = NOW() WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
    echo "✅ Fixed $null_dates records with NULL created_at\n";
} else {
    echo "✅ No NULL created_at values found\n";
}

// 4. Fix user_id for guest users (set to 0)
echo "<h3>4. Fixing User IDs</h3>\n";
$null_users = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table WHERE user_id IS NULL");
if ($null_users > 0) {
    $result = $wpdb->query("UPDATE $logs_table SET user_id = 0 WHERE user_id IS NULL");
    echo "✅ Fixed $null_users records with NULL user_id\n";
} else {
    echo "✅ No NULL user_id values found\n";
}

// 5. Test analytics functionality
echo "<h3>5. Testing Analytics</h3>\n";
if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
    $analytics = new WP_GPT_RAG_Chat\Analytics();
    
    // Test basic functionality
    $total_count = $analytics->get_logs_count();
    echo "✅ Analytics get_logs_count(): $total_count records\n";
    
    $recent_logs = $analytics->get_logs(['limit' => 5]);
    echo "✅ Analytics get_logs(): " . count($recent_logs) . " recent records\n";
    
    // Test with date filter
    $today_logs = $analytics->get_logs_count(['date_from' => date('Y-m-d')]);
    echo "✅ Today's logs count: $today_logs\n";
    
} else {
    echo "❌ Analytics class not found\n";
}

// 6. Clear any caches
echo "<h3>6. Clearing Caches</h3>\n";
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "✅ WordPress cache cleared\n";
}

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
}

// 7. Check current user context
echo "<h3>7. Current User Context</h3>\n";
$current_user_id = get_current_user_id();
echo "Current user ID: $current_user_id\n";

if ($current_user_id > 0) {
    $user = get_user_by('id', $current_user_id);
    echo "Current user: {$user->user_login}\n";
} else {
    echo "No user logged in (this is normal for frontend chat)\n";
}

// 8. Test log insertion
echo "<h3>8. Testing Log Insertion</h3>\n";
$test_data = [
    'chat_id' => 'test_fix_' . time(),
    'turn_number' => 1,
    'role' => 'user',
    'user_id' => 0, // Guest user
    'ip_address' => '127.0.0.1',
    'content' => 'Test message from fix script - ' . date('Y-m-d H:i:s'),
    'created_at' => current_time('mysql')
];

$insert_result = $wpdb->insert($logs_table, $test_data);
if ($insert_result) {
    $insert_id = $wpdb->insert_id;
    echo "✅ Test log inserted with ID: $insert_id\n";
    
    // Verify it can be retrieved
    $retrieved = $wpdb->get_row($wpdb->prepare("SELECT * FROM $logs_table WHERE id = %d", $insert_id));
    if ($retrieved) {
        echo "✅ Test log retrieved successfully\n";
        echo "  Content: " . substr($retrieved->content, 0, 50) . "...\n";
        echo "  Created: {$retrieved->created_at}\n";
        
        // Clean up
        $wpdb->delete($logs_table, ['id' => $insert_id]);
        echo "✅ Test log cleaned up\n";
    }
} else {
    echo "❌ Failed to insert test log: " . $wpdb->last_error . "\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "1. Try chatting on the frontend again\n";
echo "2. Check the analytics page: /wp-admin/admin.php?page=wp-gpt-rag-chat-analytics\n";
echo "3. If still not working, check browser console for JavaScript errors\n";
echo "4. Make sure the plugin is properly activated\n";
echo "5. Check WordPress error logs for any PHP errors\n";
?>

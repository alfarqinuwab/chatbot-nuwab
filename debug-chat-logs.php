<?php
/**
 * Debug script for chat logs issue
 * Run this from your WordPress root directory
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h2>Chat Logs Debug Report</h2>\n";

global $wpdb;

// 1. Check if chat logs table exists
echo "<h3>1. Chat Logs Table Check</h3>\n";
$logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
$exists = $wpdb->get_var("SHOW TABLES LIKE '$logs_table'");
if ($exists) {
    echo "✅ Chat logs table exists: $logs_table\n";
} else {
    echo "❌ Chat logs table missing: $logs_table\n";
    exit;
}

// 2. Check total records
echo "<h3>2. Total Records</h3>\n";
$total_records = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table");
echo "Total records in table: $total_records\n";

// 3. Check recent records (last 10)
echo "<h3>3. Recent Records (Last 10)</h3>\n";
$recent_logs = $wpdb->get_results("
    SELECT id, chat_id, role, user_id, content, created_at 
    FROM $logs_table 
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($recent_logs) {
    foreach ($recent_logs as $log) {
        $content_preview = substr($log->content, 0, 50) . '...';
        echo "ID: {$log->id}, Chat ID: {$log->chat_id}, Role: {$log->role}, User ID: {$log->user_id}, Content: $content_preview, Created: {$log->created_at}\n";
    }
} else {
    echo "❌ No recent records found\n";
}

// 4. Check records by date (today)
echo "<h3>4. Today's Records</h3>\n";
$today = date('Y-m-d');
$today_logs = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM $logs_table 
    WHERE DATE(created_at) = '$today'
");
echo "Records created today: $today_logs\n";

// 5. Check records by user_id
echo "<h3>5. Records by User ID</h3>\n";
$user_counts = $wpdb->get_results("
    SELECT user_id, COUNT(*) as count 
    FROM $logs_table 
    GROUP BY user_id 
    ORDER BY count DESC
");

if ($user_counts) {
    foreach ($user_counts as $user_count) {
        $user_info = $user_count->user_id > 0 ? get_user_by('id', $user_count->user_id) : null;
        $user_name = $user_info ? $user_info->user_login : 'Guest (ID: 0)';
        echo "User ID: {$user_count->user_id} ($user_name) - {$user_count->count} records\n";
    }
} else {
    echo "❌ No user data found\n";
}

// 6. Check if analytics class can retrieve logs
echo "<h3>6. Analytics Class Test</h3>\n";
if (class_exists('WP_GPT_RAG_Chat\Analytics')) {
    $analytics = new WP_GPT_RAG_Chat\Analytics();
    
    // Test get_logs method
    $test_logs = $analytics->get_logs(['limit' => 5]);
    echo "Analytics get_logs() returned: " . count($test_logs) . " records\n";
    
    // Test get_logs_count method
    $test_count = $analytics->get_logs_count();
    echo "Analytics get_logs_count() returned: $test_count records\n";
    
    if (!empty($test_logs)) {
        echo "Sample log from analytics:\n";
        $sample = $test_logs[0];
        echo "  ID: {$sample->id}, Role: {$sample->role}, Content: " . substr($sample->content, 0, 30) . "...\n";
    }
} else {
    echo "❌ Analytics class not found\n";
}

// 7. Check WordPress timezone settings
echo "<h3>7. WordPress Time Settings</h3>\n";
echo "WordPress timezone: " . get_option('timezone_string') . "\n";
echo "WordPress gmt_offset: " . get_option('gmt_offset') . "\n";
echo "Current WordPress time: " . current_time('mysql') . "\n";
echo "Current server time: " . date('Y-m-d H:i:s') . "\n";

// 8. Check for any database errors
echo "<h3>8. Database Errors</h3>\n";
if ($wpdb->last_error) {
    echo "❌ Last database error: " . $wpdb->last_error . "\n";
} else {
    echo "✅ No database errors\n";
}

// 9. Test inserting a sample log
echo "<h3>9. Test Log Insertion</h3>\n";
$test_data = [
    'chat_id' => 'test_debug_' . time(),
    'turn_number' => 1,
    'role' => 'user',
    'user_id' => get_current_user_id(),
    'ip_address' => '127.0.0.1',
    'content' => 'Test message from debug script',
    'created_at' => current_time('mysql')
];

$insert_result = $wpdb->insert($logs_table, $test_data);
if ($insert_result) {
    $insert_id = $wpdb->insert_id;
    echo "✅ Test log inserted successfully with ID: $insert_id\n";
    
    // Try to retrieve it
    $retrieved = $wpdb->get_row($wpdb->prepare("SELECT * FROM $logs_table WHERE id = %d", $insert_id));
    if ($retrieved) {
        echo "✅ Test log retrieved successfully\n";
        
        // Clean up test log
        $wpdb->delete($logs_table, ['id' => $insert_id]);
        echo "✅ Test log cleaned up\n";
    } else {
        echo "❌ Could not retrieve test log\n";
    }
} else {
    echo "❌ Failed to insert test log: " . $wpdb->last_error . "\n";
}

// 10. Check table structure
echo "<h3>10. Table Structure</h3>\n";
$columns = $wpdb->get_results("DESCRIBE $logs_table");
if ($columns) {
    echo "Table columns:\n";
    foreach ($columns as $column) {
        echo "  {$column->Field} - {$column->Type}\n";
    }
} else {
    echo "❌ Could not get table structure\n";
}

echo "<h3>Recommendations:</h3>\n";
echo "1. If new logs are being inserted but not showing, check the analytics page filters\n";
echo "2. If user_id is 0 for new logs, the issue might be with get_current_user_id()\n";
echo "3. If timezone is wrong, update WordPress timezone settings\n";
echo "4. If analytics class returns different results, there might be a filtering issue\n";
?>

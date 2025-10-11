<?php
/**
 * Quick Database Fix Script
 * Adds missing rag_metadata column to logs table
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator to run this script.');
}

echo "<h1>🔧 WP GPT RAG Chat - Database Fix</h1>";

global $wpdb;
$logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';

echo "<h2>Checking Database Status</h2>";

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$logs_table}'");
if (!$table_exists) {
    echo "<p style='color: red;'>❌ Logs table does not exist!</p>";
    echo "<p>Please run the main migration first.</p>";
    exit;
}

echo "<p>✅ Logs table exists: {$logs_table}</p>";

// Check current columns
$columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
$column_names = wp_list_pluck($columns, 'Field');

echo "<h3>Current Columns:</h3>";
echo "<ul>";
foreach ($column_names as $column) {
    echo "<li>{$column}</li>";
}
echo "</ul>";

// Check if rag_metadata column exists
$rag_metadata_exists = in_array('rag_metadata', $column_names);

if ($rag_metadata_exists) {
    echo "<p style='color: green;'>✅ rag_metadata column already exists!</p>";
} else {
    echo "<p style='color: orange;'>⚠️ rag_metadata column is missing. Adding it now...</p>";
    
    // Add the missing column
    $result = $wpdb->query("ALTER TABLE {$logs_table} 
        ADD COLUMN rag_metadata LONGTEXT DEFAULT NULL AFTER tokens_used
    ");
    
    if ($result !== false) {
        echo "<p style='color: green;'>✅ Successfully added rag_metadata column!</p>";
        
        // Update database version
        update_option('wp_gpt_rag_chat_db_version', '2.1.0');
        echo "<p style='color: green;'>✅ Database version updated to 2.1.0</p>";
        
        // Verify the column was added
        $new_columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
        $new_column_names = wp_list_pluck($new_columns, 'Field');
        
        if (in_array('rag_metadata', $new_column_names)) {
            echo "<p style='color: green;'>✅ Column verification successful!</p>";
        } else {
            echo "<p style='color: red;'>❌ Column verification failed!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Failed to add rag_metadata column!</p>";
        echo "<p>Error: " . $wpdb->last_error . "</p>";
    }
}

echo "<h2>Final Status</h2>";

// Final check
$final_columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
$final_column_names = wp_list_pluck($final_columns, 'Field');
$final_rag_metadata_exists = in_array('rag_metadata', $final_column_names);

if ($final_rag_metadata_exists) {
    echo "<p style='color: green; font-weight: bold;'>🎉 Database fix completed successfully!</p>";
    echo "<p>The logging system should now work properly.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Database fix failed!</p>";
    echo "<p>Please check the error messages above.</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-diagnostics') . "'>Go to Diagnostics Page</a></p>";
echo "<p><em>You can delete this file after running the fix.</em></p>";
?>


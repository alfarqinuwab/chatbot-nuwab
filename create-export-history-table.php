<?php
/**
 * Manual script to create export history table
 * Run this file directly to create the export history table
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

global $wpdb;

$table_name = $wpdb->prefix . 'wp_gpt_rag_chat_export_history';

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    export_type varchar(50) NOT NULL,
    file_url text,
    file_path text,
    file_size bigint(20) DEFAULT 0,
    record_count int(11) DEFAULT 0,
    user_id bigint(20) DEFAULT 0,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY export_type (export_type),
    KEY user_id (user_id),
    KEY created_at (created_at)
) {$charset_collate};";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

if (function_exists('dbDelta')) {
    $result = dbDelta($sql);
    
    if ($result) {
        echo "✅ Export history table created successfully!\n";
        echo "Table: {$table_name}\n";
        
        // Update database version
        update_option('wp_gpt_rag_chat_db_version', '2.3.0');
        echo "✅ Database version updated to 2.3.0\n";
    } else {
        echo "❌ Failed to create export history table\n";
    }
} else {
    echo "❌ dbDelta function not available\n";
}

// Check if table was created
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
if ($table_exists) {
    echo "✅ Table verification: Export history table exists\n";
} else {
    echo "❌ Table verification: Export history table does not exist\n";
}
?>

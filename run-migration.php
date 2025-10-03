<?php
/**
 * Manual Database Migration Script
 * Run this script to create the missing error logs and API usage tables
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator to run this script.');
}

echo "<h1>WP GPT RAG Chat - Database Migration</h1>";

// Load the migration class
require_once('includes/class-migration.php');

use WP_GPT_RAG_Chat\Migration;

echo "<h2>Running Migration to Version 2.2.0</h2>";

try {
    // Force run the migration
    Migration::migrate_to_2_2_0();
    
    // Update the version
    update_option('wp_gpt_rag_chat_db_version', '2.2.0');
    
    echo "<p style='color: green;'>✅ Migration completed successfully!</p>";
    echo "<p>Database version updated to: 2.2.0</p>";
    
    // Check database health
    $health = Migration::check_database_health();
    echo "<h3>Database Health Check:</h3>";
    echo "<p><strong>Status:</strong> " . $health['status'] . "</p>";
    echo "<p><strong>Message:</strong> " . $health['message'] . "</p>";
    
    // Show created tables
    global $wpdb;
    $errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
    $usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';
    
    $errors_exists = $wpdb->get_var("SHOW TABLES LIKE '{$errors_table}'") === $errors_table;
    $usage_exists = $wpdb->get_var("SHOW TABLES LIKE '{$usage_table}'") === $usage_table;
    
    echo "<h3>Table Status:</h3>";
    echo "<p>Error Logs Table ({$errors_table}): " . ($errors_exists ? "✅ Created" : "❌ Missing") . "</p>";
    echo "<p>API Usage Table ({$usage_table}): " . ($usage_exists ? "✅ Created" : "❌ Missing") . "</p>";
    
    if ($errors_exists && $usage_exists) {
        echo "<p style='color: green; font-weight: bold;'>🎉 All tables created successfully! You can now use the Error Logs and API Usage tabs.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Migration failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='" . admin_url('admin.php?page=wp-gpt-rag-chat-analytics') . "'>Go to Analytics Page</a></p>";
echo "<p><em>You can delete this file after running the migration.</em></p>";
?>

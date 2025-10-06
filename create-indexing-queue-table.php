<?php
/**
 * Manual Database Migration Script for Indexing Queue Table
 * Run this script to create the indexing queue table for existing installations
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator to run this script.');
}

echo "<h1>WP GPT RAG Chat - Indexing Queue Table Migration</h1>";

// Load the migration class
require_once('includes/class-migration.php');

// Ensure the class is loaded
if (!class_exists('WP_GPT_RAG_Chat\Migration')) {
    die('Migration class not found. Please check the file path.');
}

echo "<h2>Running Migration to Version 2.4.0</h2>";

try {
    // Force run the migration
    \WP_GPT_RAG_Chat\Migration::migrate_to_2_4_0();
    
    // Update the version
    update_option('wp_gpt_rag_chat_db_version', '2.4.0');
    
    echo "<p style='color: green;'>✅ Migration completed successfully!</p>";
    echo "<p>Database version updated to: 2.4.0</p>";
    
    // Check database health
    $health = \WP_GPT_RAG_Chat\Migration::check_database_health();
    echo "<h3>Database Health Check:</h3>";
    echo "<p><strong>Status:</strong> " . $health['status'] . "</p>";
    echo "<p><strong>Message:</strong> " . $health['message'] . "</p>";
    
    // Show created table
    global $wpdb;
    $queue_table = $wpdb->prefix . 'wp_gpt_rag_indexing_queue';
    
    $queue_exists = $wpdb->get_var("SHOW TABLES LIKE '{$queue_table}'") === $queue_table;
    
    echo "<h3>Table Status:</h3>";
    echo "<p>Indexing Queue Table ({$queue_table}): " . ($queue_exists ? "✅ Created" : "❌ Missing") . "</p>";
    
    if ($queue_exists) {
        // Show table structure
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$queue_table}");
        echo "<h4>Table Structure:</h4>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column->Field}</td>";
            echo "<td>{$column->Type}</td>";
            echo "<td>{$column->Null}</td>";
            echo "<td>{$column->Key}</td>";
            echo "<td>{$column->Default}</td>";
            echo "<td>{$column->Extra}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color: green; font-weight: bold;'>🎉 Indexing queue table created successfully!</p>";
        echo "<p>The new indexing system is now ready to use. Posts will be added to the queue instead of wp_postmeta.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Migration failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database permissions and try again.</p>";
}

echo "<hr>";
echo "<p><strong>Important:</strong> Delete this file after successful migration for security reasons.</p>";
echo "<p><em>File: " . __FILE__ . "</em></p>";
?>

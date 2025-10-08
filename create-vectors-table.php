<?php
/**
 * Create vectors table if it doesn't exist
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/create-vectors-table.php
 */

// Load WordPress
require_once('../../../wp-config.php');
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Vectors Table</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        h1 { color: #23282d; }
        h2 { color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .button:hover { background: #005a87; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Create Vectors Table</h1>
        
        <?php if (isset($_POST['action']) && $_POST['action'] === 'create_table'): ?>
            <div class="results">
                <?php
                global $wpdb;
                
                echo "<h2>Creating Vectors Table</h2>\n";
                
                // Check if table already exists
                $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$vectors_table'") === $vectors_table;
                
                if ($table_exists) {
                    echo "<p class='success'>✅ Vectors table already exists: <strong>$vectors_table</strong></p>\n";
                    
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM $vectors_table");
                    echo "<p class='info'>📊 Current vector count: <strong>$count</strong></p>\n";
                } else {
                    echo "<p class='warning'>⚠️ Vectors table does not exist. Creating it now...</p>\n";
                    
                    // Create the table using the same SQL from Plugin.php
                    $charset_collate = $wpdb->get_charset_collate();
                    
                    $vectors_sql = "CREATE TABLE $vectors_table (
                        id bigint(20) NOT NULL AUTO_INCREMENT,
                        post_id bigint(20) NOT NULL,
                        chunk_index int(11) NOT NULL,
                        content_hash varchar(64) NOT NULL,
                        content LONGTEXT DEFAULT NULL COMMENT 'Actual chunk content text',
                        vector_id varchar(255) NOT NULL,
                        created_at datetime DEFAULT CURRENT_TIMESTAMP,
                        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        UNIQUE KEY vector_id (vector_id),
                        KEY post_id (post_id),
                        KEY content_hash (content_hash)
                    ) $charset_collate;";
                    
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    
                    if (function_exists('dbDelta')) {
                        $result = dbDelta($vectors_sql);
                        
                        if (!empty($result)) {
                            echo "<p class='success'>✅ Vectors table created successfully!</p>\n";
                            echo "<p class='info'>📋 Table: <strong>$vectors_table</strong></p>\n";
                            
                            // Show the SQL that was executed
                            echo "<h3>SQL Executed:</h3>\n";
                            echo "<pre>" . htmlspecialchars($vectors_sql) . "</pre>\n";
                            
                            // Show dbDelta results
                            echo "<h3>dbDelta Results:</h3>\n";
                            echo "<pre>";
                            foreach ($result as $line) {
                                echo htmlspecialchars($line) . "\n";
                            }
                            echo "</pre>\n";
                            
                        } else {
                            echo "<p class='error'>❌ Failed to create vectors table</p>\n";
                        }
                    } else {
                        echo "<p class='error'>❌ dbDelta function not available</p>\n";
                    }
                }
                
                // Check all related tables
                echo "<h2>📊 Database Status</h2>\n";
                
                $tables_to_check = [
                    $wpdb->prefix . 'wp_gpt_rag_chat_vectors' => 'Vectors Table',
                    $wpdb->prefix . 'wp_gpt_rag_indexing_queue' => 'Indexing Queue',
                    $wpdb->prefix . 'wp_gpt_rag_chat_logs' => 'Chat Logs',
                    $wpdb->prefix . 'wp_gpt_rag_chat_errors' => 'Error Logs',
                    $wpdb->prefix . 'wp_gpt_rag_chat_api_usage' => 'API Usage'
                ];
                
                foreach ($tables_to_check as $table => $description) {
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
                    if ($exists) {
                        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                        echo "<p class='success'>✅ $description: <strong>$table</strong> ($count records)</p>\n";
                    } else {
                        echo "<p class='error'>❌ $description: <strong>$table</strong> (missing)</p>\n";
                    }
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Actions</h2>
        
        <form method="post">
            <input type="hidden" name="action" value="create_table">
            <button type="submit" class="button">🗄️ Create Vectors Table</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-system.php" class="button" target="_blank">🧪 Test RAG System</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-indexing" class="button" target="_blank">📚 Indexing Page</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-settings" class="button" target="_blank">⚙️ Settings</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

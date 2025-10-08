<?php
/**
 * Check if vectors exist in database
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/check-vectors-exist.php
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
    <title>Check Vectors Exist</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        h1 { color: #23282d; }
        h2 { color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Check Vectors Exist</h1>
        
        <div class="results">
            <?php
            global $wpdb;
            
            // Check vectors table
            $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
            
            echo "<h2>📊 Database Vectors Check</h2>";
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$vectors_table'");
            if ($table_exists) {
                echo "<p class='success'>✅ Vectors table exists: $vectors_table</p>";
                
                // Count total vectors
                $total_vectors = $wpdb->get_var("SELECT COUNT(*) FROM $vectors_table");
                echo "<p class='info'>Total vectors in database: <strong>$total_vectors</strong></p>";
                
                if ($total_vectors > 0) {
                    // Get sample vectors
                    $sample_vectors = $wpdb->get_results("SELECT * FROM $vectors_table LIMIT 5");
                    
                    echo "<h3>Sample Vectors:</h3>";
                    foreach ($sample_vectors as $vector) {
                        echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                        echo "<strong>ID:</strong> " . $vector->id . "<br>";
                        echo "<strong>Post ID:</strong> " . $vector->post_id . "<br>";
                        echo "<strong>Vector ID:</strong> " . $vector->vector_id . "<br>";
                        echo "<strong>Content:</strong> " . substr($vector->content, 0, 200) . "...<br>";
                        echo "<strong>Created:</strong> " . $vector->created_at;
                        echo "</div>";
                    }
                    
                    // Check for مريم content specifically
                    $maryam_vectors = $wpdb->get_results("SELECT * FROM $vectors_table WHERE content LIKE '%مريم%' LIMIT 10");
                    
                    echo "<h3>Vectors containing 'مريم':</h3>";
                    if (!empty($maryam_vectors)) {
                        echo "<p class='success'>✅ Found " . count($maryam_vectors) . " vectors containing 'مريم'</p>";
                        foreach ($maryam_vectors as $vector) {
                            echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                            echo "<strong>Post ID:</strong> " . $vector->post_id . "<br>";
                            echo "<strong>Content:</strong> " . substr($vector->content, 0, 300) . "...";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='error'>❌ No vectors found containing 'مريم'</p>";
                    }
                    
                } else {
                    echo "<p class='error'>❌ No vectors found in database!</p>";
                }
                
            } else {
                echo "<p class='error'>❌ Vectors table does not exist: $vectors_table</p>";
            }
            
            // Check Pinecone connection
            echo "<h2>🌲 Pinecone Connection Check</h2>";
            
            if (class_exists('WP_GPT_RAG_Chat\Pinecone')) {
                try {
                    $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                    $stats = $pinecone->get_index_stats();
                    
                    if ($stats) {
                        echo "<p class='success'>✅ Pinecone connection successful</p>";
                        echo "<pre>" . print_r($stats, true) . "</pre>";
                    } else {
                        echo "<p class='error'>❌ Pinecone stats failed</p>";
                    }
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Pinecone Error: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Pinecone class not found</p>";
            }
            
            // Check settings
            echo "<h2>⚙️ Settings Check</h2>";
            
            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
            
            echo "<p class='info'>Similarity threshold: <strong>" . ($settings['similarity_threshold'] ?? 'N/A') . "</strong></p>";
            echo "<p class='info'>Top K: <strong>" . ($settings['top_k'] ?? 'N/A') . "</strong></p>";
            echo "<p class='info'>Enable RAG: <strong>" . (($settings['enable_rag'] ?? false) ? 'Yes' : 'No') . "</strong></p>";
            
            if (($settings['similarity_threshold'] ?? 0.7) > 0.5) {
                echo "<p class='warning'>⚠️ Similarity threshold might be too high. Consider lowering to 0.3-0.5</p>";
            }
            ?>
        </div>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/debug-rag-deep.php" class="button">🔍 Deep RAG Debug</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

<?php
/**
 * Test RAG system directly
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/test-rag-system.php
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
    <title>RAG System Test</title>
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
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f1f1f1; }
        .highlight { background: #fff3cd; padding: 10px; border-radius: 3px; margin: 10px 0; }
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 RAG System Test</h1>
        
        <?php
        global $wpdb;
        
        // Test 1: Check if RAG classes exist
        echo "<h2>1. 🔍 Check RAG System Components</h2>\n";
        
        $classes_to_check = [
            'WP_GPT_RAG_Chat\RAG_Handler',
            'WP_GPT_RAG_Chat\Vector_DB',
            'WP_GPT_RAG_Chat\Settings',
            'WP_GPT_RAG_Chat\Chat_Handler'
        ];
        
        foreach ($classes_to_check as $class) {
            if (class_exists($class)) {
                echo "<p class='success'>✅ $class exists</p>\n";
            } else {
                echo "<p class='error'>❌ $class not found</p>\n";
            }
        }
        
        // Test 2: Check database tables
        echo "<h2>2. 📊 Check Database Tables</h2>\n";
        
        $tables_to_check = [
            $wpdb->prefix . 'wp_gpt_rag_chat_vectors',
            $wpdb->prefix . 'wp_gpt_rag_indexing_queue',
            $wpdb->prefix . 'wp_gpt_rag_chat_logs'
        ];
        
        foreach ($tables_to_check as $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                echo "<p class='success'>✅ Table $table exists with $count records</p>\n";
            } else {
                echo "<p class='error'>❌ Table $table does not exist</p>\n";
            }
        }
        
        // Test 3: Check plugin settings
        echo "<h2>3. ⚙️ Check Plugin Settings</h2>\n";
        
        if (class_exists('WP_GPT_RAG_Chat\Settings')) {
            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
            
            echo "<table>\n";
            echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>\n";
            
            $critical_settings = [
                'enable_rag' => 'RAG Enabled',
                'rag_mode' => 'RAG Mode',
                'max_sources' => 'Max Sources',
                'similarity_threshold' => 'Similarity Threshold',
                'openai_api_key' => 'OpenAI API Key',
                'pinecone_api_key' => 'Pinecone API Key'
            ];
            
            foreach ($critical_settings as $key => $label) {
                $value = $settings[$key] ?? 'Not set';
                $status = 'warning';
                
            if ($key === 'enable_rag') {
                $status = !empty($value) ? 'success' : 'error';
                $value = !empty($value) ? 'Yes' : 'No';
                } elseif ($key === 'openai_api_key' || $key === 'pinecone_api_key') {
                    $status = !empty($value) ? 'success' : 'error';
                    $value = !empty($value) ? 'Set (' . strlen($value) . ' chars)' : 'Not set';
                } elseif (!empty($value)) {
                    $status = 'success';
                }
                
                echo "<tr>";
                echo "<td>$label</td>";
                echo "<td>$value</td>";
                echo "<td class='$status'>" . ($status === 'success' ? '✅' : ($status === 'warning' ? '⚠️' : '❌')) . "</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
        
        // Test 4: Test RAG retrieval
        echo "<h2>4. 🔍 Test RAG Retrieval</h2>\n";
        
        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
            try {
                $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                
                $test_query = 'من هو الامين العام لمجلس النواب';
                echo "<p class='info'>Testing query: <span class='arabic-text'>$test_query</span></p>\n";
                
                $sources = $rag_handler->retrieve_sources($test_query, 5);
                
                if (!empty($sources)) {
                    echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                    foreach ($sources as $i => $source) {
                        echo "<div class='highlight'>";
                        echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                        echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                        echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                        echo "<strong>Content:</strong> " . substr($source['content'], 0, 300) . "...";
                        echo "</div>\n";
                    }
                } else {
                    echo "<p class='error'>❌ RAG found NO sources for this query</p>\n";
                    echo "<p class='warning'>This means the AI is likely using its training data instead of your indexed content.</p>\n";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Error testing RAG: " . $e->getMessage() . "</p>\n";
            }
        } else {
            echo "<p class='error'>❌ RAG_Handler class not available</p>\n";
        }
        
        // Test 5: Check vector database
        echo "<h2>5. 🗄️ Check Vector Database</h2>\n";
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        $total_vectors = $wpdb->get_var("SELECT COUNT(*) FROM $vectors_table");
        
        if ($total_vectors > 0) {
            echo "<p class='success'>✅ Found $total_vectors vectors in database</p>\n";
            
            // Show sample vectors
            $sample_vectors = $wpdb->get_results("SELECT id, post_id, content, metadata FROM $vectors_table LIMIT 3");
            echo "<h3>Sample Vectors:</h3>\n";
            foreach ($sample_vectors as $vector) {
                echo "<div class='highlight'>";
                echo "<strong>Vector ID:</strong> {$vector->id}<br>";
                echo "<strong>Post ID:</strong> {$vector->post_id}<br>";
                echo "<strong>Content:</strong> " . substr($vector->content, 0, 200) . "...<br>";
                echo "<strong>Metadata:</strong> " . substr($vector->metadata, 0, 100) . "...";
                echo "</div>\n";
            }
        } else {
            echo "<p class='error'>❌ No vectors found in database!</p>\n";
            echo "<p class='warning'>You need to index some content first.</p>\n";
        }
        
        // Test 6: Check Pinecone connection
        echo "<h2>6. 🌲 Check Pinecone Connection</h2>\n";
        
        if (class_exists('WP_GPT_RAG_Chat\Vector_DB')) {
            try {
                $vector_db = new WP_GPT_RAG_Chat\Vector_DB();
                
                // Try to get stats
                $stats = $vector_db->get_stats();
                echo "<p class='success'>✅ Pinecone connection successful</p>\n";
                echo "<pre>";
                print_r($stats);
                echo "</pre>";
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Pinecone connection failed: " . $e->getMessage() . "</p>\n";
            }
        } else {
            echo "<p class='error'>❌ Vector_DB class not available</p>\n";
        }
        
        // Summary
        echo "<h2>📋 Summary & Recommendations</h2>\n";
        
        $issues = [];
        $recommendations = [];
        
        if (!class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
            $issues[] = "RAG_Handler class not found";
        }
        
        if ($total_vectors == 0) {
            $issues[] = "No vectors in database";
            $recommendations[] = "Index some content using the indexing page";
        }
        
        if (class_exists('WP_GPT_RAG_Chat\Settings')) {
            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
            if (empty($settings['enable_rag'])) {
                $issues[] = "RAG is disabled in settings";
                $recommendations[] = "Enable RAG in plugin settings";
            }
        }
        
        if (!empty($issues)) {
            echo "<h3>🚨 Issues Found:</h3>\n";
            echo "<ul>\n";
            foreach ($issues as $issue) {
                echo "<li class='error'>❌ $issue</li>\n";
            }
            echo "</ul>\n";
        }
        
        if (!empty($recommendations)) {
            echo "<h3>💡 Recommendations:</h3>\n";
            echo "<ul>\n";
            foreach ($recommendations as $rec) {
                echo "<li class='info'>✅ $rec</li>\n";
            }
            echo "</ul>\n";
        }
        
        if (empty($issues)) {
            echo "<p class='success'>✅ RAG system appears to be working correctly!</p>\n";
            echo "<p class='info'>If the AI is still giving answers from training data, check the system prompt or chat handler logic.</p>\n";
        }
        ?>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-indexing" class="button" target="_blank">📚 Indexing Page</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-settings" class="button" target="_blank">⚙️ Settings</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-analytics" class="button" target="_blank">📊 Analytics</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

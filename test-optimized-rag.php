<?php
/**
 * Test Optimized RAG (Content from Local DB)
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/test-optimized-rag.php
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
    <title>Test Optimized RAG</title>
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
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
        .architecture { background: #e7f3ff; border: 2px solid #0073aa; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Test Optimized RAG Architecture</h1>
        
        <div class="architecture">
            <h2>🏗️ New Optimized Architecture</h2>
            <p><strong>Pinecone Role:</strong> Vector similarity search only (no content storage)</p>
            <p><strong>Local Database Role:</strong> Content storage and retrieval</p>
            <p><strong>Process:</strong> Pinecone → Returns post_id + chunk_index → Local DB lookup → Content</p>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'test_optimized_rag':
                        echo "<h2>🧪 Test Optimized RAG Query</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            $test_query = 'فاروق عبدالعزيز';
                            echo "<p class='info'>Testing query: <span class='arabic-text'><strong>$test_query</strong></span></p>\n";
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 5);
                            
                            if (!empty($sources)) {
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                                
                                $has_content = false;
                                foreach ($sources as $i => $source) {
                                    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 3px;'>";
                                    echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                                    echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                                    echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                                    
                                    $content = $source['content'] ?? '';
                                    if (!empty($content)) {
                                        $has_content = true;
                                        echo "<strong>Content:</strong> " . substr($content, 0, 300) . "...<br>";
                                        
                                        // Check if content contains فاروق
                                        if (strpos($content, 'فاروق') !== false) {
                                            echo "<span class='success'>✅ Contains 'فاروق'</span><br>";
                                        }
                                    } else {
                                        echo "<strong>Content:</strong> <span class='error'>No content</span><br>";
                                    }
                                    echo "</div>\n";
                                }
                                
                                if ($has_content) {
                                    echo "<p class='success'>🎉 SUCCESS! Content retrieved from local database!</p>\n";
                                    echo "<p class='info'>The optimized RAG system is working correctly.</p>\n";
                                } else {
                                    echo "<p class='error'>❌ Content is still missing from local database lookup.</p>\n";
                                }
                                
                            } else {
                                echo "<p class='error'>❌ RAG found NO sources for this query</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>\n";
                        }
                        break;
                        
                    case 'test_local_db_content':
                        echo "<h2>🗄️ Test Local Database Content</h2>\n";
                        
                        global $wpdb;
                        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
                        
                        // Get some vectors with فاروق content
                        $vectors = $wpdb->get_results($wpdb->prepare("
                            SELECT * FROM $vectors_table 
                            WHERE content LIKE %s 
                            LIMIT 5
                        ", '%فاروق%'));
                        
                        if (!empty($vectors)) {
                            echo "<p class='success'>✅ Found " . count($vectors) . " vectors with 'فاروق' content in local database:</p>\n";
                            
                            foreach ($vectors as $vector) {
                                echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                echo "<strong>Vector ID:</strong> " . $vector->id . "<br>";
                                echo "<strong>Post ID:</strong> " . $vector->post_id . "<br>";
                                echo "<strong>Chunk Index:</strong> " . $vector->chunk_index . "<br>";
                                echo "<strong>Content:</strong> " . substr($vector->content, 0, 200) . "...";
                                echo "</div>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ No vectors found with 'فاروق' content in local database</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Test Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_optimized_rag">
            <button type="submit" class="button">🧪 Test Optimized RAG</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_local_db_content">
            <button type="submit" class="button">🗄️ Test Local DB Content</button>
        </form>
        
        <h2>📋 Benefits of This Approach</h2>
        <div class="results">
            <ul>
                <li><strong>✅ Efficient:</strong> Pinecone only stores vectors + minimal metadata</li>
                <li><strong>✅ Fast:</strong> Single local DB query to get content</li>
                <li><strong>✅ Reliable:</strong> Content always available in local database</li>
                <li><strong>✅ Cost-effective:</strong> Reduces Pinecone storage and API usage</li>
                <li><strong>✅ Flexible:</strong> Easy to modify content without reindexing Pinecone</li>
            </ul>
        </div>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-new-query.php" class="button">🧪 Test New Query</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/fix-pinecone-content.php" class="button">🔧 Fix Content</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

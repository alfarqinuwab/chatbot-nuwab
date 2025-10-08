<?php
/**
 * Test Specific Query for فاروق عبدالعزيز
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/test-specific-query.php
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
    <title>Test Specific Query</title>
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
        .answer-box { background: #e7f3ff; border: 2px solid #0073aa; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Specific Query: فاروق عبدالعزيز</h1>
        
        <div class="answer-box">
            <h2>📋 Known Information</h2>
            <p class="arabic-text"><strong>فاروق عبدالعزيز</strong> is an employee who:</p>
            <ul>
                <li>✅ Participated in the "Arab AI Challenge" competition</li>
                <li>✅ Achieved third place nationally</li>
                <li>✅ Was honored by the Parliament Secretariat</li>
                <li>✅ Was congratulated by Advisor Bounejma</li>
            </ul>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'test_rag_with_lower_threshold':
                        echo "<h2>🧪 Test RAG with Lower Threshold</h2>\n";
                        
                        // Temporarily lower the threshold
                        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                        $original_threshold = $settings['similarity_threshold'];
                        $settings['similarity_threshold'] = 0.1; // Very low threshold
                        update_option('wp_gpt_rag_chat_settings', $settings);
                        
                        echo "<p class='info'>Temporarily lowered threshold to 0.1</p>";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            $test_query = 'من هو فاروق عبدالعزيز';
                            echo "<p class='info'>Testing query: <span class='arabic-text'><strong>$test_query</strong></span></p>\n";
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 10); // Get more sources
                            
                            if (!empty($sources)) {
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                                
                                $found_farouk = false;
                                foreach ($sources as $i => $source) {
                                    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 3px;'>";
                                    echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                                    echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                                    echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                                    
                                    $content = $source['content'] ?? '';
                                    if (!empty($content)) {
                                        echo "<strong>Content:</strong> " . substr($content, 0, 400) . "...<br>";
                                        
                                        // Check if content contains فاروق عبدالعزيز
                                        if (strpos($content, 'فاروق عبدالعزيز') !== false) {
                                            $found_farouk = true;
                                            echo "<span class='success'>✅ Contains 'فاروق عبدالعزيز'</span><br>";
                                        }
                                    } else {
                                        echo "<strong>Content:</strong> <span class='error'>No content</span><br>";
                                    }
                                    echo "</div>\n";
                                }
                                
                                if ($found_farouk) {
                                    echo "<p class='success'>🎉 SUCCESS! Found content about فاروق عبدالعزيز!</p>\n";
                                    echo "<p class='info'>The RAG system can find this information with a lower threshold.</p>\n";
                                } else {
                                    echo "<p class='warning'>⚠️ Still not finding فاروق عبدالعزيز content</p>\n";
                                }
                                
                            } else {
                                echo "<p class='error'>❌ RAG found NO sources</p>\n";
                            }
                        }
                        
                        // Restore original threshold
                        $settings['similarity_threshold'] = $original_threshold;
                        update_option('wp_gpt_rag_chat_settings', $settings);
                        echo "<p class='info'>Restored original threshold: $original_threshold</p>";
                        break;
                        
                    case 'test_direct_vector_lookup':
                        echo "<h2>🔍 Test Direct Vector Lookup</h2>\n";
                        
                        global $wpdb;
                        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
                        
                        // Get the specific vectors we know contain فاروق عبدالعزيز
                        $specific_vectors = $wpdb->get_results("
                            SELECT * FROM $vectors_table 
                            WHERE id IN (6594, 6643)
                        ");
                        
                        if (!empty($specific_vectors)) {
                            echo "<p class='success'>✅ Found the specific vectors containing فاروق عبدالعزيز:</p>\n";
                            
                            foreach ($specific_vectors as $vector) {
                                echo "<div style='background: #e7f3ff; padding: 10px; margin: 10px 0; border-radius: 3px;'>";
                                echo "<strong>Vector ID:</strong> " . $vector->id . "<br>";
                                echo "<strong>Post ID:</strong> " . $vector->post_id . "<br>";
                                echo "<strong>Content:</strong> " . $vector->content;
                                echo "</div>\n";
                            }
                            
                            echo "<p class='info'>These vectors should be retrievable by the RAG system.</p>\n";
                        } else {
                            echo "<p class='error'>❌ Could not find the specific vectors</p>\n";
                        }
                        break;
                        
                    case 'test_embedding_similarity':
                        echo "<h2>🧮 Test Embedding Similarity</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\OpenAI') && class_exists('WP_GPT_RAG_Chat\Pinecone')) {
                            $openai = new WP_GPT_RAG_Chat\OpenAI();
                            $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                            
                            $test_queries = [
                                'فاروق عبدالعزيز',
                                'من هو فاروق عبدالعزيز',
                                'employee Farouk Abdelaziz',
                                'Arab AI Challenge فاروق',
                                'فاروق عبدالعزيز competition'
                            ];
                            
                            foreach ($test_queries as $query) {
                                echo "<h3>Testing: <span class='arabic-text'>$query</span></h3>";
                                
                                try {
                                    $embeddings = $openai->create_embeddings([$query]);
                                    if (!empty($embeddings) && isset($embeddings[0])) {
                                        $query_vector = $embeddings[0];
                                        $result = $pinecone->query_vectors($query_vector, 20);
                                        
                                        echo "<p class='info'>Found " . count($result['matches']) . " matches</p>";
                                        
                                        $found_target = false;
                                        foreach ($result['matches'] as $match) {
                                            if (in_array($match['id'], ['wp_post_58906_chunk_0', 'wp_post_58855_chunk_0'])) {
                                                $found_target = true;
                                                echo "<p class='success'>✅ Found target vector: " . $match['id'] . " (Score: " . $match['score'] . ")</p>";
                                            }
                                        }
                                        
                                        if (!$found_target) {
                                            echo "<p class='warning'>⚠️ Target vectors not found in top results</p>";
                                        }
                                        
                                    } else {
                                        echo "<p class='error'>❌ Failed to generate embedding</p>";
                                    }
                                } catch (Exception $e) {
                                    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
                                }
                            }
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Test Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_rag_with_lower_threshold">
            <button type="submit" class="button">🧪 Test RAG with Lower Threshold</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_direct_vector_lookup">
            <button type="submit" class="button">🔍 Test Direct Vector Lookup</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_embedding_similarity">
            <button type="submit" class="button">🧮 Test Embedding Similarity</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/search-exact-name.php" class="button">🔍 Search Exact Name</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-optimized-rag.php" class="button">🚀 Test Optimized RAG</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

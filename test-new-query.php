<?php
/**
 * Test New Query: فاروق عبدالعزيز
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/test-new-query.php
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
    <title>Test New Query: فاروق عبدالعزيز</title>
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
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
        .query-box { background: #e7f3ff; border: 2px solid #0073aa; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test New Query: فاروق عبدالعزيز</h1>
        
        <div class="query-box">
            <h2>🔍 Test Query</h2>
            <p class="arabic-text"><strong>فاروق عبدالعزيز</strong></p>
            <p class="info">Testing if the RAG system can find information about this person.</p>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'test_rag_query':
                        echo "<h2>🧪 RAG Query Test</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            $test_query = 'فاروق عبدالعزيز';
                            echo "<p class='info'>Testing query: <span class='arabic-text'><strong>$test_query</strong></span></p>\n";
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 5);
                            
                            if (!empty($sources)) {
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                                foreach ($sources as $i => $source) {
                                    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 3px;'>";
                                    echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                                    echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                                    echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                                    echo "<strong>Content:</strong> " . substr($source['content'], 0, 500) . "...";
                                    echo "</div>\n";
                                }
                                
                                // Check if we found content about فاروق عبدالعزيز
                                $found_farouk = false;
                                foreach ($sources as $source) {
                                    if (strpos($source['content'], 'فاروق عبدالعزيز') !== false || 
                                        strpos($source['content'], 'فاروق') !== false) {
                                        $found_farouk = true;
                                        break;
                                    }
                                }
                                
                                if ($found_farouk) {
                                    echo "<p class='success'>✅ SUCCESS: Found content about فاروق عبدالعزيز!</p>\n";
                                    echo "<p class='info'>The AI should now be able to answer questions about this person.</p>\n";
                                } else {
                                    echo "<p class='warning'>⚠️ Content about فاروق عبدالعزيز not found in top results</p>\n";
                                }
                                
                            } else {
                                echo "<p class='error'>❌ RAG found NO sources for this query</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>\n";
                        }
                        break;
                        
                    case 'search_database':
                        echo "<h2>🔍 Database Search</h2>\n";
                        
                        global $wpdb;
                        
                        // Search in posts
                        $posts = $wpdb->get_results($wpdb->prepare("
                            SELECT ID, post_title, post_content 
                            FROM {$wpdb->posts} 
                            WHERE (post_title LIKE %s OR post_content LIKE %s) 
                            AND post_status = 'publish'
                            LIMIT 10
                        ", '%فاروق%', '%فاروق%'));
                        
                        if (!empty($posts)) {
                            echo "<p class='success'>✅ Found " . count($posts) . " posts containing 'فاروق':</p>\n";
                            foreach ($posts as $post) {
                                echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                echo "<strong>Post ID:</strong> " . $post->ID . "<br>";
                                echo "<strong>Title:</strong> " . $post->post_title . "<br>";
                                echo "<strong>Content Preview:</strong> " . substr(strip_tags($post->post_content), 0, 200) . "...";
                                echo "</div>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ No posts found containing 'فاروق'</p>\n";
                        }
                        
                        // Search in vectors table
                        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
                        $vectors = $wpdb->get_results($wpdb->prepare("
                            SELECT * FROM $vectors_table 
                            WHERE content LIKE %s 
                            LIMIT 10
                        ", '%فاروق%'));
                        
                        if (!empty($vectors)) {
                            echo "<h3>📊 Vectors Table Search</h3>";
                            echo "<p class='success'>✅ Found " . count($vectors) . " vectors containing 'فاروق':</p>\n";
                            foreach ($vectors as $vector) {
                                echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                echo "<strong>Vector ID:</strong> " . $vector->id . "<br>";
                                echo "<strong>Post ID:</strong> " . $vector->post_id . "<br>";
                                echo "<strong>Content:</strong> " . substr($vector->content, 0, 300) . "...";
                                echo "</div>\n";
                            }
                        } else {
                            echo "<h3>📊 Vectors Table Search</h3>";
                            echo "<p class='error'>❌ No vectors found containing 'فاروق'</p>\n";
                        }
                        break;
                        
                    case 'test_pinecone_direct':
                        echo "<h2>🌲 Pinecone Direct Test</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\Pinecone') && class_exists('WP_GPT_RAG_Chat\OpenAI')) {
                            try {
                                $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                                $openai = new WP_GPT_RAG_Chat\OpenAI();
                                
                                $test_query = 'فاروق عبدالعزيز';
                                $embeddings = $openai->create_embeddings([$test_query]);
                                
                                if (!empty($embeddings) && isset($embeddings[0])) {
                                    $query_vector = $embeddings[0];
                                    $result = $pinecone->query_vectors($query_vector, 10);
                                    
                                    echo "<p class='info'>Total matches: " . ($result['total_matches'] ?? 'N/A') . "</p>";
                                    echo "<p class='info'>Filtered matches: " . ($result['filtered_matches'] ?? 'N/A') . "</p>";
                                    
                                    if (!empty($result['matches'])) {
                                        echo "<p class='success'>✅ Pinecone found " . count($result['matches']) . " matches</p>";
                                        
                                        foreach ($result['matches'] as $i => $match) {
                                            echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                            echo "<strong>Match " . ($i + 1) . ":</strong><br>";
                                            echo "<strong>Score:</strong> " . ($match['score'] ?? 'N/A') . "<br>";
                                            echo "<strong>Vector ID:</strong> " . ($match['id'] ?? 'N/A') . "<br>";
                                            echo "<strong>Post ID:</strong> " . ($match['metadata']['post_id'] ?? 'N/A') . "<br>";
                                            echo "<strong>Content:</strong> " . substr($match['metadata']['content'] ?? 'No content', 0, 200) . "...";
                                            echo "</div>";
                                        }
                                    } else {
                                        echo "<p class='error'>❌ Pinecone found NO matches</p>";
                                    }
                                } else {
                                    echo "<p class='error'>❌ Failed to generate embedding</p>";
                                }
                            } catch (Exception $e) {
                                echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
                            }
                        } else {
                            echo "<p class='error'>❌ Required classes not found</p>";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Test Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_rag_query">
            <button type="submit" class="button">🧪 Test RAG Query</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="search_database">
            <button type="submit" class="button">🔍 Search Database</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_pinecone_direct">
            <button type="submit" class="button">🌲 Test Pinecone Direct</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/debug-settings-flow.php" class="button">🔍 Debug Settings</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

<?php
/**
 * Deep RAG Debug Tool
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/debug-rag-deep.php
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
    <title>Deep RAG Debug</title>
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
        .step { background: #e7f3ff; border-left: 4px solid #0073aa; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Deep RAG Debug</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'debug_step_by_step':
                        echo "<h2>🔍 Step-by-Step RAG Debug</h2>\n";
                        
                        $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                        echo "<div class='step'>";
                        echo "<h3>Step 1: Test Query</h3>";
                        echo "<p class='arabic-text'><strong>$test_query</strong></p>";
                        echo "</div>";
                        
                        // Step 2: Check OpenAI
                        echo "<div class='step'>";
                        echo "<h3>Step 2: Check OpenAI Connection</h3>";
                        if (class_exists('WP_GPT_RAG_Chat\OpenAI')) {
                            $openai = new WP_GPT_RAG_Chat\OpenAI();
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            
                            if (!empty($settings['openai_api_key'])) {
                                echo "<p class='success'>✅ OpenAI API key is configured</p>";
                                
                                try {
                                    $embeddings = $openai->create_embeddings([$test_query]);
                                    if (!empty($embeddings) && isset($embeddings[0])) {
                                        echo "<p class='success'>✅ Embedding generated successfully</p>";
                                        echo "<p class='info'>Embedding dimension: " . count($embeddings[0]) . "</p>";
                                        echo "<p class='info'>First 5 values: " . implode(', ', array_slice($embeddings[0], 0, 5)) . "...</p>";
                                    } else {
                                        echo "<p class='error'>❌ Failed to generate embedding</p>";
                                    }
                                } catch (Exception $e) {
                                    echo "<p class='error'>❌ OpenAI Error: " . $e->getMessage() . "</p>";
                                }
                            } else {
                                echo "<p class='error'>❌ OpenAI API key is not configured</p>";
                            }
                        } else {
                            echo "<p class='error'>❌ OpenAI class not found</p>";
                        }
                        echo "</div>";
                        
                        // Step 3: Check Vector DB
                        echo "<div class='step'>";
                        echo "<h3>Step 3: Check Vector DB Connection</h3>";
                        if (class_exists('WP_GPT_RAG_Chat\Vector_DB')) {
                            $vector_db = new WP_GPT_RAG_Chat\Vector_DB();
                            
                            try {
                                $stats = $vector_db->get_stats();
                                if ($stats) {
                                    echo "<p class='success'>✅ Vector DB connection successful</p>";
                                    echo "<pre>" . print_r($stats, true) . "</pre>";
                                } else {
                                    echo "<p class='error'>❌ Vector DB stats failed</p>";
                                }
                            } catch (Exception $e) {
                                echo "<p class='error'>❌ Vector DB Error: " . $e->getMessage() . "</p>";
                            }
                        } else {
                            echo "<p class='error'>❌ Vector_DB class not found</p>";
                        }
                        echo "</div>";
                        
                        // Step 4: Test Pinecone Direct Query
                        echo "<div class='step'>";
                        echo "<h3>Step 4: Test Pinecone Direct Query</h3>";
                        if (class_exists('WP_GPT_RAG_Chat\Pinecone')) {
                            $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                            
                            try {
                                // Generate embedding first
                                $openai = new WP_GPT_RAG_Chat\OpenAI();
                                $embeddings = $openai->create_embeddings([$test_query]);
                                
                                if (!empty($embeddings) && isset($embeddings[0])) {
                                    $query_vector = $embeddings[0];
                                    
                                    // Query Pinecone directly
                                    $result = $pinecone->query_vectors($query_vector, 5);
                                    
                                    echo "<p class='success'>✅ Pinecone query successful</p>";
                                    echo "<p class='info'>Total matches: " . ($result['total_matches'] ?? 'N/A') . "</p>";
                                    echo "<p class='info'>Filtered matches: " . ($result['filtered_matches'] ?? 'N/A') . "</p>";
                                    
                                    if (!empty($result['matches'])) {
                                        echo "<p class='success'>✅ Found " . count($result['matches']) . " matches</p>";
                                        foreach ($result['matches'] as $i => $match) {
                                            echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                            echo "<strong>Match " . ($i + 1) . ":</strong><br>";
                                            echo "<strong>Score:</strong> " . ($match['score'] ?? 'N/A') . "<br>";
                                            echo "<strong>ID:</strong> " . ($match['id'] ?? 'N/A') . "<br>";
                                            echo "<strong>Metadata:</strong> " . print_r($match['metadata'] ?? [], true);
                                            echo "</div>";
                                        }
                                    } else {
                                        echo "<p class='error'>❌ No matches found in Pinecone</p>";
                                    }
                                } else {
                                    echo "<p class='error'>❌ Could not generate embedding for Pinecone test</p>";
                                }
                            } catch (Exception $e) {
                                echo "<p class='error'>❌ Pinecone Error: " . $e->getMessage() . "</p>";
                            }
                        } else {
                            echo "<p class='error'>❌ Pinecone class not found</p>";
                        }
                        echo "</div>";
                        
                        // Step 5: Test RAG Handler
                        echo "<div class='step'>";
                        echo "<h3>Step 5: Test RAG Handler</h3>";
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            try {
                                $sources = $rag_handler->retrieve_sources($test_query, 5);
                                
                                if (!empty($sources)) {
                                    echo "<p class='success'>✅ RAG Handler found " . count($sources) . " sources</p>";
                                    foreach ($sources as $i => $source) {
                                        echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                        echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                                        echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                                        echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                                        echo "<strong>Content:</strong> " . substr($source['content'], 0, 200) . "...";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p class='error'>❌ RAG Handler found NO sources</p>";
                                }
                            } catch (Exception $e) {
                                echo "<p class='error'>❌ RAG Handler Error: " . $e->getMessage() . "</p>";
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>";
                        }
                        echo "</div>";
                        
                        break;
                        
                    case 'check_similarity_threshold':
                        echo "<h2>🎯 Check Similarity Threshold</h2>\n";
                        
                        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                        $threshold = $settings['similarity_threshold'] ?? 0.7;
                        
                        echo "<p class='info'>Current similarity threshold: <strong>$threshold</strong></p>";
                        
                        if ($threshold > 0.5) {
                            echo "<p class='warning'>⚠️ Threshold might be too high. Try lowering it to 0.3-0.5</p>";
                        }
                        
                        // Test with different thresholds
                        echo "<h3>Test with Different Thresholds</h3>";
                        
                        if (class_exists('WP_GPT_RAG_Chat\Pinecone')) {
                            $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                            $openai = new WP_GPT_RAG_Chat\OpenAI();
                            
                            $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                            $embeddings = $openai->create_embeddings([$test_query]);
                            
                            if (!empty($embeddings) && isset($embeddings[0])) {
                                $query_vector = $embeddings[0];
                                
                                $thresholds = [0.1, 0.3, 0.5, 0.7, 0.9];
                                
                                foreach ($thresholds as $test_threshold) {
                                    echo "<div style='background: #f9f9f9; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                    echo "<strong>Threshold: $test_threshold</strong><br>";
                                    
                                    try {
                                        // Temporarily modify threshold
                                        $original_threshold = $settings['similarity_threshold'];
                                        $settings['similarity_threshold'] = $test_threshold;
                                        
                                        $result = $pinecone->query_vectors($query_vector, 5);
                                        
                                        echo "Matches found: " . count($result['matches'] ?? []) . "<br>";
                                        
                                        if (!empty($result['matches'])) {
                                            foreach ($result['matches'] as $match) {
                                                echo "Score: " . ($match['score'] ?? 'N/A') . " | ";
                                            }
                                        }
                                        
                                        // Restore original threshold
                                        $settings['similarity_threshold'] = $original_threshold;
                                        
                                    } catch (Exception $e) {
                                        echo "Error: " . $e->getMessage();
                                    }
                                    
                                    echo "</div>";
                                }
                            }
                        }
                        
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Debug Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="debug_step_by_step">
            <button type="submit" class="button">🔍 Step-by-Step Debug</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_similarity_threshold">
            <button type="submit" class="button">🎯 Check Similarity Threshold</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/investigate-indexing-difference.php" class="button">🔍 Full Investigation</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

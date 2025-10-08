<?php
/**
 * Test Settings Fix
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/test-settings-fix.php
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
    <title>Test Settings Fix</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Settings Fix</h1>
        
        <div class="results">
            <?php
            echo "<h2>🔍 Testing Settings Integration</h2>";
            
            // Get current settings
            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
            $current_threshold = $settings['similarity_threshold'] ?? 0.7;
            
            echo "<p class='info'>Current similarity threshold from settings: <strong>$current_threshold</strong></p>";
            
            // Test RAG query
            if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                
                echo "<h3>🧪 Testing RAG Query</h3>";
                echo "<p class='arabic-text'>Query: <strong>$test_query</strong></p>";
                
                $sources = $rag_handler->retrieve_sources($test_query, 5);
                
                if (!empty($sources)) {
                    echo "<p class='success'>✅ SUCCESS! RAG found " . count($sources) . " sources</p>";
                    echo "<p class='info'>This means the settings are being used correctly.</p>";
                    
                    foreach ($sources as $i => $source) {
                        echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                        echo "<strong>Source " . ($i + 1) . ":</strong> Score: " . ($source['score'] ?? 'N/A') . " | Post ID: " . ($source['post_id'] ?? 'N/A');
                        echo "</div>";
                    }
                    
                } else {
                    echo "<p class='error'>❌ RAG found NO sources</p>";
                    echo "<p class='warning'>This suggests the settings are not being used correctly.</p>";
                }
                
            } else {
                echo "<p class='error'>❌ RAG_Handler class not found</p>";
            }
            
            // Test Pinecone directly
            echo "<h3>🌲 Testing Pinecone Directly</h3>";
            
            if (class_exists('WP_GPT_RAG_Chat\Pinecone') && class_exists('WP_GPT_RAG_Chat\OpenAI')) {
                try {
                    $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                    $openai = new WP_GPT_RAG_Chat\OpenAI();
                    
                    $embeddings = $openai->create_embeddings([$test_query]);
                    
                    if (!empty($embeddings) && isset($embeddings[0])) {
                        $query_vector = $embeddings[0];
                        $result = $pinecone->query_vectors($query_vector, 5);
                        
                        echo "<p class='info'>Total matches: " . ($result['total_matches'] ?? 'N/A') . "</p>";
                        echo "<p class='info'>Filtered matches: " . ($result['filtered_matches'] ?? 'N/A') . "</p>";
                        
                        if (!empty($result['matches'])) {
                            echo "<p class='success'>✅ Pinecone found " . count($result['matches']) . " matches</p>";
                            echo "<p class='info'>Using threshold: <strong>$current_threshold</strong></p>";
                        } else {
                            echo "<p class='error'>❌ Pinecone found NO matches</p>";
                            echo "<p class='warning'>Threshold might be too high: <strong>$current_threshold</strong></p>";
                        }
                        
                    } else {
                        echo "<p class='error'>❌ Failed to generate embedding</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Pinecone Error: " . $e->getMessage() . "</p>";
                }
            }
            ?>
        </div>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/debug-settings-flow.php" class="button">🔍 Debug Settings Flow</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

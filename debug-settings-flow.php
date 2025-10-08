<?php
/**
 * Debug Settings Flow
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/debug-settings-flow.php
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
    <title>Debug Settings Flow</title>
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
        <h1>🔍 Debug Settings Flow</h1>
        
        <div class="results">
            <?php
            echo "<h2>📊 Settings Debug</h2>";
            
            // Check raw database option
            echo "<h3>1. Raw Database Option</h3>";
            $raw_option = get_option('wp_gpt_rag_chat_settings');
            echo "<pre>" . print_r($raw_option, true) . "</pre>";
            
            // Check Settings class
            echo "<h3>2. Settings Class Output</h3>";
            if (class_exists('WP_GPT_RAG_Chat\Settings')) {
                $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                echo "<pre>" . print_r($settings, true) . "</pre>";
                
                echo "<h4>Key Settings:</h4>";
                echo "<p class='info'>Similarity Threshold: <strong>" . ($settings['similarity_threshold'] ?? 'NOT SET') . "</strong></p>";
                echo "<p class='info'>Top K: <strong>" . ($settings['top_k'] ?? 'NOT SET') . "</strong></p>";
                echo "<p class='info'>Enable RAG: <strong>" . (($settings['enable_rag'] ?? false) ? 'Yes' : 'No') . "</strong></p>";
                
            } else {
                echo "<p class='error'>❌ Settings class not found</p>";
            }
            
            // Check Pinecone class settings
            echo "<h3>3. Pinecone Class Settings</h3>";
            if (class_exists('WP_GPT_RAG_Chat\Pinecone')) {
                $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                
                // Use reflection to access private settings property
                $reflection = new ReflectionClass($pinecone);
                $settings_property = $reflection->getProperty('settings');
                $settings_property->setAccessible(true);
                $pinecone_settings = $settings_property->getValue($pinecone);
                
                echo "<pre>" . print_r($pinecone_settings, true) . "</pre>";
                
                echo "<h4>Pinecone Key Settings:</h4>";
                echo "<p class='info'>Similarity Threshold: <strong>" . ($pinecone_settings['similarity_threshold'] ?? 'NOT SET') . "</strong></p>";
                echo "<p class='info'>Top K: <strong>" . ($pinecone_settings['top_k'] ?? 'NOT SET') . "</strong></p>";
                
            } else {
                echo "<p class='error'>❌ Pinecone class not found</p>";
            }
            
            // Test actual query with current settings
            echo "<h3>4. Test Query with Current Settings</h3>";
            if (class_exists('WP_GPT_RAG_Chat\Pinecone') && class_exists('WP_GPT_RAG_Chat\OpenAI')) {
                try {
                    $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                    $openai = new WP_GPT_RAG_Chat\OpenAI();
                    
                    $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                    $embeddings = $openai->create_embeddings([$test_query]);
                    
                    if (!empty($embeddings) && isset($embeddings[0])) {
                        $query_vector = $embeddings[0];
                        
                        // Get the current threshold from Pinecone settings
                        $reflection = new ReflectionClass($pinecone);
                        $settings_property = $reflection->getProperty('settings');
                        $settings_property->setAccessible(true);
                        $current_settings = $settings_property->getValue($pinecone);
                        $current_threshold = $current_settings['similarity_threshold'] ?? 0.7;
                        
                        echo "<p class='info'>Using threshold: <strong>$current_threshold</strong></p>";
                        
                        $result = $pinecone->query_vectors($query_vector, 5);
                        
                        echo "<p class='info'>Total matches: " . ($result['total_matches'] ?? 'N/A') . "</p>";
                        echo "<p class='info'>Filtered matches: " . ($result['filtered_matches'] ?? 'N/A') . "</p>";
                        
                        if (!empty($result['matches'])) {
                            echo "<p class='success'>✅ Found " . count($result['matches']) . " matches</p>";
                            foreach ($result['matches'] as $i => $match) {
                                echo "<p class='info'>Match " . ($i + 1) . ": Score = " . ($match['score'] ?? 'N/A') . "</p>";
                            }
                        } else {
                            echo "<p class='error'>❌ No matches found</p>";
                        }
                        
                    } else {
                        echo "<p class='error'>❌ Failed to generate embedding</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
                }
            }
            ?>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'force_reload_settings':
                        // Clear any potential caches and reload
                        wp_cache_delete('wp_gpt_rag_chat_settings', 'options');
                        
                        echo "<p class='success'>✅ Settings cache cleared</p>";
                        echo "<p class='info'>Refreshing page to reload settings...</p>";
                        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
                        break;
                        
                    case 'update_threshold_direct':
                        $new_threshold = floatval($_POST['threshold']);
                        
                        if ($new_threshold >= 0 && $new_threshold <= 1) {
                            // Update directly in database
                            $current_settings = get_option('wp_gpt_rag_chat_settings', []);
                            $current_settings['similarity_threshold'] = $new_threshold;
                            update_option('wp_gpt_rag_chat_settings', $current_settings);
                            
                            // Clear cache
                            wp_cache_delete('wp_gpt_rag_chat_settings', 'options');
                            
                            echo "<p class='success'>✅ Threshold updated directly to: <strong>$new_threshold</strong></p>";
                            echo "<p class='info'>Settings cache cleared. Refreshing page...</p>";
                            echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
                        } else {
                            echo "<p class='error'>❌ Invalid threshold value</p>";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Fix Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="force_reload_settings">
            <button type="submit" class="button">🔄 Force Reload Settings</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="update_threshold_direct">
            <input type="hidden" name="threshold" value="0.3">
            <button type="submit" class="button">🎯 Set Threshold to 0.3 (Direct)</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/fix-similarity-threshold.php" class="button">🎯 Fix Threshold</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

<?php
/**
 * Test RAG fix
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php
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
    <title>Test RAG Fix</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test RAG Fix</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'test_rag_query':
                        echo "<h2>🧪 Test RAG Query After Fix</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                            echo "<p class='info'>Testing query: <span class='arabic-text'><strong>$test_query</strong></span></p>\n";
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 5);
                            
                            if (!empty($sources)) {
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                                foreach ($sources as $i => $source) {
                                    echo "<div style='background: #f9f9f9; padding: 10px; margin: 10px 0; border-radius: 3px;'>";
                                    echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                                    echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                                    echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                                    echo "<strong>Content:</strong> " . substr($source['content'], 0, 500) . "...";
                                    echo "</div>\n";
                                }
                                
                                // Check if we found the specific post about مريم صالح الظاعن
                                $found_maryam = false;
                                foreach ($sources as $source) {
                                    if (strpos($source['content'], 'مريم صالح الظاعن') !== false) {
                                        $found_maryam = true;
                                        break;
                                    }
                                }
                                
                                if ($found_maryam) {
                                    echo "<p class='success'>✅ SUCCESS: Found content about مريم صالح الظاعن!</p>\n";
                                    echo "<p class='info'>The AI should now be able to answer the question correctly.</p>\n";
                                } else {
                                    echo "<p class='warning'>⚠️ Content about مريم صالح الظاعن not found in top results</p>\n";
                                }
                                
                            } else {
                                echo "<p class='error'>❌ RAG found NO sources for this query</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>\n";
                        }
                        break;
                        
                    case 'test_frontend_chat':
                        echo "<h2>🌐 Test Frontend Chat</h2>\n";
                        echo "<p class='info'>Go to your website frontend and test the chat with this query:</p>\n";
                        echo "<p class='arabic-text'><strong>هل هناك نائب اسمه مريم الظاعن ؟</strong></p>\n";
                        echo "<p class='info'>Expected answer should mention مريم صالح الظاعن</p>\n";
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
            <input type="hidden" name="action" value="test_frontend_chat">
            <button type="submit" class="button">🌐 Test Frontend Chat</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/investigate-indexing-difference.php" class="button" target="_blank">🔍 Full Investigation</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

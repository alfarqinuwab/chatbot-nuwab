<?php
/**
 * Fix Similarity Threshold
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/fix-similarity-threshold.php
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
    <title>Fix Similarity Threshold</title>
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
        .button.danger { background: #dc3232; }
        .button.danger:hover { background: #a00; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Fix Similarity Threshold</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'lower_threshold':
                        $new_threshold = floatval($_POST['threshold']);
                        
                        if ($new_threshold >= 0 && $new_threshold <= 1) {
                            // Update the setting
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            $settings['similarity_threshold'] = $new_threshold;
                            
                            // Save the setting
                            update_option('wp_gpt_rag_chat_settings', $settings);
                            
                            echo "<p class='success'>✅ Similarity threshold updated to: <strong>$new_threshold</strong></p>";
                            echo "<p class='info'>The RAG system should now find more matches.</p>";
                            
                            // Test the change
                            echo "<h3>🧪 Testing the Change</h3>";
                            
                            if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                                $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                                $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                                
                                $sources = $rag_handler->retrieve_sources($test_query, 5);
                                
                                if (!empty($sources)) {
                                    echo "<p class='success'>✅ SUCCESS! RAG now finds " . count($sources) . " sources</p>";
                                    foreach ($sources as $i => $source) {
                                        echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                        echo "<strong>Source " . ($i + 1) . ":</strong> Score: " . ($source['score'] ?? 'N/A') . " | Post ID: " . ($source['post_id'] ?? 'N/A');
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p class='error'>❌ Still no sources found. Try an even lower threshold.</p>";
                                }
                            }
                            
                        } else {
                            echo "<p class='error'>❌ Invalid threshold value. Must be between 0 and 1.</p>";
                        }
                        break;
                        
                    case 'reset_threshold':
                        // Reset to default
                        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                        $settings['similarity_threshold'] = 0.7;
                        update_option('wp_gpt_rag_chat_settings', $settings);
                        
                        echo "<p class='success'>✅ Similarity threshold reset to default: <strong>0.7</strong></p>";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php
        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
        $current_threshold = $settings['similarity_threshold'] ?? 0.7;
        ?>
        
        <div class="results">
            <h2>📊 Current Settings</h2>
            <p class="info">Current similarity threshold: <strong><?php echo $current_threshold; ?></strong></p>
            
            <?php if ($current_threshold > 0.5): ?>
                <p class="warning">⚠️ <strong>Threshold is quite high!</strong> This might be filtering out relevant results.</p>
            <?php endif; ?>
        </div>
        
        <h2>🛠️ Quick Fixes</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="lower_threshold">
            <input type="hidden" name="threshold" value="0.3">
            <button type="submit" class="button">🎯 Set to 0.3 (Recommended)</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="lower_threshold">
            <input type="hidden" name="threshold" value="0.2">
            <button type="submit" class="button">🎯 Set to 0.2 (Lower)</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="lower_threshold">
            <input type="hidden" name="threshold" value="0.1">
            <button type="submit" class="button">🎯 Set to 0.1 (Very Low)</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="reset_threshold">
            <button type="submit" class="button danger">🔄 Reset to 0.7</button>
        </form>
        
        <h2>🔗 Debug Tools</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/debug-pinecone-scores.php" class="button">🎯 Debug Scores</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

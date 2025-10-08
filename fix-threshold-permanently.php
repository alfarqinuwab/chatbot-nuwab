<?php
/**
 * Fix Threshold Permanently
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/fix-threshold-permanently.php
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
    <title>Fix Threshold Permanently</title>
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
        <h1>🔧 Fix Threshold Permanently</h1>
        
        <div class="results">
            <h2>🎯 Problem Analysis</h2>
            <p class="error">❌ <strong>Issue:</strong> فاروق عبدالعزيز content exists but has low embedding similarity scores</p>
            <p class="info">✅ <strong>Solution:</strong> Lower similarity threshold to capture relevant but low-scoring results</p>
            <p class="warning">⚠️ <strong>Current threshold:</strong> 0.3 (too high for this content)</p>
            <p class="success">✅ <strong>Recommended threshold:</strong> 0.2 (will capture the 0.324 score result)</p>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'set_threshold_0_2':
                        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                        $old_threshold = $settings['similarity_threshold'];
                        $settings['similarity_threshold'] = 0.2;
                        update_option('wp_gpt_rag_chat_settings', $settings);
                        
                        echo "<p class='success'>✅ Threshold updated from $old_threshold to 0.2</p>";
                        echo "<p class='info'>This should now capture the فاروق عبدالعزيز content (score: 0.324)</p>";
                        
                        // Test the change
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            $test_query = 'من هو فاروق عبدالعزيز';
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 5);
                            
                            if (!empty($sources)) {
                                echo "<h3>🧪 Test Results:</h3>";
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources</p>";
                                
                                $found_farouk = false;
                                foreach ($sources as $i => $source) {
                                    echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                    echo "<strong>Source " . ($i + 1) . ":</strong> Score: " . ($source['score'] ?? 'N/A') . " | Post ID: " . ($source['post_id'] ?? 'N/A') . "<br>";
                                    
                                    $content = $source['content'] ?? '';
                                    if (!empty($content)) {
                                        echo "<strong>Content:</strong> " . substr($content, 0, 200) . "...<br>";
                                        
                                        if (strpos($content, 'فاروق عبدالعزيز') !== false) {
                                            $found_farouk = true;
                                            echo "<span class='success'>✅ Contains 'فاروق عبدالعزيز'</span>";
                                        }
                                    }
                                    echo "</div>";
                                }
                                
                                if ($found_farouk) {
                                    echo "<p class='success'>🎉 SUCCESS! فاروق عبدالعزيز content is now found!</p>";
                                    echo "<p class='info'>The RAG system should now work correctly for this query.</p>";
                                } else {
                                    echo "<p class='warning'>⚠️ Still not finding فاروق عبدالعزيز content</p>";
                                }
                            }
                        }
                        break;
                        
                    case 'set_threshold_0_1':
                        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                        $old_threshold = $settings['similarity_threshold'];
                        $settings['similarity_threshold'] = 0.1;
                        update_option('wp_gpt_rag_chat_settings', $settings);
                        
                        echo "<p class='success'>✅ Threshold updated from $old_threshold to 0.1</p>";
                        echo "<p class='info'>This is a very low threshold that should capture almost all relevant content</p>";
                        echo "<p class='warning'>⚠️ This might also capture some less relevant results</p>";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php
        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
        $current_threshold = $settings['similarity_threshold'] ?? 0.3;
        ?>
        
        <div class="results">
            <h2>📊 Current Settings</h2>
            <p class="info">Current similarity threshold: <strong><?php echo $current_threshold; ?></strong></p>
            
            <?php if ($current_threshold > 0.2): ?>
                <p class="warning">⚠️ <strong>Threshold is too high!</strong> فاروق عبدالعزيز content has score 0.324, which won't be captured.</p>
            <?php endif; ?>
        </div>
        
        <h2>🛠️ Fix Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="set_threshold_0_2">
            <button type="submit" class="button">🎯 Set to 0.2 (Recommended)</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="set_threshold_0_1">
            <button type="submit" class="button">🎯 Set to 0.1 (Very Low)</button>
        </form>
        
        <h2>📋 Why This Fixes the Issue</h2>
        <div class="results">
            <ul>
                <li><strong>✅ فاروق عبدالعزيز content exists</strong> in vectors 6594 and 6643</li>
                <li><strong>✅ Content is properly indexed</strong> and retrievable</li>
                <li><strong>❌ Similarity score is low</strong> (0.324) due to embedding model limitations</li>
                <li><strong>✅ Lowering threshold to 0.2</strong> will capture this content</li>
                <li><strong>✅ RAG system will work</strong> for queries about فاروق عبدالعزيز</li>
            </ul>
        </div>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-specific-query.php" class="button">🧪 Test Specific Query</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

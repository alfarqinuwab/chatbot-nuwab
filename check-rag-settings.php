<?php
/**
 * Check and fix RAG settings
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/check-rag-settings.php
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
    <title>Check RAG Settings</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Check RAG Settings</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'check_settings':
                        echo "<h2>📋 Current RAG Settings</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\Settings')) {
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            
                            echo "<table>\n";
                            echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>\n";
                            
                            $rag_settings = [
                                'enable_rag' => 'RAG Enabled',
                                'rag_mode' => 'RAG Mode',
                                'max_sources' => 'Max Sources',
                                'similarity_threshold' => 'Similarity Threshold',
                                'openai_api_key' => 'OpenAI API Key',
                                'pinecone_api_key' => 'Pinecone API Key',
                                'pinecone_host' => 'Pinecone Host',
                                'pinecone_index' => 'Pinecone Index'
                            ];
                            
                            foreach ($rag_settings as $key => $label) {
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
                            
                            // Check if RAG is actually enabled
                            if (empty($settings['enable_rag'])) {
                                echo "<p class='error'>❌ RAG is DISABLED! This is why the AI is using training data.</p>\n";
                            } else {
                                echo "<p class='success'>✅ RAG is ENABLED in settings</p>\n";
                            }
                            
                        } else {
                            echo "<p class='error'>❌ Settings class not found</p>\n";
                        }
                        break;
                        
                    case 'enable_rag':
                        echo "<h2>🔧 Enabling RAG</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\Settings')) {
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            
                            // Enable RAG
                            $settings['enable_rag'] = true;
                            
                            // Save settings
                            $result = update_option('wp_gpt_rag_chat_settings', $settings);
                            
                            if ($result) {
                                echo "<p class='success'>✅ RAG has been ENABLED!</p>\n";
                                echo "<p class='info'>The AI should now use your indexed content instead of training data.</p>\n";
                            } else {
                                echo "<p class='error'>❌ Failed to enable RAG</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ Settings class not found</p>\n";
                        }
                        break;
                        
                    case 'test_rag_query':
                        echo "<h2>🧪 Test RAG Query</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            $test_query = 'من هو الامين العام لمجلس النواب';
                            echo "<p class='info'>Testing query: <strong>$test_query</strong></p>\n";
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 5);
                            
                            if (!empty($sources)) {
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                                foreach ($sources as $i => $source) {
                                    echo "<div style='background: #f9f9f9; padding: 10px; margin: 10px 0; border-radius: 3px;'>";
                                    echo "<strong>Source " . ($i + 1) . " (Score: " . ($source['score'] ?? 'N/A') . "):</strong><br>";
                                    echo substr($source['content'], 0, 300) . "...";
                                    echo "</div>\n";
                                }
                            } else {
                                echo "<p class='error'>❌ RAG found NO sources for this query</p>\n";
                                echo "<p class='warning'>This means the AI will use training data instead of your indexed content.</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_settings">
            <button type="submit" class="button">📋 Check Current Settings</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="enable_rag">
            <button type="submit" class="button">🔧 Enable RAG</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_rag_query">
            <button type="submit" class="button">🧪 Test RAG Query</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-system.php" class="button" target="_blank">🧪 Test RAG System</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-settings" class="button" target="_blank">⚙️ Plugin Settings</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-indexing" class="button" target="_blank">📚 Indexing Page</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

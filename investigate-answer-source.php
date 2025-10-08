<?php
/**
 * Full investigation tool for AI answer source
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/investigate-answer-source.php
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
    <title>AI Answer Source Investigation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        .highlight { background: #fff3cd; padding: 10px; border-radius: 3px; margin: 10px 0; }
        h1 { color: #23282d; }
        h2 { color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        h3 { color: #0073aa; }
        .button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .button:hover { background: #005a87; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f1f1f1; }
        .test-query { background: #e7f3ff; padding: 15px; border-left: 4px solid #0073aa; margin: 15px 0; }
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 AI Answer Source Investigation</h1>
        
        <div class="test-query">
            <h3>🎯 Test Query:</h3>
            <p class="arabic-text"><strong>من هو الامين العام لمجلس النواب</strong></p>
            <p><em>Translation: "Who is the Secretary General of the House of Representatives?"</em></p>
        </div>
        
        <div class="test-query">
            <h3>🤖 Expected Answer:</h3>
            <p class="arabic-text"><strong>الأمين العام لمجلس النواب هو السيد محمد بونجمة</strong></p>
            <p><em>Translation: "The Secretary General of the House of Representatives is Mr. Mohamed Bounedjma"</em></p>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                global $wpdb;
                
                switch ($_POST['action']) {
                    case 'full_investigation':
                        echo "<h2>🔍 Full Investigation Results</h2>\n";
                        
                        // 1. Check indexed content
                        echo "<h3>1. 📚 Check Indexed Content</h3>\n";
                        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_vectors';
                        $indexing_queue_table = $wpdb->prefix . 'wp_gpt_rag_indexing_queue';
                        
                        // Check vectors table
                        $total_vectors = $wpdb->get_var("SELECT COUNT(*) FROM $vectors_table");
                        echo "<p class='info'>📊 Total vectors in database: <strong>$total_vectors</strong></p>\n";
                        
                        if ($total_vectors > 0) {
                            // Search for relevant content
                            $search_terms = ['محمد', 'بونجمة', 'بونجمة', 'امين', 'عام', 'مجلس', 'النواب', 'secretary', 'general', 'house', 'representatives'];
                            
                            echo "<h4>🔍 Searching for relevant content in vectors:</h4>\n";
                            foreach ($search_terms as $term) {
                                $results = $wpdb->get_results($wpdb->prepare(
                                    "SELECT id, post_id, content, metadata FROM $vectors_table WHERE content LIKE %s LIMIT 3",
                                    '%' . $wpdb->esc_like($term) . '%'
                                ));
                                
                                if (!empty($results)) {
                                    echo "<p class='success'>✅ Found content containing '<strong>$term</strong>':</p>\n";
                                    foreach ($results as $result) {
                                        echo "<div class='highlight'>";
                                        echo "<strong>Vector ID:</strong> {$result->id}<br>";
                                        echo "<strong>Post ID:</strong> {$result->post_id}<br>";
                                        echo "<strong>Content:</strong> " . substr($result->content, 0, 200) . "...<br>";
                                        echo "<strong>Metadata:</strong> " . substr($result->metadata, 0, 100) . "...";
                                        echo "</div>\n";
                                    }
                                } else {
                                    echo "<p class='warning'>❌ No content found containing '<strong>$term</strong>'</p>\n";
                                }
                            }
                        } else {
                            echo "<p class='error'>❌ No vectors found in database!</p>\n";
                        }
                        
                        // Check indexing queue
                        $queue_count = $wpdb->get_var("SELECT COUNT(*) FROM $indexing_queue_table");
                        echo "<p class='info'>📋 Items in indexing queue: <strong>$queue_count</strong></p>\n";
                        
                        // 2. Check WordPress posts
                        echo "<h3>2. 📝 Check WordPress Posts</h3>\n";
                        $search_terms_posts = ['محمد', 'بونجمة', 'امين', 'عام', 'مجلس', 'النواب'];
                        
                        foreach ($search_terms_posts as $term) {
                            $posts = $wpdb->get_results($wpdb->prepare(
                                "SELECT ID, post_title, post_content, post_type, post_status 
                                FROM {$wpdb->posts} 
                                WHERE (post_title LIKE %s OR post_content LIKE %s) 
                                AND post_status = 'publish' 
                                LIMIT 3",
                                '%' . $wpdb->esc_like($term) . '%',
                                '%' . $wpdb->esc_like($term) . '%'
                            ));
                            
                            if (!empty($posts)) {
                                echo "<p class='success'>✅ Found WordPress posts containing '<strong>$term</strong>':</p>\n";
                                foreach ($posts as $post) {
                                    echo "<div class='highlight'>";
                                    echo "<strong>Post ID:</strong> {$post->ID}<br>";
                                    echo "<strong>Title:</strong> {$post->post_title}<br>";
                                    echo "<strong>Type:</strong> {$post->post_type}<br>";
                                    echo "<strong>Status:</strong> {$post->post_status}<br>";
                                    echo "<strong>Content:</strong> " . substr(strip_tags($post->post_content), 0, 200) . "...";
                                    echo "</div>\n";
                                }
                            } else {
                                echo "<p class='warning'>❌ No WordPress posts found containing '<strong>$term</strong>'</p>\n";
                            }
                        }
                        
                        // 3. Check plugin settings
                        echo "<h3>3. ⚙️ Check Plugin Settings</h3>\n";
                        if (class_exists('WP_GPT_RAG_Chat\Settings')) {
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            
                            echo "<h4>RAG Settings:</h4>\n";
                            echo "<pre>";
                            echo "Enable RAG: " . ($settings['enable_rag'] ? 'Yes' : 'No') . "\n";
                            echo "RAG Mode: " . ($settings['rag_mode'] ?? 'Not set') . "\n";
                            echo "Max Sources: " . ($settings['max_sources'] ?? 'Not set') . "\n";
                            echo "Similarity Threshold: " . ($settings['similarity_threshold'] ?? 'Not set') . "\n";
                            echo "Post Types: " . implode(', ', $settings['post_types'] ?? []) . "\n";
                            echo "</pre>";
                            
                            // Check if RAG is actually enabled
                            if (!$settings['enable_rag']) {
                                echo "<p class='error'>❌ RAG is DISABLED! This means the AI is not using your indexed content.</p>\n";
                            }
                        }
                        
                        // 4. Test actual chat query
                        echo "<h3>4. 🤖 Test Actual Chat Query</h3>\n";
                        if (class_exists('WP_GPT_RAG_Chat\Chat_Handler')) {
                            try {
                                $chat_handler = new WP_GPT_RAG_Chat\Chat_Handler();
                                
                                // Simulate the query
                                $test_data = [
                                    'message' => 'من هو الامين العام لمجلس النواب',
                                    'chat_id' => 'test_investigation_' . time(),
                                    'user_id' => get_current_user_id()
                                ];
                                
                                echo "<p class='info'>🧪 Testing chat query...</p>\n";
                                
                                // This would normally call the chat handler
                                // For now, let's check what sources would be retrieved
                                if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                                    $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                                    
                                    // Test RAG retrieval
                                    $sources = $rag_handler->retrieve_sources('من هو الامين العام لمجلس النواب', 5);
                                    
                                    if (!empty($sources)) {
                                        echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                                        foreach ($sources as $i => $source) {
                                            echo "<div class='highlight'>";
                                            echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                                            echo "<strong>Content:</strong> " . substr($source['content'], 0, 200) . "...<br>";
                                            echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                                            echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A');
                                            echo "</div>\n";
                                        }
                                    } else {
                                        echo "<p class='error'>❌ RAG found NO sources for this query!</p>\n";
                                    }
                                }
                                
                            } catch (Exception $e) {
                                echo "<p class='error'>❌ Error testing chat: " . $e->getMessage() . "</p>\n";
                            }
                        }
                        
                        // 5. Check if AI has built-in knowledge
                        echo "<h3>5. 🧠 Check AI Model Knowledge</h3>\n";
                        echo "<p class='info'>The AI model (GPT-4) has training data that includes information about:</p>\n";
                        echo "<ul>\n";
                        echo "<li>Algerian politics and government</li>\n";
                        echo "<li>Mohamed Bounedjma (former Secretary General of the Algerian National Assembly)</li>\n";
                        echo "<li>General knowledge about parliamentary systems</li>\n";
                        echo "</ul>\n";
                        echo "<p class='warning'>⚠️ If RAG is disabled or no relevant sources are found, the AI will fall back to its training data.</p>\n";
                        
                        break;
                        
                    case 'test_rag_retrieval':
                        echo "<h2>🔍 Test RAG Retrieval</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            $test_queries = [
                                'من هو الامين العام لمجلس النواب',
                                'محمد بونجمة',
                                'secretary general house representatives',
                                'امين عام مجلس النواب'
                            ];
                            
                            foreach ($test_queries as $query) {
                                echo "<h3>Testing query: '$query'</h3>\n";
                                
                                $sources = $rag_handler->retrieve_sources($query, 5);
                                
                                if (!empty($sources)) {
                                    echo "<p class='success'>✅ Found " . count($sources) . " sources:</p>\n";
                                    foreach ($sources as $i => $source) {
                                        echo "<div class='highlight'>";
                                        echo "<strong>Source " . ($i + 1) . " (Score: " . ($source['score'] ?? 'N/A') . "):</strong><br>";
                                        echo substr($source['content'], 0, 300) . "...";
                                        echo "</div>\n";
                                    }
                                } else {
                                    echo "<p class='error'>❌ No sources found</p>\n";
                                }
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>\n";
                        }
                        break;
                        
                    case 'check_ai_fallback':
                        echo "<h2>🧠 Check AI Fallback Behavior</h2>\n";
                        
                        echo "<h3>Possible Sources of the Answer:</h3>\n";
                        echo "<ol>\n";
                        echo "<li><strong>RAG System:</strong> Retrieved from your indexed content</li>\n";
                        echo "<li><strong>AI Training Data:</strong> Built-in knowledge from GPT-4 training</li>\n";
                        echo "<li><strong>System Prompt:</strong> Instructions in the AI prompt</li>\n";
                        echo "<li><strong>External API:</strong> Information from external sources</li>\n";
                        echo "</ol>\n";
                        
                        // Check system prompt
                        if (class_exists('WP_GPT_RAG_Chat\Settings')) {
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            $system_prompt = $settings['system_prompt'] ?? '';
                            
                            if (strpos($system_prompt, 'محمد') !== false || strpos($system_prompt, 'بونجمة') !== false) {
                                echo "<p class='warning'>⚠️ Found 'محمد' or 'بونجمة' in system prompt!</p>\n";
                                echo "<div class='highlight'>";
                                echo "<strong>System Prompt:</strong><br>";
                                echo substr($system_prompt, 0, 500) . "...";
                                echo "</div>\n";
                            } else {
                                echo "<p class='info'>ℹ️ No specific mention of 'محمد' or 'بونجمة' in system prompt</p>\n";
                            }
                        }
                        
                        echo "<h3>Recommendations:</h3>\n";
                        echo "<ul>\n";
                        echo "<li>✅ <strong>Enable RAG</strong> if it's disabled</li>\n";
                        echo "<li>✅ <strong>Index relevant content</strong> about the Secretary General</li>\n";
                        echo "<li>✅ <strong>Update system prompt</strong> to specify when to use RAG vs training data</li>\n";
                        echo "<li>✅ <strong>Add source attribution</strong> to responses</li>\n";
                        echo "</ul>\n";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Investigation Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="full_investigation">
            <button type="submit" class="button">🔍 Full Investigation</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_rag_retrieval">
            <button type="submit" class="button">🧪 Test RAG Retrieval</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_ai_fallback">
            <button type="submit" class="button">🧠 Check AI Fallback</button>
        </form>
        
        <h2>🎯 What This Investigation Will Reveal</h2>
        <ul>
            <li><strong>Full Investigation:</strong> Checks indexed content, WordPress posts, plugin settings, and RAG retrieval</li>
            <li><strong>Test RAG Retrieval:</strong> Tests if the RAG system can find relevant sources for the query</li>
            <li><strong>Check AI Fallback:</strong> Analyzes why the AI might be using training data instead of your content</li>
        </ul>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-indexing" class="button" target="_blank">📚 Indexing Page</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-settings" class="button" target="_blank">⚙️ Settings</a>
            <a href="/" class="button" target="_blank">🌐 Test Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

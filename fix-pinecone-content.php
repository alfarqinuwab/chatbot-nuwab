<?php
/**
 * Fix Pinecone Content Storage
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/fix-pinecone-content.php
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
    <title>Fix Pinecone Content Storage</title>
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
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Pinecone Content Storage</h1>
        
        <div class="results">
            <h2>🔍 Problem Identified</h2>
            <p class="error">❌ <strong>Issue:</strong> Pinecone vectors are missing the actual content in metadata</p>
            <p class="info">✅ <strong>Fix Applied:</strong> Updated <code>create_vector_metadata()</code> to include content</p>
            <p class="warning">⚠️ <strong>Action Required:</strong> Reindex content to store content in Pinecone metadata</p>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'reindex_specific_posts':
                        echo "<h2>🔄 Reindexing Specific Posts</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\Indexing')) {
                            $indexing = new WP_GPT_RAG_Chat\Indexing();
                            
                            // Get posts that contain فاروق
                            global $wpdb;
                            $posts = $wpdb->get_results($wpdb->prepare("
                                SELECT ID FROM {$wpdb->posts} 
                                WHERE (post_title LIKE %s OR post_content LIKE %s) 
                                AND post_status = 'publish'
                                LIMIT 10
                            ", '%فاروق%', '%فاروق%'));
                            
                            if (!empty($posts)) {
                                echo "<p class='info'>Found " . count($posts) . " posts containing 'فاروق' to reindex</p>\n";
                                
                                $success_count = 0;
                                $error_count = 0;
                                
                                foreach ($posts as $post) {
                                    try {
                                        $result = $indexing->index_post($post->ID, true); // Force reindex
                                        if ($result) {
                                            $success_count++;
                                            echo "<p class='success'>✅ Reindexed Post ID: " . $post->ID . "</p>\n";
                                        } else {
                                            $error_count++;
                                            echo "<p class='error'>❌ Failed to reindex Post ID: " . $post->ID . "</p>\n";
                                        }
                                    } catch (Exception $e) {
                                        $error_count++;
                                        echo "<p class='error'>❌ Error reindexing Post ID " . $post->ID . ": " . $e->getMessage() . "</p>\n";
                                    }
                                }
                                
                                echo "<h3>📊 Results</h3>";
                                echo "<p class='success'>✅ Successfully reindexed: $success_count posts</p>";
                                echo "<p class='error'>❌ Failed to reindex: $error_count posts</p>";
                                
                                if ($success_count > 0) {
                                    echo "<p class='info'>🎉 Content should now be available in Pinecone metadata!</p>";
                                }
                                
                            } else {
                                echo "<p class='error'>❌ No posts found containing 'فاروق'</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ Indexing class not found</p>\n";
                        }
                        break;
                        
                    case 'test_after_fix':
                        echo "<h2>🧪 Test After Fix</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            $test_query = 'فاروق عبدالعزيز';
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 5);
                            
                            if (!empty($sources)) {
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources</p>\n";
                                
                                $has_content = false;
                                foreach ($sources as $i => $source) {
                                    echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                    echo "<strong>Source " . ($i + 1) . ":</strong><br>";
                                    echo "<strong>Score:</strong> " . ($source['score'] ?? 'N/A') . "<br>";
                                    echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                                    
                                    $content = $source['content'] ?? '';
                                    if (!empty($content) && $content !== 'No content...') {
                                        $has_content = true;
                                        echo "<strong>Content:</strong> " . substr($content, 0, 200) . "...<br>";
                                    } else {
                                        echo "<strong>Content:</strong> <span class='error'>No content</span><br>";
                                    }
                                    echo "</div>\n";
                                }
                                
                                if ($has_content) {
                                    echo "<p class='success'>🎉 SUCCESS! Content is now available in Pinecone!</p>";
                                    echo "<p class='info'>The RAG system should now work correctly.</p>";
                                } else {
                                    echo "<p class='error'>❌ Content is still missing. Try reindexing more posts.</p>";
                                }
                                
                            } else {
                                echo "<p class='error'>❌ RAG found NO sources</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Fix Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="reindex_specific_posts">
            <button type="submit" class="button">🔄 Reindex Posts with 'فاروق'</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_after_fix">
            <button type="submit" class="button">🧪 Test After Fix</button>
        </form>
        
        <h2>📋 Instructions</h2>
        <div class="results">
            <ol>
                <li><strong>Click "🔄 Reindex Posts with 'فاروق'"</strong> - This will reindex posts containing فاروق with the fixed metadata</li>
                <li><strong>Click "🧪 Test After Fix"</strong> - This will test if content is now available in Pinecone</li>
                <li><strong>Test the frontend chat</strong> - Try asking about فاروق عبدالعزيز again</li>
            </ol>
            
            <p class="warning"><strong>Note:</strong> This fix only affects new indexing. Existing vectors in Pinecone still won't have content until reindexed.</p>
        </div>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-new-query.php" class="button">🧪 Test New Query</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

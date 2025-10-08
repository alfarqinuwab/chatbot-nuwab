<?php
/**
 * Investigate bulk vs manual indexing difference
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/investigate-indexing-difference.php
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
    <title>Investigate Indexing Difference</title>
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
        .button { background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .button:hover { background: #005a87; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f1f1f1; }
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Investigate Bulk vs Manual Indexing</h1>
        
        <div class="highlight">
            <h3>🎯 Test Case:</h3>
            <p class="arabic-text"><strong>Query:</strong> هل هناك نائب اسمه مريم الظاعن ؟</p>
            <p class="arabic-text"><strong>Expected Answer:</strong> نعم، هناك نائبة تُدعى مريم صالح الظاعن</p>
            <p><strong>Issue:</strong> Bulk indexing doesn't work, but manual "Re-index Now" works</p>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                global $wpdb;
                
                switch ($_POST['action']) {
                    case 'find_test_post':
                        echo "<h2>🔍 Find Test Post</h2>\n";
                        
                        // Search for the specific post
                        $search_terms = ['مريم', 'الظاعن', 'مريم الظاعن', 'مريم صالح'];
                        
                        foreach ($search_terms as $term) {
                            $posts = $wpdb->get_results($wpdb->prepare(
                                "SELECT ID, post_title, post_content, post_type, post_status 
                                FROM {$wpdb->posts} 
                                WHERE (post_title LIKE %s OR post_content LIKE %s) 
                                AND post_status = 'publish' 
                                LIMIT 5",
                                '%' . $wpdb->esc_like($term) . '%',
                                '%' . $wpdb->esc_like($term) . '%'
                            ));
                            
                            if (!empty($posts)) {
                                echo "<p class='success'>✅ Found posts containing '<strong>$term</strong>':</p>\n";
                                foreach ($posts as $post) {
                                    echo "<div class='highlight'>";
                                    echo "<strong>Post ID:</strong> {$post->ID}<br>";
                                    echo "<strong>Title:</strong> {$post->post_title}<br>";
                                    echo "<strong>Type:</strong> {$post->post_type}<br>";
                                    echo "<strong>Status:</strong> {$post->post_status}<br>";
                                    echo "<strong>Content Preview:</strong> " . substr(strip_tags($post->post_content), 0, 200) . "...";
                                    echo "</div>\n";
                                }
                                break;
                            } else {
                                echo "<p class='warning'>❌ No posts found containing '<strong>$term</strong>'</p>\n";
                            }
                        }
                        break;
                        
                    case 'check_vectors':
                        echo "<h2>🗄️ Check Vectors for Test Post</h2>\n";
                        
                        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
                        
                        // Find posts with مريم الظاعن
                        $posts = $wpdb->get_results($wpdb->prepare(
                            "SELECT ID, post_title, post_content 
                            FROM {$wpdb->posts} 
                            WHERE (post_title LIKE %s OR post_content LIKE %s) 
                            AND post_status = 'publish' 
                            LIMIT 3",
                            '%مريم%',
                            '%مريم%'
                        ));
                        
                        foreach ($posts as $post) {
                            echo "<h3>Post: {$post->post_title} (ID: {$post->ID})</h3>\n";
                            
                            // Check vectors for this post
                            $vectors = $wpdb->get_results($wpdb->prepare(
                                "SELECT id, chunk_index, content, vector_id, created_at 
                                FROM $vectors_table 
                                WHERE post_id = %d 
                                ORDER BY chunk_index",
                                $post->ID
                            ));
                            
                            if (!empty($vectors)) {
                                echo "<p class='success'>✅ Found " . count($vectors) . " vectors for this post:</p>\n";
                                foreach ($vectors as $vector) {
                                    echo "<div class='highlight'>";
                                    echo "<strong>Vector ID:</strong> {$vector->id}<br>";
                                    echo "<strong>Chunk Index:</strong> {$vector->chunk_index}<br>";
                                    echo "<strong>Vector ID:</strong> {$vector->vector_id}<br>";
                                    echo "<strong>Created:</strong> {$vector->created_at}<br>";
                                    echo "<strong>Content:</strong> " . substr($vector->content, 0, 300) . "...";
                                    echo "</div>\n";
                                }
                            } else {
                                echo "<p class='error'>❌ No vectors found for this post!</p>\n";
                            }
                        }
                        break;
                        
                    case 'test_rag_query':
                        echo "<h2>🧪 Test RAG Query</h2>\n";
                        
                        if (class_exists('WP_GPT_RAG_Chat\RAG_Handler')) {
                            $rag_handler = new WP_GPT_RAG_Chat\RAG_Handler();
                            
                            $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                            echo "<p class='info'>Testing query: <span class='arabic-text'><strong>$test_query</strong></span></p>\n";
                            
                            $sources = $rag_handler->retrieve_sources($test_query, 5);
                            
                            if (!empty($sources)) {
                                echo "<p class='success'>✅ RAG found " . count($sources) . " sources:</p>\n";
                                foreach ($sources as $i => $source) {
                                    echo "<div class='highlight'>";
                                    echo "<strong>Source " . ($i + 1) . " (Score: " . ($source['score'] ?? 'N/A') . "):</strong><br>";
                                    echo "<strong>Post ID:</strong> " . ($source['post_id'] ?? 'N/A') . "<br>";
                                    echo "<strong>Content:</strong> " . substr($source['content'], 0, 400) . "...";
                                    echo "</div>\n";
                                }
                            } else {
                                echo "<p class='error'>❌ RAG found NO sources for this query!</p>\n";
                                echo "<p class='warning'>This explains why the AI says it doesn't have the information.</p>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ RAG_Handler class not found</p>\n";
                        }
                        break;
                        
                    case 'compare_indexing_methods':
                        echo "<h2>⚖️ Compare Indexing Methods</h2>\n";
                        
                        echo "<h3>Bulk Indexing Process:</h3>\n";
                        echo "<ol>\n";
                        echo "<li>Gets posts using <code>get_posts()</code> with filters</li>\n";
                        echo "<li>Processes posts in batches</li>\n";
                        echo "<li>Uses <code>Indexing::index_post()</code> method</li>\n";
                        echo "<li>May have different content processing</li>\n";
                        echo "</ol>\n";
                        
                        echo "<h3>Manual Re-index Process:</h3>\n";
                        echo "<ol>\n";
                        echo "<li>Gets specific post by ID</li>\n";
                        echo "<li>Uses <code>Indexing::index_post()</code> method</li>\n";
                        echo "<li>May have different content processing</li>\n";
                        echo "<li>Processes content immediately</li>\n";
                        echo "</ol>\n";
                        
                        echo "<h3>Possible Differences:</h3>\n";
                        echo "<ul>\n";
                        echo "<li><strong>Content Processing:</strong> Different chunking or content extraction</li>\n";
                        echo "<li><strong>Post Filters:</strong> Bulk indexing might filter out some posts</li>\n";
                        echo "<li><strong>Timing:</strong> Bulk indexing might have timing issues</li>\n";
                        echo "<li><strong>Error Handling:</strong> Bulk indexing might fail silently</li>\n";
                        echo "<li><strong>Vector Storage:</strong> Different vector storage methods</li>\n";
                        echo "</ul>\n";
                        break;
                        
                    case 'check_bulk_indexing_logs':
                        echo "<h2>📋 Check Bulk Indexing Logs</h2>\n";
                        
                        // Check indexing queue
                        $queue_table = $wpdb->prefix . 'wp_gpt_rag_indexing_queue';
                        $queue_items = $wpdb->get_results("
                            SELECT post_id, status, created_at, updated_at, error_message
                            FROM $queue_table 
                            WHERE status IN ('pending', 'failed', 'completed')
                            ORDER BY created_at DESC 
                            LIMIT 10
                        ");
                        
                        if (!empty($queue_items)) {
                            echo "<p class='info'>📋 Recent indexing queue items:</p>\n";
                            echo "<table>\n";
                            echo "<tr><th>Post ID</th><th>Status</th><th>Created</th><th>Updated</th><th>Error</th></tr>\n";
                            foreach ($queue_items as $item) {
                                echo "<tr>";
                                echo "<td>{$item->post_id}</td>";
                                echo "<td>{$item->status}</td>";
                                echo "<td>{$item->created_at}</td>";
                                echo "<td>{$item->updated_at}</td>";
                                echo "<td>" . ($item->error_message ?: '-') . "</td>";
                                echo "</tr>\n";
                            }
                            echo "</table>\n";
                        } else {
                            echo "<p class='warning'>⚠️ No items in indexing queue</p>\n";
                        }
                        
                        // Check for posts with مريم in the queue
                        $maryam_queue = $wpdb->get_results($wpdb->prepare(
                            "SELECT q.post_id, q.status, p.post_title, q.error_message
                            FROM $queue_table q
                            LEFT JOIN {$wpdb->posts} p ON q.post_id = p.ID
                            WHERE p.post_title LIKE %s OR p.post_content LIKE %s
                            ORDER BY q.created_at DESC",
                            '%مريم%',
                            '%مريم%'
                        ));
                        
                        if (!empty($maryam_queue)) {
                            echo "<h3>Posts with 'مريم' in Indexing Queue:</h3>\n";
                            echo "<table>\n";
                            echo "<tr><th>Post ID</th><th>Title</th><th>Status</th><th>Error</th></tr>\n";
                            foreach ($maryam_queue as $item) {
                                echo "<tr>";
                                echo "<td>{$item->post_id}</td>";
                                echo "<td>{$item->post_title}</td>";
                                echo "<td>{$item->status}</td>";
                                echo "<td>" . ($item->error_message ?: '-') . "</td>";
                                echo "</tr>\n";
                            }
                            echo "</table>\n";
                        } else {
                            echo "<p class='info'>ℹ️ No posts with 'مريم' found in indexing queue</p>\n";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Investigation Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="find_test_post">
            <button type="submit" class="button">🔍 Find Test Post</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_vectors">
            <button type="submit" class="button">🗄️ Check Vectors</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="test_rag_query">
            <button type="submit" class="button">🧪 Test RAG Query</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="compare_indexing_methods">
            <button type="submit" class="button">⚖️ Compare Methods</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="check_bulk_indexing_logs">
            <button type="submit" class="button">📋 Check Bulk Logs</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-indexing" class="button" target="_blank">📚 Indexing Page</a>
            <a href="/wp-admin/admin.php?page=wp-gpt-rag-chat-settings" class="button" target="_blank">⚙️ Settings</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

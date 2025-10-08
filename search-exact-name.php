<?php
/**
 * Search for Exact Name: فاروق عبدالعزيز
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/search-exact-name.php
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
    <title>Search Exact Name: فاروق عبدالعزيز</title>
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
        .search-box { background: #e7f3ff; border: 2px solid #0073aa; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Search Exact Name: فاروق عبدالعزيز</h1>
        
        <div class="search-box">
            <h2>🎯 Target Search</h2>
            <p class="arabic-text"><strong>فاروق عبدالعزيز</strong></p>
            <p class="info">Searching for this exact person in the database and content.</p>
        </div>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="results">
                <?php
                switch ($_POST['action']) {
                    case 'search_exact_name':
                        echo "<h2>🔍 Exact Name Search</h2>\n";
                        
                        global $wpdb;
                        
                        // Search for exact name in posts
                        $exact_posts = $wpdb->get_results($wpdb->prepare("
                            SELECT ID, post_title, post_content 
                            FROM {$wpdb->posts} 
                            WHERE (post_title LIKE %s OR post_content LIKE %s) 
                            AND post_status = 'publish'
                            LIMIT 10
                        ", '%فاروق عبدالعزيز%', '%فاروق عبدالعزيز%'));
                        
                        if (!empty($exact_posts)) {
                            echo "<p class='success'>✅ Found " . count($exact_posts) . " posts with exact name 'فاروق عبدالعزيز':</p>\n";
                            foreach ($exact_posts as $post) {
                                echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                echo "<strong>Post ID:</strong> " . $post->ID . "<br>";
                                echo "<strong>Title:</strong> " . $post->post_title . "<br>";
                                echo "<strong>Content Preview:</strong> " . substr(strip_tags($post->post_content), 0, 300) . "...";
                                echo "</div>\n";
                            }
                        } else {
                            echo "<p class='error'>❌ No posts found with exact name 'فاروق عبدالعزيز'</p>\n";
                        }
                        
                        // Search in vectors table
                        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
                        $exact_vectors = $wpdb->get_results($wpdb->prepare("
                            SELECT * FROM $vectors_table 
                            WHERE content LIKE %s 
                            LIMIT 10
                        ", '%فاروق عبدالعزيز%'));
                        
                        if (!empty($exact_vectors)) {
                            echo "<h3>📊 Vectors with Exact Name</h3>";
                            echo "<p class='success'>✅ Found " . count($exact_vectors) . " vectors with exact name 'فاروق عبدالعزيز':</p>\n";
                            foreach ($exact_vectors as $vector) {
                                echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                echo "<strong>Vector ID:</strong> " . $vector->id . "<br>";
                                echo "<strong>Post ID:</strong> " . $vector->post_id . "<br>";
                                echo "<strong>Content:</strong> " . substr($vector->content, 0, 400) . "...";
                                echo "</div>\n";
                            }
                        } else {
                            echo "<h3>📊 Vectors with Exact Name</h3>";
                            echo "<p class='error'>❌ No vectors found with exact name 'فاروق عبدالعزيز'</p>\n";
                        }
                        break;
                        
                    case 'search_variations':
                        echo "<h2>🔍 Search Name Variations</h2>\n";
                        
                        global $wpdb;
                        
                        // Search for variations
                        $variations = [
                            'فاروق عبد العزيز',
                            'فاروق عبدالعزيز',
                            'فاروق عبدالعزيز',
                            'فاروق عبد العزيز',
                            'عبدالعزيز فاروق',
                            'عبد العزيز فاروق'
                        ];
                        
                        foreach ($variations as $variation) {
                            echo "<h3>Searching: <span class='arabic-text'>$variation</span></h3>";
                            
                            $posts = $wpdb->get_results($wpdb->prepare("
                                SELECT ID, post_title, post_content 
                                FROM {$wpdb->posts} 
                                WHERE (post_title LIKE %s OR post_content LIKE %s) 
                                AND post_status = 'publish'
                                LIMIT 5
                            ", "%$variation%", "%$variation%"));
                            
                            if (!empty($posts)) {
                                echo "<p class='success'>✅ Found " . count($posts) . " posts</p>";
                                foreach ($posts as $post) {
                                    echo "<div style='background: #e7f3ff; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                    echo "<strong>Post ID:</strong> " . $post->ID . "<br>";
                                    echo "<strong>Title:</strong> " . $post->post_title . "<br>";
                                    echo "<strong>Content:</strong> " . substr(strip_tags($post->post_content), 0, 200) . "...";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p class='error'>❌ No posts found</p>";
                            }
                        }
                        break;
                        
                    case 'search_individual_names':
                        echo "<h2>🔍 Search Individual Names</h2>\n";
                        
                        global $wpdb;
                        
                        // Search for فاروق alone
                        echo "<h3>Searching: <span class='arabic-text'>فاروق</span></h3>";
                        $farouk_posts = $wpdb->get_results($wpdb->prepare("
                            SELECT ID, post_title, post_content 
                            FROM {$wpdb->posts} 
                            WHERE (post_title LIKE %s OR post_content LIKE %s) 
                            AND post_status = 'publish'
                            LIMIT 5
                        ", '%فاروق%', '%فاروق%'));
                        
                        if (!empty($farouk_posts)) {
                            echo "<p class='success'>✅ Found " . count($farouk_posts) . " posts with 'فاروق'</p>";
                            foreach ($farouk_posts as $post) {
                                echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                echo "<strong>Post ID:</strong> " . $post->ID . "<br>";
                                echo "<strong>Title:</strong> " . $post->post_title . "<br>";
                                echo "<strong>Content:</strong> " . substr(strip_tags($post->post_content), 0, 200) . "...";
                                echo "</div>";
                            }
                        } else {
                            echo "<p class='error'>❌ No posts found with 'فاروق'</p>";
                        }
                        
                        // Search for عبدالعزيز alone
                        echo "<h3>Searching: <span class='arabic-text'>عبدالعزيز</span></h3>";
                        $abdulaziz_posts = $wpdb->get_results($wpdb->prepare("
                            SELECT ID, post_title, post_content 
                            FROM {$wpdb->posts} 
                            WHERE (post_title LIKE %s OR post_content LIKE %s) 
                            AND post_status = 'publish'
                            LIMIT 5
                        ", '%عبدالعزيز%', '%عبدالعزيز%'));
                        
                        if (!empty($abdulaziz_posts)) {
                            echo "<p class='success'>✅ Found " . count($abdulaziz_posts) . " posts with 'عبدالعزيز'</p>";
                            foreach ($abdulaziz_posts as $post) {
                                echo "<div style='background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
                                echo "<strong>Post ID:</strong> " . $post->ID . "<br>";
                                echo "<strong>Title:</strong> " . $post->post_title . "<br>";
                                echo "<strong>Content:</strong> " . substr(strip_tags($post->post_content), 0, 200) . "...";
                                echo "</div>";
                            }
                        } else {
                            echo "<p class='error'>❌ No posts found with 'عبدالعزيز'</p>";
                        }
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <h2>🛠️ Search Actions</h2>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="search_exact_name">
            <button type="submit" class="button">🔍 Search Exact Name</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="search_variations">
            <button type="submit" class="button">🔍 Search Variations</button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="search_individual_names">
            <button type="submit" class="button">🔍 Search Individual Names</button>
        </form>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-optimized-rag.php" class="button">🚀 Test Optimized RAG</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-new-query.php" class="button">🧪 Test New Query</a>
            <a href="/" class="button" target="_blank">🌐 Frontend Chat</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

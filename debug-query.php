<?php
/**
 * Debug Query Tool
 * 
 * This tool helps debug why queries aren't returning results
 * URL: http://localhost/wp/wp-content/plugins/wp-nuwab-chatgpt/debug-query.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Unauthorized access');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Query Debug Tool</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 20px;
            background: #f0f0f1;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1d2327;
            border-bottom: 2px solid #2271b1;
            padding-bottom: 10px;
        }
        .test-form {
            background: #f6f7f7;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .test-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1d2327;
        }
        .test-form input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
        }
        .test-form button {
            background: #2271b1;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        .test-form button:hover {
            background: #135e96;
        }
        .results {
            margin-top: 30px;
        }
        .result-section {
            background: #f6f7f7;
            padding: 20px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #2271b1;
        }
        .result-section h3 {
            margin-top: 0;
            color: #1d2327;
        }
        pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 14px;
        }
        .success {
            border-left-color: #00a32a;
            background: #f0f6fc;
        }
        .error {
            border-left-color: #d63638;
            background: #fcf0f1;
        }
        .warning {
            border-left-color: #dba617;
            background: #fcf9e8;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #2271b1;
            color: white;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
        }
        .stat-card h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Query Debug Tool</h1>
        <p>Test queries to see why they're not returning results from your indexed content.</p>

        <?php
        // Get plugin settings
        $settings = get_option('wp_gpt_rag_chat_settings', []);
        $pinecone_configured = !empty($settings['pinecone_api_key']) && !empty($settings['pinecone_environment']);
        $openai_configured = !empty($settings['openai_api_key']);
        
        // Get stats
        global $wpdb;
        $indexed_table = $wpdb->prefix . 'wp_gpt_rag_chat_indexed';
        $total_vectors = 0;
        $total_posts = 0;
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$indexed_table'") == $indexed_table) {
            $total_vectors = (int) $wpdb->get_var("SELECT COUNT(*) FROM $indexed_table");
            $total_posts = (int) $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $indexed_table");
        }
        ?>

        <div class="stats">
            <div class="stat-card" style="background: <?php echo $pinecone_configured ? '#00a32a' : '#d63638'; ?>">
                <h4>Pinecone Status</h4>
                <p class="number"><?php echo $pinecone_configured ? '✓' : '✗'; ?></p>
            </div>
            <div class="stat-card" style="background: <?php echo $openai_configured ? '#00a32a' : '#d63638'; ?>">
                <h4>OpenAI Status</h4>
                <p class="number"><?php echo $openai_configured ? '✓' : '✗'; ?></p>
            </div>
            <div class="stat-card">
                <h4>Total Vectors</h4>
                <p class="number"><?php echo number_format($total_vectors); ?></p>
            </div>
            <div class="stat-card">
                <h4>Indexed Posts</h4>
                <p class="number"><?php echo number_format($total_posts); ?></p>
            </div>
        </div>

        <div class="test-form">
            <form method="post">
                <label>Enter Your Test Query:</label>
                <input type="text" name="test_query" placeholder="e.g., ما هو الموضوع المتوفر؟" value="<?php echo esc_attr($_POST['test_query'] ?? ''); ?>" required>
                <button type="submit">🔍 Test Query</button>
            </form>
        </div>

        <?php
        if (isset($_POST['test_query']) && !empty($_POST['test_query'])) {
            $query = sanitize_text_field($_POST['test_query']);
            
            echo '<div class="results">';
            echo '<h2>📊 Debug Results</h2>';
            
            try {
                // Load required classes
                $pinecone = new \WP_GPT_RAG_Chat\Pinecone($settings);
                $openai = new \WP_GPT_RAG_Chat\OpenAI($settings);
                
                // Step 1: Create embedding
                echo '<div class="result-section">';
                echo '<h3>Step 1: Creating Query Embedding</h3>';
                echo '<p><strong>Query:</strong> ' . esc_html($query) . '</p>';
                
                $embedding = $openai->create_embeddings([$query]);
                if ($embedding && !empty($embedding[0])) {
                    echo '<p class="success">✓ Embedding created successfully</p>';
                    echo '<p><strong>Dimensions:</strong> ' . count($embedding[0]) . '</p>';
                    echo '<p><strong>First 5 values:</strong> ' . implode(', ', array_slice($embedding[0], 0, 5)) . '...</p>';
                } else {
                    echo '<p class="error">✗ Failed to create embedding</p>';
                }
                echo '</div>';
                
                // Step 2: Query Pinecone
                if ($embedding && !empty($embedding[0])) {
                    echo '<div class="result-section">';
                    echo '<h3>Step 2: Querying Pinecone</h3>';
                    
                    $results = $pinecone->query_vectors($embedding[0], 10);
                    
                    if ($results && !empty($results)) {
                        echo '<p class="success">✓ Found ' . count($results) . ' matches in Pinecone</p>';
                        echo '<h4>Top Matches:</h4>';
                        echo '<table style="width:100%; border-collapse: collapse;">';
                        echo '<tr style="background:#f0f0f1;"><th style="padding:8px; text-align:left;">Score</th><th style="padding:8px; text-align:left;">Post ID</th><th style="padding:8px; text-align:left;">Chunk</th><th style="padding:8px; text-align:left;">Title</th></tr>';
                        
                        foreach (array_slice($results, 0, 5) as $match) {
                            $post_id = $match['metadata']['post_id'] ?? 'N/A';
                            $chunk_index = $match['metadata']['chunk_index'] ?? 'N/A';
                            $post = get_post($post_id);
                            $title = $post ? $post->post_title : 'Unknown';
                            
                            echo '<tr style="border-bottom:1px solid #ddd;">';
                            echo '<td style="padding:8px;"><strong>' . number_format($match['score'], 4) . '</strong></td>';
                            echo '<td style="padding:8px;">' . esc_html($post_id) . '</td>';
                            echo '<td style="padding:8px;">' . esc_html($chunk_index) . '</td>';
                            echo '<td style="padding:8px;">' . esc_html($title) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        
                        // Show threshold info
                        $threshold = $settings['similarity_threshold'] ?? 0.7;
                        echo '<p style="margin-top:15px;"><strong>Similarity Threshold:</strong> ' . $threshold . '</p>';
                        
                        $above_threshold = array_filter($results, function($match) use ($threshold) {
                            return $match['score'] >= $threshold;
                        });
                        
                        if (count($above_threshold) == 0) {
                            echo '<p class="warning">⚠️ <strong>WARNING:</strong> No matches above similarity threshold (' . $threshold . ')</p>';
                            echo '<p>This is why you\'re getting "I don\'t have that information"</p>';
                            echo '<p><strong>Recommendation:</strong> Lower the similarity threshold in plugin settings or reindex with better chunking.</p>';
                        } else {
                            echo '<p class="success">✓ ' . count($above_threshold) . ' matches above threshold</p>';
                        }
                    } else {
                        echo '<p class="error">✗ No matches found in Pinecone</p>';
                    }
                    echo '</div>';
                    
                    // Step 3: Build context
                    if ($results && !empty($above_threshold)) {
                        echo '<div class="result-section success">';
                        echo '<h3>Step 3: Context Retrieved</h3>';
                        
                        $context_pieces = [];
                        foreach ($above_threshold as $match) {
                            $post_id = $match['metadata']['post_id'] ?? null;
                            if ($post_id) {
                                $post = get_post($post_id);
                                if ($post) {
                                    $context_pieces[] = $post->post_title . ': ' . wp_trim_words($post->post_content, 50);
                                }
                            }
                        }
                        
                        if (!empty($context_pieces)) {
                            echo '<p class="success">✓ Context built successfully</p>';
                            echo '<h4>Context Preview:</h4>';
                            echo '<pre>' . esc_html(implode("\n\n", array_slice($context_pieces, 0, 2))) . '</pre>';
                        }
                        echo '</div>';
                    }
                }
                
            } catch (\Exception $e) {
                echo '<div class="result-section error">';
                echo '<h3>❌ Error Occurred</h3>';
                echo '<p><strong>Message:</strong> ' . esc_html($e->getMessage()) . '</p>';
                echo '<pre>' . esc_html($e->getTraceAsString()) . '</pre>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        ?>

        <div class="result-section warning" style="margin-top: 30px;">
            <h3>💡 Common Issues & Solutions</h3>
            <ul style="line-height: 1.8;">
                <li><strong>Low Similarity Scores:</strong> Try lowering the similarity threshold in Settings → Advanced → Similarity Threshold (try 0.5 instead of 0.7)</li>
                <li><strong>No Matches Found:</strong> Content might not be properly indexed. Try reindexing from the Indexing page.</li>
                <li><strong>Wrong Language:</strong> Make sure your query language matches your indexed content.</li>
                <li><strong>Embedding Dimensions Mismatch:</strong> Ensure Pinecone index dimensions match the embedding model.</li>
            </ul>
        </div>
    </div>
</body>
</html>


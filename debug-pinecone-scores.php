<?php
/**
 * Debug Pinecone Scores
 * Access via: https://localhost/wp/wp-content/plugins/chatbot-nuwab-2/debug-pinecone-scores.php
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
    <title>Debug Pinecone Scores</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        .info { color: #0073aa; }
        h1 { color: #23282d; }
        h2 { color: #0073aa; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .results { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 3px; }
        pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
        .arabic-text { direction: rtl; text-align: right; font-family: 'Arial', 'Tahoma', sans-serif; }
        .score-high { color: #46b450; font-weight: bold; }
        .score-medium { color: #ffb900; font-weight: bold; }
        .score-low { color: #dc3232; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Debug Pinecone Scores</h1>
        
        <div class="results">
            <?php
            if (class_exists('WP_GPT_RAG_Chat\Pinecone') && class_exists('WP_GPT_RAG_Chat\OpenAI')) {
                $pinecone = new WP_GPT_RAG_Chat\Pinecone();
                $openai = new WP_GPT_RAG_Chat\OpenAI();
                
                $test_query = 'هل هناك نائب اسمه مريم الظاعن';
                echo "<h2>🔍 Testing Query: <span class='arabic-text'>$test_query</span></h2>";
                
                try {
                    // Generate embedding
                    $embeddings = $openai->create_embeddings([$test_query]);
                    if (!empty($embeddings) && isset($embeddings[0])) {
                        $query_vector = $embeddings[0];
                        
                        // Query Pinecone with high top_k to get more results
                        $result = $pinecone->query_vectors($query_vector, 20);
                        
                        echo "<h3>📊 Raw Pinecone Results (Top 20)</h3>";
                        
                        if (!empty($result['matches'])) {
                            echo "<p class='info'>Found " . count($result['matches']) . " matches from Pinecone</p>";
                            
                            echo "<table style='width: 100%; border-collapse: collapse;'>";
                            echo "<tr style='background: #f0f0f0;'>";
                            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Rank</th>";
                            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Score</th>";
                            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Vector ID</th>";
                            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Post ID</th>";
                            echo "<th style='border: 1px solid #ddd; padding: 8px;'>Content Preview</th>";
                            echo "</tr>";
                            
                            foreach ($result['matches'] as $i => $match) {
                                $score = $match['score'] ?? 0;
                                $score_class = $score >= 0.7 ? 'score-high' : ($score >= 0.5 ? 'score-medium' : 'score-low');
                                
                                echo "<tr>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($i + 1) . "</td>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;' class='$score_class'>" . number_format($score, 4) . "</td>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($match['id'] ?? 'N/A') . "</td>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($match['metadata']['post_id'] ?? 'N/A') . "</td>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . substr($match['metadata']['content'] ?? 'No content', 0, 100) . "...</td>";
                                echo "</tr>";
                            }
                            
                            echo "</table>";
                            
                            // Show statistics
                            $scores = array_column($result['matches'], 'score');
                            $max_score = max($scores);
                            $min_score = min($scores);
                            $avg_score = array_sum($scores) / count($scores);
                            
                            echo "<h3>📈 Score Statistics</h3>";
                            echo "<p class='info'>Highest Score: <span class='score-high'>" . number_format($max_score, 4) . "</span></p>";
                            echo "<p class='info'>Lowest Score: <span class='score-low'>" . number_format($min_score, 4) . "</span></p>";
                            echo "<p class='info'>Average Score: <span class='score-medium'>" . number_format($avg_score, 4) . "</span></p>";
                            
                            // Check current threshold
                            $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                            $current_threshold = $settings['similarity_threshold'] ?? 0.7;
                            
                            echo "<h3>🎯 Threshold Analysis</h3>";
                            echo "<p class='info'>Current Threshold: <strong>$current_threshold</strong></p>";
                            
                            $above_threshold = array_filter($scores, function($score) use ($current_threshold) {
                                return $score >= $current_threshold;
                            });
                            
                            echo "<p class='info'>Matches above threshold: <strong>" . count($above_threshold) . "</strong></p>";
                            
                            if (count($above_threshold) == 0) {
                                echo "<p class='warning'>⚠️ <strong>No matches pass the current threshold!</strong></p>";
                                echo "<p class='info'>Recommended threshold: <strong>" . number_format($max_score - 0.1, 2) . "</strong> (slightly below highest score)</p>";
                            }
                            
                            // Test with lower threshold
                            echo "<h3>🧪 Test with Lower Threshold</h3>";
                            $test_thresholds = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9];
                            
                            foreach ($test_thresholds as $test_threshold) {
                                $passing = array_filter($scores, function($score) use ($test_threshold) {
                                    return $score >= $test_threshold;
                                });
                                
                                $count = count($passing);
                                $status = $count > 0 ? 'success' : 'error';
                                echo "<p class='$status'>Threshold $test_threshold: <strong>$count</strong> matches pass</p>";
                            }
                            
                        } else {
                            echo "<p class='error'>❌ No matches found in Pinecone</p>";
                        }
                        
                    } else {
                        echo "<p class='error'>❌ Failed to generate embedding</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
                }
                
            } else {
                echo "<p class='error'>❌ Required classes not found</p>";
            }
            ?>
        </div>
        
        <h2>🔗 Quick Links</h2>
        <p>
            <a href="/wp-content/plugins/chatbot-nuwab-2/debug-rag-deep.php" class="button">🔍 Deep RAG Debug</a>
            <a href="/wp-content/plugins/chatbot-nuwab-2/test-rag-fix.php" class="button">🧪 Test RAG Fix</a>
        </p>
        
        <p><strong>Current Time:</strong> <?php echo current_time('mysql'); ?></p>
    </div>
</body>
</html>

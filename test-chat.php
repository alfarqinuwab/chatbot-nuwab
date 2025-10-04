<?php
/**
 * Frontend Chat Testing Page
 * 
 * Access this page to test the chat functionality with your indexed content.
 * URL: http://localhost/wp/wp-content/plugins/wp-nuwab-chatgpt/test-chat.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Get the header
get_header();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat Testing Page - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }
        .test-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #667eea;
        }
        .test-header h1 {
            color: #667eea;
            margin: 0 0 10px 0;
            font-size: 36px;
        }
        .test-header p {
            color: #666;
            font-size: 18px;
            margin: 0;
        }
        .test-section {
            margin-bottom: 40px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .test-section h2 {
            color: #333;
            margin-top: 0;
            font-size: 24px;
        }
        .test-section h3 {
            color: #667eea;
            font-size: 18px;
            margin-top: 20px;
        }
        .sample-questions {
            background: white;
            padding: 20px;
            border-radius: 6px;
            margin-top: 15px;
        }
        .sample-questions ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sample-questions li {
            padding: 12px 15px;
            margin: 8px 0;
            background: #e3f2fd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 3px solid #667eea;
        }
        .sample-questions li:hover {
            background: #bbdefb;
            transform: translateX(5px);
        }
        .sample-questions li:before {
            content: "💬 ";
            margin-right: 8px;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
        }
        .instructions h4 {
            color: #856404;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .instructions h4:before {
            content: "ℹ️";
            font-size: 24px;
        }
        .instructions ol {
            margin: 15px 0;
            padding-left: 25px;
        }
        .instructions li {
            margin: 10px 0;
            line-height: 1.6;
        }
        .stats-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-card h4 {
            color: #666;
            margin: 0 0 10px 0;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .stat-card .number {
            color: #667eea;
            font-size: 36px;
            font-weight: bold;
            margin: 0;
        }
        .chat-trigger {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            animation: pulse 2s infinite;
            z-index: 9999;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .debug-section {
            background: #263238;
            color: #aed581;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .debug-section h4 {
            color: #fff;
            margin-top: 0;
        }
        .debug-section pre {
            background: #1e272e;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            color: #aed581;
        }
    </style>
</head>
<body>

<div class="test-container">
    <div class="test-header">
        <h1>🤖 Frontend Chat Testing Page</h1>
        <p>Test your indexed content with the AI chat assistant</p>
    </div>

    <?php
    // Get indexed stats using the plugin's Stats class
    $stats = [
        'total_vectors' => 0,
        'total_posts' => 0,
        'recent_activity' => 0
    ];
    
    // Try to get stats from the Stats class
    if (class_exists('WP_GPT_RAG_Chat\Stats')) {
        $stats_instance = new WP_GPT_RAG_Chat\Stats();
        $plugin_stats = $stats_instance->get_stats();
        if ($plugin_stats) {
            $stats['total_vectors'] = $plugin_stats['total_vectors'] ?? 0;
            $stats['total_posts'] = $plugin_stats['total_posts'] ?? 0;
            $stats['recent_activity'] = $plugin_stats['recent_activity'] ?? 0;
        }
    }
    
    // Fallback: Query database directly
    if ($stats['total_vectors'] == 0) {
        global $wpdb;
        $indexed_table = $wpdb->prefix . 'wp_gpt_rag_chat_indexed';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$indexed_table'") == $indexed_table;
        
        if ($table_exists) {
            $stats['total_posts'] = (int) $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $indexed_table");
            $stats['total_vectors'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $indexed_table");
            $stats['recent_activity'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $indexed_table WHERE indexed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        }
    }
    ?>

    <div class="test-section">
        <h2>📊 Current Indexing Stats</h2>
        
        <?php if ($stats['total_vectors'] == 0): ?>
            <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="color: #856404; margin-top: 0;">⚠️ No Indexed Content Found</h3>
                <p style="margin: 10px 0;"><strong>This means:</strong></p>
                <ul style="margin: 10px 0; line-height: 1.8;">
                    <li>Either no posts have been indexed yet</li>
                    <li>Or the indexing process hasn't completed</li>
                    <li>Or there was an error during indexing</li>
                </ul>
                <p style="margin: 15px 0 0 0;"><strong>👉 Action Required:</strong></p>
                <ol style="margin: 10px 0; line-height: 1.8;">
                    <li>Go to <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" style="color: #667eea; text-decoration: underline;">Indexing Page</a></li>
                    <li>Click "<strong>Sync All</strong>" button</li>
                    <li>Wait for indexing to complete</li>
                    <li>Come back to this page and refresh</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <div class="stats-box">
            <div class="stat-card">
                <h4>Total Vectors</h4>
                <p class="number"><?php echo number_format($stats['total_vectors']); ?></p>
            </div>
            <div class="stat-card">
                <h4>Indexed Posts</h4>
                <p class="number"><?php echo number_format($stats['total_posts']); ?></p>
            </div>
            <div class="stat-card">
                <h4>Recent Activity</h4>
                <p class="number"><?php echo number_format($stats['recent_activity']); ?></p>
            </div>
        </div>
    </div>

    <div class="test-section">
        <h2>🎯 How to Test</h2>
        <div class="instructions">
            <h4>Follow These Steps:</h4>
            <ol>
                <li><strong>Open the Chat Widget:</strong> Click the floating chat button in the bottom-right corner</li>
                <li><strong>Ask a Question:</strong> Type a question related to your indexed content</li>
                <li><strong>Check the Response:</strong> The AI should provide an answer based on your posts</li>
                <li><strong>Verify Sources:</strong> Look for citations and links to your actual posts</li>
                <li><strong>Test Different Topics:</strong> Try various questions to test different posts</li>
            </ol>
        </div>
    </div>

    <div class="test-section">
        <h2>💡 Sample Questions to Try</h2>
        <p>Click any question below to copy it (then paste in the chat):</p>
        
        <div class="sample-questions">
            <h3>General Questions:</h3>
            <ul>
                <li onclick="copyToClipboard(this)">What topics are covered on this website?</li>
                <li onclick="copyToClipboard(this)">Can you summarize the main content?</li>
                <li onclick="copyToClipboard(this)">What information do you have available?</li>
            </ul>

            <h3>Specific Content Questions:</h3>
            <ul>
                <?php
                // Get some indexed posts for sample questions
                $recent_posts = $wpdb->get_results("
                    SELECT DISTINCT post_id 
                    FROM $indexed_table 
                    ORDER BY indexed_at DESC 
                    LIMIT 5
                ");
                
                foreach ($recent_posts as $indexed_post) {
                    $post = get_post($indexed_post->post_id);
                    if ($post) {
                        echo '<li onclick="copyToClipboard(this)">Tell me about ' . esc_html($post->post_title) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="test-section">
        <h2>🔍 Debugging Tips</h2>
        <div class="debug-section">
            <h4>Open Browser Console (F12) and look for:</h4>
            <pre>
✓ CORNUWB: Detected language: en
✓ CORNUWB: Sending message to server
✓ CORNUWB: Response received

Check Network Tab:
→ Look for "wp_gpt_rag_chat_query" request
→ Inspect Request Payload (your question)
→ Check Response (AI answer + context)
            </pre>
        </div>
    </div>

    <div class="test-section">
        <h2>✅ What to Look For</h2>
        <ul style="line-height: 2; font-size: 16px;">
            <li>✓ Chat widget appears on the page</li>
            <li>✓ Questions are processed without errors</li>
            <li>✓ Responses are relevant to your indexed content</li>
            <li>✓ Sources are cited with post titles/links</li>
            <li>✓ Language detection works (Arabic/English)</li>
            <li>✓ Responses are in the same language as the question</li>
        </ul>
    </div>
</div>

<div class="chat-trigger" onclick="alert('Click the chat widget button (usually in bottom-right corner) to start chatting!')">
    💬 Start Testing Chat
</div>

<script>
function copyToClipboard(element) {
    const text = element.textContent.replace('💬 ', '');
    navigator.clipboard.writeText(text).then(function() {
        const original = element.textContent;
        element.textContent = '✓ Copied! Now paste in chat';
        element.style.background = '#c8e6c9';
        setTimeout(function() {
            element.textContent = original;
            element.style.background = '#e3f2fd';
        }, 2000);
    }).catch(function(err) {
        alert('To copy: ' + text);
    });
}

// Log to console
console.log('%c🤖 Chat Testing Page Loaded', 'color: #667eea; font-size: 20px; font-weight: bold;');
console.log('%cReady to test your indexed content!', 'color: #666; font-size: 14px;');
console.log('%cIndexed Posts: <?php echo $stats['total_posts']; ?>', 'color: #4caf50; font-size: 14px;');
console.log('%cTotal Vectors: <?php echo $stats['total_vectors']; ?>', 'color: #4caf50; font-size: 14px;');
</script>

<?php wp_footer(); ?>
</body>
</html>


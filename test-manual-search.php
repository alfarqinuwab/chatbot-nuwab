<!DOCTYPE html>
<html>
<head>
    <title>Test Manual Search Functionality</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2271b1; border-bottom: 3px solid #2271b1; padding-bottom: 15px; }
        h2 { color: #0073aa; margin-top: 30px; }
        .test-section { background: #f9f9f9; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1; border-radius: 4px; }
        .success { color: #00a32a; font-weight: bold; }
        .error { color: #d63638; font-weight: bold; }
        .info { color: #2271b1; }
        .warning { color: #dba617; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f1; font-weight: 600; }
        .icon { font-size: 20px; margin-right: 8px; }
        .test-action { margin: 20px 0; }
        .test-button { background: #2271b1; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-size: 14px; }
        .test-button:hover { background: #135e96; }
        .code-block { background: #23282d; color: #f0f0f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test Manual Search Functionality</h1>
        
        <?php
        // Load WordPress
        require_once('../../../wp-load.php');
        
        if (!current_user_can('manage_options')) {
            echo '<p class="error">⛔ You must be an administrator to access this page.</p>';
            exit;
        }
        
        echo '<div class="test-section">';
        echo '<h2>✅ Manual Search Section Added</h2>';
        
        echo '<p class="success">✅ <strong>Full-width manual re-indexing search box</strong> has been added!</p>';
        
        echo '<p class="info"><strong>Features:</strong></p>';
        echo '<ul>';
        echo '<li>✅ Search by <strong>Post ID</strong> or <strong>Title</strong></li>';
        echo '<li>✅ Filter by <strong>Post Type</strong> (All, Posts, Pages, Custom Post Types, PDFs)</li>';
        echo '<li>✅ Display search results with <strong>indexed status</strong></li>';
        echo '<li>✅ Show <strong>View link</strong> for each post</li>';
        echo '<li>✅ Individual <strong>Index Now</strong> or <strong>Re-index</strong> buttons</li>';
        echo '<li>✅ Real-time status updates after indexing</li>';
        echo '</ul>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>🎯 How to Use</h2>';
        
        echo '<ol>';
        echo '<li><strong>Search by Post ID:</strong> Enter a specific post ID (e.g., "123")</li>';
        echo '<li><strong>Search by Title:</strong> Enter part of a post title (e.g., "sample post")</li>';
        echo '<li><strong>Select Post Type:</strong> Choose "All Post Types" or a specific type</li>';
        echo '<li><strong>Click Search:</strong> Results will show with indexed status</li>';
        echo '<li><strong>Index Individual Posts:</strong> Click "Index Now" or "Re-index" buttons</li>';
        echo '</ol>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>📊 Search Results Display</h2>';
        
        echo '<p class="info">Each search result shows:</p>';
        echo '<ul>';
        echo '<li>✅ <strong>Post Title</strong> - The full title of the post</li>';
        echo '<li>✅ <strong>Post ID & Type</strong> - ID number and post type (POST, PAGE, etc.)</li>';
        echo '<li>✅ <strong>Excerpt</strong> - First 20 words of the post content</li>';
        echo '<li>✅ <strong>Indexed Status</strong> - Green "INDEXED" or Red "NOT INDEXED" badge</li>';
        echo '<li>✅ <strong>View Button</strong> - Opens the post in a new tab</li>';
        echo '<li>✅ <strong>Index/Re-index Button</strong> - Indexes the post immediately</li>';
        echo '</ul>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>🔧 Technical Details</h2>';
        
        echo '<table>';
        echo '<tr><th>Component</th><th>Status</th><th>Description</th></tr>';
        echo '<tr><td>Search Input</td><td class="success">✅ Added</td><td>Text input for post ID or title search</td></tr>';
        echo '<tr><td>Post Type Filter</td><td class="success">✅ Added</td><td>Dropdown to filter by post type</td></tr>';
        echo '<tr><td>Search Button</td><td class="success">✅ Added</td><td>Triggers AJAX search</td></tr>';
        echo '<tr><td>Results Display</td><td class="success">✅ Added</td><td>Shows search results with status</td></tr>';
        echo '<tr><td>Index Buttons</td><td class="success">✅ Added</td><td>Individual post indexing</td></tr>';
        echo '<tr><td>AJAX Handler</td><td class="success">✅ Exists</td><td>wp_gpt_rag_chat_search_content</td></tr>';
        echo '<tr><td>CSS Styling</td><td class="success">✅ Added</td><td>Full styling for search interface</td></tr>';
        echo '</table>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>🚀 Test the Functionality</h2>';
        
        echo '<div class="test-action">';
        echo '<button class="test-button" onclick="window.location.href=\'' . admin_url('admin.php?page=wp-gpt-rag-chat-indexing') . '\'">🔗 Go to Indexing Page</button>';
        echo '</div>';
        
        echo '<p class="info"><strong>What to test:</strong></p>';
        echo '<ul>';
        echo '<li>✅ Search for a post by ID (try "1" or any existing post ID)</li>';
        echo '<li>✅ Search for a post by title (try part of any post title)</li>';
        echo '<li>✅ Try different post types in the dropdown</li>';
        echo '<li>✅ Check that indexed status is displayed correctly</li>';
        echo '<li>✅ Test the "Index Now" or "Re-index" buttons</li>';
        echo '<li>✅ Verify that status updates after indexing</li>';
        echo '</ul>';
        
        echo '</div>';
        ?>
        
        <div class="test-action">
            <button class="test-button" onclick="window.location.href='<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>'">
                🚀 Go to Indexing Page Now
            </button>
        </div>
    </div>
</body>
</html>

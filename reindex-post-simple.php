<?php
/**
 * Simple script to re-index post 67855 using existing functionality
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo "<h1>Re-indexing Post 67855 (ميثاق العمل الوطني)</h1>";

try {
    // Initialize the indexing class
    $indexing = new \WP_GPT_RAG_Chat\Indexing();
    
    $post_id = 67855;
    
    echo "<h2>Step 1: Check Post</h2>";
    $post = get_post($post_id);
    if (!$post) {
        die("❌ Post {$post_id} not found!");
    }
    
    echo "✅ Post found: {$post->post_title}<br>";
    echo "Post type: {$post->post_type}<br>";
    echo "Post status: {$post->post_status}<br>";
    
    echo "<h2>Step 2: Re-index Post</h2>";
    echo "Starting re-indexing process...<br>";
    
    // Force re-index the post
    $results = $indexing->reindex_post($post_id);
    
    echo "<h3>✅ Re-indexing Results:</h3>";
    echo "Added: " . ($results['added'] ?? 0) . " vectors<br>";
    echo "Updated: " . ($results['updated'] ?? 0) . " vectors<br>";
    echo "Removed: " . ($results['removed'] ?? 0) . " vectors<br>";
    echo "Skipped: " . ($results['skipped'] ?? 0) . " vectors<br>";
    
    if (isset($results['message'])) {
        echo "Message: " . $results['message'] . "<br>";
    }
    
    echo "<h2>Step 3: Test Search</h2>";
    
    // Test the search
    $chat = new \WP_GPT_RAG_Chat\Chat();
    $response = $chat->process_query("ميثاق العمل الوطني", []);
    
    echo "Search response length: " . strlen($response) . " characters<br>";
    echo "Response preview: " . substr($response, 0, 300) . "...<br>";
    
    if (strpos($response, 'ميثاق العمل الوطني') !== false || 
        strpos($response, 'البحرين') !== false ||
        strpos($response, 'المشروع الإصلاحي') !== false) {
        echo "<h3>🎯 SUCCESS: Search now returns relevant content!</h3>";
    } else {
        echo "<h3>❌ Search still not working properly</h3>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<br><a href='javascript:history.back()'>← Back</a>";
?>


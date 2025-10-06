<?php
/**
 * Test current search status for ميثاق العمل الوطني
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin privileges required.');
}

echo "<h1>Current Search Test for: ميثاق العمل الوطني</h1>";

try {
    // Test the search
    $chat = new \WP_GPT_RAG_Chat\Chat();
    $query = "ميثاق العمل الوطني";
    
    echo "<h2>Testing Search Query: {$query}</h2>";
    
    $response = $chat->process_query($query, []);
    
    echo "<h3>Search Response:</h3>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
    echo nl2br(htmlspecialchars($response));
    echo "</div>";
    
    echo "<h3>Analysis:</h3>";
    echo "Response length: " . strlen($response) . " characters<br>";
    
    if (strpos($response, 'عذراً') !== false || strpos($response, 'غير متوفرة') !== false) {
        echo "❌ <strong>ISSUE:</strong> Search is returning generic fallback message<br>";
        echo "This means the content is not properly indexed in Pinecone<br>";
    } elseif (strpos($response, 'ميثاق العمل الوطني') !== false || 
              strpos($response, 'البحرين') !== false ||
              strpos($response, 'المشروع الإصلاحي') !== false) {
        echo "✅ <strong>SUCCESS:</strong> Search is returning relevant content!<br>";
    } else {
        echo "⚠️ <strong>UNCLEAR:</strong> Response doesn't clearly indicate success or failure<br>";
    }
    
    echo "<h2>Next Steps:</h2>";
    echo "If the search is not working, you need to:<br>";
    echo "1. Run the re-indexing script: <a href='reindex-post-simple.php'>reindex-post-simple.php</a><br>";
    echo "2. Or manually re-index post 67855 from the WordPress admin<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='javascript:history.back()'>← Back</a>";
?>

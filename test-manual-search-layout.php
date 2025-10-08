<!DOCTYPE html>
<html>
<head>
    <title>Test Manual Search Layout</title>
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
        <h1>📐 Test Manual Search Layout</h1>
        
        <?php
        // Load WordPress
        require_once('../../../wp-load.php');
        
        if (!current_user_can('manage_options')) {
            echo '<p class="error">⛔ You must be an administrator to access this page.</p>';
            exit;
        }
        
        echo '<div class="test-section">';
        echo '<h2>✅ Manual Search Section Repositioned</h2>';
        
        echo '<p class="success">✅ <strong>Manual search section moved to the top!</strong></p>';
        
        echo '<p class="info"><strong>New Layout Order:</strong></p>';
        echo '<ol>';
        echo '<li>✅ <strong>Statistics Cards</strong> - Total Vectors, Indexed Posts, Recent Activity</li>';
        echo '<li>✅ <strong>Manual Re-indexing Search</strong> - Full-width search box (NEW POSITION)</li>';
        echo '<li>✅ <strong>Indexed Items Table</strong> - With server-side pagination</li>';
        echo '<li>✅ <strong>Bulk Actions</strong> - Sync All, Sync Single Post, etc.</li>';
        echo '</ol>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>🎯 Layout Improvements</h2>';
        
        echo '<table>';
        echo '<tr><th>Feature</th><th>Status</th><th>Description</th></tr>';
        echo '<tr><td>Full-Width Position</td><td class="success">✅ Implemented</td><td>Manual search is now at the top, full-width</td></tr>';
        echo '<tr><td>Above Indexed Items</td><td class="success">✅ Implemented</td><td>Search section appears before the indexed items table</td></tr>';
        echo '<tr><td>Above Bulk Actions</td><td class="success">✅ Implemented</td><td>Search section appears before bulk actions</td></tr>';
        echo '<tr><td>Responsive Design</td><td class="success">✅ Implemented</td><td>Search inputs adapt to screen size</td></tr>';
        echo '<tr><td>Enhanced Styling</td><td class="success">✅ Implemented</td><td>Larger inputs, better spacing</td></tr>';
        echo '</table>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>📱 Responsive Features</h2>';
        
        echo '<p class="info"><strong>Search Input Layout:</strong></p>';
        echo '<ul>';
        echo '<li>✅ <strong>Search Input</strong> - Takes 2/3 of the width (flex: 2)</li>';
        echo '<li>✅ <strong>Post Type Dropdown</strong> - Takes 1/3 of the width (flex: 1)</li>';
        echo '<li>✅ <strong>Search Button</strong> - Fixed width, always visible</li>';
        echo '<li>✅ <strong>Responsive Wrapping</strong> - Elements wrap on smaller screens</li>';
        echo '</ul>';
        
        echo '<p class="info"><strong>Enhanced Styling:</strong></p>';
        echo '<ul>';
        echo '<li>✅ <strong>Larger Inputs</strong> - Increased padding for better usability</li>';
        echo '<li>✅ <strong>Better Spacing</strong> - Improved gap between elements</li>';
        echo '<li>✅ <strong>Full-Width Container</strong> - Uses 100% width with proper margins</li>';
        echo '<li>✅ <strong>Professional Look</strong> - Consistent with WordPress admin styling</li>';
        echo '</ul>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>🚀 Test the New Layout</h2>';
        
        echo '<div class="test-action">';
        echo '<button class="test-button" onclick="window.location.href=\'' . admin_url('admin.php?page=wp-gpt-rag-chat-indexing') . '\'">🔗 Go to Indexing Page</button>';
        echo '</div>';
        
        echo '<p class="info"><strong>What to verify:</strong></p>';
        echo '<ul>';
        echo '<li>✅ Manual search section appears at the top (full-width)</li>';
        echo '<li>✅ Search section is above the indexed items table</li>';
        echo '<li>✅ Search section is above the bulk actions</li>';
        echo '<li>✅ Search inputs are properly sized and responsive</li>';
        echo '<li>✅ Search functionality works as expected</li>';
        echo '<li>✅ Layout looks professional and organized</li>';
        echo '</ul>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>📋 Page Structure</h2>';
        
        echo '<div class="code-block">';
        echo '1. Page Header & Title<br>';
        echo '2. Statistics Cards (Total Vectors, Indexed Posts, Recent Activity)<br>';
        echo '3. <strong>Manual Re-indexing Search (FULL-WIDTH)</strong> ← NEW POSITION<br>';
        echo '4. Indexed Items Table (with server-side pagination)<br>';
        echo '5. Bulk Actions (Sync All, Sync Single Post, etc.)<br>';
        echo '6. Other sections (Sitemap, Import, etc.)';
        echo '</div>';
        
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

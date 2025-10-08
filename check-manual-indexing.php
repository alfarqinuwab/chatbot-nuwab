<!DOCTYPE html>
<html>
<head>
    <title>Check Manual Indexing Section</title>
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
        <h1>🔍 Check Manual Indexing Section</h1>
        
        <?php
        // Load WordPress
        require_once('../../../wp-load.php');
        
        if (!current_user_can('manage_options')) {
            echo '<p class="error">⛔ You must be an administrator to access this page.</p>';
            exit;
        }
        
        echo '<div class="test-section">';
        echo '<h2>📋 Manual Indexing Section Status</h2>';
        
        // Check if the indexing page template exists
        $template_path = plugin_dir_path(__FILE__) . 'templates/indexing-page.php';
        if (file_exists($template_path)) {
            echo '<p class="success">✅ Indexing page template exists</p>';
            
            // Read the template content
            $content = file_get_contents($template_path);
            
            // Check for key elements
            $checks = [
                'wp-gpt-rag-chat-bulk-actions' => 'Bulk Actions Container',
                'index-post-type' => 'Post Type Dropdown',
                'sync-single-post' => 'Sync Single Post Button',
                'sync-all-content' => 'Sync All Button',
                'Index All Content' => 'Index All Content Section',
                'Select Post Type' => 'Post Type Selector Label'
            ];
            
            echo '<table>';
            echo '<tr><th>Element</th><th>Status</th><th>Found</th></tr>';
            
            foreach ($checks as $search => $description) {
                $found = strpos($content, $search) !== false;
                $status = $found ? '✅ Found' : '❌ Missing';
                $color = $found ? 'success' : 'error';
                echo '<tr>';
                echo '<td>' . $description . '</td>';
                echo '<td class="' . $color . '">' . $status . '</td>';
                echo '<td>' . ($found ? 'Yes' : 'No') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
        } else {
            echo '<p class="error">❌ Indexing page template not found</p>';
        }
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>🔗 Direct Links</h2>';
        
        echo '<div class="test-action">';
        echo '<button class="test-button" onclick="window.location.href=\'' . admin_url('admin.php?page=wp-gpt-rag-chat-indexing') . '\'">🔗 Go to Indexing Page</button>';
        echo '</div>';
        
        echo '<p class="info"><strong>What to look for:</strong></p>';
        echo '<ul>';
        echo '<li>✅ "Bulk Actions" section should be visible</li>';
        echo '<li>✅ "Index All Content" subsection should be there</li>';
        echo '<li>✅ "Select Post Type" dropdown should be visible</li>';
        echo '<li>✅ "Sync All" and "Sync Only One Post" buttons should be there</li>';
        echo '<li>✅ The section should be on the right side of the page</li>';
        echo '</ul>';
        
        echo '</div>';
        
        echo '<div class="test-section">';
        echo '<h2>🎯 Possible Issues</h2>';
        
        echo '<ol>';
        echo '<li><strong>CSS Issue:</strong> The section might be hidden by CSS</li>';
        echo '<li><strong>JavaScript Issue:</strong> JavaScript might be hiding the section</li>';
        echo '<li><strong>Layout Issue:</strong> The section might be positioned off-screen</li>';
        echo '<li><strong>Template Issue:</strong> The template might not be loading correctly</li>';
        echo '</ol>';
        
        echo '<p class="warning"><strong>If the section is missing:</strong></p>';
        echo '<ul>';
        echo '<li>Check browser console for JavaScript errors</li>';
        echo '<li>Check if CSS is hiding the section</li>';
        echo '<li>Try refreshing the page</li>';
        echo '<li>Check if there are any PHP errors</li>';
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

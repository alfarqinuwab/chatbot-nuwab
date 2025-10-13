<?php
/**
 * Test Custom Tables Indexing - Simple Test
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
?>

<div class="wrap">
    <h1>Test Custom Tables Indexing</h1>
    
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Simple Test</h2>
        </div>
        <div class="cornuwab-card-body">
            <button id="test-simple" class="button button-primary">Test Simple Indexing</button>
            <div id="test-results" style="margin-top: 20px;"></div>
        </div>
    </div>
</div>

<style>
.cornuwab-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.cornuwab-card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f1;
    background: #f8f9fa;
}

.cornuwab-card-header h2 {
    margin: 0 0 5px 0;
    font-size: 18px;
    font-weight: 600;
}

.cornuwab-card-body {
    padding: 20px;
}

.test-result {
    background: #f8f9fa;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 15px;
    margin: 10px 0;
}

.test-success {
    border-color: #d1e7dd;
    background: #d1e7dd;
}

.test-error {
    border-color: #f8d7da;
    background: #f8d7da;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#test-simple').on('click', function() {
        $('#test-results').html('<p>Testing simple indexing...</p>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_test_simple_index',
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            let html = '<div class="test-result">';
            html += '<h3>Test Result:</h3>';
            html += '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
            html += '</div>';
            
            if (response.success && response.data.processed_count > 0) {
                html += '<div class="test-result test-success">';
                html += '<h3>✅ Success!</h3>';
                html += '<p>Successfully indexed ' + response.data.processed_count + ' record(s)</p>';
                html += '</div>';
            } else {
                html += '<div class="test-result test-error">';
                html += '<h3>❌ No Records Processed</h3>';
                html += '<p>This means the content filtering is too strict</p>';
                html += '</div>';
            }
            
            $('#test-results').html(html);
        }).fail(function(xhr, status, error) {
            $('#test-results').html('<div class="test-result test-error"><h3>Error:</h3><p>' + error + '</p></div>');
        });
    });
});
</script>

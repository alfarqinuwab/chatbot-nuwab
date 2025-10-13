<?php
/**
 * Simple Insert Test - Test database insert directly
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
?>

<div class="wrap">
    <h1>Simple Insert Test</h1>
    
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Test Direct Database Insert</h2>
        </div>
        <div class="cornuwab-card-body">
            <button id="test-insert" class="button button-primary">Test Direct Insert</button>
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
    $('#test-insert').on('click', function() {
        $('#test-results').html('<p>Testing direct insert...</p>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_test_direct_insert',
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            let html = '<div class="test-result">';
            html += '<h3>Direct Insert Result:</h3>';
            html += '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
            html += '</div>';
            
            if (response.success) {
                html += '<div class="test-result test-success">';
                html += '<h3>✅ Success!</h3>';
                html += '<p>Direct insert worked: ' + response.data.message + '</p>';
                html += '</div>';
            } else {
                html += '<div class="test-result test-error">';
                html += '<h3>❌ Insert Failed</h3>';
                html += '<p>Error: ' + response.data.message + '</p>';
                html += '</div>';
            }
            
            $('#test-results').html(html);
        }).fail(function(xhr, status, error) {
            $('#test-results').html('<div class="test-result test-error"><h3>Error:</h3><p>' + error + '</p></div>');
        });
    });
});
</script>

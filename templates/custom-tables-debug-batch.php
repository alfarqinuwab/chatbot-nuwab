<?php
/**
 * Debug Batch Indexing - Test the batch process
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
?>

<div class="wrap">
    <h1>Debug Batch Indexing</h1>
    
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Test Batch Indexing</h2>
        </div>
        <div class="cornuwab-card-body">
            <button id="test-batch" class="button button-primary">Test Batch Indexing</button>
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
    $('#test-batch').on('click', function() {
        $('#test-results').html('<p>Testing batch indexing...</p>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_index_custom_table_batch',
            table_name: 'committee_achievement',
            offset: 0,
            limit: 1,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            let html = '<div class="test-result">';
            html += '<h3>Batch Test Result:</h3>';
            html += '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
            html += '</div>';
            
            if (response.success && response.data.processed_count > 0) {
                html += '<div class="test-result test-success">';
                html += '<h3>✅ Success!</h3>';
                html += '<p>Successfully processed ' + response.data.processed_count + ' record(s)</p>';
                html += '</div>';
            } else {
                html += '<div class="test-result test-error">';
                html += '<h3>❌ No Records Processed</h3>';
                
                if (response.data.debug_info) {
                    html += '<h4>Debug Information:</h4>';
                    html += '<ul>';
                    html += '<li><strong>Table:</strong> ' + response.data.debug_info.table_name + '</li>';
                    html += '<li><strong>Full Table:</strong> ' + response.data.debug_info.full_table_name + '</li>';
                    html += '<li><strong>Records Found:</strong> ' + response.data.debug_info.records_found + '</li>';
                    html += '<li><strong>Total in Table:</strong> ' + response.data.debug_info.total_records_in_table + '</li>';
                    
                    if (response.data.debug_info.content_processing_debug) {
                        html += '<li><strong>Content Parts:</strong> ' + response.data.debug_info.content_processing_debug.content_parts_count + '</li>';
                        html += '<li><strong>Content Length:</strong> ' + response.data.debug_info.content_processing_debug.content_length + '</li>';
                        html += '<li><strong>Fields:</strong> ' + response.data.debug_info.content_processing_debug.first_record_fields.join(', ') + '</li>';
                    }
                    html += '</ul>';
                }
                html += '</div>';
            }
            
            $('#test-results').html(html);
        }).fail(function(xhr, status, error) {
            $('#test-results').html('<div class="test-result test-error"><h3>Error:</h3><p>' + error + '</p></div>');
        });
    });
});
</script>

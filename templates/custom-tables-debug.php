<?php
/**
 * Debug Custom Tables - Test the indexing process
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
?>

<div class="wrap">
    <h1>Debug Custom Tables Indexing</h1>
    
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Test Table Query</h2>
        </div>
        <div class="cornuwab-card-body">
            <form id="debug-form">
                <p>
                    <label for="test-table">Select Table:</label>
                    <select id="test-table" name="table_name">
                        <option value="">Choose a table...</option>
                        <option value="committee_achievement">committee_achievement</option>
                        <option value="topics_bills">topics_bills</option>
                        <option value="topics_agreements">topics_agreements</option>
                    </select>
                </p>
                <p>
                    <label for="test-offset">Offset:</label>
                    <input type="number" id="test-offset" name="offset" value="0" min="0">
                </p>
                <p>
                    <label for="test-limit">Limit:</label>
                    <input type="number" id="test-limit" name="limit" value="5" min="1" max="50">
                </p>
                <p>
                    <button type="button" id="test-query" class="button button-primary">Test Query</button>
                </p>
            </form>
            
            <div id="debug-results" style="margin-top: 20px;"></div>
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

.debug-result {
    background: #f8f9fa;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 15px;
    margin: 10px 0;
}

.debug-success {
    border-color: #d1e7dd;
    background: #d1e7dd;
}

.debug-error {
    border-color: #f8d7da;
    background: #f8d7da;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#test-query').on('click', function() {
        const tableName = $('#test-table').val();
        const offset = parseInt($('#test-offset').val());
        const limit = parseInt($('#test-limit').val());
        
        if (!tableName) {
            alert('Please select a table');
            return;
        }
        
        $('#debug-results').html('<p>Testing query...</p>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_index_custom_table_batch',
            table_name: tableName,
            offset: offset,
            limit: limit,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            let html = '<div class="debug-result">';
            html += '<h3>AJAX Response:</h3>';
            html += '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
            html += '</div>';
            
            // Also test direct database query
            html += '<div class="debug-result">';
            html += '<h3>Direct Database Test:</h3>';
            html += '<p>This will test the database query directly...</p>';
            html += '</div>';
            
            $('#debug-results').html(html);
            
            // Test direct database query
            testDirectQuery(tableName, offset, limit);
        }).fail(function(xhr, status, error) {
            $('#debug-results').html('<div class="debug-result debug-error"><h3>AJAX Error:</h3><p>' + error + '</p></div>');
        });
    });
    
    function testDirectQuery(tableName, offset, limit) {
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_debug_table_query',
            table_name: tableName,
            offset: offset,
            limit: limit,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            let html = $('#debug-results').html();
            html += '<div class="debug-result debug-success">';
            html += '<h3>Direct Query Results:</h3>';
            html += '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
            html += '</div>';
            $('#debug-results').html(html);
        }).fail(function(xhr, status, error) {
            let html = $('#debug-results').html();
            html += '<div class="debug-result debug-error">';
            html += '<h3>Direct Query Error:</h3><p>' + error + '</p>';
            html += '</div>';
            $('#debug-results').html(html);
        });
    }
});
</script>

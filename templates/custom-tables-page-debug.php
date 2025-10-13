<?php
/**
 * Custom Tables Indexing Page - Debug Version
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Debug: Check if we can access the database
global $wpdb;
?>

<div class="wrap">
    <h1>Custom Tables Indexing - Debug</h1>
    
    <!-- Debug Info -->
    <div class="notice notice-info">
        <p><strong>Debug Info:</strong></p>
        <ul>
            <li>WordPress DB Prefix: <?php echo $wpdb->prefix; ?></li>
            <li>Current User: <?php echo wp_get_current_user()->user_login; ?></li>
            <li>User Capabilities: <?php echo current_user_can('manage_options') ? 'Yes' : 'No'; ?></li>
        </ul>
    </div>

    <!-- Test Database Connection -->
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Database Connection Test</h2>
        </div>
        <div class="cornuwab-card-body">
            <?php
            $test_query = $wpdb->get_var("SELECT 1");
            if ($test_query) {
                echo '<p style="color: green;">✓ Database connection successful</p>';
            } else {
                echo '<p style="color: red;">✗ Database connection failed</p>';
            }
            ?>
        </div>
    </div>

    <!-- Test Custom Tables -->
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Custom Tables Test</h2>
        </div>
        <div class="cornuwab-card-body">
            <?php
            // First, let's see what tables actually exist
            $all_tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
            echo '<h4>All Tables in Database:</h4>';
            echo '<ul>';
            foreach ($all_tables as $table) {
                $table_name = $table[0];
                if (strpos($table_name, $wpdb->prefix) === 0) {
                    $clean_name = str_replace($wpdb->prefix, '', $table_name);
                    echo '<li>' . $clean_name . ' (full: ' . $table_name . ')</li>';
                }
            }
            echo '</ul>';
            
            // Now test the expected custom tables (using the correct prefix)
            $custom_tables = [
                'committee_achievement',
                'member_achievement_bp_topics', 
                'member_achievement_ipg_nuwab',
                'member_achievement_prop_topics',
                'member_achievement_ques_topics',
                'minister_details',
                'mp_detail',
                'sitting_agenda',
                'sitting_attachment',
                'topics_agreements',
                'topics_bills',
                'topics_billproposals',
                'topics_decrees',
                'topics_generaltopics',
                'topics_interrogation',
                'topics_investigation',
                'topics_proposal',
                'topics_questions'
            ];
            
            echo '<h4>Expected Custom Tables:</h4>';
            foreach ($custom_tables as $table) {
                $full_table_name = $wpdb->prefix . $table;
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
                
                if ($table_exists) {
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM `$full_table_name`");
                    echo '<p style="color: green;">✓ Table ' . $table . ' exists with ' . $count . ' records</p>';
                } else {
                    echo '<p style="color: red;">✗ Table ' . $table . ' does not exist</p>';
                }
            }
            ?>
        </div>
    </div>

    <!-- Test Vectors Table -->
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Vectors Table Test</h2>
        </div>
        <div class="cornuwab-card-body">
            <?php
            $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
            $vectors_exists = $wpdb->get_var("SHOW TABLES LIKE '$vectors_table'");
            
            if ($vectors_exists) {
                $vectors_count = $wpdb->get_var("SELECT COUNT(*) FROM `$vectors_table`");
                echo '<p style="color: green;">✓ Vectors table exists with ' . $vectors_count . ' records</p>';
                
                // Test custom table vectors
                $custom_vectors = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM `$vectors_table` WHERE metadata LIKE %s",
                    '%"table_name":"committee_achievement"%'
                ));
                echo '<p>Custom table vectors: ' . $custom_vectors . '</p>';
            } else {
                echo '<p style="color: red;">✗ Vectors table does not exist</p>';
            }
            ?>
        </div>
    </div>

    <!-- Simple Test Interface -->
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2>Simple Test Interface</h2>
        </div>
        <div class="cornuwab-card-body">
            <p>Test indexing with the topics_bills table (we know this exists with data):</p>
            <button class="button button-primary" id="test-index-btn">Test Index topics_bills</button>
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
</style>

<script>
jQuery(document).ready(function($) {
    $('#test-index-btn').on('click', function() {
        var button = $(this);
        var results = $('#test-results');
        
        button.prop('disabled', true).text('Testing...');
        results.html('<p>Testing indexing...</p>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_index_custom_table_batch',
            table_name: 'topics_bills',
            offset: 0,
            limit: 5,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                results.html('<p style="color: green;">✓ Test successful! Processed ' + response.data.processed_count + ' records</p>');
            } else {
                results.html('<p style="color: red;">✗ Test failed: ' + response.data.message + '</p>');
            }
        }).fail(function() {
            results.html('<p style="color: red;">✗ AJAX request failed</p>');
        }).always(function() {
            button.prop('disabled', false).text('Test Index Committee Achievement');
        });
    });
});
</script>

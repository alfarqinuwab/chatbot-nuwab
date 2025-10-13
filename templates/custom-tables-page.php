<?php
/**
 * Custom Tables Indexing Page
 * 
 * This page allows indexing of custom database tables for AI search
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user role
$is_aims_manager = \WP_GPT_RAG_Chat\RBAC::is_aims_manager();
$can_view_logs = \WP_GPT_RAG_Chat\RBAC::can_view_logs();
$user_role_display = \WP_GPT_RAG_Chat\RBAC::get_user_role_display();

// Get custom tables from database
global $wpdb;
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
    'topics_billproposals',
    'topics_bills',
    'topics_decrees',
    'topics_generaltopics',
    'topics_interrogation',
    'topics_investigation',
    'topics_proposal',
    'topics_questions'
];

// Get table structures
$table_structures = [];
foreach ($custom_tables as $table) {
    $full_table_name = $wpdb->prefix . $table;
    $columns = $wpdb->get_results("DESCRIBE `$full_table_name`");
    if ($columns) {
        $table_structures[$table] = $columns;
    }
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Custom Tables Indexing', 'wp-gpt-rag-chat'); ?></h1>
    
    <!-- User Role Display -->
    <div class="notice notice-info">
        <p><strong><?php esc_html_e('Your Role:', 'wp-gpt-rag-chat'); ?> <?php echo esc_html($user_role_display); ?></strong></p>
    </div>

    <!-- Custom Tables Overview -->
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2><?php esc_html_e('Custom Database Tables', 'wp-gpt-rag-chat'); ?></h2>
            <p><?php esc_html_e('Index custom database tables to make their content searchable by AI', 'wp-gpt-rag-chat'); ?></p>
        </div>
        
        <div class="cornuwab-card-body">
            <div class="custom-tables-grid">
                <?php foreach ($table_structures as $table_name => $columns): ?>
                    <div class="custom-table-card">
                        <div class="table-header">
                            <h3><?php echo esc_html($table_name); ?></h3>
                            <div class="table-stats">
                                <?php 
                                $total_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}{$table_name}`");
                                $indexed_count = $wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM `{$wpdb->prefix}wp_gpt_rag_chat_vectors` WHERE metadata LIKE %s",
                                    '%"table_name":"' . $table_name . '"%'
                                ));
                                $progress_percentage = $total_count > 0 ? round(($indexed_count / $total_count) * 100) : 0;
                                ?>
                                <div class="record-counts">
                                    <span class="total-records"><?php echo esc_html($total_count); ?> total</span>
                                    <span class="indexed-records"><?php echo esc_html($indexed_count); ?> indexed</span>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo esc_attr($progress_percentage); ?>%"></div>
                                    </div>
                                    <span class="progress-text"><?php echo esc_html($progress_percentage); ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-columns">
                            <h4><?php esc_html_e('Available Columns:', 'wp-gpt-rag-chat'); ?></h4>
                            <div class="columns-list">
                                <?php foreach ($columns as $column): ?>
                                    <div class="column-item">
                                        <span class="column-name"><?php echo esc_html($column->Field); ?></span>
                                        <span class="column-type"><?php echo esc_html($column->Type); ?></span>
                                        <?php if ($column->Key === 'PRI'): ?>
                                            <span class="primary-key">PK</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="table-actions">
                            <button class="button button-primary index-table-btn" data-table="<?php echo esc_attr($table_name); ?>" data-total="<?php echo esc_attr($total_count); ?>">
                                <?php esc_html_e('Index Table', 'wp-gpt-rag-chat'); ?>
                            </button>
                            <button class="button button-secondary clear-index-btn" data-table="<?php echo esc_attr($table_name); ?>">
                                <?php esc_html_e('Clear Index', 'wp-gpt-rag-chat'); ?>
                            </button>
                            <button class="button button-secondary analyze-table-btn" data-table="<?php echo esc_attr($table_name); ?>">
                                <?php esc_html_e('Analyze', 'wp-gpt-rag-chat'); ?>
                            </button>
                        </div>
                        
                        <div class="indexing-progress" id="progress-<?php echo esc_attr($table_name); ?>" style="display: none;">
                            <div class="progress-info">
                                <span class="progress-label"><?php esc_html_e('Indexing in progress...', 'wp-gpt-rag-chat'); ?></span>
                                <span class="progress-details">0 / <?php echo esc_html($total_count); ?></span>
                            </div>
                            <div class="progress-bar-large">
                                <div class="progress-fill-large"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Field Mapping Configuration -->
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2><?php esc_html_e('Field Mapping Configuration', 'wp-gpt-rag-chat'); ?></h2>
            <p><?php esc_html_e('Configure which fields to index for each table', 'wp-gpt-rag-chat'); ?></p>
        </div>
        
        <div class="cornuwab-card-body">
            <form id="custom-tables-mapping-form">
                <div class="mapping-sections">
                    <?php foreach ($table_structures as $table_name => $columns): ?>
                        <div class="mapping-section" data-table="<?php echo esc_attr($table_name); ?>">
                            <h3><?php echo esc_html($table_name); ?></h3>
                            
                            <div class="field-mapping">
                                <div class="mapping-row">
                                    <label><?php esc_html_e('Title Field:', 'wp-gpt-rag-chat'); ?></label>
                                    <select name="title_field[<?php echo esc_attr($table_name); ?>]">
                                        <option value=""><?php esc_html_e('Select Title Field', 'wp-gpt-rag-chat'); ?></option>
                                        <?php foreach ($columns as $column): ?>
                                            <option value="<?php echo esc_attr($column->Field); ?>">
                                                <?php echo esc_html($column->Field); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mapping-row">
                                    <label><?php esc_html_e('Content Fields:', 'wp-gpt-rag-chat'); ?></label>
                                    <div class="checkbox-group">
                                        <?php foreach ($columns as $column): ?>
                                            <label class="checkbox-item">
                                                <input type="checkbox" name="content_fields[<?php echo esc_attr($table_name); ?>][]" value="<?php echo esc_attr($column->Field); ?>">
                                                <?php echo esc_html($column->Field); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="mapping-row">
                                    <label><?php esc_html_e('Date Field:', 'wp-gpt-rag-chat'); ?></label>
                                    <select name="date_field[<?php echo esc_attr($table_name); ?>]">
                                        <option value=""><?php esc_html_e('Select Date Field', 'wp-gpt-rag-chat'); ?></option>
                                        <?php foreach ($columns as $column): ?>
                                            <?php if (strpos($column->Type, 'date') !== false || strpos($column->Type, 'time') !== false): ?>
                                                <option value="<?php echo esc_attr($column->Field); ?>">
                                                    <?php echo esc_html($column->Field); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mapping-row">
                                    <label><?php esc_html_e('ID Field:', 'wp-gpt-rag-chat'); ?></label>
                                    <select name="id_field[<?php echo esc_attr($table_name); ?>]">
                                        <option value=""><?php esc_html_e('Select ID Field', 'wp-gpt-rag-chat'); ?></option>
                                        <?php foreach ($columns as $column): ?>
                                            <?php if ($column->Key === 'PRI' || strpos($column->Field, 'id') !== false): ?>
                                                <option value="<?php echo esc_attr($column->Field); ?>">
                                                    <?php echo esc_html($column->Field); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Save Field Mappings', 'wp-gpt-rag-chat'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="auto-detect-mappings">
                        <?php esc_html_e('Auto-Detect Mappings', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Indexing Actions -->
    <div class="cornuwab-card">
        <div class="cornuwab-card-header">
            <h2><?php esc_html_e('Bulk Indexing Actions', 'wp-gpt-rag-chat'); ?></h2>
        </div>
        
        <div class="cornuwab-card-body">
            <div class="bulk-actions">
                <button class="button button-primary" id="index-all-tables">
                    <?php esc_html_e('Index All Tables', 'wp-gpt-rag-chat'); ?>
                </button>
                <button class="button button-secondary" id="clear-custom-indexes">
                    <?php esc_html_e('Clear All Custom Indexes', 'wp-gpt-rag-chat'); ?>
                </button>
                <button class="button button-secondary" id="test-custom-search">
                    <?php esc_html_e('Test Custom Search', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
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

.custom-tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
}

.custom-table-card {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 15px;
    background: #f8f9fa;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.table-header h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
}

.table-stats {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    min-width: 200px;
}

.record-counts {
    display: flex;
    gap: 10px;
    font-size: 12px;
}

.total-records {
    color: #646970;
    font-weight: 500;
}

.indexed-records {
    color: #2271b1;
    font-weight: 600;
}

.progress-container {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.progress-bar {
    flex: 1;
    height: 8px;
    background: #f0f0f1;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #2271b1 0%, #1e5a96 100%);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 11px;
    font-weight: 600;
    color: #2271b1;
    min-width: 35px;
    text-align: right;
}

.table-columns h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
}

.columns-list {
    max-height: 150px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.column-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px 0;
    border-bottom: 1px solid #f0f0f1;
}

.column-name {
    font-weight: 500;
    color: #1d2327;
    min-width: 120px;
}

.column-type {
    color: #646970;
    font-size: 12px;
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
}

.primary-key {
    background: #d1e7dd;
    color: #0f5132;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 600;
}

.table-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.indexing-progress {
    background: #f8f9fa;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 15px;
    margin-top: 10px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.progress-label {
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
}

.progress-details {
    font-size: 12px;
    color: #646970;
    font-weight: 500;
}

.progress-bar-large {
    height: 12px;
    background: #f0f0f1;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
}

.progress-fill-large {
    height: 100%;
    background: linear-gradient(90deg, #2271b1 0%, #1e5a96 100%);
    border-radius: 6px;
    transition: width 0.3s ease;
    width: 0%;
}

.mapping-section {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
    background: #f8f9fa;
}

.mapping-section h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
}

.field-mapping {
    display: grid;
    gap: 15px;
}

.mapping-row {
    display: flex;
    align-items: center;
    gap: 15px;
}

.mapping-row label {
    min-width: 120px;
    font-weight: 500;
    color: #1d2327;
}

.mapping-row select {
    min-width: 200px;
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 8px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.checkbox-item input[type="checkbox"] {
    margin: 0;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.bulk-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .custom-tables-grid {
        grid-template-columns: 1fr;
    }
    
    .mapping-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .mapping-row label {
        min-width: auto;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-detect mappings based on common field names
    $('#auto-detect-mappings').on('click', function() {
        $('.mapping-section').each(function() {
            var $section = $(this);
            var tableName = $section.data('table');
            
            // Auto-detect title field
            var titleSelect = $section.find('select[name="title_field[' + tableName + ']"]');
            var titleOptions = titleSelect.find('option');
            titleOptions.each(function() {
                var optionText = $(this).text().toLowerCase();
                if (optionText.includes('title') || optionText.includes('name') || optionText.includes('subject')) {
                    $(this).prop('selected', true);
                    return false;
                }
            });
            
            // Auto-detect content fields
            var contentCheckboxes = $section.find('input[name="content_fields[' + tableName + '][]"]');
            contentCheckboxes.each(function() {
                var fieldName = $(this).val().toLowerCase();
                if (fieldName.includes('content') || fieldName.includes('description') || 
                    fieldName.includes('remarks') || fieldName.includes('details')) {
                    $(this).prop('checked', true);
                }
            });
            
            // Auto-detect date field
            var dateSelect = $section.find('select[name="date_field[' + tableName + ']"]');
            var dateOptions = dateSelect.find('option');
            dateOptions.each(function() {
                var optionText = $(this).text().toLowerCase();
                if (optionText.includes('date') || optionText.includes('time') || optionText.includes('created')) {
                    $(this).prop('selected', true);
                    return false;
                }
            });
            
            // Auto-detect ID field
            var idSelect = $section.find('select[name="id_field[' + tableName + ']"]');
            var idOptions = idSelect.find('option');
            idOptions.each(function() {
                var optionText = $(this).text().toLowerCase();
                if (optionText.includes('id') && optionText !== 'id') {
                    $(this).prop('selected', true);
                    return false;
                }
            });
        });
        
        alert('Auto-detection completed! Please review the mappings before saving.');
    });
    
    // Index individual table with progress tracking
    $('.index-table-btn').on('click', function() {
        var tableName = $(this).data('table');
        var totalRecords = parseInt($(this).data('total'));
        var button = $(this);
        var progressContainer = $('#progress-' + tableName);
        var progressFill = progressContainer.find('.progress-fill-large');
        var progressDetails = progressContainer.find('.progress-details');
        
        // Show progress container
        progressContainer.show();
        button.prop('disabled', true).text('Indexing...');
        
        // Start indexing with progress tracking
        indexTableWithProgress(tableName, totalRecords, progressFill, progressDetails, button, progressContainer);
    });
    
    // Clear index for individual table
    $('.clear-index-btn').on('click', function() {
        var tableName = $(this).data('table');
        var button = $(this);
        
        if (confirm('Are you sure you want to clear the index for ' + tableName + '?')) {
            button.prop('disabled', true).text('Clearing...');
            
            $.post(ajaxurl, {
                action: 'wp_gpt_rag_chat_clear_custom_table_index',
                table_name: tableName,
                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('Index cleared successfully!');
                    location.reload(); // Refresh to update stats
                } else {
                    alert('Error: ' + response.data.message);
                }
            }).fail(function() {
                alert('Network error occurred.');
            }).always(function() {
                button.prop('disabled', false).text('Clear Index');
            });
        }
    });
    
    // Save field mappings
    $('#custom-tables-mapping-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_save_custom_mappings',
            form_data: formData,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                alert('Field mappings saved successfully!');
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });
    
    // Index all tables
    $('#index-all-tables').on('click', function() {
        if (confirm('This will index all custom tables. Continue?')) {
            var button = $(this);
            button.prop('disabled', true).text('Indexing All Tables...');
            
            $.post(ajaxurl, {
                action: 'wp_gpt_rag_chat_index_all_custom_tables',
                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('All tables indexed successfully!');
                } else {
                    alert('Error: ' + response.data.message);
                }
            }).fail(function() {
                alert('Network error occurred.');
            }).always(function() {
                button.prop('disabled', false).text('Index All Tables');
            });
        }
    });
    
    // Function to index table with progress tracking
    function indexTableWithProgress(tableName, totalRecords, progressFill, progressDetails, button, progressContainer) {
        var batchSize = 10; // Process 10 records at a time
        var currentIndex = 0;
        var processedRecords = 0;
        
        function processBatch() {
            if (currentIndex >= totalRecords) {
                // Indexing complete
                progressFill.css('width', '100%');
                progressDetails.text(processedRecords + ' / ' + totalRecords);
                button.prop('disabled', false).text('Index Table');
                progressContainer.hide();
                alert('Table indexed successfully! ' + processedRecords + ' records processed.');
                location.reload(); // Refresh to update stats
                return;
            }
            
            // Calculate progress percentage
            var progressPercent = Math.round((processedRecords / totalRecords) * 100);
            progressFill.css('width', progressPercent + '%');
            progressDetails.text(processedRecords + ' / ' + totalRecords);
            
            // Process next batch
            $.post(ajaxurl, {
                action: 'wp_gpt_rag_chat_index_custom_table_batch',
                table_name: tableName,
                offset: currentIndex,
                limit: batchSize,
                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    processedRecords += response.data.processed_count;
                    currentIndex += batchSize;
                    
                    // Continue with next batch
                    setTimeout(processBatch, 100); // Small delay to show progress
                } else {
                    alert('Error: ' + response.data.message);
                    button.prop('disabled', false).text('Index Table');
                    progressContainer.hide();
                }
            }).fail(function() {
                alert('Network error occurred.');
                button.prop('disabled', false).text('Index Table');
                progressContainer.hide();
            });
        }
        
        // Start processing
        processBatch();
    }
});
</script>

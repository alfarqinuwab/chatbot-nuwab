<?php
/**
 * Export page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$stats = WP_GPT_RAG_Chat\Admin::get_indexing_stats();
$chat_stats = WP_GPT_RAG_Chat\Chat::get_chat_stats();
$settings = WP_GPT_RAG_Chat\Settings::get_settings();
?>

<div class="wrap">
    <h1>
        <span class="dashicons dashicons-download"></span>
        <?php esc_html_e('Export Data', 'wp-gpt-rag-chat'); ?>
    </h1>
    
    <div class="wp-gpt-rag-chat-export">
        <!-- Export Options -->
        <div class="export-options">
            <div class="export-grid">
                <div class="export-card">
                    <div class="export-icon">
                        <span class="dashicons dashicons-format-chat"></span>
                    </div>
                    <div class="export-content">
                        <h3><?php esc_html_e('Chat Logs', 'wp-gpt-rag-chat'); ?></h3>
                        <p><?php esc_html_e('Export all chat conversations, queries, and responses.', 'wp-gpt-rag-chat'); ?></p>
                        <div class="export-stats">
                            <span><?php echo esc_html(number_format($chat_stats['total_queries'] ?? 0)); ?> <?php esc_html_e('queries', 'wp-gpt-rag-chat'); ?></span>
                        </div>
                        <button type="button" class="button button-primary export-btn" data-type="chat-logs">
                            <?php esc_html_e('Export Chat Logs', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="export-card">
                    <div class="export-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="export-content">
                        <h3><?php esc_html_e('User Analytics', 'wp-gpt-rag-chat'); ?></h3>
                        <p><?php esc_html_e('Export user activity, sessions, and engagement data.', 'wp-gpt-rag-chat'); ?></p>
                        <div class="export-stats">
                            <span><?php echo esc_html(number_format($chat_stats['unique_users'] ?? 0)); ?> <?php esc_html_e('users', 'wp-gpt-rag-chat'); ?></span>
                        </div>
                        <button type="button" class="button button-primary export-btn" data-type="user-analytics">
                            <?php esc_html_e('Export User Data', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="export-card">
                    <div class="export-icon">
                        <span class="dashicons dashicons-database"></span>
                    </div>
                    <div class="export-content">
                        <h3><?php esc_html_e('Indexing Data', 'wp-gpt-rag-chat'); ?></h3>
                        <p><?php esc_html_e('Export indexed content and vector information.', 'wp-gpt-rag-chat'); ?></p>
                        <div class="export-stats">
                            <span><?php echo esc_html(number_format($stats['total_vectors'] ?? 0)); ?> <?php esc_html_e('vectors', 'wp-gpt-rag-chat'); ?></span>
                        </div>
                        <button type="button" class="button button-primary export-btn" data-type="indexing-data">
                            <?php esc_html_e('Export Indexing Data', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="export-card">
                    <div class="export-icon">
                        <span class="dashicons dashicons-admin-settings"></span>
                    </div>
                    <div class="export-content">
                        <h3><?php esc_html_e('Settings & Configuration', 'wp-gpt-rag-chat'); ?></h3>
                        <p><?php esc_html_e('Export plugin settings and configuration data.', 'wp-gpt-rag-chat'); ?></p>
                        <div class="export-stats">
                            <span><?php esc_html_e('All settings', 'wp-gpt-rag-chat'); ?></span>
                        </div>
                        <button type="button" class="button button-primary export-btn" data-type="settings">
                            <?php esc_html_e('Export Settings', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Export Filters -->
        <div class="export-filters">
            <h2><?php esc_html_e('Export Filters', 'wp-gpt-rag-chat'); ?></h2>
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="date-from"><?php esc_html_e('From Date', 'wp-gpt-rag-chat'); ?></label>
                    <input type="date" id="date-from" name="date_from" class="regular-text" />
                </div>
                
                <div class="filter-group">
                    <label for="date-to"><?php esc_html_e('To Date', 'wp-gpt-rag-chat'); ?></label>
                    <input type="date" id="date-to" name="date_to" class="regular-text" />
                </div>
                
                <div class="filter-group">
                    <label for="user-type"><?php esc_html_e('User Type', 'wp-gpt-rag-chat'); ?></label>
                    <select id="user-type" name="user_type" class="regular-text">
                        <option value="all"><?php esc_html_e('All Users', 'wp-gpt-rag-chat'); ?></option>
                        <option value="logged-in"><?php esc_html_e('Logged In Only', 'wp-gpt-rag-chat'); ?></option>
                        <option value="anonymous"><?php esc_html_e('Anonymous Only', 'wp-gpt-rag-chat'); ?></option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="export-format"><?php esc_html_e('Export Format', 'wp-gpt-rag-chat'); ?></label>
                    <select id="export-format" name="export_format" class="regular-text">
                        <option value="csv"><?php esc_html_e('CSV', 'wp-gpt-rag-chat'); ?></option>
                        <option value="json"><?php esc_html_e('JSON', 'wp-gpt-rag-chat'); ?></option>
                        <option value="xlsx"><?php esc_html_e('Excel (XLSX)', 'wp-gpt-rag-chat'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Export History -->
        <div class="export-history">
            <h2><?php esc_html_e('Export History', 'wp-gpt-rag-chat'); ?></h2>
            <div class="history-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Export Type', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Date', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Records', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('File Size', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Status', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Actions', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="export-history-list">
                        <tr>
                            <td colspan="6"><?php esc_html_e('Loading export history...', 'wp-gpt-rag-chat'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Export Progress -->
        <div class="export-progress" id="export-progress" style="display: none;">
            <h3><?php esc_html_e('Export in Progress', 'wp-gpt-rag-chat'); ?></h3>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <p class="progress-text" id="progress-text"><?php esc_html_e('Preparing export...', 'wp-gpt-rag-chat'); ?></p>
        </div>
    </div>
</div>

<style>
.wp-gpt-rag-chat-export {
    margin-top: 20px;
}

.export-options {
    margin-bottom: 30px;
}

.export-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.export-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    display: flex;
    gap: 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s ease;
}

.export-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.export-icon {
    background: #f0f6fc;
    border-radius: 8px;
    padding: 12px;
    color: #0073aa;
    flex-shrink: 0;
}

.export-icon .dashicons {
    font-size: 24px;
}

.export-content {
    flex: 1;
}

.export-content h3 {
    margin: 0 0 8px 0;
    color: #1d2327;
    font-size: 16px;
}

.export-content p {
    margin: 0 0 12px 0;
    color: #646970;
    font-size: 14px;
    line-height: 1.4;
}

.export-stats {
    margin-bottom: 16px;
}

.export-stats span {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #646970;
}

.export-btn {
    width: 100%;
}

.export-filters {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.export-filters h2 {
    margin: 0 0 20px 0;
    color: #1d2327;
    font-size: 18px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.filter-group label {
    display: block;
    margin-bottom: 6px;
    color: #1d2327;
    font-weight: 500;
    font-size: 14px;
}

.export-history {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.export-history h2 {
    margin: 0 0 20px 0;
    color: #1d2327;
    font-size: 18px;
}

.history-table {
    overflow-x: auto;
}

.export-progress {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.export-progress h3 {
    margin: 0 0 16px 0;
    color: #1d2327;
    font-size: 16px;
}

.progress-bar {
    background: #f0f0f1;
    border-radius: 4px;
    height: 8px;
    margin-bottom: 12px;
    overflow: hidden;
}

.progress-fill {
    background: #0073aa;
    height: 100%;
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    margin: 0;
    color: #646970;
    font-size: 14px;
}

@media (max-width: 768px) {
    .export-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .export-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    loadExportHistory();
    
    // Export button handlers
    $('.export-btn').on('click', function() {
        const exportType = $(this).data('type');
        const dateFrom = $('#date-from').val();
        const dateTo = $('#date-to').val();
        const userType = $('#user-type').val();
        const exportFormat = $('#export-format').val();
        
        startExport(exportType, {
            date_from: dateFrom,
            date_to: dateTo,
            user_type: userType,
            export_format: exportFormat
        });
    });
    
    function startExport(type, filters) {
        $('#export-progress').show();
        updateProgress(0, '<?php esc_js(__('Starting export...', 'wp-gpt-rag-chat')); ?>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_start_export',
            export_type: type,
            filters: filters,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                updateProgress(100, '<?php esc_js(__('Export completed!', 'wp-gpt-rag-chat')); ?>');
                
                // Download the file
                if (response.data.download_url) {
                    window.location.href = response.data.download_url;
                }
                
                // Refresh history
                setTimeout(function() {
                    loadExportHistory();
                    $('#export-progress').hide();
                }, 2000);
            } else {
                updateProgress(0, '<?php esc_js(__('Export failed!', 'wp-gpt-rag-chat')); ?>');
                alert(response.data.message || '<?php esc_js(__('Export failed. Please try again.', 'wp-gpt-rag-chat')); ?>');
            }
        }).fail(function() {
            updateProgress(0, '<?php esc_js(__('Export failed!', 'wp-gpt-rag-chat')); ?>');
            alert('<?php esc_js(__('Export failed. Please try again.', 'wp-gpt-rag-chat')); ?>');
        });
    }
    
    function updateProgress(percent, text) {
        $('#progress-fill').css('width', percent + '%');
        $('#progress-text').text(text);
    }
    
    function loadExportHistory() {
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_get_export_history',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                $('#export-history-list').html(response.data.html);
            }
        });
    }
});
</script>




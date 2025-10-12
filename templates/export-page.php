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

<div class="wrap cornuwab-admin-wrap">
    <h1>
        <span class="dashicons dashicons-download"></span>
        <?php esc_html_e('Nuwab AI Assistant - Export Data', 'wp-gpt-rag-chat'); ?>
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
        
        <!-- Export Filters section removed -->
        
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

<!-- Export Modal -->
<div id="export-modal" class="export-modal" style="display: none;">
    <div class="export-modal-overlay"></div>
    <div class="export-modal-content">
        <div class="export-modal-header">
            <h3 id="export-modal-title"><?php esc_html_e('Export', 'wp-gpt-rag-chat'); ?></h3>
            <button type="button" class="export-modal-close">&times;</button>
        </div>
        <div class="export-modal-body">
            <div class="export-modal-icon">
                <span id="export-modal-icon" class="dashicons"></span>
            </div>
            <p id="export-modal-message"><?php esc_html_e('Export message', 'wp-gpt-rag-chat'); ?></p>
        </div>
        <div class="export-modal-footer">
            <button type="button" class="button button-primary" id="export-modal-confirm"><?php esc_html_e('OK', 'wp-gpt-rag-chat'); ?></button>
            <button type="button" class="button" id="export-modal-cancel" style="display: none;"><?php esc_html_e('Cancel', 'wp-gpt-rag-chat'); ?></button>
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

/* Export Filters CSS removed */

/* Custom Filter Group Styles - Nuwab AI Assistant Only */
.cor-nuwab-filter-group {
    box-sizing: border-box;
    position: relative;
    float: left;
    margin: 0 1% 0 0;
    padding: 20px 24px 28px;
    width: 24%;
    background: #fff;
    border: 1px solid #dcdcde;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.cor-nuwab-filter-group label {
    display: block;
    margin-bottom: 6px;
    color: #1d2327;
    font-weight: 500;
    font-size: 14px;
}

/* Responsive adjustments for filter groups */
@media (max-width: 1200px) {
    .cor-nuwab-filter-group {
        width: 32%;
    }
}

@media (max-width: 900px) {
    .cor-nuwab-filter-group {
        width: 48%;
    }
}

@media (max-width: 600px) {
    .cor-nuwab-filter-group {
        width: 100%;
        margin: 0 0 20px 0;
        float: none;
    }
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

/* Export Modal Styles */
.export-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.export-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}

.export-modal-content {
    position: relative;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.export-modal-header {
    padding: 20px 20px 0;
    border-bottom: 1px solid #e1e1e1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.export-modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #1d2327;
}

.export-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #646970;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.export-modal-close:hover {
    color: #1d2327;
}

.export-modal-body {
    padding: 20px;
    text-align: center;
}

.export-modal-icon {
    margin-bottom: 15px;
}

.export-modal-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
}

.export-modal-icon .dashicons.dashicons-warning {
    color: #d63638;
}

.export-modal-icon .dashicons.dashicons-yes-alt {
    color: #00a32a;
    font-size: 32px;
}

/* Success modal specific styling */
.export-modal-content.success .export-modal-body {
    background: #f0f8f0;
    border-left: 4px solid #00a32a;
}

.export-modal-content.success .export-modal-message {
    color: #2e7d32;
    font-weight: 500;
}

.export-modal-icon .dashicons.dashicons-info {
    color: #2271b1;
}

.export-modal-body p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    color: #2c3338;
    visibility: visible !important;
    opacity: 1 !important;
    display: block !important;
}

.export-modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #1d2327;
    visibility: visible !important;
    opacity: 1 !important;
    display: block !important;
}

.export-modal-footer {
    padding: 0 20px 20px;
    display: flex;
    justify-content: center;
    gap: 10px;
}

.export-modal-footer .button {
    min-width: 80px;
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
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
            console.log('Export response:', response);
            
            if (response && response.success) {
                updateProgress(100, '<?php esc_js(__('Export completed!', 'wp-gpt-rag-chat')); ?>');
                
                // Download the file first
                if (response.data && response.data.download_url) {
                    // Create a temporary link to trigger download
                    const link = document.createElement('a');
                    link.href = response.data.download_url;
                    link.download = response.data.download_url.split('/').pop();
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
                
        // Show success modal after download starts
        setTimeout(function() {
            console.log('About to show success modal');
            showModal('success', 'Export Successful!', 'Your data has been successfully exported and downloaded. The file is now available in your downloads folder.');
        }, 500);
                
                // Refresh history and hide progress
                setTimeout(function() {
                    loadExportHistory();
                    $('#export-progress').hide();
                }, 2000);
            } else {
                updateProgress(0, '<?php esc_js(__('Export failed!', 'wp-gpt-rag-chat')); ?>');
                
                let errorMessage = '<?php esc_js(__('Export failed. Please try again.', 'wp-gpt-rag-chat')); ?>';
                let errorTitle = '<?php esc_js(__('Export Failed', 'wp-gpt-rag-chat')); ?>';
                
                // Try to extract error message from response
                if (response && response.data && response.data.message) {
                    errorMessage = response.data.message;
                } else if (response && response.message) {
                    errorMessage = response.message;
                } else if (response && typeof response === 'string') {
                    errorMessage = response;
                }
                
                console.log('Error details:', { response, errorMessage, errorTitle });
                showModal('error', errorTitle, errorMessage);
            }
        }).fail(function(xhr, status, error) {
            console.log('AJAX fail:', { xhr, status, error, statusCode: xhr.status });
            updateProgress(0, '<?php esc_js(__('Export failed!', 'wp-gpt-rag-chat')); ?>');
            
            let errorMessage = '<?php esc_js(__('Export failed. Please try again.', 'wp-gpt-rag-chat')); ?>';
            let errorTitle = '<?php esc_js(__('Export Failed', 'wp-gpt-rag-chat')); ?>';
            
            // Handle specific HTTP status codes
            if (xhr.status === 403) {
                errorTitle = '<?php esc_js(__('Access Denied', 'wp-gpt-rag-chat')); ?>';
                errorMessage = '<?php esc_js(__('You do not have permission to perform this action. Please refresh the page and try again.', 'wp-gpt-rag-chat')); ?>';
            } else if (xhr.status === 404) {
                errorTitle = '<?php esc_js(__('Export Handler Not Found', 'wp-gpt-rag-chat')); ?>';
                errorMessage = '<?php esc_js(__('The export functionality is not available. Please contact the administrator.', 'wp-gpt-rag-chat')); ?>';
            } else if (xhr.status === 500) {
                errorTitle = '<?php esc_js(__('Server Error', 'wp-gpt-rag-chat')); ?>';
                errorMessage = '<?php esc_js(__('A server error occurred. Please try again later.', 'wp-gpt-rag-chat')); ?>';
            } else {
                // Try to get more specific error information
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.data && response.data.message) {
                            errorMessage = response.data.message;
                        } else if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If response is not JSON, use a generic message
                        errorMessage = '<?php esc_js(__('Server error occurred. Please check your server configuration.', 'wp-gpt-rag-chat')); ?>';
                    }
                }
            }
            
            console.log('Fail error details:', { errorMessage, errorTitle, statusCode: xhr.status });
            showModal('error', errorTitle, errorMessage);
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
    
    // Modal functions
    function showModal(type, title, message) {
        console.log('showModal called with:', { type, title, message });
        
        const modal = $('#export-modal');
        const modalTitle = $('#export-modal-title');
        const modalMessage = $('#export-modal-message');
        const modalIcon = $('#export-modal-icon');
        const confirmBtn = $('#export-modal-confirm');
        const cancelBtn = $('#export-modal-cancel');
        
        // Debug logging
        console.log('Modal elements found:', {
            modal: modal.length,
            modalTitle: modalTitle.length,
            modalMessage: modalMessage.length,
            modalIcon: modalIcon.length
        });
        
        // Check if elements exist
        if (modal.length === 0) {
            console.error('Modal element not found!');
            alert('Modal not found. Title: ' + title + ', Message: ' + message);
            return;
        }
        
        if (modalTitle.length === 0) {
            console.error('Modal title element not found!');
        }
        
        if (modalMessage.length === 0) {
            console.error('Modal message element not found!');
        }
        
        // Ensure we have proper values
        if (!type || typeof type !== 'string') {
            type = 'error';
        }
        
        if (!title || title.trim() === '') {
            if (type === 'error') {
                title = '<?php esc_js(__('Export Failed', 'wp-gpt-rag-chat')); ?>';
            } else if (type === 'success') {
                title = '<?php esc_js(__('Export Successful!', 'wp-gpt-rag-chat')); ?>';
            } else {
                title = '<?php esc_js(__('Notice', 'wp-gpt-rag-chat')); ?>';
            }
        }
        
        if (!message || message.trim() === '') {
            if (type === 'error') {
                message = '<?php esc_js(__('An error occurred during export. Please try again.', 'wp-gpt-rag-chat')); ?>';
            } else if (type === 'success') {
                message = '<?php esc_js(__('Your data has been successfully exported and downloaded. The file is now available in your downloads folder.', 'wp-gpt-rag-chat')); ?>';
            } else {
                message = '<?php esc_js(__('An error occurred. Please try again.', 'wp-gpt-rag-chat')); ?>';
            }
        }
        
        // Set content with debugging
        console.log('Setting modal content:', { title, message });
        
        // Try jQuery first
        if (modalTitle.length > 0) {
            modalTitle.html(title).css({
                'visibility': 'visible',
                'opacity': '1',
                'display': 'block'
            });
        }
        
        if (modalMessage.length > 0) {
            modalMessage.html(message).css({
                'visibility': 'visible',
                'opacity': '1',
                'display': 'block'
            });
        }
        
        // Fallback using vanilla JavaScript
        const titleElement = document.getElementById('export-modal-title');
        const messageElement = document.getElementById('export-modal-message');
        
        if (titleElement) {
            titleElement.innerHTML = title;
            titleElement.style.visibility = 'visible';
            titleElement.style.opacity = '1';
            titleElement.style.display = 'block';
            console.log('Set title via vanilla JS:', title);
        }
        
        if (messageElement) {
            messageElement.innerHTML = message;
            messageElement.style.visibility = 'visible';
            messageElement.style.opacity = '1';
            messageElement.style.display = 'block';
            console.log('Set message via vanilla JS:', message);
        }
        
        // Verify content was set
        console.log('Content after setting:', {
            titleText: modalTitle.length > 0 ? modalTitle.text() : 'jQuery element not found',
            messageText: modalMessage.length > 0 ? modalMessage.text() : 'jQuery element not found',
            vanillaTitle: titleElement ? titleElement.textContent : 'vanilla element not found',
            vanillaMessage: messageElement ? messageElement.textContent : 'vanilla element not found'
        });
        
        // Set icon and modal class based on type
        modalIcon.removeClass('dashicons-warning dashicons-yes-alt dashicons-info');
        modal.removeClass('success error info');
        
        if (type === 'error') {
            modalIcon.addClass('dashicons-warning');
            modal.addClass('error');
        } else if (type === 'success') {
            modalIcon.addClass('dashicons-yes-alt');
            modal.addClass('success');
        } else {
            modalIcon.addClass('dashicons-info');
            modal.addClass('info');
        }
        
        // Show/hide buttons
        confirmBtn.show();
        cancelBtn.hide();
        
        // Show modal
        modal.fadeIn(200);
        $('body').addClass('modal-open');
        
        console.log('Modal displayed with:', { type, title, message });
        console.log('Final modal HTML:', modal.html());
    }
    
    function hideModal() {
        $('#export-modal').fadeOut(200);
        $('body').removeClass('modal-open');
    }
    
    // Modal event handlers
    $('#export-modal-confirm, #export-modal-close, .export-modal-overlay').on('click', function() {
        hideModal();
    });
    
    // Prevent modal close when clicking content
    $('.export-modal-content').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>






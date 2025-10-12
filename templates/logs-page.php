<?php
/**
 * Logs page template for Log Viewer role
 * 
 * This page provides read-only access to system logs for Log Viewer users
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin data
$plugin_data = get_plugin_data(WP_GPT_RAG_CHAT_PLUGIN_DIR . 'wp-gpt-rag-chat.php');
$user_role = WP_GPT_RAG_Chat\RBAC::get_user_role_display();
?>

<div class="wrap">
    <h1><?php esc_html_e('System Logs', 'wp-gpt-rag-chat'); ?></h1>
    
    <!-- User Role Info -->
    <div class="notice notice-info">
        <p>
            <strong><?php esc_html_e('Your Role:', 'wp-gpt-rag-chat'); ?></strong> 
            <?php echo esc_html($user_role); ?> - 
            <?php esc_html_e('You have read-only access to system logs.', 'wp-gpt-rag-chat'); ?>
        </p>
    </div>
    
    <!-- Logs Container -->
    <div class="logs-container">
        <div class="logs-header">
            <h2><?php esc_html_e('Recent System Logs', 'wp-gpt-rag-chat'); ?></h2>
            <div class="logs-controls">
                <button type="button" class="button" id="refresh-logs">
                    <?php esc_html_e('Refresh Logs', 'wp-gpt-rag-chat'); ?>
                </button>
                <button type="button" class="button" id="clear-logs" disabled>
                    <?php esc_html_e('Clear Logs', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
        </div>
        
        <!-- Logs Display -->
        <div class="logs-content">
            <div id="logs-display" class="logs-display">
                <div class="loading-message">
                    <p><?php esc_html_e('Loading logs...', 'wp-gpt-rag-chat'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Log Statistics -->
        <div class="logs-stats">
            <div class="stat-item">
                <span class="stat-label"><?php esc_html_e('Total Logs:', 'wp-gpt-rag-chat'); ?></span>
                <span class="stat-value" id="total-logs">-</span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php esc_html_e('Error Logs:', 'wp-gpt-rag-chat'); ?></span>
                <span class="stat-value error-count" id="error-logs">-</span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php esc_html_e('Warning Logs:', 'wp-gpt-rag-chat'); ?></span>
                <span class="stat-value warning-count" id="warning-logs">-</span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php esc_html_e('Info Logs:', 'wp-gpt-rag-chat'); ?></span>
                <span class="stat-value info-count" id="info-logs">-</span>
            </div>
        </div>
    </div>
</div>

<style>
.logs-container {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
    overflow: hidden;
}

.logs-header {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logs-header h2 {
    margin: 0;
    color: #1d2327;
    font-size: 18px;
}

.logs-controls {
    display: flex;
    gap: 10px;
}

.logs-content {
    padding: 20px;
}

.logs-display {
    background: #1e1e1e;
    color: #ffffff;
    padding: 20px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.5;
    max-height: 500px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.loading-message {
    text-align: center;
    padding: 40px;
    color: #646970;
}

.logs-stats {
    background: #f8f9fa;
    padding: 20px;
    border-top: 1px solid #e1e5e9;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.stat-label {
    font-size: 14px;
    color: #646970;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #1d2327;
}

.stat-value.error-count {
    color: #d63638;
}

.stat-value.warning-count {
    color: #dba617;
}

.stat-value.info-count {
    color: #00a32a;
}

.log-entry {
    margin-bottom: 10px;
    padding: 8px;
    border-radius: 4px;
}

.log-entry.error {
    background: rgba(214, 54, 56, 0.1);
    border-left: 3px solid #d63638;
}

.log-entry.warning {
    background: rgba(219, 166, 23, 0.1);
    border-left: 3px solid #dba617;
}

.log-entry.info {
    background: rgba(0, 163, 42, 0.1);
    border-left: 3px solid #00a32a;
}

.log-timestamp {
    color: #646970;
    font-size: 12px;
}

.log-message {
    color: #ffffff;
    margin-top: 5px;
}

@media (max-width: 768px) {
    .logs-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .logs-controls {
        width: 100%;
        justify-content: center;
    }
    
    .logs-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .logs-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load logs on page load
    loadLogs();
    
    // Refresh logs button
    $('#refresh-logs').on('click', function() {
        loadLogs();
    });
    
    // Clear logs button (disabled for Log Viewer role)
    $('#clear-logs').on('click', function() {
        if (confirm('<?php esc_html_e('Are you sure you want to clear all logs?', 'wp-gpt-rag-chat'); ?>')) {
            clearLogs();
        }
    });
    
    function loadLogs() {
        $('#logs-display').html('<div class="loading-message"><p><?php esc_html_e('Loading logs...', 'wp-gpt-rag-chat'); ?></p></div>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_gpt_rag_chat_get_logs',
                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_logs'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    displayLogs(response.data.logs);
                    updateStats(response.data.stats);
                } else {
                    $('#logs-display').html('<div class="error-message"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $('#logs-display').html('<div class="error-message"><p><?php esc_html_e('Error loading logs.', 'wp-gpt-rag-chat'); ?></p></div>');
            }
        });
    }
    
    function displayLogs(logs) {
        if (!logs || logs.length === 0) {
            $('#logs-display').html('<div class="no-logs"><p><?php esc_html_e('No logs found.', 'wp-gpt-rag-chat'); ?></p></div>');
            return;
        }
        
        let html = '';
        logs.forEach(function(log) {
            html += '<div class="log-entry ' + log.level + '">';
            html += '<div class="log-timestamp">' + log.timestamp + '</div>';
            html += '<div class="log-message">' + log.message + '</div>';
            html += '</div>';
        });
        
        $('#logs-display').html(html);
    }
    
    function updateStats(stats) {
        $('#total-logs').text(stats.total || 0);
        $('#error-logs').text(stats.error || 0);
        $('#warning-logs').text(stats.warning || 0);
        $('#info-logs').text(stats.info || 0);
    }
    
    function clearLogs() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_gpt_rag_chat_clear_logs',
                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_logs'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    loadLogs();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('<?php esc_html_e('Error clearing logs.', 'wp-gpt-rag-chat'); ?>');
            }
        });
    }
});
</script>
<?php
/**
 * WordPress-Style Diagnostics Page with Proper Styling
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check current user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

global $wpdb;
$logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
$vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
$queue_table = $wpdb->prefix . 'wp_gpt_rag_indexing_queue';
$errors_table = $wpdb->prefix . 'wp_gpt_rag_chat_errors';
$usage_table = $wpdb->prefix . 'wp_gpt_rag_chat_api_usage';

// Get settings
$settings = \WP_GPT_RAG_Chat\Settings::get_settings();

// Get system statistics
$total_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$logs_table}");
$total_vectors = $wpdb->get_var("SELECT COUNT(*) FROM {$vectors_table}");
$total_posts = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$vectors_table}");
$queue_stats = $wpdb->get_row("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
    FROM {$queue_table}", ARRAY_A);

$recent_logs = $wpdb->get_results("SELECT * FROM {$logs_table} ORDER BY created_at DESC LIMIT 10");
$recent_errors = $wpdb->get_results("SELECT * FROM {$errors_table} ORDER BY created_at DESC LIMIT 5");

// Get WordPress info
$wp_version = get_bloginfo('version');
$php_version = PHP_VERSION;
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');

?>
<style>
/* FontAwesome Icons */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

/* WordPress Admin Exact Styling */
.wp-admin .diagnostics-page {
    margin: 20px 20px 0 2px;
}

.wp-admin .diagnostics-page h1 {
    font-size: 23px;
    font-weight: 400;
    margin: 0;
    padding: 9px 0 4px 0;
    line-height: 1.3;
}

.wp-admin .diagnostics-page .description {
    color: #646970;
    font-style: italic;
    margin: 0 0 20px 0;
}

/* WordPress Postbox Styling */
.wp-admin .postbox {
  background: #fff;
  border: 1px solid #c3c4c7;
  box-shadow: 0 1px 1px rgba(0,0,0,.04);
  margin: 0;
}

.wp-admin .postbox-header {
    background: #f6f7f7;
    border-bottom: 1px solid #c3c4c7;
    padding: 0;
    position: relative;
}

.wp-admin .postbox-header h2,
.wp-admin .postbox-header h3 {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    margin: 0;
    padding: 12px 20px;
    color: #1d2327;
    border: none;
    background: none;
}

.wp-admin .postbox .inside {
    margin: 0;
    padding: 20px;
}

.wp-admin .postbox .inside p {
    margin: 0 0 1em 0;
    line-height: 1.5;
}

/* WordPress Table Styling */
.wp-admin .widefat {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin: 0;
}

.wp-admin .widefat th,
.wp-admin .widefat td {
    padding: 8px 10px;
    text-align: left;
    border-bottom: 1px solid #c3c4c7;
    vertical-align: top;
}

.wp-admin .widefat th {
    background: #f6f7f7;
    font-weight: 600;
    color: #1d2327;
}

.wp-admin .widefat tbody tr:hover {
    background-color: #f6f7f7;
}

/* WordPress Button Styling */
.wp-admin .button {
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
    line-height: 2.15384615;
    min-height: 30px;
    margin: 0;
    padding: 0 10px;
    cursor: pointer;
    border-width: 1px;
    border-style: solid;
    -webkit-appearance: none;
    appearance: none;
    border-radius: 3px;
    white-space: nowrap;
    box-sizing: border-box;
}

.wp-admin .button-primary {
    background: #2271b1;
    border-color: #2271b1;
    color: #fff;
    text-decoration: none;
    text-shadow: none;
}

.wp-admin .button-primary:hover {
    background: #135e96;
    border-color: #135e96;
    color: #fff;
}

.wp-admin .button-secondary {
    background: #f6f7f7;
    border-color: #dcdcde;
    color: #50575e;
}

.wp-admin .button-secondary:hover {
    background: #f0f0f1;
    border-color: #8c8f94;
    color: #1d2327;
}

/* WordPress Notice Styling */
.wp-admin .notice {
    background: #fff;
    border-left: 4px solid #fff;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    margin: 5px 15px 2px;
    padding: 1px 12px;
}

.wp-admin .notice-success {
    border-left-color: #00a32a;
}

.wp-admin .notice-error {
    border-left-color: #d63638;
}

.wp-admin .notice p {
    margin: 12px 0;
    font-size: 13px;
    line-height: 1.5;
}

/* Custom Grid Layout */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.queue-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

/* Status Icons */
.status-icon {
    margin-right: 8px;
    font-size: 16px;
}

.status-success { color: #00a32a; }
.status-error { color: #d63638; }
.status-warning { color: #dba617; }
.status-info { color: #2271b1; }

/* Progress Bar */
.progress-container {
    background: #f0f0f1;
    border-radius: 3px;
    height: 20px;
    margin: 10px 0;
    overflow: hidden;
}

.progress-fill {
    background: linear-gradient(90deg, #2271b1, #00a32a);
    height: 100%;
    transition: width 0.3s ease;
    border-radius: 3px;
}

/* Log Display */
.logs-container {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #c3c4c7;
    border-radius: 3px;
}

.log-entry {
    padding: 12px;
    border-bottom: 1px solid #f0f0f1;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.log-entry:last-child {
    border-bottom: none;
}

.log-time {
    font-size: 12px;
    color: #646970;
    margin-bottom: 4px;
}

.log-content {
    font-size: 13px;
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .queue-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="wrap diagnostics-page">
    <h1><i class="fas fa-tools"></i> System Diagnostics</h1>
    <p class="description">Comprehensive system health monitoring and diagnostics for Nuwab AI Assistant</p>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-chart-bar status-icon status-info"></i>Total Logs</h3>
            </div>
            <div class="inside">
                <div style="font-size: 2.5em; font-weight: 600; color: #2271b1; margin: 15px 0;">
                    <?php echo number_format($total_logs); ?>
                </div>
                <p class="description">Chat interactions logged</p>
            </div>
        </div>
        
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-folder-open status-icon status-info"></i>Indexed Content</h3>
            </div>
            <div class="inside">
                <div style="font-size: 2.5em; font-weight: 600; color: #2271b1; margin: 15px 0;">
                    <?php echo number_format($total_posts); ?>
                </div>
                <p class="description">Posts indexed</p>
            </div>
        </div>
        
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-link status-icon status-info"></i>Vector Chunks</h3>
            </div>
            <div class="inside">
                <div style="font-size: 2.5em; font-weight: 600; color: #2271b1; margin: 15px 0;">
                    <?php echo number_format($total_vectors); ?>
                </div>
                <p class="description">Content chunks</p>
            </div>
        </div>
        
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-clock status-icon status-warning"></i>Queue Status</h3>
            </div>
            <div class="inside">
                <div style="font-size: 2.5em; font-weight: 600; color: #dba617; margin: 15px 0;">
                    <?php echo $queue_stats['pending'] ?? 0; ?>
                </div>
                <p class="description">Pending items</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        
        <!-- Connection Tests -->
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-plug status-icon status-info"></i>Connection Tests</h3>
            </div>
            <div class="inside">
                <p>Test connections to external services and APIs:</p>
                
                <div style="margin: 15px 0;">
                    <button type="button" class="button button-primary" id="test-openai" style="margin: 5px;">
                        <i class="fas fa-globe"></i> Test OpenAI
                    </button>
                    
                    <button type="button" class="button button-primary" id="test-pinecone" style="margin: 5px;">
                        <i class="fas fa-database"></i> Test Pinecone
                    </button>
                    
                    <button type="button" class="button button-primary" id="test-wordpress" style="margin: 5px;">
                        <i class="fas fa-wordpress"></i> Test WordPress
                    </button>
                    
                    <button type="button" class="button button-secondary" id="test-all" style="margin: 5px;">
                        <i class="fas fa-sync-alt"></i> Test All
                    </button>
                </div>
                
                <div id="test-results"></div>
            </div>
        </div>

        <!-- System Configuration -->
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-cog status-icon status-info"></i>System Configuration</h3>
            </div>
            <div class="inside">
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td style="width: 40%;"><strong>Chatbot Status</strong></td>
                            <td>
                                <?php if (!empty($settings['enable_chatbot'])): ?>
                                    <i class="fas fa-check-circle status-icon status-success"></i>Enabled
                                <?php else: ?>
                                    <i class="fas fa-times-circle status-icon status-error"></i>Disabled
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>OpenAI API</strong></td>
                            <td>
                                <?php if (!empty($settings['openai_api_key'])): ?>
                                    <i class="fas fa-check-circle status-icon status-success"></i>Configured
                                <?php else: ?>
                                    <i class="fas fa-times-circle status-icon status-error"></i>Not configured
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Pinecone API</strong></td>
                            <td>
                                <?php if (!empty($settings['pinecone_api_key'])): ?>
                                    <i class="fas fa-check-circle status-icon status-success"></i>Configured
                                <?php else: ?>
                                    <i class="fas fa-times-circle status-icon status-error"></i>Not configured
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>GPT Model</strong></td>
                            <td><?php echo esc_html($settings['gpt_model'] ?? 'Not set'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Chat Visibility</strong></td>
                            <td><?php echo esc_html($settings['chat_visibility'] ?? 'Not set'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Database Status -->
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-database status-icon status-info"></i>Database Status</h3>
            </div>
            <div class="inside">
                <?php
                $current_version = get_option('wp_gpt_rag_chat_db_version', '1.0.0');
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$logs_table}'");
                $rag_metadata_exists = false;
                
                if ($table_exists) {
                    $columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
                    $column_names = wp_list_pluck($columns, 'Field');
                    $rag_metadata_exists = in_array('rag_metadata', $column_names);
                }
                ?>
                
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td style="width: 40%;"><strong>Database Version</strong></td>
                            <td><?php echo esc_html($current_version); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Logs Table</strong></td>
                            <td>
                                <?php if ($table_exists): ?>
                                    <i class="fas fa-check-circle status-icon status-success"></i>Exists
                                <?php else: ?>
                                    <i class="fas fa-times-circle status-icon status-error"></i>Missing
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Vectors Table</strong></td>
                            <td>
                                <?php if ($wpdb->get_var("SHOW TABLES LIKE '{$vectors_table}'")): ?>
                                    <i class="fas fa-check-circle status-icon status-success"></i>Exists
                                <?php else: ?>
                                    <i class="fas fa-times-circle status-icon status-error"></i>Missing
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Queue Table</strong></td>
                            <td>
                                <?php if ($wpdb->get_var("SHOW TABLES LIKE '{$queue_table}'")): ?>
                                    <i class="fas fa-check-circle status-icon status-success"></i>Exists
                                <?php else: ?>
                                    <i class="fas fa-times-circle status-icon status-error"></i>Missing
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Schema Status</strong></td>
                            <td>
                                <?php if ($rag_metadata_exists): ?>
                                    <i class="fas fa-check-circle status-icon status-success"></i>Up to date
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle status-icon status-warning"></i>Needs migration
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php if (!$rag_metadata_exists): ?>
                    <div style="margin-top: 15px;">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="run_migration" value="1">
                            <?php wp_nonce_field('run_migration', 'migration_nonce'); ?>
                            <button type="submit" class="button button-primary">
                                <i class="fas fa-sync-alt"></i> Run Migration
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Server Information -->
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-server status-icon status-info"></i>Server Information</h3>
            </div>
            <div class="inside">
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td style="width: 40%;"><strong>WordPress Version</strong></td>
                            <td><?php echo esc_html($wp_version); ?></td>
                        </tr>
                        <tr>
                            <td><strong>PHP Version</strong></td>
                            <td><?php echo esc_html($php_version); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Memory Limit</strong></td>
                            <td><?php echo esc_html($memory_limit); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max Execution Time</strong></td>
                            <td><?php echo esc_html($max_execution_time); ?>s</td>
                        </tr>
                        <tr>
                            <td><strong>Plugin Version</strong></td>
                            <td><?php echo defined('WP_GPT_RAG_CHAT_VERSION') ? WP_GPT_RAG_CHAT_VERSION : 'Unknown'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Indexing Queue Status -->
    <div class="postbox">
        <div class="postbox-header">
            <h3><i class="fas fa-list-alt status-icon status-info"></i>Indexing Queue Status</h3>
        </div>
        <div class="inside">
            <?php if ($queue_stats): ?>
                <div class="queue-grid">
                    <div class="postbox" style="margin: 0;">
                        <div class="postbox-header">
                            <h4><i class="fas fa-list status-icon status-info"></i>Total Items</h4>
                        </div>
                        <div class="inside">
                            <div style="font-size: 1.8em; font-weight: 600; color: #2271b1;">
                                <?php echo number_format($queue_stats['total']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="postbox" style="margin: 0;">
                        <div class="postbox-header">
                            <h4><i class="fas fa-clock status-icon status-warning"></i>Pending</h4>
                        </div>
                        <div class="inside">
                            <div style="font-size: 1.8em; font-weight: 600; color: #dba617;">
                                <?php echo number_format($queue_stats['pending']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="postbox" style="margin: 0;">
                        <div class="postbox-header">
                            <h4><i class="fas fa-sync-alt status-icon status-info"></i>Processing</h4>
                        </div>
                        <div class="inside">
                            <div style="font-size: 1.8em; font-weight: 600; color: #2271b1;">
                                <?php echo number_format($queue_stats['processing']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="postbox" style="margin: 0;">
                        <div class="postbox-header">
                            <h4><i class="fas fa-check-circle status-icon status-success"></i>Completed</h4>
                        </div>
                        <div class="inside">
                            <div style="font-size: 1.8em; font-weight: 600; color: #00a32a;">
                                <?php echo number_format($queue_stats['completed']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="postbox" style="margin: 0;">
                        <div class="postbox-header">
                            <h4><i class="fas fa-times-circle status-icon status-error"></i>Failed</h4>
                        </div>
                        <div class="inside">
                            <div style="font-size: 1.8em; font-weight: 600; color: #d63638;">
                                <?php echo number_format($queue_stats['failed']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($queue_stats['total'] > 0): ?>
                    <div class="progress-container">
                        <div class="progress-fill" style="width: <?php echo ($queue_stats['completed'] / $queue_stats['total']) * 100; ?>%;"></div>
                    </div>
                    <p style="text-align: center; margin: 10px 0; font-weight: 600;">
                        <?php echo round(($queue_stats['completed'] / $queue_stats['total']) * 100, 1); ?>% Complete
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p>No queue data available.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="content-grid">
        
        <!-- Recent Logs -->
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-file-alt status-icon status-info"></i>Recent Chat Logs</h3>
            </div>
            <div class="inside">
                <?php if (!empty($recent_logs)): ?>
                    <div class="logs-container">
                        <?php foreach ($recent_logs as $log): ?>
                            <div class="log-entry">
                                <div class="log-time">
                                    <?php echo date('Y-m-d H:i:s', strtotime($log->created_at)); ?>
                                </div>
                                <div class="log-content">
                                    <strong><?php echo esc_html($log->role); ?>:</strong> 
                                    <?php echo esc_html(substr($log->content, 0, 100)); ?>
                                    <?php if (strlen($log->content) > 100): ?>...<?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No recent logs found. Try using the chat widget or run a test below.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Test Logging -->
        <div class="postbox">
            <div class="postbox-header">
                <h3><i class="fas fa-flask status-icon status-info"></i>Test Logging System</h3>
            </div>
            <div class="inside">
                <p>Test if the logging system is working properly:</p>
                
                <form method="post" style="margin: 15px 0;">
                    <input type="hidden" name="test_logging" value="1">
                    <?php wp_nonce_field('test_logging', 'test_nonce'); ?>
                    <button type="submit" class="button button-primary">
                        <i class="fas fa-file-plus"></i> Run Test Insert
                    </button>
                </form>
                
                <?php
                if (isset($_POST['test_logging']) && wp_verify_nonce($_POST['test_nonce'], 'test_logging')) {
                    $analytics = new WP_GPT_RAG_Chat\Analytics();
                    $test_chat_id = $analytics->generate_chat_id();
                    
                    $test_data = [
                        'chat_id' => $test_chat_id,
                        'turn_number' => 1,
                        'role' => 'user',
                        'content' => 'Test message from diagnostics page - ' . date('Y-m-d H:i:s'),
                        'user_id' => get_current_user_id()
                    ];
                    
                    $result = $analytics->log_interaction($test_data);
                    
                    if ($result) {
                        echo "<div class='notice notice-success is-dismissible'><p><i class='fas fa-check-circle'></i> Test insert successful! Log ID: {$result}</p></div>";
                    } else {
                        echo "<div class='notice notice-error is-dismissible'><p><i class='fas fa-times-circle'></i> Test insert failed!</p></div>";
                        echo "<p>Error: " . $wpdb->last_error . "</p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- WordPress-Style JavaScript -->
<script>
console.log('=== WORDPRESS DIAGNOSTICS PAGE LOADED ===');

// Define ajaxurl for admin pages
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

// Test if jQuery is available
if (typeof jQuery !== 'undefined') {
    console.log('✅ jQuery is available, version:', jQuery.fn.jquery);
} else {
    console.log('❌ jQuery is NOT available');
}

// Wait for document ready
jQuery(document).ready(function($) {
    console.log('✅ Document ready fired');
    
    // Test button detection
    var buttons = $('button[id^="test-"]');
    console.log('✅ Found', buttons.length, 'test buttons');
    
    // Add click handlers
    $('#test-openai').on('click', function() {
        console.log('🔵 OpenAI button clicked');
        testConnection('openai', $(this));
    });
    
    $('#test-pinecone').on('click', function() {
        console.log('🔵 Pinecone button clicked');
        testConnection('pinecone', $(this));
    });
    
    $('#test-wordpress').on('click', function() {
        console.log('🔵 WordPress button clicked');
        testConnection('wordpress', $(this));
    });
    
    $('#test-all').on('click', function() {
        console.log('🔵 Test All button clicked');
        testAllConnections();
    });
    
    console.log('✅ All click handlers attached');
    
    // WordPress-style test connection function
    function testConnection(type, $button) {
        console.log('🔄 Testing connection:', type);
        
        // Store original text
        if (!$button.data('original-text')) {
            $button.data('original-text', $button.html());
        }
        
        // Update button with WordPress styling
        $button.prop('disabled', true)
               .removeClass('button-primary button-secondary')
               .addClass('button-secondary')
               .html('<i class="fas fa-sync-alt fa-spin"></i> Testing...');
        
        // Make AJAX request
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wp_gpt_rag_chat_test_connection',
                connection_type: type,
                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
            },
            success: function(response) {
                console.log('✅ AJAX success for', type, response);
                
                if (response.success) {
                    $button.removeClass('button-secondary')
                           .addClass('button-primary')
                           .html('<i class="fas fa-check-circle"></i> Success');
                    showResult(type, response.data.message, 'success');
                } else {
                    $button.removeClass('button-secondary')
                           .addClass('button-secondary')
                           .html('<i class="fas fa-times-circle"></i> Failed');
                    showResult(type, response.data.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('❌ AJAX error for', type, status, error);
                $button.removeClass('button-secondary')
                       .addClass('button-secondary')
                       .html('<i class="fas fa-exclamation-triangle"></i> Error');
                showResult(type, 'Connection test failed: ' + error, 'error');
            },
            complete: function() {
                // Re-enable button after 3 seconds
                setTimeout(function() {
                    $button.prop('disabled', false)
                           .removeClass('button-primary button-secondary')
                           .addClass('button-primary')
                           .html($button.data('original-text'));
                }, 3000);
            }
        });
    }
    
    // Test all connections
    function testAllConnections() {
        console.log('🔄 Testing all connections');
        $('#test-openai, #test-pinecone, #test-wordpress').each(function() {
            testConnection($(this).attr('id').replace('test-', ''), $(this));
        });
    }
    
    // WordPress-style result display
    function showResult(type, message, status) {
        var $results = $('#test-results');
        var statusClass = status === 'success' ? 'notice-success' : 'notice-error';
        var icon = status === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
        
        var resultHtml = '<div class="notice ' + statusClass + ' is-dismissible" style="margin: 10px 0;">' +
            '<p style="margin: 0.5em 0;">' + icon + ' <strong>' + type.toUpperCase() + ':</strong> ' + message + '</p>' +
            '</div>';
        
        $results.append(resultHtml);
        
        // Auto-dismiss after 8 seconds
        setTimeout(function() {
            $results.find('.notice').last().fadeOut(500, function() {
                $(this).remove();
            });
        }, 8000);
    }
    
    console.log('✅ All functions defined');
    console.log('=== WORDPRESS DIAGNOSTICS PAGE READY ===');
});
</script>

<?php
// Handle migration request
if (isset($_POST['run_migration']) && wp_verify_nonce($_POST['migration_nonce'], 'run_migration')) {
    delete_option('wp_gpt_rag_chat_db_version');
    WP_GPT_RAG_Chat\Migration::run_migrations();
    echo "<div class='notice notice-success is-dismissible'><p><i class='fas fa-check-circle'></i> Migration completed! Please refresh this page.</p></div>";
    echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
}
?>
<?php
/**
 * Diagnostics Page - Check database and logging status
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

?>
<div class="wrap cornuwab-admin-wrap">
    <h1><?php _e('Chat System Diagnostics', 'wp-gpt-rag-chat'); ?></h1>
    
    <style>
        .diagnostic-section { 
            background: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-left: 4px solid #0073aa; 
        }
        .diagnostic-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .diagnostic-table th, 
        .diagnostic-table td { 
            padding: 10px; 
            border: 1px solid #ddd; 
            text-align: left; 
        }
        .diagnostic-table th { 
            background: #f4f4f4; 
        }
        .status-ok { color: green; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        .status-warning { color: orange; font-weight: bold; }
    </style>
    
    <!-- Database Status -->
    <div class="diagnostic-section">
        <h2>📊 Database Status</h2>
        <?php
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$logs_table}'");
        
        if ($table_exists) {
            echo "<p class='status-ok'>✅ Table '{$logs_table}' exists</p>";
            
            // Show columns
            $columns = $wpdb->get_results("SHOW COLUMNS FROM {$logs_table}");
            echo "<h3>Table Structure:</h3>";
            echo "<table class='diagnostic-table'>";
            echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Default</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>{$column->Field}</td>";
                echo "<td>{$column->Type}</td>";
                echo "<td>{$column->Null}</td>";
                echo "<td>" . ($column->Default ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Check for required columns
            $column_names = wp_list_pluck($columns, 'Field');
            $required_columns = [
                'id', 'chat_id', 'turn_number', 'role', 'user_id', 'ip_address', 
                'content', 'response_latency', 'sources_count', 'rag_sources', 
                'rating', 'tags', 'model_used', 'tokens_used', 'rag_metadata', 'created_at'
            ];
            
            echo "<h3>Required Columns Check:</h3>";
            $missing_columns = [];
            foreach ($required_columns as $req_col) {
                if (!in_array($req_col, $column_names)) {
                    $missing_columns[] = $req_col;
                }
            }
            
            if (empty($missing_columns)) {
                echo "<p class='status-ok'>✅ All required columns exist</p>";
            } else {
                echo "<p class='status-error'>❌ Missing columns: " . implode(', ', $missing_columns) . "</p>";
                echo "<p><strong>Action needed:</strong> Run database migration</p>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='run_migration' value='1'>";
                wp_nonce_field('run_migration', 'migration_nonce');
                echo "<button type='submit' class='button button-primary'>Run Migration Now</button>";
                echo "</form>";
            }
        } else {
            echo "<p class='status-error'>❌ Table '{$logs_table}' does NOT exist!</p>";
            echo "<p>Please deactivate and reactivate the plugin to create the table.</p>";
        }
        ?>
    </div>
    
    <!-- Database Version -->
    <div class="diagnostic-section">
        <h2>🔢 Database Version</h2>
        <?php
        $db_version = get_option('wp_gpt_rag_chat_db_version', 'Not set');
        echo "<p>Current version: <strong>{$db_version}</strong></p>";
        echo "<p>Required version: <strong>2.1.0</strong></p>";
        
        if (version_compare($db_version, '2.1.0', '<')) {
            echo "<p class='status-warning'>⚠️ Database needs migration!</p>";
        } else {
            echo "<p class='status-ok'>✅ Database is up to date</p>";
        }
        ?>
    </div>
    
    <!-- Recent Logs -->
    <div class="diagnostic-section">
        <h2>📝 Recent Log Entries</h2>
        <?php
        $recent_logs = $wpdb->get_results("SELECT * FROM {$logs_table} ORDER BY created_at DESC LIMIT 10");
        
        if ($recent_logs) {
            echo "<p>Found " . count($recent_logs) . " recent entries:</p>";
            echo "<table class='diagnostic-table'>";
            echo "<tr><th>ID</th><th>Chat ID</th><th>Turn</th><th>Role</th><th>Content (100 chars)</th><th>Created</th></tr>";
            foreach ($recent_logs as $log) {
                echo "<tr>";
                echo "<td>{$log->id}</td>";
                echo "<td>" . substr($log->chat_id, 0, 25) . "...</td>";
                echo "<td>{$log->turn_number}</td>";
                echo "<td>{$log->role}</td>";
                echo "<td>" . esc_html(substr($log->content, 0, 100)) . "...</td>";
                echo "<td>{$log->created_at}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='status-warning'>⚠️ No log entries found. Try asking a question in the chat.</p>";
        }
        ?>
    </div>
    
    <!-- Test Logging -->
    <div class="diagnostic-section">
        <h2>🧪 Test Logging</h2>
        <p>Click the button below to test if logging is working:</p>
        <form method="post">
            <input type="hidden" name="test_logging" value="1">
            <?php wp_nonce_field('test_logging', 'test_nonce'); ?>
            <button type="submit" class="button button-secondary">Run Test Insert</button>
        </form>
        
        <?php
        if (isset($_POST['test_logging']) && wp_verify_nonce($_POST['test_nonce'], 'test_logging')) {
            $analytics = new WP_GPT_RAG_Chat\Analytics();
            $test_chat_id = $analytics->generate_chat_id();
            
            $test_data = [
                'chat_id' => $test_chat_id,
                'turn_number' => 1,
                'role' => 'user',
                'content' => 'Test message from diagnostics',
                'user_id' => get_current_user_id()
            ];
            
            $result = $analytics->log_interaction($test_data);
            
            if ($result) {
                echo "<p class='status-ok'>✅ Test insert successful! Log ID: {$result}</p>";
            } else {
                echo "<p class='status-error'>❌ Test insert failed!</p>";
                echo "<p>Error: " . $wpdb->last_error . "</p>";
            }
        }
        ?>
    </div>
    
    <!-- Sitemap Indexing -->
    <div class="diagnostic-section">
        <h2>🗺️ Sitemap Fallback Index</h2>
        <?php
        $sitemap = new WP_GPT_RAG_Chat\Sitemap();
        $indexed_count = $sitemap->get_indexed_count();
        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
        $sitemap_url = $settings['sitemap_url'] ?? 'sitemap.xml';
        ?>
        
        <p><?php _e('Sitemap fallback allows the chatbot to suggest relevant pages when no answers are found in the knowledge base.', 'wp-gpt-rag-chat'); ?></p>
        
        <table class="diagnostic-table">
            <tr>
                <th><?php _e('Status', 'wp-gpt-rag-chat'); ?></th>
                <td>
                    <?php if ($settings['enable_sitemap_fallback'] ?? true): ?>
                        <span class="status-ok">✅ <?php _e('Enabled', 'wp-gpt-rag-chat'); ?></span>
                    <?php else: ?>
                        <span class="status-warning">⚠️ <?php _e('Disabled', 'wp-gpt-rag-chat'); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('Sitemap URL', 'wp-gpt-rag-chat'); ?></th>
                <td><code><?php echo esc_html($sitemap_url); ?></code></td>
            </tr>
            <tr>
                <th><?php _e('Indexed URLs', 'wp-gpt-rag-chat'); ?></th>
                <td><strong><?php echo esc_html($indexed_count); ?></strong> pages</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <button type="button" id="index-sitemap-btn" class="button button-primary">
                <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
                <?php _e('Index Sitemap Now', 'wp-gpt-rag-chat'); ?>
            </button>
            
            <button type="button" id="clear-sitemap-btn" class="button button-secondary" style="margin-left: 10px;">
                <span class="dashicons dashicons-trash" style="margin-top: 3px;"></span>
                <?php _e('Clear Sitemap Index', 'wp-gpt-rag-chat'); ?>
            </button>
            
            <button type="button" id="cancel-sitemap-indexing" class="button button-secondary" style="display: none; margin-left: 10px; background: #d63638; color: white; border-color: #d63638;">
                <span class="dashicons dashicons-no" style="margin-top: 3px;"></span>
                <?php _e('Cancel', 'wp-gpt-rag-chat'); ?>
            </button>
        </div>
        
        <div id="sitemap-indexing-progress" style="display: none; margin-top: 15px;">
            <div style="background: #f0f0f0; border-radius: 4px; height: 30px; position: relative; overflow: hidden;">
                <div id="sitemap-progress-fill" style="background: linear-gradient(90deg, #2271b1, #135e96); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
            </div>
            <div style="margin-top: 8px; display: flex; justify-content: space-between; font-size: 13px;">
                <span id="sitemap-progress-message"><?php _e('Preparing...', 'wp-gpt-rag-chat'); ?></span>
                <span id="sitemap-progress-stats"></span>
            </div>
        </div>
        
        <div id="sitemap-indexing-status" style="margin-top: 15px;"></div>
        
        <script>
        jQuery(document).ready(function($) {
            var sitemapIndexingCancelled = false;
            var currentSitemapOffset = 0;
            var totalSitemapUrls = 0;
            
            $('#index-sitemap-btn').on('click', function() {
                sitemapIndexingCancelled = false;
                currentSitemapOffset = 0;
                totalSitemapUrls = 0;
                
                $('#index-sitemap-btn').prop('disabled', true);
                $('#cancel-sitemap-indexing').show();
                $('#sitemap-indexing-progress').fadeIn();
                $('#sitemap-indexing-status').empty();
                $('#sitemap-progress-fill').css('width', '0%');
                $('#sitemap-progress-message').text('<?php _e('Starting...', 'wp-gpt-rag-chat'); ?>');
                
                indexSitemapNextBatch();
            });
            
            $('#cancel-sitemap-indexing').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to cancel sitemap indexing?', 'wp-gpt-rag-chat'); ?>')) {
                    sitemapIndexingCancelled = true;
                    $(this).prop('disabled', true).text('<?php _e('Cancelling...', 'wp-gpt-rag-chat'); ?>');
                }
            });
            
            function indexSitemapNextBatch() {
                if (sitemapIndexingCancelled) {
                    $('#index-sitemap-btn').prop('disabled', false);
                    $('#cancel-sitemap-indexing').hide().prop('disabled', false).html('<span class="dashicons dashicons-no"></span> <?php _e('Cancel', 'wp-gpt-rag-chat'); ?>');
                    $('#sitemap-progress-message').text('<?php _e('Cancelled by user', 'wp-gpt-rag-chat'); ?>');
                    $('#sitemap-indexing-status').html('<div class="notice notice-warning"><p>⚠️ <?php _e('Indexing cancelled. Processed: ', 'wp-gpt-rag-chat'); ?>' + currentSitemapOffset + ' <?php _e('URLs', 'wp-gpt-rag-chat'); ?></p></div>');
                    
                    setTimeout(function() {
                        $('#sitemap-indexing-progress').fadeOut();
                    }, 2000);
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wp_gpt_rag_chat_index_sitemap_batch',
                        nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>',
                        sitemap_url: '<?php echo esc_js($sitemap_url); ?>',
                        offset: currentSitemapOffset
                    },
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            
                            // Set total on first batch
                            if (totalSitemapUrls === 0) {
                                totalSitemapUrls = data.total;
                            }
                            
                            currentSitemapOffset += data.processed;
                            
                            // Update progress
                            var percentage = totalSitemapUrls > 0 ? Math.min((currentSitemapOffset / totalSitemapUrls) * 100, 100) : 0;
                            $('#sitemap-progress-fill').css('width', percentage + '%');
                            $('#sitemap-progress-message').text('<?php _e('Indexing...', 'wp-gpt-rag-chat'); ?>');
                            $('#sitemap-progress-stats').text(currentSitemapOffset + ' / ' + totalSitemapUrls);
                            
                            // Check if more to process
                            if (data.has_more) {
                                indexSitemapNextBatch();
                            } else {
                                // Complete!
                                $('#index-sitemap-btn').prop('disabled', false);
                                $('#cancel-sitemap-indexing').hide();
                                $('#sitemap-progress-message').text('<?php _e('Completed!', 'wp-gpt-rag-chat'); ?>');
                                $('#sitemap-progress-fill').css('width', '100%');
                                $('#sitemap-indexing-status').html('<div class="notice notice-success"><p>✅ <?php _e('Successfully indexed ', 'wp-gpt-rag-chat'); ?>' + currentSitemapOffset + ' <?php _e('URLs from sitemap.', 'wp-gpt-rag-chat'); ?></p></div>');
                                
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        } else {
                            $('#index-sitemap-btn').prop('disabled', false);
                            $('#cancel-sitemap-indexing').hide();
                            $('#sitemap-indexing-status').html('<div class="notice notice-error"><p>❌ ' + response.data.message + '</p></div>');
                            $('#sitemap-indexing-progress').fadeOut();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#index-sitemap-btn').prop('disabled', false);
                        $('#cancel-sitemap-indexing').hide();
                        $('#sitemap-indexing-status').html('<div class="notice notice-error"><p>❌ Error: ' + error + '</p></div>');
                        $('#sitemap-indexing-progress').fadeOut();
                    }
                });
            }
            
            $('#clear-sitemap-btn').on('click', function() {
                if (!confirm('<?php _e('Are you sure you want to clear the sitemap index?', 'wp-gpt-rag-chat'); ?>')) {
                    return;
                }
                
                var $btn = $(this);
                var $status = $('#sitemap-indexing-status');
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wp_gpt_rag_chat_clear_sitemap',
                        nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<div class="notice notice-success"><p>✅ ' + response.data.message + '</p></div>');
                            setTimeout(function() { location.reload(); }, 1500);
                        } else {
                            $status.html('<div class="notice notice-error"><p>❌ ' + response.data.message + '</p></div>');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        $status.html('<div class="notice notice-error"><p>❌ Error: ' + error + '</p></div>');
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
        </script>
    </div>
    
    <!-- Recent Errors -->
    <div class="diagnostic-section">
        <h2>⚠️ Recent Error Log</h2>
        <?php
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($debug_log) && is_readable($debug_log)) {
            $log_lines = file($debug_log);
            $recent_errors = array_slice($log_lines, -30); // Last 30 lines
            
            $chat_errors = array_filter($recent_errors, function($line) {
                return stripos($line, 'WP GPT RAG Chat') !== false || 
                       stripos($line, 'wp_gpt_rag_chat') !== false ||
                       stripos($line, 'chatbot-nuwab') !== false;
            });
            
            if (!empty($chat_errors)) {
                echo "<pre style='background: #f4f4f4; padding: 10px; overflow-x: auto; max-height: 300px;'>";
                echo esc_html(implode('', $chat_errors));
                echo "</pre>";
            } else {
                echo "<p class='status-ok'>✅ No chat-related errors found in debug.log</p>";
            }
        } else {
            echo "<p>Debug log not found or not readable.</p>";
            echo "<p>To enable WordPress debug logging, add to wp-config.php:</p>";
            echo "<pre>define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true);\ndefine('WP_DEBUG_DISPLAY', false);</pre>";
        }
        ?>
    </div>
</div>

<?php
// Handle migration request
if (isset($_POST['run_migration']) && wp_verify_nonce($_POST['migration_nonce'], 'run_migration')) {
    delete_option('wp_gpt_rag_chat_db_version');
    WP_GPT_RAG_Chat\Migration::run_migrations();
    echo "<div class='notice notice-success'><p>Migration completed! Please refresh this page.</p></div>";
    echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
}


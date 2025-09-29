<?php
/**
 * Logs page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$logger = new WP_GPT_RAG_Chat\Logger();
$stats = $logger->get_chat_statistics('7d');
$daily_stats = $logger->get_daily_usage_stats(7);
$popular_queries = $logger->get_popular_queries(10);
?>

<div class="wrap">
    <h1><?php esc_html_e('Chat Logs & Analytics', 'wp-gpt-rag-chat'); ?></h1>
    
    <div class="wp-gpt-rag-chat-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php esc_html_e('Total Queries (7d)', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['total_queries'])); ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Unique Users (7d)', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['unique_users'])); ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Unique IPs (7d)', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['unique_ips'])); ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Avg Query Length', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['avg_query_length'])); ?></div>
            </div>
        </div>
    </div>
    
    <div class="wp-gpt-rag-chat-logs-content">
        <div class="logs-section">
            <h2><?php esc_html_e('Recent Chat Logs', 'wp-gpt-rag-chat'); ?></h2>
            
            <div class="logs-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wp-gpt-rag-chat-logs" />
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="user_id"><?php esc_html_e('User ID:', 'wp-gpt-rag-chat'); ?></label>
                            <input type="number" id="user_id" name="user_id" value="<?php echo esc_attr($_GET['user_id'] ?? ''); ?>" />
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_from"><?php esc_html_e('From:', 'wp-gpt-rag-chat'); ?></label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($_GET['date_from'] ?? ''); ?>" />
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_to"><?php esc_html_e('To:', 'wp-gpt-rag-chat'); ?></label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($_GET['date_to'] ?? ''); ?>" />
                        </div>
                        
                        <div class="filter-group">
                            <label for="search"><?php esc_html_e('Search:', 'wp-gpt-rag-chat'); ?></label>
                            <input type="text" id="search" name="search" value="<?php echo esc_attr($_GET['search'] ?? ''); ?>" placeholder="<?php esc_attr_e('Search queries or responses...', 'wp-gpt-rag-chat'); ?>" />
                        </div>
                        
                        <div class="filter-group">
                            <input type="submit" class="button button-primary" value="<?php esc_attr_e('Filter', 'wp-gpt-rag-chat'); ?>" />
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-logs')); ?>" class="button"><?php esc_html_e('Clear', 'wp-gpt-rag-chat'); ?></a>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="logs-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-logs&action=export_csv')); ?>" class="button button-secondary">
                    <?php esc_html_e('Export CSV', 'wp-gpt-rag-chat'); ?>
                </a>
                <button type="button" id="cleanup-logs" class="button button-secondary">
                    <?php esc_html_e('Cleanup Old Logs', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
            
            <?php
            $args = [
                'limit' => 50,
                'offset' => intval($_GET['offset'] ?? 0),
                'user_id' => !empty($_GET['user_id']) ? intval($_GET['user_id']) : null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'search' => $_GET['search'] ?? null
            ];
            
            $logs = $logger->get_chat_logs($args);
            ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'wp-gpt-rag-chat'); ?></th>
                        <th><?php esc_html_e('User', 'wp-gpt-rag-chat'); ?></th>
                        <th><?php esc_html_e('IP Address', 'wp-gpt-rag-chat'); ?></th>
                        <th><?php esc_html_e('Query', 'wp-gpt-rag-chat'); ?></th>
                        <th><?php esc_html_e('Response', 'wp-gpt-rag-chat'); ?></th>
                        <th><?php esc_html_e('Date', 'wp-gpt-rag-chat'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            <?php esc_html_e('No logs found.', 'wp-gpt-rag-chat'); ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log->id); ?></td>
                            <td>
                                <?php if ($log->user_id): ?>
                                    <?php
                                    $user = get_user_by('id', $log->user_id);
                                    if ($user) {
                                        echo esc_html($user->display_name);
                                    } else {
                                        echo esc_html($log->user_id);
                                    }
                                    ?>
                                <?php else: ?>
                                    <?php esc_html_e('Guest', 'wp-gpt-rag-chat'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($log->ip_address); ?></td>
                            <td>
                                <div class="log-query">
                                    <?php echo esc_html(wp_trim_words($log->query, 10)); ?>
                                    <?php if (strlen($log->query) > 50): ?>
                                        <button type="button" class="button-link show-full-query" data-query="<?php echo esc_attr($log->query); ?>">
                                            <?php esc_html_e('Show full', 'wp-gpt-rag-chat'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="log-response">
                                    <?php echo esc_html(wp_trim_words($log->response, 10)); ?>
                                    <?php if (strlen($log->response) > 50): ?>
                                        <button type="button" class="button-link show-full-response" data-response="<?php echo esc_attr($log->response); ?>">
                                            <?php esc_html_e('Show full', 'wp-gpt-rag-chat'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo esc_html(human_time_diff(strtotime($log->created_at)) . ' ' . __('ago', 'wp-gpt-rag-chat')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if (count($logs) >= 50): ?>
            <div class="logs-pagination">
                <a href="<?php echo esc_url(add_query_arg('offset', $args['offset'] + 50)); ?>" class="button">
                    <?php esc_html_e('Load More', 'wp-gpt-rag-chat'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="analytics-section">
            <h2><?php esc_html_e('Analytics', 'wp-gpt-rag-chat'); ?></h2>
            
            <div class="analytics-grid">
                <div class="analytics-card">
                    <h3><?php esc_html_e('Daily Usage (Last 7 Days)', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="daily-stats">
                        <?php foreach ($daily_stats as $stat): ?>
                        <div class="daily-stat">
                            <span class="date"><?php echo esc_html(date('M j', strtotime($stat->date))); ?></span>
                            <span class="queries"><?php echo esc_html($stat->queries); ?> <?php esc_html_e('queries', 'wp-gpt-rag-chat'); ?></span>
                            <span class="users"><?php echo esc_html($stat->unique_users); ?> <?php esc_html_e('users', 'wp-gpt-rag-chat'); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="analytics-card">
                    <h3><?php esc_html_e('Popular Queries', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="popular-queries">
                        <?php foreach ($popular_queries as $query): ?>
                        <div class="popular-query">
                            <div class="query-text"><?php echo esc_html(wp_trim_words($query->query, 8)); ?></div>
                            <div class="query-stats">
                                <span class="frequency"><?php echo esc_html($query->frequency); ?> <?php esc_html_e('times', 'wp-gpt-rag-chat'); ?></span>
                                <span class="last-asked"><?php echo esc_html(human_time_diff(strtotime($query->last_asked)) . ' ' . __('ago', 'wp-gpt-rag-chat')); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for showing full query/response -->
<div id="log-modal" class="log-modal" style="display: none;">
    <div class="log-modal-content">
        <div class="log-modal-header">
            <h3 id="log-modal-title"></h3>
            <button type="button" class="log-modal-close">&times;</button>
        </div>
        <div class="log-modal-body">
            <pre id="log-modal-text"></pre>
        </div>
    </div>
</div>

<style>
.logs-filters {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.filter-row {
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    font-size: 12px;
    color: #646970;
}

.filter-group input {
    padding: 6px 8px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
}

.logs-actions {
    margin-bottom: 20px;
}

.log-query,
.log-response {
    max-width: 200px;
    word-wrap: break-word;
}

.log-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.log-modal-content {
    background: #fff;
    border-radius: 4px;
    max-width: 80%;
    max-height: 80%;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.log-modal-header {
    padding: 20px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.log-modal-header h3 {
    margin: 0;
}

.log-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.log-modal-body {
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.log-modal-body pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
    font-family: inherit;
}

.analytics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.analytics-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.analytics-card h3 {
    margin-top: 0;
    color: #1d2327;
}

.daily-stat {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f1;
}

.daily-stat:last-child {
    border-bottom: none;
}

.popular-query {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f1;
}

.popular-query:last-child {
    border-bottom: none;
}

.query-text {
    font-weight: 500;
    margin-bottom: 4px;
}

.query-stats {
    font-size: 12px;
    color: #646970;
    display: flex;
    gap: 15px;
}

@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .analytics-grid {
        grid-template-columns: 1fr;
    }
    
    .log-modal-content {
        max-width: 95%;
        max-height: 90%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Show full query/response
    $('.show-full-query, .show-full-response').on('click', function() {
        var text = $(this).data('query') || $(this).data('response');
        var title = $(this).hasClass('show-full-query') ? '<?php esc_js(__('Full Query', 'wp-gpt-rag-chat')); ?>' : '<?php esc_js(__('Full Response', 'wp-gpt-rag-chat')); ?>';
        
        $('#log-modal-title').text(title);
        $('#log-modal-text').text(text);
        $('#log-modal').show();
    });
    
    // Close modal
    $('.log-modal-close, #log-modal').on('click', function(e) {
        if (e.target === this) {
            $('#log-modal').hide();
        }
    });
    
    // Cleanup logs
    $('#cleanup-logs').on('click', function() {
        if (!confirm('<?php esc_js(__('This will delete old log entries. Continue?', 'wp-gpt-rag-chat')); ?>')) {
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).text('<?php esc_js(__('Cleaning up...', 'wp-gpt-rag-chat')); ?>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_cleanup_logs',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('<?php esc_js(__('Logs cleaned up successfully.', 'wp-gpt-rag-chat')); ?>');
                location.reload();
            } else {
                alert('<?php esc_js(__('Error cleaning up logs.', 'wp-gpt-rag-chat')); ?>');
            }
        }).fail(function() {
            alert('<?php esc_js(__('Error cleaning up logs.', 'wp-gpt-rag-chat')); ?>');
        }).always(function() {
            $button.prop('disabled', false).text('<?php esc_js(__('Cleanup Old Logs', 'wp-gpt-rag-chat')); ?>');
        });
    });
});
</script>

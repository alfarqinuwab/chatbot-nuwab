<?php
/**
 * Analytics Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

use WP_GPT_RAG_Chat\Analytics;

$analytics = new Analytics();

// Handle actions
if (isset($_POST['action'])) {
    check_admin_referer('wp_gpt_rag_chat_analytics');
    
    switch ($_POST['action']) {
        case 'export_csv':
            $analytics->export_to_csv($_GET);
            break;
            
        case 'cleanup_logs':
            $deleted = $analytics->cleanup_old_logs();
            echo '<div class="notice notice-success"><p>' . sprintf(__('Deleted %d old log entries.', 'wp-gpt-rag-chat'), $deleted) . '</p></div>';
            break;
    }
}

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';
$tags = $_GET['tags'] ?? '';
$model = $_GET['model'] ?? '';
$rating = isset($_GET['rating']) ? (int) $_GET['rating'] : null;

// Pagination
$per_page = 50;
$paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
$offset = ($paged - 1) * $per_page;

// Build filter args
$filter_args = array_filter([
    'date_from' => $date_from,
    'date_to' => $date_to,
    'role' => $role,
    'search' => $search,
    'tags' => $tags,
    'model' => $model,
    'rating' => $rating,
    'limit' => $per_page,
    'offset' => $offset
]);

// Get logs
$logs = $analytics->get_logs($filter_args);
$total_logs = $analytics->get_logs_count($filter_args);
$total_pages = ceil($total_logs / $per_page);

?>

<div class="wrap cornuwab-admin-wrap cornuwab-wp-gpt-rag-analytics">
    <h1><?php _e('Chat Analytics & Logs', 'wp-gpt-rag-chat'); ?></h1>
    
    <div class="nav-tab-wrapper">
        <a href="?page=wp-gpt-rag-chat-analytics&tab=logs" class="nav-tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'logs') ? 'nav-tab-active' : ''; ?>">
            <?php _e('Logs', 'wp-gpt-rag-chat'); ?>
        </a>
        <a href="?page=wp-gpt-rag-chat-analytics&tab=dashboard" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'dashboard') ? 'nav-tab-active' : ''; ?>">
            <?php _e('Dashboard', 'wp-gpt-rag-chat'); ?>
        </a>
                    </div>
    
    <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'logs'): ?>
        <!-- Logs Tab -->
        <div class="analytics-section">
            <h2><?php _e('Filter Logs', 'wp-gpt-rag-chat'); ?></h2>
            
            <form method="get" class="analytics-filters">
                <input type="hidden" name="page" value="wp-gpt-rag-chat-analytics">
                <input type="hidden" name="tab" value="logs">
                
                <div class="filter-row">
                    <label>
                        <?php _e('Date From:', 'wp-gpt-rag-chat'); ?>
                        <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                    </label>
                    
                    <label>
                        <?php _e('Date To:', 'wp-gpt-rag-chat'); ?>
                        <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                    </label>
                    
                    <label>
                        <?php _e('Role:', 'wp-gpt-rag-chat'); ?>
                        <select name="role">
                            <option value=""><?php _e('All Roles', 'wp-gpt-rag-chat'); ?></option>
                            <option value="user" <?php selected($role, 'user'); ?>><?php _e('User', 'wp-gpt-rag-chat'); ?></option>
                            <option value="assistant" <?php selected($role, 'assistant'); ?>><?php _e('Assistant', 'wp-gpt-rag-chat'); ?></option>
                        </select>
                    </label>
                    
                    <label>
                        <?php _e('Rating:', 'wp-gpt-rag-chat'); ?>
                        <select name="rating">
                            <option value=""><?php _e('All Ratings', 'wp-gpt-rag-chat'); ?></option>
                            <option value="1" <?php selected($rating, 1); ?>><?php _e('👍 Positive', 'wp-gpt-rag-chat'); ?></option>
                            <option value="-1" <?php selected($rating, -1); ?>><?php _e('👎 Negative', 'wp-gpt-rag-chat'); ?></option>
                        </select>
                    </label>
                    </div>
                
                <div class="filter-row">
                    <label>
                        <?php _e('Search Content:', 'wp-gpt-rag-chat'); ?>
                        <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search in messages...', 'wp-gpt-rag-chat'); ?>">
                    </label>
                    
                    <label>
                        <?php _e('Filter by Tags:', 'wp-gpt-rag-chat'); ?>
                        <input type="text" name="tags" value="<?php echo esc_attr($tags); ?>" placeholder="<?php _e('e.g., good_answer', 'wp-gpt-rag-chat'); ?>">
                    </label>
                    
                    <label>
                        <?php _e('AI Model:', 'wp-gpt-rag-chat'); ?>
                        <input type="text" name="model" value="<?php echo esc_attr($model); ?>" placeholder="<?php _e('e.g., gpt-4', 'wp-gpt-rag-chat'); ?>">
                    </label>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-filter" style="vertical-align: middle; margin-top: 4px;"></span>
                        <?php _e('Apply Filters', 'wp-gpt-rag-chat'); ?>
                    </button>
                    <a href="?page=wp-gpt-rag-chat-analytics&tab=logs" class="button">
                        <span class="dashicons dashicons-dismiss" style="vertical-align: middle; margin-top: 4px;"></span>
                        <?php _e('Clear Filters', 'wp-gpt-rag-chat'); ?>
                    </a>
                    </div>
            </form>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="post" style="display: inline-block;">
                        <?php wp_nonce_field('wp_gpt_rag_chat_analytics'); ?>
                        <input type="hidden" name="action" value="export_csv">
                        <button type="submit" class="button">
                            <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: 4px;"></span>
                            <?php _e('Export to CSV', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </form>
                    
                    <form method="post" style="display: inline-block;" onsubmit="return confirm('<?php _e('Are you sure you want to delete old logs?', 'wp-gpt-rag-chat'); ?>');">
                        <?php wp_nonce_field('wp_gpt_rag_chat_analytics'); ?>
                        <input type="hidden" name="action" value="cleanup_logs">
                        <button type="submit" class="button">
                            <span class="dashicons dashicons-trash" style="vertical-align: middle; margin-top: 4px;"></span>
                            <?php _e('Cleanup Old Logs', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </form>
                </div>
                
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php printf(_n('%s item', '%s items', $total_logs, 'wp-gpt-rag-chat'), number_format_i18n($total_logs)); ?></span>
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;', 'wp-gpt-rag-chat'),
                        'next_text' => __('&raquo;', 'wp-gpt-rag-chat'),
                        'total' => $total_pages,
                        'current' => $paged,
                        'type' => 'plain'
                    ]);
                    ?>
                    </div>
                </div>
                
            <div class="table-wrapper">
                <table class="wp-list-table widefat fixed striped analytics-logs-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><?php _e('ID', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 110px;"><?php _e('Time', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 70px;"><?php _e('User', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 70px;"><?php _e('Role', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 35%; min-width: 200px;"><?php _e('Content (120 chars)', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 75px;"><?php _e('Latency', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 60px;"><?php _e('Sources', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 60px;"><?php _e('Rating', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 130px;"><?php _e('Tags', 'wp-gpt-rag-chat'); ?></th>
                        <th style="width: 180px;"><?php _e('Actions', 'wp-gpt-rag-chat'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="10"><?php _e('No logs found.', 'wp-gpt-rag-chat'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log->id); ?></td>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($log->created_at))); ?></td>
                                <td>
                                    <?php 
                                    if ($log->user_id) {
                                        $user = get_userdata($log->user_id);
                                        echo esc_html($user ? $user->display_name : 'Unknown');
                                    } else {
                                        echo '<em>' . __('Guest', 'wp-gpt-rag-chat') . '</em>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo esc_attr($log->role); ?>">
                                        <?php echo esc_html(ucfirst($log->role)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    // Handle both old (query/response) and new (content) schema
                                    $content = '';
                                    if (isset($log->content) && !empty($log->content)) {
                                        $content = $log->content;
                                    } elseif (isset($log->query) && !empty($log->query)) {
                                        $content = $log->query;
                                    } elseif (isset($log->response) && !empty($log->response)) {
                                        $content = $log->response;
                                    }
                                    ?>
                                    <div class="content-preview" title="<?php echo esc_attr($content); ?>">
                                        <?php echo esc_html(mb_substr($content, 0, 120)); ?>
                                        <?php if (mb_strlen($content) > 120): ?>...<?php endif; ?>
                    </div>
                                </td>
                                <td>
                                    <?php 
                                    if ($log->response_latency) {
                                        echo esc_html($log->response_latency) . ' ms';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html($log->sources_count ?: '-'); ?></td>
                                <td>
                                    <?php
                                    if ($log->rating === '1') {
                                        echo '👍';
                                    } elseif ($log->rating === '-1') {
                                        echo '👎';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="tags-cell" data-log-id="<?php echo esc_attr($log->id); ?>">
                                        <?php if ($log->tags): ?>
                                            <?php foreach (explode(',', $log->tags) as $tag): ?>
                                                <span class="tag-badge"><?php echo esc_html(trim($tag)); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <button class="button-link add-tag-btn" data-log-id="<?php echo esc_attr($log->id); ?>">+</button>
                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-conversation&chat_id=' . urlencode($log->chat_id)); ?>" class="button button-small">
                                        <?php _e('View Chat', 'wp-gpt-rag-chat'); ?>
                                    </a>
                                    <?php if ($log->role === 'assistant'): ?>
                                    <button class="button button-small link-source-btn" data-log-id="<?php echo esc_attr($log->id); ?>" title="<?php _e('Link Correct Source', 'wp-gpt-rag-chat'); ?>">
                                        📎 <?php _e('Link', 'wp-gpt-rag-chat'); ?>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div><!-- .table-wrapper -->
        </div><!-- .analytics-section -->
        
    <?php elseif ($_GET['tab'] === 'dashboard'): ?>
        <!-- Dashboard Tab -->
        <?php
        $kpis = $analytics->get_kpis(30);
        ?>
        
        <div class="analytics-dashboard">
            <h2><?php _e('Dashboard (Last 30 Days)', 'wp-gpt-rag-chat'); ?></h2>
            
            <div class="kpi-cards">
                <div class="kpi-card">
                    <h3><?php _e('Avg Turns/Conversation', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="kpi-value"><?php echo esc_html($kpis['avg_turns_per_conversation']); ?></div>
                </div>
                
                <div class="kpi-card">
                    <h3><?php _e('Avg Latency', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="kpi-value"><?php echo esc_html($kpis['avg_latency_ms']); ?> ms</div>
                    </div>
                
                <div class="kpi-card">
                    <h3><?php _e('Satisfaction Rate', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="kpi-value"><?php echo esc_html($kpis['satisfaction_rate']); ?>%</div>
                    <div class="kpi-detail">
                        👍 <?php echo esc_html($kpis['thumbs_up']); ?> / 
                        👎 <?php echo esc_html($kpis['thumbs_down']); ?>
                    </div>
                </div>
                
                <div class="kpi-card">
                    <h3><?php _e('Total Rated', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="kpi-value"><?php echo esc_html($kpis['total_rated']); ?></div>
            </div>
        </div>
        
            <div class="dashboard-section">
                <h3><?php _e('Conversations Per Day', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="chart-container">
                    <canvas id="conversations-chart"></canvas>
                    </div>
                </div>
                
            <div class="dashboard-section">
                <h3><?php _e('Token Usage by Model', 'wp-gpt-rag-chat'); ?></h3>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Model', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php _e('Total Tokens', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php _e('API Calls', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php _e('Avg Tokens/Call', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kpis['token_usage'] as $usage): ?>
                            <tr>
                                <td><?php echo esc_html($usage->model_used ?: 'Unknown'); ?></td>
                                <td><?php echo esc_html(number_format($usage->total_tokens)); ?></td>
                                <td><?php echo esc_html(number_format($usage->calls)); ?></td>
                                <td><?php echo esc_html(number_format($usage->total_tokens / $usage->calls, 0)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                    </div>
        
            <div class="dashboard-section">
                <h3><?php _e('Top User Queries', 'wp-gpt-rag-chat'); ?></h3>
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Query', 'wp-gpt-rag-chat'); ?></th>
                            <th style="width: 100px;"><?php _e('Frequency', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kpis['top_queries'] as $query): ?>
                        <tr>
                                <td><?php echo esc_html(mb_substr($query->content, 0, 120)); ?></td>
                                <td><?php echo esc_html($query->frequency); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>
        
            <!-- Content Gaps Section -->
            <div class="dashboard-section">
                <h3>
                    <?php _e('Content Gaps (Unanswered Questions)', 'wp-gpt-rag-chat'); ?>
                    <span class="dashicons dashicons-info-outline" title="<?php esc_attr_e('Questions where the chatbot could not find good answers. Create content for these topics to improve accuracy.', 'wp-gpt-rag-chat'); ?>"></span>
                </h3>
                <?php
                $rag_improvements = new WP_GPT_RAG_Chat\RAG_Improvements();
                $content_gaps = $rag_improvements->get_content_gaps(20, 'open');
                ?>
                
                <?php if (empty($content_gaps)): ?>
                    <p class="content-gaps-empty">
                        ✅ <?php _e('Great! No content gaps detected. Your knowledge base is covering user questions well.', 'wp-gpt-rag-chat'); ?>
                    </p>
                <?php else: ?>
                    <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                                <th><?php _e('Question / Query', 'wp-gpt-rag-chat'); ?></th>
                                <th style="width: 80px;"><?php _e('Frequency', 'wp-gpt-rag-chat'); ?></th>
                                <th style="width: 120px;"><?php _e('Reason', 'wp-gpt-rag-chat'); ?></th>
                                <th style="width: 120px;"><?php _e('Last Seen', 'wp-gpt-rag-chat'); ?></th>
                                <th style="width: 100px;"><?php _e('Actions', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                        <tbody>
                            <?php foreach ($content_gaps as $gap): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($gap->query); ?></strong>
                                    </td>
                                    <td>
                                        <span class="gap-frequency-badge"><?php echo esc_html($gap->frequency); ?>x</span>
                                    </td>
                                    <td>
                                        <?php
                                        $reason_labels = [
                                            'no_sources_found' => __('No sources', 'wp-gpt-rag-chat'),
                                            'low_similarity' => __('Low match', 'wp-gpt-rag-chat'),
                                            'no_answer_response' => __('No answer', 'wp-gpt-rag-chat')
                                        ];
                                        $reason_class = [
                                            'no_sources_found' => 'gap-reason-critical',
                                            'low_similarity' => 'gap-reason-warning',
                                            'no_answer_response' => 'gap-reason-info'
                                        ];
                                        $reason = $gap->gap_reason;
                                        ?>
                                        <span class="gap-reason-badge <?php echo esc_attr($reason_class[$reason] ?? ''); ?>">
                                            <?php echo esc_html($reason_labels[$reason] ?? $reason); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo esc_html(date('Y-m-d H:i', strtotime($gap->last_seen))); ?>
                                    </td>
                                    <td>
                                        <button class="button button-small resolve-gap-btn" data-gap-id="<?php echo esc_attr($gap->id); ?>">
                                            <?php _e('Resolve', 'wp-gpt-rag-chat'); ?>
                                        </button>
                                    </td>
                        </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
                    
                    <p class="content-gaps-note">
                        <span class="dashicons dashicons-lightbulb"></span>
                        <strong><?php _e('Tip:', 'wp-gpt-rag-chat'); ?></strong>
                        <?php _e('Create pages or posts answering these questions, index them, then click "Resolve" to mark them as handled.', 'wp-gpt-rag-chat'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
        jQuery(document).ready(function($) {
            // Conversations Per Day Chart
            const ctx = document.getElementById('conversations-chart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_reverse(array_column($kpis['conversations_per_day'], 'date'))); ?>,
                    datasets: [{
                        label: '<?php _e('Conversations', 'wp-gpt-rag-chat'); ?>',
                        data: <?php echo json_encode(array_reverse(array_column($kpis['conversations_per_day'], 'conversations'))); ?>,
                        borderColor: '#d1a85f',
                        backgroundColor: 'rgba(209, 168, 95, 0.1)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
        </script>
    <?php endif; ?>
    
    <!-- Source Link Modal -->
    <div id="source-modal" class="tag-modal" style="display: none;">
        <div class="tag-modal-overlay"></div>
        <div class="tag-modal-content source-modal-large">
            <div class="tag-modal-header">
                <h2><?php _e('Link Correct Source', 'wp-gpt-rag-chat'); ?></h2>
                <button type="button" class="tag-modal-close source-modal-close">&times;</button>
            </div>
            <div class="tag-modal-body">
                <p class="tag-modal-description">
                    <?php _e('Search for the correct page or PDF that should answer this question. Type to search in real-time.', 'wp-gpt-rag-chat'); ?>
                </p>
                
                <div class="source-search-box">
                    <input type="text" id="source-search-input" class="custom-tag-input" placeholder="<?php _e('Type to search pages, posts, or PDFs...', 'wp-gpt-rag-chat'); ?>" autocomplete="off">
                </div>
                
                <div id="source-search-results" class="source-search-results">
                    <p class="source-search-empty"><?php _e('Start typing to search for content...', 'wp-gpt-rag-chat'); ?></p>
                </div>
            </div>
            <div class="tag-modal-footer">
                <div class="source-modal-footer-left">
                    <label class="reindex-checkbox">
                        <input type="checkbox" id="reindex-checkbox">
                        <?php _e('Re-index content after linking', 'wp-gpt-rag-chat'); ?>
                    </label>
                    <span class="reindex-note"><?php _e('(Only for items not already indexed)', 'wp-gpt-rag-chat'); ?></span>
                </div>
                <div class="source-modal-footer-right">
                    <button type="button" class="button button-secondary source-modal-cancel"><?php _e('Cancel', 'wp-gpt-rag-chat'); ?></button>
                    <button type="button" class="button button-primary" id="link-selected-btn" disabled><?php _e('Link Selected', 'wp-gpt-rag-chat'); ?></button>
                </div>
        </div>
    </div>
    </div>
    
    <!-- Tag Modal -->
    <div id="tag-modal" class="tag-modal" style="display: none;">
        <div class="tag-modal-overlay"></div>
        <div class="tag-modal-content">
            <div class="tag-modal-header">
                <h2><?php _e('Add Tags', 'wp-gpt-rag-chat'); ?></h2>
                <button type="button" class="tag-modal-close">&times;</button>
            </div>
            <div class="tag-modal-body">
                <p class="tag-modal-description"><?php _e('Select tags to categorize this conversation:', 'wp-gpt-rag-chat'); ?></p>
                
                <div class="tag-categories">
                    <div class="tag-category">
                        <h4><?php _e('Quality Assessment', 'wp-gpt-rag-chat'); ?></h4>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="good_answer">
                            <span class="tag-label">
                                <span class="tag-icon">✅</span>
                                <?php _e('Good Answer', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="excellent">
                            <span class="tag-label">
                                <span class="tag-icon">⭐</span>
                                <?php _e('Excellent', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="needs_improvement">
                            <span class="tag-label">
                                <span class="tag-icon">⚠️</span>
                                <?php _e('Needs Improvement', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                    </div>
                    
                    <div class="tag-category">
                        <h4><?php _e('Issues', 'wp-gpt-rag-chat'); ?></h4>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="hallucination">
                            <span class="tag-label">
                                <span class="tag-icon">🔴</span>
                                <?php _e('Hallucination', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="off_topic">
                            <span class="tag-label">
                                <span class="tag-icon">❌</span>
                                <?php _e('Off Topic', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="incomplete">
                            <span class="tag-label">
                                <span class="tag-icon">📝</span>
                                <?php _e('Incomplete', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="incorrect">
                            <span class="tag-label">
                                <span class="tag-icon">🚫</span>
                                <?php _e('Incorrect', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                    </div>
                    
                    <div class="tag-category">
                        <h4><?php _e('Action Required', 'wp-gpt-rag-chat'); ?></h4>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="needs_doc_update">
                            <span class="tag-label">
                                <span class="tag-icon">📚</span>
                                <?php _e('Needs Doc Update', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="escalated">
                            <span class="tag-label">
                                <span class="tag-icon">🔼</span>
                                <?php _e('Escalated', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="training_data">
                            <span class="tag-label">
                                <span class="tag-icon">🎓</span>
                                <?php _e('Training Data', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                        <label class="tag-checkbox">
                            <input type="checkbox" name="tag" value="review_needed">
                            <span class="tag-label">
                                <span class="tag-icon">👀</span>
                                <?php _e('Review Needed', 'wp-gpt-rag-chat'); ?>
                            </span>
                        </label>
                    </div>
                    
                    <div class="tag-category">
                        <h4><?php _e('Custom Tag', 'wp-gpt-rag-chat'); ?></h4>
                        <input type="text" id="custom-tag-input" class="custom-tag-input" placeholder="<?php _e('Enter custom tag...', 'wp-gpt-rag-chat'); ?>">
                    </div>
                </div>
            </div>
            <div class="tag-modal-footer">
                <button type="button" class="button button-secondary tag-modal-cancel"><?php _e('Cancel', 'wp-gpt-rag-chat'); ?></button>
                <button type="button" class="button button-primary tag-modal-save"><?php _e('Add Tags', 'wp-gpt-rag-chat'); ?></button>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var currentLogId = null;
        var currentSourceLogId = null;
        
        // Open modal when + button clicked
        $('.add-tag-btn').on('click', function(e) {
            e.preventDefault();
            currentLogId = $(this).data('log-id');
            $('#tag-modal').fadeIn(200);
            $('body').addClass('modal-open');
        });
        
        // Close modal
        function closeModal() {
            $('#tag-modal').fadeOut(200);
            $('body').removeClass('modal-open');
            $('#tag-modal input[type="checkbox"]').prop('checked', false);
            $('#custom-tag-input').val('');
            currentLogId = null;
        }
        
        $('.tag-modal-close, .tag-modal-cancel, .tag-modal-overlay').on('click', function(e) {
            e.preventDefault();
            closeModal();
        });
        
        // Close on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#tag-modal').is(':visible')) {
                closeModal();
            }
        });
        
        // Save tags
        $('.tag-modal-save').on('click', function(e) {
            e.preventDefault();
            
            if (!currentLogId) return;
            
            // Collect selected tags
            var tags = [];
            $('#tag-modal input[type="checkbox"]:checked').each(function() {
                tags.push($(this).val());
            });
            
            // Add custom tag if provided
            var customTag = $('#custom-tag-input').val().trim();
            if (customTag) {
                tags.push(customTag);
            }
            
            if (tags.length === 0) {
                alert('<?php _e('Please select at least one tag or enter a custom tag.', 'wp-gpt-rag-chat'); ?>');
                return;
            }
            
            // Disable save button
            var $saveBtn = $(this);
            $saveBtn.prop('disabled', true).text('<?php _e('Saving...', 'wp-gpt-rag-chat'); ?>');
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_gpt_rag_chat_add_tags',
                    log_id: currentLogId,
                    tags: tags.join(','),
                    nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        closeModal();
                        location.reload();
                    } else {
                        alert('<?php _e('Error:', 'wp-gpt-rag-chat'); ?> ' + (response.data.message || '<?php _e('Failed to add tags', 'wp-gpt-rag-chat'); ?>'));
                        $saveBtn.prop('disabled', false).text('<?php _e('Add Tags', 'wp-gpt-rag-chat'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Error: Failed to add tags', 'wp-gpt-rag-chat'); ?>');
                    $saveBtn.prop('disabled', false).text('<?php _e('Add Tags', 'wp-gpt-rag-chat'); ?>');
                }
            });
        });
        
        // ===== Source Linking Modal =====
        
        var searchTimeout = null;
        
        // Open source modal
        $('.link-source-btn').on('click', function(e) {
            e.preventDefault();
            currentSourceLogId = $(this).data('log-id');
            $('#source-modal').fadeIn(200);
            $('#source-search-input').val('').focus();
            $('body').addClass('modal-open');
        });
        
        // Close source modal
        function closeSourceModal() {
            $('#source-modal').fadeOut(200);
            $('body').removeClass('modal-open');
            $('#source-search-input').val('');
            $('#source-search-results').html('<p class="source-search-empty"><?php _e('Start typing to search for content...', 'wp-gpt-rag-chat'); ?></p>');
            $('#link-selected-btn').prop('disabled', true);
            currentSourceLogId = null;
        }
        
        $('.source-modal-close, .source-modal-cancel').on('click', function(e) {
            e.preventDefault();
            closeSourceModal();
        });
        
        // Real-time search with debouncing
        $('#source-search-input').on('input', function() {
            var query = $(this).val().trim();
            
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // If empty, show default message
            if (!query) {
                $('#source-search-results').html('<p class="source-search-empty"><?php _e('Start typing to search for content...', 'wp-gpt-rag-chat'); ?></p>');
                $('#link-selected-btn').prop('disabled', true);
                return;
            }
            
            // Show searching indicator
            $('#source-search-results').html('<p class="source-search-loading">⏳ <?php _e('Searching...', 'wp-gpt-rag-chat'); ?></p>');
            
            // Debounce search (wait 500ms after user stops typing)
            searchTimeout = setTimeout(function() {
                searchSources(query);
            }, 500);
        });
        
        // Search sources function
        function searchSources(query) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_gpt_rag_chat_search_content',
                    search: query,
                    post_type: 'any',
                    nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.results.length > 0) {
                        var html = '<div class="source-results-list">';
                        
                        $.each(response.data.results, function(i, result) {
                            var icon = result.type === 'pdf' ? '📄' : (result.type === 'page' ? '📝' : '📰');
                            var indexBadge = result.is_indexed ? '<span class="indexed-badge">✓ Indexed</span>' : '<span class="not-indexed-badge">Not Indexed</span>';
                            
                            html += '<label class="source-result-item">';
                            html += '<input type="checkbox" class="source-checkbox" data-source-id="' + result.id + '" data-source-type="' + (result.type === 'pdf' ? 'attachment' : 'post') + '" data-is-indexed="' + (result.is_indexed ? '1' : '0') + '">';
                            html += '<div class="source-result-content-wrapper">';
                            html += '<div class="source-result-icon">' + icon + '</div>';
                            html += '<div class="source-result-content">';
                            html += '<h4 class="source-result-title">' + result.title + '</h4>';
                            html += '<p class="source-result-excerpt">' + result.excerpt + '</p>';
                            html += '<div class="source-result-meta">';
                            html += '<span class="source-result-type">' + result.type.toUpperCase() + '</span>';
                            html += indexBadge;
                            html += '<a href="' + result.url + '" target="_blank" class="source-result-link" onclick="event.stopPropagation()">View →</a>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                            html += '</label>';
                        });
                        
                        html += '</div>';
                        $('#source-search-results').html(html);
                        updateLinkButtonState();
                    } else {
                        $('#source-search-results').html('<p class="source-search-empty">❌ <?php _e('No content found. Try different keywords.', 'wp-gpt-rag-chat'); ?></p>');
                        $('#link-selected-btn').prop('disabled', true);
                    }
                },
                error: function() {
                    $('#source-search-results').html('<p class="source-search-empty">❌ <?php _e('Search failed. Please try again.', 'wp-gpt-rag-chat'); ?></p>');
                    $('#link-selected-btn').prop('disabled', true);
                }
            });
        }
        
        // Update Link Selected button state
        function updateLinkButtonState() {
            var checkedCount = $('.source-checkbox:checked').length;
            $('#link-selected-btn').prop('disabled', checkedCount === 0);
            
            if (checkedCount > 0) {
                $('#link-selected-btn').text('<?php _e('Link Selected', 'wp-gpt-rag-chat'); ?> (' + checkedCount + ')');
            } else {
                $('#link-selected-btn').text('<?php _e('Link Selected', 'wp-gpt-rag-chat'); ?>');
            }
        }
        
        // Handle checkbox changes
        $(document).on('change', '.source-checkbox', function() {
            updateLinkButtonState();
        });
        
        // Link selected sources
        $('#link-selected-btn').on('click', function() {
            if (!currentSourceLogId) return;
            
            var $btn = $(this);
            var $checkedBoxes = $('.source-checkbox:checked');
            
            if ($checkedBoxes.length === 0) return;
            
            var sources = [];
            $checkedBoxes.each(function() {
                sources.push({
                    id: $(this).data('source-id'),
                    type: $(this).data('source-type'),
                    is_indexed: $(this).data('is-indexed') == '1'
                });
            });
            
            var reindex = $('#reindex-checkbox').is(':checked');
            
            $btn.prop('disabled', true).text('<?php _e('Linking...', 'wp-gpt-rag-chat'); ?>');
            
            // Link each source
            var promises = [];
            $.each(sources, function(i, source) {
                var shouldReindex = reindex && !source.is_indexed;
                
                promises.push(
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wp_gpt_rag_chat_link_source',
                            log_id: currentSourceLogId,
                            source_id: source.id,
                            source_type: source.type,
                            reindex: shouldReindex,
                            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
                        }
                    })
                );
            });
            
            // Wait for all requests to complete
            $.when.apply($, promises).then(
                function() {
                    // Show success message
                    var message = '✅ ' + sources.length + ' <?php _e('source(s) linked successfully!', 'wp-gpt-rag-chat'); ?>';
                    alert(message);
                    closeSourceModal();
                    location.reload();
                },
                function() {
                    alert('<?php _e('Error: Some sources failed to link. Please try again.', 'wp-gpt-rag-chat'); ?>');
                    $btn.prop('disabled', false);
                    updateLinkButtonState();
                }
            );
        });
        
        // ===== Content Gap Resolution =====
        
        $('.resolve-gap-btn').on('click', function() {
            var $btn = $(this);
            var gapId = $btn.data('gap-id');
            
            if (!confirm('<?php _e('Have you created content for this question? Click OK to mark as resolved.', 'wp-gpt-rag-chat'); ?>')) {
                return;
            }
            
            $btn.prop('disabled', true).text('<?php _e('Resolving...', 'wp-gpt-rag-chat'); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_gpt_rag_chat_resolve_gap',
                    gap_id: gapId,
                    nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                            // Check if table is empty
                            if ($('.content-gaps-table tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert('<?php _e('Error:', 'wp-gpt-rag-chat'); ?> ' + (response.data.message || '<?php _e('Failed to resolve content gap', 'wp-gpt-rag-chat'); ?>'));
                        $btn.prop('disabled', false).text('<?php _e('Resolve', 'wp-gpt-rag-chat'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Error: Failed to resolve content gap', 'wp-gpt-rag-chat'); ?>');
                    $btn.prop('disabled', false).text('<?php _e('Resolve', 'wp-gpt-rag-chat'); ?>');
                }
            });
        });
    });
    </script>
</div>

<style>
/* Main Analytics Page Wrapper */
.wp-gpt-rag-analytics {
    margin: 20px 20px 20px 0;
}

.wp-gpt-rag-analytics h1 {
    margin-bottom: 20px;
    color: #1d2327;
    font-size: 23px;
    font-weight: 400;
    padding: 0;
}

/* Navigation Tabs */
.wp-gpt-rag-analytics .nav-tab-wrapper {
    border-bottom: 1px solid #c3c4c7;
    margin: 0 0 20px;
    padding: 0;
}

.wp-gpt-rag-analytics .nav-tab {
    border: 1px solid transparent;
    border-bottom: none;
    background: transparent;
    color: #50575e;
    padding: 8px 16px;
    font-size: 14px;
    line-height: 1.71428571;
    margin: 0 4px -1px 0;
    text-decoration: none;
    display: inline-block;
}

.wp-gpt-rag-analytics .nav-tab:hover {
    background-color: #f6f7f7;
    color: #1d2327;
}

.wp-gpt-rag-analytics .nav-tab-active {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-bottom-color: #fff;
    color: #1d2327;
    font-weight: 600;
}

/* Analytics Section */
.analytics-section {
    background: #fff;
    padding: 0;
    margin-top: 20px;
}

.analytics-section h2 {
    background: #f8f8f8;
    border-bottom: 1px solid #ccd0d4;
    padding: 15px 20px;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
}

/* Filters Section */
.analytics-filters {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-top: none;
    margin: 0;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.filter-row label {
    display: flex;
    flex-direction: column;
    gap: 5px;
    font-weight: 600;
    font-size: 13px;
    color: #1d2327;
}

.filter-row input,
.filter-row select {
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
}

.filter-row input:focus,
.filter-row select:focus {
    border-color: #d1a85f;
    outline: 2px solid rgba(209, 168, 95, 0.3);
}

/* Filter Actions */
.filter-actions {
    display: flex;
    gap: 10px;
    padding-top: 10px;
    border-top: 1px solid #dcdcde;
    margin-top: 15px;
}

/* Table Container */
.wp-gpt-rag-analytics .wp-list-table {
    width: 100% !important;
    border: 1px solid #c3c4c7;
    margin-top: 0;
    background: #fff;
}

.wp-gpt-rag-analytics .widefat {
    border-spacing: 0;
    width: 100%;
    clear: both;
}

.wp-gpt-rag-analytics .wp-list-table thead th,
.wp-gpt-rag-analytics .wp-list-table thead td {
    background: #f6f7f7;
    border-bottom: 1px solid #c3c4c7;
    font-weight: 600;
    padding: 10px;
}

.wp-gpt-rag-analytics .wp-list-table tbody td {
    padding: 10px;
    border-top: 1px solid #c3c4c7;
    vertical-align: middle;
}

.role-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}

.role-badge.role-user {
    background: #d1a85f;
    color: #fff;
}

.role-badge.role-assistant {
    background: #e0e0e0;
    color: #333;
}

.content-preview {
    line-height: 1.4;
    font-size: 13px;
}

.tags-cell {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    align-items: center;
}

.tag-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #f0f0f0;
    border-radius: 3px;
    font-size: 11px;
}

.add-tag-btn {
    color: #d1a85f;
    font-weight: bold;
    font-size: 16px;
}

/* Table Navigation */
.wp-gpt-rag-analytics .tablenav {
    background: #f6f7f7;
    border: 1px solid #c3c4c7;
    border-bottom: none;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 20px;
}

.wp-gpt-rag-analytics .tablenav .actions {
    display: flex;
    gap: 8px;
}

.wp-gpt-rag-analytics .tablenav .button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 32px;
}

.wp-gpt-rag-analytics .tablenav-pages {
    display: flex;
    align-items: center;
    gap: 10px;
}

.wp-gpt-rag-analytics .displaying-num {
    font-size: 13px;
    color: #646970;
    font-weight: 500;
}

.wp-gpt-rag-analytics .pagination-links {
    display: flex;
    gap: 4px;
    align-items: center;
}

.wp-gpt-rag-analytics .pagination-links a,
.wp-gpt-rag-analytics .pagination-links span {
    padding: 4px 8px;
    border: 1px solid #dcdcde;
    background: #fff;
    text-decoration: none;
    color: #2c3338;
    font-size: 13px;
    min-width: 32px;
    text-align: center;
}

.wp-gpt-rag-analytics .pagination-links .current {
    background: #d1a85f;
    color: #fff;
    border-color: #d1a85f;
}

.wp-gpt-rag-analytics .pagination-links a:hover {
    background: #f6f7f7;
    border-color: #c3c4c7;
}

/* Table Wrapper */
.wp-gpt-rag-analytics .table-wrapper {
    overflow-x: auto;
    background: #fff;
    border-left: 1px solid #c3c4c7;
    border-right: 1px solid #c3c4c7;
    border-bottom: 1px solid #c3c4c7;
}

/* Analytics Logs Table */
.wp-gpt-rag-analytics .analytics-logs-table {
    table-layout: fixed;
    min-width: 1200px;
}

/* Table Specific Styles */
.wp-gpt-rag-analytics .striped tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}

.wp-gpt-rag-analytics .striped tbody tr:hover {
    background-color: #f0f6fc;
}

.wp-gpt-rag-analytics .wp-list-table tbody tr td {
    word-break: break-word;
}

.wp-gpt-rag-analytics .wp-list-table th,
.wp-gpt-rag-analytics .wp-list-table td {
    white-space: nowrap;
}

/* Content Preview Column - Allow wrapping */
.wp-gpt-rag-analytics .content-preview {
    white-space: normal !important;
    word-wrap: break-word;
    word-break: break-word;
    line-height: 1.5;
}

/* Actions Column - Always visible */
.wp-gpt-rag-analytics .analytics-logs-table td:last-child,
.wp-gpt-rag-analytics .analytics-logs-table th:last-child {
    width: 180px !important;
    min-width: 180px !important;
    white-space: normal !important;
}

.wp-gpt-rag-analytics .analytics-logs-table td:last-child {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.wp-gpt-rag-analytics .analytics-logs-table .button-small {
    font-size: 12px;
    padding: 4px 10px;
    line-height: 1.5;
    height: auto;
    white-space: nowrap;
}

/* Dashboard Section */
.analytics-dashboard {
    margin-top: 20px;
}

.analytics-dashboard h2 {
    margin-bottom: 20px;
    font-size: 20px;
    color: #1d2327;
}

.kpi-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.kpi-card {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.kpi-card h3 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #666;
    font-weight: 600;
}

.kpi-value {
    font-size: 32px;
    font-weight: 700;
    color: #d1a85f;
}

.kpi-detail {
    margin-top: 8px;
    font-size: 13px;
    color: #666;
}

.dashboard-section {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    margin: 20px 0;
}

.dashboard-section h3 {
    margin: 0 0 20px;
}

.chart-container {
    height: 300px;
    position: relative;
}

/* Tag Modal Styles */
body.modal-open {
    overflow: hidden;
}

.tag-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100000;
}

.tag-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.tag-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 700px;
    width: 90%;
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.tag-modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
}

.tag-modal-header h2 {
    margin: 0;
    font-size: 20px;
    color: #fff;
}

.tag-modal-close {
    background: none;
    border: none;
    font-size: 32px;
    color: #fff;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background 0.2s;
    line-height: 1;
    padding: 0;
}

.tag-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.tag-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

.tag-modal-description {
    margin: 0 0 20px;
    color: #666;
    font-size: 14px;
}

.tag-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
}

.tag-category {
    background: #f9f9f9;
        padding: 16px;
    border-radius: 6px;
    border: 1px solid #e1e5e9;
}

.tag-category h4 {
    margin: 0 0 12px;
    font-size: 13px;
    font-weight: 600;
    color: #d1a85f;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tag-checkbox {
    display: flex;
    align-items: center;
    padding: 8px 0;
    cursor: pointer;
    user-select: none;
}

.tag-checkbox input[type="checkbox"] {
    margin: 0 10px 0 0;
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #d1a85f;
}

.tag-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #333;
}

.tag-icon {
    font-size: 16px;
    width: 20px;
    display: inline-block;
}

.tag-checkbox:hover {
    background: rgba(209, 168, 95, 0.1);
    border-radius: 4px;
    margin: 0 -4px;
    padding: 8px 4px;
}

.custom-tag-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.custom-tag-input:focus {
    border-color: #d1a85f;
    outline: none;
    box-shadow: 0 0 0 3px rgba(209, 168, 95, 0.1);
}

.tag-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e1e5e9;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f9f9f9;
}

.tag-modal-footer .button {
    margin: 0;
}

.tag-modal-save {
    background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
    border-color: #c89a4f;
    color: #fff;
}

.tag-modal-save:hover {
    background: linear-gradient(135deg, #c89a4f 0%, #b88a3f 100%);
    border-color: #b88a3f;
}

@media (max-width: 768px) {
    .tag-modal-content {
        width: 95%;
        max-height: 90vh;
    }
    
    .tag-categories {
        grid-template-columns: 1fr;
    }
}

/* Source Link Modal Styles */
.source-modal-large {
    max-width: 1100px !important;
    width: 95% !important;
    max-height: 95vh !important;
    height: auto !important;
}

.source-modal-large .tag-modal-body {
    padding: 28px 32px;
    max-height: calc(95vh - 160px);
    overflow-y: auto;
}

.source-modal-large .tag-modal-footer {
    padding: 20px 32px;
}

.source-search-box {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
}

.source-search-box #source-search-input {
    flex: 1;
    font-size: 15px;
    padding: 12px 16px;
    border: 2px solid #dcdcde;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.source-search-box #source-search-input:focus {
    border-color: #d1a85f;
    outline: none;
    box-shadow: 0 0 0 3px rgba(209, 168, 95, 0.1);
}

.source-search-results {
    max-height: 700px;
    overflow-y: auto;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    background: #f9f9f9;
    min-height: 250px;
}

.source-search-empty,
.source-search-loading {
    padding: 40px 20px;
    text-align: center;
    color: #666;
    font-style: italic;
}

.source-results-list {
        padding: 16px;
    }

.source-result-item {
    display: flex;
    gap: 14px;
    padding: 18px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    margin-bottom: 14px;
    transition: all 0.2s ease;
    background: #fff;
    cursor: pointer;
    align-items: flex-start;
}

.source-result-item:hover {
    border-color: #d1a85f;
    box-shadow: 0 3px 12px rgba(209, 168, 95, 0.25);
    transform: translateY(-1px);
}

.source-result-item:last-child {
    margin-bottom: 0;
}

.source-result-item input[type="checkbox"] {
    margin-top: 6px;
    width: 20px;
    height: 20px;
    cursor: pointer;
    flex-shrink: 0;
    accent-color: #d1a85f;
}

.source-result-content-wrapper {
    display: flex;
    gap: 16px;
    flex: 1;
    min-width: 0;
}

.source-result-icon {
    font-size: 32px;
    flex-shrink: 0;
}

.source-result-content {
    flex: 1;
    min-width: 0;
}

.source-result-title {
    margin: 0 0 8px;
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.source-result-excerpt {
    margin: 0 0 8px;
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

.source-result-meta {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.source-result-type {
    display: inline-block;
    padding: 2px 8px;
    background: #f0f0f0;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    color: #666;
}

.indexed-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #d4edda;
    color: #155724;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}

.not-indexed-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #fff3cd;
    color: #856404;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}

.source-result-link {
    color: #d1a85f;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
}

.source-result-link:hover {
    text-decoration: underline;
}

.link-this-source-btn {
    flex-shrink: 0;
    align-self: flex-start;
}

.source-modal-footer-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.source-modal-footer-right {
    display: flex;
    gap: 8px;
    align-items: center;
}

.reindex-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #333;
    cursor: pointer;
    user-select: none;
}

.reindex-checkbox input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #d1a85f;
}

.reindex-note {
    font-size: 11px;
    color: #666;
    font-style: italic;
    margin-left: 24px;
}

.link-source-btn {
    margin-top: 4px;
}

/* Content Gaps Styles */
.content-gaps-empty {
    padding: 30px;
    text-align: center;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    color: #0369a1;
    font-size: 14px;
    font-weight: 500;
}

.content-gaps-note {
    margin-top: 15px;
    padding: 15px;
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    font-size: 13px;
    color: #92400e;
}

.content-gaps-note .dashicons {
    color: #f59e0b;
    vertical-align: middle;
    margin-right: 5px;
}

.gap-frequency-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #d1a85f;
    color: #fff;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.gap-reason-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.gap-reason-critical {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.gap-reason-warning {
    background: #fed7aa;
    color: #9a3412;
    border: 1px solid #fdba74;
}

.gap-reason-info {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
}

.resolve-gap-btn {
    font-size: 12px;
    padding: 4px 10px;
    height: auto;
    line-height: 1.5;
}

.dashboard-section h3 .dashicons-info-outline {
    font-size: 18px;
    color: #6b7280;
    cursor: help;
    vertical-align: middle;
    margin-left: 8px;
}

/* Responsive Styles */
@media screen and (max-width: 1280px) {
    .wp-gpt-rag-analytics .filter-row {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}

@media screen and (max-width: 782px) {
    .wp-gpt-rag-analytics {
        margin: 10px 10px 10px 0;
    }
    
    .wp-gpt-rag-analytics h1 {
        font-size: 20px;
        margin-bottom: 15px;
    }
    
    .wp-gpt-rag-analytics .filter-row {
        grid-template-columns: 1fr;
    }
    
    .wp-gpt-rag-analytics .tablenav {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .wp-gpt-rag-analytics .tablenav .actions {
        width: 100%;
        flex-wrap: wrap;
    }
    
    .wp-gpt-rag-analytics .tablenav-pages {
        width: 100%;
        justify-content: center;
    }
    
    .wp-gpt-rag-analytics .wp-list-table {
        font-size: 13px;
    }
    
    .wp-gpt-rag-analytics .wp-list-table th,
    .wp-gpt-rag-analytics .wp-list-table td {
        padding: 8px 6px;
    }
    
    .kpi-cards {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions .button {
        width: 100%;
    }
}

@media screen and (max-width: 600px) {
    .wp-gpt-rag-analytics .content-preview {
        max-width: 150px;
    }
    
    .wp-gpt-rag-analytics .nav-tab {
        font-size: 13px;
        padding: 6px 12px;
    }
    
    .source-modal-large {
        max-width: 100% !important;
        width: 100% !important;
        max-height: 100vh !important;
        height: 100vh !important;
        border-radius: 0 !important;
    }
    
    .source-modal-large .tag-modal-body {
        padding: 20px 16px;
        max-height: calc(100vh - 180px);
    }
    
    .source-search-results {
        max-height: calc(100vh - 340px);
        min-height: 300px;
    }
    
    .source-result-item {
        padding: 14px;
        gap: 10px;
    }
}
</style>

<?php

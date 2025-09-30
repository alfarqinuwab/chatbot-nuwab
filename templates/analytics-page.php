<?php
/**
 * Analytics page template
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
        <span class="dashicons dashicons-chart-bar"></span>
        <?php esc_html_e('Analytics Overview', 'wp-gpt-rag-chat'); ?>
    </h1>
    
    <div class="wp-gpt-rag-chat-analytics">
        <!-- Key Metrics -->
        <div class="analytics-metrics">
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-format-chat"></span>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('Total Queries', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html(number_format($chat_stats['total_queries'] ?? 0)); ?></div>
                        <div class="metric-description"><?php esc_html_e('Chat interactions', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-database"></span>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('Indexed Content', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html(number_format($stats['total_vectors'] ?? 0)); ?></div>
                        <div class="metric-description"><?php esc_html_e('Vector embeddings', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('Active Users', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html(number_format($chat_stats['unique_users'] ?? 0)); ?></div>
                        <div class="metric-description"><?php esc_html_e('Unique users', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-clock"></span>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('Avg Response Time', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html($chat_stats['avg_response_time'] ?? '0'); ?>s</div>
                        <div class="metric-description"><?php esc_html_e('Average response time', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="analytics-charts">
            <div class="charts-grid">
                <div class="chart-card">
                    <h3><?php esc_html_e('Query Volume (Last 30 Days)', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="chart-container">
                        <canvas id="queriesChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3><?php esc_html_e('User Activity', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="chart-container">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Queries -->
        <div class="analytics-section">
            <h2><?php esc_html_e('Top Queries', 'wp-gpt-rag-chat'); ?></h2>
            <div class="top-queries-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Query', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Count', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Last Asked', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="top-queries-list">
                        <tr>
                            <td colspan="3"><?php esc_html_e('Loading top queries...', 'wp-gpt-rag-chat'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="analytics-section">
            <h2><?php esc_html_e('Recent Activity', 'wp-gpt-rag-chat'); ?></h2>
            <div class="recent-activity-list" id="recent-activity">
                <p><?php esc_html_e('Loading recent activity...', 'wp-gpt-rag-chat'); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.wp-gpt-rag-chat-analytics {
    margin-top: 20px;
}

.analytics-metrics {
    margin-bottom: 30px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.metric-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.metric-icon {
    background: #f0f6fc;
    border-radius: 8px;
    padding: 12px;
    color: #0073aa;
}

.metric-icon .dashicons {
    font-size: 24px;
}

.metric-content h3 {
    margin: 0 0 8px 0;
    color: #646970;
    font-size: 14px;
    font-weight: 500;
}

.metric-number {
    font-size: 28px;
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 4px;
}

.metric-description {
    color: #646970;
    font-size: 12px;
}

.analytics-charts {
    margin-bottom: 30px;
}

.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.chart-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.chart-card h3 {
    margin: 0 0 20px 0;
    color: #1d2327;
    font-size: 16px;
}

.chart-container {
    position: relative;
    height: 300px;
}

.analytics-section {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.analytics-section h2 {
    margin: 0 0 20px 0;
    color: #1d2327;
    font-size: 18px;
}

.top-queries-table {
    overflow-x: auto;
}

.recent-activity-list {
    max-height: 400px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .metric-card {
        padding: 16px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize charts
    initQueriesChart();
    initUsersChart();
    loadTopQueries();
    loadRecentActivity();
    
    function initQueriesChart() {
        const ctx = document.getElementById('queriesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
                datasets: [{
                    label: 'Queries',
                    data: [12, 19, 3, 5, 2, 3, 8],
                    borderColor: '#0073aa',
                    backgroundColor: 'rgba(0, 115, 170, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    function initUsersChart() {
        const ctx = document.getElementById('usersChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Logged In', 'Anonymous'],
                datasets: [{
                    data: [65, 35],
                    backgroundColor: ['#0073aa', '#646970']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    function loadTopQueries() {
        // AJAX call to load top queries
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_get_top_queries',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                $('#top-queries-list').html(response.data.html);
            }
        });
    }
    
    function loadRecentActivity() {
        // AJAX call to load recent activity
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_get_recent_activity',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                $('#recent-activity').html(response.data.html);
            }
        });
    }
});
</script>

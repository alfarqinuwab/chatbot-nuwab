<?php
/**
 * User Analytics page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$analytics = new WP_GPT_RAG_Chat\Analytics();
$user_stats = $analytics->get_user_stats(30);
$user_activity = $analytics->get_user_activity(7);
$settings = WP_GPT_RAG_Chat\Settings::get_settings();
?>

<div class="wrap cornuwab-admin-wrap">
    <h1>
        <span class="dashicons dashicons-admin-users"></span>
        <?php esc_html_e('User Analytics', 'wp-gpt-rag-chat'); ?>
    </h1>
    
    <div class="wp-gpt-rag-chat-user-analytics">
        <!-- User Overview -->
        <div class="user-overview">
            <div class="overview-grid">
                <div class="overview-card">
                    <h3><?php esc_html_e('Total Users', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="overview-number"><?php echo esc_html(number_format($user_stats['total_users'])); ?></div>
                </div>
                
                <div class="overview-card">
                    <h3><?php esc_html_e('Logged In Users', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="overview-number"><?php echo esc_html(number_format($user_stats['logged_in_users'])); ?></div>
                </div>
                
                <div class="overview-card">
                    <h3><?php esc_html_e('Anonymous Users', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="overview-number"><?php echo esc_html(number_format($user_stats['anonymous_users'])); ?></div>
                </div>
                
                <div class="overview-card">
                    <h3><?php esc_html_e('Returning Users', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="overview-number"><?php echo esc_html(number_format($user_stats['returning_users'])); ?></div>
                </div>
            </div>
        </div>
        
        <!-- User Activity Chart -->
        <div class="user-charts">
            <div class="chart-card">
                <h3><?php esc_html_e('User Activity Over Time', 'wp-gpt-rag-chat'); ?></h3>
                <div class="chart-container">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- User Sessions -->
        <div class="user-sessions">
            <h2><?php esc_html_e('User Sessions', 'wp-gpt-rag-chat'); ?></h2>
            <div class="sessions-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('User', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Type', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Sessions', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Queries', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Last Activity', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="user-sessions-list">
                        <tr>
                            <td colspan="5"><?php esc_html_e('Loading user sessions...', 'wp-gpt-rag-chat'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- User Engagement -->
        <div class="user-engagement">
            <h2><?php esc_html_e('User Engagement', 'wp-gpt-rag-chat'); ?></h2>
            <div class="engagement-grid">
                <div class="engagement-card">
                    <h4><?php esc_html_e('Average Queries per User', 'wp-gpt-rag-chat'); ?></h4>
                    <div class="engagement-number"><?php echo esc_html($user_stats['avg_queries_per_user']); ?></div>
                </div>
                
                <div class="engagement-card">
                    <h4><?php esc_html_e('Average Session Duration', 'wp-gpt-rag-chat'); ?></h4>
                    <div class="engagement-number"><?php echo esc_html($user_stats['avg_session_duration']); ?></div>
                </div>
                
                <div class="engagement-card">
                    <h4><?php esc_html_e('Most Active Hour', 'wp-gpt-rag-chat'); ?></h4>
                    <div class="engagement-number"><?php echo esc_html($user_stats['most_active_hour']); ?></div>
                </div>
                
                <div class="engagement-card">
                    <h4><?php esc_html_e('User Retention Rate', 'wp-gpt-rag-chat'); ?></h4>
                    <div class="engagement-number"><?php echo esc_html($user_stats['retention_rate']); ?>%</div>
                </div>
            </div>
        </div>
        
        <!-- Geographic Distribution -->
        <div class="geographic-data">
            <h2><?php esc_html_e('Geographic Distribution', 'wp-gpt-rag-chat'); ?></h2>
            <div class="geo-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Country', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Users', 'wp-gpt-rag-chat'); ?></th>
                            <th><?php esc_html_e('Percentage', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="geo-distribution-list">
                        <tr>
                            <td colspan="3"><?php esc_html_e('Loading geographic data...', 'wp-gpt-rag-chat'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.wp-gpt-rag-chat-user-analytics {
    margin-top: 20px;
}

.user-overview {
    margin-bottom: 30px;
}

.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.overview-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.overview-card h3 {
    margin: 0 0 12px 0;
    color: #646970;
    font-size: 14px;
    font-weight: 500;
}

.overview-number {
    font-size: 24px;
    font-weight: 600;
    color: #1d2327;
}

.user-charts {
    margin-bottom: 30px;
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

.user-sessions,
.user-engagement,
.geographic-data {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.user-sessions h2,
.user-engagement h2,
.geographic-data h2 {
    margin: 0 0 20px 0;
    color: #1d2327;
    font-size: 18px;
}

.sessions-table,
.geo-table {
    overflow-x: auto;
}

.engagement-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.engagement-card {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    padding: 16px;
    text-align: center;
}

.engagement-card h4 {
    margin: 0 0 8px 0;
    color: #646970;
    font-size: 13px;
    font-weight: 500;
}

.engagement-number {
    font-size: 20px;
    font-weight: 600;
    color: #1d2327;
}

@media (max-width: 768px) {
    .overview-grid,
    .engagement-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize user activity chart
    initUserActivityChart();
    loadUserSessions();
    loadGeographicData();
    
    function initUserActivityChart() {
        const ctx = document.getElementById('userActivityChart').getContext('2d');
        
        // Real data from PHP
        const activityData = <?php echo json_encode($user_activity); ?>;
        
        const labels = activityData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        });
        const loggedInData = activityData.map(item => parseInt(item.logged_in_users) || 0);
        const anonymousData = activityData.map(item => parseInt(item.anonymous_users) || 0);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '<?php _e('Logged In Users', 'wp-gpt-rag-chat'); ?>',
                    data: loggedInData,
                    backgroundColor: '#0073aa'
                }, {
                    label: '<?php _e('Anonymous Users', 'wp-gpt-rag-chat'); ?>',
                    data: anonymousData,
                    backgroundColor: '#646970'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    
    function loadUserSessions() {
        // AJAX call to load user sessions
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_get_user_sessions',
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#user-sessions-list').html(response.data.html);
            } else {
                $('#user-sessions-list').html('<tr><td colspan="5">Error: ' + (response.data.message || 'Unknown error') + '</td></tr>');
            }
        });
    }
    
    function loadGeographicData() {
        // AJAX call to load geographic data
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_get_geographic_data',
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#geo-distribution-list').html(response.data.html);
            } else {
                $('#geo-distribution-list').html('<tr><td colspan="3">Error: ' + (response.data.message || 'Unknown error') + '</td></tr>');
            }
        });
    }
});
</script>


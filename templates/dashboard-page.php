<?php
/**
 * Main admin page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$stats = WP_GPT_RAG_Chat\Admin::get_indexing_stats();
$chat_stats = WP_GPT_RAG_Chat\Chat::get_chat_stats();
$settings = WP_GPT_RAG_Chat\Settings::get_settings();
$user_role = WP_GPT_RAG_Chat\RBAC::get_user_role_display();
$is_aims_manager = WP_GPT_RAG_Chat\RBAC::is_aims_manager();
$is_log_viewer = WP_GPT_RAG_Chat\RBAC::is_log_viewer();

// Get comprehensive dashboard data
$dashboard_data = WP_GPT_RAG_Chat\Admin::get_dashboard_data();
?>

<div class="wrap cornuwab-admin-wrap">
    <h1><?php esc_html_e('WP GPT RAG Chat Dashboard', 'wp-gpt-rag-chat'); ?></h1>
    
    <!-- Role Information -->
    <div class="role-info">
        <div class="role-badge <?php echo $is_aims_manager ? 'aims-manager' : 'log-viewer'; ?>">
            <span class="role-label"><?php esc_html_e('Your Role:', 'wp-gpt-rag-chat'); ?></span>
            <span class="role-name"><?php echo esc_html($user_role); ?></span>
        </div>
        <div class="role-description">
            <?php if ($is_aims_manager): ?>
                <p><?php esc_html_e('You have full access to all plugin features and settings.', 'wp-gpt-rag-chat'); ?></p>
            <?php elseif ($is_log_viewer): ?>
                <p><?php esc_html_e('You have read-only access to system logs and basic dashboard information.', 'wp-gpt-rag-chat'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="wp-gpt-rag-chat-dashboard">
        <!-- Key Metrics Row -->
        <div class="dashboard-metrics">
            <div class="metrics-grid">
                <div class="metric-card system-status">
                    <div class="metric-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('System Status', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html(number_format($stats['total_vectors'])); ?></div>
                        <div class="metric-description"><?php esc_html_e('Indexed Vectors', 'wp-gpt-rag-chat'); ?></div>
                        <div class="metric-sub"><?php echo esc_html($stats['total_posts']); ?> <?php esc_html_e('Posts', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
                
                <div class="metric-card activity-metrics">
                    <div class="metric-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('Activity Metrics', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html(number_format($dashboard_data['total_queries'])); ?></div>
                        <div class="metric-description"><?php esc_html_e('Total Queries', 'wp-gpt-rag-chat'); ?></div>
                        <div class="metric-sub">+<?php echo esc_html($dashboard_data['queries_today']); ?> <?php esc_html_e('today', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
                
                <div class="metric-card incidents-risk">
                    <div class="metric-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('Incidents & Risk', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html(number_format($dashboard_data['total_incidents'])); ?></div>
                        <div class="metric-description"><?php esc_html_e('Total Incidents', 'wp-gpt-rag-chat'); ?></div>
                        <div class="metric-sub risk-<?php echo $dashboard_data['risk_level']; ?>">
                            <?php echo esc_html(ucfirst($dashboard_data['risk_level'])); ?> <?php esc_html_e('Risk', 'wp-gpt-rag-chat'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card health-status">
                    <div class="metric-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="metric-content">
                        <h3><?php esc_html_e('Health Status', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="metric-number"><?php echo esc_html(number_format($dashboard_data['resolved_incidents'])); ?></div>
                        <div class="metric-description"><?php esc_html_e('Resolved Issues', 'wp-gpt-rag-chat'); ?></div>
                        <div class="metric-sub"><?php echo esc_html($stats['recent_activity']); ?> <?php esc_html_e('recent updates', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!-- Main Content Grid -->
        <div class="dashboard-main-content">
            <div class="content-grid">
                <!-- Quick Actions Column -->
                <div class="content-column">
                    <?php if ($is_aims_manager): ?>
                    <div class="dashboard-section">
                        <h2><?php esc_html_e('Quick Actions', 'wp-gpt-rag-chat'); ?></h2>
                        
                        <div class="actions-grid">
                            <div class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="action-content">
                                    <h3><?php esc_html_e('Settings', 'wp-gpt-rag-chat'); ?></h3>
                                    <p><?php esc_html_e('Configure OpenAI and Pinecone API settings', 'wp-gpt-rag-chat'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-settings')); ?>" class="button button-primary">
                                        <?php esc_html_e('Configure', 'wp-gpt-rag-chat'); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="action-content">
                                    <h3><?php esc_html_e('Indexing', 'wp-gpt-rag-chat'); ?></h3>
                                    <p><?php esc_html_e('Manage content indexing and bulk operations', 'wp-gpt-rag-chat'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-indexing')); ?>" class="button button-secondary">
                                        <?php esc_html_e('Manage', 'wp-gpt-rag-chat'); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="action-content">
                                    <h3><?php esc_html_e('Analytics', 'wp-gpt-rag-chat'); ?></h3>
                                    <p><?php esc_html_e('View chat logs and usage analytics', 'wp-gpt-rag-chat'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-logs')); ?>" class="button button-secondary">
                                        <?php esc_html_e('View', 'wp-gpt-rag-chat'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- System Overview Column -->
                <div class="content-column">
                    <div class="dashboard-section">
                        <h2><?php esc_html_e('System Overview', 'wp-gpt-rag-chat'); ?></h2>
                        
                        <div class="overview-grid">
                            <!-- Configuration Status -->
                            <?php if ($is_aims_manager): ?>
                            <div class="overview-card">
                                <h3><?php esc_html_e('Configuration Status', 'wp-gpt-rag-chat'); ?></h3>
                                <div class="status-list">
                                    <div class="status-item">
                                        <div class="status-icon">
                                            <?php if (!empty($settings['openai_api_key'])): ?>
                                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                                            <?php else: ?>
                                                <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="status-content">
                                            <span class="status-label"><?php esc_html_e('OpenAI API', 'wp-gpt-rag-chat'); ?></span>
                                            <span class="status-value">
                                                <?php if (!empty($settings['openai_api_key'])): ?>
                                                    <?php esc_html_e('Configured', 'wp-gpt-rag-chat'); ?>
                                                <?php else: ?>
                                                    <?php esc_html_e('Not configured', 'wp-gpt-rag-chat'); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="status-item">
                                        <div class="status-icon">
                                            <?php if (!empty($settings['pinecone_api_key'])): ?>
                                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                                            <?php else: ?>
                                                <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="status-content">
                                            <span class="status-label"><?php esc_html_e('Pinecone API', 'wp-gpt-rag-chat'); ?></span>
                                            <span class="status-value">
                                                <?php if (!empty($settings['pinecone_api_key'])): ?>
                                                    <?php esc_html_e('Configured', 'wp-gpt-rag-chat'); ?>
                                                <?php else: ?>
                                                    <?php esc_html_e('Not configured', 'wp-gpt-rag-chat'); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="status-item">
                                        <div class="status-icon">
                                            <?php if ($stats['total_vectors'] > 0): ?>
                                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                                            <?php else: ?>
                                                <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="status-content">
                                            <span class="status-label"><?php esc_html_e('Content Indexed', 'wp-gpt-rag-chat'); ?></span>
                                            <span class="status-value">
                                                <?php if ($stats['total_vectors'] > 0): ?>
                                                    <?php echo esc_html(sprintf(_n('%d vector', '%d vectors', $stats['total_vectors'], 'wp-gpt-rag-chat'), $stats['total_vectors'])); ?>
                                                <?php else: ?>
                                                    <?php esc_html_e('No content indexed', 'wp-gpt-rag-chat'); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Recent Activity -->
                            <div class="overview-card">
                                <h3><?php esc_html_e('Recent Activity', 'wp-gpt-rag-chat'); ?></h3>
                                <div class="activity-summary">
                                    <div class="activity-item">
                                        <span class="activity-label"><?php esc_html_e('Queries Today', 'wp-gpt-rag-chat'); ?></span>
                                        <span class="activity-value"><?php echo esc_html($dashboard_data['queries_today']); ?></span>
                                    </div>
                                    <div class="activity-item">
                                        <span class="activity-label"><?php esc_html_e('Recent Updates', 'wp-gpt-rag-chat'); ?></span>
                                        <span class="activity-value"><?php echo esc_html($stats['recent_activity']); ?></span>
                                    </div>
                                    <div class="activity-item">
                                        <span class="activity-label"><?php esc_html_e('Pending Issues', 'wp-gpt-rag-chat'); ?></span>
                                        <span class="activity-value"><?php echo esc_html($dashboard_data['pending_incidents']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="dashboard-activity">
            <h2><?php esc_html_e('Recent Activity', 'wp-gpt-rag-chat'); ?></h2>
            
            <div class="activity-content">
                <?php
                $recent_posts = get_posts([
                    'numberposts' => 5,
                    'post_status' => 'publish',
                    'post_type' => get_post_types(['public' => true]),
                    'meta_query' => [
                        [
                            'key' => '_wp_gpt_rag_chat_include',
                            'value' => '1',
                            'compare' => '='
                        ]
                    ],
                    'orderby' => 'modified',
                    'order' => 'DESC'
                ]);
                ?>
                
                <?php if (!empty($recent_posts)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Post', 'wp-gpt-rag-chat'); ?></th>
                                <th><?php esc_html_e('Type', 'wp-gpt-rag-chat'); ?></th>
                                <th><?php esc_html_e('Status', 'wp-gpt-rag-chat'); ?></th>
                                <th><?php esc_html_e('Last Modified', 'wp-gpt-rag-chat'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_posts as $post): ?>
                                <?php $post_status = WP_GPT_RAG_Chat\Admin::get_post_indexing_status($post->ID); ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>">
                                                <?php echo esc_html($post->post_title); ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td><?php echo esc_html(get_post_type_object($post->post_type)->labels->singular_name ?? $post->post_type); ?></td>
                                    <td>
                                        <?php if ($post_status['vector_count'] > 0): ?>
                                            <span class="status-indexed"><?php esc_html_e('Indexed', 'wp-gpt-rag-chat'); ?></span>
                                        <?php else: ?>
                                            <span class="status-not-indexed"><?php esc_html_e('Not Indexed', 'wp-gpt-rag-chat'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html(human_time_diff(strtotime($post->post_modified)) . ' ' . __('ago', 'wp-gpt-rag-chat')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php esc_html_e('No recent activity found.', 'wp-gpt-rag-chat'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Help & Documentation -->
        <div class="dashboard-help">
            <h2><?php esc_html_e('Help & Documentation', 'wp-gpt-rag-chat'); ?></h2>
            
            <div class="help-content">
                <div class="help-section">
                    <h3><?php esc_html_e('Getting Started', 'wp-gpt-rag-chat'); ?></h3>
                    <ol>
                        <li><?php esc_html_e('Configure your OpenAI and Pinecone API keys in Settings', 'wp-gpt-rag-chat'); ?></li>
                        <li><?php esc_html_e('Index your content using the Indexing page', 'wp-gpt-rag-chat'); ?></li>
                        <li><?php esc_html_e('Add the chat widget to your posts/pages using the shortcode [wp_gpt_rag_chat]', 'wp-gpt-rag-chat'); ?></li>
                    </ol>
                </div>
                
                <div class="help-section">
                    <h3><?php esc_html_e('Shortcodes', 'wp-gpt-rag-chat'); ?></h3>
                    <ul>
                        <li><code>[wp_gpt_rag_chat]</code> - <?php esc_html_e('Display the chat widget', 'wp-gpt-rag-chat'); ?></li>
                        <li><code>[wp_gpt_rag_chat enabled="0"]</code> - <?php esc_html_e('Disable chat for specific content', 'wp-gpt-rag-chat'); ?></li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h3><?php esc_html_e('Support', 'wp-gpt-rag-chat'); ?></h3>
                    <p>
                        <?php esc_html_e('For support and documentation, please visit the plugin page or contact the developer.', 'wp-gpt-rag-chat'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.role-info {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: 600;
    margin-bottom: 15px;
}

.role-badge.aims-manager {
    background: #d1a85f;
    color: #ffffff;
}

.role-badge.log-viewer {
    background: #0073aa;
    color: #ffffff;
}

.role-label {
    font-size: 14px;
    opacity: 0.9;
}

.role-name {
    font-size: 16px;
}

.role-description p {
    margin: 0;
    color: #646970;
    font-size: 14px;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .role-badge {
        flex-direction: column;
        text-align: center;
        gap: 5px;
    }
}
</style>

<style>
.wp-gpt-rag-chat-dashboard {
    margin-top: 20px;
}

.dashboard-stats {
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.stat-card h3 {
    margin: 0 0 10px 0;
    color: #646970;
    font-size: 14px;
    font-weight: 500;
}

.stat-number {
    font-size: 32px;
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 5px;
}

.stat-description {
    font-size: 12px;
    color: #646970;
}

/* New Dashboard Structure Styles */
.dashboard-metrics {
    margin-bottom: 30px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.metric-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.metric-card.system-status .metric-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.metric-card.activity-metrics .metric-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.metric-card.incidents-risk .metric-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.metric-card.health-status .metric-icon {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.metric-content h3 {
    margin: 0 0 10px 0;
    color: #1d2327;
    font-size: 16px;
    font-weight: 600;
}

.metric-number {
    font-size: 28px;
    font-weight: 700;
    color: #1d2327;
    margin-bottom: 5px;
}

.metric-description {
    font-size: 14px;
    color: #646970;
    margin-bottom: 5px;
}

.metric-sub {
    font-size: 12px;
    color: #8c8f94;
}

.metric-sub.risk-low {
    color: #00a32a;
}

.metric-sub.risk-medium {
    color: #dba617;
}

.metric-sub.risk-high {
    color: #d63638;
}

/* Main Content Grid */
.dashboard-main-content {
    margin-bottom: 30px;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.content-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.dashboard-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 25px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.dashboard-section h2 {
    margin: 0 0 20px 0;
    color: #1d2327;
    font-size: 18px;
    font-weight: 600;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 10px;
}

/* Action Cards */
.actions-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #0073aa;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.action-content {
    flex: 1;
}

.action-content h3 {
    margin: 0 0 5px 0;
    color: #1d2327;
    font-size: 16px;
    font-weight: 600;
}

.action-content p {
    margin: 0 0 10px 0;
    color: #646970;
    font-size: 14px;
}

/* Overview Cards */
.overview-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.overview-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 20px;
}

.overview-card h3 {
    margin: 0 0 15px 0;
    color: #1d2327;
    font-size: 16px;
    font-weight: 600;
}

.status-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
}

.status-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-label {
    font-weight: 500;
    color: #1d2327;
}

.status-value {
    font-size: 14px;
    color: #646970;
}

.activity-summary {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
}

.activity-label {
    font-weight: 500;
    color: #1d2327;
}

.activity-value {
    font-weight: 600;
    color: #0073aa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .metric-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .action-card {
        flex-direction: column;
        text-align: center;
    }
}

.dashboard-actions {
    margin-bottom: 30px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.action-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.action-card h3 {
    margin-top: 0;
    color: #1d2327;
}

.action-card p {
    color: #646970;
    margin-bottom: 15px;
}

.dashboard-status {
    margin-bottom: 30px;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.status-item {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.status-icon {
    font-size: 24px;
}

.status-content h4 {
    margin: 0 0 5px 0;
    color: #1d2327;
}

.status-content p {
    margin: 0;
    color: #646970;
    font-size: 14px;
}

.dashboard-activity {
    margin-bottom: 30px;
}

.activity-content {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.dashboard-help {
    margin-bottom: 30px;
}

.help-content {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.help-section {
    margin-bottom: 20px;
}

.help-section:last-child {
    margin-bottom: 0;
}

.help-section h3 {
    margin-top: 0;
    color: #1d2327;
}

.help-section ol,
.help-section ul {
    margin-left: 20px;
}

.help-section li {
    margin-bottom: 5px;
}

.help-section code {
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}

.status-indexed {
    color: #00a32a;
    font-weight: 500;
}

.status-not-indexed {
    color: #d63638;
    font-weight: 500;
}

/* KPIs Dashboard Styles */
.dashboard-kpis {
    margin-bottom: 30px;
}

.kpis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.kpi-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    display: flex;
    align-items: center;
    gap: 15px;
}

.kpi-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.kpi-card:nth-child(1) .kpi-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.kpi-card:nth-child(2) .kpi-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.kpi-card:nth-child(3) .kpi-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.kpi-card:nth-child(4) .kpi-icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

.kpi-content h3 {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #646970;
    font-weight: 500;
}

.kpi-number {
    font-size: 28px;
    font-weight: 700;
    color: #1d2327;
    margin-bottom: 5px;
}

.kpi-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
}

.trend-up {
    color: #00a32a;
    font-weight: 600;
}

.trend-warning {
    color: #d63638;
    font-weight: 600;
}

.trend-success {
    color: #00a32a;
    font-weight: 600;
}

.trend-neutral {
    color: #646970;
    font-weight: 600;
}

.trend-label {
    color: #646970;
}

.risk-low {
    color: #00a32a;
}

.risk-medium {
    color: #dba617;
}

.risk-high {
    color: #d63638;
}


@media (max-width: 768px) {
    .stats-grid,
    .actions-grid,
    .status-grid,
    .kpis-grid {
        grid-template-columns: 1fr;
    }
    
    .status-item {
        flex-direction: column;
        text-align: center;
    }
    
    .kpi-card {
        flex-direction: column;
        text-align: center;
    }
    
    .report-card {
        flex-direction: column;
        text-align: center;
    }
    
    .report-actions {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dashboard initialization code can be added here
});
</script>

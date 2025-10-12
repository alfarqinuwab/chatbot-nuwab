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
        <!-- Overview Stats -->
        <div class="dashboard-stats">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php esc_html_e('Total Vectors', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="stat-number"><?php echo esc_html(number_format($stats['total_vectors'])); ?></div>
                    <div class="stat-description"><?php esc_html_e('Indexed content chunks', 'wp-gpt-rag-chat'); ?></div>
                </div>
                
                <div class="stat-card">
                    <h3><?php esc_html_e('Indexed Posts', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="stat-number"><?php echo esc_html(number_format($stats['total_posts'])); ?></div>
                    <div class="stat-description"><?php esc_html_e('Posts with vectors', 'wp-gpt-rag-chat'); ?></div>
                </div>
                
                <div class="stat-card">
                    <h3><?php esc_html_e('Total Queries', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="stat-number"><?php echo esc_html(number_format($chat_stats['total_queries'])); ?></div>
                    <div class="stat-description"><?php esc_html_e('Chat interactions', 'wp-gpt-rag-chat'); ?></div>
                </div>
                
                <div class="stat-card">
                    <h3><?php esc_html_e('Recent Activity', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="stat-number"><?php echo esc_html(number_format($stats['recent_activity'])); ?></div>
                    <div class="stat-description"><?php esc_html_e('Vectors updated (24h)', 'wp-gpt-rag-chat'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions (Administrators only) -->
        <?php if ($is_aims_manager): ?>
        <div class="dashboard-actions">
            <h2><?php esc_html_e('Quick Actions', 'wp-gpt-rag-chat'); ?></h2>
            
            <div class="actions-grid">
                <div class="action-card">
                    <h3><?php esc_html_e('Settings', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('Configure OpenAI and Pinecone API settings', 'wp-gpt-rag-chat'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-settings')); ?>" class="button button-primary">
                        <?php esc_html_e('Configure', 'wp-gpt-rag-chat'); ?>
                    </a>
                </div>
                
                <div class="action-card">
                    <h3><?php esc_html_e('Indexing', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('Manage content indexing and bulk operations', 'wp-gpt-rag-chat'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-indexing')); ?>" class="button button-secondary">
                        <?php esc_html_e('Manage', 'wp-gpt-rag-chat'); ?>
                    </a>
                </div>
                
                <div class="action-card">
                    <h3><?php esc_html_e('Logs & Analytics', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('View chat logs and usage analytics', 'wp-gpt-rag-chat'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-logs')); ?>" class="button button-secondary">
                        <?php esc_html_e('View', 'wp-gpt-rag-chat'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Configuration Status (Administrators only) -->
        <?php if ($is_aims_manager): ?>
        <div class="dashboard-status">
            <h2><?php esc_html_e('Configuration Status', 'wp-gpt-rag-chat'); ?></h2>
            
            <div class="status-grid">
                <div class="status-item">
                    <div class="status-icon">
                        <?php if (!empty($settings['openai_api_key'])): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                        <?php else: ?>
                            <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
                        <?php endif; ?>
                    </div>
                    <div class="status-content">
                        <h4><?php esc_html_e('OpenAI API', 'wp-gpt-rag-chat'); ?></h4>
                        <p>
                            <?php if (!empty($settings['openai_api_key'])): ?>
                                <?php esc_html_e('Configured', 'wp-gpt-rag-chat'); ?>
                            <?php else: ?>
                                <?php esc_html_e('Not configured', 'wp-gpt-rag-chat'); ?>
                            <?php endif; ?>
                        </p>
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
                        <h4><?php esc_html_e('Pinecone API', 'wp-gpt-rag-chat'); ?></h4>
                        <p>
                            <?php if (!empty($settings['pinecone_api_key'])): ?>
                                <?php esc_html_e('Configured', 'wp-gpt-rag-chat'); ?>
                            <?php else: ?>
                                <?php esc_html_e('Not configured', 'wp-gpt-rag-chat'); ?>
                            <?php endif; ?>
                        </p>
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
                        <h4><?php esc_html_e('Content Indexed', 'wp-gpt-rag-chat'); ?></h4>
                        <p>
                            <?php if ($stats['total_vectors'] > 0): ?>
                                <?php echo esc_html(sprintf(_n('%d vector', '%d vectors', $stats['total_vectors'], 'wp-gpt-rag-chat'), $stats['total_vectors'])); ?>
                            <?php else: ?>
                                <?php esc_html_e('No content indexed', 'wp-gpt-rag-chat'); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
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

@media (max-width: 768px) {
    .stats-grid,
    .actions-grid,
    .status-grid {
        grid-template-columns: 1fr;
    }
    
    .status-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

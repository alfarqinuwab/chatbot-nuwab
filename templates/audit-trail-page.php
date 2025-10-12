<?php
/**
 * Audit Trail Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

use WP_GPT_RAG_Chat\Audit_Trail;

$audit_trail = new Audit_Trail();

// Handle actions
if (isset($_POST['action'])) {
    check_admin_referer('wp_gpt_rag_chat_audit_trail');
    
    switch ($_POST['action']) {
        case 'export_audit_trail':
            $filters = [
                'date_from' => sanitize_text_field($_POST['date_from'] ?? ''),
                'date_to' => sanitize_text_field($_POST['date_to'] ?? ''),
                'action' => sanitize_text_field($_POST['action_filter'] ?? ''),
                'severity' => sanitize_text_field($_POST['severity'] ?? ''),
                'user_id' => intval($_POST['user_id'] ?? 0)
            ];
            $format = sanitize_text_field($_POST['export_format'] ?? 'csv');
            $audit_trail->export($format, $filters);
            break;
            
        case 'cleanup_audit_trail':
            $days = intval($_POST['cleanup_days'] ?? 365);
            $deleted = $audit_trail->cleanup($days);
            echo '<div class="notice notice-success"><p>' . sprintf(__('Cleaned up %d audit trail entries.', 'wp-gpt-rag-chat'), $deleted) . '</p></div>';
            break;
    }
}

// Get filters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$action_filter = $_GET['action'] ?? '';
$severity_filter = $_GET['severity'] ?? '';
$user_id_filter = $_GET['user_id'] ?? '';

$filters = array_filter([
    'date_from' => $date_from,
    'date_to' => $date_to,
    'action' => $action_filter,
    'severity' => $severity_filter,
    'user_id' => $user_id_filter
]);

// Get audit trail entries
$page = max(1, intval($_GET['paged'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

$entries = $audit_trail->get_entries($per_page, $offset, $filters);
$total_entries = $audit_trail->get_entries(10000, 0, $filters); // Get total count
$total_pages = ceil(count($total_entries) / $per_page);

// Get statistics
$stats = $audit_trail->get_statistics(30);

// Get available actions and object types
$actions = Audit_Trail::get_actions();
$object_types = Audit_Trail::get_object_types();
?>

<div class="wrap cornuwab-admin-wrap">
    <h1>
        <span class="dashicons dashicons-shield"></span>
        <?php esc_html_e('Audit Trail', 'wp-gpt-rag-chat'); ?>
    </h1>
    
    <!-- Statistics -->
    <div class="audit-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php esc_html_e('Total Entries (30 days)', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['total_entries'])); ?></div>
            </div>
            
            <div class="stat-card">
                <h3><?php esc_html_e('Critical Events', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number">
                    <?php 
                    $critical_count = 0;
                    foreach ($stats['by_severity'] as $severity) {
                        if ($severity->severity === 'critical') {
                            $critical_count = $severity->count;
                            break;
                        }
                    }
                    echo esc_html(number_format($critical_count));
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3><?php esc_html_e('Top Action', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number">
                    <?php 
                    if (!empty($stats['by_action'])) {
                        echo esc_html($stats['by_action'][0]->action);
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3><?php esc_html_e('Most Active User', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number">
                    <?php 
                    if (!empty($stats['by_user'])) {
                        echo esc_html($stats['by_user'][0]->user_login);
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="audit-filters">
        <h2><?php esc_html_e('Filters', 'wp-gpt-rag-chat'); ?></h2>
        <form method="get" class="audit-filter-form">
            <input type="hidden" name="page" value="wp-gpt-rag-chat-audit-trail">
            
            <div class="filter-row">
                <div class="wp-gpt-rag-filter-group">
                    <label for="date_from"><?php esc_html_e('From Date:', 'wp-gpt-rag-chat'); ?></label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                </div>
                
                <div class="wp-gpt-rag-filter-group">
                    <label for="date_to"><?php esc_html_e('To Date:', 'wp-gpt-rag-chat'); ?></label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                </div>
                
                <div class="wp-gpt-rag-filter-group">
                    <label for="action"><?php esc_html_e('Action:', 'wp-gpt-rag-chat'); ?></label>
                    <select id="action" name="action">
                        <option value=""><?php esc_html_e('All Actions', 'wp-gpt-rag-chat'); ?></option>
                        <?php foreach ($actions as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($action_filter, $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="wp-gpt-rag-filter-group">
                    <label for="severity"><?php esc_html_e('Severity:', 'wp-gpt-rag-chat'); ?></label>
                    <select id="severity" name="severity">
                        <option value=""><?php esc_html_e('All Severities', 'wp-gpt-rag-chat'); ?></option>
                        <option value="low" <?php selected($severity_filter, 'low'); ?>><?php esc_html_e('Low', 'wp-gpt-rag-chat'); ?></option>
                        <option value="medium" <?php selected($severity_filter, 'medium'); ?>><?php esc_html_e('Medium', 'wp-gpt-rag-chat'); ?></option>
                        <option value="high" <?php selected($severity_filter, 'high'); ?>><?php esc_html_e('High', 'wp-gpt-rag-chat'); ?></option>
                        <option value="critical" <?php selected($severity_filter, 'critical'); ?>><?php esc_html_e('Critical', 'wp-gpt-rag-chat'); ?></option>
                    </select>
                </div>
                
                <div class="wp-gpt-rag-filter-group">
                    <label for="user_id"><?php esc_html_e('User ID:', 'wp-gpt-rag-chat'); ?></label>
                    <input type="number" id="user_id" name="user_id" value="<?php echo esc_attr($user_id_filter); ?>" placeholder="<?php esc_html_e('User ID', 'wp-gpt-rag-chat'); ?>">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="button button-primary">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e('Apply Filters', 'wp-gpt-rag-chat'); ?>
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-gpt-rag-chat-audit-trail')); ?>" class="button">
                    <span class="dashicons dashicons-dismiss"></span>
                    <?php esc_html_e('Clear Filters', 'wp-gpt-rag-chat'); ?>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Export and Cleanup Actions -->
    <div class="audit-actions">
        <h2><?php esc_html_e('Actions', 'wp-gpt-rag-chat'); ?></h2>
        
        <form method="post" class="audit-action-form">
            <?php wp_nonce_field('wp_gpt_rag_chat_audit_trail'); ?>
            
            <div class="action-row">
                <div class="action-group">
                    <h3><?php esc_html_e('Export Audit Trail', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('Export audit trail data to CSV or JSON format.', 'wp-gpt-rag-chat'); ?></p>
                    
                    <div class="export-filters">
                        <div class="wp-gpt-rag-filter-group">
                            <label for="export_date_from"><?php esc_html_e('From Date:', 'wp-gpt-rag-chat'); ?></label>
                            <input type="date" id="export_date_from" name="date_from">
                        </div>
                        
                        <div class="wp-gpt-rag-filter-group">
                            <label for="export_date_to"><?php esc_html_e('To Date:', 'wp-gpt-rag-chat'); ?></label>
                            <input type="date" id="export_date_to" name="date_to">
                        </div>
                        
                        <div class="wp-gpt-rag-filter-group">
                            <label for="export_format"><?php esc_html_e('Format:', 'wp-gpt-rag-chat'); ?></label>
                            <select id="export_format" name="export_format">
                                <option value="csv"><?php esc_html_e('CSV', 'wp-gpt-rag-chat'); ?></option>
                                <option value="json"><?php esc_html_e('JSON', 'wp-gpt-rag-chat'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="action" value="export_audit_trail" class="button button-primary">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Export Audit Trail', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
                
                <div class="action-group">
                    <h3><?php esc_html_e('Cleanup Old Entries', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('Remove audit trail entries older than specified days.', 'wp-gpt-rag-chat'); ?></p>
                    
                    <div class="cleanup-filters">
                        <div class="wp-gpt-rag-filter-group">
                            <label for="cleanup_days"><?php esc_html_e('Keep entries newer than (days):', 'wp-gpt-rag-chat'); ?></label>
                            <input type="number" id="cleanup_days" name="cleanup_days" value="365" min="30" max="3650">
                        </div>
                    </div>
                    
                    <button type="submit" name="action" value="cleanup_audit_trail" class="button button-secondary" onclick="return confirm('<?php esc_html_e('Are you sure you want to delete old audit trail entries? This action cannot be undone.', 'wp-gpt-rag-chat'); ?>')">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Cleanup Old Entries', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Audit Trail Entries -->
    <div class="audit-entries">
        <h2><?php esc_html_e('Audit Trail Entries', 'wp-gpt-rag-chat'); ?></h2>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <span class="displaying-num">
                    <?php printf(__('%s items', 'wp-gpt-rag-chat'), number_format(count($total_entries))); ?>
                </span>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped audit-trail-table">
            <thead>
                <tr>
                    <th style="width: 40px;"><?php esc_html_e('ID', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 120px;"><?php esc_html_e('Date/Time', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('User', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 80px;"><?php esc_html_e('Role', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 120px;"><?php esc_html_e('Action', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('Object', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php esc_html_e('Description', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 80px;"><?php esc_html_e('Severity', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 80px;"><?php esc_html_e('Status', 'wp-gpt-rag-chat'); ?></th>
                    <th style="width: 100px;"><?php esc_html_e('IP Address', 'wp-gpt-rag-chat'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entries)): ?>
                    <tr>
                        <td colspan="10" class="no-items">
                            <?php esc_html_e('No audit trail entries found.', 'wp-gpt-rag-chat'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?php echo esc_html($entry->id); ?></td>
                            <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($entry->created_at))); ?></td>
                            <td>
                                <strong><?php echo esc_html($entry->user_login); ?></strong>
                                <br><small>ID: <?php echo esc_html($entry->user_id); ?></small>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo esc_attr($entry->user_role); ?>">
                                    <?php echo esc_html(ucfirst($entry->user_role)); ?>
                                </span>
                            </td>
                            <td>
                                <span class="action-badge action-<?php echo esc_attr($entry->action); ?>">
                                    <?php echo esc_html($entry->action); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($entry->object_id): ?>
                                    <?php echo esc_html($entry->object_type); ?> #<?php echo esc_html($entry->object_id); ?>
                                <?php else: ?>
                                    <?php echo esc_html($entry->object_type); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($entry->description); ?></td>
                            <td>
                                <span class="severity-badge severity-<?php echo esc_attr($entry->severity); ?>">
                                    <?php echo esc_html(ucfirst($entry->severity)); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($entry->status); ?>">
                                    <?php echo esc_html(ucfirst($entry->status)); ?>
                                </span>
                            </td>
                            <td>
                                <code><?php echo esc_html($entry->ip_address); ?></code>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $pagination_args = [
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'current' => $page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;'
                    ];
                    echo paginate_links($pagination_args);
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.audit-stats {
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #646970;
    font-weight: 600;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #1d2327;
}

.audit-filters {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr 0.8fr 0.8fr 0.6fr;
    gap: 15px;
    margin-bottom: 15px;
    align-items: end;
}

.wp-gpt-rag-filter-group {
    display: flex;
    flex-direction: column;
}

.wp-gpt-rag-filter-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #1d2327;
}

.wp-gpt-rag-filter-group input,
.wp-gpt-rag-filter-group select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.audit-actions {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.action-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.action-group {
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    padding: 20px;
}

.action-group h3 {
    margin: 0 0 10px 0;
    color: #1d2327;
}

.action-group p {
    margin: 0 0 15px 0;
    color: #646970;
    font-size: 14px;
}

.export-filters {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.export-filters .wp-gpt-rag-filter-group:last-child {
    grid-column: 1 / -1;
}

.cleanup-filters {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.audit-trail-table {
    margin-top: 20px;
}

.role-badge,
.action-badge,
.severity-badge,
.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.role-badge {
    background: #f0f0f1;
    color: #1d2327;
}

.action-badge {
    background: #e7f3ff;
    color: #0073aa;
}

.severity-badge.severity-low {
    background: #d1ecf1;
    color: #0c5460;
}

.severity-badge.severity-medium {
    background: #fff3cd;
    color: #856404;
}

.severity-badge.severity-high {
    background: #f8d7da;
    color: #721c24;
}

.severity-badge.severity-critical {
    background: #f5c6cb;
    color: #721c24;
    font-weight: bold;
}

.status-badge.status-success {
    background: #d4edda;
    color: #155724;
}

.status-badge.status-error {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.status-warning {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 1200px) {
    .filter-row {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    
    .filter-row .filter-group:nth-child(n+3) {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .action-row {
        grid-template-columns: 1fr;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .export-filters {
        grid-template-columns: 1fr;
    }
    
    .export-filters .wp-gpt-rag-filter-group:last-child {
        grid-column: 1;
    }
    
    .cleanup-filters {
        flex-direction: column;
    }
}
</style>

<?php
/**
 * Incident Reports Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get incident reports from database
global $wpdb;
$table_name = $wpdb->prefix . 'gpt_rag_incident_reports';

// Handle status updates
if (isset($_POST['update_status']) && wp_verify_nonce($_POST['_wpnonce'], 'update_incident_status')) {
    $incident_id = intval($_POST['incident_id']);
    $new_status = sanitize_text_field($_POST['status']);
    $admin_notes = sanitize_textarea_field($_POST['admin_notes']);
    
    $wpdb->update(
        $table_name,
        [
            'status' => $new_status,
            'admin_notes' => $admin_notes,
            'resolved_at' => $new_status === 'resolved' ? current_time('mysql') : null
        ],
        ['id' => $incident_id],
        ['%s', '%s', '%s'],
        ['%d']
    );
    
    echo '<div class="notice notice-success"><p>' . __('Incident status updated successfully', 'wp-gpt-rag-chat') . '</p></div>';
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['problem_type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$where_values = [];

if (!empty($status_filter)) {
    $where_conditions[] = 'status = %s';
    $where_values[] = $status_filter;
}

if (!empty($type_filter)) {
    $where_conditions[] = 'problem_type = %s';
    $where_values[] = $type_filter;
}

if (!empty($search)) {
    $where_conditions[] = '(problem_description LIKE %s OR user_email LIKE %s)';
    $where_values[] = '%' . $wpdb->esc_like($search) . '%';
    $where_values[] = '%' . $wpdb->esc_like($search) . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_query = "SELECT COUNT(*) FROM $table_name $where_clause";
if (!empty($where_values)) {
    $total_count = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
} else {
    $total_count = $wpdb->get_var($count_query);
}

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get incidents
$query = "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
$where_values[] = $per_page;
$where_values[] = $offset;

$incidents = $wpdb->get_results($wpdb->prepare($query, $where_values));

// Get unique problem types for filter
$problem_types = $wpdb->get_results("SELECT DISTINCT problem_type FROM $table_name ORDER BY problem_type");

// Get users for assignment dropdown
$users = get_users(['role__in' => ['administrator', 'editor', 'author']]);

// Function to get problem type color and display name
function get_problem_type_info($problem_type) {
    $problem_types = [
        'wrong_information' => [
            'color' => '#e74c3c',
            'bg_color' => '#fdf2f2',
            'text_color' => '#c53030',
            'display_name' => __('Wrong Information', 'wp-gpt-rag-chat'),
            'icon' => '❌'
        ],
        'bias' => [
            'color' => '#f39c12',
            'bg_color' => '#fef5e7',
            'text_color' => '#d69e2e',
            'display_name' => __('Bias', 'wp-gpt-rag-chat'),
            'icon' => '⚖️'
        ],
        'hallucination' => [
            'color' => '#8e44ad',
            'bg_color' => '#f3e8ff',
            'text_color' => '#7c3aed',
            'display_name' => __('Hallucination', 'wp-gpt-rag-chat'),
            'icon' => '🔮'
        ],
        'inappropriate_response' => [
            'color' => '#e67e22',
            'bg_color' => '#fef3e2',
            'text_color' => '#d97706',
            'display_name' => __('Inappropriate Response', 'wp-gpt-rag-chat'),
            'icon' => '🚫'
        ],
        'technical_issue' => [
            'color' => '#3498db',
            'bg_color' => '#e6f3ff',
            'text_color' => '#2563eb',
            'display_name' => __('Technical Issue', 'wp-gpt-rag-chat'),
            'icon' => '🔧'
        ],
        'privacy_concern' => [
            'color' => '#9b59b6',
            'bg_color' => '#f3e8ff',
            'text_color' => '#7c3aed',
            'display_name' => __('Privacy Concern', 'wp-gpt-rag-chat'),
            'icon' => '🔒'
        ],
        'parliamentary_procedure' => [
            'color' => '#2c3e50',
            'bg_color' => '#f1f5f9',
            'text_color' => '#475569',
            'display_name' => __('Parliamentary Procedure', 'wp-gpt-rag-chat'),
            'icon' => '🏛️'
        ],
        'legal_advice' => [
            'color' => '#c0392b',
            'bg_color' => '#fef2f2',
            'text_color' => '#dc2626',
            'display_name' => __('Legal Advice', 'wp-gpt-rag-chat'),
            'icon' => '⚖️'
        ],
        'other' => [
            'color' => '#95a5a6',
            'bg_color' => '#f8f9fa',
            'text_color' => '#6b7280',
            'display_name' => __('Other', 'wp-gpt-rag-chat'),
            'icon' => '📝'
        ]
    ];
    
    return isset($problem_types[$problem_type]) ? $problem_types[$problem_type] : [
        'color' => '#6b7280',
        'bg_color' => '#f9fafb',
        'text_color' => '#6b7280',
        'display_name' => ucfirst(str_replace('_', ' ', $problem_type)),
        'icon' => '📋'
    ];
}

?>

<div class="wrap">
    <h1><?php esc_html_e('Incident Reports', 'wp-gpt-rag-chat'); ?></h1>
    
    <!-- Report Generation Section -->
    <div class="tablenav top" style="margin-bottom: 20px;">
        <div class="alignleft actions">
            <button type="button" class="button button-secondary" id="generate-report-btn" style="background: #007cba; color: white; border-color: #007cba;">
                <span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php esc_html_e('Generate Full Report', 'wp-gpt-rag-chat'); ?>
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="tablenav top">
        <form method="get" action="">
            <input type="hidden" name="page" value="wp-gpt-rag-chat-incidents">
            
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php esc_html_e('All Statuses', 'wp-gpt-rag-chat'); ?></option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php esc_html_e('Pending', 'wp-gpt-rag-chat'); ?></option>
                    <option value="in_progress" <?php selected($status_filter, 'in_progress'); ?>><?php esc_html_e('In Progress', 'wp-gpt-rag-chat'); ?></option>
                    <option value="resolved" <?php selected($status_filter, 'resolved'); ?>><?php esc_html_e('Resolved', 'wp-gpt-rag-chat'); ?></option>
                </select>
                
                <select name="problem_type">
                    <option value=""><?php esc_html_e('All Types', 'wp-gpt-rag-chat'); ?></option>
                    <?php foreach ($problem_types as $type): 
                        $type_info = get_problem_type_info($type->problem_type);
                    ?>
                        <option value="<?php echo esc_attr($type->problem_type); ?>" <?php selected($type_filter, $type->problem_type); ?>>
                            <?php echo esc_html($type_info['icon'] . ' ' . $type_info['display_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="search" name="search" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search reports...', 'wp-gpt-rag-chat'); ?>">
                
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'wp-gpt-rag-chat'); ?>">
            </div>
        </form>
    </div>
    
    <!-- Statistics -->
    <div class="incident-stats" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 5px;">
        <?php
        $stats = $wpdb->get_results("
            SELECT 
                status,
                COUNT(*) as count 
            FROM $table_name 
            GROUP BY status
        ");
        
        $stats_array = [];
        foreach ($stats as $stat) {
            $stats_array[$stat->status] = $stat->count;
        }
        ?>
        
        <strong><?php esc_html_e('Statistics:', 'wp-gpt-rag-chat'); ?></strong>
        <span style="margin-left: 20px;">
            <?php esc_html_e('Pending:', 'wp-gpt-rag-chat'); ?> <strong><?php echo $stats_array['pending'] ?? 0; ?></strong>
        </span>
        <span style="margin-left: 20px;">
            <?php esc_html_e('In Progress:', 'wp-gpt-rag-chat'); ?> <strong><?php echo $stats_array['in_progress'] ?? 0; ?></strong>
        </span>
        <span style="margin-left: 20px;">
            <?php esc_html_e('Resolved:', 'wp-gpt-rag-chat'); ?> <strong><?php echo $stats_array['resolved'] ?? 0; ?></strong>
        </span>
        <span style="margin-left: 20px;">
            <?php esc_html_e('Total:', 'wp-gpt-rag-chat'); ?> <strong><?php echo $total_count; ?></strong>
        </span>
    </div>
    
    <!-- Incidents Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'wp-gpt-rag-chat'); ?></th>
                <th><?php esc_html_e('Problem Type', 'wp-gpt-rag-chat'); ?></th>
                <th><?php esc_html_e('Description', 'wp-gpt-rag-chat'); ?></th>
                <th><?php esc_html_e('User', 'wp-gpt-rag-chat'); ?></th>
                <th><?php esc_html_e('Status', 'wp-gpt-rag-chat'); ?></th>
                <th><?php esc_html_e('Assign To', 'wp-gpt-rag-chat'); ?></th>
                <th><?php esc_html_e('Date', 'wp-gpt-rag-chat'); ?></th>
                <th><?php esc_html_e('Actions', 'wp-gpt-rag-chat'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($incidents)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        <?php esc_html_e('No incident reports found.', 'wp-gpt-rag-chat'); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($incidents as $incident): ?>
                    <tr>
                        <td><?php echo esc_html($incident->id); ?></td>
                        <td>
                            <?php 
                            $type_info = get_problem_type_info($incident->problem_type);
                            ?>
                            <span class="problem-type-tag" style="
                                display: inline-flex;
                                align-items: center;
                                gap: 6px;
                                padding: 6px 12px;
                                border-radius: 20px;
                                font-size: 12px;
                                font-weight: 500;
                                background: <?php echo esc_attr($type_info['bg_color']); ?>;
                                color: <?php echo esc_attr($type_info['text_color']); ?>;
                                border: 1px solid <?php echo esc_attr($type_info['color']); ?>;
                            ">
                                <span style="font-size: 14px;"><?php echo esc_html($type_info['icon']); ?></span>
                                <span><?php echo esc_html($type_info['display_name']); ?></span>
                            </span>
                        </td>
                        <td>
                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo esc_html(wp_trim_words($incident->problem_description, 10)); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($incident->user_id > 0): ?>
                                <?php $user = get_user_by('id', $incident->user_id); ?>
                                <?php echo $user ? esc_html($user->display_name) : esc_html__('Unknown User', 'wp-gpt-rag-chat'); ?>
                            <?php else: ?>
                                <?php esc_html_e('Guest', 'wp-gpt-rag-chat'); ?>
                            <?php endif; ?>
                            <?php if ($incident->user_email): ?>
                                <br><small><?php echo esc_html($incident->user_email); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-<?php echo esc_attr($incident->status); ?>" style="
                                padding: 4px 8px; 
                                border-radius: 3px; 
                                font-size: 12px;
                                background: <?php 
                                    echo $incident->status === 'pending' ? '#f0ad4e' : 
                                        ($incident->status === 'in_progress' ? '#5bc0de' : '#5cb85c'); 
                                ?>;
                                color: white;
                            ">
                                <?php echo esc_html(ucfirst($incident->status)); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($incident->assigned_to)): ?>
                                <?php $assigned_user = get_user_by('id', $incident->assigned_to); ?>
                                <?php if ($assigned_user): ?>
                                    <span class="assigned-user" style="background: #e7f3ff; color: #0066cc; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                        <?php echo esc_html($assigned_user->display_name); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="assigned-user" style="background: #f0f0f0; color: #666; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                        <?php esc_html_e('Unknown User', 'wp-gpt-rag-chat'); ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="unassigned" style="background: #f8f8f8; color: #999; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                    <?php esc_html_e('Unassigned', 'wp-gpt-rag-chat'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(date_i18n('M j, Y g:i A', strtotime($incident->created_at))); ?></td>
                        <td>
                            <button class="button button-small view-incident" data-id="<?php echo esc_attr($incident->id); ?>">
                                <?php esc_html_e('View', 'wp-gpt-rag-chat'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($total_count > $per_page): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $total_pages = ceil($total_count / $per_page);
                $current_url = remove_query_arg('paged');
                
                if ($current_page > 1): ?>
                    <a class="button" href="<?php echo esc_url(add_query_arg('paged', $current_page - 1, $current_url)); ?>">
                        <?php esc_html_e('&laquo; Previous', 'wp-gpt-rag-chat'); ?>
                    </a>
                <?php endif; ?>
                
                <span class="paging-input">
                    <?php printf(__('Page %1$s of %2$s', 'wp-gpt-rag-chat'), $current_page, $total_pages); ?>
                </span>
                
                <?php if ($current_page < $total_pages): ?>
                    <a class="button" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1, $current_url)); ?>">
                        <?php esc_html_e('Next &raquo;', 'wp-gpt-rag-chat'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Incident Details Modal -->
<div id="incident-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div id="incident-modal-content"></div>
    </div>
</div>

<!-- Assignment Success Modal -->
<div id="assignment-success-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="font-size: 48px; color: #00a32a; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 style="margin: 0 0 15px 0; color: #1d2327; font-size: 20px;">
            <?php esc_html_e('Assignment Updated Successfully', 'wp-gpt-rag-chat'); ?>
        </h3>
        <p style="color: #646970; margin-bottom: 25px; line-height: 1.5;">
            <?php esc_html_e('The incident assignment has been updated successfully.', 'wp-gpt-rag-chat'); ?>
        </p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button class="button button-primary" id="closeAssignmentSuccessModalBtn">
                <?php esc_html_e('OK', 'wp-gpt-rag-chat'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Report Generation Success Modal -->
<div id="report-success-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="font-size: 48px; color: #00a32a; margin-bottom: 20px;">
            <i class="fas fa-file-download"></i>
        </div>
        <h3 style="margin: 0 0 15px 0; color: #1d2327; font-size: 20px;">
            <?php esc_html_e('Report Generated Successfully', 'wp-gpt-rag-chat'); ?>
        </h3>
        <p style="color: #646970; margin-bottom: 25px; line-height: 1.5;">
            <?php esc_html_e('Your incident report has been generated and is ready for download.', 'wp-gpt-rag-chat'); ?>
        </p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button class="button button-primary" id="closeReportSuccessModalBtn">
                <?php esc_html_e('OK', 'wp-gpt-rag-chat'); ?>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle view incident button clicks
    document.querySelectorAll('.view-incident').forEach(button => {
        button.addEventListener('click', function() {
            const incidentId = this.getAttribute('data-id');
            loadIncidentDetails(incidentId);
        });
    });
});

function loadIncidentDetails(incidentId) {
    const modal = document.getElementById('incident-modal');
    const content = document.getElementById('incident-modal-content');
    
    // Store the current incident ID for use in form submissions
    modal.dataset.currentIncidentId = incidentId;
    
    // Show loading state with preloader
    content.innerHTML = `
        <h3><?php esc_html_e('Incident Details', 'wp-gpt-rag-chat'); ?></h3>
        <div style="text-align: center; padding: 40px 20px;">
            <div class="incident-preloader" style="
                display: inline-block;
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #007cba;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 20px;
            "></div>
            <p style="color: #666; font-size: 14px; margin: 0;">
                <?php esc_html_e('Loading incident details...', 'wp-gpt-rag-chat'); ?>
            </p>
        </div>
    `;
    modal.style.display = 'block';
    
    // Make AJAX call to get incident details
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_incident_details&incident_id=' + incidentId + '&nonce=<?php echo wp_create_nonce('get_incident_details'); ?>'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('AJAX Response:', data); // Debug log
        if (data.success) {
            displayIncidentDetails(data.data);
        } else {
            console.error('AJAX Error:', data);
            content.innerHTML = `
                <h3><?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?></h3>
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; padding: 15px; margin: 10px 0;">
                    <p style="color: #dc2626; margin: 0;">
                        <?php esc_html_e('Failed to load incident details', 'wp-gpt-rag-chat'); ?>
                        ${data.data ? ': ' + data.data : ''}
                    </p>
                </div>
                <button class="button" onclick="document.getElementById('incident-modal').style.display='none'">
                    <?php esc_html_e('Close', 'wp-gpt-rag-chat'); ?>
                </button>
            `;
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        content.innerHTML = `
            <h3><?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?></h3>
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; padding: 15px; margin: 10px 0;">
                <p style="color: #dc2626; margin: 0;">
                    <?php esc_html_e('Network error occurred while loading incident details', 'wp-gpt-rag-chat'); ?>
                </p>
                <p style="color: #666; font-size: 12px; margin: 5px 0 0 0;">
                    ${error.message}
                </p>
            </div>
            <button class="button" onclick="document.getElementById('incident-modal').style.display='none'">
                <?php esc_html_e('Close', 'wp-gpt-rag-chat'); ?>
            </button>
        `;
    });
}

// JavaScript function to get problem type info
function getProblemTypeInfo(problemType) {
    const problemTypes = {
        'wrong_information': {
            color: '#e74c3c',
            bg_color: '#fdf2f2',
            text_color: '#c53030',
            display_name: '<?php echo esc_js(__('Wrong Information', 'wp-gpt-rag-chat')); ?>',
            icon: '❌'
        },
        'bias': {
            color: '#f39c12',
            bg_color: '#fef5e7',
            text_color: '#d69e2e',
            display_name: '<?php echo esc_js(__('Bias', 'wp-gpt-rag-chat')); ?>',
            icon: '⚖️'
        },
        'hallucination': {
            color: '#8e44ad',
            bg_color: '#f3e8ff',
            text_color: '#7c3aed',
            display_name: '<?php echo esc_js(__('Hallucination', 'wp-gpt-rag-chat')); ?>',
            icon: '🔮'
        },
        'inappropriate_response': {
            color: '#e67e22',
            bg_color: '#fef3e2',
            text_color: '#d97706',
            display_name: '<?php echo esc_js(__('Inappropriate Response', 'wp-gpt-rag-chat')); ?>',
            icon: '🚫'
        },
        'technical_issue': {
            color: '#3498db',
            bg_color: '#e6f3ff',
            text_color: '#2563eb',
            display_name: '<?php echo esc_js(__('Technical Issue', 'wp-gpt-rag-chat')); ?>',
            icon: '🔧'
        },
        'privacy_concern': {
            color: '#9b59b6',
            bg_color: '#f3e8ff',
            text_color: '#7c3aed',
            display_name: '<?php echo esc_js(__('Privacy Concern', 'wp-gpt-rag-chat')); ?>',
            icon: '🔒'
        },
        'parliamentary_procedure': {
            color: '#2c3e50',
            bg_color: '#f1f5f9',
            text_color: '#475569',
            display_name: '<?php echo esc_js(__('Parliamentary Procedure', 'wp-gpt-rag-chat')); ?>',
            icon: '🏛️'
        },
        'legal_advice': {
            color: '#c0392b',
            bg_color: '#fef2f2',
            text_color: '#dc2626',
            display_name: '<?php echo esc_js(__('Legal Advice', 'wp-gpt-rag-chat')); ?>',
            icon: '⚖️'
        },
        'other': {
            color: '#95a5a6',
            bg_color: '#f8f9fa',
            text_color: '#6b7280',
            display_name: '<?php echo esc_js(__('Other', 'wp-gpt-rag-chat')); ?>',
            icon: '📝'
        }
    };
    
    return problemTypes[problemType] || {
        color: '#6b7280',
        bg_color: '#f9fafb',
        text_color: '#6b7280',
        display_name: problemType.charAt(0).toUpperCase() + problemType.slice(1).replace(/_/g, ' '),
        icon: '📋'
    };
}

function displayIncidentDetails(incident) {
    const content = document.getElementById('incident-modal-content');
    
    const statusClass = 'status-' + incident.status;
    const statusText = incident.status.charAt(0).toUpperCase() + incident.status.slice(1);
    
    // Get problem type info
    const problemTypeInfo = getProblemTypeInfo(incident.problem_type);
    
    content.innerHTML = `
        <!-- Header -->
        <div class="incident-header">
            <h2><?php esc_html_e('Incident Details', 'wp-gpt-rag-chat'); ?> #${incident.id}</h2>
            <div class="incident-status">
                <span class="status-${statusClass}">${statusText}</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="incident-content">
            <!-- Problem Type -->
            <div class="postbox">
                <div class="postbox-header">
                    <h3 class="hndle"><?php esc_html_e('Problem Type', 'wp-gpt-rag-chat'); ?></h3>
                </div>
                <div class="inside">
                    <span class="problem-type-tag" style="
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 4px 12px;
                        border-radius: 3px;
                        font-size: 12px;
                        font-weight: 500;
                        background: ${problemTypeInfo.bg_color};
                        color: ${problemTypeInfo.text_color};
                        border: 1px solid ${problemTypeInfo.color};
                    ">
                        <span style="font-size: 14px;">${problemTypeInfo.icon}</span>
                        <span>${problemTypeInfo.display_name}</span>
                    </span>
                </div>
            </div>

            <!-- User Information -->
            <div class="postbox">
                <div class="postbox-header">
                    <h3 class="hndle"><?php esc_html_e('User Information', 'wp-gpt-rag-chat'); ?></h3>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Email:', 'wp-gpt-rag-chat'); ?></th>
                            <td>${incident.user_email || '<?php esc_html_e('Guest', 'wp-gpt-rag-chat'); ?>'}</td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('IP Address:', 'wp-gpt-rag-chat'); ?></th>
                            <td><code>${incident.user_ip}</code></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Date:', 'wp-gpt-rag-chat'); ?></th>
                            <td>${incident.created_at}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Assignment -->
            <div class="postbox">
                <div class="postbox-header">
                    <h3 class="hndle"><?php esc_html_e('Assignment', 'wp-gpt-rag-chat'); ?></h3>
                </div>
                <div class="inside">
                    <div id="assigned-user-display" class="assignment-display ${incident.assigned_to ? 'assigned' : 'unassigned'}">
                        ${incident.assigned_to ? 
                            `<strong>${incident.assigned_user_name}</strong>` : 
                            `<em><?php esc_html_e('Unassigned', 'wp-gpt-rag-chat'); ?></em>`
                        }
                    </div>
                </div>
            </div>

            <!-- Problem Description -->
            <div class="postbox">
                <div class="postbox-header">
                    <h3 class="hndle"><?php esc_html_e('Problem Description', 'wp-gpt-rag-chat'); ?></h3>
                </div>
                <div class="inside">
                    <div class="description-text">${incident.problem_description}</div>
                </div>
            </div>

            ${incident.admin_notes ? `
            <!-- Admin Notes -->
            <div class="postbox">
                <div class="postbox-header">
                    <h3 class="hndle"><?php esc_html_e('Admin Notes', 'wp-gpt-rag-chat'); ?></h3>
                </div>
                <div class="inside">
                    <div class="admin-notes-text">${incident.admin_notes}</div>
                </div>
            </div>
            ` : ''}

            <!-- Technical Details -->
            <div class="postbox">
                <div class="postbox-header">
                    <h3 class="hndle"><?php esc_html_e('Technical Details', 'wp-gpt-rag-chat'); ?></h3>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('User Agent:', 'wp-gpt-rag-chat'); ?></th>
                            <td><code style="word-break: break-all; font-size: 11px;">${incident.user_agent}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Update Form -->
        <div class="postbox">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e('Update Status', 'wp-gpt-rag-chat'); ?></h3>
            </div>
            <div class="inside">
                ${!incident.assigned_to ? `
                <div class="notice notice-warning inline">
                    <p><strong><?php esc_html_e('Notice:', 'wp-gpt-rag-chat'); ?></strong> <?php esc_html_e('This incident must be assigned to a user before the status can be updated.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                ` : ''}
                <form id="statusUpdateForm" ${!incident.assigned_to ? 'style="opacity: 0.6; pointer-events: none;"' : ''}>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="newStatus"><?php esc_html_e('Status:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="newStatus" name="new_status" class="regular-text" ${!incident.assigned_to ? 'disabled' : ''}>
                                    <option value="pending" ${incident.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="in_progress" ${incident.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                    <option value="resolved" ${incident.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="adminNotes"><?php esc_html_e('Admin Notes:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <textarea id="adminNotes" name="admin_notes" rows="4" class="large-text" placeholder="<?php esc_attr_e('Add notes about this incident...', 'wp-gpt-rag-chat'); ?>" ${!incident.assigned_to ? 'disabled' : ''}>${incident.admin_notes || ''}</textarea>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" ${!incident.assigned_to ? 'disabled' : ''}>
                            <?php esc_html_e('Update Status', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="postbox">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e('Assign Incident', 'wp-gpt-rag-chat'); ?></h3>
            </div>
            <div class="inside">
                <form id="assignmentForm">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="assignTo"><?php esc_html_e('Assign To:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="assignTo" name="assigned_to" class="regular-text">
                                    <option value=""><?php esc_html_e('Unassigned', 'wp-gpt-rag-chat'); ?></option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-secondary">
                            <?php esc_html_e('Update Assignment', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="incident-footer">
            <button type="button" class="button" onclick="document.getElementById('incident-modal').style.display='none'">
                <?php esc_html_e('Close', 'wp-gpt-rag-chat'); ?>
            </button>
        </div>
    `;
    
    // Set current assignment
    setCurrentAssignment(incident);
    
    // Add event listener for status update form
    document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateIncidentStatus(incident.id);
    });
}

function updateIncidentStatus(incidentId) {
    const newStatus = document.getElementById('newStatus').value;
    const adminNotes = document.getElementById('adminNotes').value;
    const submitBtn = document.querySelector('#statusUpdateForm button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = '<?php echo esc_js(__('Updating...', 'wp-gpt-rag-chat')); ?>';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'update_incident_status');
    formData.append('incident_id', incidentId);
    formData.append('new_status', newStatus);
    formData.append('admin_notes', adminNotes);
    formData.append('nonce', '<?php echo wp_create_nonce('update_incident_status'); ?>');
    
    // Submit via AJAX
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('<?php echo esc_js(__('Status updated successfully', 'wp-gpt-rag-chat')); ?>');
            document.getElementById('incident-modal').style.display = 'none';
            // Reload the page to show updated status
            location.reload();
        } else {
            alert('<?php echo esc_js(__('Failed to update status', 'wp-gpt-rag-chat')); ?>: ' + (data.data || ''));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?php echo esc_js(__('Failed to update status', 'wp-gpt-rag-chat')); ?>');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

// Handle assignment form submission
document.addEventListener('submit', function(e) {
    if (e.target.id === 'assignmentForm') {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const modal = document.getElementById('incident-modal');
        const incidentId = modal.dataset.currentIncidentId;
        const assignedTo = formData.get('assigned_to');
        
        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '<?php esc_js(__('Updating...', 'wp-gpt-rag-chat')); ?>';
        
        // Prepare AJAX data
        const ajaxData = new FormData();
        ajaxData.append('action', 'assign_incident');
        ajaxData.append('incident_id', incidentId);
        ajaxData.append('assigned_to', assignedTo);
        ajaxData.append('nonce', '<?php echo wp_create_nonce('incident_assignment_nonce'); ?>');
        
        // Submit via AJAX
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: ajaxData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the modal display
                const assignedDisplay = document.getElementById('assigned-user-display');
                if (data.data.assigned_user) {
                    assignedDisplay.textContent = data.data.assigned_user.name;
                    assignedDisplay.style.background = '#e7f3ff';
                    assignedDisplay.style.color = '#0066cc';
                } else {
                    assignedDisplay.textContent = '<?php esc_js(__('Unassigned', 'wp-gpt-rag-chat')); ?>';
                    assignedDisplay.style.background = '#f8f8f8';
                    assignedDisplay.style.color = '#999';
                }
                
                // Update the table row
                updateTableRowAssignment(incidentId, data.data.assigned_user);
                
                // Show success modal
                const successModal = document.getElementById('assignment-success-modal');
                if (successModal) {
                    successModal.style.display = 'block';
                }
            } else {
                alert('<?php echo esc_js(__('Failed to update assignment', 'wp-gpt-rag-chat')); ?>: ' + (data.data || ''));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo esc_js(__('Failed to update assignment', 'wp-gpt-rag-chat')); ?>');
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    }
});

// Handle report generation button
document.addEventListener('click', function(e) {
    if (e.target.id === 'generate-report-btn') {
        generateFullReport();
    }
});

// Generate full report function
function generateFullReport() {
    const button = document.getElementById('generate-report-btn');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<span class="dashicons dashicons-update" style="vertical-align: middle; margin-right: 5px; animation: spin 1s linear infinite;"></span><?php esc_js(__('Generating Report...', 'wp-gpt-rag-chat')); ?>';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'generate_incident_report');
    formData.append('nonce', '<?php echo wp_create_nonce('generate_incident_report'); ?>');
    
    // Submit via AJAX
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create download link with proper UTF-8 headers
            const downloadLink = document.createElement('a');
            downloadLink.href = data.data.download_url;
            downloadLink.download = data.data.filename;
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            
            // Show success modal
            const reportSuccessModal = document.getElementById('report-success-modal');
            if (reportSuccessModal) {
                reportSuccessModal.style.display = 'block';
            }
        } else {
            alert('<?php echo esc_js(__('Failed to generate report', 'wp-gpt-rag-chat')); ?>: ' + (data.data || ''));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?php echo esc_js(__('Failed to generate report', 'wp-gpt-rag-chat')); ?>');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Set current assignment when modal opens
function setCurrentAssignment(incident) {
    const assignSelect = document.getElementById('assignTo');
    if (assignSelect && incident.assigned_to) {
        assignSelect.value = incident.assigned_to;
    } else if (assignSelect) {
        assignSelect.value = '';
    }
}

// Update table row assignment display
function updateTableRowAssignment(incidentId, assignedUser) {
    // Find the table row for this incident
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        const firstCell = row.querySelector('td:first-child');
        if (firstCell && firstCell.textContent.trim() === incidentId.toString()) {
            // Find the assignment column (6th column - after ID, Type, Description, User, Status, Assign To)
            const assignmentCell = row.querySelector('td:nth-child(6)');
            if (assignmentCell) {
                if (assignedUser) {
                    assignmentCell.innerHTML = `
                        <span class="assigned-user" style="background: #e7f3ff; color: #0066cc; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                            ${assignedUser.name}
                        </span>
                    `;
                } else {
                    assignmentCell.innerHTML = `
                        <span class="unassigned" style="background: #f8f8f8; color: #999; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                            <?php esc_html_e('Unassigned', 'wp-gpt-rag-chat'); ?>
                        </span>
                    `;
                }
            }
        }
    });
}

// Assignment Success Modal Event Handlers
document.addEventListener('DOMContentLoaded', function() {
    const assignmentSuccessModal = document.getElementById('assignment-success-modal');
    const closeAssignmentSuccessModalBtn = document.getElementById('closeAssignmentSuccessModalBtn');

    // Close modal when OK button is clicked
    if (closeAssignmentSuccessModalBtn) {
        closeAssignmentSuccessModalBtn.addEventListener('click', function() {
            assignmentSuccessModal.style.display = 'none';
        });
    }

    // Close modal when clicking outside the modal content
    if (assignmentSuccessModal) {
        assignmentSuccessModal.addEventListener('click', function(e) {
            if (e.target === assignmentSuccessModal) {
                assignmentSuccessModal.style.display = 'none';
            }
        });
    }
});

// Report Success Modal Event Handlers
document.addEventListener('DOMContentLoaded', function() {
    const reportSuccessModal = document.getElementById('report-success-modal');
    const closeReportSuccessModalBtn = document.getElementById('closeReportSuccessModalBtn');

    // Close modal when OK button is clicked
    if (closeReportSuccessModalBtn) {
        closeReportSuccessModalBtn.addEventListener('click', function() {
            reportSuccessModal.style.display = 'none';
        });
    }

    // Close modal when clicking outside the modal content
    if (reportSuccessModal) {
        reportSuccessModal.addEventListener('click', function(e) {
            if (e.target === reportSuccessModal) {
                reportSuccessModal.style.display = 'none';
            }
        });
    }
});

</script>

<style>
/* Status colors */
.status-pending { background: #f0ad4e !important; }
.status-in_progress { background: #5bc0de !important; }
.status-resolved { background: #5cb85c !important; }

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#generate-report-btn:hover {
    background: #005a87 !important;
    border-color: #005a87 !important;
}

/* Problem type tags styling */
.problem-type-tag {
    transition: all 0.2s ease;
}

.problem-type-tag:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Preloader animation */
.incident-preloader {
    animation: spin 1s linear infinite;
}

/* Modal improvements */
#incident-modal {
    backdrop-filter: blur(2px);
}

#incident-modal .modal-content {
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    border: 1px solid #e1e5e9;
    max-width: 1000px;
    width: 95%;
    max-height: 90vh;
    overflow-y: auto;
}

/* WordPress Admin Styling for Incident Details */
.incident-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0 15px 0;
    border-bottom: 1px solid #ccd0d4;
    margin-bottom: 20px;
}

.incident-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 400;
}

.incident-status .status-pending,
.incident-status .status-in_progress,
.incident-status .status-resolved {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
    color: white;
}

.incident-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

/* WordPress Postbox Styling */
.incident-content .postbox,
.incident-actions .postbox {
    margin: 0 0 15px 0;
    background: #fff;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.postbox-header {
    background: #f6f7f7;
    border-bottom: 1px solid #c3c4c7;
    padding: 0;
}

.postbox-header .hndle {
    margin: 0;
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    color: #1d2327;
    cursor: default;
}

.postbox .inside {
    margin: 0;
    padding: 12px;
}

/* Assignment Display */
.assignment-display.assigned {
    color: #00a32a;
    font-weight: 500;
}

.assignment-display.unassigned {
    color: #dba617;
    font-style: italic;
}

/* Description Text */
.description-text {
    line-height: 1.6;
    color: #1d2327;
    white-space: pre-wrap;
    font-size: 14px;
}

/* Admin Notes */
.admin-notes-text {
    line-height: 1.6;
    color: #1d2327;
    white-space: pre-wrap;
    font-size: 14px;
    background: #f0f6fc;
    padding: 12px;
    border-left: 4px solid #72aee6;
    margin: 0;
}

/* WordPress Notice Styling */
.notice {
    background: #fff;
    border-left: 4px solid #ffb900;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    margin: 5px 0 15px;
    padding: 1px 12px;
}

.notice p {
    margin: 0.5em 0;
    padding: 2px;
}

.notice-warning {
    border-left-color: #ffb900;
}

.notice-warning p {
    color: #8a6914;
}

/* Action Forms - Now separate rows */
.incident-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

/* WordPress Form Styling */
.form-table {
    margin: 0;
}

.form-table th {
    width: 150px;
    padding: 10px 0;
    font-weight: 600;
    color: #1d2327;
    vertical-align: top;
}

.form-table td {
    padding: 10px 0;
}

.form-table th label {
    font-weight: 600;
    color: #1d2327;
}

.regular-text {
    width: 250px;
}

.large-text {
    width: 100%;
    max-width: 500px;
}

.submit {
    margin: 15px 0 0 0;
    padding: 0;
}

/* Footer */
.incident-footer {
    padding: 15px 0 0 0;
    border-top: 1px solid #c3c4c7;
    margin-top: 20px;
    text-align: right;
}

/* Responsive Design */
@media (max-width: 768px) {
    .incident-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    #incident-modal .modal-content {
        width: 98%;
        margin: 10px;
    }
    
    .regular-text {
        width: 100%;
    }
}
</style>

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
                    <?php foreach ($problem_types as $type): ?>
                        <option value="<?php echo esc_attr($type->problem_type); ?>" <?php selected($type_filter, $type->problem_type); ?>>
                            <?php echo esc_html($type->problem_type); ?>
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
                            <strong><?php echo esc_html($incident->problem_type); ?></strong>
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
        <div style="text-align: right; margin-top: 20px;">
            <button class="button" onclick="document.getElementById('incident-modal').style.display='none'">
                <?php esc_html_e('Close', 'wp-gpt-rag-chat'); ?>
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
    
    // Show loading state
    content.innerHTML = '<h3><?php esc_html_e('Incident Details', 'wp-gpt-rag-chat'); ?></h3><p><?php esc_html_e('Loading incident details...', 'wp-gpt-rag-chat'); ?></p>';
    modal.style.display = 'block';
    
    // Make AJAX call to get incident details
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_incident_details&incident_id=' + incidentId + '&nonce=<?php echo wp_create_nonce('get_incident_details'); ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayIncidentDetails(data.data);
        } else {
            content.innerHTML = '<h3><?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?></h3><p><?php esc_html_e('Failed to load incident details', 'wp-gpt-rag-chat'); ?></p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<h3><?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?></h3><p><?php esc_html_e('Failed to load incident details', 'wp-gpt-rag-chat'); ?></p>';
    });
}

function displayIncidentDetails(incident) {
    const content = document.getElementById('incident-modal-content');
    
    const statusClass = 'status-' + incident.status;
    const statusText = incident.status.charAt(0).toUpperCase() + incident.status.slice(1);
    
    content.innerHTML = `
        <h3><?php esc_html_e('Incident Details', 'wp-gpt-rag-chat'); ?></h3>
        <div style="margin-bottom: 20px;">
            <strong><?php esc_html_e('ID:', 'wp-gpt-rag-chat'); ?></strong> ${incident.id}<br>
            <strong><?php esc_html_e('Status:', 'wp-gpt-rag-chat'); ?></strong> 
            <span class="${statusClass}" style="padding: 4px 8px; border-radius: 3px; font-size: 12px; background: ${
                incident.status === 'pending' ? '#f0ad4e' : 
                (incident.status === 'in_progress' ? '#5bc0de' : '#5cb85c')
            }; color: white;">${statusText}</span><br>
            <strong><?php esc_html_e('Problem Type:', 'wp-gpt-rag-chat'); ?></strong> ${incident.problem_type}<br>
            <strong><?php esc_html_e('Date:', 'wp-gpt-rag-chat'); ?></strong> ${incident.created_at}<br>
            <strong><?php esc_html_e('User:', 'wp-gpt-rag-chat'); ?></strong> ${incident.user_email || '<?php esc_html_e('Guest', 'wp-gpt-rag-chat'); ?>'}<br>
            <strong><?php esc_html_e('IP Address:', 'wp-gpt-rag-chat'); ?></strong> ${incident.user_ip}<br>
            <strong><?php esc_html_e('Assigned To:', 'wp-gpt-rag-chat'); ?></strong> 
            <span id="assigned-user-display" style="background: ${incident.assigned_to ? '#e7f3ff' : '#f8f8f8'}; color: ${incident.assigned_to ? '#0066cc' : '#999'}; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                ${incident.assigned_to ? incident.assigned_user_name : '<?php esc_html_e('Unassigned', 'wp-gpt-rag-chat'); ?>'}
            </span>
        </div>
        
        <div style="margin-bottom: 20px;">
            <strong><?php esc_html_e('Problem Description:', 'wp-gpt-rag-chat'); ?></strong><br>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 5px; white-space: pre-wrap;">${incident.problem_description}</div>
        </div>
        
        ${incident.admin_notes ? `
        <div style="margin-bottom: 20px;">
            <strong><?php esc_html_e('Admin Notes:', 'wp-gpt-rag-chat'); ?></strong><br>
            <div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin-top: 5px; white-space: pre-wrap;">${incident.admin_notes}</div>
        </div>
        ` : ''}
        
        <!-- Status Change Section -->
        <div style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">
            <h4 style="margin-top: 0; color: #007cba;"><?php esc_html_e('Update Status', 'wp-gpt-rag-chat'); ?></h4>
            <form id="statusUpdateForm" style="margin-bottom: 15px;">
                <div style="margin-bottom: 15px;">
                    <label for="newStatus" style="display: block; margin-bottom: 5px; font-weight: 500;">
                        <?php esc_html_e('Change Status:', 'wp-gpt-rag-chat'); ?>
                    </label>
                    <select id="newStatus" name="new_status" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                        <option value="pending" ${incident.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="in_progress" ${incident.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                        <option value="resolved" ${incident.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="adminNotes" style="display: block; margin-bottom: 5px; font-weight: 500;">
                        <?php esc_html_e('Admin Notes:', 'wp-gpt-rag-chat'); ?>
                    </label>
                    <textarea id="adminNotes" name="admin_notes" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;" placeholder="<?php esc_attr_e('Add notes about this incident...', 'wp-gpt-rag-chat'); ?>">${incident.admin_notes || ''}</textarea>
                </div>
                <button type="submit" class="button button-primary" style="margin-right: 10px;">
                    <?php esc_html_e('Update Status', 'wp-gpt-rag-chat'); ?>
                </button>
                <button type="button" class="button" onclick="document.getElementById('incident-modal').style.display='none'">
                    <?php esc_html_e('Close', 'wp-gpt-rag-chat'); ?>
                </button>
            </form>
        </div>
        
        <!-- Assignment Section -->
        <div style="margin: 20px 0; padding: 20px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #0066cc;">
            <h4 style="margin-top: 0; color: #0066cc;"><?php esc_html_e('Assign Incident', 'wp-gpt-rag-chat'); ?></h4>
            <form id="assignmentForm" style="margin-bottom: 15px;">
                <div style="margin-bottom: 15px;">
                    <label for="assignTo" style="display: block; margin-bottom: 5px; font-weight: 500;">
                        <?php esc_html_e('Assign To:', 'wp-gpt-rag-chat'); ?>
                    </label>
                    <select id="assignTo" name="assigned_to" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px;">
                        <option value=""><?php esc_html_e('Unassigned', 'wp-gpt-rag-chat'); ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="button button-secondary" style="margin-right: 10px;">
                    <?php esc_html_e('Update Assignment', 'wp-gpt-rag-chat'); ?>
                </button>
            </form>
        </div>
        
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
            <strong><?php esc_html_e('User Agent:', 'wp-gpt-rag-chat'); ?></strong><br>
            <small style="color: #666; word-break: break-all;">${incident.user_agent}</small>
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
        const incidentId = document.querySelector('.view-incident[data-id]').dataset.id;
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
                
                // Show success message
                alert('<?php echo esc_js(__('Assignment updated successfully', 'wp-gpt-rag-chat')); ?>');
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
            // Create download link
            const downloadLink = document.createElement('a');
            downloadLink.href = data.data.report_url;
            downloadLink.download = data.data.filename;
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            
            // Show success message
            alert('<?php echo esc_js(__('Report generated successfully!', 'wp-gpt-rag-chat')); ?>');
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

</script>

<style>
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
</style>

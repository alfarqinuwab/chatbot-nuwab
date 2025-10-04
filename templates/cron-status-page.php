<?php
/**
 * Cron Status Page Template
 * 
 * Displays the current status of persistent indexing cron jobs.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check user capabilities
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Handle clear all cron jobs request
if (isset($_POST['clear_all_cron']) && check_admin_referer('wp_gpt_rag_chat_clear_cron', 'clear_cron_nonce')) {
    $our_hook = 'wp_gpt_rag_chat_process_indexing_batch';
    $cleared = 0;
    while ($timestamp = wp_next_scheduled($our_hook)) {
        wp_unschedule_event($timestamp, $our_hook);
        $cleared++;
        if ($cleared > 100) break; // Safety limit
    }
    wp_clear_scheduled_hook($our_hook);
    
    echo '<div class="notice notice-success is-dismissible"><p>';
    printf(__('✓ Successfully cleared %d scheduled cron jobs!', 'wp-gpt-rag-chat'), $cleared);
    echo '</p></div>';
}

// Get all scheduled cron events
$crons = _get_cron_array();

// Check for our specific hook
$our_hook = 'wp_gpt_rag_chat_process_indexing_batch';
$found_jobs = [];

if ($crons) {
    foreach ($crons as $timestamp => $cron) {
        if (isset($cron[$our_hook])) {
            foreach ($cron[$our_hook] as $key => $job) {
                $found_jobs[] = [
                    'timestamp' => $timestamp,
                    'time' => date('Y-m-d H:i:s', $timestamp),
                    'key' => $key,
                    'job' => $job
                ];
            }
        }
    }
}

// Get indexing state
$state = get_transient('wp_gpt_rag_chat_indexing_state');

// Get all plugin-related cron hooks
$plugin_hooks = [
    'wp_gpt_rag_chat_process_indexing_batch',
    'wp_gpt_rag_chat_cleanup_indexing_state',
    'wp_gpt_rag_chat_auto_index_posts'
];
?>

<div class="wrap cornuwab-admin-wrap">
    <h1><?php _e('WP-Cron Status - Persistent Indexing', 'wp-gpt-rag-chat'); ?></h1>
    
    <style>
        .cron-status-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .cron-status-box h2 {
            margin-top: 0;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        .cron-status-box.success {
            border-left: 4px solid #46b450;
        }
        .cron-status-box.warning {
            border-left: 4px solid #ffb900;
        }
        .cron-status-box.error {
            border-left: 4px solid #dc3232;
        }
        .cron-status-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .cron-status-table th,
        .cron-status-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .cron-status-table th {
            background-color: #0073aa;
            color: white;
            font-weight: 600;
        }
        .cron-status-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.running {
            background: #ffb900;
            color: #000;
        }
        .status-badge.completed {
            background: #46b450;
            color: white;
        }
        .status-badge.cancelled {
            background: #999;
            color: white;
        }
        .status-badge.error {
            background: #dc3232;
            color: white;
        }
        .status-badge.idle {
            background: #eee;
            color: #666;
        }
        .alert-text {
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 3px;
            font-weight: 600;
        }
        .alert-text.success {
            background: #d4edda;
            color: #155724;
        }
        .alert-text.warning {
            background: #fff3cd;
            color: #856404;
        }
        .alert-text.error {
            background: #f8d7da;
            color: #721c24;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
    
    <!-- Indexing State -->
    <div class="cron-status-box <?php echo $state ? ($state['status'] === 'running' ? 'warning' : '') : 'success'; ?>">
        <h2><?php _e('Current Indexing State', 'wp-gpt-rag-chat'); ?></h2>
        
        <?php if ($state): ?>
            <table class="cron-status-table">
                <tr>
                    <th><?php _e('Property', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php _e('Value', 'wp-gpt-rag-chat'); ?></th>
                </tr>
                <?php foreach ($state as $key => $value): ?>
                    <?php if ($key === 'status'): ?>
                        <tr>
                            <td><strong><?php echo esc_html($key); ?></strong></td>
                            <td><span class="status-badge <?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></span></td>
                        </tr>
                    <?php elseif ($key === 'newly_indexed'): ?>
                        <tr>
                            <td><strong><?php echo esc_html($key); ?></strong></td>
                            <td><?php echo is_array($value) ? count($value) . ' ' . __('items', 'wp-gpt-rag-chat') : esc_html($value); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td><strong><?php echo esc_html($key); ?></strong></td>
                            <td><?php echo esc_html(is_array($value) ? json_encode($value) : $value); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="alert-text success">✓ <?php _e('No active indexing state found.', 'wp-gpt-rag-chat'); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Scheduled Cron Jobs -->
    <div class="cron-status-box <?php echo count($found_jobs) > 0 ? 'warning' : 'success'; ?>">
        <h2><?php _e('Scheduled Indexing Batch Jobs', 'wp-gpt-rag-chat'); ?></h2>
        
        <?php if (count($found_jobs) > 0): ?>
            <p class="alert-text error">
                <strong><?php printf(__('⚠ WARNING: Found %d scheduled indexing batch jobs!', 'wp-gpt-rag-chat'), count($found_jobs)); ?></strong>
            </p>
            
            <table class="cron-status-table">
                <tr>
                    <th><?php _e('Timestamp', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php _e('Scheduled Time', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php _e('Time Until Execution', 'wp-gpt-rag-chat'); ?></th>
                </tr>
                <?php foreach ($found_jobs as $job): ?>
                    <?php $time_diff = $job['timestamp'] - time(); ?>
                    <tr>
                        <td><?php echo esc_html($job['timestamp']); ?></td>
                        <td><?php echo esc_html($job['time']); ?></td>
                        <td><?php echo $time_diff > 0 ? esc_html($time_diff) . ' ' . __('seconds', 'wp-gpt-rag-chat') : '<strong style="color:red;">' . __('PAST DUE', 'wp-gpt-rag-chat') . '</strong>'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('wp_gpt_rag_chat_clear_cron', 'clear_cron_nonce'); ?>
                <button type="submit" name="clear_all_cron" class="button button-primary button-large" 
                        onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all scheduled indexing jobs?', 'wp-gpt-rag-chat'); ?>');">
                    <?php _e('Clear All Cron Jobs', 'wp-gpt-rag-chat'); ?>
                </button>
            </form>
        <?php else: ?>
            <p class="alert-text success">✓ <?php _e('No scheduled indexing batch jobs found. Background processing is stopped.', 'wp-gpt-rag-chat'); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Next Scheduled Run -->
    <div class="cron-status-box">
        <h2><?php _e('Next Scheduled Batch', 'wp-gpt-rag-chat'); ?></h2>
        
        <?php $next_cron = wp_next_scheduled($our_hook); ?>
        <?php if ($next_cron): ?>
            <p><strong><?php _e('Next batch scheduled at:', 'wp-gpt-rag-chat'); ?></strong> <?php echo date('Y-m-d H:i:s', $next_cron); ?></p>
            <p><strong><?php _e('Time until next run:', 'wp-gpt-rag-chat'); ?></strong> <?php echo ($next_cron - time()); ?> <?php _e('seconds', 'wp-gpt-rag-chat'); ?></p>
        <?php else: ?>
            <p class="alert-text success">✓ <?php _e('No next run scheduled.', 'wp-gpt-rag-chat'); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- All Plugin Cron Hooks -->
    <div class="cron-status-box">
        <h2><?php _e('All Plugin Cron Hooks', 'wp-gpt-rag-chat'); ?></h2>
        
        <table class="cron-status-table">
            <tr>
                <th><?php _e('Hook Name', 'wp-gpt-rag-chat'); ?></th>
                <th><?php _e('Scheduled Count', 'wp-gpt-rag-chat'); ?></th>
                <th><?php _e('Next Run', 'wp-gpt-rag-chat'); ?></th>
            </tr>
            <?php foreach ($plugin_hooks as $hook): ?>
                <?php
                $count = 0;
                $next = wp_next_scheduled($hook);
                
                if ($crons) {
                    foreach ($crons as $timestamp => $cron) {
                        if (isset($cron[$hook])) {
                            $count += count($cron[$hook]);
                        }
                    }
                }
                ?>
                <tr>
                    <td><code><?php echo esc_html($hook); ?></code></td>
                    <td><?php echo esc_html($count); ?></td>
                    <td><?php echo $next ? date('Y-m-d H:i:s', $next) : __('Not scheduled', 'wp-gpt-rag-chat'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <p>
        <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-cron-status'); ?>" class="button button-secondary">
            <?php _e('Refresh Page', 'wp-gpt-rag-chat'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>" class="button button-secondary">
            <?php _e('Go to Indexing Page', 'wp-gpt-rag-chat'); ?>
        </a>
    </p>
    
    <p style="color: #666; font-size: 12px;">
        <strong><?php _e('Last Updated:', 'wp-gpt-rag-chat'); ?></strong> <?php echo date('Y-m-d H:i:s'); ?>
    </p>
</div>


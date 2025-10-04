<?php
/**
 * Check WP-Cron Status for Persistent Indexing
 * 
 * Run this file to see the current status of persistent indexing cron jobs.
 * Access it via: http://localhost/wp/wp-content/plugins/wp-nuwab-chatgpt/check-cron-status.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Unauthorized access');
}

echo "<h1>WP-Cron Status for Persistent Indexing</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .info-box { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; }
    .warning { background: #fff3cd; color: #856404; }
    .error { background: #f8d7da; color: #721c24; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    .button { background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 10px 5px 10px 0; }
    .button:hover { background: #005177; }
    .button.danger { background: #dc3232; }
    .button.danger:hover { background: #a00; }
</style>";

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

// Display indexing state
echo "<div class='info-box " . ($state ? ($state['status'] === 'running' ? 'warning' : 'info-box') : 'success') . "'>";
echo "<h2>Indexing State</h2>";
if ($state) {
    echo "<table>";
    echo "<tr><th>Property</th><th>Value</th></tr>";
    foreach ($state as $key => $value) {
        if ($key === 'newly_indexed') {
            echo "<tr><td><strong>" . esc_html($key) . "</strong></td><td>" . count($value) . " items</td></tr>";
        } else {
            echo "<tr><td><strong>" . esc_html($key) . "</strong></td><td>" . esc_html(is_array($value) ? json_encode($value) : $value) . "</td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p><strong>No active indexing state found.</strong></p>";
}
echo "</div>";

// Display cron jobs
echo "<div class='info-box " . (count($found_jobs) > 0 ? 'warning' : 'success') . "'>";
echo "<h2>Scheduled Cron Jobs</h2>";
if (count($found_jobs) > 0) {
    echo "<p><strong style='color:red;'>WARNING: Found " . count($found_jobs) . " scheduled indexing batch jobs!</strong></p>";
    echo "<table>";
    echo "<tr><th>Timestamp</th><th>Scheduled Time</th><th>Time Until Execution</th></tr>";
    foreach ($found_jobs as $job) {
        $time_diff = $job['timestamp'] - time();
        echo "<tr>";
        echo "<td>" . $job['timestamp'] . "</td>";
        echo "<td>" . $job['time'] . "</td>";
        echo "<td>" . ($time_diff > 0 ? $time_diff . " seconds" : "PAST DUE") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><a href='?clear_all=1' class='button danger' onclick='return confirm(\"Are you sure you want to clear all scheduled indexing jobs?\");'>Clear All Cron Jobs</a></p>";
} else {
    echo "<p><strong style='color:green;'>✓ No scheduled indexing batch jobs found. Background processing is stopped.</strong></p>";
}
echo "</div>";

// Handle clear all request
if (isset($_GET['clear_all']) && $_GET['clear_all'] == '1') {
    $cleared = 0;
    while ($timestamp = wp_next_scheduled($our_hook)) {
        wp_unschedule_event($timestamp, $our_hook);
        $cleared++;
        if ($cleared > 100) break; // Safety limit
    }
    wp_clear_scheduled_hook($our_hook);
    
    echo "<div class='info-box success'>";
    echo "<h2>Action Result</h2>";
    echo "<p><strong>✓ Cleared " . $cleared . " scheduled cron jobs!</strong></p>";
    echo "<p><a href='?' class='button'>Refresh Page</a></p>";
    echo "</div>";
}

// Display next scheduled cron run
$next_cron = wp_next_scheduled($our_hook);
echo "<div class='info-box'>";
echo "<h2>Next Scheduled Run</h2>";
if ($next_cron) {
    echo "<p><strong>Next batch scheduled at:</strong> " . date('Y-m-d H:i:s', $next_cron) . "</p>";
    echo "<p><strong>Time until next run:</strong> " . ($next_cron - time()) . " seconds</p>";
} else {
    echo "<p><strong style='color:green;'>✓ No next run scheduled.</strong></p>";
}
echo "</div>";

// Display all cron hooks
echo "<div class='info-box'>";
echo "<h2>All WP-Cron Hooks (Related to Plugin)</h2>";
$plugin_hooks = [
    'wp_gpt_rag_chat_process_indexing_batch',
    'wp_gpt_rag_chat_cleanup_indexing_state',
    'wp_gpt_rag_chat_auto_index_posts'
];

echo "<table>";
echo "<tr><th>Hook Name</th><th>Scheduled Count</th><th>Next Run</th></tr>";
foreach ($plugin_hooks as $hook) {
    $count = 0;
    $next = wp_next_scheduled($hook);
    
    if ($crons) {
        foreach ($crons as $timestamp => $cron) {
            if (isset($cron[$hook])) {
                $count += count($cron[$hook]);
            }
        }
    }
    
    echo "<tr>";
    echo "<td><code>" . esc_html($hook) . "</code></td>";
    echo "<td>" . $count . "</td>";
    echo "<td>" . ($next ? date('Y-m-d H:i:s', $next) : 'Not scheduled') . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<p><a href='?' class='button'>Refresh Page</a></p>";
echo "<p><small>Generated at: " . date('Y-m-d H:i:s') . "</small></p>";


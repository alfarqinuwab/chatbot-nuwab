<?php
/**
 * Custom Tables Indexing page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get user role display
$user_role_display = \WP_GPT_RAG_Chat\RBAC::get_user_role_display();

// Get custom tables
global $wpdb;
$custom_tables = [
    'committee_achievement',
    'member_achievement_bp_topics', 
    'member_achievement_ipg_nuwab',
    'member_achievement_prop_topics',
    'member_achievement_ques_topics',
    'minister_details',
    'mp_detail',
    'sitting_agenda',
    'sitting_attachment',
    'topics_agreements',
    'topics_bills',
    'topics_billproposals',
    'topics_decrees',
    'topics_generaltopics',
    'topics_interrogation',
    'topics_investigation',
    'topics_proposal',
    'topics_questions'
];

// Get table structures and counts
$table_structures = [];
foreach ($custom_tables as $table_name) {
    // Try with prefix first, then without prefix
    $full_table_name = $wpdb->prefix . $table_name;
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
    
    if (!$table_exists) {
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists) {
            $full_table_name = $table_name; // Use table name without prefix
        }
    }
    
    if ($table_exists) {
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM `$full_table_name`");
        $indexed_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM `{$wpdb->prefix}wp_gpt_rag_chat_vectors` WHERE metadata LIKE %s",
            '%"table_name":"' . $table_name . '"%'
        ));
        
        $table_structures[$table_name] = [
            'total_count' => $total_count,
            'indexed_count' => $indexed_count,
            'progress' => $total_count > 0 ? round(($indexed_count / $total_count) * 100) : 0
        ];
    }
}

// Calculate total stats
$total_tables = count($table_structures);
$total_records = array_sum(array_column($table_structures, 'total_count'));
$total_indexed = array_sum(array_column($table_structures, 'indexed_count'));
$overall_progress = $total_records > 0 ? round(($total_indexed / $total_records) * 100) : 0;
?>

<div class="wrap cornuwab-admin-wrap">
    <h1>
        <?php esc_html_e('Nuwab AI Assistant - Custom Tables Indexing', 'wp-gpt-rag-chat'); ?>
    </h1>
    
    <!-- Emergency Stop Button (Shows when indexing is active) -->
    <div id="emergency-stop-notice" style="background: #d63638; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center; display: none;">
        <h2 style="color: white; margin: 0 0 10px 0;">⚠️ <?php esc_html_e('Indexing Controls', 'wp-gpt-rag-chat'); ?></h2>
        <p style="margin: 0 0 10px 0;" id="emergency-status-text"><?php esc_html_e('No indexing in progress. Use the controls below to start indexing.', 'wp-gpt-rag-chat'); ?></p>
        <button type="button" id="emergency-stop-btn" class="button button-primary" style="background: white; color: #d63638; border: 2px solid white; font-weight: bold; font-size: 16px; padding: 10px 30px; display: none;">
            <span class="dashicons dashicons-no" style="margin-top: 4px; font-size: 20px;"></span>
            <?php esc_html_e('STOP ALL INDEXING NOW', 'wp-gpt-rag-chat'); ?>
        </button>
        <div id="emergency-progress-info" style="display: none; margin-top: 10px; font-size: 18px; font-weight: bold;">
            <span id="emergency-progress-text"></span>
        </div>
    </div>
    
    <div class="indexing-page-container">
    
    <div class="wp-gpt-rag-chat-stats">
        <div class="stats-grid">
            <div class="stat-card cornuwb-stat-card">
                <h3><?php esc_html_e('Total Tables', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number" id="cornuwb-stat-tables"><?php echo esc_html(number_format($total_tables)); ?></div>
                <div class="cornuwb-stat-loading" style="display: none;">
                    <span class="dashicons dashicons-update cornuwb-rotate"></span>
                </div>
            </div>
            <div class="stat-card cornuwb-stat-card">
                <h3><?php esc_html_e('Total Records', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number" id="cornuwb-stat-records"><?php echo esc_html(number_format($total_records)); ?></div>
                <div class="cornuwb-stat-loading" style="display: none;">
                    <span class="dashicons dashicons-update cornuwb-rotate"></span>
                </div>
            </div>
            <div class="stat-card cornuwb-stat-card">
                <h3><?php esc_html_e('Indexed Records', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number" id="cornuwb-stat-indexed"><?php echo esc_html(number_format($total_indexed)); ?></div>
                <div class="cornuwb-stat-loading" style="display: none;">
                    <span class="dashicons dashicons-update cornuwb-rotate"></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($table_structures)): ?>
        <div class="post-type-breakdown">
            <h3><?php esc_html_e('By Table', 'wp-gpt-rag-chat'); ?></h3>
            <ul>
                <?php foreach ($table_structures as $table_name => $stats): ?>
                <li>
                    <strong><?php echo esc_html($table_name); ?>:</strong>
                    <?php echo esc_html(number_format($stats['indexed_count'])); ?> / <?php echo esc_html(number_format($stats['total_count'])); ?> 
                    (<?php echo esc_html($stats['progress']); ?>%)
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Global Progress Bar (full width above all content) -->
    <div id="global-progress-container" style="display: none; margin-bottom: 20px;">
        <div class="progress-section">
            <div class="progress-header">
                <h3><?php esc_html_e('Indexing Progress', 'wp-gpt-rag-chat'); ?></h3>
                <button type="button" id="stop-indexing" class="button button-secondary" style="display: none; color: #d63638;">
                    <?php esc_html_e('Stop', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-text"><?php esc_html_e('Preparing...', 'wp-gpt-rag-chat'); ?></div>
        </div>
    </div>
    
    <!-- Manual Re-indexing Search Section -->
    <div class="wp-gpt-rag-chat-manual-search">
        <div class="manual-search-header">
            <div class="search-title-section">
                <h2>
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e('Manual Re-indexing', 'wp-gpt-rag-chat'); ?>
                </h2>
                <p><?php esc_html_e('Search for specific records by ID or title to check their indexing status and re-index them individually.', 'wp-gpt-rag-chat'); ?></p>
            </div>
            <div class="search-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html(number_format($total_indexed)); ?></span>
                    <span class="stat-label"><?php esc_html_e('Indexed Records', 'wp-gpt-rag-chat'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="manual-search-form">
            <div class="search-input-group">
                <div class="search-input-wrapper">
                    <label for="manual-search-input" class="search-label">
                        <span class="dashicons dashicons-admin-post"></span>
                        <?php esc_html_e('Search Records', 'wp-gpt-rag-chat'); ?>
                    </label>
                    <input type="text" id="manual-search-input" placeholder="<?php esc_attr_e('Enter record ID or search by title...', 'wp-gpt-rag-chat'); ?>" class="search-input" />
                </div>
                
                <div class="search-filter-wrapper">
                    <label for="manual-search-table" class="search-label">
                        <span class="dashicons dashicons-category"></span>
                        <?php esc_html_e('Table', 'wp-gpt-rag-chat'); ?>
                    </label>
                    <select id="manual-search-table" class="search-post-type">
                        <option value="any"><?php esc_html_e('All Tables', 'wp-gpt-rag-chat'); ?></option>
                        <?php foreach ($table_structures as $table_name => $stats): ?>
                        <option value="<?php echo esc_attr($table_name); ?>" data-count="<?php echo esc_attr($stats['total_count']); ?>">
                            <?php echo esc_html($table_name); ?> (<?php echo esc_html($stats['total_count']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-button-wrapper">
                    <button type="button" id="manual-search-btn" class="search-button">
                        <span class="dashicons dashicons-search"></span>
                        <span class="button-text"><?php esc_html_e('Search', 'wp-gpt-rag-chat'); ?></span>
                    </button>
                </div>
            </div>
            
            <div class="search-options">
                <div class="search-option">
                    <input type="checkbox" id="search-include-drafts" class="search-checkbox">
                    <label for="search-include-drafts"><?php esc_html_e('Include Empty Records', 'wp-gpt-rag-chat'); ?></label>
                </div>
                <div class="search-option">
                    <input type="checkbox" id="search-exact-match" class="search-checkbox">
                    <label for="search-exact-match"><?php esc_html_e('Exact Match Only', 'wp-gpt-rag-chat'); ?></label>
                </div>
            </div>
        </div>
        
        <div id="manual-search-results" class="search-results" style="display: none;">
            <div class="search-results-header">
                <h3>
                    <span class="dashicons dashicons-list-view"></span>
                    <?php esc_html_e('Search Results', 'wp-gpt-rag-chat'); ?>
                </h3>
                <div class="results-actions">
                    <button type="button" id="clear-search-results" class="button button-secondary">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e('Clear Results', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
            </div>
            <div class="search-results-list"></div>
        </div>
    </div>
    
    <div class="indexing-layout">
        <div class="main-content-area">
            <div class="wp-gpt-rag-chat-indexed-items">
            <div class="indexed-items-header">
                <h2>
                    <?php esc_html_e('Custom Table Records', 'wp-gpt-rag-chat'); ?>
                    <span id="indexed-items-table-count" style="font-size: 16px; font-weight: normal; color: #646970; margin-left: 10px; padding: 3px 10px; background: #f0f0f0; border-radius: 4px;">
                        (<span id="indexed-items-table-number" style="color: #2271b1; font-weight: 600;"><?php echo esc_html(number_format($total_indexed)); ?></span>)
                    </span>
                </h2>
                <div class="header-actions">
                    <button type="button" class="button button-secondary" id="select-all-items">
                        <span class="wpgrc-label"><?php esc_html_e('Select All', 'wp-gpt-rag-chat'); ?></span>
                    </button>
                    <button type="button" class="button button-primary" id="bulk-reindex-selected">
                        <?php esc_html_e('Reindex Selected', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
            </div>
            
            <?php
            // Table will be populated via AJAX with server-side pagination
            ?>
            
            <div class="indexed-items-table-container">
                <table class="indexed-items-table">
                    <thead>
                        <tr>
                            <th class="checkbox-column">
                                <input type="checkbox" id="select-all-checkbox" />
                            </th>
                            <th class="status-column"><?php esc_html_e('Status', 'wp-gpt-rag-chat'); ?></th>
                            <th class="title-column"><?php esc_html_e('Title / Subject', 'wp-gpt-rag-chat'); ?></th>
                            <th class="ref-column"><?php esc_html_e('Table', 'wp-gpt-rag-chat'); ?></th>
                            <th class="updated-column"><?php esc_html_e('Updated', 'wp-gpt-rag-chat'); ?></th>
                            <th class="actions-column"><?php esc_html_e('Actions', 'wp-gpt-rag-chat'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table content will be loaded via AJAX with server-side pagination -->
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <span class="dashicons dashicons-update cornuwb-rotate" style="font-size: 24px; color: #2271b1;"></span>
                                <p style="margin-top: 10px; color: #646970;"><?php esc_html_e('Loading custom table records...', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Server-side pagination will be inserted here by JavaScript -->
            </div>
        </div>
        
        <div class="sidebar-area">
            <div class="wp-gpt-rag-chat-bulk-actions">
                <h2><?php esc_html_e('Bulk Actions', 'wp-gpt-rag-chat'); ?></h2>
                
                <div class="bulk-action-section">
                    <h3><?php esc_html_e('Index All Custom Tables', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('Index all records from all custom tables in batches of 5 records.', 'wp-gpt-rag-chat'); ?></p>
                        
                        <div class="post-type-selector">
                            <label for="index-table-type"><?php esc_html_e('Select Table:', 'wp-gpt-rag-chat'); ?></label>
                            <select id="index-table-type" class="post-type-dropdown">
                                <option value="all" data-count="<?php echo esc_attr($total_records); ?>"><?php esc_html_e('All Tables', 'wp-gpt-rag-chat'); ?> (<span id="all-table-count"><?php echo esc_html($total_records); ?></span>)</option>
                                <?php foreach ($table_structures as $table_name => $stats): ?>
                                <option value="<?php echo esc_attr($table_name); ?>" data-count="<?php echo esc_attr($stats['total_count']); ?>" data-table-name="<?php echo esc_attr($table_name); ?>">
                                    <?php echo esc_html($table_name); ?> (<?php echo esc_html($stats['total_count']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="sync-buttons">
                            <button type="button" id="sync-all-content" class="button button-primary">
                                <?php esc_html_e('Sync All', 'wp-gpt-rag-chat'); ?>
                </button>
                            <button type="button" id="sync-single-post" class="button button-secondary">
                                <?php esc_html_e('Sync Only One Record', 'wp-gpt-rag-chat'); ?>
                            </button>
                            <button type="button" id="cancel-sync-all" class="button button-secondary" style="display: none; background: #d63638; color: white; border-color: #d63638;">
                                <span class="dashicons dashicons-no" style="margin-top: 3px;"></span>
                                <?php esc_html_e('Cancel', 'wp-gpt-rag-chat'); ?>
                            </button>
                        </div>
                        
                        <div id="sync-all-progress" style="display: none; margin-top: 15px;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <div class="progress-text">
                                <span id="sync-progress-message"><?php esc_html_e('Preparing...', 'wp-gpt-rag-chat'); ?></span>
                                <span id="sync-progress-stats" style="float: right;"></span>
                            </div>
                        </div>
                </div>
                
                <div class="bulk-action-section">
                    <h3><?php esc_html_e('Reindex Changed Records', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('Reindex only records that have been modified since last indexing.', 'wp-gpt-rag-chat'); ?></p>
                    <button type="button" id="reindex-changed" class="button button-secondary">
                        <?php esc_html_e('Reindex Changed Records', 'wp-gpt-rag-chat'); ?>
                    </button>
                    <div id="reindex-changed-progress" style="display: none;">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <div class="progress-text"><?php esc_html_e('Preparing...', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
                
                <div class="bulk-action-section">
                    <h3><?php esc_html_e('Clear All Indexes', 'wp-gpt-rag-chat'); ?></h3>
                    <p><?php esc_html_e('Remove all indexed records from all custom tables. This action cannot be undone.', 'wp-gpt-rag-chat'); ?></p>
                    <button type="button" id="clear-all-indexes" class="button button-secondary" style="background: #d63638; color: white; border-color: #d63638;">
                        <?php esc_html_e('Clear All Indexes', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    </div>
    </div>
</div>

<style>
/* Copy all CSS from indexing-page.php */
.cornuwab-admin-wrap {
    max-width: none;
    margin: 0;
    width: 100%;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
    font-weight: 600;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #2271b1;
    margin: 10px 0;
}

.post-type-breakdown {
    background: #f8f9fa;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.post-type-breakdown h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #1d2327;
}

.post-type-breakdown ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.post-type-breakdown li {
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.post-type-breakdown li:last-child {
    border-bottom: none;
}

.progress-section {
    background: #f8f9fa;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 20px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.progress-header h3 {
    margin: 0;
    font-size: 18px;
    color: #1d2327;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #2271b1, #135e96);
    border-radius: 10px;
    transition: width 0.3s ease;
    width: 0%;
}

.progress-text {
    font-size: 14px;
    color: #666;
    text-align: center;
}

.wp-gpt-rag-chat-manual-search {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.manual-search-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
}

.search-title-section h2 {
    margin: 0 0 10px 0;
    font-size: 20px;
    color: #1d2327;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-title-section p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.search-stats {
    background: #f8f9fa;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 15px;
    text-align: center;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #2271b1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    font-weight: 600;
}

.manual-search-form {
    margin-bottom: 20px;
}

.search-input-group {
    display: flex;
    gap: 15px;
    align-items: end;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.search-input-wrapper,
.search-filter-wrapper {
    flex: 1;
    min-width: 200px;
}

.search-button-wrapper {
    flex-shrink: 0;
}

.search-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.search-input,
.search-post-type {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
}

.search-input:focus,
.search-post-type:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}

.search-button {
    background: #2271b1;
    color: white;
    border: 1px solid #2271b1;
    border-radius: 4px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.15s ease;
}

.search-button:hover {
    background: #135e96;
    border-color: #135e96;
}

.search-options {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.search-option {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-checkbox {
    margin: 0;
}

.search-option label {
    font-size: 14px;
    color: #1d2327;
    cursor: pointer;
}

.search-results {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #f8f9fa;
    margin-top: 20px;
}

.search-results-header {
    background: #fff;
    border-bottom: 1px solid #d1d5db;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.search-results-header h3 {
    margin: 0;
    font-size: 16px;
    color: #1d2327;
    display: flex;
    align-items: center;
    gap: 8px;
}

.results-actions {
    display: flex;
    gap: 10px;
}

.search-results-list {
    padding: 20px;
}

.indexed-items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #d1d5db;
}

.indexed-items-header h2 {
    margin: 0;
    font-size: 20px;
    color: #1d2327;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.indexed-items-table-container {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    overflow: hidden;
}

.indexed-items-table {
    width: 100%;
    border-collapse: collapse;
}

.indexed-items-table th,
.indexed-items-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.indexed-items-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.indexed-items-table tr:hover {
    background: #f9fafb;
}

.checkbox-column {
    width: 40px;
}

.status-column {
    width: 100px;
}

.title-column {
    min-width: 300px;
}

.ref-column {
    width: 150px;
}

.updated-column {
    width: 120px;
}

.actions-column {
    width: 120px;
}

.bulk-action-section {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.bulk-action-section h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    color: #1d2327;
}

.bulk-action-section p {
    margin: 0 0 20px 0;
    color: #666;
    font-size: 14px;
}

.post-type-selector {
    margin-bottom: 20px;
}

.post-type-selector label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #1d2327;
}

.post-type-dropdown {
    width: 100%;
    max-width: 400px;
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
}

.sync-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.cornuwb-rotate {
    animation: cornuwb-rotate 1s linear infinite;
}

@keyframes cornuwb-rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.cornuwb-stat-loading {
    display: none;
}

.cornuwb-stat-loading.show {
    display: block;
}

.cornuwb-stat-loading .dashicons {
    font-size: 20px;
    color: #2271b1;
}

/* Indexing Layout - Exact match with main indexing page */
.indexing-layout {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    max-width: 100%;
    overflow-x: hidden;
}

.main-content-area {
    flex: 1;
    min-width: 0;
}

.sidebar-area {
    flex: 0 0 331px;
    min-width: 331px;
}

.wp-gpt-rag-chat-indexed-items {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.indexed-items-header {
    background: #f8f9fa;
    border-bottom: 1px solid #ccd0d4;
    padding: 20px;
    margin: 0;
}

.indexed-items-table-container {
    background: #fff;
    overflow: hidden;
}

.wp-gpt-rag-chat-bulk-actions {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 15px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    height: fit-content;
}

.wp-gpt-rag-chat-bulk-actions h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #1d2327;
    font-size: 18px;
}

.bulk-action-section {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 15px;
}

.bulk-action-section h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #1d2327;
    font-size: 14px;
}

.bulk-action-section p {
    color: #646970;
    margin-bottom: 12px;
    font-size: 13px;
    line-height: 1.4;
}

.post-type-selector {
    margin-bottom: 15px;
}

.post-type-selector label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #1d2327;
    font-size: 13px;
}

.post-type-dropdown {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
    font-size: 13px;
    background: #fff;
}

.sync-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sync-buttons .button {
    width: 100%;
    text-align: center;
    justify-content: center;
    font-size: 13px;
    padding: 6px 12px;
}

/* Responsive Design - Exact match with main indexing page */
@media (max-width: 1400px) {
    .indexing-layout {
        flex-direction: column;
        gap: 20px;
    }
    
    .main-content-area {
        flex: none;
        min-width: auto;
    }
    
    .sidebar-area {
        flex: none;
        min-width: auto;
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    .indexing-layout {
        margin-top: 20px;
        gap: 15px;
    }
    
    .wp-gpt-rag-chat-bulk-actions {
        padding: 12px;
    }
    
    .bulk-action-section {
        padding: 12px;
        margin-bottom: 12px;
    }
    
    .bulk-action-section h3 {
        font-size: 13px;
    }
    
    .bulk-action-section p {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .bulk-action-section {
        padding: 10px;
    }
    
    .bulk-action-section h3 {
        font-size: 13px;
    }
    
    .bulk-action-section p {
        font-size: 12px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let isIndexing = false;
    let currentTable = '';
    let currentPage = 1;
    const recordsPerPage = 10;
    let totalRecords = 0;
    let totalPages = 0;
    
    // Initialize the page
    loadIndexedItems();
    
    // Manual search functionality
    $('#manual-search-btn').on('click', function() {
        const searchTerm = $('#manual-search-input').val().trim();
        const tableName = $('#manual-search-table').val();
        
        if (!searchTerm) {
            alert('Please enter a search term');
            return;
        }
        
        searchRecords(searchTerm, tableName);
    });
    
    // Clear search results
    $('#clear-search-results').on('click', function() {
        $('#manual-search-results').hide();
        $('#manual-search-input').val('');
    });
    
    // Bulk actions
    $('#sync-all-content').on('click', function() {
        const tableName = $('#index-table-type').val();
        if (tableName === 'all') {
            if (confirm('Are you sure you want to index ALL custom tables? This may take a while.')) {
                startBulkIndexing('all');
            }
        } else {
            if (confirm('Are you sure you want to index all records from ' + tableName + '?')) {
                startBulkIndexing(tableName);
            }
        }
    });
    
    $('#sync-single-post').on('click', function() {
        const recordId = prompt('Enter record ID to index:');
        if (recordId && !isNaN(recordId)) {
            indexSingleRecord(recordId);
        }
    });
    
    $('#cancel-sync-all').on('click', function() {
        if (confirm('Are you sure you want to cancel indexing?')) {
            cancelIndexing();
        }
    });
    
    $('#clear-all-indexes').on('click', function() {
        if (confirm('Are you sure you want to clear ALL custom table indexes? This cannot be undone.')) {
            clearAllIndexes();
        }
    });
    
    // Select all functionality
    $('#select-all-checkbox, #select-all-items').on('click', function() {
        const isChecked = $('#select-all-checkbox').is(':checked');
        $('.record-checkbox').prop('checked', isChecked);
        updateBulkActions();
    });
    
    // Bulk reindex selected
    $('#bulk-reindex-selected').on('click', function() {
        const selectedIds = $('.record-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedIds.length === 0) {
            alert('Please select records to reindex');
            return;
        }
        
        if (confirm('Are you sure you want to reindex ' + selectedIds.length + ' selected records?')) {
            reindexSelected(selectedIds);
        }
    });
    
    function loadIndexedItems(page = 1) {
        currentPage = page;
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_get_custom_table_records',
            page: page,
            per_page: recordsPerPage,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                displayIndexedItems(response.data.items);
                displayPagination(response.data.pagination);
                updateStats(response.data.stats);
            } else {
                console.error('Error loading indexed items:', response.data.message);
            }
        }).fail(function() {
            console.error('Network error loading indexed items');
        });
    }
    
    function displayIndexedItems(items) {
        let html = '';
        
        if (items.length === 0) {
            html = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #666;">No records found</td></tr>';
        } else {
            items.forEach(function(item) {
                const statusClass = item.is_indexed ? 'indexed' : 'not-indexed';
                const statusText = item.is_indexed ? 'Indexed' : 'Not Indexed';
                
                html += '<tr>';
                html += '<td><input type="checkbox" class="record-checkbox" value="' + item.id + '" /></td>';
                html += '<td><span class="status-badge ' + statusClass + '">' + statusText + '</span></td>';
                html += '<td>' + item.title + '</td>';
                html += '<td>' + item.table_name + '</td>';
                html += '<td>' + item.updated + '</td>';
                html += '<td>';
                if (item.is_indexed) {
                    html += '<button class="button button-secondary reindex-record" data-id="' + item.id + '">Re-index</button>';
                } else {
                    html += '<button class="button button-primary index-record" data-id="' + item.id + '">Index</button>';
                }
                html += '</td>';
                html += '</tr>';
            });
        }
        
        $('.indexed-items-table tbody').html(html);
        
        // Add event handlers for individual actions
        $('.index-record, .reindex-record').on('click', function() {
            const recordId = $(this).data('id');
            const isReindex = $(this).hasClass('reindex-record');
            indexIndividualRecord(recordId, isReindex);
        });
        
        $('.record-checkbox').on('change', function() {
            updateBulkActions();
        });
    }
    
    function displayPagination(pagination) {
        if (pagination.total_pages <= 1) {
            $('.pagination-container').remove();
            return;
        }
        
        let html = '<div class="pagination-container">';
        html += '<div class="pagination-info">';
        html += 'Showing page ' + pagination.current_page + ' of ' + pagination.total_pages + ' (' + pagination.total_items + ' total records)';
        html += '</div>';
        html += '<div class="pagination-buttons">';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += '<button class="button pagination-btn" data-page="' + (pagination.current_page - 1) + '">← Previous</button>';
        }
        
        // Page numbers
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
        
        if (startPage > 1) {
            html += '<button class="button pagination-btn" data-page="1">1</button>';
            if (startPage > 2) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === pagination.current_page ? 'button-primary' : 'button-secondary';
            html += '<button class="button pagination-btn ' + activeClass + '" data-page="' + i + '">' + i + '</button>';
        }
        
        if (endPage < pagination.total_pages) {
            if (endPage < pagination.total_pages - 1) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
            html += '<button class="button pagination-btn" data-page="' + pagination.total_pages + '">' + pagination.total_pages + '</button>';
        }
        
        // Next button
        if (pagination.current_page < pagination.total_pages) {
            html += '<button class="button pagination-btn" data-page="' + (pagination.current_page + 1) + '">Next →</button>';
        }
        
        html += '</div></div>';
        
        $('.indexed-items-table-container').after(html);
        
        // Add click handlers for pagination buttons
        $('.pagination-btn').on('click', function() {
            const page = parseInt($(this).data('page'));
            if (page !== currentPage) {
                loadIndexedItems(page);
            }
        });
    }
    
    function updateStats(stats) {
        $('#cornuwb-stat-tables').text(stats.total_tables);
        $('#cornuwb-stat-records').text(stats.total_records);
        $('#cornuwb-stat-indexed').text(stats.indexed_records);
        $('#indexed-items-table-number').text(stats.indexed_records);
    }
    
    function searchRecords(searchTerm, tableName) {
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_search_custom_records',
            search_term: searchTerm,
            table_name: tableName,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                displaySearchResults(response.data.results);
                $('#manual-search-results').show();
            } else {
                alert('Search failed: ' + response.data.message);
            }
        }).fail(function() {
            alert('Network error during search');
        });
    }
    
    function displaySearchResults(results) {
        let html = '';
        
        if (results.length === 0) {
            html = '<p style="text-align: center; color: #666; padding: 20px;">No records found</p>';
        } else {
            results.forEach(function(result) {
                const statusClass = result.is_indexed ? 'indexed' : 'not-indexed';
                const statusText = result.is_indexed ? 'Indexed' : 'Not Indexed';
                
                html += '<div class="search-result-item" style="border: 1px solid #d1d5db; border-radius: 4px; padding: 15px; margin-bottom: 10px; background: #fff;">';
                html += '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">';
                html += '<h4 style="margin: 0; font-size: 16px; color: #1d2327;">' + result.title + '</h4>';
                html += '<span class="status-badge ' + statusClass + '" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">' + statusText + '</span>';
                html += '</div>';
                html += '<p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Table: ' + result.table_name + ' | ID: ' + result.id + '</p>';
                html += '<div>';
                if (result.is_indexed) {
                    html += '<button class="button button-secondary reindex-record" data-id="' + result.id + '" data-table="' + result.table_name + '">Re-index</button>';
                } else {
                    html += '<button class="button button-primary index-record" data-id="' + result.id + '" data-table="' + result.table_name + '">Index</button>';
                }
                html += '</div>';
                html += '</div>';
            });
        }
        
        $('.search-results-list').html(html);
        
        // Add event handlers for search result actions
        $('.search-result-item .index-record, .search-result-item .reindex-record').on('click', function() {
            const recordId = $(this).data('id');
            const tableName = $(this).data('table');
            const isReindex = $(this).hasClass('reindex-record');
            indexIndividualRecord(recordId, isReindex, tableName);
        });
    }
    
    function indexIndividualRecord(recordId, isReindex, tableName = null) {
        const button = $('button[data-id="' + recordId + '"]');
        const row = button.closest('tr');
        const statusSpan = row.find('.status-badge');
        
        // Update UI to show indexing
        button.prop('disabled', true).text('Indexing...');
        if (statusSpan.length) {
            statusSpan.removeClass('indexed not-indexed').addClass('indexing').text('Indexing');
        }
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_index_single_record',
            table_name: tableName || currentTable,
            record_id: recordId,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                // Update status to indexed
                if (statusSpan.length) {
                    statusSpan.removeClass('indexing not-indexed').addClass('indexed').text('Indexed');
                }
                button.removeClass('index-record button-primary').addClass('reindex-record button-secondary').text('Re-index');
                
                // Refresh the main table
                loadIndexedItems(currentPage);
            } else {
                // Revert to not indexed
                if (statusSpan.length) {
                    statusSpan.removeClass('indexing indexed').addClass('not-indexed').text('Not Indexed');
                }
                button.removeClass('reindex-record button-secondary').addClass('index-record button-primary').text('Index');
                alert('Error: ' + response.data.message);
            }
        }).fail(function() {
            // Revert to not indexed
            if (statusSpan.length) {
                statusSpan.removeClass('indexing indexed').addClass('not-indexed').text('Not Indexed');
            }
            button.removeClass('reindex-record button-secondary').addClass('index-record button-primary').text('Index');
            alert('Network error occurred');
        }).always(function() {
            button.prop('disabled', false);
        });
    }
    
    function startBulkIndexing(tableName) {
        isIndexing = true;
        currentTable = tableName;
        
        // Show progress container
        $('#global-progress-container').show();
        $('#sync-all-progress').show();
        $('#sync-all-content, #sync-single-post').hide();
        $('#cancel-sync-all').show();
        
        // Start the indexing process
        processBulkIndexing(0, 5); // Start with offset 0, limit 5
    }
    
    function processBulkIndexing(offset, limit) {
        if (!isIndexing) return;
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_index_custom_table_batch',
            table_name: currentTable,
            offset: offset,
            limit: limit,
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                updateProgress(response.data);
                
                if (response.data.processed_count > 0) {
                    // Continue with next batch
                    setTimeout(() => {
                        processBulkIndexing(offset + limit, limit);
                    }, 1000);
                } else {
                    // Finished
                    finishBulkIndexing();
                }
            } else {
                finishBulkIndexingWithError(response.data.message);
            }
        }).fail(function() {
            finishBulkIndexingWithError('Network error occurred');
        });
    }
    
    function updateProgress(data) {
        const percentage = Math.round((data.processed / data.total) * 100);
        $('.progress-fill').css('width', percentage + '%');
        $('.progress-text').text(data.message);
        $('#sync-progress-stats').text(data.processed + ' / ' + data.total + ' (' + percentage + '%)');
    }
    
    function finishBulkIndexing() {
        isIndexing = false;
        $('#global-progress-container').hide();
        $('#sync-all-progress').hide();
        $('#sync-all-content, #sync-single-post').show();
        $('#cancel-sync-all').hide();
        
        // Refresh the table
        loadIndexedItems(currentPage);
        
        alert('Bulk indexing completed successfully!');
    }
    
    function finishBulkIndexingWithError(errorMessage) {
        isIndexing = false;
        $('#global-progress-container').hide();
        $('#sync-all-progress').hide();
        $('#sync-all-content, #sync-single-post').show();
        $('#cancel-sync-all').hide();
        
        alert('Bulk indexing failed: ' + errorMessage);
    }
    
    function cancelIndexing() {
        isIndexing = false;
        $('#global-progress-container').hide();
        $('#sync-all-progress').hide();
        $('#sync-all-content, #sync-single-post').show();
        $('#cancel-sync-all').hide();
    }
    
    function clearAllIndexes() {
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_clear_all_custom_indexes',
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                alert('All custom table indexes cleared successfully! ' + response.data.deleted_count + ' records deleted.');
                loadIndexedItems(currentPage);
            } else {
                alert('Error: ' + response.data.message);
            }
        }).fail(function() {
            alert('Network error occurred');
        });
    }
    
    function reindexSelected(selectedIds) {
        // Implementation for reindexing selected records
        console.log('Reindexing selected records:', selectedIds);
        // This would call the appropriate AJAX handler
    }
    
    function updateBulkActions() {
        const selectedCount = $('.record-checkbox:checked').length;
        $('#bulk-reindex-selected').prop('disabled', selectedCount === 0);
    }
});
</script>
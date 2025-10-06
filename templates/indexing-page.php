<?php
/**
 * Indexing page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$stats = WP_GPT_RAG_Chat\Admin::get_indexing_stats();
$settings = WP_GPT_RAG_Chat\Settings::get_settings();
?>

<div class="wrap cornuwab-admin-wrap">
    <h1>
        <?php esc_html_e('Nuwab AI Assistant - Content Indexing', 'wp-gpt-rag-chat'); ?>
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
                <h3><?php esc_html_e('Total Vectors', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number" id="cornuwb-stat-vectors"><?php echo esc_html(number_format($stats['total_vectors'])); ?></div>
                <div class="cornuwb-stat-loading" style="display: none;">
                    <span class="dashicons dashicons-update cornuwb-rotate"></span>
                </div>
            </div>
            <div class="stat-card cornuwb-stat-card">
                <h3><?php esc_html_e('Indexed Posts', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number" id="cornuwb-stat-posts"><?php echo esc_html(number_format($stats['total_posts'])); ?></div>
                <div class="cornuwb-stat-loading" style="display: none;">
                    <span class="dashicons dashicons-update cornuwb-rotate"></span>
                </div>
            </div>
            <div class="stat-card cornuwb-stat-card">
                <h3><?php esc_html_e('Recent Activity (24h)', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number" id="cornuwb-stat-activity"><?php echo esc_html(number_format($stats['recent_activity'])); ?></div>
                <div class="cornuwb-stat-loading" style="display: none;">
                    <span class="dashicons dashicons-update cornuwb-rotate"></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($stats['by_post_type'])): ?>
        <div class="post-type-breakdown">
            <h3><?php esc_html_e('By Post Type', 'wp-gpt-rag-chat'); ?></h3>
            <ul>
                <?php foreach ($stats['by_post_type'] as $post_type => $count): ?>
                <li>
                    <strong><?php echo esc_html(get_post_type_object($post_type)->labels->name ?? $post_type); ?>:</strong>
                    <?php echo esc_html(number_format($count)); ?> <?php esc_html_e('vectors', 'wp-gpt-rag-chat'); ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Manual Search & Reindex -->
    <div class="postbox" style="margin-top:20px;">
        <div class="indexed-items-header"><h2 class="hndle"><?php esc_html_e('Manual Search & Re-index', 'wp-gpt-rag-chat'); ?></h2></div>
        <div class="inside">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="text" id="manual-search-input" class="regular-text" placeholder="<?php esc_attr_e('Search by title or Post ID (e.g. 67855 or #67855)…', 'wp-gpt-rag-chat'); ?>" />
                <select id="manual-search-post-type">
                    <option value="any"><?php esc_html_e('Any type', 'wp-gpt-rag-chat'); ?></option>
                    <?php foreach (get_post_types(['public'=>true],'objects') as $pt): if ($pt->name==='attachment') continue; ?>
                        <option value="<?php echo esc_attr($pt->name); ?>"><?php echo esc_html($pt->labels->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="button" id="manual-search-btn"><?php esc_html_e('Search', 'wp-gpt-rag-chat'); ?></button>
            </div>
            <div id="manual-search-results" style="margin-top:12px;"></div>
        </div>
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
    
    <div class="indexing-layout">
        <div class="wp-gpt-rag-chat-indexed-items">
        <div class="indexed-items-header">
            <h2>
                <?php esc_html_e('Indexed Items', 'wp-gpt-rag-chat'); ?>
                <span id="indexed-items-table-count" style="font-size: 16px; font-weight: normal; color: #646970; margin-left: 10px; padding: 3px 10px; background: #f0f0f0; border-radius: 4px;">
                    (<span id="indexed-items-table-number" style="color: #2271b1; font-weight: 600;"><?php echo esc_html(number_format($stats['total_posts'])); ?></span>)
                </span>
            </h2>
            <div class="header-actions">
                <input type="text" id="indexed-items-filter" class="regular-text" placeholder="<?php esc_attr_e('Filter by title or ID…', 'wp-gpt-rag-chat'); ?>" style="min-width:280px;margin-right:8px;" />
                <button type="button" class="button button-secondary" id="select-all-items">
                    <span class="wpgrc-label"><?php esc_html_e('Select All', 'wp-gpt-rag-chat'); ?></span>
                </button>
                <button type="button" class="button button-primary" id="bulk-reindex-selected">
                    <?php esc_html_e('Reindex Selected', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
        </div>
        
        <?php
        // Get posts that are in the index queue (have been indexed or are being processed)
        global $wpdb;
        
        // Get allowed post types from settings that can be indexed
        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
        $allowed_post_types = $settings['post_types'] ?? ['post', 'page'];
        $indexable_post_types = $allowed_post_types;
        $post_type_placeholders = implode(',', array_fill(0, count($indexable_post_types), '%s'));
        
        $indexed_posts = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT p.ID, p.post_title, p.post_type, p.post_modified
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_status = 'publish'
            AND p.post_type IN ($post_type_placeholders)
            AND pm.meta_key = '_wp_gpt_rag_chat_indexed'
            AND pm.meta_value = '1'
            ORDER BY p.post_modified DESC
        ", $indexable_post_types));
        
        // Convert to array format for compatibility
        $posts = [];
        foreach ($indexed_posts as $post_data) {
            $post = new stdClass();
            $post->ID = $post_data->ID;
            $post->post_title = $post_data->post_title;
            $post->post_type = $post_data->post_type;
            $post->post_modified = $post_data->post_modified;
            $posts[] = $post;
        }
        ?>
        
        <div class="indexed-items-table-container">
            <table class="indexed-items-table">
                <thead>
                    <tr>
                        <th class="checkbox-column">
                            <input type="checkbox" id="select-all-checkbox" />
                        </th>
                        <th class="status-column"><?php esc_html_e('Status', 'wp-gpt-rag-chat'); ?></th>
                        <th class="title-column"><?php esc_html_e('Title / Model', 'wp-gpt-rag-chat'); ?></th>
                        <th class="ref-column"><?php esc_html_e('Ref', 'wp-gpt-rag-chat'); ?></th>
                        <th class="updated-column"><?php esc_html_e('Updated', 'wp-gpt-rag-chat'); ?></th>
                        <th class="actions-column"><?php esc_html_e('Actions', 'wp-gpt-rag-chat'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <?php 
                    $status = WP_GPT_RAG_Chat\Admin::get_post_indexing_status($post->ID);
                    $post_modified = get_post_modified_time('Y/m/d H:i:s', false, $post);
                    $indexed_time = $status['last_updated'] ? date('Y/m/d H:i:s', strtotime($status['last_updated'])) : null;
                    
                    // Determine status - since we only show indexed posts, they should be OK or OUTDATED
                    $status_class = 'ok';
                    $status_text = 'OK';
                    $status_icon = '✓';
                    
                    // Check if the post has been modified since last indexing
                    if ($indexed_time && strtotime($indexed_time) < strtotime($post_modified)) {
                        $status_class = 'outdated';
                        $status_text = 'OUTDATED';
                        $status_icon = '⚠';
                    }
                    
                    // If no vectors found, mark as pending (shouldn't happen with our query, but safety check)
                    if ($status['vector_count'] == 0) {
                        $status_class = 'pending';
                        $status_text = 'PENDING';
                        $status_icon = '⚠';
                    }
                    ?>
                    <tr class="indexed-item-row" data-post-id="<?php echo esc_attr($post->ID); ?>">
                        <td class="checkbox-column">
                            <input type="checkbox" class="item-checkbox" value="<?php echo esc_attr($post->ID); ?>" />
                        </td>
                        <td class="status-column">
                            <span class="status-badge status-<?php echo esc_attr($status_class); ?>">
                                <span class="status-icon"><?php echo esc_html($status_icon); ?></span>
                                <span class="status-text"><?php echo esc_html($status_text); ?></span>
                            </span>
                        </td>
                        <td class="title-column">
                            <div class="item-title">
                                <strong><?php echo esc_html($post->post_title); ?></strong>
                                <?php if ($status['vector_count'] > 0): ?>
                                <div class="embedding-info">
                                    <?php echo esc_html($settings['embedding_model'] ?? 'text-embedding-3-small'); ?>, <?php echo esc_html($settings['pinecone_dimensions'] ?? '1536'); ?> dimensions
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="ref-column">
                            <span class="ref-info">ID #<?php echo esc_html($post->ID); ?> <?php echo esc_html(strtoupper($post->post_type)); ?></span>
                        </td>
                        <td class="updated-column">
                            <span class="updated-time"><?php echo esc_html($indexed_time ?: $post_modified); ?></span>
                        </td>
                        <td class="actions-column">
                            <div class="action-buttons">
                                <button type="button" class="action-btn edit-btn" title="<?php esc_attr_e('Edit', 'wp-gpt-rag-chat'); ?>" onclick="window.open('<?php echo esc_url(get_edit_post_link($post->ID)); ?>', '_blank')">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button type="button" class="action-btn reindex-btn" title="<?php esc_attr_e('Reindex', 'wp-gpt-rag-chat'); ?>" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                    <span class="dashicons dashicons-update"></span>
                                </button>
                                <button type="button" class="action-btn delete-btn" title="<?php esc_attr_e('Remove from Index', 'wp-gpt-rag-chat'); ?>" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($posts)): ?>
        <div class="no-items-message">
            <p><?php esc_html_e('No items in the index queue. Posts will appear here after they have been indexed.', 'wp-gpt-rag-chat'); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($posts)): ?>
        <div class="cornuwb-pagination">
            <div class="cornuwb-pagination-info">
                <?php esc_html_e('Showing', 'wp-gpt-rag-chat'); ?> <span id="cornuwb-showing-start">1</span> - <span id="cornuwb-showing-end">20</span> <?php esc_html_e('of', 'wp-gpt-rag-chat'); ?> <span id="cornuwb-total-items"><?php echo count($posts); ?></span>
            </div>
            <div class="cornuwb-pagination-controls">
                <button type="button" class="button cornuwb-page-btn" id="cornuwb-first-page" disabled>
                    <span class="dashicons dashicons-controls-skipback"></span>
                </button>
                <button type="button" class="button cornuwb-page-btn" id="cornuwb-prev-page" disabled>
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <span class="cornuwb-page-numbers">
                    <?php esc_html_e('Page', 'wp-gpt-rag-chat'); ?> <span id="cornuwb-current-page">1</span> <?php esc_html_e('of', 'wp-gpt-rag-chat'); ?> <span id="cornuwb-total-pages">1</span>
                </span>
                <button type="button" class="button cornuwb-page-btn" id="cornuwb-next-page" disabled>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
                <button type="button" class="button cornuwb-page-btn" id="cornuwb-last-page" disabled>
                    <span class="dashicons dashicons-controls-skipforward"></span>
                </button>
            </div>
            <div class="cornuwb-per-page">
                <label for="cornuwb-items-per-page"><?php esc_html_e('Items per page:', 'wp-gpt-rag-chat'); ?></label>
                <select id="cornuwb-items-per-page">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="wp-gpt-rag-chat-bulk-actions">
        <h2><?php esc_html_e('Bulk Actions', 'wp-gpt-rag-chat'); ?></h2>
        
        <div class="bulk-action-section">
            <h3><?php esc_html_e('Index All Content', 'wp-gpt-rag-chat'); ?></h3>
            <p><?php esc_html_e('Index all published posts, pages, and custom post types that are marked for inclusion.', 'wp-gpt-rag-chat'); ?></p>
                
                <div class="post-type-selector">
                    <label for="index-post-type"><?php esc_html_e('Select Post Type:', 'wp-gpt-rag-chat'); ?></label>
                    <select id="index-post-type" class="post-type-dropdown">
                        <?php
                        // Get settings to filter allowed post types
                        $settings = WP_GPT_RAG_Chat\Settings::get_settings();
                        $allowed_post_types = $settings['post_types'] ?? ['post', 'page'];
                        
                        $post_types = get_post_types(['public' => true], 'objects');
                        $total_initial_count = 0;
                        
                        // Calculate total count only for allowed post types
                        foreach ($post_types as $post_type) {
                            if ($post_type->name !== 'attachment' && in_array($post_type->name, $allowed_post_types)) {
                                $count = wp_count_posts($post_type->name);
                                $published_count = isset($count->publish) ? (int) $count->publish : 0;
                                $private_count = isset($count->private) ? (int) $count->private : 0;
                                $total_for_type = $published_count + $private_count;
                                $total_initial_count += $total_for_type;
                            }
                        }
                        ?>
                        <option value="all" data-count="<?php echo esc_attr($total_initial_count); ?>"><?php esc_html_e('All Post Types', 'wp-gpt-rag-chat'); ?> (<span id="all-post-count"><?php echo esc_html($total_initial_count); ?></span>)</option>
                        <?php
                        // Only show allowed post types in the dropdown
                        foreach ($post_types as $post_type) {
                            if ($post_type->name !== 'attachment' && in_array($post_type->name, $allowed_post_types)) {
                                $count = wp_count_posts($post_type->name);
                                $published_count = isset($count->publish) ? (int) $count->publish : 0;
                                $private_count = isset($count->private) ? (int) $count->private : 0;
                                $total_for_type = $published_count + $private_count;
                                echo '<option value="' . esc_attr($post_type->name) . '" data-count="' . esc_attr($total_for_type) . '" data-post-type="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . ' (' . esc_html($total_for_type) . ')</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="sync-buttons">
                    <button type="button" id="sync-all-content" class="button button-primary">
                        <?php esc_html_e('Sync All', 'wp-gpt-rag-chat'); ?>
            </button>
                    <button type="button" id="sync-single-post" class="button button-secondary">
                        <?php esc_html_e('Sync Only One Post', 'wp-gpt-rag-chat'); ?>
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
                    <div id="sync-error-panel" style="display:none;margin-top:8px;background:#fff3f2;border:1px solid #d63638;border-radius:4px;padding:8px;max-height:140px;overflow:auto;">
                        <div style="color:#d63638;font-weight:600;margin-bottom:4px;">
                            <?php esc_html_e('Recent Errors', 'wp-gpt-rag-chat'); ?>
                            <span id="sync-error-count" style="margin-left:6px;">(0)</span>
                        </div>
                        <ul id="sync-error-list" style="margin:0;padding-left:16px;list-style:disc;"></ul>
                    </div>
                </div>
        </div>
        
        <div class="bulk-action-section">
            <h3><?php esc_html_e('Reindex Changed Content', 'wp-gpt-rag-chat'); ?></h3>
            <p><?php esc_html_e('Reindex only content that has been modified since last indexing.', 'wp-gpt-rag-chat'); ?></p>
            <button type="button" id="reindex-changed" class="button button-secondary">
                <?php esc_html_e('Reindex Changed Content', 'wp-gpt-rag-chat'); ?>
            </button>
            <div id="reindex-changed-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text"><?php esc_html_e('Preparing...', 'wp-gpt-rag-chat'); ?></div>
            </div>
        </div>
        
        <div class="bulk-action-section">
            <h3><?php esc_html_e('Generate XML Sitemap for Documentation', 'wp-gpt-rag-chat'); ?></h3>
            <p><?php esc_html_e('Generate an XML sitemap file of all your content. After generating, use "Sync All" above to index content to Pinecone.', 'wp-gpt-rag-chat'); ?></p>
                
            <div class="sitemap-controls">
                <div class="post-type-selector">
                    <label for="sitemap-post-type"><?php esc_html_e('Select Content Types:', 'wp-gpt-rag-chat'); ?></label>
                    <select id="sitemap-post-type" class="post-type-dropdown">
                        <option value="all"><?php esc_html_e('All Post Types', 'wp-gpt-rag-chat'); ?> (<span id="sitemap-all-post-count"><?php echo esc_html($total_initial_count); ?></span>)</option>
                        <?php
                        // Only show allowed post types in the sitemap dropdown
                        foreach ($post_types as $post_type) {
                            if ($post_type->name !== 'attachment' && in_array($post_type->name, $allowed_post_types)) {
                                $count = wp_count_posts($post_type->name);
                                $published_count = isset($count->publish) ? (int) $count->publish : 0;
                                $private_count = isset($count->private) ? (int) $count->private : 0;
                                $total_for_type = $published_count + $private_count;
                                echo '<option value="' . esc_attr($post_type->name) . '" data-count="' . esc_attr($total_for_type) . '">' . esc_html($post_type->label) . ' (' . esc_html($total_for_type) . ')</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="sync-buttons">
                    <button type="button" id="generate-sitemap-btn" class="button button-secondary">
                        <span class="dashicons dashicons-media-text"></span>
                        <?php esc_html_e('Generate & Download Sitemap', 'wp-gpt-rag-chat'); ?>
                    </button>
                    <button type="button" id="generate-and-index-btn" class="button button-primary">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e('Generate Sitemap & Start Indexing', 'wp-gpt-rag-chat'); ?>
                    </button>
                    <button type="button" id="view-indexable-content-btn" class="button button-secondary">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('View All Content', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
            </div>
            
            <div id="sitemap-result" style="display: none; margin-top: 15px;">
                <div class="sitemap-info">
                    <p id="sitemap-message"></p>
                    <a id="sitemap-download-link" href="#" class="button button-secondary" style="display: none;" download>
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Download Sitemap', 'wp-gpt-rag-chat'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Clear All Vectors section removed per request -->
        
        <div class="bulk-action-section cornuwb-danger-section">
            <h3><?php esc_html_e('Delete All Indexed Items', 'wp-gpt-rag-chat'); ?></h3>
            <p><?php esc_html_e('Permanently remove ALL indexed items from both the database and Pinecone. This action cannot be undone.', 'wp-gpt-rag-chat'); ?></p>
            <button type="button" id="delete-all-indexed" class="button button-secondary cornuwb-danger-btn">
                <span class="dashicons dashicons-trash"></span>
                <?php esc_html_e('Delete All Indexed Items', 'wp-gpt-rag-chat'); ?>
            </button>
        </div>
            
            <div class="bulk-action-section">
                <h3><?php esc_html_e('Import Data', 'wp-gpt-rag-chat'); ?></h3>
                <p><?php esc_html_e('Import content from CSV or PDF files to be indexed.', 'wp-gpt-rag-chat'); ?></p>
                
                <div class="import-buttons">
                    <button type="button" id="import-csv-btn" class="button button-primary">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php esc_html_e('Import CSV', 'wp-gpt-rag-chat'); ?>
                    </button>
                    <button type="button" id="import-pdf-btn" class="button button-secondary">
                        <span class="dashicons dashicons-media-document"></span>
                        <?php esc_html_e('Import PDF', 'wp-gpt-rag-chat'); ?>
                    </button>
    </div>
    
                <!-- Hidden CSV file input -->
                <input type="file" id="csv-file" accept=".csv" style="display: none;">
                
                <!-- CSV Import Modal -->
                <div id="csv-import-modal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3><?php esc_html_e('Import CSV File', 'wp-gpt-rag-chat'); ?></h3>
                            <button type="button" class="modal-close" id="close-csv-modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="file-info" id="csv-file-info" style="display: none;">
                                <div class="file-details">
                                    <span class="file-name" id="csv-file-name"></span>
                                    <button type="button" class="remove-file" id="remove-csv-file">×</button>
                                </div>
                            </div>
                            
                            <div class="import-settings" id="csv-settings" style="display: none;">
                                <label for="csv-title-column"><?php esc_html_e('Title Column:', 'wp-gpt-rag-chat'); ?></label>
                                <select id="csv-title-column" class="csv-column-dropdown">
                                    <option value=""><?php esc_html_e('Select column...', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                
                                <label for="csv-content-column"><?php esc_html_e('Content Column:', 'wp-gpt-rag-chat'); ?></label>
                                <select id="csv-content-column" class="csv-column-dropdown">
                                    <option value=""><?php esc_html_e('Select column...', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                        </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="button button-secondary" id="cancel-csv-import">
                                <?php esc_html_e('Cancel', 'wp-gpt-rag-chat'); ?>
                        </button>
                            <button type="button" class="button button-primary" id="start-csv-import" disabled>
                                <?php esc_html_e('Start CSV Import', 'wp-gpt-rag-chat'); ?>
                            </button>
                        </div>
                        
                        <div id="csv-import-progress" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <div class="progress-text"><?php esc_html_e('Importing CSV...', 'wp-gpt-rag-chat'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-confirmation-modal" class="wpgrc-modal" style="display: none;">
    <div class="wpgrc-modal-content">
        <div class="wpgrc-modal-header">
            <h3 class="wpgrc-modal-title"><?php esc_html_e('Confirm Deletion', 'wp-gpt-rag-chat'); ?></h3>
            <button type="button" class="button button-secondary wpgrc-modal-close" id="close-delete-modal">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="wpgrc-modal-body">
            <p class="wpgrc-modal-text"><?php esc_html_e('Are you sure you want to remove this item from the index?', 'wp-gpt-rag-chat'); ?></p>
            <p class="wpgrc-modal-description"><?php esc_html_e('This will delete all vectors from both the local database and Pinecone. This action cannot be undone.', 'wp-gpt-rag-chat'); ?></p>
            <div class="wpgrc-item-preview" id="delete-item-preview">
                <!-- Item details will be populated here -->
            </div>
        </div>
        <div class="wpgrc-modal-footer">
            <button type="button" class="button button-secondary wpgrc-cancel-btn" id="cancel-delete">
                <?php esc_html_e('Cancel', 'wp-gpt-rag-chat'); ?>
            </button>
            <button type="button" class="button button-primary wpgrc-delete-btn cornuwb-delete-confirm" id="confirm-delete" data-original-text="<?php esc_attr_e('Delete from Index', 'wp-gpt-rag-chat'); ?>">
                <span class="cornuwb-btn-text"><?php esc_html_e('Delete from Index', 'wp-gpt-rag-chat'); ?></span>
            </button>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div id="notification-modal" class="wpgrc-modal" style="display: none;">
    <div class="wpgrc-modal-content" style="max-width: 500px;">
        <div class="wpgrc-modal-header" id="notification-modal-header">
            <h3 class="wpgrc-modal-title" id="notification-modal-title"></h3>
            <button type="button" class="button button-secondary wpgrc-modal-close" id="close-notification-modal">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="wpgrc-modal-body">
            <div id="notification-modal-icon" style="text-align: center; font-size: 48px; margin-bottom: 15px;"></div>
            <p class="wpgrc-modal-text" id="notification-modal-message" style="text-align: center;"></p>
            <div id="notification-modal-details" style="margin-top: 10px; display: none;"></div>
        </div>
        <div class="wpgrc-modal-footer" style="text-align: center;">
            <button type="button" class="button button-primary" id="notification-modal-ok">
                <?php esc_html_e('OK', 'wp-gpt-rag-chat'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Delete All Indexed Items Modal -->
<div id="delete-all-modal" class="wpgrc-modal" style="display: none;">
    <div class="wpgrc-modal-content cornuwb-delete-all-modal">
        <div class="wpgrc-modal-header">
            <h3 class="wpgrc-modal-title" style="color: #d63638;">
                <span class="dashicons dashicons-warning" style="font-size: 24px; margin-right: 8px;"></span>
                <?php esc_html_e('Delete ALL Indexed Items?', 'wp-gpt-rag-chat'); ?>
            </h3>
            <button type="button" class="button button-secondary wpgrc-modal-close" id="close-delete-all-modal">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="wpgrc-modal-body">
            <div class="cornuwb-warning-box">
                <p class="wpgrc-modal-text" style="font-weight: 600; font-size: 15px; color: #d63638;">
                    <?php esc_html_e('⚠️ WARNING: This action is PERMANENT and CANNOT be undone!', 'wp-gpt-rag-chat'); ?>
                </p>
                <p class="wpgrc-modal-description">
                    <?php esc_html_e('This will permanently delete:', 'wp-gpt-rag-chat'); ?>
                </p>
                <ul class="cornuwb-delete-list">
                    <li><?php esc_html_e('All vectors from Pinecone', 'wp-gpt-rag-chat'); ?></li>
                    <li><?php esc_html_e('All indexing metadata from your database', 'wp-gpt-rag-chat'); ?></li>
                    <li><?php esc_html_e('All indexed items from the table below', 'wp-gpt-rag-chat'); ?></li>
                </ul>
                <p class="wpgrc-modal-description" style="margin-top: 15px;">
                    <strong><?php esc_html_e('Total items to be deleted:', 'wp-gpt-rag-chat'); ?></strong> 
                    <span id="cornuwb-delete-all-count" style="color: #d63638; font-weight: 600;">0</span>
                </p>
            </div>
            
            <div class="cornuwb-confirmation-input">
                <label for="cornuwb-delete-confirmation-text" style="font-weight: 600; margin-bottom: 8px; display: block;">
                    <?php esc_html_e('Type DELETE to confirm:', 'wp-gpt-rag-chat'); ?>
                </label>
                <input 
                    type="text" 
                    id="cornuwb-delete-confirmation-text" 
                    class="cornuwb-delete-input" 
                    placeholder="<?php esc_attr_e('Type DELETE here', 'wp-gpt-rag-chat'); ?>"
                    autocomplete="off"
                />
                <p class="cornuwb-input-hint"><?php esc_html_e('You must type "DELETE" exactly to enable the delete button.', 'wp-gpt-rag-chat'); ?></p>
            </div>
        </div>
        <div class="wpgrc-modal-footer">
            <button type="button" class="button button-secondary wpgrc-cancel-btn" id="cancel-delete-all">
                <?php esc_html_e('Cancel', 'wp-gpt-rag-chat'); ?>
            </button>
            <button 
                type="button" 
                class="button button-primary cornuwb-delete-all-confirm" 
                id="confirm-delete-all" 
                disabled
                data-original-text="<?php esc_attr_e('Delete All Items', 'wp-gpt-rag-chat'); ?>"
            >
                <span class="cornuwb-btn-text"><?php esc_html_e('Delete All Items', 'wp-gpt-rag-chat'); ?></span>
            </button>
        </div>
    </div>
</div>

<!-- PDF Import Modal -->
<div id="pdf-import-modal" class="modal" style="display: none;">
    <div class="modal-content pdf-modal-content">
        <div class="modal-header">
            <h3><?php esc_html_e('Import from PDF', 'wp-gpt-rag-chat'); ?></h3>
            <div class="modal-header-buttons">
                <button type="button" class="button button-primary" id="create-embeddings" disabled>
                    <?php esc_html_e('Create Embeddings', 'wp-gpt-rag-chat'); ?>
                </button>
                <button type="button" class="button button-secondary" id="cancel-pdf-import">
                    <?php esc_html_e('Close', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <div id="pdf-upload-section">
                <div class="pdf-upload-submodal">
                    <h4><?php esc_html_e('Upload PDF Document', 'wp-gpt-rag-chat'); ?></h4>
                    <p><?php esc_html_e('Select a PDF file to extract its content and create embeddings.', 'wp-gpt-rag-chat'); ?></p>
                    
                    <div class="pdf-upload-button-container">
                        <button type="button" class="pdf-select-button" id="pdf-upload-area">
                            <span class="dashicons dashicons-upload"></span>
                            <span class="button-text"><?php esc_html_e('Select PDF', 'wp-gpt-rag-chat'); ?></span>
                        </button>
                        <input type="file" id="pdf-file" accept=".pdf" style="display: none;">
                        <div class="pdf-parsing-message" id="pdf-parsing-message" style="display: none;">
                            <?php esc_html_e('Parsing PDF pages...', 'wp-gpt-rag-chat'); ?>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <div id="pdf-preview-section" style="display: none;">
                <div class="pdf-chunking-container">
                    <div class="chunks-panel">
                        <div class="chunks-header">
                            <h4 id="chunks-count">0 Chunks</h4>
                            <div class="chunks-controls">
                                <label class="select-all-label">
                                    <input type="checkbox" id="select-all-chunks" checked>
                                    Select All
                                </label>
                            </div>
                        </div>
                        
                        <div id="embeddings-progress" style="display: none;">
                            <div class="embeddings-progress-bar">
                                <div class="embeddings-progress-fill"></div>
                            </div>
                            <div class="embeddings-progress-text"><?php esc_html_e('Creating embeddings...', 'wp-gpt-rag-chat'); ?></div>
                        </div>
                        <div class="chunks-list" id="chunks-list">
                            <!-- Chunks will be populated here -->
                        </div>
                    </div>
                    
                    <div class="chunking-settings-panel">
                        <div class="document-info">
                            <h4><?php esc_html_e('Document Info', 'wp-gpt-rag-chat'); ?></h4>
                            <div class="info-item">
                                <strong><?php esc_html_e('Filename:', 'wp-gpt-rag-chat'); ?></strong>
                                <span id="document-filename">-</span>
                            </div>
                            <div class="info-item">
                                <strong><?php esc_html_e('Size:', 'wp-gpt-rag-chat'); ?></strong>
                                <span id="document-size">-</span>
                            </div>
                        </div>
                        
                        <div class="titles-section">
                            <h4><?php esc_html_e('Titles', 'wp-gpt-rag-chat'); ?></h4>
                            <p><?php esc_html_e('Better titles make embeddings easier to identify in the UI. Edit them manually or generate individually. This doesn\'t affect search or retrieval quality.', 'wp-gpt-rag-chat'); ?></p>
                            <button type="button" class="button button-primary generate-all-titles" id="generate-all-titles">
                                <?php esc_html_e('Generate', 'wp-gpt-rag-chat'); ?>
                            </button>
                        </div>
                        
                        <div class="chunking-section">
                            <h4><?php esc_html_e('Chunking', 'wp-gpt-rag-chat'); ?></h4>
                            <p><?php esc_html_e('Splits your PDF into smaller pieces to create searchable embeddings. Default settings work well for most cases.', 'wp-gpt-rag-chat'); ?></p>
                            
                            <div class="chunking-control">
                                <label for="density-slider"><?php esc_html_e('Density', 'wp-gpt-rag-chat'); ?></label>
                                <div class="slider-container">
                                    <input type="range" id="density-slider" min="1" max="5" value="3" class="chunking-slider">
                                    <div class="slider-labels">
                                        <span>Low</span>
                                        <span>Medium</span>
                                        <span>High</span>
                                    </div>
                                </div>
                                <p class="slider-description"><?php esc_html_e('Higher density creates more, smaller chunks.', 'wp-gpt-rag-chat'); ?></p>
                            </div>
                            
                            <div class="chunking-control">
                                <label for="overlap-slider"><?php esc_html_e('Overlap', 'wp-gpt-rag-chat'); ?></label>
                                <div class="slider-container">
                                    <input type="range" id="overlap-slider" min="0" max="50" value="15" class="chunking-slider">
                                    <div class="slider-value" id="overlap-value">15%</div>
                                </div>
                                <p class="slider-description"><?php esc_html_e('Overlap improves context between chunks.', 'wp-gpt-rag-chat'); ?></p>
                            </div>
                            
                            <button type="button" class="button button-secondary" id="rechunk-document">
                                <?php esc_html_e('Rechunk Document', 'wp-gpt-rag-chat'); ?>
                            </button>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <div id="pdf-import-progress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-text"><?php esc_html_e('Extracting text from PDF...', 'wp-gpt-rag-chat'); ?></div>
        </div>
    </div>
</div>

<style>
/* Page Container */
.indexing-page-container {
    max-width: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
}

/* Indexing Layout */
.indexing-layout {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    max-width: 100%;
    overflow-x: hidden;
}

.wp-gpt-rag-chat-indexed-items {
    flex: 1;
    min-width: 0;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.wp-gpt-rag-chat-bulk-actions {
    flex: 0 0 331px;
    min-width: 331px;
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

/* Responsive Design */
@media (max-width: 1400px) {
    .indexing-layout {
        flex-direction: column;
        gap: 20px;
    }
    
    .wp-gpt-rag-chat-indexed-items {
        flex: none;
        min-width: auto;
    }
    
    .wp-gpt-rag-chat-bulk-actions {
        flex: none;
        min-width: auto;
        max-width: 100%;
    }
    
    .progress-section {
        padding: 15px;
    }
    
    .progress-header h3 {
        font-size: 14px;
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
    
    .indexed-items-table {
        min-width: 500px;
    }
    
    .indexed-items-table th,
    .indexed-items-table td {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .progress-section {
        padding: 12px;
    }
    
    .progress-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .progress-header h3 {
        font-size: 14px;
    }
    
    .progress-text {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .indexing-page-container {
        padding: 0 10px;
    }
    
    .indexed-items-table {
        min-width: 400px;
    }
    
    .indexed-items-table th,
    .indexed-items-table td {
        padding: 6px 8px;
        font-size: 12px;
    }
    
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

.wp-gpt-rag-chat-stats {
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
}

.post-type-breakdown {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.post-type-breakdown h3 {
    margin-top: 0;
    color: #1d2327;
}

.post-type-breakdown ul {
    margin: 0;
    padding-left: 20px;
}

.post-type-breakdown li {
    margin-bottom: 8px;
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
    background: #fff;
    font-size: 13px;
    color: #1d2327;
}

.post-type-dropdown:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}

.sync-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
}

.sync-buttons .button {
    width: 100%;
    text-align: center;
    font-size: 13px;
    padding: 8px 12px;
}

.sync-buttons .button-primary {
    background: #2271b1;
    border-color: #2271b1;
}

.sync-buttons .button-primary:hover {
    background: #135e96;
    border-color: #135e96;
}

.sync-buttons .button-secondary {
    background: #f6f7f7;
    border-color: #dcdcde;
    color: #2c3338;
}

.sync-buttons .button-secondary:hover {
    background: #f0f0f1;
    border-color: #8c8f94;
}

/* Import Data Styles */
.import-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
}

.import-buttons .button {
    width: 100%;
    text-align: center;
    font-size: 13px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.import-buttons .button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.import-buttons .button-primary {
    background: #2271b1;
    border-color: #2271b1;
}

.import-buttons .button-primary:hover {
    background: #135e96;
    border-color: #135e96;
}

.import-buttons .button-secondary {
    background: #f6f7f7;
    border-color: #dcdcde;
    color: #2c3338;
}

.import-buttons .button-secondary:hover {
    background: #f0f0f1;
    border-color: #8c8f94;
}

#csv-import-section {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e1e5e9;
}

.file-upload-area {
    border: 2px dashed #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    margin-bottom: 15px;
    cursor: pointer;
    transition: border-color 0.2s ease;
}

.file-upload-area:hover {
    border-color: #2271b1;
}

.file-upload-area.dragover {
    border-color: #2271b1;
    background: #f0f6fc;
}

.upload-placeholder {
    color: #646970;
}

.upload-placeholder .dashicons {
    font-size: 32px;
    margin-bottom: 10px;
    color: #8c8f94;
}

.upload-placeholder p {
    margin: 0;
    font-size: 13px;
}

.file-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0f6fc;
    border: 1px solid #c3c4c7;
    border-radius: 3px;
    padding: 8px 12px;
}

.file-name {
    font-size: 13px;
    color: #1d2327;
    font-weight: 500;
}

.remove-file {
    background: none;
    border: none;
    color: #d63638;
    font-size: 18px;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-file:hover {
    color: #b32d2e;
}

.import-settings {
    margin-bottom: 15px;
}

.import-settings label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #1d2327;
    font-size: 13px;
}

.import-settings label:not(:first-child) {
    margin-top: 10px;
}

.csv-column-dropdown {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
    background: #fff;
    font-size: 13px;
    color: #1d2327;
}

.csv-column-dropdown:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

/* Delete Confirmation Modal Overlay */
.wpgrc-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0, 0, 0, 0.6) !important;
    z-index: 999999 !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0 !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}

.wpgrc-modal.show {
    display: flex !important;
}

/* Delete Modal Content Box */
.wpgrc-modal-content {
    background: #fff !important;
    border-radius: 8px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important;
    max-width: 560px !important;
    width: 100% !important;
    max-height: 90vh !important;
    overflow-y: auto !important;
    position: relative !important;
    z-index: 1000000 !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}

/* Modal Header */
.wpgrc-modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 20px 24px !important;
    border-bottom: 1px solid #e1e5e9 !important;
    background: #fff !important;
    border-radius: 8px 8px 0 0 !important;
}

.wpgrc-modal-title {
    margin: 0 !important;
    padding: 0 !important;
    color: #1d2327 !important;
    font-size: 20px !important;
    font-weight: 600 !important;
    line-height: 1.4 !important;
}

.wpgrc-modal-close {
    min-width: 36px !important;
    min-height: 36px !important;
    padding: 6px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* Modal Body */
.wpgrc-modal-body {
    padding: 24px !important;
    background: #fff !important;
}

.wpgrc-modal-text {
    margin: 0 0 12px 0 !important;
    padding: 0 !important;
    color: #1d2327 !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    line-height: 1.6 !important;
}

.wpgrc-modal-description {
    margin: 0 0 16px 0 !important;
    padding: 0 !important;
    color: #646970 !important;
    font-size: 13px !important;
    font-style: italic !important;
    line-height: 1.5 !important;
}

/* Modal Footer */
.wpgrc-modal-footer {
    padding: 16px 24px !important;
    border-top: 1px solid #e1e5e9 !important;
    background: #f9f9f9 !important;
    display: flex !important;
    gap: 10px !important;
    justify-content: flex-end !important;
    border-radius: 0 0 8px 8px !important;
}

.wpgrc-cancel-btn {
    min-width: 100px !important;
    padding: 8px 16px !important;
}

.wpgrc-delete-btn {
    min-width: 140px !important;
    padding: 8px 16px !important;
    background-color: #d63638 !important;
    border-color: #d63638 !important;
    color: #fff !important;
}

.wpgrc-delete-btn:hover {
    background-color: #b32d2e !important;
    border-color: #b32d2e !important;
}

/* Button loading state with cornuwb prefix */
.cornuwb-btn-loading {
    position: relative !important;
    pointer-events: none !important;
    opacity: 0.7 !important;
}

.cornuwb-btn-loading .cornuwb-btn-text {
    visibility: visible !important;
    opacity: 1 !important;
}

.cornuwb-btn-loading::after {
    content: '' !important;
    position: absolute !important;
    width: 16px !important;
    height: 16px !important;
    top: 50% !important;
    right: 12px !important;
    margin-top: -8px !important;
    border: 2px solid currentColor !important;
    border-right-color: transparent !important;
    border-radius: 50% !important;
    animation: cornuwb-spin 0.8s linear infinite !important;
}

@keyframes cornuwb-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* CORNUWB - Real-time indexing visual indicators */
.cornuwb-newly-indexed {
    background: #e7f5ff !important;
    animation: cornuwb-fade-in 0.5s ease-in-out !important;
    position: relative !important;
}

.cornuwb-newly-indexed::before {
    content: 'NEW' !important;
    position: absolute !important;
    top: 50% !important;
    left: -45px !important;
    transform: translateY(-50%) !important;
    background: #00a32a !important;
    color: white !important;
    padding: 4px 8px !important;
    border-radius: 3px !important;
    font-size: 10px !important;
    font-weight: 600 !important;
    letter-spacing: 0.5px !important;
    z-index: 10 !important;
    animation: cornuwb-slide-in 0.5s ease-out !important;
}

.cornuwb-flash-update {
    animation: cornuwb-flash 0.6s ease-in-out !important;
}

@keyframes cornuwb-fade-in {
    0% {
        background: #00a32a;
        opacity: 0.3;
    }
    100% {
        background: #e7f5ff;
        opacity: 1;
    }
}

@keyframes cornuwb-slide-in {
    0% {
        left: -100px;
        opacity: 0;
    }
    100% {
        left: -45px;
        opacity: 1;
    }
}

@keyframes cornuwb-flash {
    0%, 100% {
        background: transparent;
    }
    50% {
        background: #e7f5ff;
    }
}

.pdf-modal-content {
    max-width: 1728px;
    width: 1728px;
    max-height: 800px;
    height: 800px;
    background: #fff;
    display: flex;
    flex-direction: column;
    position: relative;
    margin: 0 auto;
}

.pdf-modal-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    z-index: 1;
}

.pdf-modal-content > * {
    position: relative;
    z-index: 2;
}

/* Responsive modal sizing */
@media (max-width: 1728px) {
    .pdf-modal-content {
        width: 100vw;
        max-width: 100vw;
    }
}

@media (max-height: 800px) {
    .pdf-modal-content {
        height: 100vh;
        max-height: 100vh;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 60px;
    border-bottom: 1px solid #e1e5e9;
    background: #fff;
    width: 100%;
    box-sizing: border-box;
    min-height: 80px;
}

.modal-header h3 {
    margin: 0;
    color: #1d2327;
    font-size: 24px;
    font-weight: 600;
    flex: 1;
    text-align: left;
}

.modal-header-buttons {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-shrink: 0;
}

.modal-header-buttons .button {
    min-width: 100px;
    height: 40px;
    font-size: 15px;
    font-weight: 500;
    padding: 0 20px;
}

/* Responsive header adjustments */
@media (max-width: 768px) {
    .modal-header {
        padding: 15px 30px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .modal-header h3 {
        font-size: 18px;
    }
    
    .modal-header-buttons .button {
        min-width: 80px;
        height: 34px;
        font-size: 13px;
        padding: 0 14px;
    }
}

@media (max-width: 480px) {
    .modal-header {
        padding: 12px 20px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .modal-header h3 {
        font-size: 16px;
    }
    
    .modal-header-buttons {
        gap: 10px;
    }
    
    .modal-header-buttons .button {
        min-width: 70px;
        height: 32px;
        font-size: 12px;
        padding: 0 12px;
    }
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #646970;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #1d2327;
}

.modal-body {
    padding: 0;
    background: #fff;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    max-height: calc(100vh - 160px);
}

.modal-body p {
    margin-bottom: 20px;
    color: #646970;
    font-size: 14px;
}

.pdf-options {
    margin-top: 15px;
}

.pdf-options label {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    font-size: 13px;
    color: #1d2327;
    cursor: pointer;
}

.pdf-options input[type="checkbox"] {
    margin-right: 8px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e1e5e9;
    background: #f8f9fa;
}

.modal-footer .button {
    min-width: 100px;
}

.file-details {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f0f6fc;
    border: 1px solid #c3c4c7;
    border-radius: 3px;
    padding: 12px;
    margin-bottom: 15px;
}

/* PDF Preview Section */
#pdf-preview-section {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
}

#pdf-preview-section h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #1d2327;
    font-size: 16px;
}

.extracted-text-preview {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    padding: 15px;
    max-height: 400px;
    overflow-y: auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    color: #1d2327;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.text-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    justify-content: flex-end;
}

.text-actions .button {
    min-width: 100px;
}

/* PDF Submodal Styles */
.pdf-upload-submodal,
.pdf-preview-submodal {
    background: #fff;
    border-radius: 8px;
    padding: 40px;
    margin: 20px;
    text-align: center;
    max-width: 500px;
    width: calc(100% - 40px);
    max-height: 80vh;
    overflow-y: auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .pdf-upload-submodal,
    .pdf-preview-submodal {
        max-width: 90vw;
        width: calc(100% - 20px);
        margin: 10px;
        padding: 30px 20px;
    }
}

@media (max-width: 480px) {
    .pdf-upload-submodal,
    .pdf-preview-submodal {
        max-width: 95vw;
        width: calc(100% - 10px);
        margin: 5px;
        padding: 20px 15px;
    }
    
    .pdf-upload-submodal h4,
    .pdf-preview-submodal h4 {
        font-size: 18px;
        margin-bottom: 10px;
    }
    
    .pdf-upload-submodal p {
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .pdf-select-button {
        padding: 12px 24px;
        font-size: 14px;
        min-width: 150px;
    }
}

/* Responsive adjustments for chunking interface */
@media (max-width: 1400px) {
    .chunking-settings-panel {
        width: 350px;
    }
}

@media (max-width: 1200px) {
    .pdf-chunking-container {
        flex-direction: column;
        height: auto;
    }
    
    .chunking-settings-panel {
    width: 100%;
    }
}

.pdf-upload-submodal h4,
.pdf-preview-submodal h4 {
    margin: 0 0 15px 0;
    font-size: 22px;
    font-weight: 600;
    color: #1d2327;
}

.pdf-upload-submodal p {
    margin: 0 0 30px 0;
    color: #646970;
    font-size: 16px;
    line-height: 1.5;
}

.pdf-upload-button-container {
    margin-bottom: 0;
}

.pdf-select-button {
    background: #0073aa;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: background-color 0.2s ease;
    min-width: 180px;
    justify-content: center;
}

.pdf-select-button:hover {
    background: #005a87;
}

.pdf-select-button .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Preloader state for PDF button */
.pdf-select-button.loading {
    background: #e3f2fd;
    color: #1976d2;
    cursor: not-allowed;
    position: relative;
}

.pdf-select-button.loading .dashicons {
    display: none;
}

.pdf-select-button.loading::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid #1976d2;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.pdf-select-button.loading .button-text {
    opacity: 0;
}

/* Parsing message */
.pdf-parsing-message {
    margin-top: 15px;
    color: #1d2327;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
}

/* PDF Chunking Interface */
.pdf-chunking-container {
    display: flex;
    height: 100%;
    gap: 20px;
    padding: 20px;
}

.chunks-panel {
    flex: 1;
    background: #fff;
    border-radius: 8px;
    padding: 20px;
}

.chunks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e1e5e9;
}

.chunks-header h4 {
    margin: 0;
    color: #1d2327;
    font-size: 18px;
    font-weight: 600;
}

.select-all-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #1d2327;
    cursor: pointer;
}

.chunks-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chunk-item {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    transition: all 0.2s ease;
    overflow: hidden;
}

.chunk-header {
    display: flex;
    align-items: center;
    padding: 15px;
    cursor: pointer;
}

.chunk-item:hover {
    background: #f0f6fc;
    border-color: #c3c4c7;
}

.chunk-item.selected {
    background: #e3f2fd;
    border-color: #2196f3;
}

.chunk-checkbox {
    margin-right: 12px;
}

.chunk-content {
    flex: 1;
    min-width: 0;
}

.chunk-title-container {
    position: relative;
}

.chunk-title {
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 3px;
    transition: background-color 0.2s ease;
}

.chunk-title:hover {
    background-color: #f0f6fc;
}

.chunk-title-edit {
    width: 100%;
    font-weight: 600;
    color: #1d2327;
    margin-bottom: 4px;
    padding: 2px 4px;
    border: 1px solid #2271b1;
    border-radius: 3px;
    background: #fff;
    font-size: 14px;
    outline: none;
    box-shadow: 0 0 0 1px #2271b1;
}

.chunk-meta {
    font-size: 12px;
    color: #646970;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chunk-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.view-chunk {
    color: #0073aa;
    text-decoration: none;
    font-size: 12px;
}

.view-chunk:hover {
    text-decoration: underline;
}

.generate-title-btn {
    background: #8b5cf6;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.generate-title-btn:hover {
    background: #7c3aed;
}

.chunk-content-preview {
    padding: 15px;
    background: #f8f9fa;
    border-top: 1px solid #e1e5e9;
    font-size: 13px;
    line-height: 1.5;
    color: #1d2327;
}

.chunk-preview-text {
    white-space: pre-wrap;
    word-wrap: break-word;
}

.chunking-settings-panel {
    width: 350px;
    background: #fff;
    border-radius: 8px;
    padding: 20px;
}

.chunking-settings-panel h4 {
    margin: 0 0 15px 0;
    color: #1d2327;
    font-size: 16px;
    font-weight: 600;
}

.chunking-settings-panel p {
    margin: 0 0 15px 0;
    color: #646970;
    font-size: 14px;
    line-height: 1.5;
}

.document-info,
.titles-section,
.chunking-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e1e5e9;
}

.info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
}

.info-item strong {
    color: #1d2327;
}

.chunking-control {
    margin-bottom: 20px;
}

.chunking-control label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1d2327;
    font-size: 14px;
}

.slider-container {
    position: relative;
    margin-bottom: 8px;
}

.chunking-slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: #e1e5e9;
    outline: none;
    -webkit-appearance: none;
    appearance: none;
}

.chunking-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #2196f3;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.chunking-slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #2196f3;
    cursor: pointer;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.slider-labels {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #646970;
    margin-top: 5px;
}

.slider-value {
    position: absolute;
    top: -25px;
    right: 0;
    background: #2196f3;
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.slider-description {
    font-size: 12px;
    color: #646970;
    margin: 0;
}

.generate-all-titles {
    background: #8b5cf6;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease;
    width: 100%;
}

.generate-all-titles:hover {
    background: #7c3aed;
}

/* Embeddings Progress Bar */
#embeddings-progress {
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
}

.embeddings-progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f1;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
    border: 1px solid #e1e5e9;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.embeddings-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0073aa 0%, #00a32a 100%);
    width: 0%;
    transition: width 0.5s ease;
    position: relative;
    overflow: hidden;
}

.embeddings-progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, .2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, .2) 50%,
        rgba(255, 255, 255, .2) 75%,
        transparent 75%,
        transparent
    );
    background-size: 20px 20px;
    animation: move 1s linear infinite;
}

.embeddings-progress-text {
    text-align: center;
    font-size: 14px;
    color: #1d2327;
    font-weight: 500;
}


.pdf-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
    margin-top: 30px;
}

.pdf-options label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    color: #1d2327;
    cursor: pointer;
}

.pdf-options input[type="checkbox"] {
    margin: 0;
}

/* Global Progress Section */
#global-progress-container {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.progress-section {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 100%;
    box-sizing: border-box;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.progress-header h3 {
    margin: 0;
    color: #1d2327;
    font-size: 16px;
}

.progress-bar {
    width: 100%;
    height: 24px;
    background: #f0f0f1;
    border-radius: 12px;
    overflow: hidden;
    margin: 15px 0;
    border: 1px solid #e1e5e9;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0073aa 0%, #00a32a 100%);
    width: 0%;
    transition: width 0.5s ease;
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-image: linear-gradient(
        -45deg,
        rgba(255, 255, 255, .2) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, .2) 50%,
        rgba(255, 255, 255, .2) 75%,
        transparent 75%,
        transparent
    );
    background-size: 20px 20px;
    animation: move 1s linear infinite;
}

@keyframes move {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 20px 20px;
    }
}

.progress-text {
    text-align: center;
    font-size: 14px;
    color: #1d2327;
    line-height: 1.4;
    margin-top: 8px;
}

/* Generic button busy state (spinner without removing label) */
.wpgrc-busy {
    position: relative;
}

.wpgrc-spinner {
    display: inline-block;
    width: 14px;
    height: 14px;
    margin-left: 6px;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: wpgrc-spin 1s linear infinite;
    vertical-align: -2px;
}

@keyframes wpgrc-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.status-indexed {
    color: #00a32a;
    font-weight: 500;
}

.status-not-indexed {
    color: #d63638;
    font-weight: 500;
}

/* Indexed Items Table Styles */

.indexed-items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ccd0d4;
    background: #f8f9fa;
}

.indexed-items-header h2 {
    margin: 0;
    color: #1d2327;
    font-size: 18px;
    font-weight: 600;
}

.header-actions {
    display: flex;
    gap: 10px;
}

#select-all-items {
    min-width: 120px;
}

/* Delete Confirmation Modal - Using unique prefixed classes */
.wpgrc-item-preview {
    background: #f8f9fa !important;
    border: 1px solid #e1e5e9 !important;
    border-radius: 4px !important;
    padding: 15px !important;
    margin-top: 15px !important;
    box-sizing: border-box !important;
}

.wpgrc-item-preview h4 {
    margin: 0 0 8px 0 !important;
    color: #1d2327 !important;
    font-size: 14px !important;
    font-weight: 600 !important;
}

.wpgrc-item-preview .item-details {
    font-size: 13px !important;
    color: #646970 !important;
    line-height: 1.4 !important;
}

.wpgrc-item-preview .item-details strong {
    color: #1d2327 !important;
    font-weight: 600 !important;
}

.indexed-items-table-container {
    overflow-x: auto;
}

.indexed-items-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    table-layout: fixed;
    min-width: 600px;
}

.indexed-items-table th {
    background: #f8f9fa;
    border-bottom: 1px solid #ccd0d4;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
}

.indexed-items-table th.checkbox-column {
    width: 40px;
}

.indexed-items-table th.status-column {
    width: 100px;
}

.indexed-items-table th.title-column {
    width: 35%;
}

.indexed-items-table th.ref-column {
    width: 120px;
}

.indexed-items-table th.updated-column {
    width: 140px;
}

.indexed-items-table th.actions-column {
    width: 120px;
}

.indexed-items-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f1;
    vertical-align: middle;
    word-wrap: break-word;
    overflow: hidden;
}

.indexed-items-table td.title-column {
    max-width: 0;
}

.indexed-items-table td.title-column .item-title {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.indexed-items-table tr:hover {
    background: #f8f9fa;
}

/* Column widths - consistent with th widths */
.checkbox-column {
    width: 40px;
    text-align: center;
}

.status-column {
    width: 100px;
}

.title-column {
    width: auto;
    min-width: 300px;
}

.ref-column {
    width: 120px;
}

.updated-column {
    width: 140px;
}

.actions-column {
    width: 120px;
}

/* Status badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-ok {
    background: #d1e7dd;
    color: #0f5132;
}

.status-badge.status-pending {
    background: #fff3cd;
    color: #664d03;
}

.status-badge.status-outdated {
    background: #f8d7da;
    color: #842029;
}

.status-icon {
    font-size: 14px;
}

/* Item title */
.item-title {
    line-height: 1.4;
}

.item-title strong {
    display: block;
    margin-bottom: 4px;
    color: #1d2327;
    font-size: 14px;
}

.embedding-info {
    font-size: 12px;
    color: #646970;
    font-style: italic;
}

/* Reference info */
.ref-info {
    font-size: 12px;
    color: #646970;
    font-family: monospace;
}

/* Updated time */
.updated-time {
    font-size: 12px;
    color: #646970;
    font-family: monospace;
}

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 4px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 14px;
}

.action-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.edit-btn {
    background: #0073aa;
    color: #fff;
}

.edit-btn:hover {
    background: #005a87;
}

.reindex-btn {
    background: #00a32a;
    color: #fff;
}

.reindex-btn:hover {
    background: #007a1f;
}

.delete-btn {
    background: #d63638;
    color: #fff;
}

.delete-btn:hover {
    background: #b32d2e;
}

/* Checkboxes */
.item-checkbox, #select-all-checkbox {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

/* Stats Cards - Real-time Updates */
.cornuwb-stat-card {
    position: relative !important;
}

.cornuwb-stat-loading {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 10 !important;
    background: rgba(255, 255, 255, 0.95) !important;
    padding: 20px !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}

.cornuwb-stat-loading .dashicons {
    font-size: 32px !important;
    width: 32px !important;
    height: 32px !important;
    color: #0073aa !important;
}

.cornuwb-rotate {
    animation: cornuwb-rotate 1s linear infinite !important;
}

@keyframes cornuwb-rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.cornuwb-stat-updated {
    animation: cornuwb-stat-pulse 0.5s ease !important;
}

@keyframes cornuwb-stat-pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
        color: #00a32a;
    }
    100% {
        transform: scale(1);
    }
}

/* Delete All Section & Button */
.cornuwb-danger-section {
    border: 2px solid #d63638 !important;
    background: #fff5f5 !important;
}

.cornuwb-danger-btn {
    background: #d63638 !important;
    color: #fff !important;
    border-color: #d63638 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.cornuwb-danger-btn:hover {
    background: #b32d2e !important;
    border-color: #b32d2e !important;
}

.cornuwb-danger-btn .dashicons {
    font-size: 18px !important;
    width: 18px !important;
    height: 18px !important;
}

/* Delete All Modal */
.cornuwb-delete-all-modal {
    max-width: 600px !important;
}

.cornuwb-warning-box {
    background: #fff3cd !important;
    border: 2px solid #ffc107 !important;
    border-radius: 6px !important;
    padding: 20px !important;
    margin-bottom: 20px !important;
}

.cornuwb-delete-list {
    margin: 10px 0 !important;
    padding-left: 25px !important;
    list-style: disc !important;
}

.cornuwb-delete-list li {
    margin: 8px 0 !important;
    color: #1d2327 !important;
    font-size: 14px !important;
}

.cornuwb-confirmation-input {
    margin-top: 20px !important;
}

.cornuwb-delete-input {
    width: 100% !important;
    padding: 12px !important;
    font-size: 16px !important;
    border: 2px solid #ccd0d4 !important;
    border-radius: 4px !important;
    font-family: monospace !important;
    letter-spacing: 2px !important;
    text-transform: uppercase !important;
    transition: border-color 0.2s ease !important;
}

.cornuwb-delete-input:focus {
    outline: none !important;
    border-color: #0073aa !important;
    box-shadow: 0 0 0 1px #0073aa !important;
}

.cornuwb-delete-input.cornuwb-input-valid {
    border-color: #00a32a !important;
    background: #f0f8f0 !important;
}

.cornuwb-delete-input.cornuwb-input-invalid {
    border-color: #d63638 !important;
    background: #fff5f5 !important;
}

.cornuwb-input-hint {
    margin-top: 8px !important;
    font-size: 12px !important;
    color: #646970 !important;
    font-style: italic !important;
}

.cornuwb-delete-all-confirm {
    background: #d63638 !important;
    border-color: #d63638 !important;
    color: #fff !important;
}

.cornuwb-delete-all-confirm:hover:not(:disabled) {
    background: #b32d2e !important;
    border-color: #b32d2e !important;
}

.cornuwb-delete-all-confirm:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
}

/* Pagination */
.cornuwb-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 33px;
    margin-top: 20px;
    border-top: 1px solid #e1e5e9;
    flex-wrap: wrap;
    gap: 15px;
}

.cornuwb-pagination-info {
    font-size: 14px;
    color: #646970;
}

.cornuwb-pagination-info span {
    font-weight: 600;
    color: #1d2327;
}

.cornuwb-pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.cornuwb-page-btn {
    min-width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #ccd0d4;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cornuwb-page-btn:hover:not(:disabled) {
    background: #f0f0f1;
    border-color: #0073aa;
    color: #0073aa;
}

.cornuwb-page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.cornuwb-page-btn .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.cornuwb-page-numbers {
    padding: 0 12px;
    font-size: 14px;
    color: #646970;
    white-space: nowrap;
}

.cornuwb-page-numbers span {
    font-weight: 600;
    color: #1d2327;
}

.cornuwb-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #646970;
}

.cornuwb-per-page label {
    margin: 0;
    font-weight: normal;
}

.cornuwb-per-page select {
    height: 36px;
    padding: 4px 8px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    background: #fff;
    font-size: 14px;
}

@media (max-width: 768px) {
    .cornuwb-pagination {
        flex-direction: column;
        align-items: stretch;
    }
    
    .cornuwb-pagination-info,
    .cornuwb-pagination-controls,
    .cornuwb-per-page {
        justify-content: center;
    }
}

/* No items message */
.no-items-message {
    padding: 40px 20px;
    text-align: center;
    color: #646970;
}

.no-items-message p {
    margin: 0;
    font-size: 16px;
}

/* Processing state */
.indexed-item-row.processing {
    opacity: 0.6;
    background: #f0f6fc;
}

.indexed-item-row.processing .action-btn {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Real-time statistics update animation - removed green box indicator */

.cornuwb-stat-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0073aa;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Global variables for progress tracking
    var isIndexingInProgress = false;

    // ==========================
    // Manual Search & Re-index
    // ==========================
    (function manualSearchInit(){
        var $results = $('#manual-search-results');
        function renderResults(items){
            if (!items || !items.length){
                $results.html('<div class="notice notice-info"><p><?php echo esc_js(__('No results found.', 'wp-gpt-rag-chat')); ?></p></div>');
                return;
            }
            var html = '<table class="widefat"><thead><tr><th><?php echo esc_js(__('ID','wp-gpt-rag-chat')); ?></th><th><?php echo esc_js(__('Title','wp-gpt-rag-chat')); ?></th><th><?php echo esc_js(__('Type','wp-gpt-rag-chat')); ?></th><th><?php echo esc_js(__('Indexed','wp-gpt-rag-chat')); ?></th><th><?php echo esc_js(__('Actions','wp-gpt-rag-chat')); ?></th></tr></thead><tbody>';
            items.forEach(function(it){
                var viewUrl = it.view_url || it.url || '';
                html += '<tr>'+
                    '<td>'+ it.id +'</td>'+
                    '<td>'+ $('<div/>').text(it.title||'').html() + (viewUrl ? ' <a href="'+viewUrl+'" target="_blank" rel="noopener" class="button-link"><?php echo esc_js(__('View','wp-gpt-rag-chat')); ?></a>' : '') + '</td>'+
                    '<td>'+ (it.type||'') +'</td>'+
                    '<td>' + (it.is_indexed ? '✅' : '❌') + '</td>'+
                    '<td>'+
                        '<button class="button enqueue-post" data-id="'+it.id+'"><?php echo esc_js(__('Add to Queue','wp-gpt-rag-chat')); ?></button> '+
                        '<button class="button button-primary reindex-post" data-id="'+it.id+'"><?php echo esc_js(__('Re-index Now','wp-gpt-rag-chat')); ?></button>'+
                    '</td>'+
                '</tr>';
            });
            html += '</tbody></table>';
            $results.html(html);
        }

        function doUnifiedSearch(){
            var q = $('#manual-search-input').val().trim();
            var pt = $('#manual-search-post-type').val();
            if (!q){ $results.html(''); return; }
            var m = q.match(/^#?(?:id:)?(\d+)$/i);
            if (m){
                var id = parseInt(m[1],10);
                $results.html('<?php echo esc_js(__('Fetching…','wp-gpt-rag-chat')); ?>');
                $.post(wpGptRagChatAdmin.ajaxUrl, {
                    action: 'wp_gpt_rag_chat_get_post_by_id',
                    nonce: wpGptRagChatAdmin.nonce,
                    post_id: id
                }, function(res){
                    if (res.success){ renderResults([res.data]); } else { $results.html('<div class="notice notice-error"><p>'+ (res.data && res.data.message ? res.data.message : 'Error') +'</p></div>'); }
                });
                return;
            }
            $results.html('<?php echo esc_js(__('Searching…','wp-gpt-rag-chat')); ?>');
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_search_content',
                nonce: wpGptRagChatAdmin.nonce,
                search: q,
                post_type: pt
            }, function(res){
                if (res.success){ renderResults(res.data.results||[]); } else { $results.html('<div class="notice notice-error"><p>'+ (res.data && res.data.message ? res.data.message : 'Error') +'</p></div>'); }
            });
        }

        $('#manual-search-btn').on('click', doUnifiedSearch);
        $('#manual-search-input').on('keydown', function(e){ if (e.key === 'Enter'){ e.preventDefault(); doUnifiedSearch(); }});
        // If input cleared, reset results panel
        $('#manual-search-input').on('input', function(){
            if (!$(this).val().trim()) {
                $results.html('');
            }
        });

        $results.on('click', '.enqueue-post', function(){
            var id = $(this).data('id');
            $(this).prop('disabled', true);
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_enqueue_post',
                nonce: wpGptRagChatAdmin.nonce,
                post_id: id
            }, function(res){
                CORNUWB.showNotification(res.success ? '<?php echo esc_js(__('Queued','wp-gpt-rag-chat')); ?>' : '<?php echo esc_js(__('Error','wp-gpt-rag-chat')); ?>', (res.data && res.data.message) || '', res.success ? 'success' : 'error');
                updateIndexedItemsTable();
            });
        });

        $results.on('click', '.reindex-post', function(){
            var id = $(this).data('id');
            $(this).prop('disabled', true);
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_reindex_post_now',
                nonce: wpGptRagChatAdmin.nonce,
                post_id: id
            }, function(res){
                CORNUWB.showNotification(res.success ? '<?php echo esc_js(__('Re-indexed','wp-gpt-rag-chat')); ?>' : '<?php echo esc_js(__('Error','wp-gpt-rag-chat')); ?>', (res.data && res.data.message) || '', res.success ? 'success' : 'error');
                updateIndexedItemsTable();
            });
        });
    })();

    // ==========================
    // Indexed Items - client filter
    // ==========================
    (function indexedItemsFilterInit(){
        var $input = $('#indexed-items-filter');
        function normalize(str){
            if (!str) return '';
            str = (str+'').toLowerCase();
            // Remove Arabic tatweel and common diacritics for better matching
            return str.replace(/[\u0640\u064B-\u0652]/g,'').trim();
        }
        function applyFilter(){
            var qRaw = ($input.val()||'');
            var q = normalize(qRaw);
            var $rows  = $('.indexed-items-table tbody tr.indexed-item-row');
            if (!q){ $rows.show(); return; }
            var idMatch = qRaw.match(/^#?(\d+)$/); // allow numeric ID without normalization
            $rows.each(function(){
                var $r = $(this);
                var id = String($r.data('post-id')||'');
                var title = normalize($r.find('.item-title').text());
                var hit = idMatch ? (id === idMatch[1]) : (title.indexOf(q) !== -1);
                $r.toggle(hit);
            });
        }
        $input.on('input', applyFilter);
        $input.on('keydown', function(e){ if (e.key==='Enter'){ e.preventDefault(); applyFilter(); } if (e.key==='Escape'){ $(this).val(''); applyFilter(); }});
        // Re-apply filter after dynamic table updates
        $(document).on('wpgrc:indexedTableUpdated', applyFilter);
    })();
    var currentIndexingAction = null;
    var retryCount = 0;
    var maxRetries = 3;
    var currentBatchSize = 10; // Start with 10, reduce on errors
    
    // CORNUWB - Utility functions with cornuwb prefix to avoid WordPress conflicts
    var CORNUWB = {
        // Show notification modal (replaces alert())
        showNotification: function(title, message, type, details) {
            type = type || 'info'; // 'success', 'error', 'warning', 'info'
            
            console.log('CORNUWB: showNotification called with:', {title: title, message: message, type: type, details: details});
            
            var icons = {
                'success': '✅',
                'error': '❌',
                'warning': '⚠️',
                'info': 'ℹ️'
            };
            
            var colors = {
                'success': '#00a32a',
                'error': '#d63638',
                'warning': '#dba617',
                'info': '#2271b1'
            };
            
            $('#notification-modal-title').text(title);
            $('#notification-modal-message').html(message);
            console.log('CORNUWB: Set modal title to:', title, 'and message to:', message);
            $('#notification-modal-icon').html(icons[type] || icons['info']);
            $('#notification-modal-header').css('border-bottom-color', colors[type] || colors['info']);
            
            if (details) {
                $('#notification-modal-details').html(details).show();
            } else {
                $('#notification-modal-details').empty().hide();
            }
            
            $('#notification-modal').fadeIn(200).addClass('show');
        },
        
        // Set button loading state without changing text
        setButtonLoading: function(button, loading) {
            if (!button || button.length === 0) return;
            
            if (loading) {
                button.prop('disabled', true);
                button.addClass('cornuwb-btn-loading');
            } else {
                button.prop('disabled', false);
                button.removeClass('cornuwb-btn-loading');
            }
        },
        
        // Legacy helper for non-cornuwb buttons
        setButtonBusy: function(button, busy) {
            if (!button || button.length === 0) return;
            if (busy) {
                if (!button.data('original-label')) {
                    button.data('original-label', $.trim(button.text()));
                }
                button.prop('disabled', true).addClass('wpgrc-busy');
                if (!button.find('.wpgrc-spinner').length) {
                    button.append('<span class="wpgrc-spinner" aria-hidden="true"></span>');
                }
            } else {
                button.prop('disabled', false).removeClass('wpgrc-busy');
                button.find('.wpgrc-spinner').remove();
                if (button.data('original-label')) {
                    button.text(button.data('original-label'));
                    button.removeData('original-label');
                }
            }
        }
    };
    
    // Helper: toggle button busy state while preserving label (legacy)
    function setButtonBusy(button, busy) {
        CORNUWB.setButtonBusy(button, busy);
    }
    
    // Close notification modal handlers
    $('#close-notification-modal, #notification-modal-ok').on('click', function() {
        $('#notification-modal').fadeOut(200).removeClass('show');
    });
    
    // Click outside modal to close
    $('#notification-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).fadeOut(200).removeClass('show');
        }
    });
    
    // Global variables for sync control
    var syncCancelled = false;
    var sitemapIndexingCancelled = false;
    var currentSyncOffset = 0;
    var totalToSync = 0;
    
    // Show emergency stop notice when indexing starts
    function showEmergencyStop(progressText) {
        $('#emergency-status-text').text('<?php esc_html_e('⚠️ INDEXING IN PROGRESS! You can stop it below.', 'wp-gpt-rag-chat'); ?>').css('color', '#fff');
        $('#emergency-stop-btn').fadeIn();
        if (progressText) {
            $('#emergency-progress-info').show();
            $('#emergency-progress-text').text(progressText);
        }
    }
    
    function updateEmergencyProgress(text) {
        $('#emergency-progress-text').text(text);
    }
    
    function hideEmergencyStop() {
        $('#emergency-status-text').text('<?php esc_html_e('No indexing in progress. Use the controls below to start indexing.', 'wp-gpt-rag-chat'); ?>');
        $('#emergency-stop-btn').fadeOut();
        $('#emergency-progress-info').hide();
    }
    
    // Emergency stop button handler
    $('#emergency-stop-btn').on('click', function() {
        if (!confirm('<?php esc_html_e('Are you sure you want to STOP all indexing operations?', 'wp-gpt-rag-chat'); ?>')) {
            return;
        }
        
        syncCancelled = true;
        sitemapIndexingCancelled = true;
        isIndexingInProgress = false;
        
        // Reset all UI
        $('#sync-all-content, #generate-and-index-btn, #sync-single-post, #index-sitemap-btn').prop('disabled', false);
        $('#cancel-sync-all, #cancel-sitemap-indexing').hide();
        $('#sync-all-progress, #sitemap-indexing-progress').fadeOut();
        hideEmergencyStop();
        
        CORNUWB.showNotification(
            '<?php esc_html_e('STOPPED!', 'wp-gpt-rag-chat'); ?>',
            '<?php esc_html_e('All indexing operations have been stopped. The page will reload in 2 seconds.', 'wp-gpt-rag-chat'); ?>',
            'warning'
        );
        
        setTimeout(function() {
            location.reload();
        }, 2000);
    });
    
    // Update emergency progress during indexing
    setInterval(function() {
        if (isIndexingInProgress && totalToSync > 0) {
            var percentage = Math.round((currentSyncOffset / totalToSync) * 100);
            updateEmergencyProgress('Processing: ' + currentSyncOffset + ' / ' + totalToSync + ' (' + percentage + '%)');
        }
    }, 500);
    
    // Function to update indexed items count in header
    function updateIndexedItemsCount() {
        $.ajax({
            url: wpGptRagChatAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_gpt_rag_chat_get_stats',
                nonce: wpGptRagChatAdmin.nonce
            },
            success: function(response) {
                if (response.success && response.data.total_posts !== undefined) {
                    var newCount = parseInt(response.data.total_posts);
                    var currentCount = parseInt($('#indexed-items-number').text().replace(/,/g, ''));
                    
                    if (newCount !== currentCount) {
                        // Animate the number change
                        $('#indexed-items-number').css({
                            'color': '#00a32a',
                            'font-weight': 'bold',
                            'transform': 'scale(1.2)',
                            'text-shadow': '0 0 8px rgba(0, 163, 42, 0.3)'
                        }).text(newCount.toLocaleString());
                        
                        // Also update table header counter
                        $('#indexed-items-table-number').css({
                            'color': '#00a32a',
                            'font-weight': 'bold'
                        }).text(newCount.toLocaleString());
                        
                        // Reset style after animation
                        setTimeout(function() {
                            $('#indexed-items-number').css({
                                'color': '#2271b1',
                                'font-weight': '600',
                                'transform': 'scale(1)',
                                'text-shadow': 'none'
                            });
                            $('#indexed-items-table-number').css({
                                'color': '#2271b1',
                                'font-weight': '600'
                            });
                        }, 500);
                    }
                }
            }
        });
    }
    
    // Update count every 3 seconds when indexing is in progress
    setInterval(function() {
        if (isIndexingInProgress) {
            updateIndexedItemsCount();
        }
    }, 3000);
    
    // Also update immediately when indexing completes
    var originalSyncNextBatch = syncNextBatch;
    
    // ============================================
    // PERSISTENT INDEXING FUNCTIONS
    // ============================================
    
    /**
     * Start persistent indexing
     */
    function startPersistentIndexing(button, postType) {
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_bulk_index',
            nonce: wpGptRagChatAdmin.nonce,
            bulk_action: 'index_all',
            post_type: postType,
            batch_size: 10,
            offset: 0
        }, function(response) {
            if (response.success) {
                // First, immediately refresh the table to show pending items
                updateIndexedItemsTable();
                
                // Show progress bar and start processing
                button.prop('disabled', true);
                $('#cancel-sync-all').show();
                $('#global-progress-container').show();
                $('#global-progress-container .progress-text').text('<?php esc_html_e('Posts added to queue, starting processing...', 'wp-gpt-rag-chat'); ?>');
                $('#global-progress-container .progress-fill').css('width', '0%');
                
                // Start processing the queue after a short delay to show pending items first
                setTimeout(function() {
                    var total = 0;
                    if (response.data && typeof response.data.total_posts !== 'undefined') {
                        total = response.data.total_posts;
                    } else if (response.data && typeof response.data.total !== 'undefined') {
                        total = response.data.total;
                    } else {
                        total = response.data.processed || 0;
                    }
                    var initiallyEnqueued = (response.data && typeof response.data.processed !== 'undefined') ? response.data.processed : 0;
                    // Get initial indexed count and persist sync state so refresh can resume
                    $.post(wpGptRagChatAdmin.ajaxUrl, {
                        action: 'wp_gpt_rag_chat_get_stats',
                        nonce: wpGptRagChatAdmin.nonce
                    }, function(statsResponse) {
                        var initialIndexedCount = 0;
                        if (statsResponse.success) {
                            initialIndexedCount = statsResponse.data.total_posts || 0;
                        }
                        
                        try {
                            localStorage.setItem('wpGptRagSyncState', JSON.stringify({
                                postType: postType,
                                total: total,
                                initialIndexedCount: initialIndexedCount,
                                startedAt: Date.now()
                            }));
                        } catch (e) {}
                        
                        startQueueProcessing(button, postType, total, initiallyEnqueued);
                    }).fail(function() {
                        // Fallback if stats request fails
                        try {
                            localStorage.setItem('wpGptRagSyncState', JSON.stringify({
                                postType: postType,
                                total: total,
                                initialIndexedCount: 0,
                                startedAt: Date.now()
                            }));
                        } catch (e) {}
                        
                        startQueueProcessing(button, postType, total, initiallyEnqueued);
                    });
                }, 500);
                
            } else {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?>',
                    response.data.message || '<?php esc_html_e('Failed to start indexing.', 'wp-gpt-rag-chat'); ?>',
                    'error'
                );
                button.prop('disabled', false);
            }
        }).fail(function() {
            CORNUWB.showNotification(
                '<?php esc_html_e('Connection Error', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Failed to connect to the server. Please try again.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
            button.prop('disabled', false);
        });
    }
    
    /**
     * Start monitoring indexing progress
     */
    function startProgressMonitoring() {
        // Clear any existing interval
        if (window.progressMonitoringInterval) {
            clearInterval(window.progressMonitoringInterval);
        }
        
        // Don't start monitoring if new batched system is active
        if (localStorage.getItem('wpGptRagSyncState')) {
            console.log('CORNUWB: Skipping old progress monitoring - new batched system is active');
            return;
        }
        
        console.log('CORNUWB: Starting old progress monitoring system');
        
        // Check progress every 2 seconds
        window.progressMonitoringInterval = setInterval(function() {
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_get_indexing_status',
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    console.log('CORNUWB: Progress monitoring response:', response.data);
                    updateProgressBarFromStatus(response.data);
                    
                    // Check for newly indexed items and update table
                    if (response.data.state) {
                        console.log('CORNUWB: State found, checking for newly_indexed:', response.data.state.newly_indexed);
                        
                        if (response.data.state.newly_indexed && response.data.state.newly_indexed.length > 0) {
                            console.log('CORNUWB: Found ' + response.data.state.newly_indexed.length + ' newly indexed items:', response.data.state.newly_indexed);
                            addNewItemsToTable(response.data.state.newly_indexed);
                            
                            // Update stats cards in real-time
                            console.log('CORNUWB: Updating stats cards in real-time');
                            if (typeof cornuwbStatsUpdater !== 'undefined') {
                                cornuwbStatsUpdater.updateStats();
                            }
                            
                            // After updating indexed items, fetch and display next batch of pending posts
                            if (response.data.state.status === 'running' && response.data.state.current_offset < response.data.state.total_posts) {
                                console.log('CORNUWB: Fetching next batch of pending posts from offset:', response.data.state.current_offset);
                                fetchNextPendingBatch(response.data.state.post_type, response.data.state.current_offset);
                            }
                            
                            // Clear the newly indexed items from the server state
                            $.post(wpGptRagChatAdmin.ajaxUrl, {
                                action: 'wp_gpt_rag_chat_clear_newly_indexed',
                                nonce: wpGptRagChatAdmin.nonce
                            }, function(clearResponse) {
                                if (clearResponse.success) {
                                    console.log('CORNUWB: Cleared newly indexed items from server state');
                                }
                            }).fail(function() {
                                console.log('CORNUWB: Failed to clear newly indexed items from server state');
                            });
                        } else {
                            console.log('CORNUWB: No newly indexed items found in this poll');
                        }
                    } else {
                        console.log('CORNUWB: No state found in response');
                    }
                    
                    // Stop monitoring if indexing is complete
                    if (!response.data.is_running) {
                        stopProgressMonitoring();
                        // Only call handleIndexingComplete if we're not using the new batched system
                        if (!localStorage.getItem('wpGptRagSyncState')) {
                        handleIndexingComplete(response.data);
                        }
                    }
                }
            });
        }, 2000);
    }
    
    /**
     * Stop monitoring indexing progress
     */
    function stopProgressMonitoring() {
        if (window.progressMonitoringInterval) {
            clearInterval(window.progressMonitoringInterval);
            window.progressMonitoringInterval = null;
        }
    }
    
    /**
     * Update progress bar from server status
     */
    function updateProgressBarFromStatus(data) {
        if (data.state) {
            var percentage = data.progress_percentage || 0;
            var progressText = data.progress_text || '<?php esc_html_e('Indexing...', 'wp-gpt-rag-chat'); ?>';
            
            $('#global-progress-container .progress-fill').css('width', percentage + '%');
            $('#global-progress-container .progress-text').text(progressText);
            
            // Update stats if available
            if (data.state.processed !== undefined && data.state.total_posts !== undefined) {
                $('#sync-progress-stats').text(data.state.processed + ' / ' + data.state.total_posts);
            }
        }
    }
    
    /**
     * Restore progress bar state from server
     */
    function restoreProgressBarState(data) {
        if (data.is_running) {
            // Don't restore old system if new batched system is active
            if (localStorage.getItem('wpGptRagSyncState')) {
                console.log('CORNUWB: Skipping old system restoration - new batched system is active');
                return;
            }
            
            console.log('CORNUWB: Restoring old persistent indexing system');
            $('#sync-all-content').prop('disabled', true);
            $('#cancel-sync-all').show();
            $('#global-progress-container').show();
            updateProgressBarFromStatus(data);
            
            // Get and display pending posts if not already shown
            if (data.state && data.state.post_type) {
                getAndDisplayPendingPosts(data.state.post_type);
            }
            
            startProgressMonitoring();
        }
    }
    
    /**
     * Handle indexing completion
     */
    function handleIndexingComplete(data) {
        $('#sync-all-content').prop('disabled', false);
        $('#cancel-sync-all').hide();
        
        // Update stats cards for all completion states
        console.log('CORNUWB: Updating stats cards on indexing completion');
        if (typeof cornuwbStatsUpdater !== 'undefined') {
            cornuwbStatsUpdater.updateStats();
        }
        
        if (data.state && data.state.status === 'completed') {
            $('#global-progress-container .progress-fill').css('width', '100%');
            $('#global-progress-container .progress-text').text('<?php esc_html_e('Completed!', 'wp-gpt-rag-chat'); ?>');
            
            CORNUWB.showNotification(
                '<?php esc_html_e('Indexing Complete', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('All content has been successfully indexed.', 'wp-gpt-rag-chat'); ?>',
                'success'
            );
            
            // Hide progress bar after 3 seconds
            setTimeout(function() {
                $('#global-progress-container').fadeOut();
            }, 3000);
            
            // Refresh the indexed items table
            setTimeout(function() {
                location.reload();
            }, 2000);
        } else if (data.state && data.state.status === 'cancelled') {
            $('#global-progress-container').fadeOut();
            CORNUWB.showNotification(
                '<?php esc_html_e('Indexing Cancelled', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Indexing has been cancelled.', 'wp-gpt-rag-chat'); ?>',
                'warning'
            );
        } else if (data.state && data.state.status === 'error') {
            $('#global-progress-container').fadeOut();
            CORNUWB.showNotification(
                '<?php esc_html_e('Indexing Error', 'wp-gpt-rag-chat'); ?>',
                data.state.error || '<?php esc_html_e('An error occurred during indexing.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
        }
    }
    
    /**
     * Cancel persistent indexing
     */
    function cancelPersistentIndexing() {
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_cancel_persistent_indexing',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                stopProgressMonitoring();
                handleIndexingComplete(response.data);
            } else {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?>',
                    response.data.message || '<?php esc_html_e('Failed to cancel indexing.', 'wp-gpt-rag-chat'); ?>',
                    'error'
                );
            }
        });
    }
    
    /**
     * Start regular indexing (fallback when persistent indexing is not available)
     */
    function startRegularIndexing(button, postType) {
        console.log('CORNUWB: Starting regular indexing as fallback');
        
        // Reset sync state
        syncCancelled = false;
        currentSyncOffset = 0;
        totalToSync = 0;
        cumulativeProcessed = 0;
        isIndexingInProgress = true;
        
        // Show progress bar
        button.prop('disabled', true);
        $('#cancel-sync-all').show();
        $('#global-progress-container').show();
        $('#global-progress-container .progress-text').text('<?php esc_html_e('Starting indexing...', 'wp-gpt-rag-chat'); ?>');
        $('#global-progress-container .progress-fill').css('width', '0%');
        
        // Start with the original syncNextBatch function
        syncNextBatch('index_all', postType);
        
        CORNUWB.showNotification(
            '<?php esc_html_e('Indexing Started', 'wp-gpt-rag-chat'); ?>',
            '<?php esc_html_e('Using regular indexing mode. Progress will be lost if you navigate away from this page.', 'wp-gpt-rag-chat'); ?>',
            'info'
        );
    }
    
    /**
     * Get and display pending posts for persistent indexing
     */
    function getAndDisplayPendingPosts(postType) {
        console.log('CORNUWB: Getting pending posts for persistent indexing');
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_persistent_pending_posts',
            nonce: wpGptRagChatAdmin.nonce,
            post_type: postType,
            limit: 10, // Get first 10 posts to show as pending (matches batch size)
            offset: 0
        }, function(response) {
            if (response.success && response.data.items) {
                console.log('CORNUWB: Got', response.data.count, 'pending posts');
                
                // Convert to table item format and mark as pending
                var pendingItems = response.data.items.map(function(item) {
                    return {
                        id: item.id,
                        title: item.title,
                        type: item.type,
                        edit_url: item.edit_url,
                        pending: true
                    };
                });
                
                // Add pending items to table
                addPendingItemsToTable(pendingItems);
            } else {
                console.log('CORNUWB: No pending posts found or error:', response);
            }
        }).fail(function(xhr) {
            console.log('CORNUWB: Failed to get pending posts:', xhr);
        });
    }
    
    /**
     * Fetch and display the next batch of pending posts
     */
    function fetchNextPendingBatch(postType, offset) {
        console.log('CORNUWB: Fetching next pending batch from offset:', offset);
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_persistent_pending_posts',
            nonce: wpGptRagChatAdmin.nonce,
            post_type: postType,
            limit: 10, // Get next 10 posts (matches batch size)
            offset: offset
        }, function(response) {
            if (response.success && response.data.items && response.data.items.length > 0) {
                console.log('CORNUWB: Got', response.data.count, 'pending posts for next batch');
                
                // Convert to table item format and mark as pending
                var pendingItems = response.data.items.map(function(item) {
                    return {
                        id: item.id,
                        title: item.title,
                        type: item.type,
                        edit_url: item.edit_url,
                        pending: true
                    };
                });
                
                // Add pending items to table
                addPendingItemsToTable(pendingItems);
            } else {
                console.log('CORNUWB: No more pending posts found for offset:', offset);
            }
        }).fail(function(xhr) {
            console.log('CORNUWB: Failed to fetch next pending batch:', xhr);
        });
    }
    
    // Sync all content - PERSISTENT INDEXING VERSION
    $('#sync-all-content').on('click', function() {
        var button = $(this);
        var selectedPostType = $('#index-post-type').val();
        
        // Check if new batched system is already running
        if (localStorage.getItem('wpGptRagSyncState')) {
            console.log('CORNUWB: New batched system already running, ignoring click');
            return;
        }
        
        // Check if persistent indexing is already running
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_indexing_status',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success && response.data.is_running) {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Indexing In Progress', 'wp-gpt-rag-chat'); ?>',
                    '<?php esc_html_e('Indexing is already running in the background. The progress bar will show the current status.', 'wp-gpt-rag-chat'); ?>',
                    'info'
                );
                // Restore progress bar state
                restoreProgressBarState(response.data);
                return;
            }
            
            // Start persistent indexing
            startPersistentIndexing(button, selectedPostType);
        }).fail(function(xhr) {
            console.log('CORNUWB: Persistent indexing not available, falling back to regular indexing');
            // Fallback to regular indexing if persistent indexing fails
            startRegularIndexing(button, selectedPostType);
        });
    });
    
    // Cancel sync button - PERSISTENT INDEXING VERSION
    $('#cancel-sync-all').on('click', function() {
        if (confirm('<?php esc_html_e('Are you sure you want to cancel the indexing process?', 'wp-gpt-rag-chat'); ?>')) {
            $(this).prop('disabled', true).text('<?php esc_html_e('Cancelling...', 'wp-gpt-rag-chat'); ?>');
            cancelPersistentIndexing();
        }
    });
    
    // Function to sync content in batches
    function syncNextBatch(action, postType) {
        if (syncCancelled) {
            // User cancelled
            isIndexingInProgress = false;
            hideEmergencyStop();
            $('#sync-all-content').prop('disabled', false);
            $('#cancel-sync-all').hide().prop('disabled', false).html('<span class="dashicons dashicons-no"></span> <?php esc_html_e('Cancel', 'wp-gpt-rag-chat'); ?>');
            $('#sync-progress-message').text('<?php esc_html_e('Cancelled by user', 'wp-gpt-rag-chat'); ?>');
            
            setTimeout(function() {
                $('#sync-all-progress').fadeOut();
            }, 2000);
            
            CORNUWB.showNotification(
                '<?php esc_html_e('Cancelled', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Indexing was cancelled. Processed: ', 'wp-gpt-rag-chat'); ?>' + (cumulativeProcessed || 0) + ' <?php esc_html_e('items', 'wp-gpt-rag-chat'); ?>',
                'warning'
            );
            return;
        }
        
        $.ajax({
            url: wpGptRagChatAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_gpt_rag_chat_bulk_index',
                nonce: wpGptRagChatAdmin.nonce,
                bulk_action: action,
                offset: currentSyncOffset,
                post_type: postType,
                batch_size: currentBatchSize
            },
            success: function(response) {
                if (response.success) {
                    // Reset retry count and batch size on successful batch
                    retryCount = 0;
                    if (currentBatchSize < 10) {
                        currentBatchSize = 10; // Reset to default batch size
                        console.log('CORNUWB: Resetting batch size to', currentBatchSize, 'after successful batch');
                    }
                    
                    var data = response.data;
                    
                    // Get total count if this is first batch
                    if (totalToSync === 0) {
                        totalToSync = data.total_posts || 0;
                        console.log('CORNUWB: Setting totalToSync to:', totalToSync);
                    }
                    
                    // Update cumulative progress counter
                    // data.processed is the number of items processed in this batch
                    cumulativeProcessed += data.processed || 0;
                    console.log('CORNUWB: Updated cumulativeProcessed to:', cumulativeProcessed, 'added batch processed:', data.processed);
                    
                    // Update offset for next batch
                    currentSyncOffset += data.processed || 0;
                    
                    // Calculate progress and update global progress bar
                    var percentage = totalToSync > 0 ? Math.min((cumulativeProcessed / totalToSync) * 100, 100) : 0;
                    console.log('CORNUWB: Progress update - cumulativeProcessed:', cumulativeProcessed, 'totalToSync:', totalToSync, 'percentage:', percentage);
                    $('#global-progress-container .progress-fill').css('width', percentage + '%');
                    
                    // Update message (real-time count)
                    var progressText = '<?php esc_html_e('Indexing...', 'wp-gpt-rag-chat'); ?> ' + cumulativeProcessed + ' / ' + totalToSync;
                    console.log('CORNUWB: Setting progress text to:', progressText);
                    $('#global-progress-container .progress-text').text(progressText);
                    $('#sync-progress-stats').text(cumulativeProcessed + ' / ' + totalToSync);

                    // Update table rows in real-time (flip PENDING -> OK)
                    console.log('CORNUWB: Checking for newly_indexed items:', data.newly_indexed);
                    if (data.newly_indexed && data.newly_indexed.length > 0) {
                        console.log('CORNUWB: Found', data.newly_indexed.length, 'newly indexed items, updating table');
                        try {
                            addNewItemsToTable(data.newly_indexed);
                        } catch (e) {
                            console.log('Realtime table update failed:', e);
                        }
                    } else {
                        console.log('CORNUWB: No newly_indexed items in this batch');
                    }
                    
                    // Update stats if provided
                    if (data.stats) {
                        $('#cornuwb-stat-vectors').text(data.stats.total_vectors || 0).addClass('cornuwab-stat-updated');
                        $('#cornuwb-stat-posts').text(data.stats.total_posts || 0).addClass('cornuwab-stat-updated');
                        
                        // Update header counter with animation
                        var newCount = data.stats.total_posts || 0;
                        $('#indexed-items-number').css({
                            'color': '#00a32a',
                            'font-weight': 'bold',
                            'transform': 'scale(1.2)',
                            'text-shadow': '0 0 8px rgba(0, 163, 42, 0.3)'
                        }).text(newCount.toLocaleString());
                        
                        // Also update table header counter
                        $('#indexed-items-table-number').css({
                            'color': '#00a32a',
                            'font-weight': 'bold'
                        }).text(newCount.toLocaleString());
                        
                        setTimeout(function() {
                            $('#cornuwb-stat-vectors, #cornuwb-stat-posts').removeClass('cornuwab-stat-updated');
                            $('#indexed-items-number').css({
                                'color': '#2271b1',
                                'font-weight': '600',
                                'transform': 'scale(1)',
                                'text-shadow': 'none'
                            });
                            $('#indexed-items-table-number').css({
                                'color': '#2271b1',
                                'font-weight': '600'
                            });
                        }, 500);
                    }
                    
                    // Check if more to process
                    if (data.processed > 0 && currentSyncOffset < totalToSync) {
                        // Continue with next batch
                        syncNextBatch(action, postType);
                    } else {
                        // All done!
                        isIndexingInProgress = false;
                        hideEmergencyStop();
                        $('#sync-all-content').prop('disabled', false);
                        $('#cancel-sync-all').hide();
                        $('#sync-progress-message').text('<?php esc_html_e('Completed!', 'wp-gpt-rag-chat'); ?>');
                        $('.progress-fill').css('width', '100%');
                        
                        setTimeout(function() {
                            $('#global-progress-container').hide();
                        }, 3000);
                        
                        CORNUWB.showNotification(
                            '<?php esc_html_e('Indexing Complete!', 'wp-gpt-rag-chat'); ?>',
                            '<?php esc_html_e('Successfully indexed ', 'wp-gpt-rag-chat'); ?>' + cumulativeProcessed + ' <?php esc_html_e('items to Pinecone.', 'wp-gpt-rag-chat'); ?>',
                            'success'
                        );
                        
                        // Reset
                        currentSyncOffset = 0;
                        cumulativeProcessed = 0;
                        totalToSync = 0;
                    }
                } else {
                    // If emergency stop is active, automatically resume and retry without showing the modal
                    if (response.data && response.data.emergency_stop) {
                        $.post(wpGptRagChatAdmin.ajaxUrl, {
                            action: 'wp_gpt_rag_resume_indexing',
                            nonce: wpGptRagChatAdmin.nonce
                        }).done(function(resumeResp) {
                            if (resumeResp && resumeResp.success) {
                                // Retry the same batch immediately; keep UI in-progress
                                syncNextBatch(action, postType);
                            } else {
                                // Fallback to original error handling
                                isIndexingInProgress = false;
                                hideEmergencyStop();
                                $('#sync-all-content').prop('disabled', false);
                                $('#cancel-sync-all').hide();
                                $('#sync-progress-message').text('<?php esc_html_e('Error occurred', 'wp-gpt-rag-chat'); ?>');
                                CORNUWB.showNotification(
                                    '<?php esc_html_e('Indexing Error', 'wp-gpt-rag-chat'); ?>',
                                    (resumeResp && resumeResp.data && resumeResp.data.message) || (response.data && response.data.message) || '<?php esc_html_e('An error occurred during indexing.', 'wp-gpt-rag-chat'); ?>',
                                    'error'
                                );
                            }
                        }).fail(function() {
                            isIndexingInProgress = false;
                            hideEmergencyStop();
                            $('#sync-all-content').prop('disabled', false);
                            $('#cancel-sync-all').hide();
                            $('#sync-progress-message').text('<?php esc_html_e('Connection error', 'wp-gpt-rag-chat'); ?>');
                            CORNUWB.showNotification(
                                '<?php esc_html_e('Indexing Error', 'wp-gpt-rag-chat'); ?>',
                                '<?php esc_html_e('Failed to auto-resume indexing. Please try again.', 'wp-gpt-rag-chat'); ?>',
                                'error'
                            );
                        });
                    } else {
                        // Regular error handling
                        isIndexingInProgress = false;
                        hideEmergencyStop();
                        $('#sync-all-content').prop('disabled', false);
                        $('#cancel-sync-all').hide();
                        $('#sync-progress-message').text('<?php esc_html_e('Error occurred', 'wp-gpt-rag-chat'); ?>');
                        
                        CORNUWB.showNotification(
                            '<?php esc_html_e('Indexing Error', 'wp-gpt-rag-chat'); ?>',
                            (response.data && response.data.message) || '<?php esc_html_e('An error occurred during indexing.', 'wp-gpt-rag-chat'); ?>',
                            'error'
                        );
                    }
                }
            },
            error: function(xhr) {
                console.log('CORNUWB: AJAX error occurred, status:', xhr.status, 'response:', xhr.responseText);
                
                // Check if it's a 500 error and we haven't exceeded max retries
                if (xhr.status === 500 && retryCount < maxRetries) {
                    retryCount++;
                    
                    // Reduce batch size on retry to handle problematic posts
                    if (retryCount === 1 && currentBatchSize > 5) {
                        currentBatchSize = 5;
                        console.log('CORNUWB: Reducing batch size to', currentBatchSize, 'due to 500 error');
                    } else if (retryCount === 2 && currentBatchSize > 1) {
                        currentBatchSize = 1;
                        console.log('CORNUWB: Reducing batch size to', currentBatchSize, 'due to repeated 500 errors');
                    }
                    
                    var retryDelay = Math.pow(2, retryCount) * 1000; // Exponential backoff: 2s, 4s, 8s
                    
                    console.log('CORNUWB: Retrying batch in', retryDelay, 'ms (attempt', retryCount, 'of', maxRetries, ') with batch size', currentBatchSize);
                    
                    $('#sync-progress-message').text('<?php esc_html_e('Retrying after error...', 'wp-gpt-rag-chat'); ?> (<?php esc_html_e('Attempt', 'wp-gpt-rag-chat'); ?> ' + retryCount + '/' + maxRetries + ', <?php esc_html_e('Batch size', 'wp-gpt-rag-chat'); ?>: ' + currentBatchSize + ')');
                    
                    setTimeout(function() {
                        syncNextBatch(action, postType);
                    }, retryDelay);
                    return;
                }
                
                // Reset retry count for next batch
                retryCount = 0;
                
                // Attempt auto-resume on network error as well
                $.post(wpGptRagChatAdmin.ajaxUrl, {
                    action: 'wp_gpt_rag_resume_indexing',
                    nonce: wpGptRagChatAdmin.nonce
                }).done(function(resumeResp) {
                    if (resumeResp && resumeResp.success) {
                        // Retry the same batch
                        syncNextBatch(action, postType);
                    } else {
                        isIndexingInProgress = false;
                        hideEmergencyStop();
                        $('#sync-all-content').prop('disabled', false);
                        $('#cancel-sync-all').hide();
                        $('#sync-progress-message').text('<?php esc_html_e('Connection error', 'wp-gpt-rag-chat'); ?>');
                        CORNUWB.showNotification(
                            '<?php esc_html_e('Connection Error', 'wp-gpt-rag-chat'); ?>',
                            (resumeResp && resumeResp.data && resumeResp.data.message) || '<?php esc_html_e('Failed to connect to the server. Please try again.', 'wp-gpt-rag-chat'); ?>',
                            'error'
                        );
                    }
                }).fail(function() {
                    isIndexingInProgress = false;
                    hideEmergencyStop();
                    $('#sync-all-content').prop('disabled', false);
                    $('#cancel-sync-all').hide();
                    $('#sync-progress-message').text('<?php esc_html_e('Connection error', 'wp-gpt-rag-chat'); ?>');
                    CORNUWB.showNotification(
                        '<?php esc_html_e('Connection Error', 'wp-gpt-rag-chat'); ?>',
                        '<?php esc_html_e('Failed to connect to the server. Please try again.', 'wp-gpt-rag-chat'); ?>',
                        'error'
                    );
                });
            }
        });
    }
    
    // Sync single post (use global full-width progress bar at the top)
    $('#sync-single-post').on('click', function() {
        var button = $(this);
        var selectedPostType = $('#index-post-type').val();
        
        if (isIndexingInProgress) {
            CORNUWB.showNotification(
                '<?php esc_html_e('Indexing In Progress', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Indexing is already in progress. Please wait for it to complete.', 'wp-gpt-rag-chat'); ?>',
                'warning'
            );
            return;
        }
        
        // Disable the button and use the global top progress bar
        button.prop('disabled', true);
        showGlobalProgress('index_single', button, selectedPostType);
    });
    
    // Stop indexing button
    $('#stop-indexing').on('click', function() {
        if (confirm('<?php esc_js(__('Are you sure you want to stop the indexing process?', 'wp-gpt-rag-chat')); ?>')) {
            stopIndexing();
        }
    });
    
    // Update count when post type changes
    $('#index-post-type').on('change', function() {
        updateSyncAllCount();
    });
    
    // Initial count update
    updateSyncAllCount();
    
    // Function to show global progress
    function showGlobalProgress(action, button, postType) {
        isIndexingInProgress = true;
        currentIndexingAction = action;
        
        var progressContainer = $('#global-progress-container');
        var progressFill = progressContainer.find('.progress-fill');
        var progressText = progressContainer.find('.progress-text');
        var stopButton = $('#stop-indexing');
        
        // Show progress container and stop button
        progressContainer.show();
        stopButton.show();
        
        // Reset progress
        progressFill.css('width', '0%');
        progressText.text('<?php esc_js(__('Preparing...', 'wp-gpt-rag-chat')); ?>');
        
        // Start the indexing process
        startBulkIndexing(action, progressContainer, button, postType);
    }
    
    // Function to stop indexing
    function stopIndexing() {
        isIndexingInProgress = false;
        currentIndexingAction = null;
        
        var progressContainer = $('#global-progress-container');
        var progressText = progressContainer.find('.progress-text');
        var stopButton = $('#stop-indexing');
        
        // Update progress text
        progressText.html('<strong style="color: #d63638;"><?php esc_js(__('Indexing Stopped', 'wp-gpt-rag-chat')); ?></strong>');
        
        // Hide stop button
        stopButton.hide();
        
        // Reset buttons
        resetIndexingButtons();
        
        // Hide progress after delay
        setTimeout(function() {
            progressContainer.hide();
        }, 3000);
    }
    
    // Function to reset indexing buttons
    function resetIndexingButtons() {
        $('#sync-all-content').prop('disabled', false).find('span').text('0');
        setButtonBusy($('#sync-single-post'), false);
        updateSyncAllCount();
    }
    
    // Function to update post counts
    function updatePostCounts() {
        console.log('Updating post counts...');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_gpt_rag_chat_get_post_counts',
                nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
            },
            success: function(response) {
                console.log('Post counts response:', response);
                if (response.success) {
                    var postCounts = response.data.post_counts;
                    var totalCount = response.data.total_count;
                    
                    console.log('Post counts:', postCounts);
                    console.log('Total count:', totalCount);
                    
                    // Only update if we have valid counts (greater than 0 or if we explicitly got 0)
                    if (totalCount >= 0) {
                        // Update "All Post Types" count
                        $('#all-post-count').text(totalCount);
                        $('#sitemap-all-post-count').text(totalCount);
                        
                        // Update individual post type counts in dropdowns
                        $('#index-post-type option, #sitemap-post-type option').each(function() {
                            var postType = $(this).val();
                            if (postType !== 'all' && postCounts[postType]) {
                                var newText = postCounts[postType].label + ' (' + postCounts[postType].count + ')';
                                $(this).text(newText);
                                $(this).attr('data-count', postCounts[postType].count);
                            }
                        });
                        
                        // Update sync all button count
                        updateSyncAllCount();
                    } else {
                        console.log('Invalid total count received:', totalCount);
                    }
                } else {
                    console.log('Error in response:', response.data);
                    // Don't update counts if there's an error - keep the server-side calculated ones
                }
            },
            error: function(xhr, status, error) {
                console.log('Failed to update post counts:', error);
                console.log('XHR:', xhr);
                // Don't update counts if AJAX fails - keep the server-side calculated ones
            }
        });
    }
    
    // Function to update sync all button count based on selected post type
    function updateSyncAllCount() {
        var selectedPostType = $('#index-post-type').val();
        var count = 0;
        
        console.log('updateSyncAllCount called for post type:', selectedPostType);
        
        if (selectedPostType === 'all') {
            // Method 1: Get from button's data attribute
            count = parseInt($('#sync-all-content').attr('data-total-count') || 0);
            console.log('Method 1 - data-total-count:', count);
            
            // Method 2: Get from "all" option's data-count attribute
            if (count === 0) {
                count = parseInt($('#index-post-type option[value="all"]').attr('data-count') || 0);
                console.log('Method 2 - all option data-count:', count);
            }
            
            // Method 3: Get from span text
            if (count === 0) {
                var spanText = $('#all-post-count').text();
                count = parseInt(spanText) || 0;
                console.log('Method 3 - span text:', count);
            }
            
            // Method 4: Parse from option text
            if (count === 0) {
                var allOptionText = $('#index-post-type option[value="all"]').text();
                var match = allOptionText.match(/\((\d+)\)/);
                if (match) {
                    count = parseInt(match[1]);
                    console.log('Method 4 - parsed from text:', count);
                }
            }
        } else {
            // Get count for selected post type
            count = parseInt($('#index-post-type option:selected').attr('data-count') || 0);
            console.log('Selected post type count:', count);
        }
        
        console.log('Final count to set:', count);
        $('#sync-all-count').text(count);
    }
    
    // Function to preserve server-side calculated counts
    function preserveServerSideCounts() {
        // Store the initial server-side calculated counts
        var initialAllCount = $('#all-post-count').text();
        var initialSyncCount = $('#sync-all-count').text();
        
        console.log('Preserving server-side counts - All:', initialAllCount, 'Sync:', initialSyncCount);
        
        // If AJAX fails or returns 0, restore the server-side counts
        setTimeout(function() {
            if ($('#all-post-count').text() === '0' && initialAllCount !== '0') {
                console.log('Restoring server-side count:', initialAllCount);
                $('#all-post-count').text(initialAllCount);
                $('#sitemap-all-post-count').text(initialAllCount);
                $('#sync-all-count').text(initialSyncCount);
            }
        }, 2000);
    }
    
    // Simple function to force set the sync all count
    function forceSetSyncAllCount(count) {
        console.log('Force setting sync all count to:', count);
        
        // Only update the span, not the entire button
        $('#sync-all-count').text(count);
        
        // Also update the button's data attribute
        $('#sync-all-content').attr('data-total-count', count);
        
        console.log('Count updated to:', count);
    }
    
    // Function to complete indexing
    function completeIndexing(button, totalItems, postType) {
        isIndexingInProgress = false;
        currentIndexingAction = null;
        
        var progressContainer = $('#global-progress-container');
        var progressFill = progressContainer.find('.progress-fill');
        var progressText = progressContainer.find('.progress-text');
        var stopButton = $('#stop-indexing');
        
        // Complete progress bar
        progressFill.css('width', '100%');
        
        // Update completion message
        var postTypeText = (postType && postType !== 'all') ? ' (' + postType + ')' : '';
        progressText.html(
            '<strong style="color: #00a32a;"><?php esc_js(__('Indexing Successfully Completed!', 'wp-gpt-rag-chat')); ?></strong><br>' +
            '<span style="color: #646970;">' + totalItems + ' items successfully indexed' + postTypeText + '</span>'
        );
        
        // Hide stop button
        stopButton.hide();
        
        // Reset buttons
        resetIndexingButtons();
        
        // Hide progress after delay
        setTimeout(function() {
            progressContainer.hide();
        }, 5000);
    }
    
    // Function to update the sync all count
    function updateSyncAllCount() {
        var selectedPostType = $('#index-post-type').val();
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_post_count',
            post_type: selectedPostType,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                $('#sync-all-count').text(response.data.count);
            }
        });
    }
    
    // Import functionality
    var selectedCsvFile = null;
    var selectedPdfFile = null;
    
    // CSV Import button handler - opens file browser directly
    $('#import-csv-btn').on('click', function() {
        $('#csv-file').click();
    });
    
    // PDF Import button handler
    $('#import-pdf-btn').on('click', function() {
        $('#pdf-import-modal').show();
    });
    
    // CSV Modal close handlers
    $('#close-csv-modal, #cancel-csv-import').on('click', function() {
        $('#csv-import-modal').hide();
        resetCsvFileSelection();
    });
    
    // PDF Modal close handlers
    $('#close-pdf-modal, #cancel-pdf-import').on('click', function() {
        $('#pdf-import-modal').hide();
        resetPdfFileSelection();
    });
    
    // Close modals when clicking outside
    $('#csv-import-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
            resetCsvFileSelection();
        }
    });
    
    $('#pdf-import-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
            resetPdfFileSelection();
        }
    });
    
    // CSV file change handler
    $('#csv-file').on('change', function() {
        var file = this.files[0];
        if (file) {
            handleCsvFileSelection(file);
            $('#csv-import-modal').show(); // Show modal after file selection
        }
    });
    
    // PDF file upload handlers - using event delegation to prevent recursion
    $(document).on('click', '#pdf-upload-area', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#pdf-file').click();
    });
    
    $(document).on('change', '#pdf-file', function() {
        var file = this.files[0];
        if (file) {
            handlePdfFileSelection(file);
        }
    });
    
    // PDF drag and drop handlers
    $(document).on('dragover', '#pdf-upload-area', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $(document).on('dragleave', '#pdf-upload-area', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $(document).on('drop', '#pdf-upload-area', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handlePdfFileSelection(files[0]);
        }
    });
    
    // Remove file handlers
    $('#remove-csv-file').on('click', function(e) {
        e.stopPropagation();
        resetCsvFileSelection();
    });
    
    $('#remove-pdf-file').on('click', function(e) {
        e.stopPropagation();
        resetPdfFileSelection();
    });
    
    // Start import handlers
    $('#start-csv-import').on('click', function() {
        if (!selectedCsvFile) return;
        
        var button = $(this);
        var progress = $('#csv-import-progress');
        
        button.prop('disabled', true).text('<?php esc_js(__('Importing...', 'wp-gpt-rag-chat')); ?>');
        progress.show();
        
        startCsvImport(selectedCsvFile, progress, button);
    });
    
    $('#start-pdf-import').on('click', function() {
        if (!selectedPdfFile) return;
        
        var button = $(this);
        var progress = $('#pdf-import-progress');
        var extractImages = $('#extract-images').is(':checked');
        var preserveFormatting = $('#preserve-formatting').is(':checked');
        
        button.prop('disabled', true).text('<?php esc_js(__('Importing...', 'wp-gpt-rag-chat')); ?>');
        progress.show();
        
        startPdfImport(selectedPdfFile, progress, button, extractImages, preserveFormatting);
    });
    
    // Function to handle CSV file selection
    function handleCsvFileSelection(file) {
        if (file.type !== 'text/csv' && !file.name.toLowerCase().endsWith('.csv')) {
            CORNUWB.showNotification(
                '<?php esc_html_e('Invalid File', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Please select a valid CSV file.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
            return;
        }
        
        selectedCsvFile = file;
        $('#csv-file-name').text(file.name);
        $('#csv-file-info').show();
        $('#csv-settings').show();
        
        // Enable start import button
        $('#start-csv-import').prop('disabled', false);
        
        // Parse CSV columns
        parseCSVColumns(file);
    }
    
    // Function to handle PDF file selection
    function handlePdfFileSelection(file) {
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            CORNUWB.showNotification(
                '<?php esc_html_e('Invalid File', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Please select a valid PDF file.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
            return;
        }
        
        selectedPdfFile = file;
        
        // Show preloader state
        showPdfPreloader();
        
        // Extract text and show preview
        extractPdfTextPreview(file);
    }
    
    // Function to show PDF preloader
    function showPdfPreloader() {
        $('#pdf-upload-area').addClass('loading').prop('disabled', true);
        $('#pdf-parsing-message').show();
    }
    
    // Function to hide PDF preloader
    function hidePdfPreloader() {
        $('#pdf-upload-area').removeClass('loading').prop('disabled', false);
        $('#pdf-parsing-message').hide();
    }
    
    // Function to display chunking interface
    function displayChunkingInterface(sections) {
        // Update document info
        $('#document-filename').text(window.pdfDocumentInfo.filename);
        $('#document-size').text(window.pdfDocumentInfo.characterCount.toLocaleString() + ' characters');
        
        // Update chunks count
        $('#chunks-count').text(sections.length + ' Chunks');
        
        // Populate chunks list
        var chunksList = $('#chunks-list');
        chunksList.empty();
        
        sections.forEach(function(section, index) {
            var chunkItem = createChunkItem(section, index);
            chunksList.append(chunkItem);
        });
        
        // Initialize event handlers
        initializeChunkingHandlers();
    }
    
    // Function to create a chunk item
    function createChunkItem(section, index) {
        var pageText = section.page ? 'Page ' + section.page : 'Pages ' + section.startPage + '-' + section.endPage;
        var charCount = section.content.length;
        
        return $('<div class="chunk-item selected" data-index="' + index + '">' +
            '<div class="chunk-header">' +
                '<input type="checkbox" class="chunk-checkbox" checked>' +
                '<div class="chunk-content">' +
                    '<div class="chunk-title-container">' +
                        '<div class="chunk-title" title="' + section.title + '" data-index="' + index + '">' + section.title + '</div>' +
                        '<input type="text" class="chunk-title-edit" data-index="' + index + '" value="' + section.title + '" style="display: none;">' +
                    '</div>' +
                    '<div class="chunk-meta">' +
                        '<span>' + pageText + ' • ' + charCount + ' chars</span>' +
                        '<a href="#" class="view-chunk" data-index="' + index + '">View</a>' +
                    '</div>' +
                '</div>' +
                '<div class="chunk-actions">' +
                    '<button type="button" class="generate-title-btn" data-index="' + index + '">Generate</button>' +
                '</div>' +
            '</div>' +
            '<div class="chunk-content-preview" data-index="' + index + '" style="display: none;">' +
                '<div class="chunk-preview-text">' + section.content.substring(0, 500) + (section.content.length > 500 ? '...' : '') + '</div>' +
            '</div>' +
        '</div>');
    }
    
    // Function to initialize chunking event handlers
    function initializeChunkingHandlers() {
        // Select all chunks checkbox
        $('#select-all-chunks').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.chunk-checkbox').prop('checked', isChecked);
            $('.chunk-item').toggleClass('selected', isChecked);
        });
        
        // Individual chunk checkboxes
        $(document).on('change', '.chunk-checkbox', function() {
            var chunkItem = $(this).closest('.chunk-item');
            chunkItem.toggleClass('selected', $(this).is(':checked'));
            updateSelectAllState();
        });
        
        // View chunk
        $(document).on('click', '.view-chunk', function(e) {
            e.preventDefault();
            var index = $(this).data('index');
            viewChunkContent(index);
        });
        
        // Edit chunk title
        $(document).on('click', '.chunk-title', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var index = $(this).data('index');
            editChunkTitle(index);
        });
        
        // Save chunk title on Enter or blur
        $(document).on('keydown', '.chunk-title-edit', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var index = $(this).data('index');
                saveChunkTitle(index);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                var index = $(this).data('index');
                cancelEditChunkTitle(index);
            }
        });
        
        $(document).on('blur', '.chunk-title-edit', function() {
            var index = $(this).data('index');
            saveChunkTitle(index);
        });
        
        // Generate individual title
        $(document).on('click', '.generate-title-btn', function() {
            var index = $(this).data('index');
            generateChunkTitle(index);
        });
        
        // Generate all titles
        $('#generate-all-titles').on('click', function() {
            generateAllTitles();
        });
        
        // Overlap slider
        $('#overlap-slider').on('input', function() {
            $('#overlap-value').text($(this).val() + '%');
        });
        
        // Rechunk document
        $('#rechunk-document').on('click', function() {
            rechunkDocument();
        });
        
        // Create embeddings / Stop embeddings
        $('#create-embeddings').on('click', function() {
            var button = $(this);
            if (button.hasClass('button-secondary') && button.css('background-color') === 'rgb(214, 54, 56)') {
                // Stop the process
                stopEmbeddings();
            } else {
                // Start the process
                createEmbeddings();
            }
        });
        
    }
    
    // Function to update select all state
    function updateSelectAllState() {
        var totalChunks = $('.chunk-checkbox').length;
        var checkedChunks = $('.chunk-checkbox:checked').length;
        
        if (checkedChunks === 0) {
            $('#select-all-chunks').prop('indeterminate', false).prop('checked', false);
        } else if (checkedChunks === totalChunks) {
            $('#select-all-chunks').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#select-all-chunks').prop('indeterminate', true);
        }
    }
    
    // Function to view chunk content (accordion toggle)
    function viewChunkContent(index) {
        var preview = $('.chunk-content-preview[data-index="' + index + '"]');
        var viewLink = $('.view-chunk[data-index="' + index + '"]');
        
        if (preview.is(':visible')) {
            // Hide the preview
            preview.slideUp(300);
            viewLink.text('View');
        } else {
            // Show the preview
            preview.slideDown(300);
            viewLink.text('Hide');
        }
    }
    
    // Function to edit chunk title
    function editChunkTitle(index) {
        var titleElement = $('.chunk-title[data-index="' + index + '"]');
        var editInput = $('.chunk-title-edit[data-index="' + index + '"]');
        
        // Hide title, show input
        titleElement.hide();
        editInput.show().focus().select();
    }
    
    // Function to save chunk title
    function saveChunkTitle(index) {
        var titleElement = $('.chunk-title[data-index="' + index + '"]');
        var editInput = $('.chunk-title-edit[data-index="' + index + '"]');
        var newTitle = editInput.val().trim();
        
        if (newTitle) {
            // Update the section data
            if (window.pdfSections[index]) {
                window.pdfSections[index].title = newTitle;
            }
            
            // Update the display
            titleElement.text(newTitle).attr('title', newTitle);
        }
        
        // Hide input, show title
        editInput.hide();
        titleElement.show();
    }
    
    // Function to cancel chunk title edit
    function cancelEditChunkTitle(index) {
        var titleElement = $('.chunk-title[data-index="' + index + '"]');
        var editInput = $('.chunk-title-edit[data-index="' + index + '"]');
        
        // Reset input value to original title
        var originalTitle = titleElement.text();
        editInput.val(originalTitle);
        
        // Hide input, show title
        editInput.hide();
        titleElement.show();
    }
    
    // Function to generate chunk title using OpenAI
    function generateChunkTitle(index) {
        var section = window.pdfSections[index];
        if (!section) return;
        
        var button = $('.generate-title-btn[data-index="' + index + '"]');
        var originalText = button.text();
        
        // Show loading state
        button.prop('disabled', true).text('Generating...');
        
        // Make AJAX call to generate title
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_generate_chunk_title',
            chunk_content: section.content,
            chunk_index: index,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success && response.data.title) {
                var newTitle = response.data.title;
                
                // Update the section title
                window.pdfSections[index].title = newTitle;
                
                // Update the UI
                $('.chunk-item[data-index="' + index + '"] .chunk-title').text(newTitle).attr('title', newTitle);
                $('.chunk-item[data-index="' + index + '"] .chunk-title-edit').val(newTitle);
            } else {
                // Fallback to simple title generation
                var words = section.content.split(' ').slice(0, 6);
                var newTitle = words.join(' ');
                if (newTitle.length > 50) {
                    newTitle = newTitle.substring(0, 47) + '...';
                }
                
                // Update the section title
                window.pdfSections[index].title = newTitle;
                
                // Update the UI
                $('.chunk-item[data-index="' + index + '"] .chunk-title').text(newTitle).attr('title', newTitle);
                $('.chunk-item[data-index="' + index + '"] .chunk-title-edit').val(newTitle);
                
                console.warn('OpenAI title generation failed, using fallback:', response.data?.message || 'Unknown error');
            }
        }).fail(function() {
            // Fallback to simple title generation on error
            var words = section.content.split(' ').slice(0, 6);
            var newTitle = words.join(' ');
            if (newTitle.length > 50) {
                newTitle = newTitle.substring(0, 47) + '...';
            }
            
            // Update the section title
            window.pdfSections[index].title = newTitle;
            
            // Update the UI
            $('.chunk-item[data-index="' + index + '"] .chunk-title').text(newTitle).attr('title', newTitle);
            $('.chunk-item[data-index="' + index + '"] .chunk-title-edit').val(newTitle);
            
            console.error('Failed to generate title with OpenAI');
        }).always(function() {
            // Restore button state
            button.prop('disabled', false).text(originalText);
        });
    }
    
    // Function to generate all titles
    function generateAllTitles() {
        var button = $('#generate-all-titles');
        var originalText = button.text();
        
        // Show loading state
        button.prop('disabled', true).text('Generating All Titles...');
        
        // Generate titles for all chunks with a small delay between each
        var currentIndex = 0;
        var totalChunks = window.pdfSections.length;
        
        function generateNextTitle() {
            if (currentIndex >= totalChunks) {
                // All titles generated, restore button
                button.prop('disabled', false).text(originalText);
                return;
            }
            
            // Update button text to show progress
            button.text('Generating... (' + (currentIndex + 1) + '/' + totalChunks + ')');
            
            // Generate title for current chunk
            generateChunkTitle(currentIndex);
            
            // Move to next chunk after a short delay
            currentIndex++;
            setTimeout(generateNextTitle, 1000); // 1 second delay between requests
        }
        
        generateNextTitle();
    }
    
    // Function to rechunk document
    function rechunkDocument() {
        var density = $('#density-slider').val();
        var overlap = $('#overlap-slider').val();
        
        // Show loading state
        $('#rechunk-document').prop('disabled', true).text('Rechunking...');
        
        // Simulate rechunking (in real implementation, this would call the backend)
        setTimeout(function() {
            // For now, just regenerate titles
            generateAllTitles();
            $('#rechunk-document').prop('disabled', false).text('<?php esc_js(__('Rechunk Document', 'wp-gpt-rag-chat')); ?>');
        }, 1000);
    }
    
    // Function to create embeddings
    function createEmbeddings() {
        // Get selected chunks
        var selectedChunks = [];
        $('.chunk-checkbox:checked').each(function() {
            var index = $(this).closest('.chunk-item').data('index');
            if (window.pdfSections[index]) {
                selectedChunks.push(window.pdfSections[index]);
            }
        });
        
        if (selectedChunks.length === 0) {
            CORNUWB.showNotification(
                '<?php esc_html_e('No Selection', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Please select at least one chunk to create embeddings.', 'wp-gpt-rag-chat'); ?>',
                'warning'
            );
            return;
        }
        
        var button = $('#create-embeddings');
        var progressBar = $('#embeddings-progress');
        var progressFill = $('.embeddings-progress-fill');
        var progressText = $('.embeddings-progress-text');
        
        // Show progress bar and change button to stop
        progressBar.show();
        button.prop('disabled', false)
              .removeClass('button-primary')
              .addClass('button-secondary')
              .css('background-color', '#d63638')
              .css('border-color', '#d63638')
              .html('<span class="dashicons dashicons-controls-pause" style="margin-right: 5px;"></span><?php esc_js(__('Stop', 'wp-gpt-rag-chat')); ?>');
        
        // Store original button state for restoration
        window.originalButtonState = {
            text: '<?php esc_js(__('Create Embeddings', 'wp-gpt-rag-chat')); ?>',
            class: 'button-primary',
            background: '',
            border: ''
        };
        
        // Start creating embeddings
        window.embeddingsStopped = false;
        createEmbeddingsForChunks(selectedChunks, 0, progressFill, progressText, button);
    }
    
    // Function to stop embeddings process
    function stopEmbeddings() {
        window.embeddingsStopped = true;
        var button = $('#create-embeddings');
        var progressText = $('.embeddings-progress-text');
        
        // Restore button to original state
        button.prop('disabled', false)
              .removeClass('button-secondary')
              .addClass('button-primary')
              .css('background-color', '')
              .css('border-color', '')
              .text('<?php esc_js(__('Create Embeddings', 'wp-gpt-rag-chat')); ?>');
        
        // Update progress text
        progressText.html('<strong style="color: #d63638;"><?php esc_js(__('Process Stopped', 'wp-gpt-rag-chat')); ?></strong>');
    }
    
    // Function to create embeddings for chunks with progress tracking
    function createEmbeddingsForChunks(chunks, currentIndex, progressFill, progressText, button) {
        // Check if process was stopped
        if (window.embeddingsStopped) {
            return;
        }
        
        if (currentIndex >= chunks.length) {
            // All chunks processed
            progressFill.css('width', '100%');
            progressText.html('<strong style="color: #00a32a;"><?php esc_js(__('Embeddings Created Successfully!', 'wp-gpt-rag-chat')); ?></strong><br><span style="color: #646970;">' + chunks.length + ' <?php esc_js(__('chunks processed', 'wp-gpt-rag-chat')); ?></span>');
            
            // Restore button to original state
            button.prop('disabled', false)
                  .removeClass('button-secondary')
                  .addClass('button-primary')
                  .css('background-color', '')
                  .css('border-color', '')
                  .text('<?php esc_js(__('Create Embeddings', 'wp-gpt-rag-chat')); ?>');
            
            // Close modal after a short delay
            setTimeout(function() {
                $('#pdf-import-modal').hide();
                resetPdfFileSelection();
            }, 2000);
            return;
        }
        
        var chunk = chunks[currentIndex];
        var percentage = Math.round(((currentIndex + 1) / chunks.length) * 100);
        
        // Update progress
        progressFill.css('width', percentage + '%');
        progressText.text('<?php esc_js(__('Creating embeddings...', 'wp-gpt-rag-chat')); ?> ' + (currentIndex + 1) + '/' + chunks.length + ' (' + percentage + '%)');
        
        // Create embedding for current chunk
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_create_chunk_embedding',
            chunk_title: chunk.title,
            chunk_content: chunk.content,
            chunk_index: currentIndex,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                // Move to next chunk
                setTimeout(function() {
                    createEmbeddingsForChunks(chunks, currentIndex + 1, progressFill, progressText, button);
                }, 500); // Small delay between chunks
            } else {
                // Handle error
                progressText.html('<strong style="color: #d63638;"><?php esc_js(__('Error creating embeddings:', 'wp-gpt-rag-chat')); ?></strong><br><span style="color: #d63638;">' + response.data.message + '</span>');
                
                // Restore button to original state
                button.prop('disabled', false)
                      .removeClass('button-secondary')
                      .addClass('button-primary')
                      .css('background-color', '')
                      .css('border-color', '')
                      .text('<?php esc_js(__('Create Embeddings', 'wp-gpt-rag-chat')); ?>');
            }
        }).fail(function() {
            // Handle network error
            progressText.html('<strong style="color: #d63638;"><?php esc_js(__('Network error occurred.', 'wp-gpt-rag-chat')); ?></strong><br><span style="color: #d63638;"><?php esc_js(__('Please try again.', 'wp-gpt-rag-chat')); ?></span>');
            
            // Restore button to original state
            button.prop('disabled', false)
                  .removeClass('button-secondary')
                  .addClass('button-primary')
                  .css('background-color', '')
                  .css('border-color', '')
                  .text('<?php esc_js(__('Create Embeddings', 'wp-gpt-rag-chat')); ?>');
        });
    }
    
    // Function to extract PDF text for preview
    function extractPdfTextPreview(file) {
        var progressText = $('#pdf-import-progress .progress-text');
        var progressFill = $('#pdf-import-progress .progress-fill');
        
        // Show progress
        $('#pdf-import-progress').show();
        progressText.text('<?php esc_js(__('Extracting text from PDF...', 'wp-gpt-rag-chat')); ?>');
        progressFill.css('width', '50%');
        
        // Create FormData for file upload
        var formData = new FormData();
        formData.append('action', 'wp_gpt_rag_chat_extract_pdf_text');
        formData.append('file', file);
        formData.append('nonce', wpGptRagChatAdmin.nonce);
        
        $.ajax({
            url: wpGptRagChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#pdf-import-progress').hide();
                hidePdfPreloader();
                
                if (response.success) {
                    // Store sections data for import
                    window.pdfSections = response.data.sections;
                    window.pdfDocumentInfo = {
                        filename: selectedPdfFile.name,
                        size: selectedPdfFile.size,
                        characterCount: response.data.text.length
                    };
                    
                    // Show chunking interface
                    displayChunkingInterface(response.data.sections);
                    $('#pdf-upload-section').hide();
                    $('#pdf-preview-section').show();
                    
                    // Enable Create Embeddings button
                    $('#create-embeddings').prop('disabled', false);
                } else {
                    CORNUWB.showNotification(
                        '<?php esc_html_e('Extraction Error', 'wp-gpt-rag-chat'); ?>',
                        '<?php esc_html_e('Error extracting text:', 'wp-gpt-rag-chat'); ?> ' + response.data.message,
                        'error'
                    );
                    resetPdfFileSelection();
                }
            },
            error: function() {
                $('#pdf-import-progress').hide();
                hidePdfPreloader();
                CORNUWB.showNotification(
                    '<?php esc_html_e('Extraction Failed', 'wp-gpt-rag-chat'); ?>',
                    '<?php esc_html_e('Error occurred while extracting text from PDF.', 'wp-gpt-rag-chat'); ?>',
                    'error'
                );
                resetPdfFileSelection();
            }
        });
    }
    
    // Function to reset CSV file selection
    function resetCsvFileSelection() {
        selectedCsvFile = null;
        $('#csv-file').val('');
        $('#csv-file-info').hide();
        $('#csv-settings').hide();
        $('#start-csv-import').prop('disabled', true);
        $('#csv-title-column, #csv-content-column').empty().append('<option value=""><?php esc_js(__('Select column...', 'wp-gpt-rag-chat')); ?></option>');
    }
    
    // Function to reset PDF file selection
    function resetPdfFileSelection() {
        selectedPdfFile = null;
        $('#pdf-file').val('');
        $('#pdf-upload-section').show();
        $('#pdf-preview-section').hide();
        $('#extracted-text-preview').text('');
        $('#pdf-import-progress').hide();
        hidePdfPreloader();
    }
    
    
    // Edit extracted text button handler
    $(document).on('click', '#edit-extracted-text', function() {
        var currentText = $('#extracted-text-preview').text();
        var newText = prompt('<?php esc_js(__('Edit the extracted text:', 'wp-gpt-rag-chat')); ?>', currentText);
        if (newText !== null) {
            $('#extracted-text-preview').text(newText);
        }
    });
    
    // Function to parse CSV columns
    function parseCSVColumns(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var csv = e.target.result;
            var lines = csv.split('\n');
            if (lines.length > 0) {
                var headers = lines[0].split(',').map(function(header) {
                    return header.trim().replace(/"/g, '');
                });
                
                var titleSelect = $('#csv-title-column');
                var contentSelect = $('#csv-content-column');
                
                titleSelect.empty().append('<option value=""><?php esc_js(__('Select column...', 'wp-gpt-rag-chat')); ?></option>');
                contentSelect.empty().append('<option value=""><?php esc_js(__('Select column...', 'wp-gpt-rag-chat')); ?></option>');
                
                headers.forEach(function(header, index) {
                    if (header) {
                        titleSelect.append('<option value="' + index + '">' + header + '</option>');
                        contentSelect.append('<option value="' + index + '">' + header + '</option>');
                    }
                });
            }
        };
        reader.readAsText(file);
    }
    
    // Function to start CSV import process
    function startCsvImport(file, progressContainer, button) {
        var progressFill = progressContainer.find('.progress-fill');
        var progressText = progressContainer.find('.progress-text');
        
        // Create FormData for file upload
        var formData = new FormData();
        formData.append('action', 'wp_gpt_rag_chat_import_csv');
        formData.append('file', file);
        formData.append('title_column', $('#csv-title-column').val());
        formData.append('content_column', $('#csv-content-column').val());
        formData.append('nonce', wpGptRagChatAdmin.nonce);
        
        // Start import
        $.ajax({
            url: wpGptRagChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    progressFill.css('width', '100%');
                    progressText.html(
                        '<strong style="color: #00a32a;"><?php esc_js(__('CSV Import Completed!', 'wp-gpt-rag-chat')); ?></strong><br>' +
                        '<span style="color: #646970;">' + response.data.imported + ' items imported successfully</span>'
                    );
                    
                    button.prop('disabled', false).text('<?php esc_js(__('Start CSV Import', 'wp-gpt-rag-chat')); ?>').css('background', '');
                    $('#csv-import-modal').hide();
                    resetCsvFileSelection();
                    
                    // Update the indexed items table
                    updateIndexedItemsTable();
                } else {
                    progressText.html(
                        '<strong style="color: #d63638;"><?php esc_js(__('CSV Import Failed:', 'wp-gpt-rag-chat')); ?></strong><br>' +
                        '<span style="color: #d63638;">' + response.data.message + '</span>'
                    );
                    
                    button.prop('disabled', false).text('<?php esc_js(__('Start CSV Import', 'wp-gpt-rag-chat')); ?>').css('background', '');
                }
            },
            error: function() {
                progressText.html(
                    '<strong style="color: #d63638;"><?php esc_js(__('Error occurred during CSV import.', 'wp-gpt-rag-chat')); ?></strong><br>' +
                    '<span style="color: #d63638;"><?php esc_js(__('Please try again or check your connection.', 'wp-gpt-rag-chat')); ?></span>'
                );
                
                button.prop('disabled', false).text('<?php esc_js(__('Start CSV Import', 'wp-gpt-rag-chat')); ?>').css('background', '');
            }
        });
    }
    
    // Function to start PDF import process
    function startPdfImport(file, progressContainer, button, extractImages, preserveFormatting) {
        var progressFill = progressContainer.find('.progress-fill');
        var progressText = progressContainer.find('.progress-text');
        
        // Get selected chunks
        var selectedChunks = [];
        $('.chunk-checkbox:checked').each(function() {
            var index = $(this).closest('.chunk-item').data('index');
            if (window.pdfSections[index]) {
                selectedChunks.push(window.pdfSections[index]);
            }
        });
        
        if (selectedChunks.length === 0) {
            CORNUWB.showNotification(
                '<?php esc_html_e('No Selection', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Please select at least one chunk to import.', 'wp-gpt-rag-chat'); ?>',
                'warning'
            );
            return;
        }
        
        // Create FormData for file upload
        var formData = new FormData();
        formData.append('action', 'wp_gpt_rag_chat_import_pdf');
        formData.append('file', file);
        formData.append('extracted_sections', JSON.stringify(selectedChunks));
        formData.append('extract_images', extractImages ? '1' : '0');
        formData.append('preserve_formatting', preserveFormatting ? '1' : '0');
        formData.append('nonce', wpGptRagChatAdmin.nonce);
        
        // Update progress text
        progressText.text('<?php esc_js(__('Importing', 'wp-gpt-rag-chat')); ?> ' + selectedChunks.length + ' <?php esc_js(__('chunks...', 'wp-gpt-rag-chat')); ?>');
        
        // Start import
        $.ajax({
            url: wpGptRagChatAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    progressFill.css('width', '100%');
                    progressText.html(
                        '<strong style="color: #00a32a;"><?php esc_js(__('PDF Import Completed!', 'wp-gpt-rag-chat')); ?></strong><br>' +
                        '<span style="color: #646970;">' + response.data.imported + ' items imported successfully</span>'
                    );
                    
                    button.prop('disabled', false).text('<?php esc_js(__('Start PDF Import', 'wp-gpt-rag-chat')); ?>').css('background', '');
                    $('#pdf-import-modal').hide();
                    resetPdfFileSelection();
                    
                    // Update the indexed items table
                    updateIndexedItemsTable();
                } else {
                    progressText.html(
                        '<strong style="color: #d63638;"><?php esc_js(__('PDF Import Failed:', 'wp-gpt-rag-chat')); ?></strong><br>' +
                        '<span style="color: #d63638;">' + response.data.message + '</span>'
                    );
                    
                    button.prop('disabled', false).text('<?php esc_js(__('Start PDF Import', 'wp-gpt-rag-chat')); ?>').css('background', '');
                }
            },
            error: function() {
                progressText.html(
                    '<strong style="color: #d63638;"><?php esc_js(__('Error occurred during PDF import.', 'wp-gpt-rag-chat')); ?></strong><br>' +
                    '<span style="color: #d63638;"><?php esc_js(__('Please try again or check your connection.', 'wp-gpt-rag-chat')); ?></span>'
                );
                
                button.prop('disabled', false).text('<?php esc_js(__('Start PDF Import', 'wp-gpt-rag-chat')); ?>').css('background', '');
            }
        });
    }
    
    // Reindex changed content
    $('#reindex-changed').on('click', function() {
        var button = $(this);
        var progress = $('#reindex-changed-progress');
        
        setButtonBusy(button, true);
        progress.show();
        
        startBulkIndexing('reindex_changed', progress, button);
    });
    
    // Clear all vectors
    $('#clear-all-vectors').on('click', function() {
        if (!confirm('<?php esc_js(__('This will delete ALL vectors from Pinecone and the local database. This action cannot be undone. Continue?', 'wp-gpt-rag-chat')); ?>')) {
            return;
        }
        
        var button = $(this);
        setButtonBusy(button, true);
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_clear_vectors',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                CORNUWB.showNotification('<?php esc_js(__('Vectors Cleared', 'wp-gpt-rag-chat')); ?>', '<?php esc_js(__('All vectors cleared successfully.', 'wp-gpt-rag-chat')); ?>', 'success');
                location.reload();
            } else {
                CORNUWB.showNotification('<?php esc_js(__('Error', 'wp-gpt-rag-chat')); ?>', (response.data && response.data.message) || '<?php esc_js(__('Unexpected error.', 'wp-gpt-rag-chat')); ?>', 'error');
            }
        }).fail(function() {
            CORNUWB.showNotification('<?php esc_js(__('Error', 'wp-gpt-rag-chat')); ?>', '<?php esc_js(__('Error clearing vectors.', 'wp-gpt-rag-chat')); ?>', 'error');
        }).always(function() {
            setButtonBusy(button, false);
        });
    });
    
    // Select all functionality
    $('#select-all-checkbox').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.item-checkbox').prop('checked', isChecked);
        updateSelectAllButton();
    });
    
    $('.item-checkbox').on('change', function() {
        updateSelectAllButton();
    });
    
    function updateSelectAllButton() {
        var totalItems = $('.item-checkbox').length;
        var checkedItems = $('.item-checkbox:checked').length;
        
        if (checkedItems === 0) {
            $('#select-all-items .wpgrc-label').text('<?php esc_js(__('Select All', 'wp-gpt-rag-chat')); ?>');
        } else if (checkedItems === totalItems) {
            $('#select-all-items .wpgrc-label').text('<?php esc_js(__('Deselect All', 'wp-gpt-rag-chat')); ?>');
        } else {
            $('#select-all-items .wpgrc-label').text('<?php esc_js(__('Select All', 'wp-gpt-rag-chat')); ?>');
        }
    }
    
    // Select all button
    $('#select-all-items').on('click', function() {
        var totalItems = $('.item-checkbox').length;
        var checkedItems = $('.item-checkbox:checked').length;
        var shouldCheck = checkedItems < totalItems;
        
        $('.item-checkbox').prop('checked', shouldCheck);
        $('#select-all-checkbox').prop('checked', shouldCheck);
        updateSelectAllButton();
    });
    
    // Bulk reindex selected
    $('#bulk-reindex-selected').on('click', function() {
        var selectedItems = $('.item-checkbox:checked');
        
        if (selectedItems.length === 0) {
            CORNUWB.showNotification('<?php esc_js(__('No Items Selected', 'wp-gpt-rag-chat')); ?>', '<?php esc_js(__('Please select at least one item to reindex.', 'wp-gpt-rag-chat')); ?>', 'warning');
            return;
        }
        
        if (!confirm('<?php esc_js(__('Are you sure you want to reindex the selected items?', 'wp-gpt-rag-chat')); ?>')) {
            return;
        }
        
        var button = $(this);
        var postIds = selectedItems.map(function() { return $(this).val(); }).get();
        
        setButtonBusy(button, true);
        
        // Reindex each item
        var completed = 0;
        var total = postIds.length;
        
        function reindexNext() {
            if (completed >= total) {
                setButtonBusy(button, false);
                CORNUWB.showNotification('<?php esc_js(__('Reindex Complete', 'wp-gpt-rag-chat')); ?>', '<?php esc_js(__('Bulk reindexing completed.', 'wp-gpt-rag-chat')); ?>', 'success');
                location.reload();
                return;
            }
            
            var postId = postIds[completed];
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_reindex',
                post_id: postId,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                completed++;
                reindexNext();
            }).fail(function() {
                completed++;
                reindexNext();
            });
        }
        
        reindexNext();
    });
    
    // Reindex individual post
    $('.reindex-btn').on('click', function() {
        var button = $(this);
        var postId = button.data('post-id');
        var row = button.closest('tr');
        
        button.prop('disabled', true);
        row.addClass('processing');
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_reindex',
            post_id: postId,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                // Update status badge
                var statusBadge = row.find('.status-badge');
                statusBadge.removeClass('status-pending status-outdated').addClass('status-ok');
                statusBadge.find('.status-text').text('OK');
                statusBadge.find('.status-icon').text('✓');
                
                // Update timestamp
                var now = new Date();
                var timestamp = now.getFullYear() + '/' + 
                    String(now.getMonth() + 1).padStart(2, '0') + '/' + 
                    String(now.getDate()).padStart(2, '0') + ' ' + 
                    String(now.getHours()).padStart(2, '0') + ':' + 
                    String(now.getMinutes()).padStart(2, '0') + ':' + 
                    String(now.getSeconds()).padStart(2, '0');
                row.find('.updated-time').text(timestamp);
            } else {
                CORNUWB.showNotification('<?php esc_js(__('Error', 'wp-gpt-rag-chat')); ?>', (response.data && response.data.message) || '<?php esc_js(__('Unexpected error.', 'wp-gpt-rag-chat')); ?>', 'error');
            }
        }).fail(function() {
            CORNUWB.showNotification('<?php esc_js(__('Error', 'wp-gpt-rag-chat')); ?>', '<?php esc_js(__('Error reindexing post.', 'wp-gpt-rag-chat')); ?>', 'error');
        }).always(function() {
            button.prop('disabled', false);
            row.removeClass('processing');
        });
    });
    
    // Delete/Remove from index
    $('.delete-btn').on('click', function() {
        var button = $(this);
        var postId = button.data('post-id');
        var row = button.closest('tr');
        
        // Get item details for preview
        var title = row.find('.item-title strong').text();
        var ref = row.find('.ref-info').text();
        var status = row.find('.status-text').text();
        
        // Populate modal with item details
        $('#delete-item-preview').html(
            '<h4>' + title + '</h4>' +
            '<div class="item-details">' +
                '<strong>Reference:</strong> ' + ref + '<br>' +
                '<strong>Status:</strong> ' + status + '<br>' +
                '<strong>Action:</strong> Remove from index and delete all vectors from Pinecone' +
            '</div>'
        );
        
        // Store current context for deletion
        window.pendingDelete = {
            button: button,
            postId: postId,
            row: row
        };
        
        // Show modal with proper display
        $('#delete-confirmation-modal').addClass('show');
    });
    
    // Modal event handlers
    $('#close-delete-modal, #cancel-delete').on('click', function() {
        $('#delete-confirmation-modal').removeClass('show');
        window.pendingDelete = null;
    });
    
    // Close modal when clicking outside
    $('#delete-confirmation-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('show');
            window.pendingDelete = null;
        }
    });
    
    // Confirm deletion - Using CORNUWB utilities
    $(document).on('click', '.cornuwb-delete-confirm', function() {
        if (!window.pendingDelete) return;
        
        var button = window.pendingDelete.button;
        var postId = window.pendingDelete.postId;
        var row = window.pendingDelete.row;
        var confirmButton = $(this);
        
        // Set loading state using CORNUWB utility (preserves button text)
        CORNUWB.setButtonLoading(confirmButton, true);
        button.prop('disabled', true);
        row.addClass('processing');
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_remove_from_index',
            post_id: postId,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                // Remove the row with animation
                row.fadeOut(300, function() {
                    $(this).remove();
                    
                    // Check if table is empty
                    if ($('.indexed-item-row').length === 0) {
                        $('.indexed-items-table tbody').append(
                            '<tr><td colspan="6" class="no-items-message">' +
                            '<p><?php esc_js(__('No items in the index queue. Posts will appear here after they have been indexed.', 'wp-gpt-rag-chat')); ?></p>' +
                            '</td></tr>'
                        );
                    }
                });
                
                // Close modal
                $('#delete-confirmation-modal').removeClass('show');
            } else {
                alert('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
            }
        }).fail(function() {
            alert('<?php esc_js(__('Error removing from index.', 'wp-gpt-rag-chat')); ?>');
        }).always(function() {
            // Remove loading state using CORNUWB utility
            CORNUWB.setButtonLoading(confirmButton, false);
            button.prop('disabled', false);
            row.removeClass('processing');
            window.pendingDelete = null;
        });
    });
    
    function startBulkIndexing(action, progressContainer, button, postType) {
        var progressFill = progressContainer.find('.progress-fill');
        var progressText = progressContainer.find('.progress-text');
        var isCompleted = false;
        var currentOffset = 0;
        var totalProcessed = 0; // Track cumulative processed items
        var totalItems = 0; // Track total items to process
        
        function updateProgress() {
            if (isCompleted) return;
            
            var ajaxData = {
                action: 'wp_gpt_rag_chat_bulk_index',
                bulk_action: action,
                offset: currentOffset,
                nonce: wpGptRagChatAdmin.nonce
            };
            
            // Add post type if specified
            if (postType && postType !== 'all') {
                ajaxData.post_type = postType;
            }
            
            // Debug log
            console.log('CORNUWB: Sending AJAX request with offset:', currentOffset);
            
            $.post(wpGptRagChatAdmin.ajaxUrl, ajaxData, function(response) {
                console.log('CORNUWB: Received response:', response);
                
                if (response.success) {
                    var data = response.data;
                    console.log('CORNUWB: Response data:', data);
                    
                    // Update totals
                    totalProcessed += data.processed;
                    totalItems = data.total;
                    
                    var percentage = Math.round((totalProcessed / totalItems) * 100);
                    console.log('CORNUWB: Progress: ' + totalProcessed + '/' + totalItems + ' (' + percentage + '%)');
                    
                    // Update progress bar
                    progressFill.css('width', percentage + '%');
                    
                    // Update progress text with detailed information
                    var postTypeText = (postType && postType !== 'all') ? ' (' + postType + ')' : '';
                    progressText.html(
                        '<strong>' + totalProcessed + ' / ' + totalItems + ' items indexed' + postTypeText + '</strong><br>' +
                        '<span style="color: #0073aa;">' + percentage + '% completed</span>'
                    );
                    
                    // Update button text
                    if (button) {
                        if (button.attr('id') === 'sync-all-content') {
                            button.find('span').text('...');
                        } else {
                            if (!button.find('.wpgrc-progress').length) {
                                button.append(' <span class="wpgrc-progress"></span>');
                            }
                            button.find('.wpgrc-progress').text('(' + percentage + '%)');
                        }
                    }
                    
                    // Always add newly indexed items to table in real-time, even if completed
                    if (data.newly_indexed && data.newly_indexed.length > 0) {
                        console.log('CORNUWB: Adding ' + data.newly_indexed.length + ' new items to table', data.newly_indexed);
                        addNewItemsToTable(data.newly_indexed);
                    } else {
                        console.log('CORNUWB: No newly indexed items in this batch');
                    }
                    
                    if (data.completed) {
                        isCompleted = true;
                        
                        // Use the new completion function
                        completeIndexing(button, totalProcessed, postType);
                } else {
                        
                        // Increment offset for next batch
                        currentOffset += 10;
                        
                        // Continue polling every 2 seconds
                        setTimeout(updateProgress, 2000);
                    }
                } else {
                    isCompleted = true;
                    isIndexingInProgress = false;
                    currentIndexingAction = null;
                    
                    progressText.html(
                        '<strong style="color: #d63638;"><?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?></strong><br>' +
                        '<span style="color: #d63638;">' + response.data.message + '</span>'
                    );
                    
                    // Hide stop button
                    $('#stop-indexing').hide();
                    
                    // Reset buttons
                    resetIndexingButtons();
                    
                    // Hide progress after delay
                    setTimeout(function() {
                        $('#global-progress-container').hide();
                    }, 5000);
                }
            }).fail(function() {
                isCompleted = true;
                isIndexingInProgress = false;
                currentIndexingAction = null;
                
                progressText.html(
                    '<strong style="color: #d63638;"><?php esc_js(__('Error occurred during indexing.', 'wp-gpt-rag-chat')); ?></strong><br>' +
                    '<span style="color: #d63638;"><?php esc_js(__('Please try again or check your connection.', 'wp-gpt-rag-chat')); ?></span>'
                );
                
                // Hide stop button
                $('#stop-indexing').hide();
                
                // Reset buttons
                resetIndexingButtons();
                
                // Hide progress after delay
                setTimeout(function() {
                    $('#global-progress-container').hide();
                }, 5000);
            });
        }
        
        // Start the progress tracking
        updateProgress();
    }
    
    // Function to add newly indexed items to the table - CORNUWB real-time updates
    function addNewItemsToTable(newlyIndexedItems) {
        console.log('CORNUWB: addNewItemsToTable called with:', newlyIndexedItems);
        
        if (!newlyIndexedItems || newlyIndexedItems.length === 0) {
            console.log('CORNUWB: No items to add, returning');
            return;
        }
        
        console.log('CORNUWB: Processing', newlyIndexedItems.length, 'items for table update');
        
        var tbody = $('.indexed-items-table tbody');
        console.log('CORNUWB: Found tbody:', tbody.length);
        
        // Remove "no items" message if it exists
        var noItemsRow = tbody.find('.no-items-message').closest('tr');
        if (noItemsRow.length > 0) {
            console.log('CORNUWB: Removing "no items" message');
            noItemsRow.remove();
        }
        
        newlyIndexedItems.forEach(function(item, index) {
            console.log('CORNUWB: Processing item ' + (index + 1) + '/' + newlyIndexedItems.length + ':', item);
            
            // Check if item already exists in table
            var existingRow = $('tr[data-post-id="' + item.id + '"]');
            if (existingRow.length > 0) {
                console.log('CORNUWB: Item already exists, updating:', item.id);
                // Update existing row instead
                existingRow.find('.status-badge').removeClass('status-pending status-outdated').addClass('status-ok');
                existingRow.find('.status-text').text('OK');
                existingRow.find('.status-icon').text('✓');
                
                // Add flash effect
                existingRow.addClass('cornuwb-flash-update');
                setTimeout(function() {
                    existingRow.removeClass('cornuwb-flash-update');
                }, 1000);
                return;
            }
            
            console.log('CORNUWB: Creating new row for item:', item.id);
            
            // Create new table row with "new" indicator
            var row = createIndexedItemRow(item);
            row.addClass('cornuwb-newly-indexed');
            
            // Add to top of table with animation
            row.hide().prependTo(tbody).fadeIn(500);
            
            console.log('CORNUWB: Row added to table');
            
            // Remove "new" indicator after animation
            setTimeout(function() {
                row.removeClass('cornuwb-newly-indexed');
            }, 3000);
        });
        
        // Update checkbox handlers
        $('.item-checkbox').off('change').on('change', function() {
            updateSelectAllButton();
        });

        // Notify filter to re-apply
        $(document).trigger('wpgrc:indexedTableUpdated');
    }

    // Add items with PENDING status to the table
    function addPendingItemsToTable(items){
        console.log('CORNUWB: addPendingItemsToTable called with:', items);
        if(!items || items.length === 0) {
            console.log('CORNUWB: No pending items to add, returning');
            return;
        }
        console.log('CORNUWB: Adding', items.length, 'pending items to table');
        var tbody = $('.indexed-items-table tbody');

        // Remove "no items" message if present
        var noItemsRow = tbody.find('.no-items-message').closest('tr');
        if (noItemsRow.length > 0) noItemsRow.remove();

        items.forEach(function(item){
            // Avoid duplicates
            if ($('tr[data-post-id="' + item.id + '"]').length) return;
            var row = createIndexedItemRow(item);
            // Force pending status visuals
            row.find('.status-badge').removeClass('status-ok status-outdated').addClass('status-pending');
            row.find('.status-text').text('PENDING');
            row.find('.status-icon').text('…');
            row.hide().prependTo(tbody).fadeIn(300);
        });
    }
    
    // Function to create a table row for an indexed item
    function createIndexedItemRow(item) {
        var now = new Date();
        var timestamp = now.getFullYear() + '/' + 
            String(now.getMonth() + 1).padStart(2, '0') + '/' + 
            String(now.getDate()).padStart(2, '0') + ' ' + 
            String(now.getHours()).padStart(2, '0') + ':' + 
            String(now.getMinutes()).padStart(2, '0') + ':' + 
            String(now.getSeconds()).padStart(2, '0');
        
        var row = $('<tr class="indexed-item-row" data-post-id="' + item.id + '">' +
            '<td class="checkbox-column">' +
                '<input type="checkbox" class="item-checkbox" value="' + item.id + '" />' +
            '</td>' +
            '<td class="status-column">' +
                '<span class="status-badge ' + (item.pending ? 'status-pending' : 'status-ok') + '">' +
                    '<span class="status-icon">' + (item.pending ? '…' : '✓') + '</span>' +
                    '<span class="status-text">' + (item.pending ? 'PENDING' : 'OK') + '</span>' +
                '</span>' +
            '</td>' +
            '<td class="title-column">' +
                '<div class="item-title">' +
                    '<strong>' + item.title + '</strong>' +
                    '<div class="embedding-info">' +
                        'text-embedding-3-small, 1536 dimensions' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td class="ref-column">' +
                '<span class="ref-info">ID #' + item.id + ' ' + item.type.toUpperCase() + '</span>' +
            '</td>' +
            '<td class="updated-column">' +
                '<span class="updated-time">' + timestamp + '</span>' +
            '</td>' +
            '<td class="actions-column">' +
                '<div class="action-buttons">' +
                    '<button type="button" class="action-btn edit-btn" title="Edit" onclick="window.open(\'' + item.edit_url + '\', \'_blank\')">' +
                        '<span class="dashicons dashicons-edit"></span>' +
                    '</button>' +
                    '<button type="button" class="action-btn reindex-btn" title="Reindex" data-post-id="' + item.id + '">' +
                        '<span class="dashicons dashicons-update"></span>' +
                    '</button>' +
                    '<button type="button" class="action-btn delete-btn" title="Remove from Index" data-post-id="' + item.id + '">' +
                        '<span class="dashicons dashicons-trash"></span>' +
                    '</button>' +
                '</div>' +
            '</td>' +
        '</tr>');
        
        // Attach event handlers to the new row
        attachRowEventHandlers(row);
        
        return row;
    }
    
    // Function to update the entire indexed items table
    function updateIndexedItemsTable() {
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_indexed_items',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                var tbody = $('.indexed-items-table tbody');
                tbody.empty();
                
                if (response.data.items && response.data.items.length > 0) {
                    response.data.items.forEach(function(item) {
                        var row = createIndexedItemRow(item);
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="6" class="no-items-message"><p><?php esc_js(__('No items in the index queue. Posts will appear here after they have been indexed.', 'wp-gpt-rag-chat')); ?></p></td></tr>');
                }
                
                // Refresh pagination after table update
                if ($('.cornuwb-pagination').length > 0) {
                    cornuwbPagination.refresh();
                }
            }
        });
    }
    
    // Function to update summary statistics
    function updateSummaryStatistics() {
        // Show loading indicators
        $('.cornuwb-stat-loading').fadeIn(200);
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_stats',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                // Update the summary statistics boxes with animation
                updateStatValue('#cornuwb-stat-vectors', response.data.total_vectors || 0);
                updateStatValue('#cornuwb-stat-posts', response.data.total_posts || 0);
                updateStatValue('#cornuwb-stat-activity', response.data.recent_activity || 0);
            }
            
            // Hide loading indicators
            $('.cornuwb-stat-loading').fadeOut(200);
        }).fail(function() {
            // Hide loading indicators on error
            $('.cornuwb-stat-loading').fadeOut(200);
        });
    }
    
    // Helper function to update stat values
    function updateStatValue(selector, newValue) {
        var $element = $(selector);
        $element.text(newValue.toLocaleString());
    }
    
    // Function to attach event handlers to a table row
    function attachRowEventHandlers(row) {
        // Reindex button
        row.find('.reindex-btn').on('click', function() {
            var button = $(this);
            var postId = button.data('post-id');
            var row = button.closest('tr');
            
            button.prop('disabled', true);
            row.addClass('processing');
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_reindex',
                post_id: postId,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    // Update status badge
                    var statusBadge = row.find('.status-badge');
                    statusBadge.removeClass('status-pending status-outdated').addClass('status-ok');
                    statusBadge.find('.status-text').text('OK');
                    statusBadge.find('.status-icon').text('✓');
                    
                    // Update timestamp
                    var now = new Date();
                    var timestamp = now.getFullYear() + '/' + 
                        String(now.getMonth() + 1).padStart(2, '0') + '/' + 
                        String(now.getDate()).padStart(2, '0') + ' ' + 
                        String(now.getHours()).padStart(2, '0') + ':' + 
                        String(now.getMinutes()).padStart(2, '0') + ':' + 
                        String(now.getSeconds()).padStart(2, '0');
                    row.find('.updated-time').text(timestamp);
                } else {
                    alert('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
                }
            }).fail(function() {
                alert('<?php esc_js(__('Error reindexing post.', 'wp-gpt-rag-chat')); ?>');
            }).always(function() {
                button.prop('disabled', false);
                row.removeClass('processing');
            });
        });
        
        // Delete button - use the same modal approach
        row.find('.delete-btn').on('click', function() {
            var button = $(this);
            var postId = button.data('post-id');
            var row = button.closest('tr');
            
            // Get item details for preview
            var title = row.find('.item-title strong').text();
            var ref = row.find('.ref-info').text();
            var status = row.find('.status-text').text();
            
            // Populate modal with item details
            $('#delete-item-preview').html(
                '<h4>' + title + '</h4>' +
                '<div class="item-details">' +
                    '<strong>Reference:</strong> ' + ref + '<br>' +
                    '<strong>Status:</strong> ' + status + '<br>' +
                    '<strong>Action:</strong> Remove from index and delete all vectors from Pinecone' +
                '</div>'
            );
            
            // Store current context for deletion
            window.pendingDelete = {
                button: button,
                postId: postId,
                row: row
            };
            
            // Show modal with proper display
            $('#delete-confirmation-modal').addClass('show');
        });
    }
    
    // ============================================
    // PAGINATION FUNCTIONALITY
    // ============================================
    
    var cornuwbPagination = {
        currentPage: 1,
        itemsPerPage: 20,
        totalItems: 0,
        
        init: function() {
            this.updateTotalItems();
            this.bindEvents();
            this.showPage(); // Show first page immediately
            this.updatePagination();
        },
        
        bindEvents: function() {
            var self = this;
            
            $('#cornuwb-first-page').on('click', function() {
                self.goToPage(1);
            });
            
            $('#cornuwb-prev-page').on('click', function() {
                if (self.currentPage > 1) {
                    self.goToPage(self.currentPage - 1);
                }
            });
            
            $('#cornuwb-next-page').on('click', function() {
                var totalPages = self.getTotalPages();
                if (self.currentPage < totalPages) {
                    self.goToPage(self.currentPage + 1);
                }
            });
            
            $('#cornuwb-last-page').on('click', function() {
                self.goToPage(self.getTotalPages());
            });
            
            $('#cornuwb-items-per-page').on('change', function() {
                self.itemsPerPage = parseInt($(this).val());
                self.goToPage(1);
            });
        },
        
        updateTotalItems: function() {
            this.totalItems = $('.indexed-items-table tbody tr').not('.no-items-message').length;
            $('#cornuwb-total-items').text(this.totalItems);
        },
        
        getTotalPages: function() {
            return Math.max(1, Math.ceil(this.totalItems / this.itemsPerPage));
        },
        
        goToPage: function(page) {
            this.currentPage = page;
            this.showPage();
            this.updatePagination();
        },
        
        showPage: function() {
            var self = this;
            var start = (this.currentPage - 1) * this.itemsPerPage;
            var end = start + this.itemsPerPage;
            
            $('.indexed-items-table tbody tr').not('.no-items-message').each(function(index) {
                if (index >= start && index < end) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            // Update showing info
            var actualEnd = Math.min(end, this.totalItems);
            $('#cornuwb-showing-start').text(this.totalItems > 0 ? start + 1 : 0);
            $('#cornuwb-showing-end').text(actualEnd);
        },
        
        updatePagination: function() {
            var totalPages = this.getTotalPages();
            
            // Update page numbers
            $('#cornuwb-current-page').text(this.currentPage);
            $('#cornuwb-total-pages').text(totalPages);
            $('#cornuwb-total-items').text(this.totalItems);
            
            // Enable/disable buttons
            $('#cornuwb-first-page, #cornuwb-prev-page').prop('disabled', this.currentPage === 1);
            $('#cornuwb-next-page, #cornuwb-last-page').prop('disabled', this.currentPage === totalPages || totalPages === 1);
        },
        
        refresh: function() {
            this.updateTotalItems();
            
            // Adjust current page if needed
            var totalPages = this.getTotalPages();
            if (this.currentPage > totalPages) {
                this.currentPage = totalPages;
            }
            
            this.showPage();
            this.updatePagination();
        }
    };
    
    // Initialize pagination on page load
    if ($('.cornuwb-pagination').length > 0) {
        cornuwbPagination.init();
    }
    
    // Refresh pagination after adding new items
    var originalAddNewItemsToTable = addNewItemsToTable;
    addNewItemsToTable = function(newlyIndexedItems) {
        originalAddNewItemsToTable(newlyIndexedItems);
        if ($('.cornuwb-pagination').length > 0) {
            cornuwbPagination.refresh();
        }
    };
    
    // ============================================
    // STATS CARDS REAL-TIME UPDATE
    // ============================================
    
    var cornuwbStatsUpdater = {
        isUpdating: false,
        
        // Update stats from server
        updateStats: function() {
            var self = this;
            
            if (self.isUpdating) {
                return;
            }
            
            self.isUpdating = true;
            
            // Show loading indicators
            $('.cornuwb-stat-loading').fadeIn(200);
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_get_stats',
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    var stats = response.data;
                    
                    // Update Total Vectors
                    self.updateStatValue('#cornuwb-stat-vectors', stats.total_vectors);
                    
                    // Update Indexed Posts
                    self.updateStatValue('#cornuwb-stat-posts', stats.total_posts);
                    
                    // Update Recent Activity
                    self.updateStatValue('#cornuwb-stat-activity', stats.recent_activity);
                }
            }).fail(function() {
                console.error('CORNUWB: Failed to update stats');
            }).always(function() {
                // Hide loading indicators
                $('.cornuwb-stat-loading').fadeOut(200);
                self.isUpdating = false;
            });
        },
        
        // Update individual stat with animation
        updateStatValue: function(selector, newValue) {
            var $element = $(selector);
            var currentValue = $element.text().replace(/,/g, '');
            var formattedValue = Number(newValue).toLocaleString();
            
            // Only update if value changed
            if (currentValue !== newValue.toString()) {
                $element.addClass('cornuwb-stat-updated');
                $element.text(formattedValue);
                
                // Remove animation class after it completes
                setTimeout(function() {
                    $element.removeClass('cornuwb-stat-updated');
                }, 500);
            }
        },
        
        // Manually trigger update (for testing or manual refresh)
        refresh: function() {
            this.updateStats();
        }
    };
    
    // Auto-update stats after indexing completes
    var originalCompleteIndexing = completeIndexing;
    completeIndexing = function(button, totalCount, postType) {
        originalCompleteIndexing(button, totalCount, postType);
        
        // Update stats after a short delay to ensure database is updated
        setTimeout(function() {
            cornuwbStatsUpdater.updateStats();
        }, 1000);
    };
    
    // Update stats after deleting an item
    var originalDeleteSuccess = $('#confirm-delete').on('click');
    $(document).on('ajaxSuccess', function(event, xhr, settings) {
        // Check if this was a delete request
        if (settings.data && settings.data.indexOf('wp_gpt_rag_chat_remove_from_index') !== -1) {
            setTimeout(function() {
                cornuwbStatsUpdater.updateStats();
            }, 500);
        }
    });
    
    // ============================================
    // DELETE ALL INDEXED ITEMS FUNCTIONALITY
    // ============================================
    
    // Open delete all modal
    $('#delete-all-indexed').on('click', function() {
        var totalItems = $('.indexed-items-table tbody tr').not('.no-items-message').length;
        
        if (totalItems === 0) {
            CORNUWB.showNotification('<?php esc_js(__('Nothing to delete', 'wp-gpt-rag-chat')); ?>', '<?php esc_js(__('There are no indexed items to delete.', 'wp-gpt-rag-chat')); ?>', 'info');
            return;
        }
        
        // Update count in modal
        $('#cornuwb-delete-all-count').text(totalItems);
        
        // Clear input and disable button
        $('#cornuwb-delete-confirmation-text').val('').removeClass('cornuwb-input-valid cornuwb-input-invalid');
        $('#confirm-delete-all').prop('disabled', true);
        
        // Show modal
        $('#delete-all-modal').addClass('show');
    });
    
    // Close delete all modal
    $('#close-delete-all-modal, #cancel-delete-all').on('click', function() {
        $('#delete-all-modal').removeClass('show');
    });
    
    // Click outside modal to close
    $('#delete-all-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('show');
        }
    });
    
    // Validate confirmation input
    $('#cornuwb-delete-confirmation-text').on('input', function() {
        var inputValue = $(this).val().trim();
        var confirmButton = $('#confirm-delete-all');
        
        if (inputValue === 'DELETE') {
            $(this).removeClass('cornuwb-input-invalid').addClass('cornuwb-input-valid');
            confirmButton.prop('disabled', false);
        } else {
            $(this).removeClass('cornuwb-input-valid');
            if (inputValue.length > 0) {
                $(this).addClass('cornuwb-input-invalid');
            }
            confirmButton.prop('disabled', true);
        }
    });
    
    // Confirm delete all
    $('#confirm-delete-all').on('click', function() {
        var button = $(this);
        var input = $('#cornuwb-delete-confirmation-text');
        
        if (input.val().trim() !== 'DELETE') {
            return;
        }
        
        // Get all post IDs
        var postIds = [];
        $('.indexed-items-table tbody tr').not('.no-items-message').each(function() {
            var postId = $(this).data('post-id');
            if (postId) {
                postIds.push(postId);
            }
        });
        
        if (postIds.length === 0) {
            CORNUWB.showNotification('<?php esc_js(__('Nothing to delete', 'wp-gpt-rag-chat')); ?>', '<?php esc_js(__('No items found to delete.', 'wp-gpt-rag-chat')); ?>', 'info');
            return;
        }
        
        // Disable inputs and show loading
        CORNUWB.setButtonLoading(button, true);
        input.prop('disabled', true);
        $('#cancel-delete-all, #close-delete-all-modal').prop('disabled', true);
        
        // Track progress
        var totalItems = postIds.length;
        var processedItems = 0;
        var failedItems = 0;
        
        // Update button text with progress
        function updateProgress() {
            var percentage = Math.round((processedItems / totalItems) * 100);
            button.find('.cornuwb-btn-text').text('<?php esc_js(__('Deleting...', 'wp-gpt-rag-chat')); ?> ' + percentage + '%');
        }
        
        // Delete items one by one
        function deleteNextItem() {
            if (processedItems >= totalItems) {
                // All done
                CORNUWB.setButtonLoading(button, false);
                
                var successCount = totalItems - failedItems;
                console.log('CORNUWB: Delete all completed - totalItems:', totalItems, 'failedItems:', failedItems, 'successCount:', successCount);
                
                // Close the modal first
                $('#delete-all-modal').removeClass('show');
                // Scroll to top so the toast is visible
                $('html, body').animate({ scrollTop: 0 }, 250);
                
                // Show a clear toast message
                if (failedItems === 0) {
                    var successMessage = successCount + ' <?php esc_js(__('items were successfully deleted.', 'wp-gpt-rag-chat')); ?>';
                    console.log('CORNUWB: Showing success notification with message:', successMessage);
                    CORNUWB.showNotification('<?php esc_js(__('Delete Complete', 'wp-gpt-rag-chat')); ?>', successMessage, 'success');
                } else {
                    var errorMessage = successCount + ' <?php esc_js(__('deleted,', 'wp-gpt-rag-chat')); ?> ' + failedItems + ' <?php esc_js(__('failed.', 'wp-gpt-rag-chat')); ?>';
                    console.log('CORNUWB: Showing error notification with message:', errorMessage);
                    CORNUWB.showNotification('<?php esc_js(__('Completed with Errors', 'wp-gpt-rag-chat')); ?>', errorMessage, 'warning');
                }
                // Refresh pagination and table after delete all
                setTimeout(function(){ 
                    updateIndexedItemsTable();
                    updateSummaryStatistics();
                    if ($('.cornuwb-pagination').length > 0) {
                        cornuwbPagination.refresh();
                    }
                }, 500);
                return;
            }
            
            var postId = postIds[processedItems];
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_remove_from_index',
                post_id: postId,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                processedItems++;
                
                if (!response.success) {
                    failedItems++;
                }
                
                // Remove row from table
                $('tr[data-post-id="' + postId + '"]').fadeOut(300, function() {
                    $(this).remove();
                    
                    // Update pagination if needed
                    if ($('.cornuwb-pagination').length > 0) {
                        cornuwbPagination.refresh();
                    }
                });
                
                updateProgress();
                
                // Process next item
                setTimeout(deleteNextItem, 100); // Small delay to prevent overwhelming the server
            }).fail(function() {
                processedItems++;
                failedItems++;
                updateProgress();
                
                // Process next item even if this one failed
                setTimeout(deleteNextItem, 100);
            });
        }
        
        // Start deletion process
        updateProgress();
        deleteNextItem();
    });
    
    // Generate Sitemap
    $('#generate-sitemap-btn').on('click', function() {
        var button = $(this);
        var postType = $('#sitemap-post-type').val();
        
        CORNUWB.setButtonLoading(button, true);
        $('#sitemap-result').hide();
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_generate_sitemap',
            nonce: wpGptRagChatAdmin.nonce,
            post_types: postType,
            download: 'true'
        }, function(response) {
            CORNUWB.setButtonLoading(button, false);
            
            if (response.success) {
                $('#sitemap-message').html(
                    '<strong>' + response.data.message + '</strong><br>' +
                    'Post Types: ' + response.data.post_types.join(', ')
                );
                $('#sitemap-download-link').attr('href', response.data.download_url).show();
                $('#sitemap-result').fadeIn();
                
                CORNUWB.showNotification(
                    '<?php esc_html_e('Sitemap Generated!', 'wp-gpt-rag-chat'); ?>',
                    '<?php esc_html_e('Sitemap generated successfully! Now use "Sync All" above to ensure all content is indexed in Pinecone.', 'wp-gpt-rag-chat'); ?>',
                    'success',
                    '<p style="margin-top: 10px;"><strong><?php esc_html_e('Count:', 'wp-gpt-rag-chat'); ?></strong> ' + response.data.count + ' <?php esc_html_e('URLs', 'wp-gpt-rag-chat'); ?><br>' +
                    '<strong><?php esc_html_e('Post Types:', 'wp-gpt-rag-chat'); ?></strong> ' + response.data.post_types.join(', ') + '</p>'
                );
            } else {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?>',
                    response.data.message,
                    'error'
                );
            }
        }).fail(function() {
            CORNUWB.setButtonLoading(button, false);
            CORNUWB.showNotification(
                '<?php esc_html_e('Request Failed', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('The request failed. Please try again.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
        });
    });
    
    // Generate Sitemap & Start Indexing (Combined action)
    $('#generate-and-index-btn').on('click', function() {
        var button = $(this);
        var postType = $('#sitemap-post-type').val();
        
        if (isIndexingInProgress) {
            CORNUWB.showNotification(
                '<?php esc_html_e('Indexing In Progress', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Indexing is already in progress. Please wait for it to complete.', 'wp-gpt-rag-chat'); ?>',
                'warning'
            );
            return;
        }
        
        CORNUWB.setButtonLoading(button, true);
        $('#sitemap-result').hide();
        
        // Step 1: Generate sitemap
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_generate_sitemap',
            nonce: wpGptRagChatAdmin.nonce,
            post_types: postType,
            download: 'true'
        }, function(response) {
            CORNUWB.setButtonLoading(button, false);
            
            if (response.success) {
                $('#sitemap-message').html(
                    '<strong>' + response.data.message + '</strong><br>' +
                    'Post Types: ' + response.data.post_types.join(', ')
                );
                $('#sitemap-download-link').attr('href', response.data.download_url).show();
                $('#sitemap-result').fadeIn();
                
                // Step 2: Automatically start indexing
                CORNUWB.showNotification(
                    '<?php esc_html_e('Sitemap Generated!', 'wp-gpt-rag-chat'); ?>',
                    '<?php esc_html_e('Sitemap generated successfully! Now starting automatic indexing to Pinecone...', 'wp-gpt-rag-chat'); ?>',
                    'success',
                    '<p style="margin-top: 10px;"><strong><?php esc_html_e('Count:', 'wp-gpt-rag-chat'); ?></strong> ' + response.data.count + ' <?php esc_html_e('URLs', 'wp-gpt-rag-chat'); ?><br>' +
                    '<strong><?php esc_html_e('Starting automatic indexing in 2 seconds...', 'wp-gpt-rag-chat'); ?></strong></p>'
                );
                
                // Wait 2 seconds then trigger sync
                setTimeout(function() {
                    // Close notification
                    $('#notification-modal').fadeOut(200).removeClass('show');
                    
                    // Reset sync state and start
                    syncCancelled = false;
                    currentSyncOffset = 0;
                    totalToSync = 0;
                    isIndexingInProgress = true;
                    
                    // Show emergency stop notice
                    showEmergencyStop('Starting automatic indexing...');
                    
                    // Show progress UI
                    $('#sync-all-content').prop('disabled', true);
                    $('#cancel-sync-all').show();
                    $('#sync-all-progress').fadeIn();
                    $('#sync-progress-message').text('<?php esc_html_e('Starting automatic indexing...', 'wp-gpt-rag-chat'); ?>');
                    $('.progress-fill').css('width', '0%');
                    
                    // Start syncing with the same post type
                    syncNextBatch('index_all', postType);
                }, 2000);
                
            } else {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?>',
                    response.data.message,
                    'error'
                );
            }
        }).fail(function() {
            CORNUWB.setButtonLoading(button, false);
            CORNUWB.showNotification(
                '<?php esc_html_e('Request Failed', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('The request failed. Please try again.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
        });
    });
    
    // View Indexable Content
    $('#view-indexable-content-btn').on('click', function() {
        var button = $(this);
        
        CORNUWB.setButtonLoading(button, true);
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_indexable_content',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            CORNUWB.setButtonLoading(button, false);
            
            if (response.success) {
                // Create modal to display content
                var html = '<div id="content-list-modal" class="modal show" style="display: block;">' +
                    '<div class="modal-content" style="max-width: 90%; width: 900px; max-height: 80vh; overflow-y: auto;">' +
                    '<div class="modal-header">' +
                    '<h3><?php esc_js(__('All Indexable Content', 'wp-gpt-rag-chat')); ?></h3>' +
                    '<button type="button" class="modal-close" id="close-content-modal">&times;</button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                    '<div class="content-stats" style="margin-bottom: 15px; padding: 10px; background: #f0f0f0; border-radius: 4px;">' +
                    '<strong><?php esc_js(__('Total:', 'wp-gpt-rag-chat')); ?></strong> ' + response.data.total + ' | ' +
                    '<strong style="color: green;"><?php esc_js(__('Indexed:', 'wp-gpt-rag-chat')); ?></strong> ' + response.data.indexed + ' | ' +
                    '<strong style="color: orange;"><?php esc_js(__('Not Indexed:', 'wp-gpt-rag-chat')); ?></strong> ' + response.data.unindexed +
                    '</div>' +
                    '<table class="wp-list-table widefat fixed striped">' +
                    '<thead><tr>' +
                    '<th><?php esc_js(__('ID', 'wp-gpt-rag-chat')); ?></th>' +
                    '<th><?php esc_js(__('Title', 'wp-gpt-rag-chat')); ?></th>' +
                    '<th><?php esc_js(__('Type', 'wp-gpt-rag-chat')); ?></th>' +
                    '<th><?php esc_js(__('Status', 'wp-gpt-rag-chat')); ?></th>' +
                    '<th><?php esc_js(__('Indexed', 'wp-gpt-rag-chat')); ?></th>' +
                    '<th><?php esc_js(__('Modified', 'wp-gpt-rag-chat')); ?></th>' +
                    '<th><?php esc_js(__('URL', 'wp-gpt-rag-chat')); ?></th>' +
                    '</tr></thead>' +
                    '<tbody>';
                
                response.data.content.forEach(function(item) {
                    var indexedBadge = item.indexed ? 
                        '<span style="color: green;">✓ <?php esc_js(__('Yes', 'wp-gpt-rag-chat')); ?></span>' : 
                        '<span style="color: orange;">✗ <?php esc_js(__('No', 'wp-gpt-rag-chat')); ?></span>';
                    
                    html += '<tr>' +
                        '<td>' + item.id + '</td>' +
                        '<td><strong>' + item.title + '</strong></td>' +
                        '<td>' + item.type + '</td>' +
                        '<td>' + item.status + '</td>' +
                        '<td>' + indexedBadge + '</td>' +
                        '<td>' + item.modified + '</td>' +
                        '<td><a href="' + item.url + '" target="_blank"><?php esc_js(__('View', 'wp-gpt-rag-chat')); ?></a></td>' +
                        '</tr>';
                });
                
                html += '</tbody></table>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
                
                // Remove existing modal if any
                $('#content-list-modal').remove();
                
                // Add modal to body
                $('body').append(html);
                
                // Close modal handler
                $('#close-content-modal, #content-list-modal').on('click', function(e) {
                    if (e.target === this) {
                        $('#content-list-modal').remove();
                    }
                });
            } else {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?>',
                    response.data.message,
                    'error'
                );
            }
        }).fail(function() {
            CORNUWB.setButtonLoading(button, false);
            CORNUWB.showNotification(
                '<?php esc_html_e('Request Failed', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('The request failed. Please try again.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
        });
    });
    
    // Initialize post counts and event handlers
    // Don't call updatePostCounts() on page load since server-side calculation is working
    // updatePostCounts(); // Load post counts on page load
    
    // Preserve server-side calculated counts
    preserveServerSideCounts();
    
    // Get the initial count from the button's data attribute
    var initialCount = parseInt($('#sync-all-content').attr('data-total-count') || 0);
    console.log('Initial count from data attribute:', initialCount);
    
    // Force set the count immediately
    if (initialCount > 0) {
        forceSetSyncAllCount(initialCount);
    }
    
    // Update sync all button count on page load - multiple attempts
    updateSyncAllCount();
    
    // Try again after a short delay
    setTimeout(function() {
        console.log('Delayed update attempt...');
        updateSyncAllCount();
        if (initialCount > 0) {
            forceSetSyncAllCount(initialCount);
        }
    }, 500);
    
    // Try again after another delay
    setTimeout(function() {
        console.log('Second delayed update attempt...');
        updateSyncAllCount();
        if (initialCount > 0) {
            forceSetSyncAllCount(initialCount);
        }
    }, 1000);
    
    // Test the AJAX endpoint immediately
    console.log('Testing AJAX endpoint...');
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'wp_gpt_rag_chat_get_post_counts',
            nonce: '<?php echo wp_create_nonce('wp_gpt_rag_chat_admin_nonce'); ?>'
        },
        success: function(response) {
            console.log('AJAX test successful:', response);
        },
        error: function(xhr, status, error) {
            console.log('AJAX test failed:', error, xhr.responseText);
        }
    });
    
    // Set up periodic updates for post counts (less frequent since server-side calculation works)
    setInterval(updatePostCounts, 300000); // Update post counts every 5 minutes
    
    // Handle dropdown changes
    $('#index-post-type, #sitemap-post-type').on('change', function() {
        if ($(this).attr('id') === 'index-post-type') {
            updateSyncAllCount();
        }
    });
    
    // ============================================
    // QUEUE PROCESSING FUNCTIONS
    // ============================================
    
    /**
     * Show process queue button
     */
    function showProcessQueueButton() {
        if ($('#process-queue-btn').length === 0) {
            var button = $('<button id="process-queue-btn" class="button button-primary" style="margin-top: 10px;"><?php esc_html_e('Process Queue', 'wp-gpt-rag-chat'); ?></button>');
            button.insertAfter('#sync-all-content');
            
            button.on('click', function() {
                processQueue();
            });
        }
    }
    
    /**
     * Start processing the queue with progress bar
     */
    function startQueueProcessing(button, postType, totalItems, initiallyEnqueued) {
        var processed = initiallyEnqueued || 0; // account for already enqueued items
        var batchSize = 3; // smaller processing batch to avoid timeouts
        var isProcessing = true;
        var totalErrors = 0;
        var isTrulyComplete = false; // Flag to track if indexing is truly complete

        function appendErrors(errors) {
            if (!errors || !errors.length) return;
            var list = $('#sync-error-list');
            var panel = $('#sync-error-panel');
            errors.forEach(function(err){
                var text = (typeof err === 'string') ? err : (err.message || JSON.stringify(err));
                $('<li/>').text(text).appendTo(list);
            });
            totalErrors += errors.length;
            $('#sync-error-count').text('(' + totalErrors + ')');
            if (panel.is(':hidden')) panel.slideDown(200);
        }
        
        function processNextBatch() {
            if (!isProcessing) return;
            
            $.ajax({
                url: wpGptRagChatAdmin.ajaxUrl,
                method: 'POST',
                timeout: 120000,
                data: {
                action: 'wp_gpt_rag_chat_process_queue',
                nonce: wpGptRagChatAdmin.nonce,
                batch_size: batchSize,
                post_type: postType
                }
            }).done(function(response) {
                if (response.success) {
                    processed += response.data.processed || 0;
                    var progress = totalItems > 0 ? (processed / totalItems) * 100 : 0;
                    
                    // Update progress bar
                    $('#global-progress-container .progress-fill').css('width', progress + '%');
                    $('#global-progress-container .progress-text').text(
                        '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: ' + processed + ' / ' + totalItems + 
                        ' (<?php esc_html_e('Remaining', 'wp-gpt-rag-chat'); ?>: ' + (response.data.remaining || 0) + ')'
                    );
                    
                    // Refresh table to show updated statuses
                    updateIndexedItemsTable();
                    
                    // Update summary statistics in real-time
                    updateSummaryStatistics();
                    
                    // Show any server-reported errors
                    if (response.data && response.data.errors && response.data.errors.length) {
                        appendErrors(response.data.errors);
                    }

                    // If queue still has items, continue
                    if (response.data.remaining > 0 && isProcessing) {
                        setTimeout(processNextBatch, 800);
                    } else {
                        // Queue is empty. If we still have more total items to cover, enqueue the next batch.
                        if (processed < totalItems && isProcessing) {
                            $.post(wpGptRagChatAdmin.ajaxUrl, {
                                action: 'wp_gpt_rag_chat_bulk_index',
                                nonce: wpGptRagChatAdmin.nonce,
                                bulk_action: 'index_all',
                                post_type: postType,
                                batch_size: 10,
                                offset: processed
                            }, function(enqueueResp) {
                                if (enqueueResp.success) {
                                    if (enqueueResp.data && enqueueResp.data.errors && enqueueResp.data.errors.length) {
                                        appendErrors(enqueueResp.data.errors);
                                    }
                                    // Show new pending items then resume processing
                                    updateIndexedItemsTable();
                                    setTimeout(processNextBatch, 800);
                                } else {
                                    completeQueueProcessing(button, processed, enqueueResp.data && enqueueResp.data.message ? enqueueResp.data.message : '<?php esc_html_e('Failed to enqueue next batch.', 'wp-gpt-rag-chat'); ?>');
                                }
                            }).fail(function() {
                                completeQueueProcessing(button, processed, '<?php esc_html_e('Connection error while enqueuing next batch', 'wp-gpt-rag-chat'); ?>');
                            });
                        } else {
                            // All done
                            isTrulyComplete = true;
                        completeQueueProcessing(button, processed);
                        }
                    }
                } else {
                    // Error occurred
                    appendErrors(response.data && response.data.errors ? response.data.errors : [response.data && response.data.message ? response.data.message : 'Unknown error']);
                    completeQueueProcessing(button, processed, response.data && response.data.message ? response.data.message : '<?php esc_html_e('Error occurred during processing.', 'wp-gpt-rag-chat'); ?>');
                }
            }).fail(function() {
                // Don't stop on transient connection errors; retry after a short delay
                appendErrors(['<?php esc_html_e('Connection error', 'wp-gpt-rag-chat'); ?>']);
                if (isProcessing) {
                    // Keep progress bar visible and retry
                    setTimeout(processNextBatch, 3000);
                } else {
                    // Only complete if user explicitly cancelled
                completeQueueProcessing(button, processed, '<?php esc_html_e('Connection error', 'wp-gpt-rag-chat'); ?>');
                }
            });
        }
        
        // Start processing
        processNextBatch();
        
        // Handle cancel button
        $('#cancel-sync-all').off('click').on('click', function() {
            isProcessing = false;
            isTrulyComplete = true; // User explicitly cancelled
            completeQueueProcessing(button, processed, '<?php esc_html_e('Cancelled by user', 'wp-gpt-rag-chat'); ?>');
        });
    }
    
    /**
     * Complete queue processing
     */
    function completeQueueProcessing(button, processed, errorMessage) {
        // Check if there are still pending items before hiding progress bar
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_get_queue_status',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success && response.data.pending > 0 && !errorMessage) {
                // Still have pending items and no error, don't hide progress bar
                console.log('Still have pending items, keeping progress bar visible');
                return;
            } else {
                // No pending items or there was an error, safe to hide progress bar
        $('#global-progress-container').fadeOut();
        $('#cancel-sync-all').hide();
        button.prop('disabled', false);
                try { localStorage.removeItem('wpGptRagSyncState'); } catch (e) {}
                // Clear any old monitoring intervals
                if (window.progressMonitoringInterval) {
                    clearInterval(window.progressMonitoringInterval);
                    window.progressMonitoringInterval = null;
                }
                console.log('CORNUWB: Batched indexing completed, cleared localStorage state and monitoring intervals');
        
        if (errorMessage) {
            CORNUWB.showNotification(
                '<?php esc_html_e('Indexing Stopped', 'wp-gpt-rag-chat'); ?>',
                errorMessage,
                'warning'
            );
        } else {
            CORNUWB.showNotification(
                '<?php esc_html_e('Indexing Complete', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Successfully processed', 'wp-gpt-rag-chat'); ?> ' + processed + ' <?php esc_html_e('items.', 'wp-gpt-rag-chat'); ?>',
                'success'
            );
        }
            }
        }).fail(function() {
            // If status check fails, only hide if there was an error or it's truly complete
            if (errorMessage) {
                $('#global-progress-container').fadeOut();
                $('#cancel-sync-all').hide();
                button.prop('disabled', false);
                try { localStorage.removeItem('wpGptRagSyncState'); } catch (e) {}
                // Clear any old monitoring intervals
                if (window.progressMonitoringInterval) {
                    clearInterval(window.progressMonitoringInterval);
                    window.progressMonitoringInterval = null;
                }
                console.log('CORNUWB: Batched indexing stopped due to error, cleared localStorage state and monitoring intervals');
                
                CORNUWB.showNotification(
                    '<?php esc_html_e('Indexing Stopped', 'wp-gpt-rag-chat'); ?>',
                    errorMessage,
                    'warning'
                );
            } else {
                // No error message, keep progress bar visible in case of network issues
                console.log('Status check failed but no error, keeping progress bar visible');
            }
        });
        
        // Final refresh of the table
        updateIndexedItemsTable();
        
        // Update summary statistics one final time
        updateSummaryStatistics();
    }
    
    /**
     * Process the indexing queue
     */
    function processQueue() {
        var button = $('#process-queue-btn');
        button.prop('disabled', true).text('<?php esc_html_e('Processing...', 'wp-gpt-rag-chat'); ?>');
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_process_queue',
            nonce: wpGptRagChatAdmin.nonce,
            batch_size: 5
        }, function(response) {
            if (response.success) {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Queue Processed', 'wp-gpt-rag-chat'); ?>',
                    response.data.message,
                    'success'
                );
                
                // Refresh the table to show updated statuses
                updateIndexedItemsTable();
                
                // Check if there are more items to process
                if (response.data.remaining > 0) {
                    button.prop('disabled', false).text('<?php esc_html_e('Process More', 'wp-gpt-rag-chat'); ?>');
                } else {
                    button.text('<?php esc_html_e('Queue Complete', 'wp-gpt-rag-chat'); ?>');
                }
            } else {
                CORNUWB.showNotification(
                    '<?php esc_html_e('Error', 'wp-gpt-rag-chat'); ?>',
                    response.data.message,
                    'error'
                );
                button.prop('disabled', false).text('<?php esc_html_e('Process Queue', 'wp-gpt-rag-chat'); ?>');
            }
        }).fail(function() {
            CORNUWB.showNotification(
                '<?php esc_html_e('Connection Error', 'wp-gpt-rag-chat'); ?>',
                '<?php esc_html_e('Failed to process queue. Please try again.', 'wp-gpt-rag-chat'); ?>',
                'error'
            );
            button.prop('disabled', false).text('<?php esc_html_e('Process Queue', 'wp-gpt-rag-chat'); ?>');
        });
    }
    
    // ============================================
    // PAGE LOAD: CHECK FOR PERSISTENT INDEXING
    // ============================================
    
    // Load indexed items table on page load
    updateIndexedItemsTable();
    
    // Load summary statistics on page load
    updateSummaryStatistics();
    
    // Check if there are pending items and auto-resume UI if needed
    console.log('CORNUWB: Checking for pending items and saved state on page load');
    $.post(wpGptRagChatAdmin.ajaxUrl, {
        action: 'wp_gpt_rag_chat_get_queue_status',
        nonce: wpGptRagChatAdmin.nonce
    }, function(response) {
        console.log('CORNUWB: Queue status response:', response);
        if (response.success) {
            if (response.data.pending > 0) {
                console.log('CORNUWB: Found pending items:', response.data.pending);
            showProcessQueueButton();
                // If we have a saved sync state, restore progress UI and resume processing
                try {
                    var saved = localStorage.getItem('wpGptRagSyncState');
                    if (saved) {
                        console.log('CORNUWB: Found saved state, restoring progress UI');
                        saved = JSON.parse(saved);
                        var total = saved.total || 0;
                        var postType = saved.postType || $('#index-post-type').val();
                        
                        // Get actual indexed posts count from stats instead of queue stats
                        $.post(wpGptRagChatAdmin.ajaxUrl, {
                            action: 'wp_gpt_rag_chat_get_stats',
                            nonce: wpGptRagChatAdmin.nonce
                        }, function(statsResponse) {
                            if (statsResponse.success) {
                                var actualIndexedCount = statsResponse.data.total_posts || 0;
                                var pending = response.data.pending || 0;
                                var processing = response.data.processing || 0;
                                var failed = response.data.failed || 0;
                                
                                // Calculate current progress based on actual indexed posts
                                // We need to estimate how many were indexed during this session
                                var initialIndexedCount = saved.initialIndexedCount || 0;
                                var currentProgress = Math.max(0, actualIndexedCount - initialIndexedCount);
                                
                                console.log('CORNUWB: Progress calculation - Total:', total, 'Actual Indexed:', actualIndexedCount, 'Initial Indexed:', initialIndexedCount, 'Current Progress:', currentProgress, 'Pending:', pending, 'Processing:', processing, 'Failed:', failed);
                                
                                $('#global-progress-container').show();
                                $('#cancel-sync-all').show();
                                $('#sync-all-content').prop('disabled', true);
                                
                                // Update progress bar with current progress
                                var progressPercentage = total > 0 ? (currentProgress / total) * 100 : 0;
                                $('#global-progress-container .progress-fill').css('width', progressPercentage + '%');
                                $('#global-progress-container .progress-text').text(
                                    '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: ' + currentProgress + ' / ' + total + 
                                    ' (<?php esc_html_e('Remaining', 'wp-gpt-rag-chat'); ?>: ' + (pending + processing) + ')'
                                );
                                
                                // Start processing immediately on load with current progress
                                startQueueProcessing($('#sync-all-content'), postType, total, currentProgress);
                            } else {
                                // Fallback to queue stats if stats request fails
                                var completed = response.data.completed || 0;
                                var pending = response.data.pending || 0;
                                var processing = response.data.processing || 0;
                                var failed = response.data.failed || 0;
                                var currentProgress = completed;
                                
                                console.log('CORNUWB: Stats request failed, using queue stats - Total:', total, 'Completed:', completed, 'Pending:', pending, 'Processing:', processing, 'Failed:', failed);
                                
                                $('#global-progress-container').show();
                                $('#cancel-sync-all').show();
                                $('#sync-all-content').prop('disabled', true);
                                
                                // Update progress bar with current progress
                                var progressPercentage = total > 0 ? (currentProgress / total) * 100 : 0;
                                $('#global-progress-container .progress-fill').css('width', progressPercentage + '%');
                                $('#global-progress-container .progress-text').text(
                                    '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: ' + currentProgress + ' / ' + total + 
                                    ' (<?php esc_html_e('Remaining', 'wp-gpt-rag-chat'); ?>: ' + (pending + processing) + ')'
                                );
                                
                                // Start processing immediately on load with current progress
                                startQueueProcessing($('#sync-all-content'), postType, total, currentProgress);
                            }
                        }).fail(function() {
                            // Fallback to queue stats if stats request fails
                            var completed = response.data.completed || 0;
                            var pending = response.data.pending || 0;
                            var processing = response.data.processing || 0;
                            var failed = response.data.failed || 0;
                            var currentProgress = completed;
                            
                            console.log('CORNUWB: Stats request failed, using queue stats - Total:', total, 'Completed:', completed, 'Pending:', pending, 'Processing:', processing, 'Failed:', failed);
                            
                            $('#global-progress-container').show();
                            $('#cancel-sync-all').show();
                            $('#sync-all-content').prop('disabled', true);
                            
                            // Update progress bar with current progress
                            var progressPercentage = total > 0 ? (currentProgress / total) * 100 : 0;
                            $('#global-progress-container .progress-fill').css('width', progressPercentage + '%');
                            $('#global-progress-container .progress-text').text(
                                '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: ' + currentProgress + ' / ' + total + 
                                ' (<?php esc_html_e('Remaining', 'wp-gpt-rag-chat'); ?>: ' + (pending + processing) + ')'
                            );
                            
                            // Start processing immediately on load with current progress
                            startQueueProcessing($('#sync-all-content'), postType, total, currentProgress);
                        });
                    } else {
                        console.log('CORNUWB: No saved state found');
                    }
                } catch (e) {
                    console.log('CORNUWB: Error parsing saved state:', e);
                }
            } else {
                console.log('CORNUWB: No pending items found');
                // Check if we have saved state but no pending items (all completed)
                try {
                    var saved = localStorage.getItem('wpGptRagSyncState');
                    if (saved) {
                        console.log('CORNUWB: Found saved state but no pending items - likely completed');
                        saved = JSON.parse(saved);
                        var total = saved.total || 0;
                        var postType = saved.postType || $('#index-post-type').val();
                        
                        // Get actual indexed posts count from stats instead of queue stats
                        $.post(wpGptRagChatAdmin.ajaxUrl, {
                            action: 'wp_gpt_rag_chat_get_stats',
                            nonce: wpGptRagChatAdmin.nonce
                        }, function(statsResponse) {
                            if (statsResponse.success) {
                                var actualIndexedCount = statsResponse.data.total_posts || 0;
                                var initialIndexedCount = saved.initialIndexedCount || 0;
                                var currentProgress = Math.max(0, actualIndexedCount - initialIndexedCount);
                                
                                if (currentProgress >= total) {
                                    console.log('CORNUWB: All items completed, clearing saved state');
                                    localStorage.removeItem('wpGptRagSyncState');
                                } else {
                                    console.log('CORNUWB: Some items completed but not all, showing progress');
                                    $('#global-progress-container').show();
                                    $('#cancel-sync-all').show();
                                    $('#sync-all-content').prop('disabled', true);
                                    
                                    // Update progress bar with current progress
                                    var progressPercentage = total > 0 ? (currentProgress / total) * 100 : 0;
                                    $('#global-progress-container .progress-fill').css('width', progressPercentage + '%');
                                    $('#global-progress-container .progress-text').text(
                                        '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: ' + currentProgress + ' / ' + total + 
                                        ' (<?php esc_html_e('Completed', 'wp-gpt-rag-chat'); ?>)'
                                    );
                                    
                                    // Start processing to check for more items
                                    startQueueProcessing($('#sync-all-content'), postType, total, currentProgress);
                                }
                            } else {
                                // Fallback to queue stats
                                var completed = response.data.completed || 0;
                                if (completed >= total) {
                                    console.log('CORNUWB: All items completed, clearing saved state');
                                    localStorage.removeItem('wpGptRagSyncState');
                                } else {
                                    console.log('CORNUWB: Some items completed but not all, showing progress');
                                    $('#global-progress-container').show();
                                    $('#cancel-sync-all').show();
                                    $('#sync-all-content').prop('disabled', true);
                                    
                                    // Update progress bar with current progress
                                    var progressPercentage = total > 0 ? (completed / total) * 100 : 0;
                                    $('#global-progress-container .progress-fill').css('width', progressPercentage + '%');
                                    $('#global-progress-container .progress-text').text(
                                        '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: ' + completed + ' / ' + total + 
                                        ' (<?php esc_html_e('Completed', 'wp-gpt-rag-chat'); ?>)'
                                    );
                                    
                                    // Start processing to check for more items
                                    startQueueProcessing($('#sync-all-content'), postType, total, completed);
                                }
                            }
                        }).fail(function() {
                            // Fallback to queue stats if stats request fails
                            var completed = response.data.completed || 0;
                            if (completed >= total) {
                                console.log('CORNUWB: All items completed, clearing saved state');
                                localStorage.removeItem('wpGptRagSyncState');
                            } else {
                                console.log('CORNUWB: Some items completed but not all, showing progress');
                                $('#global-progress-container').show();
                                $('#cancel-sync-all').show();
                                $('#sync-all-content').prop('disabled', true);
                                
                                // Update progress bar with current progress
                                var progressPercentage = total > 0 ? (completed / total) * 100 : 0;
                                $('#global-progress-container .progress-fill').css('width', progressPercentage + '%');
                                $('#global-progress-container .progress-text').text(
                                    '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: ' + completed + ' / ' + total + 
                                    ' (<?php esc_html_e('Completed', 'wp-gpt-rag-chat'); ?>)'
                                );
                                
                                // Start processing to check for more items
                                startQueueProcessing($('#sync-all-content'), postType, total, completed);
                            }
                        });
                    }
                } catch (e) {
                    console.log('CORNUWB: Error checking saved state when no pending items:', e);
                }
            }
        }
    }).fail(function(){
        console.log('CORNUWB: Queue status check failed, checking for saved state');
        // Even if status check fails (e.g., refresh race), keep the UI visible if we had a saved state
        try {
            var saved = localStorage.getItem('wpGptRagSyncState');
            if (saved) {
                console.log('CORNUWB: Found saved state despite status check failure, restoring progress UI');
                saved = JSON.parse(saved);
                var total = saved.total || 0;
                var postType = saved.postType || $('#index-post-type').val();
                
                $('#global-progress-container').show();
                $('#cancel-sync-all').show();
                $('#sync-all-content').prop('disabled', true);
                
                // Since we can't get queue stats, start with 0 progress but show the UI
                $('#global-progress-container .progress-fill').css('width', '0%');
                $('#global-progress-container .progress-text').text(
                    '<?php esc_html_e('Processing', 'wp-gpt-rag-chat'); ?>: 0 / ' + total + 
                    ' (<?php esc_html_e('Resuming...', 'wp-gpt-rag-chat'); ?>)'
                );
                
                // Try to resume processing even if status check failed
                startQueueProcessing($('#sync-all-content'), postType, total, 0);
            } else {
                console.log('CORNUWB: No saved state found after status check failure');
            }
        } catch (e) {
            console.log('CORNUWB: Error parsing saved state after status check failure:', e);
        }
    });
    
    // Check if persistent indexing is already running on page load
    // Only do this if we're not using the new batched system
    if (!localStorage.getItem('wpGptRagSyncState')) {
        console.log('CORNUWB: No new batched system active, checking for old persistent indexing');
    $.post(wpGptRagChatAdmin.ajaxUrl, {
        action: 'wp_gpt_rag_chat_get_indexing_status',
        nonce: wpGptRagChatAdmin.nonce
    }, function(response) {
        if (response.success && response.data.is_running) {
                console.log('CORNUWB: Old persistent indexing detected on page load, restoring progress bar');
            restoreProgressBarState(response.data);
            } else {
                console.log('CORNUWB: No old persistent indexing running');
        }
    }).fail(function(xhr) {
        console.log('CORNUWB: Persistent indexing status check failed on page load, continuing normally');
        // Don't show error to user, just continue normally
    });
    } else {
        console.log('CORNUWB: New batched system active, skipping old persistent indexing check');
    }
});
</script>

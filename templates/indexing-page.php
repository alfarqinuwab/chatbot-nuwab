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

<div class="wrap">
    <h1><?php esc_html_e('Content Indexing', 'wp-gpt-rag-chat'); ?></h1>
    <div class="indexing-page-container">
    
    <div class="wp-gpt-rag-chat-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php esc_html_e('Total Vectors', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['total_vectors'])); ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Indexed Posts', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['total_posts'])); ?></div>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Recent Activity (24h)', 'wp-gpt-rag-chat'); ?></h3>
                <div class="stat-number"><?php echo esc_html(number_format($stats['recent_activity'])); ?></div>
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
    
    <div class="indexing-layout">
        <div class="wp-gpt-rag-chat-indexed-items">
        <div class="indexed-items-header">
            <h2><?php esc_html_e('Indexed Items', 'wp-gpt-rag-chat'); ?></h2>
            <div class="header-actions">
                <button type="button" class="button button-secondary" id="select-all-items">
                    <?php esc_html_e('Select All', 'wp-gpt-rag-chat'); ?>
                </button>
                <button type="button" class="button button-primary" id="bulk-reindex-selected">
                    <?php esc_html_e('Reindex Selected', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
        </div>
        
        <?php
        // Get posts that are in the index queue (have been indexed or are being processed)
        global $wpdb;
        
        $indexed_posts = $wpdb->get_results("
            SELECT DISTINCT p.ID, p.post_title, p.post_type, p.post_modified
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_status = 'publish'
            AND p.post_type IN ('post', 'page')
            AND pm.meta_key = '_wp_gpt_rag_chat_indexed'
            AND pm.meta_value = '1'
            ORDER BY p.post_modified DESC
            LIMIT 50
        ");
        
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
    </div>
    
    <div class="wp-gpt-rag-chat-bulk-actions">
        <h2><?php esc_html_e('Bulk Actions', 'wp-gpt-rag-chat'); ?></h2>
        
        <div class="bulk-action-section">
            <h3><?php esc_html_e('Index All Content', 'wp-gpt-rag-chat'); ?></h3>
            <p><?php esc_html_e('Index all published posts, pages, and custom post types that are marked for inclusion.', 'wp-gpt-rag-chat'); ?></p>
                
                <div class="post-type-selector">
                    <label for="index-post-type"><?php esc_html_e('Select Post Type:', 'wp-gpt-rag-chat'); ?></label>
                    <select id="index-post-type" class="post-type-dropdown">
                        <option value="all"><?php esc_html_e('All Post Types', 'wp-gpt-rag-chat'); ?></option>
                        <?php
                        $post_types = get_post_types(['public' => true], 'objects');
                        foreach ($post_types as $post_type) {
                            if ($post_type->name !== 'attachment') {
                                echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="sync-buttons">
                    <button type="button" id="sync-all-content" class="button button-primary">
                        <?php esc_html_e('Sync All', 'wp-gpt-rag-chat'); ?> (<span id="sync-all-count">0</span>)
            </button>
                    <button type="button" id="sync-single-post" class="button button-secondary">
                        <?php esc_html_e('Sync Only One Post', 'wp-gpt-rag-chat'); ?>
                    </button>
                </div>
            <div id="index-all-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text"><?php esc_html_e('Preparing...', 'wp-gpt-rag-chat'); ?></div>
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
            <h3><?php esc_html_e('Clear All Vectors', 'wp-gpt-rag-chat'); ?></h3>
            <p><?php esc_html_e('Remove all vectors from Pinecone and local database. This will require a full reindex.', 'wp-gpt-rag-chat'); ?></p>
            <button type="button" id="clear-all-vectors" class="button button-secondary" style="color: #d63638;">
                <?php esc_html_e('Clear All Vectors', 'wp-gpt-rag-chat'); ?>
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
}

.chunking-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
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

/* Column widths */
.checkbox-column {
    width: 40px;
    text-align: center;
}

.status-column {
    width: 120px;
}

.title-column {
    width: auto;
    min-width: 300px;
}

.ref-column {
    width: 150px;
}

.updated-column {
    width: 180px;
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
</style>

<script>
jQuery(document).ready(function($) {
    // Sync all content
    $('#sync-all-content').on('click', function() {
        var button = $(this);
        var progress = $('#index-all-progress');
        var selectedPostType = $('#index-post-type').val();
        
        button.prop('disabled', true).find('span').text('...');
        progress.show();
        
        startBulkIndexing('index_all', progress, button, selectedPostType);
    });
    
    // Sync single post
    $('#sync-single-post').on('click', function() {
        var button = $(this);
        var progress = $('#index-all-progress');
        var selectedPostType = $('#index-post-type').val();
        
        button.prop('disabled', true).text('<?php esc_js(__('Starting...', 'wp-gpt-rag-chat')); ?>');
        progress.show();
        
        startBulkIndexing('index_single', progress, button, selectedPostType);
    });
    
    // Update count when post type changes
    $('#index-post-type').on('change', function() {
        updateSyncAllCount();
    });
    
    // Initial count update
    updateSyncAllCount();
    
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
            alert('<?php esc_js(__('Please select a valid CSV file.', 'wp-gpt-rag-chat')); ?>');
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
            alert('<?php esc_js(__('Please select a valid PDF file.', 'wp-gpt-rag-chat')); ?>');
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
            alert('<?php esc_js(__('Please select at least one chunk to create embeddings.', 'wp-gpt-rag-chat')); ?>');
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
                    alert('<?php esc_js(__('Error extracting text:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
                    resetPdfFileSelection();
                }
            },
            error: function() {
                $('#pdf-import-progress').hide();
                hidePdfPreloader();
                alert('<?php esc_js(__('Error occurred while extracting text from PDF.', 'wp-gpt-rag-chat')); ?>');
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
            alert('<?php esc_js(__('Please select at least one chunk to import.', 'wp-gpt-rag-chat')); ?>');
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
        
        button.prop('disabled', true).text('<?php esc_js(__('Starting...', 'wp-gpt-rag-chat')); ?>');
        progress.show();
        
        startBulkIndexing('reindex_changed', progress, button);
    });
    
    // Clear all vectors
    $('#clear-all-vectors').on('click', function() {
        if (!confirm('<?php esc_js(__('This will delete ALL vectors from Pinecone and the local database. This action cannot be undone. Continue?', 'wp-gpt-rag-chat')); ?>')) {
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).text('<?php esc_js(__('Clearing...', 'wp-gpt-rag-chat')); ?>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_clear_vectors',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('<?php esc_js(__('All vectors cleared successfully.', 'wp-gpt-rag-chat')); ?>');
                location.reload();
            } else {
                alert('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
            }
        }).fail(function() {
            alert('<?php esc_js(__('Error clearing vectors.', 'wp-gpt-rag-chat')); ?>');
        }).always(function() {
            button.prop('disabled', false).text('<?php esc_js(__('Clear All Vectors', 'wp-gpt-rag-chat')); ?>');
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
            $('#select-all-items').text('<?php esc_js(__('Select All', 'wp-gpt-rag-chat')); ?>');
        } else if (checkedItems === totalItems) {
            $('#select-all-items').text('<?php esc_js(__('Deselect All', 'wp-gpt-rag-chat')); ?>');
        } else {
            $('#select-all-items').text('<?php esc_js(__('Select All', 'wp-gpt-rag-chat')); ?>');
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
            alert('<?php esc_js(__('Please select at least one item to reindex.', 'wp-gpt-rag-chat')); ?>');
            return;
        }
        
        if (!confirm('<?php esc_js(__('Are you sure you want to reindex the selected items?', 'wp-gpt-rag-chat')); ?>')) {
            return;
        }
        
        var button = $(this);
        var postIds = selectedItems.map(function() { return $(this).val(); }).get();
        
        button.prop('disabled', true).text('<?php esc_js(__('Reindexing...', 'wp-gpt-rag-chat')); ?>');
        
        // Reindex each item
        var completed = 0;
        var total = postIds.length;
        
        function reindexNext() {
            if (completed >= total) {
                button.prop('disabled', false).text('<?php esc_js(__('Reindex Selected', 'wp-gpt-rag-chat')); ?>');
                alert('<?php esc_js(__('Bulk reindexing completed.', 'wp-gpt-rag-chat')); ?>');
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
                alert('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
            }
        }).fail(function() {
            alert('<?php esc_js(__('Error reindexing post.', 'wp-gpt-rag-chat')); ?>');
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
        
        if (!confirm('<?php esc_js(__('Are you sure you want to remove this item from the index?', 'wp-gpt-rag-chat')); ?>')) {
            return;
        }
        
        button.prop('disabled', true);
        row.addClass('processing');
        
        $.post(wpGptRagChatAdmin.ajaxUrl, {
            action: 'wp_gpt_rag_chat_remove_from_index',
            post_id: postId,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                // Update status badge
                var statusBadge = row.find('.status-badge');
                statusBadge.removeClass('status-ok status-outdated').addClass('status-pending');
                statusBadge.find('.status-text').text('PENDING');
                statusBadge.find('.status-icon').text('⚠');
                
                // Remove embedding info
                row.find('.embedding-info').remove();
            } else {
                alert('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
            }
        }).fail(function() {
            alert('<?php esc_js(__('Error removing from index.', 'wp-gpt-rag-chat')); ?>');
        }).always(function() {
            button.prop('disabled', false);
            row.removeClass('processing');
        });
    });
    
    function startBulkIndexing(action, progressContainer, button, postType) {
        var progressFill = progressContainer.find('.progress-fill');
        var progressText = progressContainer.find('.progress-text');
        var isCompleted = false;
        
        function updateProgress() {
            if (isCompleted) return;
            
            var ajaxData = {
                action: 'wp_gpt_rag_chat_bulk_index',
                bulk_action: action,
                nonce: wpGptRagChatAdmin.nonce
            };
            
            // Add post type if specified
            if (postType && postType !== 'all') {
                ajaxData.post_type = postType;
            }
            
            $.post(wpGptRagChatAdmin.ajaxUrl, ajaxData, function(response) {
                if (response.success) {
                    var data = response.data;
                    var percentage = Math.round((data.processed / data.total) * 100);
                    
                    // Update progress bar
                    progressFill.css('width', percentage + '%');
                    
                    // Update progress text with detailed information
                    var postTypeText = (postType && postType !== 'all') ? ' (' + postType + ')' : '';
                    progressText.html(
                        '<strong>' + data.processed + ' / ' + data.total + ' items indexed' + postTypeText + '</strong><br>' +
                        '<span style="color: #0073aa;">' + percentage + '% completed</span>'
                    );
                    
                    // Update button text
                    if (button) {
                        if (button.attr('id') === 'sync-all-content') {
                            button.find('span').text('...');
                        } else {
                            button.text('<?php esc_js(__('Indexing...', 'wp-gpt-rag-chat')); ?> (' + percentage + '%)');
                        }
                    }
                    
                    if (data.completed) {
                        isCompleted = true;
                        progressFill.css('width', '100%');
                        var postTypeText = (postType && postType !== 'all') ? ' (' + postType + ')' : '';
                        progressText.html(
                            '<strong style="color: #00a32a;"><?php esc_js(__('Indexing Completed!', 'wp-gpt-rag-chat')); ?></strong><br>' +
                            '<span style="color: #646970;">' + data.total + ' items successfully indexed' + postTypeText + '</span>'
                        );
                        
                        if (button) {
                            if (button.attr('id') === 'sync-all-content') {
                                button.prop('disabled', false).css('background', '#00a32a');
                                updateSyncAllCount(); // Refresh the count
                    } else {
                                button.text('<?php esc_js(__('Completed!', 'wp-gpt-rag-chat')); ?>').css('background', '#00a32a');
                    }
                        }
                        
                        // Update the table with any remaining items
                        updateIndexedItemsTable();
                } else {
                        // Update the table with newly indexed items
                        if (data.newly_indexed && data.newly_indexed.length > 0) {
                            addNewItemsToTable(data.newly_indexed);
                        }
                        
                        // Continue polling every 2 seconds
                        setTimeout(updateProgress, 2000);
                    }
                } else {
                    isCompleted = true;
                    progressText.html(
                        '<strong style="color: #d63638;"><?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?></strong><br>' +
                        '<span style="color: #d63638;">' + response.data.message + '</span>'
                    );
                    
                    if (button) {
                        if (button.attr('id') === 'sync-all-content') {
                            button.prop('disabled', false).css('background', '');
                            updateSyncAllCount(); // Refresh the count
                        } else {
                            button.prop('disabled', false).text('<?php esc_js(__('Sync Only One Post', 'wp-gpt-rag-chat')); ?>').css('background', '');
                        }
                    }
                }
            }).fail(function() {
                isCompleted = true;
                progressText.html(
                    '<strong style="color: #d63638;"><?php esc_js(__('Error occurred during indexing.', 'wp-gpt-rag-chat')); ?></strong><br>' +
                    '<span style="color: #d63638;"><?php esc_js(__('Please try again or check your connection.', 'wp-gpt-rag-chat')); ?></span>'
                );
                
                if (button) {
                    if (button.attr('id') === 'sync-all-content') {
                        button.prop('disabled', false).css('background', '');
                        updateSyncAllCount(); // Refresh the count
                    } else {
                        button.prop('disabled', false).text('<?php esc_js(__('Sync Only One Post', 'wp-gpt-rag-chat')); ?>').css('background', '');
                    }
                }
            });
        }
        
        // Start the progress tracking
        updateProgress();
    }
    
    // Function to add newly indexed items to the table
    function addNewItemsToTable(newlyIndexedItems) {
        var tbody = $('.indexed-items-table tbody');
        
        newlyIndexedItems.forEach(function(item) {
            // Check if item already exists in table
            if ($('tr[data-post-id="' + item.id + '"]').length > 0) {
                return; // Skip if already exists
            }
            
            // Create new table row
            var row = createIndexedItemRow(item);
            
            // Add to top of table with animation
            row.hide().prependTo(tbody).fadeIn(500);
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
                '<span class="status-badge status-ok">' +
                    '<span class="status-icon">✓</span>' +
                    '<span class="status-text">OK</span>' +
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
            }
        });
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
        
        // Delete button
        row.find('.delete-btn').on('click', function() {
            var button = $(this);
            var postId = button.data('post-id');
            var row = button.closest('tr');
            
            if (!confirm('<?php esc_js(__('Are you sure you want to remove this item from the index?', 'wp-gpt-rag-chat')); ?>')) {
                return;
            }
            
            button.prop('disabled', true);
            row.addClass('processing');
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_remove_from_index',
                post_id: postId,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    // Update status badge
                    var statusBadge = row.find('.status-badge');
                    statusBadge.removeClass('status-ok status-outdated').addClass('status-pending');
                    statusBadge.find('.status-text').text('PENDING');
                    statusBadge.find('.status-icon').text('⚠');
                    
                    // Remove embedding info
                    row.find('.embedding-info').remove();
                } else {
                    alert('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
                }
            }).fail(function() {
                alert('<?php esc_js(__('Error removing from index.', 'wp-gpt-rag-chat')); ?>');
            }).always(function() {
                button.prop('disabled', false);
                row.removeClass('processing');
            });
        });
    }
});
</script>

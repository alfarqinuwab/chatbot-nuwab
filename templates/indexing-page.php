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
    
    <div class="wp-gpt-rag-chat-bulk-actions">
        <h2><?php esc_html_e('Bulk Actions', 'wp-gpt-rag-chat'); ?></h2>
        
        <div class="bulk-action-section">
            <h3><?php esc_html_e('Index All Content', 'wp-gpt-rag-chat'); ?></h3>
            <p><?php esc_html_e('Index all published posts, pages, and custom post types that are marked for inclusion.', 'wp-gpt-rag-chat'); ?></p>
            <button type="button" id="index-all-content" class="button button-primary">
                <?php esc_html_e('Start Full Indexing', 'wp-gpt-rag-chat'); ?>
            </button>
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
    </div>
    
    <div class="wp-gpt-rag-chat-post-list">
        <h2><?php esc_html_e('Recent Posts', 'wp-gpt-rag-chat'); ?></h2>
        
        <?php
        $posts = get_posts([
            'numberposts' => 20,
            'post_status' => 'publish',
            'post_type' => ['post', 'page'],
            'meta_query' => [
                [
                    'key' => '_wp_gpt_rag_chat_include',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ]);
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php esc_html_e('Type', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php esc_html_e('Status', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php esc_html_e('Vectors', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php esc_html_e('Last Updated', 'wp-gpt-rag-chat'); ?></th>
                    <th><?php esc_html_e('Actions', 'wp-gpt-rag-chat'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <?php $status = WP_GPT_RAG_Chat\Admin::get_post_indexing_status($post->ID); ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($post->post_title); ?></strong>
                        <div class="row-actions">
                            <span class="edit">
                                <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>">
                                    <?php esc_html_e('Edit', 'wp-gpt-rag-chat'); ?>
                                </a>
                            </span>
                        </div>
                    </td>
                    <td><?php echo esc_html(get_post_type_object($post->post_type)->labels->singular_name ?? $post->post_type); ?></td>
                    <td>
                        <?php if ($status['vector_count'] > 0): ?>
                            <span class="status-indexed"><?php esc_html_e('Indexed', 'wp-gpt-rag-chat'); ?></span>
                        <?php else: ?>
                            <span class="status-not-indexed"><?php esc_html_e('Not Indexed', 'wp-gpt-rag-chat'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($status['vector_count']); ?></td>
                    <td>
                        <?php if ($status['last_updated']): ?>
                            <?php echo esc_html(human_time_diff(strtotime($status['last_updated'])) . ' ' . __('ago', 'wp-gpt-rag-chat')); ?>
                        <?php else: ?>
                            <?php esc_html_e('Never', 'wp-gpt-rag-chat'); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="button button-small reindex-post" data-post-id="<?php echo esc_attr($post->ID); ?>">
                            <?php esc_html_e('Reindex', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
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
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.bulk-action-section h3 {
    margin-top: 0;
    color: #1d2327;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f1;
    border-radius: 10px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: #2271b1;
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    text-align: center;
    font-size: 14px;
    color: #646970;
}

.status-indexed {
    color: #00a32a;
    font-weight: 500;
}

.status-not-indexed {
    color: #d63638;
    font-weight: 500;
}

.wp-gpt-rag-chat-post-list {
    margin-top: 30px;
}

.wp-gpt-rag-chat-post-list .wp-list-table {
    margin-top: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Index all content
    $('#index-all-content').on('click', function() {
        if (!confirm('<?php esc_js(__('This will index all content. This may take a while and consume API credits. Continue?', 'wp-gpt-rag-chat')); ?>')) {
            return;
        }
        
        var button = $(this);
        var progress = $('#index-all-progress');
        
        button.prop('disabled', true);
        progress.show();
        
        startBulkIndexing('index_all', progress);
    });
    
    // Reindex changed content
    $('#reindex-changed').on('click', function() {
        var button = $(this);
        var progress = $('#reindex-changed-progress');
        
        button.prop('disabled', true);
        progress.show();
        
        startBulkIndexing('reindex_changed', progress);
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
    
    // Reindex individual post
    $('.reindex-post').on('click', function() {
        var button = $(this);
        var postId = button.data('post-id');
        
        button.prop('disabled', true).text('<?php esc_js(__('Reindexing...', 'wp-gpt-rag-chat')); ?>');
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_reindex',
            post_id: postId,
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                alert('<?php esc_js(__('Post reindexed successfully.', 'wp-gpt-rag-chat')); ?>');
                location.reload();
            } else {
                alert('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
            }
        }).fail(function() {
            alert('<?php esc_js(__('Error reindexing post.', 'wp-gpt-rag-chat')); ?>');
        }).always(function() {
            button.prop('disabled', false).text('<?php esc_js(__('Reindex', 'wp-gpt-rag-chat')); ?>');
        });
    });
    
    function startBulkIndexing(action, progressContainer) {
        var progressFill = progressContainer.find('.progress-fill');
        var progressText = progressContainer.find('.progress-text');
        
        function updateProgress() {
            $.post(ajaxurl, {
                action: 'wp_gpt_rag_chat_bulk_index',
                bulk_action: action,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    var data = response.data;
                    var percentage = Math.round((data.processed / data.total) * 100);
                    
                    progressFill.css('width', percentage + '%');
                    progressText.text(data.processed + ' / ' + data.total + ' (' + percentage + '%)');
                    
                    if (data.completed) {
                        progressText.text('<?php esc_js(__('Completed!', 'wp-gpt-rag-chat')); ?>');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        setTimeout(updateProgress, 1000);
                    }
                } else {
                    progressText.text('<?php esc_js(__('Error:', 'wp-gpt-rag-chat')); ?> ' + response.data.message);
                }
            }).fail(function() {
                progressText.text('<?php esc_js(__('Error occurred during indexing.', 'wp-gpt-rag-chat')); ?>');
            });
        }
        
        updateProgress();
    }
});
</script>

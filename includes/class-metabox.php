<?php

namespace WP_GPT_RAG_Chat;

/**
 * Metabox functionality class
 */
class Metabox {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_metabox_scripts']);
    }
    
    /**
     * Add metabox to post edit screens
     */
    public function add_metabox() {
        $post_types = get_post_types(['public' => true], 'names');
        
        foreach ($post_types as $post_type) {
            if ($post_type === 'attachment') {
                continue;
            }
            
            add_meta_box(
                'wp_gpt_rag_chat_metabox',
                __('GPT RAG Chat', 'wp-gpt-rag-chat'),
                [$this, 'metabox_callback'],
                $post_type,
                'side',
                'default'
            );
        }
    }
    
    /**
     * Metabox callback
     */
    public function metabox_callback($post) {
        wp_nonce_field('wp_gpt_rag_chat_metabox', 'wp_gpt_rag_chat_metabox_nonce');
        
        $include = get_post_meta($post->ID, '_wp_gpt_rag_chat_include', true);
        $include = $include === '' ? true : (bool) $include;
        
        $status = Admin::get_post_indexing_status($post->ID);
        
        ?>
        <div class="wp-gpt-rag-chat-metabox">
            <p>
                <label>
                    <input type="checkbox" name="wp_gpt_rag_chat_include" value="1" <?php checked($include, true); ?> />
                    <?php esc_html_e('Include in RAG Chat', 'wp-gpt-rag-chat'); ?>
                </label>
            </p>
            
            <div class="indexing-status">
                <h4><?php esc_html_e('Indexing Status', 'wp-gpt-rag-chat'); ?></h4>
                
                <div class="status-item">
                    <strong><?php esc_html_e('Vectors:', 'wp-gpt-rag-chat'); ?></strong>
                    <span class="vector-count"><?php echo esc_html($status['vector_count']); ?></span>
                </div>
                
                <div class="status-item">
                    <strong><?php esc_html_e('Last Updated:', 'wp-gpt-rag-chat'); ?></strong>
                    <span class="last-updated">
                        <?php if ($status['last_updated']): ?>
                            <?php echo esc_html(human_time_diff(strtotime($status['last_updated'])) . ' ' . __('ago', 'wp-gpt-rag-chat')); ?>
                        <?php else: ?>
                            <?php esc_html_e('Never', 'wp-gpt-rag-chat'); ?>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="status-indicator">
                    <?php if ($status['vector_count'] > 0): ?>
                        <span class="status-indexed">✓ <?php esc_html_e('Indexed', 'wp-gpt-rag-chat'); ?></span>
                    <?php else: ?>
                        <span class="status-not-indexed">⚠ <?php esc_html_e('Not Indexed', 'wp-gpt-rag-chat'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="metabox-actions">
                <button type="button" class="button button-small reindex-post" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <?php esc_html_e('Reindex Now', 'wp-gpt-rag-chat'); ?>
                </button>
                
                <button type="button" class="button button-small refresh-status" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <?php esc_html_e('Refresh Status', 'wp-gpt-rag-chat'); ?>
                </button>
            </div>
            
            <div class="metabox-info">
                <p class="description">
                    <?php esc_html_e('This post will be automatically indexed when saved if "Include in RAG Chat" is checked.', 'wp-gpt-rag-chat'); ?>
                </p>
            </div>
        </div>
        
        <style>
        .wp-gpt-rag-chat-metabox {
            font-size: 13px;
        }
        
        .wp-gpt-rag-chat-metabox h4 {
            margin: 15px 0 10px 0;
            font-size: 13px;
            color: #1d2327;
        }
        
        .indexing-status {
            background: #f6f7f7;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 12px;
            margin: 15px 0;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .status-item:last-child {
            margin-bottom: 0;
        }
        
        .status-indicator {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #c3c4c7;
        }
        
        .status-indexed {
            color: #00a32a;
            font-weight: 500;
        }
        
        .status-not-indexed {
            color: #d63638;
            font-weight: 500;
        }
        
        .metabox-actions {
            margin: 15px 0;
        }
        
        .metabox-actions .button {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .metabox-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #c3c4c7;
        }
        
        .metabox-info .description {
            margin: 0;
            font-style: italic;
            color: #646970;
        }
        </style>
        <?php
    }
    
    /**
     * Save metabox data
     */
    public function save_metabox($post_id, $post) {
        // ⚠️ EMERGENCY STOP CHECK - MUST BE FIRST!
        if (get_transient('wp_gpt_rag_emergency_stop')) {
            return; // Block all indexing when emergency stop is active
        }
        
        // Check nonce
        if (!isset($_POST['wp_gpt_rag_chat_metabox_nonce']) || 
            !wp_verify_nonce($_POST['wp_gpt_rag_chat_metabox_nonce'], 'wp_gpt_rag_chat_metabox')) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save include setting
        $include = isset($_POST['wp_gpt_rag_chat_include']) ? (bool) $_POST['wp_gpt_rag_chat_include'] : false;
        update_post_meta($post_id, '_wp_gpt_rag_chat_include', $include);
        
        // If post is published and should be included, schedule indexing
        if (in_array($post->post_status, ['publish', 'private']) && $include) {
            wp_schedule_single_event(time() + 30, 'wp_gpt_rag_chat_index_content', [$post_id]);
        }
    }
    
    /**
     * Enqueue metabox scripts
     */
    public function enqueue_metabox_scripts($hook) {
        global $post;
        
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }
        
        if (!$post || !in_array($post->post_type, get_post_types(['public' => true]))) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'wpGptRagChatMetabox', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_gpt_rag_chat_admin_nonce'),
            'strings' => [
                'reindexing' => __('Reindexing...', 'wp-gpt-rag-chat'),
                'refreshing' => __('Refreshing...', 'wp-gpt-rag-chat'),
                'success' => __('Success!', 'wp-gpt-rag-chat'),
                'error' => __('Error:', 'wp-gpt-rag-chat'),
            ]
        ]);
        
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Reindex post
            $('.reindex-post').on('click', function() {
                var button = $(this);
                var postId = button.data('post-id');
                var originalText = button.text();
                
                button.prop('disabled', true).text(wpGptRagChatMetabox.strings.reindexing);
                
                $.post(wpGptRagChatMetabox.ajaxUrl, {
                    action: 'wp_gpt_rag_chat_reindex',
                    post_id: postId,
                    nonce: wpGptRagChatMetabox.nonce
                }, function(response) {
                    if (response.success) {
                        alert(wpGptRagChatMetabox.strings.success + ' ' + response.data.message);
                        refreshStatus(postId);
                    } else {
                        alert(wpGptRagChatMetabox.strings.error + ' ' + response.data.message);
                    }
                }).fail(function() {
                    alert(wpGptRagChatMetabox.strings.error + ' ' + '<?php esc_js(__('Request failed.', 'wp-gpt-rag-chat')); ?>');
                }).always(function() {
                    button.prop('disabled', false).text(originalText);
                });
            });
            
            // Refresh status
            $('.refresh-status').on('click', function() {
                var button = $(this);
                var postId = button.data('post-id');
                var originalText = button.text();
                
                button.prop('disabled', true).text(wpGptRagChatMetabox.strings.refreshing);
                
                refreshStatus(postId);
                
                setTimeout(function() {
                    button.prop('disabled', false).text(originalText);
                }, 1000);
            });
            
            function refreshStatus(postId) {
                $.post(wpGptRagChatMetabox.ajaxUrl, {
                    action: 'wp_gpt_rag_chat_get_post_status',
                    post_id: postId,
                    nonce: wpGptRagChatMetabox.nonce
                }, function(response) {
                    if (response.success) {
                        var data = response.data;
                        
                        // Update vector count
                        $('.vector-count').text(data.vector_count);
                        
                        // Update last updated
                        if (data.last_updated) {
                            $('.last-updated').text(data.last_updated + ' <?php esc_js(__('ago', 'wp-gpt-rag-chat')); ?>');
                        } else {
                            $('.last-updated').text('<?php esc_js(__('Never', 'wp-gpt-rag-chat')); ?>');
                        }
                        
                        // Update status indicator
                        if (data.vector_count > 0) {
                            $('.status-indicator span').removeClass('status-not-indexed').addClass('status-indexed')
                                .html('✓ <?php esc_js(__('Indexed', 'wp-gpt-rag-chat')); ?>');
                        } else {
                            $('.status-indicator span').removeClass('status-indexed').addClass('status-not-indexed')
                                .html('⚠ <?php esc_js(__('Not Indexed', 'wp-gpt-rag-chat')); ?>');
                        }
                    }
                });
            }
        });
        </script>
        <?php
    }
}

<?php
/**
 * Conversation View Page - View full conversation by chat_id
 */

if (!defined('ABSPATH')) {
    exit;
}

use WP_GPT_RAG_Chat\Analytics;

$analytics = new Analytics();
$chat_id = $_GET['chat_id'] ?? '';

if (empty($chat_id)) {
    echo '<div class="wrap cornuwab-admin-wrap"><h1>' . __('Invalid Conversation', 'wp-gpt-rag-chat') . '</h1>';
    echo '<p>' . __('No chat ID provided.', 'wp-gpt-rag-chat') . '</p></div>';
    return;
}

$conversation = $analytics->get_conversation($chat_id);

if (empty($conversation)) {
    echo '<div class="wrap cornuwab-admin-wrap"><h1>' . __('Conversation Not Found', 'wp-gpt-rag-chat') . '</h1>';
    echo '<p>' . __('This conversation could not be found.', 'wp-gpt-rag-chat') . '</p></div>';
    return;
}

$first_message = $conversation[0];
$user_display = $first_message->user_id ? get_userdata($first_message->user_id)->display_name : 'Guest';
?>

<div class="wrap wp-gpt-rag-conversation-view">
    <h1><?php _e('Conversation Details', 'wp-gpt-rag-chat'); ?></h1>
    
    <div class="conversation-meta">
        <p>
            <strong><?php _e('Chat ID:', 'wp-gpt-rag-chat'); ?></strong> <?php echo esc_html($chat_id); ?><br>
            <strong><?php _e('User:', 'wp-gpt-rag-chat'); ?></strong> <?php echo esc_html($user_display); ?><br>
            <strong><?php _e('Started:', 'wp-gpt-rag-chat'); ?></strong> <?php echo esc_html(date('Y-m-d H:i:s', strtotime($first_message->created_at))); ?><br>
            <strong><?php _e('Total Turns:', 'wp-gpt-rag-chat'); ?></strong> <?php echo count(array_filter($conversation, function($m) { return $m->role === 'user'; })); ?>
        </p>
        <a href="?page=wp-gpt-rag-chat-analytics&tab=logs" class="button">&larr; <?php _e('Back to Logs', 'wp-gpt-rag-chat'); ?></a>
    </div>
    
    <div class="conversation-messages">
        <?php foreach ($conversation as $message): ?>
            <div class="conversation-message conversation-message-<?php echo esc_attr($message->role); ?>">
                <div class="message-header">
                    <span class="message-role <?php echo $message->role === 'user' ? 'role-user' : 'role-assistant'; ?>">
                        <?php echo esc_html(ucfirst($message->role)); ?>
                    </span>
                    <span class="message-time"><?php echo esc_html(date('H:i:s', strtotime($message->created_at))); ?></span>
                    <?php if ($message->role === 'assistant' && $message->response_latency): ?>
                        <span class="message-latency" title="<?php _e('Response Time', 'wp-gpt-rag-chat'); ?>">
                            ⏱️ <?php echo esc_html($message->response_latency); ?>ms
                        </span>
                    <?php endif; ?>
                    <?php if ($message->rating): ?>
                        <span class="message-rating-display">
                            <?php echo $message->rating == 1 ? '👍' : '👎'; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="message-content">
                    <?php echo nl2br(esc_html($message->content)); ?>
                </div>
                
                <?php if (isset($message->rag_metadata) && $message->rag_metadata && $message->role === 'user'): ?>
                    <?php $metadata = json_decode($message->rag_metadata, true); ?>
                    <?php if (!empty($metadata)): ?>
                        <div class="message-rag-metadata">
                            <details class="rag-details">
                                <summary class="rag-summary">
                                    <span class="dashicons dashicons-search"></span>
                                    <?php _e('Query Processing Details (RAG)', 'wp-gpt-rag-chat'); ?>
                                </summary>
                                <div class="rag-content">
                                    <?php if (!empty($metadata['query_variations'])): ?>
                                        <div class="rag-section">
                                            <strong><?php _e('Query Variations:', 'wp-gpt-rag-chat'); ?></strong>
                                            <span class="rag-status-badge <?php echo ($metadata['query_expansion_enabled'] ?? false) ? 'status-enabled' : 'status-disabled'; ?>">
                                                <?php echo ($metadata['query_expansion_enabled'] ?? false) ? __('Enabled', 'wp-gpt-rag-chat') : __('Disabled', 'wp-gpt-rag-chat'); ?>
                                            </span>
                                            <ol class="query-variations-list">
                                                <?php foreach ($metadata['query_variations'] as $i => $variation): ?>
                                                    <li <?php if ($i === 0): ?>class="original-query"<?php endif; ?>>
                                                        <?php echo esc_html($variation); ?>
                                                        <?php if ($i === 0): ?>
                                                            <span class="badge-original"><?php _e('Original', 'wp-gpt-rag-chat'); ?></span>
                                                        <?php else: ?>
                                                            <span class="badge-variation"><?php _e('Variation', 'wp-gpt-rag-chat'); ?> <?php echo $i; ?></span>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ol>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="rag-section rag-stats">
                                        <div class="rag-stat">
                                            <span class="stat-label"><?php _e('Results Found:', 'wp-gpt-rag-chat'); ?></span>
                                            <span class="stat-value"><?php echo esc_html($metadata['total_results_found'] ?? 0); ?></span>
                                        </div>
                                        <div class="rag-stat">
                                            <span class="stat-label"><?php _e('After Deduplication:', 'wp-gpt-rag-chat'); ?></span>
                                            <span class="stat-value"><?php echo esc_html($metadata['unique_results'] ?? 0); ?></span>
                                        </div>
                                        <div class="rag-stat">
                                            <span class="stat-label"><?php _e('Used in Answer:', 'wp-gpt-rag-chat'); ?></span>
                                            <span class="stat-value"><?php echo esc_html($metadata['final_results_used'] ?? 0); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="rag-section rag-features">
                                        <strong><?php _e('RAG Features:', 'wp-gpt-rag-chat'); ?></strong>
                                        <div class="rag-feature">
                                            <?php if ($metadata['reranking_enabled'] ?? false): ?>
                                                ✅ <?php _e('Re-ranking', 'wp-gpt-rag-chat'); ?>
                                            <?php else: ?>
                                                ❌ <?php _e('Re-ranking', 'wp-gpt-rag-chat'); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="rag-feature">
                                            <?php if ($metadata['few_shot_enabled'] ?? false): ?>
                                                ✅ <?php _e('Few-shot Learning', 'wp-gpt-rag-chat'); ?>
                                                (<?php echo esc_html($metadata['few_shot_examples_count'] ?? 0); ?> <?php _e('examples', 'wp-gpt-rag-chat'); ?>)
                                            <?php else: ?>
                                                ❌ <?php _e('Few-shot Learning', 'wp-gpt-rag-chat'); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($message->tags): ?>
                    <div class="message-tags">
                        <strong><?php _e('Tags:', 'wp-gpt-rag-chat'); ?></strong>
                        <?php foreach (explode(',', $message->tags) as $tag): ?>
                            <span class="tag-badge"><?php echo esc_html(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($message->rag_sources && $message->role === 'assistant'): ?>
                    <?php $sources = json_decode($message->rag_sources, true); ?>
                    <?php if (!empty($sources)): ?>
                        <div class="message-sources">
                            <strong><?php _e('RAG Sources:', 'wp-gpt-rag-chat'); ?></strong>
                            <ul>
                                <?php foreach ($sources as $source): ?>
                                    <li>
                                        <a href="<?php echo esc_url($source['url'] ?? '#'); ?>" target="_blank">
                                            <?php echo esc_html($source['title'] ?? __('Unknown Source', 'wp-gpt-rag-chat')); ?>
                                        </a>
                                        <?php if (isset($source['score'])): ?>
                                            <span class="source-score">(Score: <?php echo number_format($source['score'], 2); ?>)</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($message->model_used && $message->role === 'assistant'): ?>
                    <div class="message-model">
                        <small><strong><?php _e('Model:', 'wp-gpt-rag-chat'); ?></strong> <?php echo esc_html($message->model_used); ?></small>
                        <?php if ($message->tokens_used): ?>
                            | <strong><?php _e('Tokens:', 'wp-gpt-rag-chat'); ?></strong> <?php echo esc_html(number_format($message->tokens_used)); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.wp-gpt-rag-conversation-view {
    max-width: 900px;
}

.conversation-meta {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    margin: 20px 0;
}

.conversation-messages {
    background: #f5f5f5;
    padding: 20px;
    border: 1px solid #ccd0d4;
}

.conversation-message {
    background: #fff;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
}

.conversation-message-user {
    border-right: 4px solid #d1a85f;
}

.conversation-message-assistant {
    border-left: 4px solid #e0e0e0;
}

.message-header {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.message-role {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}

.role-user {
    background: #d1a85f;
    color: #fff;
}

.role-assistant {
    background: #e0e0e0;
    color: #333;
}

.message-time {
    color: #666;
    font-size: 12px;
}

.message-latency {
    color: #d1a85f;
    font-size: 12px;
    font-weight: 600;
}

.message-rating-display {
    font-size: 16px;
}

.message-content {
    padding: 10px 0;
    line-height: 1.6;
    direction: rtl;
    text-align: right;
}

.message-tags {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f0f0f0;
}

.tag-badge {
    display: inline-block;
    padding: 3px 8px;
    background: #f0f0f0;
    border-radius: 3px;
    font-size: 11px;
    margin-right: 5px;
}

.message-sources {
    margin-top: 10px;
    padding: 10px;
    background: #fef9f0;
    border: 1px solid #f0e6d6;
    border-radius: 4px;
}

.message-sources ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

.message-sources li {
    margin: 5px 0;
}

.source-score {
    color: #666;
    font-size: 12px;
}

.message-model {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f0f0f0;
    color: #666;
    font-size: 12px;
}

/* RAG Metadata Styles */
.message-rag-metadata {
    margin-top: 15px;
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 6px;
    overflow: hidden;
}

.rag-details {
    margin: 0;
}

.rag-summary {
    padding: 12px 16px;
    cursor: pointer;
    user-select: none;
    background: #f0f2f4;
    border-bottom: 1px solid #e1e5e9;
    font-weight: 600;
    font-size: 13px;
    color: #2c3338;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}

.rag-summary:hover {
    background: #e8eaed;
}

.rag-summary .dashicons {
    color: #d1a85f;
    font-size: 18px;
}

.rag-content {
    padding: 16px;
}

.rag-section {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e1e5e9;
}

.rag-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.rag-section strong {
    display: inline-block;
    margin-bottom: 8px;
    color: #1d2327;
    font-size: 13px;
}

.rag-status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
}

.rag-status-badge.status-enabled {
    background: #d1f4e0;
    color: #0c6b2e;
}

.rag-status-badge.status-disabled {
    background: #f0f0f0;
    color: #666;
}

.query-variations-list {
    list-style: none;
    padding: 0;
    margin: 8px 0 0;
}

.query-variations-list li {
    padding: 10px 12px;
    background: #fff;
    border: 1px solid #dcdcde;
    border-radius: 4px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 13px;
}

.query-variations-list li.original-query {
    background: #fff9e6;
    border-color: #d1a85f;
    font-weight: 600;
}

.badge-original {
    background: #d1a85f;
    color: #fff;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    flex-shrink: 0;
}

.badge-variation {
    background: #e8eaed;
    color: #5f6368;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    flex-shrink: 0;
}

.rag-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.rag-stat {
    background: #fff;
    padding: 12px;
    border-radius: 4px;
    border: 1px solid #dcdcde;
    text-align: center;
}

.rag-stat .stat-label {
    display: block;
    font-size: 11px;
    color: #666;
    margin-bottom: 6px;
}

.rag-stat .stat-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #d1a85f;
}

.rag-features {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.rag-feature {
    padding: 8px 12px;
    background: #fff;
    border: 1px solid #dcdcde;
    border-radius: 4px;
    font-size: 13px;
}

@media screen and (max-width: 782px) {
    .rag-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<?php


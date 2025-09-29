<?php
/**
 * Settings page template with tabbed interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$settings = WP_GPT_RAG_Chat\Settings::get_settings();
?>

<div class="wrap wp-gpt-rag-chat-settings">
    <h1>
        <span class="dashicons dashicons-format-chat"></span>
        <?php esc_html_e('WP GPT RAG Chat Settings', 'wp-gpt-rag-chat'); ?>
    </h1>
    
    <nav class="nav-tab-wrapper wp-clearfix">
        <a href="#api-keys" class="nav-tab nav-tab-active" data-tab="api-keys">
            <span class="dashicons dashicons-admin-network"></span>
            <?php esc_html_e('API Keys', 'wp-gpt-rag-chat'); ?>
        </a>
        <a href="#models" class="nav-tab" data-tab="models">
            <span class="dashicons dashicons-admin-settings"></span>
            <?php esc_html_e('Models & Parameters', 'wp-gpt-rag-chat'); ?>
        </a>
        <a href="#chunking" class="nav-tab" data-tab="chunking">
            <span class="dashicons dashicons-editor-break"></span>
            <?php esc_html_e('Content Processing', 'wp-gpt-rag-chat'); ?>
        </a>
        <a href="#privacy" class="nav-tab" data-tab="privacy">
            <span class="dashicons dashicons-shield"></span>
            <?php esc_html_e('Privacy & Logging', 'wp-gpt-rag-chat'); ?>
        </a>
        <a href="#help" class="nav-tab" data-tab="help">
            <span class="dashicons dashicons-info"></span>
            <?php esc_html_e('Help & Testing', 'wp-gpt-rag-chat'); ?>
        </a>
    </nav>

    <form method="post" action="options.php" class="tab-content">
        <?php settings_fields('wp_gpt_rag_chat_settings'); ?>
        
        <!-- API Keys Tab -->
        <div id="tab-api-keys" class="tab-panel active">
            <div class="settings-section">
                <div class="settings-header">
                    <h2><?php esc_html_e('API Configuration', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Configure your OpenAI and Pinecone API credentials.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                
                <div class="settings-grid">
                    <!-- OpenAI Section -->
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-admin-site-alt3"></span>
                            <?php esc_html_e('OpenAI API', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <?php do_settings_fields('wp_gpt_rag_chat_settings', 'wp_gpt_rag_chat_openai'); ?>
                    </div>
                    
                    <!-- Pinecone Section -->
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-database-view"></span>
                            <?php esc_html_e('Pinecone Vector Database', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <?php do_settings_fields('wp_gpt_rag_chat_settings', 'wp_gpt_rag_chat_pinecone'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Models & Parameters Tab -->
        <div id="tab-models" class="tab-panel">
            <div class="settings-section">
                <div class="settings-header">
                    <h2><?php esc_html_e('AI Models & Parameters', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Configure the AI models and generation parameters.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                
                <div class="settings-grid">
                    <!-- Model Settings -->
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php esc_html_e('Model Selection', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <?php do_settings_fields('wp_gpt_rag_chat_settings', 'wp_gpt_rag_chat_models'); ?>
                    </div>
                    
                    <!-- Generation Settings -->
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-performance"></span>
                            <?php esc_html_e('Generation Parameters', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <?php do_settings_fields('wp_gpt_rag_chat_settings', 'wp_gpt_rag_chat_generation'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Processing Tab -->
        <div id="tab-chunking" class="tab-panel">
            <div class="settings-section">
                <div class="settings-header">
                    <h2><?php esc_html_e('Content Processing', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Configure how your content is processed and indexed.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                
                <div class="settings-grid">
                    <!-- Chunking Settings -->
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-editor-break"></span>
                            <?php esc_html_e('Text Chunking', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <?php do_settings_fields('wp_gpt_rag_chat_settings', 'wp_gpt_rag_chat_chunking'); ?>
                    </div>
                    
                    <!-- Retrieval Settings -->
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-search"></span>
                            <?php esc_html_e('Retrieval Settings', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <?php do_settings_fields('wp_gpt_rag_chat_settings', 'wp_gpt_rag_chat_retrieval'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy & Logging Tab -->
        <div id="tab-privacy" class="tab-panel">
            <div class="settings-section">
                <div class="settings-header">
                    <h2><?php esc_html_e('Privacy & Logging', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Configure privacy controls and logging settings.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                
                <div class="settings-grid">
                    <div class="settings-card full-width">
                        <h3>
                            <span class="dashicons dashicons-shield"></span>
                            <?php esc_html_e('Privacy & Compliance', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <?php do_settings_fields('wp_gpt_rag_chat_settings', 'wp_gpt_rag_chat_privacy'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help & Testing Tab -->
        <div id="tab-help" class="tab-panel">
            <div class="settings-section">
                <div class="settings-header">
                    <h2><?php esc_html_e('Help & Testing', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Setup guidance and connection testing tools.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                
                <div class="settings-grid">
                    <!-- Connection Testing -->
                    <?php if (!empty($settings['openai_api_key']) && !empty($settings['pinecone_api_key'])): ?>
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php esc_html_e('Connection Testing', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <div class="test-section">
                            <button type="button" id="test-connection" class="button button-primary">
                                <span class="dashicons dashicons-admin-network"></span>
                                <?php esc_html_e('Test API Connections', 'wp-gpt-rag-chat'); ?>
                            </button>
                            <div id="connection-test-result"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Setup Guide -->
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-admin-site-alt3"></span>
                            <?php esc_html_e('OpenAI Setup', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <ol class="setup-steps">
                <li><?php esc_html_e('Get your API key from', 'wp-gpt-rag-chat'); ?> <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a></li>
                            <li><?php esc_html_e('Choose the appropriate embedding model:', 'wp-gpt-rag-chat'); ?>
                                <ul class="model-list">
                        <li><strong>text-embedding-3-large:</strong> <?php esc_html_e('Best quality, higher cost', 'wp-gpt-rag-chat'); ?></li>
                        <li><strong>text-embedding-3-small:</strong> <?php esc_html_e('Good quality, lower cost', 'wp-gpt-rag-chat'); ?></li>
                    </ul>
                </li>
            </ol>
        </div>
        
                    <div class="settings-card">
                        <h3>
                            <span class="dashicons dashicons-database-view"></span>
                            <?php esc_html_e('Pinecone Setup', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <ol class="setup-steps">
                <li><?php esc_html_e('Create an account at', 'wp-gpt-rag-chat'); ?> <a href="https://www.pinecone.io/" target="_blank">Pinecone</a></li>
                            <li><?php esc_html_e('Create a new index with dimensions:', 'wp-gpt-rag-chat'); ?>
                                <ul class="model-list">
                                    <li><strong>1536:</strong> <?php esc_html_e('For text-embedding-3-large/small', 'wp-gpt-rag-chat'); ?></li>
                                    <li><strong>1536:</strong> <?php esc_html_e('For text-embedding-ada-002', 'wp-gpt-rag-chat'); ?></li>
                                </ul>
                            </li>
                            <li><?php esc_html_e('Copy the host URL from your index dashboard', 'wp-gpt-rag-chat'); ?></li>
            </ol>
        </div>
        
                    <div class="settings-card full-width">
                        <h3>
                            <span class="dashicons dashicons-editor-help"></span>
                            <?php esc_html_e('Configuration Tips', 'wp-gpt-rag-chat'); ?>
                        </h3>
                        <div class="tips-grid">
                            <div class="tip">
                                <h4><?php esc_html_e('Chunk Size', 'wp-gpt-rag-chat'); ?></h4>
                                <p><?php esc_html_e('Larger chunks provide more context but may exceed token limits. Start with 1400.', 'wp-gpt-rag-chat'); ?></p>
                            </div>
                            <div class="tip">
                                <h4><?php esc_html_e('Chunk Overlap', 'wp-gpt-rag-chat'); ?></h4>
                                <p><?php esc_html_e('Overlap ensures continuity between chunks. 150 characters is recommended.', 'wp-gpt-rag-chat'); ?></p>
                            </div>
                            <div class="tip">
                                <h4><?php esc_html_e('Top K Results', 'wp-gpt-rag-chat'); ?></h4>
                                <p><?php esc_html_e('Number of relevant chunks to retrieve. Higher values provide more context but increase costs.', 'wp-gpt-rag-chat'); ?></p>
                            </div>
                        </div>
                    </div>
        </div>
        </div>
    </div>
    
        <div class="settings-footer">
            <?php submit_button(__('Save Settings', 'wp-gpt-rag-chat'), 'primary large', 'submit', false); ?>
    </div>
    </form>
</div>

<style>
/* Main Container */
.wp-gpt-rag-chat-settings {
    background: #f1f1f1;
    margin: 20px 0 0 -20px;
    padding: 0;
}

.wp-gpt-rag-chat-settings h1 {
    background: #fff;
    padding: 20px 30px;
    margin: 0;
    border-bottom: 1px solid #ccd0d4;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.wp-gpt-rag-chat-settings h1 .dashicons {
    color: #0073aa;
    font-size: 28px;
}

/* Navigation Tabs */
.nav-tab-wrapper {
    background: #fff;
    margin: 0;
    padding: 0 30px;
    border-bottom: 1px solid #ccd0d4;
}

.nav-tab {
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    color: #646970;
    padding: 15px 20px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-tab:hover {
    color: #0073aa;
    background: rgba(0, 115, 170, 0.05);
}

.nav-tab.nav-tab-active {
    color: #0073aa;
    border-bottom-color: #0073aa;
    background: rgba(0, 115, 170, 0.05);
}

.nav-tab .dashicons {
    font-size: 16px;
}

/* Tab Content */
.tab-content {
    background: #f1f1f1;
    min-height: 600px;
}

.tab-panel {
    display: none;
    padding: 30px;
    animation: fadeIn 0.3s ease;
}

.tab-panel.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Settings Sections */
.settings-section {
    max-width: 1200px;
}

.settings-header {
    margin-bottom: 30px;
}

.settings-header h2 {
    font-size: 20px;
    margin: 0 0 8px 0;
    color: #1d2327;
}

.settings-header .description {
    color: #646970;
    font-size: 14px;
    margin: 0;
}

/* Settings Grid */
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.settings-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s ease;
}

.settings-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.settings-card.full-width {
    grid-column: 1 / -1;
}

.settings-card h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
    color: #1d2327;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0f0f1;
}

.settings-card h3 .dashicons {
    color: #0073aa;
    font-size: 18px;
}

/* Form Fields */
.settings-card table {
    width: 100%;
    border-collapse: collapse;
}

.settings-card th {
    text-align: left;
    padding: 8px 0;
    width: 150px;
    vertical-align: top;
    font-weight: 600;
    color: #1d2327;
}

.settings-card td {
    padding: 8px 0;
    vertical-align: top;
}

.settings-card input[type="text"],
.settings-card input[type="password"],
.settings-card input[type="number"],
.settings-card select {
    width: 100%;
    max-width: 300px;
    padding: 8px 12px;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.settings-card input:focus,
.settings-card select:focus {
    border-color: #0073aa;
    box-shadow: 0 0 0 1px #0073aa;
    outline: none;
}

.settings-card .description {
    color: #646970;
    font-size: 13px;
    margin-top: 4px;
    line-height: 1.4;
}

/* Checkbox Styling */
.settings-card input[type="checkbox"] {
    margin-right: 8px;
    transform: scale(1.1);
}

/* Test Section */
.test-section {
    text-align: center;
}

.test-section button {
    padding: 12px 24px;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

#connection-test-result {
    margin-top: 15px;
    padding: 12px;
    border-radius: 6px;
    display: none;
    font-weight: 500;
}

#connection-test-result.success {
    background: #d1e7dd;
    border: 1px solid #badbcc;
    color: #0f5132;
}

#connection-test-result.error {
    background: #f8d7da;
    border: 1px solid #f5c2c7;
    color: #842029;
}

/* Setup Steps */
.setup-steps {
    margin: 0;
    padding-left: 20px;
}

.setup-steps li {
    margin-bottom: 12px;
    line-height: 1.5;
}

.model-list {
    margin: 8px 0 0 20px;
    padding: 0;
}

.model-list li {
    margin-bottom: 6px;
    font-size: 13px;
}

/* Tips Grid */
.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.tip {
    padding: 16px;
    background: #f9f9f9;
    border-radius: 6px;
    border-left: 4px solid #0073aa;
}

.tip h4 {
    margin: 0 0 8px 0;
    color: #1d2327;
    font-size: 14px;
}

.tip p {
    margin: 0;
    color: #646970;
    font-size: 13px;
    line-height: 1.4;
}

/* Footer */
.settings-footer {
    background: #fff;
    padding: 20px 30px;
    border-top: 1px solid #ccd0d4;
    margin: 0 -30px -30px -30px;
    text-align: center;
}

.settings-footer .button {
    padding: 12px 30px;
    font-size: 14px;
    height: auto;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .nav-tab {
        padding: 12px 15px;
        font-size: 13px;
    }
    
    .tab-panel {
        padding: 20px;
    }
}

@media (max-width: 600px) {
    .wp-gpt-rag-chat-settings {
        margin: 20px 0 0 0;
    }
    
    .wp-gpt-rag-chat-settings h1 {
        padding: 15px 20px;
        font-size: 20px;
    }
    
    .nav-tab-wrapper {
        padding: 0 20px;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .nav-tab {
        padding: 10px 12px;
        font-size: 12px;
    }
    
    .tab-panel {
        padding: 15px;
    }
    
    .settings-card {
        padding: 16px;
    }
    
    .tips-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab Navigation
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target panel
        $('.tab-panel').removeClass('active');
        $('#tab-' + targetTab).addClass('active');
        
        // Update URL hash
        window.location.hash = targetTab;
    });
    
    // Handle initial hash
    var hash = window.location.hash.substr(1);
    if (hash && $('#tab-' + hash).length) {
        $('.nav-tab[data-tab="' + hash + '"]').click();
    }
    
    // Connection Test
    $('#test-connection').on('click', function() {
        var button = $(this);
        var result = $('#connection-test-result');
        
        button.prop('disabled', true)
              .html('<span class="dashicons dashicons-update-alt"></span> <?php esc_js(__('Testing...', 'wp-gpt-rag-chat')); ?>');
        result.hide();
        
        $.post(ajaxurl, {
            action: 'wp_gpt_rag_chat_test_connection',
            nonce: wpGptRagChatAdmin.nonce
        }, function(response) {
            if (response.success) {
                result.removeClass('error').addClass('success')
                    .html('<strong><?php esc_js(__('Success!', 'wp-gpt-rag-chat')); ?></strong> ' + response.data.message)
                    .show();
            } else {
                result.removeClass('success').addClass('error')
                    .html('<strong><?php esc_js(__('Error!', 'wp-gpt-rag-chat')); ?></strong> ' + response.data.message)
                    .show();
            }
        }).fail(function() {
            result.removeClass('success').addClass('error')
                .html('<strong><?php esc_js(__('Error!', 'wp-gpt-rag-chat')); ?></strong> <?php esc_js(__('Connection test failed.', 'wp-gpt-rag-chat')); ?>')
                .show();
        }).always(function() {
            button.prop('disabled', false)
                  .html('<span class="dashicons dashicons-admin-network"></span> <?php esc_js(__('Test API Connections', 'wp-gpt-rag-chat')); ?>');
        });
    });
    
    // Form validation feedback
    $('input, select').on('change', function() {
        $(this).removeClass('error');
    });
});
</script>
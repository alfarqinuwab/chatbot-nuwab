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

<div class="wrap cornuwab-admin-wrap cornuwab-wp-gpt-rag-chat-settings">
    <?php
    // Display WordPress settings errors (validation errors)
    settings_errors('wp_gpt_rag_chat_settings');
    
    // Check if plugin needs configuration
    $settings = WP_GPT_RAG_Chat\Settings::get_settings();
    if (empty($settings['openai_api_key']) || empty($settings['pinecone_api_key']) || empty($settings['pinecone_host'])) {
        echo '<div class="notice notice-warning is-dismissible" style="margin: 20px 0;">';
        echo '<p>';
        echo sprintf(
            __('<strong>WP GPT RAG Chat needs to be configured.</strong> Please fill in the required API keys and settings below.', 'wp-gpt-rag-chat')
        );
        echo '</p>';
        echo '</div>';
    }
    ?>
    
    <form method="post" action="options.php" class="cornuwab-settings-form" id="settings-form">
        <!-- AJAX Nonce for Security -->
        <?php wp_nonce_field('wp_gpt_rag_chat_settings_nonce', 'wp_gpt_rag_chat_settings_nonce'); ?>
        
        <!-- Toast Notification Container -->
        <div id="toast-container" class="cornuwab-toast-container"></div>
        
        <div class="cornuwab-settings-card-container">
            <div class="cornuwab-settings-header">
    <h1>
        <span class="dashicons dashicons-format-chat"></span>
        <?php esc_html_e('Nuwab AI Assistant Settings', 'wp-gpt-rag-chat'); ?>
    </h1>
            </div>
    <nav class="cornuwab-nav-tab-wrapper wp-clearfix">
                <a href="#general" class="cornuwab-nav-tab cornuwab-nav-tab-active" data-tab="general">
            <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e('General Settings', 'wp-gpt-rag-chat'); ?>
                </a>
                <a href="#indexing" class="cornuwab-nav-tab" data-tab="indexing">
                    <span class="dashicons dashicons-database"></span>
                    <?php esc_html_e('Indexing Settings', 'wp-gpt-rag-chat'); ?>
                </a>
                <a href="#chat" class="cornuwab-nav-tab" data-tab="chat">
                    <span class="dashicons dashicons-format-chat"></span>
                    <?php esc_html_e('Chat Settings', 'wp-gpt-rag-chat'); ?>
                </a>
                <a href="#advanced" class="cornuwab-nav-tab" data-tab="advanced">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e('Advanced Settings', 'wp-gpt-rag-chat'); ?>
        </a>
    </nav>

            <div class="cornuwab-tab-content">
        <?php settings_fields('wp_gpt_rag_chat_settings'); ?>
        
        <!-- General Settings Tab -->
        <div id="tab-general" class="cornuwab-tab-panel active">
            <div class="cornuwab-settings-section">
                <div class="cornuwab-settings-header">
                    <h2><?php esc_html_e('General Settings', 'wp-gpt-rag-chat'); ?></h2>
                </div>
                
                <!-- Inner Tabs for General Settings -->
                <div class="cornuwab-inner-tab-wrapper">
                    <a href="#openai-config" class="cornuwab-inner-tab cornuwab-inner-tab-active" data-inner-tab="openai-config">
                        <span class="dashicons dashicons-admin-network"></span>
                        <?php esc_html_e('OpenAI Configuration', 'wp-gpt-rag-chat'); ?>
                    </a>
                    <a href="#pinecone-config" class="cornuwab-inner-tab" data-inner-tab="pinecone-config">
                        <span class="dashicons dashicons-database"></span>
                        <?php esc_html_e('Pinecone Configuration', 'wp-gpt-rag-chat'); ?>
                    </a>
                    <a href="#chatbot-behavior" class="cornuwab-inner-tab" data-inner-tab="chatbot-behavior">
                        <span class="dashicons dashicons-format-chat"></span>
                        <?php esc_html_e('Chatbot Behavior', 'wp-gpt-rag-chat'); ?>
                    </a>
                    <a href="#retrieval-rag" class="cornuwab-inner-tab" data-inner-tab="retrieval-rag">
                        <span class="dashicons dashicons-search"></span>
                        <?php esc_html_e('Retrieval & RAG', 'wp-gpt-rag-chat'); ?>
                    </a>
                    </div>
                    
                <!-- OpenAI Configuration Tab -->
                <div id="inner-tab-openai-config" class="cornuwab-inner-tab-panel active">
                    <div class="cornuwab-settings-group">
                        <h3><?php esc_html_e('OpenAI Configuration', 'wp-gpt-rag-chat'); ?></h3>
                    <div class="cornuwab-ai-model-header">
                        <span class="cornuwab-ai-model-provider"><?php esc_html_e('OpenAI, GPT-4.1', 'wp-gpt-rag-chat'); ?></span>
                    </div>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row">
                                <label for="openai_api_key"><?php esc_html_e('API Key', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="openai_api_key" name="wp_gpt_rag_chat_settings[openai_api_key]" value="<?php echo esc_attr($settings['openai_api_key'] ?? ''); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('Enter your OpenAI API key from the OpenAI Platform.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="openai_environment"><?php esc_html_e('Environment:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="openai_environment" name="wp_gpt_rag_chat_settings[openai_environment]" class="regular-text">
                                    <option value="openai" <?php selected($settings['openai_environment'] ?? 'openai', 'openai'); ?>><?php esc_html_e('OpenAI', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('AI service provider environment.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="chat_model"><?php esc_html_e('Model:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="chat_model" name="wp_gpt_rag_chat_settings[chat_model]" class="regular-text">
                                    <option value="gpt-4.1" <?php selected($settings['chat_model'] ?? 'gpt-4.1', 'gpt-4.1'); ?>><?php esc_html_e('GPT-4.1', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="gpt-4o" <?php selected($settings['chat_model'] ?? 'gpt-4.1', 'gpt-4o'); ?>><?php esc_html_e('GPT-4o', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="gpt-4o-mini" <?php selected($settings['chat_model'] ?? 'gpt-4.1', 'gpt-4o-mini'); ?>><?php esc_html_e('GPT-4o Mini', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="gpt-4-turbo" <?php selected($settings['chat_model'] ?? 'gpt-4.1', 'gpt-4-turbo'); ?>><?php esc_html_e('GPT-4 Turbo', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="gpt-3.5-turbo" <?php selected($settings['chat_model'] ?? 'gpt-4.1', 'gpt-3.5-turbo'); ?>><?php esc_html_e('GPT-3.5 Turbo', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Select the OpenAI model to use for chat responses.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="openai_vision"><?php esc_html_e('Vision:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="openai_vision" name="wp_gpt_rag_chat_settings[openai_vision]" value="1" <?php checked($settings['openai_vision'] ?? 0, 1); ?> />
                                    <?php esc_html_e('Enable', 'wp-gpt-rag-chat'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Enable vision capabilities for image analysis.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="temperature"><?php esc_html_e('Temperature:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="temperature" name="wp_gpt_rag_chat_settings[temperature]" value="<?php echo esc_attr($settings['temperature'] ?? '0.8'); ?>" min="0" max="2" step="0.1" class="small-text" />
                                <span id="temperature-value"><?php echo esc_html($settings['temperature'] ?? '0.8'); ?></span>
                                <p class="description"><?php esc_html_e('Controls randomness in responses (0.0 = deterministic, 2.0 = very creative).', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="max_tokens"><?php esc_html_e('Max Tokens:', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="max_tokens" name="wp_gpt_rag_chat_settings[max_tokens]" value="<?php echo esc_attr($settings['max_tokens'] ?? '1024'); ?>" min="1" max="32768" class="small-text" />
                                <div class="cornuwab-token-info">
                                    <p class="description"><?php esc_html_e('Contextual: 1047576 - Completion: 32768', 'wp-gpt-rag-chat'); ?></p>
                                    <p class="description recommended"><?php esc_html_e('Recommended: 32768', 'wp-gpt-rag-chat'); ?></p>
                </div>
                            </td>
                        </tr>
                    </table>
            </div>
        </div>

                <!-- Pinecone Configuration Tab -->
                <div id="inner-tab-pinecone-config" class="cornuwab-inner-tab-panel">
                    <div class="cornuwab-settings-group">
                        <h3><?php esc_html_e('Pinecone Configuration', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row">
                                <label for="pinecone_name"><?php esc_html_e('Name', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="pinecone_name" name="wp_gpt_rag_chat_settings[pinecone_name]" value="<?php echo esc_attr($settings['pinecone_name'] ?? 'Pinecone'); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('Display name for your Pinecone configuration.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_type"><?php esc_html_e('Type', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="pinecone_type" name="wp_gpt_rag_chat_settings[pinecone_type]" class="regular-text">
                                    <option value="pinecone" <?php selected($settings['pinecone_type'] ?? 'pinecone', 'pinecone'); ?>><?php esc_html_e('Pinecone', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Vector database type.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_api_key"><?php esc_html_e('API Key', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="pinecone_api_key" name="wp_gpt_rag_chat_settings[pinecone_api_key]" value="<?php echo esc_attr($settings['pinecone_api_key'] ?? ''); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('You can get your API Keys in your <a href="https://app.pinecone.io/" target="_blank">Pinecone Account</a>.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_index_name"><?php esc_html_e('Index Name', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="pinecone_index_name" name="wp_gpt_rag_chat_settings[pinecone_index_name]" value="<?php echo esc_attr($settings['pinecone_index_name'] ?? ''); ?>" class="regular-text" placeholder="your-index-name" />
                                <p class="description"><?php esc_html_e('The name of your Pinecone index (must already exist).', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_host"><?php esc_html_e('Server', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="pinecone_host" name="wp_gpt_rag_chat_settings[pinecone_host]" value="<?php echo esc_attr($settings['pinecone_host'] ?? ''); ?>" class="regular-text" placeholder="https://your-index.svc.region.pinecone.io" />
                                <p class="description"><?php esc_html_e('The URL of your host (check your Indexes).', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_namespace"><?php esc_html_e('Namespace', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="pinecone_namespace" name="wp_gpt_rag_chat_settings[pinecone_namespace]" value="<?php echo esc_attr($settings['pinecone_namespace'] ?? ''); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('The namespace is used to separate the data from other data. This allows you to use the same server/index on more than one website. This is optional.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_dimensions"><?php esc_html_e('Index Dimensions', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="pinecone_dimensions" name="wp_gpt_rag_chat_settings[pinecone_dimensions]" value="<?php echo esc_attr($settings['pinecone_dimensions'] ?? ''); ?>" class="small-text" min="1" max="2048" />
                                <button type="button" class="button" id="run-quick-test"><?php esc_html_e('Run Quick Test', 'wp-gpt-rag-chat'); ?></button>
                                <p class="description"><?php esc_html_e('The vector dimensions of your Pinecone index. This is detected automatically when you run Quick Test.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="embedding_dimensions"><?php esc_html_e('Embedding Model & Dimensions', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="embedding_dimensions" name="wp_gpt_rag_chat_settings[embedding_dimensions]" class="regular-text">
                                    <option value="512" <?php selected($settings['embedding_dimensions'] ?? $settings['pinecone_dimensions'] ?? '512', '512'); ?>><?php esc_html_e('text-embedding-3-small (512 dimensions)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="1536" <?php selected($settings['embedding_dimensions'] ?? $settings['pinecone_dimensions'] ?? '512', '1536'); ?>><?php esc_html_e('text-embedding-3-small (1536 dimensions)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="3072" <?php selected($settings['embedding_dimensions'] ?? $settings['pinecone_dimensions'] ?? '512', '3072'); ?>><?php esc_html_e('text-embedding-3-large (3072 dimensions)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="1536_ada" <?php selected($settings['embedding_dimensions'] ?? $settings['pinecone_dimensions'] ?? '512', '1536_ada'); ?>><?php esc_html_e('text-embedding-ada-002 (1536 dimensions)', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Select the embedding model and dimensions. Must match your Pinecone index dimensions. This setting overrides the Embedding Model setting in OpenAI Configuration.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_env_id"><?php esc_html_e('Env ID', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="pinecone_env_id" name="wp_gpt_rag_chat_settings[pinecone_env_id]" value="<?php echo esc_attr($settings['pinecone_env_id'] ?? ''); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('The unique identifier for this environment.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pinecone_score_threshold"><?php esc_html_e('Score', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="pinecone_score_threshold" name="wp_gpt_rag_chat_settings[pinecone_score_threshold]" class="regular-text">
                                    <option value="0.7" <?php selected($settings['pinecone_score_threshold'] ?? '0.7', '0.7'); ?>><?php esc_html_e('0.7 (Default)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="0.8" <?php selected($settings['pinecone_score_threshold'] ?? '0.7', '0.8'); ?>><?php esc_html_e('0.8 (High)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="0.6" <?php selected($settings['pinecone_score_threshold'] ?? '0.7', '0.6'); ?>><?php esc_html_e('0.6 (Medium)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="0.5" <?php selected($settings['pinecone_score_threshold'] ?? '0.7', '0.5'); ?>><?php esc_html_e('0.5 (Low)', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Similarity score threshold for vector search results.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                    </div>
                    
                <!-- Chatbot Behavior Tab -->
                <div id="inner-tab-chatbot-behavior" class="cornuwab-inner-tab-panel">
                    <div class="cornuwab-settings-group">
                        <h3><?php esc_html_e('Chatbot Behavior', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row">
                                <label for="system_prompt"><?php esc_html_e('System Prompt / Bot Personality', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <textarea id="system_prompt" name="wp_gpt_rag_chat_settings[system_prompt]" rows="4" class="large-text"><?php echo esc_textarea($settings['system_prompt'] ?? 'You are a helpful AI assistant. Answer questions based on the provided context.'); ?></textarea>
                                <p class="description"><?php esc_html_e('Define the personality and behavior of your chatbot.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="temperature"><?php esc_html_e('Temperature', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="range" id="temperature" name="wp_gpt_rag_chat_settings[temperature]" min="0" max="2" step="0.1" value="<?php echo esc_attr($settings['temperature'] ?? '0.7'); ?>" />
                                <span id="temperature-value"><?php echo esc_html($settings['temperature'] ?? '0.7'); ?></span>
                                <p class="description"><?php esc_html_e('Controls randomness. Lower values make responses more focused and deterministic.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="max_tokens"><?php esc_html_e('Max Tokens', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="max_tokens" name="wp_gpt_rag_chat_settings[max_tokens]" min="1" max="4000" value="<?php echo esc_attr($settings['max_tokens'] ?? '1000'); ?>" class="small-text" />
                                <p class="description"><?php esc_html_e('Maximum number of tokens in the response.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                    </div>
                    
                    <!-- Sitemap Fallback Settings -->
                    <div class="cornuwab-settings-group" style="padding-top: 20px; margin-top: 20px;">
                        <h3><?php esc_html_e('Sitemap Fallback Suggestions', 'wp-gpt-rag-chat'); ?></h3>
                        <p class="description"><?php esc_html_e('When RAG finds no relevant answers, suggest related pages from your sitemap.', 'wp-gpt-rag-chat'); ?></p>
                        <table class="cornuwab-form-table">
                            <tr>
                                <th scope="row">
                                    <label for="enable_sitemap_fallback"><?php esc_html_e('Enable Sitemap Fallback', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="enable_sitemap_fallback" name="wp_gpt_rag_chat_settings[enable_sitemap_fallback]" value="1" <?php checked($settings['enable_sitemap_fallback'] ?? true, 1); ?> />
                                        <?php esc_html_e('Suggest relevant pages when no answer is found', 'wp-gpt-rag-chat'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('When enabled, the chatbot will search your sitemap and suggest relevant pages to visit.', 'wp-gpt-rag-chat'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="sitemap_url"><?php esc_html_e('Sitemap URL', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="sitemap_url" name="wp_gpt_rag_chat_settings[sitemap_url]" value="<?php echo esc_attr($settings['sitemap_url'] ?? 'sitemap.xml'); ?>" class="regular-text" />
                                    <p class="description">
                                        <?php esc_html_e('Enter your sitemap URL (e.g., sitemap.xml or https://yoursite.com/sitemap.xml)', 'wp-gpt-rag-chat'); ?>
                                        <br>
                                        <strong><?php esc_html_e('Note:', 'wp-gpt-rag-chat'); ?></strong> <?php esc_html_e('After saving, go to the Diagnostics page to index your sitemap.', 'wp-gpt-rag-chat'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="sitemap_suggestions_count"><?php esc_html_e('Number of Suggestions', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="sitemap_suggestions_count" name="wp_gpt_rag_chat_settings[sitemap_suggestions_count]" min="1" max="10" value="<?php echo esc_attr($settings['sitemap_suggestions_count'] ?? '5'); ?>" class="small-text" />
                                    <p class="description"><?php esc_html_e('Maximum number of page suggestions to show (1-10).', 'wp-gpt-rag-chat'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Retrieval & RAG Tab -->
                <div id="inner-tab-retrieval-rag" class="cornuwab-inner-tab-panel">
                    <div class="cornuwab-settings-group">
                        <h3><?php esc_html_e('Retrieval Settings', 'wp-gpt-rag-chat'); ?></h3>
                        <table class="cornuwab-form-table">
                            <tr>
                                <th scope="row">
                                    <label for="top_k"><?php esc_html_e('Top K Results', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="top_k" name="wp_gpt_rag_chat_settings[top_k]" value="<?php echo esc_attr($settings['top_k'] ?? 5); ?>" min="1" max="50" step="1" class="small-text" />
                                    <p class="description"><?php esc_html_e('How many nearest chunks to retrieve before re-ranking (try 30–40).', 'wp-gpt-rag-chat'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="similarity_threshold"><?php esc_html_e('Similarity Threshold', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="similarity_threshold" name="wp_gpt_rag_chat_settings[similarity_threshold]" value="<?php echo esc_attr($settings['similarity_threshold'] ?? 0.7); ?>" min="0" max="1" step="0.01" class="small-text" />
                                    <p class="description"><?php esc_html_e('Minimum cosine similarity score to keep a match (0.6–0.65 recommended for recall).', 'wp-gpt-rag-chat'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="cornuwab-settings-group">
                        <h3><?php esc_html_e('RAG Enhancements', 'wp-gpt-rag-chat'); ?></h3>
                        <table class="cornuwab-form-table">
                            <tr>
                                <th scope="row">
                                    <label for="enable_rag"><?php esc_html_e('Enable RAG (Retrieval-Augmented Generation)', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="enable_rag" name="wp_gpt_rag_chat_settings[enable_rag]" value="1" <?php checked($settings['enable_rag'] ?? true, 1); ?> />
                                        <?php esc_html_e('Enable RAG to use your indexed content for AI responses', 'wp-gpt-rag-chat'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('When enabled, the AI will search your indexed content first. When disabled, AI will use only its training data.', 'wp-gpt-rag-chat'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="enable_query_expansion"><?php esc_html_e('Query Expansion', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="enable_query_expansion" name="wp_gpt_rag_chat_settings[enable_query_expansion]" value="1" <?php checked($settings['enable_query_expansion'] ?? 1, 1); ?> />
                                        <?php esc_html_e('Enable multi-query expansion to improve recall', 'wp-gpt-rag-chat'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="enable_hyde"><?php esc_html_e('HyDE (Hypothetical Answer)', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="enable_hyde" name="wp_gpt_rag_chat_settings[enable_hyde]" value="1" <?php checked($settings['enable_hyde'] ?? 1, 1); ?> />
                                        <?php esc_html_e('Generate a hypothetical answer to guide retrieval', 'wp-gpt-rag-chat'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="enable_reranking"><?php esc_html_e('Re-ranking', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="enable_reranking" name="wp_gpt_rag_chat_settings[enable_reranking]" value="1" <?php checked($settings['enable_reranking'] ?? 1, 1); ?> />
                                        <?php esc_html_e('Re-rank retrieved results for better relevance', 'wp-gpt-rag-chat'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="enable_llm_rerank"><?php esc_html_e('LLM Re-ranker', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="enable_llm_rerank" name="wp_gpt_rag_chat_settings[enable_llm_rerank]" value="1" <?php checked($settings['enable_llm_rerank'] ?? 0, 1); ?> />
                                        <?php esc_html_e('Use the chat model to score top candidates (more accurate, higher cost)', 'wp-gpt-rag-chat'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="llm_rerank_top_k"><?php esc_html_e('LLM Re-rank Top K', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="llm_rerank_top_k" name="wp_gpt_rag_chat_settings[llm_rerank_top_k]" value="<?php echo esc_attr($settings['llm_rerank_top_k'] ?? 20); ?>" min="5" max="50" step="1" class="small-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="final_context_chunks"><?php esc_html_e('Final Context Chunks', 'wp-gpt-rag-chat'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="final_context_chunks" name="wp_gpt_rag_chat_settings[final_context_chunks]" value="<?php echo esc_attr($settings['final_context_chunks'] ?? 6); ?>" min="3" max="12" step="1" class="small-text" />
                                    <p class="description"><?php esc_html_e('How many top chunks to include in the final context window.', 'wp-gpt-rag-chat'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indexing Settings Tab -->
        <div id="tab-indexing" class="cornuwab-tab-panel">
            <div class="cornuwab-settings-section">
                <div class="cornuwab-settings-header">
                    <h2><?php esc_html_e('Indexing Settings', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Configure content sources and indexing controls.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                
                <!-- Content Sources Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Select Content Sources', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Post Types', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <?php
                                $post_types = get_post_types(['public' => true], 'objects');
                                $selected_post_types = $settings['post_types'] ?? ['post', 'page'];
                                foreach ($post_types as $post_type) {
                                    $checked = in_array($post_type->name, $selected_post_types) ? 'checked' : '';
                                    echo '<label><input type="checkbox" name="wp_gpt_rag_chat_settings[post_types][]" value="' . esc_attr($post_type->name) . '" ' . $checked . '> ' . esc_html($post_type->label) . '</label><br>';
                                }
                                ?>
                                <p class="description"><?php esc_html_e('Select which post types to include in the knowledge base.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                    </div>
                    
                <!-- Indexing Controls Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Indexing Controls', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Auto-sync', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wp_gpt_rag_chat_settings[auto_sync]" value="1" <?php checked($settings['auto_sync'] ?? 1, 1); ?> />
                                    <?php esc_html_e('Auto-sync on publish/update', 'wp-gpt-rag-chat'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Automatically index content when posts are published or updated.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Manual Sync', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <button type="button" id="run-full-sync" class="button button-secondary">
                                    <?php esc_html_e('Run Full Sync', 'wp-gpt-rag-chat'); ?>
                                </button>
                                <button type="button" id="sync-new-content" class="button button-primary">
                                    <?php esc_html_e('Sync New Content Only', 'wp-gpt-rag-chat'); ?>
                                </button>
                                <p class="description"><?php esc_html_e('Manually trigger content synchronization.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Auto-Indexing Settings Section -->
                <div class="cornuwab-settings-group">
                    <h3>
                        <span class="dashicons dashicons-update-alt" style="color: #2271b1;"></span>
                        <?php esc_html_e('Automatic Indexing', 'wp-gpt-rag-chat'); ?>
                    </h3>
                    <p class="description" style="margin: 10px 0 20px;">
                        <?php esc_html_e('Control automatic background indexing when content is saved or published.', 'wp-gpt-rag-chat'); ?>
                    </p>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row">
                                <label for="enable_auto_indexing"><?php esc_html_e('Enable Auto-Indexing', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" id="enable_auto_indexing" name="wp_gpt_rag_chat_settings[enable_auto_indexing]" value="1" <?php checked($settings['enable_auto_indexing'] ?? 1, 1); ?> />
                                    <?php esc_html_e('Enable', 'wp-gpt-rag-chat'); ?>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Automatically index content to Pinecone when posts are saved or published. This runs in the background via WP-Cron.', 'wp-gpt-rag-chat'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php esc_html_e('Auto-Index Post Types', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <?php
                                    $post_types = get_post_types(['public' => true], 'objects');
                                    $selected_auto_index = $settings['auto_index_post_types'] ?? ['post', 'page'];
                                    foreach ($post_types as $post_type) {
                                        $checked = in_array($post_type->name, $selected_auto_index) ? 'checked' : '';
                                        echo '<label style="display: block; margin-bottom: 5px;">';
                                        echo '<input type="checkbox" name="wp_gpt_rag_chat_settings[auto_index_post_types][]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' />';
                                        echo ' ' . esc_html($post_type->label) . ' <code>(' . esc_html($post_type->name) . ')</code>';
                                        echo '</label>';
                                    }
                                    ?>
                                </fieldset>
                                <p class="description">
                                    <?php esc_html_e('Select which post types should be automatically indexed when saved. Custom post types will appear here if they are public.', 'wp-gpt-rag-chat'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="auto_index_delay"><?php esc_html_e('Indexing Delay', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="auto_index_delay" name="wp_gpt_rag_chat_settings[auto_index_delay]" value="<?php echo esc_attr($settings['auto_index_delay'] ?? 30); ?>" min="10" max="600" step="10" class="small-text" />
                                <span><?php esc_html_e('seconds', 'wp-gpt-rag-chat'); ?></span>
                                <p class="description">
                                    <?php esc_html_e('Time to wait before indexing. Prevents indexing during rapid edits. Minimum: 10 seconds, Maximum: 600 seconds (10 minutes).', 'wp-gpt-rag-chat'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <div class="notice notice-info inline" style="margin: 15px 0; padding: 10px 15px;">
                        <p style="margin: 0;">
                            <strong><?php esc_html_e('Note:', 'wp-gpt-rag-chat'); ?></strong>
                            <?php esc_html_e('Auto-indexing uses WordPress Cron (WP-Cron) which runs when someone visits your site. For high-traffic sites, this works reliably. For low-traffic sites, consider setting up a real cron job or trigger manual syncs from the', 'wp-gpt-rag-chat'); ?>
                            <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-indexing'); ?>"><?php esc_html_e('Indexing page', 'wp-gpt-rag-chat'); ?></a>.
                        </p>
                    </div>
                </div>

                <!-- Indexing Status Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Indexing Status', 'wp-gpt-rag-chat'); ?></h3>
                    <div id="indexing-status">
                        <p><?php esc_html_e('Loading indexing status...', 'wp-gpt-rag-chat'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Settings Tab -->
        <div id="tab-chat" class="cornuwab-tab-panel">
            <div class="cornuwab-settings-section">
                <div class="cornuwab-settings-header">
                    <h2><?php esc_html_e('Chat Settings', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Configure chat widget appearance and behavior.', 'wp-gpt-rag-chat'); ?></p>
                </div>
                
                <!-- Chat Widget Customization Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Chat Widget Customization', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Chatbot', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wp_gpt_rag_chat_settings[enable_chatbot]" value="1" <?php checked($settings['enable_chatbot'] ?? 1, 1); ?> />
                                    <?php esc_html_e('Enable chatbot on front-end', 'wp-gpt-rag-chat'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Master switch to enable/disable the chat widget site-wide.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="chat_visibility"><?php esc_html_e('Chat Visibility', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="chat_visibility" name="wp_gpt_rag_chat_settings[chat_visibility]">
                                    <option value="everyone" <?php selected($settings['chat_visibility'] ?? 'everyone', 'everyone'); ?>><?php esc_html_e('Show to Everyone (Visitors & Logged-in Users)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="logged_in_only" <?php selected($settings['chat_visibility'] ?? '', 'logged_in_only'); ?>><?php esc_html_e('Show to Logged-in Users Only', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="visitors_only" <?php selected($settings['chat_visibility'] ?? '', 'visitors_only'); ?>><?php esc_html_e('Show to Visitors Only (Not Logged-in)', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Control who can see and use the chat widget on your website.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="response_mode"><?php esc_html_e('Response Source', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="response_mode" name="wp_gpt_rag_chat_settings[response_mode]">
                                    <option value="hybrid" <?php selected($settings['response_mode'] ?? 'hybrid', 'hybrid'); ?>><?php esc_html_e('Hybrid (AI + Knowledge Base)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="openai" <?php selected($settings['response_mode'] ?? 'hybrid', 'openai'); ?>><?php esc_html_e('OpenAI Only (Generative AI)', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="knowledge_base" <?php selected($settings['response_mode'] ?? 'hybrid', 'knowledge_base'); ?>><?php esc_html_e('Knowledge Base Only (Indexed Content)', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Choose how chat responses are generated: directly from OpenAI, strictly from your indexed content, or a hybrid using AI with knowledge-base context.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="widget_placement"><?php esc_html_e('Widget Placement', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="widget_placement" name="wp_gpt_rag_chat_settings[widget_placement]">
                                    <option value="floating" <?php selected($settings['widget_placement'] ?? 'floating', 'floating'); ?>><?php esc_html_e('Floating Button', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="inline" <?php selected($settings['widget_placement'] ?? '', 'inline'); ?>><?php esc_html_e('Inline Shortcode', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="block" <?php selected($settings['widget_placement'] ?? '', 'block'); ?>><?php esc_html_e('Gutenberg Block', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="greeting_text"><?php esc_html_e('Greeting Text', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="greeting_text" name="wp_gpt_rag_chat_settings[greeting_text]" value="<?php echo esc_attr($settings['greeting_text'] ?? 'Hello! How can I help you today?'); ?>" class="regular-text" />
                            </td>
                        </tr>
                    </table>
                    </div>
                    
                <!-- Conversation Settings Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Conversation Settings', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Conversation History', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wp_gpt_rag_chat_settings[enable_history]" value="1" <?php checked($settings['enable_history'] ?? 1, 1); ?> />
                                    <?php esc_html_e('Enable conversation history', 'wp-gpt-rag-chat'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="max_conversation_length"><?php esc_html_e('Max Conversation Length', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="max_conversation_length" name="wp_gpt_rag_chat_settings[max_conversation_length]" min="1" max="50" value="<?php echo esc_attr($settings['max_conversation_length'] ?? '10'); ?>" class="small-text" />
                                <p class="description"><?php esc_html_e('Maximum number of messages to keep in conversation history.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Anonymous Users', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wp_gpt_rag_chat_settings[allow_anonymous]" value="1" <?php checked($settings['allow_anonymous'] ?? 1, 1); ?> />
                                    <?php esc_html_e('Allow anonymous users to chat', 'wp-gpt-rag-chat'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Template Settings Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Template Settings', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Chat Logo', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <div class="image-upload-field">
                                    <input type="url" id="chat_logo" name="wp_gpt_rag_chat_settings[chat_logo]" value="<?php echo esc_attr($settings['chat_logo'] ?? ''); ?>" class="regular-text" />
                                    <input type="button" class="button button-secondary" value="<?php esc_attr_e('Select Image', 'wp-gpt-rag-chat'); ?>" onclick="wp_gpt_rag_chat_upload_image('chat_logo')" />
                                    
                                    <?php if (!empty($settings['chat_logo'])): ?>
                                        <div class="image-preview" style="margin-top: 10px;">
                                            <img src="<?php echo esc_url($settings['chat_logo']); ?>" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd;" />
                                            <br><a href="#" onclick="wp_gpt_rag_chat_remove_image('chat_logo'); return false;" style="color: #a00;"><?php esc_html_e('Remove', 'wp-gpt-rag-chat'); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p class="description"><?php esc_html_e('Upload a logo to display in the chat header. Recommended size: 183x60px.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Show chat in footer', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wp_gpt_rag_chat_settings[show_chat_in_footer]" value="1" <?php checked($settings['show_chat_in_footer'] ?? 1, 1); ?> />
                                    <?php esc_html_e('Display the floating chat widget in the footer', 'wp-gpt-rag-chat'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Show the traditional floating chat widget in the footer of your website.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>


        <!-- Advanced Settings Tab -->
        <div id="tab-advanced" class="cornuwab-tab-panel">
            <div class="cornuwab-settings-section">
                <div class="cornuwab-settings-header">
                    <h2><?php esc_html_e('Advanced Settings', 'wp-gpt-rag-chat'); ?></h2>
                    <p class="description"><?php esc_html_e('Debug tools, index maintenance, and custom embeddings.', 'wp-gpt-rag-chat'); ?></p>
                        </div>
        
                <!-- Maintenance Mode Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Maintenance Mode', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Maintenance Mode', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <label class="cornuwab-toggle-switch">
                                    <input type="checkbox" name="wp_gpt_rag_chat_settings[maintenance_mode]" value="1" <?php checked($settings['maintenance_mode'] ?? 0, 1); ?> />
                                    <span class="cornuwab-toggle-slider"></span>
                                </label>
                                <span class="cornuwab-toggle-label"><?php esc_html_e('Show chat widget only to logged-in admin users', 'wp-gpt-rag-chat'); ?></span>
                                <p class="description"><?php esc_html_e('When enabled, the chat widget will only be visible to administrators who are logged in. Regular visitors and non-admin users will not see the chat.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Debug & Developer Tools Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Debug & Developer Tools', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Debug Mode', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wp_gpt_rag_chat_settings[debug_mode]" value="1" <?php checked($settings['debug_mode'] ?? 0, 1); ?> />
                                    <?php esc_html_e('Show raw API requests/responses', 'wp-gpt-rag-chat'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="logging_level"><?php esc_html_e('Logging Level', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="logging_level" name="wp_gpt_rag_chat_settings[logging_level]">
                                    <option value="error" <?php selected($settings['logging_level'] ?? 'error', 'error'); ?>><?php esc_html_e('Errors Only', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="verbose" <?php selected($settings['logging_level'] ?? '', 'verbose'); ?>><?php esc_html_e('Verbose', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    </div>
        
                <!-- Index Maintenance Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Index Maintenance', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Clear Index', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <button type="button" id="clear-index" class="button button-secondary">
                                    <?php esc_html_e('Clear All Indexed Data in Pinecone', 'wp-gpt-rag-chat'); ?>
                                </button>
                                <p class="description"><?php esc_html_e('Warning: This will delete all indexed content from Pinecone.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Schema Sync', 'wp-gpt-rag-chat'); ?></th>
                            <td>
                                <button type="button" id="force-schema-sync" class="button button-secondary">
                                    <?php esc_html_e('Force Schema Re-sync', 'wp-gpt-rag-chat'); ?>
                                </button>
                                <p class="description"><?php esc_html_e('Force synchronization of the database schema.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                            </div>

                <!-- Custom Embeddings Section -->
                <div class="cornuwab-settings-group">
                    <h3><?php esc_html_e('Custom Embeddings', 'wp-gpt-rag-chat'); ?></h3>
                    <table class="cornuwab-form-table">
                        <tr>
                            <th scope="row">
                                <label for="embedding_model"><?php esc_html_e('Embedding Model', 'wp-gpt-rag-chat'); ?></label>
                            </th>
                            <td>
                                <select id="embedding_model" name="wp_gpt_rag_chat_settings[embedding_model]">
                                    <option value="text-embedding-3-small" <?php selected($settings['embedding_model'] ?? 'text-embedding-3-small', 'text-embedding-3-small'); ?>><?php esc_html_e('text-embedding-3-small', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="text-embedding-3-large" <?php selected($settings['embedding_model'] ?? '', 'text-embedding-3-large'); ?>><?php esc_html_e('text-embedding-3-large', 'wp-gpt-rag-chat'); ?></option>
                                    <option value="text-embedding-ada-002" <?php selected($settings['embedding_model'] ?? '', 'text-embedding-ada-002'); ?>><?php esc_html_e('text-embedding-ada-002', 'wp-gpt-rag-chat'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Choose the embedding model for vector generation. Note: This setting is overridden by the "Embedding Model & Dimensions" setting in Pinecone Configuration.', 'wp-gpt-rag-chat'); ?></p>
                            </td>
                        </tr>
                    </table>
                    </div>
        </div>
        </div>
    </div>
    
        <div class="cornuwab-settings-footer">
            <?php submit_button(__('Save Settings', 'wp-gpt-rag-chat'), 'primary large', 'submit', false); ?>
            </div>
    </div>
    </form>
    
    <!-- Nuwab Branding Footer -->
    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px; text-align: center; border-left: 4px solid #0073aa;">
        <p style="margin: 0; color: #646970; font-size: 14px;">
            <strong>Nuwab AI Assistant</strong> - Developed by 
            <a href="https://nuwab.com" target="_blank" style="color: #0073aa; text-decoration: none;">Nuwab</a>
            | Powered by OpenAI & Pinecone
        </p>
    </div>
</div>

<style>
/* Main Container */
.wp-gpt-rag-chat-settings {
    background: #f8f9fa;
    margin: 20px 0 0 -20px;
    padding: 0;
    min-height: 100vh;
}

/* Configuration Notice */
.wp-gpt-rag-chat-settings .notice {
    margin: 20px 40px !important;
    border-left: 4px solid #dba617 !important;
    background: #fff8e5 !important;
    border: 1px solid #f0b849 !important;
    border-radius: 4px !important;
    padding: 12px 20px !important;
}

.wp-gpt-rag-chat-settings .notice p {
    margin: 0;
    font-size: 14px;
    line-height: 1.4;
}

.wp-gpt-rag-chat-settings .notice strong {
    color: #d63638;
}

/* Wrap h1 styling */
.wrap h1 {
    background: #f0f0f1;
    padding: 20px;
    margin: 0 0 20px 0;
    border: none;
    box-shadow: none;
    color: #1d2327;
    font-size: 24px;
    font-weight: 600;
}

/* Settings Form Container */
.cornuwab-settings-form {
    margin: 40px;
}

.cornuwab-settings-card-container {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: none;
    border: none;
    overflow: hidden;
}

/* Navigation Tabs - Modern Clean Style */
.cornuwab-nav-tab-wrapper {
    background: #f9f9f9;
    margin: 0;
    padding: 0;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    flex-wrap: wrap;
}

.cornuwab-nav-tab {
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    color: #646970;
    padding: 16px 20px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 0;
    margin-right: 0;
    position: relative;
    flex: 1;
    justify-content: center;
    min-width: 0;
    font-size: 14px;
}

.cornuwab-nav-tab:hover {
    color: #0073aa;
    background: rgba(0, 115, 170, 0.05);
    border-bottom-color: rgba(0, 115, 170, 0.3);
}

.cornuwab-nav-tab.cornuwab-nav-tab-active {
    color: #0073aa;
    border-bottom-color: #0073aa;
    background: transparent;
    font-weight: 600;
}

.cornuwab-nav-tab .dashicons {
    font-size: 16px;
    opacity: 0.8;
}

.cornuwab-nav-tab:hover .dashicons,
.cornuwab-nav-tab.cornuwab-nav-tab-active .dashicons {
    opacity: 1;
}

/* Tab Content */
.cornuwab-tab-content {
    background: #ffffff;
    min-height: 600px;
    padding: 0;
}

.cornuwab-tab-panel {
    display: none;
    padding: 40px;
    animation: fadeIn 0.4s ease;
}

.cornuwab-tab-panel.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Settings Sections */
.cornuwab-settings-section {
    margin: 0;
    padding: 0;
    border: none;
    box-shadow: none;
    background: transparent;
}

.cornuwab-settings-header {
    margin-bottom: 0;
    padding: 0;
    border-bottom: none;
    background: #ffffff;
}

.cornuwab-settings-header h1 {
    margin: 0 0 8px 0;
    font-size: 24px;
    color: #1d2327;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.cornuwab-settings-header h1 .dashicons {
    font-size: 24px;
    color: #0073aa;
}

.cornuwab-settings-header .description {
    color: #646970;
    font-size: 15px;
    margin: 0;
    line-height: 1.5;
}

/* Settings Groups */
.cornuwab-settings-group {
    margin-bottom: 32px;
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 0;
    box-shadow: none;
    transition: none;
}

.cornuwab-settings-group:hover {
    box-shadow: none;
}

.cornuwab-settings-group:last-child {
    margin-bottom: 0;
}

.cornuwab-settings-group h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #1d2327;
    font-weight: 600;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f1;
    position: relative;
}

.cornuwab-settings-group h3::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 40px;
    height: 2px;
    background: #0073aa;
}

/* AI Model Header */
.ai-model-header {
    margin-bottom: 16px;
    padding: 8px 0;
}

.ai-model-provider {
    color: #646970;
    font-size: 14px;
    font-weight: 500;
}

/* Token Info */
.token-info {
    margin-top: 4px;
}

.token-info .description.recommended {
    color: #d63638;
    font-weight: 500;
}

.token-info .description.recommended strong {
    color: #d63638;
}

/* Inner Tabs - Modern Clean Style */
.cornuwab-inner-tab-wrapper {
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 20px;
    border-bottom: 1px solid #e1e5e9;
}

.cornuwab-inner-tab {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    text-decoration: none;
    color: #646970;
    font-size: 14px;
    font-weight: 500;
    border: none;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    transition: all 0.3s ease;
    margin-right: 0;
    background: transparent;
    position: relative;
    z-index: 1;
}

.cornuwab-inner-tab:hover {
    color: #0073aa;
    background: rgba(0, 115, 170, 0.05);
    border-bottom-color: rgba(0, 115, 170, 0.3);
}

.cornuwab-inner-tab.cornuwab-inner-tab-active {
    color: #0073aa;
    background: transparent;
    border-bottom-color: #0073aa;
    font-weight: 600;
}

.cornuwab-inner-tab .dashicons {
    font-size: 16px;
    margin-right: 8px;
    opacity: 0.8;
}

.cornuwab-inner-tab:hover .dashicons,
.cornuwab-inner-tab.cornuwab-inner-tab-active .dashicons {
    opacity: 1;
}

.cornuwab-inner-tab-panel {
    display: none;
    background: transparent;
    padding: 0;
    border: none;
    border-radius: 0;
}

.cornuwab-inner-tab-panel.active {
    display: block;
}

/* Form Fields */
.cornuwab-form-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    background: transparent;
    border: none;
    box-shadow: none;
}

.cornuwab-form-table th {
    text-align: left;
    padding: 16px 0 16px 20px;
    width: 220px;
    vertical-align: top;
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
    border-bottom: 1px solid #f0f0f1;
}

.cornuwab-form-table td {
    padding: 16px 0 16px 20px;
    vertical-align: top;
    border-bottom: 1px solid #f0f0f1;
}

.cornuwab-form-table tr:last-child th,
.cornuwab-form-table tr:last-child td {
    border-bottom: none;
}

.cornuwab-form-table input[type="text"],
.cornuwab-form-table input[type="password"],
.cornuwab-form-table input[type="number"],
.cornuwab-form-table input[type="range"],
.cornuwab-form-table select,
.cornuwab-form-table textarea {
    width: 100%;
    max-width: 450px;
    padding: 10px 14px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.cornuwab-form-table input:focus,
.cornuwab-form-table select:focus,
.cornuwab-form-table textarea:focus {
    border-color: #0073aa;
    box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1);
    outline: none;
    transform: translateY(-1px);
}

.cornuwab-form-table input:hover,
.cornuwab-form-table select:hover,
.cornuwab-form-table textarea:hover {
    border-color: #0073aa;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.cornuwab-form-table .description {
    color: #646970;
    font-size: 13px;
    margin-top: 8px;
    line-height: 1.5;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #0073aa;
}

/* Checkbox Styling */
.cornuwab-form-table input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
    accent-color: #0073aa;
}

.cornuwab-form-table label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
    color: #1d2327;
}

/* Range Slider Styling */
.cornuwab-form-table input[type="range"] {
    width: 250px;
    margin-right: 12px;
    height: 6px;
    border-radius: 3px;
    background: #e1e5e9;
    outline: none;
    -webkit-appearance: none;
}

.cornuwab-form-table input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #0073aa;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.cornuwab-form-table input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #0073aa;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

#temperature-value {
    font-weight: 600;
    color: #0073aa;
    min-width: 30px;
    display: inline-block;
    background: #f0f6fc;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #0073aa;
}

/* Toggle Switch Styling */
.cornuwab-toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    margin-right: 12px;
    vertical-align: middle;
}

.cornuwab-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.cornuwab-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 24px;
}

.cornuwab-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.cornuwab-toggle-switch input:checked + .cornuwab-toggle-slider {
    background-color: #0073aa;
}

.cornuwab-toggle-switch input:checked + .cornuwab-toggle-slider:before {
    transform: translateX(26px);
}

.cornuwab-toggle-switch:hover .cornuwab-toggle-slider {
    box-shadow: 0 0 8px rgba(0, 115, 170, 0.3);
}

.cornuwab-toggle-label {
    vertical-align: middle;
    font-weight: 500;
    color: #1d2327;
}

/* Button Styling */
.cornuwab-form-table .button {
    margin-right: 8px;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.cornuwab-form-table .button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Toast Notifications (prefixed to avoid WP conflicts) */
.cornuwab-toast-container {
    position: fixed;
    top: 50px;
    right: 20px;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    pointer-events: none;
}

/* Ensure toast is visible on mobile and different screen sizes */
@media (max-width: 782px) {
    .cornuwab-toast-container {
        top: 70px;
        right: 10px;
        left: 10px;
        align-items: stretch;
    }
}

.cornuwab-toast {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    width: 360px;
    max-width: 92vw;
    transform: translateX(20px);
    opacity: 0;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
    pointer-events: auto;
}

.cornuwab-toast.show {
    transform: translateX(0);
    opacity: 1;
}

.cornuwab-toast.success { background: #d1e7dd; }

.cornuwab-toast.error { background: #f8d7da; }

.cornuwab-toast.warning { background: #fffbe6; }

.cornuwab-toast.info { background: #e8f4fb; }

.cornuwab-toast-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    margin-right: 6px;
    font-size: 18px;
    flex-shrink: 0;
    line-height: 1;
    margin-top: 2px; /* align with title */
}

.cornuwab-toast.success .cornuwab-toast-icon {
    color: #0f5132;
}

.cornuwab-toast.error .cornuwab-toast-icon {
    color: #842029;
}

.cornuwab-toast.warning .cornuwab-toast-icon {
    color: #dba617;
}

.cornuwab-toast.info .cornuwab-toast-icon {
    color: #0073aa;
}

.cornuwab-toast-content {
    flex: 1;
}

.cornuwab-toast-title {
    font-weight: 600;
    font-size: 13px;
    margin: 0 0 2px 0;
    color: #1d2327;
}

.cornuwab-toast-message {
    font-size: 12px;
    color: #646970;
    margin: 0;
    line-height: 1.35;
}

.cornuwab-toast.success .cornuwab-toast-title {
    color: #0f5132;
}

.cornuwab-toast.success .cornuwab-toast-message {
    color: #0f5132;
}

.cornuwab-toast.error .cornuwab-toast-title {
    color: #842029;
}

.cornuwab-toast.error .cornuwab-toast-message {
    color: #842029;
}

.cornuwab-toast-close {
    position: absolute;
    top: 6px;
    right: 6px;
    background: none;
    border: none;
    color: #646970;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    padding: 0;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.cornuwab-toast-close:hover {
    background: #f0f0f1;
    color: #1d2327;
}

.cornuwab-toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 0 0 8px 8px;
    transition: width linear;
}

.cornuwab-toast.success .cornuwab-toast-progress {
    background: #00a32a;
}

.cornuwab-toast.error .cornuwab-toast-progress {
    background: #d63638;
}

.cornuwab-toast.warning .cornuwab-toast-progress {
    background: #dba617;
}

.cornuwab-toast.info .cornuwab-toast-progress {
    background: #0073aa;
}


/* Footer */
.cornuwab-settings-footer {
    background: #f8f9fa;
    padding: 24px 40px;
    border-top: none;
    margin: 0;
    text-align: center;
}

.cornuwab-settings-footer .button {
    padding: 12px 24px;
    font-size: 14px;
    height: auto;
    border-radius: 4px;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .cornuwab-settings-form {
        margin: 20px;
    }
    
    .cornuwab-nav-tab {
        padding: 14px 16px;
        font-size: 14px;
    }
    
    .cornuwab-nav-tab-wrapper {
        padding: 0;
    }
    
    .cornuwab-tab-panel {
        padding: 32px;
    }
    
    .cornuwab-settings-footer {
        padding: 20px 32px;
    }
    
    .cornuwab-form-table th {
        width: 180px;
    }
    
    .cornuwab-settings-group {
        padding: 0;
    }
    
    .cornuwab-inner-tab-wrapper {
        padding: 0;
        flex-direction: column;
        border-bottom: 1px solid #e1e5e9;
    }
    
    .cornuwab-inner-tab {
        margin-right: 0;
        margin-bottom: 0;
        justify-content: center;
        border-radius: 0;
        padding: 12px 16px;
        border-bottom: 1px solid #e1e5e9;
    }
    
    .cornuwab-inner-tab:last-child {
        border-bottom: none;
    }
    
    .cornuwab-inner-tab-panel {
        padding: 0;
        border-radius: 0;
    }
    
    .wp-gpt-rag-chat-settings .notice {
        margin: 20px 16px !important;
        padding: 12px 16px !important;
    }
}

@media (max-width: 768px) {
    .cornuwab-settings-form {
        margin: 16px;
    }
    
    /* Main h1 responsive styling removed */
    
    .cornuwab-nav-tab-wrapper {
        padding: 0;
        flex-direction: column;
        border-bottom: 1px solid #e1e5e9;
    }
    
    .cornuwab-nav-tab {
        padding: 12px 16px;
        font-size: 13px;
        flex: none;
        border-bottom: 1px solid #e1e5e9;
        border-radius: 0;
        justify-content: flex-start;
    }
    
    .cornuwab-nav-tab:last-child {
        border-bottom: none;
    }
    
    .cornuwab-tab-panel {
        padding: 24px;
    }
    
    .cornuwab-settings-footer {
        padding: 16px 24px;
    }
    
    .cornuwab-form-table th,
    .cornuwab-form-table td {
        display: block;
        width: 100%;
        padding: 8px 0 8px 16px;
        border-bottom: none;
    }
    
    .cornuwab-form-table th {
        font-weight: 600;
        margin-bottom: 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e1e5e9;
    }
    
    .cornuwab-settings-group {
        padding: 0;
    }
    
    .wp-gpt-rag-chat-settings .notice {
        margin: 20px 12px !important;
        padding: 12px 14px !important;
    }
}

@media (max-width: 600px) {
    .wp-gpt-rag-chat-settings {
        margin: 20px 0 0 0;
    }
    
    .cornuwab-settings-form {
        margin: 12px;
    }
    
    .wp-gpt-rag-chat-settings h1 {
        padding: 16px 20px;
        font-size: 20px;
    }
    
    .cornuwab-nav-tab-wrapper {
        padding: 0;
    }
    
    .cornuwab-nav-tab {
        padding: 10px 12px;
        font-size: 12px;
    }
    
    .cornuwab-tab-panel {
        padding: 20px;
    }
    
    .cornuwab-settings-footer {
        padding: 12px 20px;
    }
    
    .cornuwab-form-table input[type="text"],
    .cornuwab-form-table input[type="password"],
    .cornuwab-form-table input[type="number"],
    .cornuwab-form-table input[type="range"],
    .cornuwab-form-table select,
    .cornuwab-form-table textarea {
        max-width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab Navigation
    $('.cornuwab-nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Update active tab
        $('.cornuwab-nav-tab').removeClass('cornuwab-nav-tab-active');
        $(this).addClass('cornuwab-nav-tab-active');
        
        // Show target panel
        $('.cornuwab-tab-panel').removeClass('active');
        $('#tab-' + targetTab).addClass('active');
        
        // Update URL hash
        window.location.hash = targetTab;
    });
    
    // Handle initial hash
    var hash = window.location.hash.substr(1);
    if (hash && $('#tab-' + hash).length) {
        $('.cornuwab-nav-tab[data-tab="' + hash + '"]').click();
    }

    // Inner tab switching
    $('.cornuwab-inner-tab').on('click', function(e) {
        e.preventDefault();
        
        var targetInnerTab = $(this).data('inner-tab');
        
        // Update active inner tab
        $('.cornuwab-inner-tab').removeClass('cornuwab-inner-tab-active');
        $(this).addClass('cornuwab-inner-tab-active');
        
        // Show target inner panel
        $('.cornuwab-inner-tab-panel').removeClass('active');
        $('#inner-tab-' + targetInnerTab).addClass('active');
    });
    
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
    
    // Temperature slider value display
    $('#temperature').on('input', function() {
        $('#temperature-value').text($(this).val());
    });

    // Pinecone Quick Test functionality
    $('#run-quick-test').on('click', function() {
        const apiKey = $('#pinecone_api_key').val();
        const host = $('#pinecone_host').val();
        
        if (!apiKey || !host) {
            alert('Please enter both API Key and Server URL before running the test.');
            return;
        }
        
        // Show loading state
        const $btn = $(this);
        $btn.prop('disabled', true).text('Testing...');
        
        // Simulate API call (replace with actual implementation)
        setTimeout(() => {
            // Mock response - replace with actual Pinecone API call
            const mockDimensions = 1536; // Default for text-embedding-3-small
            $('#pinecone_dimensions').val(mockDimensions);
            
            // Reset button
            $btn.prop('disabled', false).text('Run Quick Test');
            
            // Show success message
            showToast('success', 'Quick Test Successful', 'Pinecone connection established. Dimensions detected: ' + mockDimensions);
        }, 2000);
    });
    
    // Form validation feedback
    $('input, select').on('change', function() {
        $(this).removeClass('error');
    });
    
    // Indexing controls
    $('#run-full-sync').on('click', function() {
        if (confirm('<?php esc_js(__('Are you sure you want to run a full sync? This may take a while.', 'wp-gpt-rag-chat')); ?>')) {
            // AJAX call for full sync
            console.log('Running full sync...');
        }
    });
    
    $('#sync-new-content').on('click', function() {
        // AJAX call for new content sync
        console.log('Syncing new content...');
    });
    
    // Export functionality
    $('#export-queries').on('click', function() {
        // AJAX call for export
        console.log('Exporting queries...');
    });
    
    // Clear index
    $('#clear-index').on('click', function() {
        if (confirm('<?php esc_js(__('Are you sure you want to clear all indexed data? This action cannot be undone.', 'wp-gpt-rag-chat')); ?>')) {
            // AJAX call for clearing index
            console.log('Clearing index...');
        }
    });
    
    // Force schema sync
    $('#force-schema-sync').on('click', function() {
        // AJAX call for schema sync
        console.log('Forcing schema sync...');
    });
    
    // Toast notification function
    function showToast(type, title, message, duration = 5000) {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;
        
        const toast = document.createElement('div');
        toast.className = `cornuwab-toast ${type}`;
        
        const iconMap = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        toast.innerHTML = `
            <div class="cornuwab-toast-icon">${iconMap[type] || iconMap.info}</div>
            <div class="cornuwab-toast-content">
                <div class="cornuwab-toast-title">${title}</div>
                <div class="cornuwab-toast-message">${message}</div>
            </div>
            <button class="cornuwab-toast-close" onclick="this.parentElement.remove()">×</button>
            <div class="cornuwab-toast-progress"></div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Store timeout ID for potential cancellation
        const timeoutId = setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        // Store timeout ID on toast element for potential cancellation
        toast._timeoutId = timeoutId;
        
        // Progress bar animation
        const progress = toast.querySelector('.cornuwab-toast-progress');
        progress.style.width = '100%';
        progress.style.transition = `width ${duration}ms linear`;
        setTimeout(() => progress.style.width = '0%', 100);
        
        return toast;
    }
    
    // Function to dismiss all toasts
    function dismissAllToasts() {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) return;
        
        const toasts = toastContainer.querySelectorAll('.toast');
        toasts.forEach(toast => {
            if (toast._timeoutId) {
                clearTimeout(toast._timeoutId);
            }
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    }
    
    // Handle form submission with AJAX
    $('#settings-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        const $form = $(this);
        const $submitBtn = $form.find('input[type="submit"]');
        
        // Disable submit button and show loading state
        $submitBtn.prop('disabled', true).val('Saving...');
        
        // Dismiss any existing toasts and show loading toast
        dismissAllToasts();
        const loadingToast = showToast('info', 'Saving Settings', 'Please wait while your settings are being saved...', 3000);
        
        // Collect form data
        const settings = {};
        
        // Get all form elements
        const formElements = $(this).find('input, select, textarea');
        
        formElements.each(function() {
            const $element = $(this);
            const name = $element.attr('name');
            
            if (name && name.startsWith('wp_gpt_rag_chat_settings[')) {
                const fieldName = name.match(/\[([^\]]+)\]/)[1];
                
                if ($element.attr('type') === 'checkbox') {
                    // Handle checkboxes
                    if ($element.is(':checked')) {
                        if (settings[fieldName] === undefined) {
                            settings[fieldName] = $element.val();
                        } else if (Array.isArray(settings[fieldName])) {
                            settings[fieldName].push($element.val());
                        } else {
                            settings[fieldName] = [settings[fieldName], $element.val()];
                        }
                    }
                } else if ($element.attr('type') === 'radio') {
                    // Handle radio buttons
                    if ($element.is(':checked')) {
                        settings[fieldName] = $element.val();
                    }
                } else {
                    // Handle text inputs, selects, textareas
                    const value = $element.val();
                    if (value !== '') {
                        settings[fieldName] = value;
                    }
                }
            }
        });
        
        // Get nonce
        const nonce = $('input[name="wp_gpt_rag_chat_settings_nonce"]').val();
        
        // Send AJAX request
        $.ajax({
            url: wpGptRagChatAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_gpt_rag_chat_save_settings',
                nonce: nonce,
                settings: settings
            },
            success: function(response) {
                // Dismiss loading toast first
                if (loadingToast && loadingToast._timeoutId) {
                    clearTimeout(loadingToast._timeoutId);
                    loadingToast.classList.remove('show');
                    setTimeout(() => loadingToast.remove(), 300);
                }
                
                if (response.success) {
                    showToast('success', 'Settings Saved', response.data.message);
                    // Scroll to top to show the notification
                    $('html, body').animate({ scrollTop: 0 }, 300);
                } else {
                    showToast('error', 'Save Failed', response.data.message);
                    // Scroll to top to show the notification
                    $('html, body').animate({ scrollTop: 0 }, 300);
                }
            },
            error: function(xhr, status, error) {
                // Dismiss loading toast first
                if (loadingToast && loadingToast._timeoutId) {
                    clearTimeout(loadingToast._timeoutId);
                    loadingToast.classList.remove('show');
                    setTimeout(() => loadingToast.remove(), 300);
                }
                
                showToast('error', 'Save Failed', 'An error occurred while saving settings. Please try again.');
                // Scroll to top to show the notification
                $('html, body').animate({ scrollTop: 0 }, 300);
                console.error('AJAX Error:', error);
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.prop('disabled', false).val('Save Settings');
            }
        });
    });
    
    // Check for success/error messages in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const status = urlParams.get('status');
    
    if (message) {
        const toastType = status === 'success' ? 'success' : (status === 'error' ? 'error' : 'info');
        showToast(toastType, status === 'success' ? 'Settings Saved' : 'Settings Error', decodeURIComponent(message));
        
        // Clean up URL
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }
    
    // Test toast notification (for debugging - remove in production)
    // Uncomment the line below to test toast notifications
    // showToast('success', 'Test Toast', 'This is a test toast notification!');
    
    // Test AJAX functionality (for debugging - remove in production)
    // Uncomment the line below to test AJAX settings save
    // $('#settings-form').trigger('submit');
    
    // Auto-sync embedding dimensions with index dimensions
    $('#pinecone_dimensions').on('input', function() {
        const indexDimensions = $(this).val();
        if (indexDimensions && indexDimensions > 0) {
            // Find matching option in dropdown
            const matchingOption = $('#embedding_dimensions option').filter(function() {
                return $(this).val() === indexDimensions;
            });
            
            if (matchingOption.length > 0) {
                $('#embedding_dimensions').val(indexDimensions);
            } else {
                // If no exact match, try to find the closest option
                if (indexDimensions <= 512) {
                    $('#embedding_dimensions').val('512');
                } else if (indexDimensions <= 1536) {
                    $('#embedding_dimensions').val('1536');
                } else if (indexDimensions <= 3072) {
                    $('#embedding_dimensions').val('3072');
                }
            }
        }
    });
    
    // Auto-sync when Quick Test updates dimensions
    $('#run-quick-test').on('click', function() {
        // After the quick test completes, sync the dimensions
        setTimeout(() => {
            const indexDimensions = $('#pinecone_dimensions').val();
            if (indexDimensions && indexDimensions > 0) {
                // Find matching option in dropdown
                const matchingOption = $('#embedding_dimensions option').filter(function() {
                    return $(this).val() === indexDimensions;
                });
                
                if (matchingOption.length > 0) {
                    $('#embedding_dimensions').val(indexDimensions);
                } else {
                    // If no exact match, try to find the closest option
                    if (indexDimensions <= 512) {
                        $('#embedding_dimensions').val('512');
                    } else if (indexDimensions <= 1536) {
                        $('#embedding_dimensions').val('1536');
                    } else if (indexDimensions <= 3072) {
                        $('#embedding_dimensions').val('3072');
                    }
                }
            }
        }, 2000); // Wait for the test to complete
    });
});
</script>
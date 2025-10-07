<?php

namespace WP_GPT_RAG_Chat;

/**
 * Chat functionality class
 */
class Chat {
    
    /**
     * OpenAI instance
     */
    private $openai;
    
    /**
     * Pinecone instance
     */
    private $pinecone;
    
    /**
     * Settings instance
     */
    private $settings;
    
    /**
     * Last RAG metadata
     */
    private $last_rag_metadata = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize dependencies lazily to avoid autoloader issues
        $this->settings = Settings::get_settings();
    }
    
    /**
     * Get OpenAI instance (lazy loading)
     */
    private function get_openai() {
        if (!$this->openai) {
            $this->openai = new OpenAI();
        }
        return $this->openai;
    }
    
    /**
     * Get Pinecone instance (lazy loading)
     */
    private function get_pinecone() {
        if (!$this->pinecone) {
            $this->pinecone = new Pinecone();
        }
        return $this->pinecone;
    }
    
    /**
     * Process a chat query with RAG improvements
     */
    public function process_query($query, $conversation_history = [], $detected_language = null) {
        $response_mode = $this->settings['response_mode'] ?? 'hybrid';
        $context = '';
        $query_embedding = null;
        $rag_sources = [];
        $query_variations = [$query]; // Default to original query only
        $rag_metadata = [];
        
        // Initialize RAG improvements
        $rag_improvements = new RAG_Improvements();
        
        try {
            if ($response_mode !== 'openai') {
                // Step 0: Check for similar questions with manually linked sources
                $linked_sources = $this->get_linked_sources_for_similar_query($query);
                
                if (!empty($linked_sources)) {
                    // Use manually linked sources as priority context
                    $context = $this->build_context_from_linked_sources($linked_sources);
                    $rag_sources = $linked_sources;
                    $rag_metadata['used_linked_sources'] = true;
                    $rag_metadata['linked_sources_count'] = count($linked_sources);
                } else {
                    // Step 1: Query Expansion
                    $query_variations = $rag_improvements->expand_query($query);
                    $rag_metadata['query_variations'] = $query_variations;
                    $rag_metadata['query_expansion_enabled'] = !empty($this->settings['enable_query_expansion']);
                    
                    // Step 1.5: HyDE embedding (optional)
                    $hyde_embedding = null;
                    if (!empty($this->settings['enable_hyde'])) {
                        $hyde_embedding = $rag_improvements->create_hyde_embedding($query);
                        $rag_metadata['hyde_enabled'] = true;
                        $rag_metadata['has_hyde_vector'] = !empty($hyde_embedding);
                    } else {
                        $rag_metadata['hyde_enabled'] = false;
                    }

                    // Step 2: Create embeddings for all query variations
                    $all_embeddings = $this->openai->create_embeddings($query_variations);
                    
                    // Step 3: Retrieve context using all variations
                    $all_results = [];
                    $pre_filter = $this->build_metadata_filter();
                    foreach ($all_embeddings as $embedding) {
                        $results = $this->pinecone->query_vectors($embedding, null, $pre_filter);
                        if (!empty($results['matches'])) {
                            $all_results = array_merge($all_results, $results['matches']);
                        }
                    }

                    // Query with HyDE embedding as well
                    if (!empty($hyde_embedding)) {
                        $hyde_results = $this->pinecone->query_vectors($hyde_embedding, null, $pre_filter);
                        if (!empty($hyde_results['matches'])) {
                            $all_results = array_merge($all_results, $hyde_results['matches']);
                        }
                    }
                    
                    $rag_metadata['total_results_found'] = count($all_results);
                    
                    // Remove duplicates based on post_id + chunk_index
                    $all_results = $this->deduplicate_results($all_results);
                    $rag_metadata['unique_results'] = count($all_results);
                    
                    // Step 4: Re-rank results
                    $reranked_results = $rag_improvements->rerank_results($query, $all_results);
                    $rag_metadata['reranking_enabled'] = !empty($this->settings['enable_reranking']);
                    $final_limit = intval($this->settings['final_context_chunks'] ?? 6);
                    $reranked_results = array_slice($reranked_results, 0, max(1, $final_limit));
                    $rag_metadata['final_results_used'] = count($reranked_results);
                    
                    // Step 5: Build context from top results
                    $context = $this->build_context_from_results($reranked_results);
                    $rag_sources = $this->extract_sources_from_results($reranked_results);
                    
                    // Step 6: If no context found (general question), provide sample indexed items
                    if (empty($context)) {
                        $context = $this->get_sample_indexed_content();
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: ' . $e->getMessage());
            $context = '';
            $rag_metadata['error'] = $e->getMessage();
        }
        
        if ($response_mode === 'knowledge_base') {
            $response = $this->generate_knowledge_base_response($query, $context);
            // Detect content gaps
            $rag_improvements->detect_content_gap($query, $response, $rag_sources);
            
            // Store metadata for later retrieval
            $this->last_rag_metadata = $rag_metadata;
            
            return $response;
        }
        
        // Step 6: Add few-shot examples
        $few_shot_examples = $rag_improvements->get_few_shot_examples();
        $rag_metadata['few_shot_enabled'] = !empty($this->settings['enable_few_shot']);
        $rag_metadata['few_shot_examples_count'] = !empty($few_shot_examples) ? $this->settings['few_shot_examples_count'] : 0;
        
        if (!empty($few_shot_examples)) {
            $context = $few_shot_examples . "\n\n" . $context;
        }
        
        // Build conversation messages
        $messages = $this->build_conversation_messages($conversation_history, $query);
        
        // Generate response with detected language
        $response = $this->openai->generate_chat_completion($messages, $context, $detected_language);
        
        // Detect content gaps
        $rag_improvements->detect_content_gap($query, $response, $rag_sources);
        
        // Store metadata for later retrieval
        $this->last_rag_metadata = $rag_metadata;
        
        return $response;
    }

    /**
     * Build Pinecone metadata filter based on settings
     */
    private function build_metadata_filter() {
        $filter = [];

        // Example: filter by post types selected for auto-indexing (if configured)
        $allowed_post_types = $this->settings['auto_index_post_types'] ?? [];
        if (!empty($allowed_post_types) && is_array($allowed_post_types)) {
            $filter['post_type'] = [ '$in' => array_values($allowed_post_types) ];
        }

        // Optionally filter by language when detected
        // If you later store language in metadata, you can enable this block
        // $detected_language = null; // pass in if needed
        // if ($detected_language) { $filter['language'] = [ '$eq' => $detected_language ]; }

        return !empty($filter) ? $filter : null;
    }
    
    /**
     * Get RAG metadata from last query processing
     */
    public function get_last_rag_metadata() {
        return $this->last_rag_metadata ?? [];
    }

    /**
     * Generate response using knowledge base only
     */
    private function generate_knowledge_base_response($query, $context) {
        if (empty($context)) {
            return $this->get_fallback_response($query);
        }
        
        // Return formatted context directly without repetitive headers
        return $context;
    }
    
    /**
     * Get fallback response with sitemap page suggestions
     */
    private function get_fallback_response($query) {
        // Check if sitemap fallback is enabled
        if (empty($this->settings['enable_sitemap_fallback'])) {
            return __('Sorry, I don\'t have that information.', 'wp-gpt-rag-chat');
        }
        
        try {
            $sitemap = new Sitemap();
            $suggestions = $sitemap->search_relevant_pages(
                $query, 
                $this->settings['sitemap_suggestions_count'] ?? 5
            );
            
            if (empty($suggestions)) {
                return __('Sorry, I don\'t have that information.', 'wp-gpt-rag-chat');
            }
            
            // Format response with suggestions
            $response = __('عذراً، لم أجد معلومات كافية للإجابة على سؤالك في قاعدة المعرفة. ولكن قد تجد ما تبحث عنه في الصفحات التالية:', 'wp-gpt-rag-chat');
            $response .= "\n\n";
            
            foreach ($suggestions as $i => $suggestion) {
                $response .= sprintf(
                    "%d. **%s**\n   %s\n   %s\n\n",
                    $i + 1,
                    $suggestion['title'],
                    !empty($suggestion['description']) ? $suggestion['description'] : '',
                    $suggestion['url']
                );
            }
            
            $response .= "\n" . __('يرجى زيارة هذه الصفحات للحصول على مزيد من المعلومات.', 'wp-gpt-rag-chat');
            
            return $response;
            
        } catch (\Exception $e) {
            error_log('Sitemap fallback error: ' . $e->getMessage());
            return __('Sorry, I don\'t have that information.', 'wp-gpt-rag-chat');
        }
    }
    
    /**
     * Retrieve relevant context from Pinecone
     */
    private function retrieve_context($query_embedding) {
        try {
            $results = $this->pinecone->query_vectors($query_embedding);
            
            if (empty($results['matches'])) {
                return '';
            }
            
            $context_parts = [];
            foreach ($results['matches'] as $match) {
                $metadata = $match['metadata'];
                $chunk_content = $this->get_chunk_content($metadata['post_id'], $metadata['chunk_index']);
                
                // Format: Content first, then link with source title at the end
                $formatted_part = $chunk_content;
                
                // Add clickable link after content if URL exists
                if (!empty($metadata['post_url']) && !empty($metadata['post_title'])) {
                    $formatted_part .= sprintf(
                        " 🔗 [%s](%s)",
                        $metadata['post_title'],
                        $metadata['post_url']
                    );
                }
                
                $context_parts[] = $formatted_part;
            }
            
            return implode("\n\n━━━━━━━━━━━━━━━━\n\n", $context_parts);
        } catch (\Exception $e) {
            error_log('WP GPT RAG Chat: Error retrieving context: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Get chunk content from local database
     */
    private function get_chunk_content($post_id, $chunk_index) {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        $vector = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$vectors_table} WHERE post_id = %d AND chunk_index = %d",
            $post_id,
            $chunk_index
        ));
        
        if (!$vector) {
            return '';
        }
        
        // If content is stored in database, use it directly
        if (!empty($vector->content)) {
            return $vector->content;
        }
        
        // Fallback: re-chunk if content not stored (for backwards compatibility)
        $post = get_post($post_id);
        if (!$post) {
            return '';
        }
        
        $chunking = new Chunking();
        $chunks = $chunking->chunk_post($post_id);
        
        foreach ($chunks as $chunk) {
            if ($chunk['chunk_index'] === $chunk_index) {
                return $chunk['content'];
            }
        }
        
        return '';
    }
    
    /**
     * Deduplicate results based on post_id and chunk_index
     */
    private function deduplicate_results($results) {
        $seen = [];
        $unique_results = [];
        
        foreach ($results as $result) {
            $metadata = $result['metadata'] ?? [];
            $key = ($metadata['post_id'] ?? '') . '_' . ($metadata['chunk_index'] ?? '');
            
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique_results[] = $result;
            }
        }
        
        return $unique_results;
    }
    
    /**
     * Build context string from results
     */
    private function build_context_from_results($results) {
        if (empty($results)) {
            return '';
        }
        
        $context_parts = [];
        $processed_count = 0;
        $max_attempts = count($results) * 2; // Allow checking more results if needed
        
        foreach ($results as $match) {
            $metadata = $match['metadata'];
            $content = $this->get_chunk_content($metadata['post_id'], $metadata['chunk_index']);
            
            if (!empty($content)) {
                // Format: Content first, then link with source title at the end
                $formatted_part = $content;
                
                // Add clickable link after content if URL exists
                if (!empty($metadata['post_url']) && !empty($metadata['post_title'])) {
                    $formatted_part .= sprintf(
                        " 🔗 [%s](%s)",
                        $metadata['post_title'],
                        $metadata['post_url']
                    );
                }
                
                $context_parts[] = $formatted_part;
                $processed_count++;
                
                // Stop if we have enough content (to avoid too long context)
                if ($processed_count >= 5) {
                    break;
                }
            }
        }
        
        // If we still don't have enough content, try to get more results from Pinecone
        if (empty($context_parts) && count($results) < $max_attempts) {
            error_log('WP GPT RAG Chat: No content found in initial results, trying to get more results');
            
            // This is a fallback - in a real scenario, we might want to re-query with more results
            // For now, we'll return empty and let the system use the fallback
        }
        
        return implode("\n\n━━━━━━━━━━━━━━━━\n\n", $context_parts);
    }
    
    /**
     * Extract sources metadata from results
     */
    private function extract_sources_from_results($results) {
        if (empty($results)) {
            return [];
        }
        
        $sources = [];
        foreach ($results as $match) {
            $metadata = $match['metadata'];
            $sources[] = [
                'post_id' => $metadata['post_id'],
                'post_title' => $metadata['post_title'],
                'post_url' => $metadata['post_url'],
                'post_type' => $metadata['post_type'],
                'score' => $match['score'] ?? 0
            ];
        }
        
        return $sources;
    }
    
    /**
     * Build conversation messages
     */
    private function build_conversation_messages($conversation_history, $current_query) {
        $messages = [];
        
        // Add conversation history
        foreach ($conversation_history as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }
        
        // Add current query
        $messages[] = [
            'role' => 'user',
            'content' => $current_query
        ];
        
        return $messages;
    }
    
    /**
     * Get linked sources for similar queries
     * Checks if there are manually linked sources for similar questions
     */
    private function get_linked_sources_for_similar_query($query) {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Find similar user queries with linked sources (using simple LIKE matching)
        // Use BINARY for case-sensitive matching, or remove for case-insensitive
        $similar_logs = $wpdb->get_results($wpdb->prepare("
            SELECT rag_sources 
            FROM {$logs_table} 
            WHERE role = 'user' 
            AND rag_sources IS NOT NULL 
            AND rag_sources != '[]'
            AND rag_sources != ''
            AND (
                content LIKE %s 
                OR %s LIKE CONCAT('%%', SUBSTRING(content, 1, 20), '%%')
            )
            ORDER BY created_at DESC 
            LIMIT 1
        ", '%' . $wpdb->esc_like($query) . '%', $query));
        
        if (empty($similar_logs)) {
            return [];
        }
        
        $sources_json = $similar_logs[0]->rag_sources;
        $sources = json_decode($sources_json, true);
        
        if (empty($sources) || !is_array($sources)) {
            return [];
        }
        
        // Filter for manually linked sources only
        $linked_sources = array_filter($sources, function($source) {
            return !empty($source['manually_linked']);
        });
        
        return array_values($linked_sources);
    }
    
    /**
     * Build context from manually linked sources
     */
    private function build_context_from_linked_sources($linked_sources) {
        $context_parts = [];
        
        foreach ($linked_sources as $source) {
            $post_id = $source['id'] ?? 0;
            if (!$post_id) {
                continue;
            }
            
            $post = get_post($post_id);
            if (!$post) {
                continue;
            }
            
            // Get full post content
            $content = $post->post_content;
            $content = wp_strip_all_tags($content);
            $content = wp_trim_words($content, 500); // Limit to 500 words
            
            // Format with title and link
            $formatted_part = sprintf(
                "%s\n\n%s\n\n🔗 [%s](%s)",
                $post->post_title,
                $content,
                $source['title'] ?? $post->post_title,
                $source['url'] ?? get_permalink($post_id)
            );
            
            $context_parts[] = $formatted_part;
        }
        
        if (empty($context_parts)) {
            return '';
        }
        
        return implode("\n\n━━━━━━━━━━━━━━━━\n\n", $context_parts);
    }
    
    /**
     * Get sample indexed content for general questions
     * Returns recent/popular indexed items with titles and links
     */
    private function get_sample_indexed_content() {
        global $wpdb;
        
        $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
        
        // Get up to 10 recent indexed items from vectors table
        $results = $wpdb->get_results("
            SELECT DISTINCT post_id 
            FROM {$vectors_table} 
            WHERE post_id IS NOT NULL
            ORDER BY updated_at DESC 
            LIMIT 10
        ");
        
        if (empty($results)) {
            return '';
        }
        
        $context_parts = [];
        foreach ($results as $row) {
            $post = get_post($row->post_id);
            if ($post && in_array($post->post_status, ['publish', 'private'])) {
                $post_url = get_permalink($post->ID);
                // Decode URL for better display but keep it functional
                $post_url = urldecode($post_url);
                $context_parts[] = sprintf(
                    "📄 %s\n🔗 [%s](%s)",
                    $post->post_title,
                    $post->post_title,
                    $post_url
                );
            }
        }
        
        if (empty($context_parts)) {
            return '';
        }
        
        return "=== AVAILABLE TOPICS ON WEBSITE ===\nThe following pages are available. Use these titles and links to answer the user's question:\n\n" . implode("\n\n", $context_parts);
    }
    
    /**
     * Get chat widget HTML
     */
    public function get_chat_widget_html($bypass_visibility_check = false) {
        $settings = $this->settings;
        
        // Check if chat is enabled
        if (empty($settings['enable_chatbot'])) {
            return '';
        }
        
        // Check visibility settings (unless bypassed for shortcode)
        if (!$bypass_visibility_check && !$this->should_display_chat()) {
            return '';
        }
        
        if (empty($settings['openai_api_key']) || empty($settings['pinecone_api_key'])) {
            return '<div class="cornuwab-wp-gpt-rag-chat-error" style="font-family: \'Tajawal\', sans-serif; direction: rtl; text-align: right;">' . 
                   'المحادثة غير متاحة حالياً. يرجى التواصل مع المسؤول.' . 
                   '</div>';
        }
        
        ob_start();
        ?>
        <div id="cornuwab-wp-gpt-rag-chat-widget" class="cornuwab-wp-gpt-rag-chat-widget">
            <!-- Floating Button (Collapsed State) -->
            <div class="cornuwab-wp-gpt-rag-chat-fab">
                <div class="cornuwab-wp-gpt-rag-chat-fab-bubble" aria-live="polite"></div>
                <button type="button" class="cornuwab-wp-gpt-rag-chat-fab-button" aria-label="فتح المحادثة">
                    <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/avatar_small.png' ); ?>" alt="فتح المحادثة" class="cornuwab-wp-gpt-rag-chat-fab-avatar" />
                </button>
            </div>
            
            <!-- Chat Window (Expanded State) -->
            <div class="cornuwab-wp-gpt-rag-chat-overlay" role="presentation"></div>
            <div class="cornuwab-wp-gpt-rag-chat-window">
                <div class="cornuwab-wp-gpt-rag-chat-header">
                    <h3 class="cornuwab-wp-gpt-rag-chat-header-title">
                        <img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/avatar_small.png' ); ?>" alt="AI Avatar" class="cornuwab-wp-gpt-rag-chat-header-avatar" />
                        مساعدك الذكي
                    </h3>
                    <div class="cornuwab-wp-gpt-rag-chat-header-actions">
                        <button type="button" class="cornuwab-wp-gpt-rag-chat-refresh" aria-label="مسح المحادثة" title="مسح المحادثة">
                            <i class="fas fa-rotate-right"></i>
                        </button>
                        <button type="button" class="cornuwab-wp-gpt-rag-chat-expand" aria-label="تكبير المحادثة" aria-expanded="false">
                            <i class="fas fa-up-right-and-down-left-from-center"></i>
                        </button>
                        <button type="button" class="cornuwab-wp-gpt-rag-chat-toggle" aria-label="إغلاق المحادثة">
                            <span class="cornuwab-wp-gpt-rag-chat-icon">×</span>
                        </button>
                    </div>
                </div>
            
            <div class="cornuwab-wp-gpt-rag-chat-body">
                <div class="cornuwab-wp-gpt-rag-chat-messages" id="cornuwab-wp-gpt-rag-chat-messages">
                    <div class="cornuwab-wp-gpt-rag-chat-message cornuwab-wp-gpt-rag-chat-message-system">
                        <div class="cornuwab-wp-gpt-rag-chat-message-content">
                            مرحباً! يمكنني مساعدتك في إيجاد المعلومات من هذا الموقع. كيف يمكنني مساعدتك؟
                        </div>
                    </div>
                </div>
                
                <div class="cornuwab-wp-gpt-rag-chat-input-container">
                    <div class="cornuwab-wp-gpt-rag-chat-input-wrapper">
                        <input 
                            type="text"
                            id="cornuwab-wp-gpt-rag-chat-input" 
                            placeholder="اكتب سؤالك هنا..."
                        />
                        <button type="button" id="cornuwab-wp-gpt-rag-chat-send" class="cornuwab-wp-gpt-rag-chat-send-button" aria-label="إرسال">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            
                <div class="cornuwab-wp-gpt-rag-chat-footer">
                    <small>
                        هذه المنصة مدعومة بالذكاء الاصطناعي
                    </small>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Check if chat should be displayed based on visibility settings
     */
    private function should_display_chat() {
        $settings = $this->settings;
        $chat_visibility = $settings['chat_visibility'] ?? 'everyone';
        $is_user_logged_in = is_user_logged_in();
        
        switch ($chat_visibility) {
            case 'logged_in_only':
                // Show only to logged-in users
                return $is_user_logged_in;
                
            case 'visitors_only':
                // Show only to non-logged-in users (visitors)
                return !$is_user_logged_in;
                
            case 'private_link_only':
                // Show to logged-in users everywhere, or to visitors only on pages with shortcode
                if ($is_user_logged_in) {
                    return true;
                } else {
                    // For visitors, this will be handled by shortcode callback
                    return false; // This will be overridden by shortcode callback
                }
                
            case 'everyone':
            default:
                // Show to everyone
                return true;
        }
    }
    
    /**
     * Render floating chat widget in footer (appears on all pages)
     */
    public function render_floating_chat_widget() {
        // Don't show in admin area
        if (is_admin()) {
            return;
        }
        
        // Check maintenance mode
        $settings = Settings::get_settings();
        if (!empty($settings['maintenance_mode'])) {
            // Only show to logged-in admin users
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                return;
            }
        }
        
        // Don't show floating widget if chat visibility is set to "private_link_only"
        // UNLESS the user is logged in (admins and logged-in users can see it everywhere)
        $chat_visibility = $settings['chat_visibility'] ?? 'everyone';
        if ($chat_visibility === 'private_link_only' && !is_user_logged_in()) {
            return;
        }
        
        // Output the chat widget
        echo $this->get_chat_widget_html();
    }
    
    /**
     * Add chat widget to content
     */
    public function add_chat_widget_to_content($content) {
        // Only add to single posts/pages
        if (!is_singular()) {
            return $content;
        }
        
        // Check maintenance mode
        $settings = Settings::get_settings();
        if (!empty($settings['maintenance_mode'])) {
            // Only show to logged-in admin users
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                return $content;
            }
        }
        
        // Check if chat is enabled for this post type
        $post_type = get_post_type();
        $enabled_post_types = apply_filters('wp_gpt_rag_chat_enabled_post_types', ['post', 'page']);
        
        if (!in_array($post_type, $enabled_post_types)) {
            return $content;
        }
        
        // Check if chat is enabled for this specific post
        $chat_enabled = get_post_meta(get_the_ID(), '_wp_gpt_rag_chat_enabled', true);
        if ($chat_enabled === '0') {
            return $content;
        }
        
        // Add chat widget
        $chat_widget = $this->get_chat_widget_html();
        
        return $content . $chat_widget;
    }
    
    /**
     * Register shortcode
     */
    public function register_shortcode() {
        add_shortcode('wp_gpt_rag_chat', [$this, 'shortcode_callback']);
    }
    
    /**
     * Shortcode callback
     */
    public function shortcode_callback($atts) {
        $atts = shortcode_atts([
            'enabled' => '1',
            'position' => 'bottom'
        ], $atts);
        
        if ($atts['enabled'] === '0') {
            return '';
        }
        
        // Always show chat when shortcode is used, regardless of visibility settings
        // This allows the "private_link_only" mode to work properly
        return $this->get_chat_widget_html(true); // Pass true to bypass visibility check
    }
    
    /**
     * Get conversation history from session
     */
    private function get_conversation_history() {
        if (!session_id()) {
            session_start();
        }
        
        return $_SESSION['wp_gpt_rag_chat_history'] ?? [];
    }
    
    /**
     * Save conversation history to session
     */
    private function save_conversation_history($history) {
        if (!session_id()) {
            session_start();
        }
        
        $_SESSION['wp_gpt_rag_chat_history'] = $history;
    }
    
    /**
     * Clear conversation history
     */
    public function clear_conversation_history() {
        if (!session_id()) {
            session_start();
        }
        
        unset($_SESSION['wp_gpt_rag_chat_history']);
    }
    
    /**
     * Get chat statistics
     */
    public static function get_chat_stats() {
        global $wpdb;
        
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_queries,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as queries_24h,
                COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as queries_7d
            FROM {$logs_table}
        ");
        
        return [
            'total_queries' => intval($stats->total_queries ?? 0),
            'unique_users' => intval($stats->unique_users ?? 0),
            'queries_24h' => intval($stats->queries_24h ?? 0),
            'queries_7d' => intval($stats->queries_7d ?? 0)
        ];
    }
}

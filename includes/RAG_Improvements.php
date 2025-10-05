<?php

namespace WP_GPT_RAG_Chat;

/**
 * RAG Improvements Class
 * Handles query expansion, re-ranking, few-shot learning, and content gap analysis
 */
class RAG_Improvements {
    
    private $settings;
    private $openai;
    private $analytics;
    
    public function __construct() {
        $this->settings = Settings::get_settings();
        $this->openai = new OpenAI();
        $this->analytics = new Analytics();
    }
    
    /**
     * Expand query with multiple variations
     * Improves retrieval by searching with different phrasings
     */
    public function expand_query($original_query) {
        // Check if query expansion is enabled
        if (empty($this->settings['enable_query_expansion'])) {
            return [$original_query];
        }
        
        try {
            // Use GPT to generate query variations
            $prompt = $this->build_query_expansion_prompt($original_query);
            
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'timeout' => 15,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->settings['openai_api_key'],
                    'Content-Type' => 'application/json'
                ],
                'body' => wp_json_encode([
                    'model' => 'gpt-3.5-turbo',  // Use faster model for query expansion
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt],
                        ['role' => 'user', 'content' => $original_query]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 150
                ])
            ]);
            
            if (is_wp_error($response)) {
                return [$original_query];
            }
            
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $expanded_text = $body['choices'][0]['message']['content'] ?? '';
            
            if (empty($expanded_text)) {
                return [$original_query];
            }
            
            // Parse variations (one per line)
            $variations = array_filter(array_map('trim', explode("\n", $expanded_text)));
            
            // Remove numbering (1., 2., etc.)
            $variations = array_map(function($v) {
                return preg_replace('/^\d+[\.\)]\s*/', '', $v);
            }, $variations);
            
            // Ensure original query is included
            if (!in_array($original_query, $variations)) {
                array_unshift($variations, $original_query);
            }
            
            // Limit to 3 variations
            return array_slice($variations, 0, 3);
            
        } catch (\Exception $e) {
            error_log('Query expansion error: ' . $e->getMessage());
            return [$original_query];
        }
    }

    /**
     * Generate a short hypothetical answer (HyDE) for better retrieval
     */
    public function generate_hyde_answer($original_query) {
        if (empty($this->settings['enable_hyde'])) {
            return '';
        }

        try {
            $is_arabic = preg_match('/[\x{0600}-\x{06FF}]/u', $original_query);
            $system = $is_arabic
                ? "اكتب إجابة قصيرة ومباشرة للسؤال التالي كما لو كانت صحيحة وموجودة في الموقع. لا تضف آراء أو أمثلة. لا تتجاوز 5-7 جمل."
                : "Write a short, direct answer to the user question as if it were correct and present in the docs. No opinions, no examples. Keep it 5-7 sentences.";

            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'timeout' => 20,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->settings['openai_api_key'],
                    'Content-Type' => 'application/json'
                ],
                'body' => wp_json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $original_query]
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 220
                ])
            ]);

            if (is_wp_error($response)) {
                return '';
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            return trim($body['choices'][0]['message']['content'] ?? '');
        } catch (\Exception $e) {
            error_log('HyDE generation error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Create an embedding vector for HyDE
     */
    public function create_hyde_embedding($original_query) {
        if (empty($this->settings['enable_hyde'])) {
            return null;
        }

        $hypo = $this->generate_hyde_answer($original_query);
        if (empty($hypo)) {
            return null;
        }

        try {
            $embeddings = $this->openai->create_embeddings([$hypo]);
            return $embeddings[0] ?? null;
        } catch (\Exception $e) {
            error_log('HyDE embedding error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Build prompt for query expansion
     */
    private function build_query_expansion_prompt($query) {
        // Detect language
        $is_arabic = preg_match('/[\x{0600}-\x{06FF}]/u', $query);
        
        if ($is_arabic) {
            return "أنت خبير في إعادة صياغة الأسئلة. قم بإعادة كتابة السؤال التالي بـ 2 طرق مختلفة مع الحفاظ على نفس المعنى. اكتب كل صياغة في سطر منفصل. كن مختصراً.

مثال:
المدخل: ما هي سياسة الاسترجاع؟
المخرج:
1. كيف يمكنني إرجاع المنتج؟
2. ما هي شروط استرداد الأموال؟";
        } else {
            return "You are an expert at rephrasing questions. Rewrite the following query in 2 different ways while keeping the same meaning. Write each variation on a new line. Be concise.

Example:
Input: What is your refund policy?
Output:
1. How can I return a product?
2. What are the conditions for getting my money back?";
        }
    }
    
    /**
     * Re-rank retrieved results using cross-encoder or GPT scoring
     */
    public function rerank_results($original_query, $results) {
        // Check if re-ranking is enabled
        if (empty($this->settings['enable_reranking']) || empty($results)) {
            return $results;
        }
        
        try {
            // If LLM re-ranker is enabled, try it first
            if (!empty($this->settings['enable_llm_rerank'])) {
                $llm_sorted = $this->llm_rerank($original_query, $results);
                if (!empty($llm_sorted)) {
                    return $llm_sorted;
                }
            }

            // Score each result against the query
            $scored_results = [];
            
            foreach ($results as $result) {
                $content = $this->extract_content_from_result($result);
                $score = $this->score_relevance($original_query, $content);
                
                $scored_results[] = [
                    'result' => $result,
                    'rerank_score' => $score,
                    'original_score' => $result['score'] ?? 0
                ];
            }
            
            // Sort by re-ranking score
            usort($scored_results, function($a, $b) {
                // Combine original similarity and re-rank score
                $score_a = ($a['original_score'] * 0.6) + ($a['rerank_score'] * 0.4);
                $score_b = ($b['original_score'] * 0.6) + ($b['rerank_score'] * 0.4);
                return $score_b <=> $score_a;
            });
            
            // Return all results sorted; caller can slice as needed
            return array_map(function($item) {
                return $item['result'];
            }, $scored_results);
            
        } catch (\Exception $e) {
            error_log('Re-ranking error: ' . $e->getMessage());
            return $results;
        }
    }

    /**
     * LLM-based re-ranker for better relevance on top candidates
     */
    private function llm_rerank($original_query, $results) {
        try {
            $top_k = intval($this->settings['llm_rerank_top_k'] ?? 20);
            $candidates = array_slice($results, 0, max(1, $top_k));

            // Build passages
            $passages = [];
            foreach ($candidates as $i => $res) {
                $content = $this->extract_content_from_result($res);
                $content = mb_substr($content, 0, 800);
                $passages[] = [
                    'idx' => $i,
                    'content' => $content
                ];
            }

            if (empty($passages)) {
                return $results;
            }

            $instructions = "You are a relevance judge. Score each passage from 0.0 to 1.0 for how well it answers the user query. Return ONLY a compact JSON array of objects: [{\"idx\":number,\"score\":number}] with the given idx values, no text, no markdown.";

            $prompt = [
                ['role' => 'system', 'content' => $instructions],
                ['role' => 'user', 'content' => "Query:\n" . $original_query]
            ];

            $passages_text = "\nPassages:\n";
            foreach ($passages as $p) {
                $passages_text .= "#" . $p['idx'] . ": " . $p['content'] . "\n\n";
            }
            $prompt[] = ['role' => 'user', 'content' => $passages_text . "\nReturn JSON now."];

            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'timeout' => 25,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->settings['openai_api_key'],
                    'Content-Type' => 'application/json'
                ],
                'body' => wp_json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => $prompt,
                    'temperature' => 0.0,
                    'max_tokens' => 200
                ])
            ]);

            if (is_wp_error($response)) {
                return $results;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            $content = $body['choices'][0]['message']['content'] ?? '';
            $json = json_decode(trim($content), true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($json)) {
                return $results;
            }

            // Build score map
            $score_map = [];
            foreach ($json as $row) {
                if (isset($row['idx']) && isset($row['score'])) {
                    $score_map[intval($row['idx'])] = floatval($row['score']);
                }
            }

            // Combine with original similarity
            $scored = [];
            foreach ($candidates as $i => $res) {
                $orig = $res['score'] ?? 0;
                $llm = $score_map[$i] ?? 0;
                $combined = ($orig * 0.5) + ($llm * 0.5);
                $scored[] = [
                    'result' => $res,
                    'score' => $combined
                ];
            }

            usort($scored, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Append any leftover results not scored by LLM (preserve order)
            $sorted = array_map(function($r) { return $r['result']; }, $scored);
            if (count($results) > count($candidates)) {
                $sorted = array_merge($sorted, array_slice($results, count($candidates)));
            }

            return $sorted;
        } catch (\Exception $e) {
            error_log('LLM rerank error: ' . $e->getMessage());
            return $results;
        }
    }
    
    /**
     * Score relevance using simple heuristics
     * In production, use a cross-encoder model for better accuracy
     */
    private function score_relevance($query, $content) {
        $score = 0.0;
        
        // Normalize
        $query_lower = mb_strtolower($query);
        $content_lower = mb_strtolower($content);
        
        // Tokenize
        $query_tokens = preg_split('/\s+/', $query_lower);
        $query_tokens = array_filter($query_tokens, function($t) { return mb_strlen($t) > 2; });
        
        // Count matches
        $matches = 0;
        foreach ($query_tokens as $token) {
            if (mb_strpos($content_lower, $token) !== false) {
                $matches++;
            }
        }
        
        $token_count = count($query_tokens);
        if ($token_count > 0) {
            $score = $matches / $token_count;
        }
        
        // Boost if query appears as phrase
        if (mb_strpos($content_lower, $query_lower) !== false) {
            $score += 0.3;
        }
        
        // Length penalty for very short content
        $content_length = mb_strlen($content);
        if ($content_length < 50) {
            $score *= 0.7;
        }
        
        return min($score, 1.0);
    }
    
    /**
     * Extract content from Pinecone result
     */
    private function extract_content_from_result($result) {
        if (isset($result['metadata']['content'])) {
            return $result['metadata']['content'];
        }
        
        // Fallback: get from database
        if (isset($result['metadata']['post_id']) && isset($result['metadata']['chunk_index'])) {
            global $wpdb;
            $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
            
            $vector = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$vectors_table} WHERE post_id = %d AND chunk_index = %d",
                $result['metadata']['post_id'],
                $result['metadata']['chunk_index']
            ));
            
            if ($vector && isset($vector->content)) {
                return $vector->content;
            }
        }
        
        return '';
    }
    
    /**
     * Get few-shot examples from excellent tagged conversations
     */
    public function get_few_shot_examples($limit = 5) {
        // Check if few-shot learning is enabled
        if (empty($this->settings['enable_few_shot'])) {
            return '';
        }
        
        global $wpdb;
        $logs_table = $wpdb->prefix . 'wp_gpt_rag_chat_logs';
        
        // Get excellent conversations (tagged as "excellent" or "good_answer" with thumbs up)
        $examples = $wpdb->get_results($wpdb->prepare(
            "SELECT l1.content as user_query, l2.content as assistant_response
            FROM {$logs_table} l1
            JOIN {$logs_table} l2 ON l1.chat_id = l2.chat_id AND l1.turn_number = l2.turn_number AND l2.role = 'assistant'
            WHERE l1.role = 'user'
            AND l2.rating = 1
            AND (l2.tags LIKE %s OR l2.tags LIKE %s)
            ORDER BY l2.created_at DESC
            LIMIT %d",
            '%excellent%',
            '%good_answer%',
            $limit
        ));
        
        if (empty($examples)) {
            return '';
        }
        
        // Format examples
        $formatted = "\n\nHere are examples of excellent responses:\n\n";
        
        foreach ($examples as $i => $example) {
            $formatted .= sprintf(
                "Example %d:\nUser: %s\nAssistant: %s\n\n",
                $i + 1,
                $example->user_query,
                $example->assistant_response
            );
        }
        
        $formatted .= "Now provide a similar high-quality response to the user's question.\n";
        
        return $formatted;
    }
    
    /**
     * Detect if response indicates content gap
     */
    public function detect_content_gap($query, $response, $rag_sources = []) {
        $is_gap = false;
        $gap_reason = '';
        
        // Check 1: No sources found
        if (empty($rag_sources)) {
            $is_gap = true;
            $gap_reason = 'no_sources_found';
        }
        
        // Check 2: Low similarity scores
        if (!empty($rag_sources)) {
            $max_score = 0;
            foreach ($rag_sources as $source) {
                if (isset($source['score']) && $source['score'] > $max_score) {
                    $max_score = $source['score'];
                }
            }
            
            if ($max_score < 0.65) {
                $is_gap = true;
                $gap_reason = 'low_similarity';
            }
        }
        
        // Check 3: Response contains "don't know" phrases
        $no_answer_patterns = [
            '/I don\'?t (have|know|find)/i',
            '/cannot find/i',
            '/no information/i',
            '/لا أجد/u',
            '/لا يوجد/u',
            '/عذراً/u',
            '/للأسف/u'
        ];
        
        foreach ($no_answer_patterns as $pattern) {
            if (preg_match($pattern, $response)) {
                $is_gap = true;
                $gap_reason = 'no_answer_response';
                break;
            }
        }
        
        // Log content gap
        if ($is_gap) {
            $this->log_content_gap($query, $gap_reason);
        }
        
        return $is_gap;
    }
    
    /**
     * Log content gap for analysis
     */
    private function log_content_gap($query, $reason) {
        global $wpdb;
        $gaps_table = $wpdb->prefix . 'wp_gpt_rag_chat_content_gaps';
        
        // Check if table exists, create if not
        $this->ensure_content_gaps_table();
        
        // Insert or update gap log
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$gaps_table} WHERE query_hash = %s",
            md5(mb_strtolower(trim($query)))
        ));
        
        if ($existing) {
            // Update frequency
            $wpdb->update(
                $gaps_table,
                [
                    'frequency' => $existing->frequency + 1,
                    'last_seen' => current_time('mysql')
                ],
                ['id' => $existing->id],
                ['%d', '%s'],
                ['%d']
            );
        } else {
            // Insert new gap
            $wpdb->insert(
                $gaps_table,
                [
                    'query' => $query,
                    'query_hash' => md5(mb_strtolower(trim($query))),
                    'gap_reason' => $reason,
                    'frequency' => 1,
                    'created_at' => current_time('mysql'),
                    'last_seen' => current_time('mysql')
                ],
                ['%s', '%s', '%s', '%d', '%s', '%s']
            );
        }
    }
    
    /**
     * Ensure content gaps table exists
     */
    private function ensure_content_gaps_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_gpt_rag_chat_content_gaps';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            query text NOT NULL,
            query_hash varchar(32) NOT NULL,
            gap_reason varchar(50) NOT NULL,
            frequency int NOT NULL DEFAULT 1,
            status varchar(20) DEFAULT 'open',
            created_at datetime NOT NULL,
            last_seen datetime NOT NULL,
            PRIMARY KEY (id),
            KEY query_hash (query_hash),
            KEY status (status)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get content gaps for dashboard
     */
    public function get_content_gaps($limit = 20, $status = 'open') {
        global $wpdb;
        $gaps_table = $wpdb->prefix . 'wp_gpt_rag_chat_content_gaps';
        
        $this->ensure_content_gaps_table();
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$gaps_table} 
            WHERE status = %s
            ORDER BY frequency DESC, last_seen DESC
            LIMIT %d",
            $status,
            $limit
        ));
    }
    
    /**
     * Mark content gap as resolved
     */
    public function resolve_content_gap($gap_id) {
        global $wpdb;
        $gaps_table = $wpdb->prefix . 'wp_gpt_rag_chat_content_gaps';
        
        return $wpdb->update(
            $gaps_table,
            ['status' => 'resolved'],
            ['id' => $gap_id],
            ['%s'],
            ['%d']
        );
    }
}


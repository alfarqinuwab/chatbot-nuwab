<?php

namespace WP_GPT_RAG_Chat;

/**
 * Vector Database Handler
 * Manages vector operations with Pinecone
 */
class Vector_DB {
    
    private $pinecone;
    private $settings;
    
    public function __construct() {
        $this->settings = Settings::get_settings();
        $this->pinecone = new Pinecone();
    }
    
    /**
     * Get vector database statistics
     */
    public function get_stats() {
        try {
            return $this->pinecone->get_index_stats();
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat: Error getting vector stats - ' . $e->getMessage());
            return [
                'total_vectors' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Search for similar vectors
     */
    public function search($query_vector, $top_k = 5, $filter = null) {
        try {
            return $this->pinecone->query_vectors($query_vector, $top_k, $filter);
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat: Error searching vectors - ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Upsert vectors to Pinecone
     */
    public function upsert_vectors($vectors) {
        try {
            return $this->pinecone->upsert_vectors($vectors);
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat: Error upserting vectors - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete vectors from Pinecone
     */
    public function delete_vectors($vector_ids) {
        try {
            return $this->pinecone->delete_vectors($vector_ids);
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat: Error deleting vectors - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear all vectors from Pinecone
     */
    public function clear_all() {
        try {
            // Use delete_vectors_by_filter with empty filter to delete all
            return $this->pinecone->delete_vectors_by_filter([]);
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat: Error clearing vectors - ' . $e->getMessage());
            return false;
        }
    }
}

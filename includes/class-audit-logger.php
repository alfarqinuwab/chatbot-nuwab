<?php
/**
 * Audit Logger Helper
 * 
 * Provides easy methods to log common actions
 */

namespace WP_GPT_RAG_Chat;

if (!defined('ABSPATH')) {
    exit;
}

class Audit_Logger {
    
    private static $audit_trail;
    
    public static function init() {
        self::$audit_trail = new Audit_Trail();
    }
    
    /**
     * Log user login
     */
    public static function log_login($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        self::$audit_trail->log(
            'login',
            'user',
            $user_id,
            sprintf(__('User %s logged in', 'wp-gpt-rag-chat'), $user->user_login),
            ['user_login' => $user->user_login],
            'low',
            'success'
        );
    }
    
    /**
     * Log user logout
     */
    public static function log_logout($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        self::$audit_trail->log(
            'logout',
            'user',
            $user_id,
            sprintf(__('User %s logged out', 'wp-gpt-rag-chat'), $user->user_login),
            ['user_login' => $user->user_login],
            'low',
            'success'
        );
    }
    
    /**
     * Log settings update
     */
    public static function log_settings_update($settings, $old_settings = []) {
        $changes = [];
        foreach ($settings as $key => $value) {
            if (!isset($old_settings[$key]) || $old_settings[$key] !== $value) {
                $changes[$key] = [
                    'old' => $old_settings[$key] ?? null,
                    'new' => $value
                ];
            }
        }
        
        if (empty($changes)) return;
        
        self::$audit_trail->log(
            'settings_update',
            'settings',
            null,
            sprintf(__('Settings updated: %d changes', 'wp-gpt-rag-chat'), count($changes)),
            ['changes' => $changes],
            'medium',
            'success'
        );
    }
    
    /**
     * Log content indexing
     */
    public static function log_content_index($post_id, $post_type = 'post') {
        $post = get_post($post_id);
        if (!$post) return;
        
        self::$audit_trail->log(
            'content_index',
            $post_type,
            $post_id,
            sprintf(__('Content indexed: %s', 'wp-gpt-rag-chat'), $post->post_title),
            [
                'post_title' => $post->post_title,
                'post_type' => $post_type,
                'post_status' => $post->post_status
            ],
            'low',
            'success'
        );
    }
    
    /**
     * Log content unindexing
     */
    public static function log_content_unindex($post_id, $post_type = 'post') {
        $post = get_post($post_id);
        if (!$post) return;
        
        self::$audit_trail->log(
            'content_unindex',
            $post_type,
            $post_id,
            sprintf(__('Content unindexed: %s', 'wp-gpt-rag-chat'), $post->post_title),
            [
                'post_title' => $post->post_title,
                'post_type' => $post_type,
                'post_status' => $post->post_status
            ],
            'low',
            'success'
        );
    }
    
    /**
     * Log chat start
     */
    public static function log_chat_start($chat_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        $user_login = $user ? $user->user_login : 'anonymous';
        
        self::$audit_trail->log(
            'chat_start',
            'chat',
            $chat_id,
            sprintf(__('Chat started by %s', 'wp-gpt-rag-chat'), $user_login),
            [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'user_login' => $user_login
            ],
            'low',
            'success'
        );
    }
    
    /**
     * Log chat end
     */
    public static function log_chat_end($chat_id, $user_id = null, $message_count = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('id', $user_id);
        $user_login = $user ? $user->user_login : 'anonymous';
        
        self::$audit_trail->log(
            'chat_end',
            'chat',
            $chat_id,
            sprintf(__('Chat ended by %s (%d messages)', 'wp-gpt-rag-chat'), $user_login, $message_count),
            [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'user_login' => $user_login,
                'message_count' => $message_count
            ],
            'low',
            'success'
        );
    }
    
    /**
     * Log API call
     */
    public static function log_api_call($api_name, $endpoint, $status = 'success', $response_time = null, $metadata = []) {
        $severity = $status === 'success' ? 'low' : 'medium';
        
        self::$audit_trail->log(
            'api_call',
            'api',
            $api_name,
            sprintf(__('API call to %s: %s', 'wp-gpt-rag-chat'), $api_name, $status),
            array_merge([
                'api_name' => $api_name,
                'endpoint' => $endpoint,
                'status' => $status,
                'response_time' => $response_time
            ], $metadata),
            $severity,
            $status
        );
    }
    
    /**
     * Log error
     */
    public static function log_error($error_message, $error_code = null, $context = []) {
        self::$audit_trail->log(
            'error_occurred',
            'system',
            null,
            $error_message,
            array_merge([
                'error_code' => $error_code,
                'error_message' => $error_message
            ], $context),
            'high',
            'error'
        );
    }
    
    /**
     * Log permission denied
     */
    public static function log_permission_denied($action, $object_type = null, $object_id = null) {
        self::$audit_trail->log(
            'permission_denied',
            $object_type ?? 'system',
            $object_id,
            sprintf(__('Permission denied for action: %s', 'wp-gpt-rag-chat'), $action),
            [
                'action' => $action,
                'object_type' => $object_type,
                'object_id' => $object_id
            ],
            'medium',
            'error'
        );
    }
    
    /**
     * Log data export
     */
    public static function log_export($export_type, $record_count = 0, $format = 'csv') {
        self::$audit_trail->log(
            'export_data',
            'export',
            $export_type,
            sprintf(__('Data exported: %s (%d records, %s format)', 'wp-gpt-rag-chat'), $export_type, $record_count, $format),
            [
                'export_type' => $export_type,
                'record_count' => $record_count,
                'format' => $format
            ],
            'low',
            'success'
        );
    }
    
    /**
     * Log data import
     */
    public static function log_import($import_type, $record_count = 0, $format = 'csv') {
        self::$audit_trail->log(
            'import_data',
            'import',
            $import_type,
            sprintf(__('Data imported: %s (%d records, %s format)', 'wp-gpt-rag-chat'), $import_type, $record_count, $format),
            [
                'import_type' => $import_type,
                'record_count' => $record_count,
                'format' => $format
            ],
            'medium',
            'success'
        );
    }
    
    /**
     * Log security event
     */
    public static function log_security_event($event_type, $description, $metadata = []) {
        self::$audit_trail->log(
            'security_scan',
            'security',
            null,
            $description,
            array_merge([
                'event_type' => $event_type
            ], $metadata),
            'high',
            'success'
        );
    }
    
    /**
     * Log system backup
     */
    public static function log_backup($backup_type, $file_size = 0, $file_path = null) {
        self::$audit_trail->log(
            'system_backup',
            'system',
            $backup_type,
            sprintf(__('System backup created: %s', 'wp-gpt-rag-chat'), $backup_type),
            [
                'backup_type' => $backup_type,
                'file_size' => $file_size,
                'file_path' => $file_path
            ],
            'medium',
            'success'
        );
    }
    
    /**
     * Log system restore
     */
    public static function log_restore($restore_type, $file_path = null) {
        self::$audit_trail->log(
            'system_restore',
            'system',
            $restore_type,
            sprintf(__('System restored from: %s', 'wp-gpt-rag-chat'), $restore_type),
            [
                'restore_type' => $restore_type,
                'file_path' => $file_path
            ],
            'high',
            'success'
        );
    }
    
    /**
     * Log role change
     */
    public static function log_role_change($user_id, $old_role, $new_role) {
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        self::$audit_trail->log(
            'role_changed',
            'user',
            $user_id,
            sprintf(__('User %s role changed from %s to %s', 'wp-gpt-rag-chat'), $user->user_login, $old_role, $new_role),
            [
                'user_login' => $user->user_login,
                'old_role' => $old_role,
                'new_role' => $new_role
            ],
            'high',
            'success'
        );
    }
    
    /**
     * Log audit trail export
     */
    public static function log_audit_export($format, $record_count = 0) {
        self::$audit_trail->log(
            'audit_export',
            'audit',
            null,
            sprintf(__('Audit trail exported (%d records, %s format)', 'wp-gpt-rag-chat'), $record_count, $format),
            [
                'format' => $format,
                'record_count' => $record_count
            ],
            'medium',
            'success'
        );
    }
    
    /**
     * Log audit trail cleanup
     */
    public static function log_audit_cleanup($deleted_count, $days_kept = 365) {
        self::$audit_trail->log(
            'audit_cleanup',
            'audit',
            null,
            sprintf(__('Audit trail cleaned up: %d entries deleted (kept %d days)', 'wp-gpt-rag-chat'), $deleted_count, $days_kept),
            [
                'deleted_count' => $deleted_count,
                'days_kept' => $days_kept
            ],
            'low',
            'success'
        );
    }
}

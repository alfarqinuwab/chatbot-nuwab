<?php
/**
 * Plugin Name: WP GPT RAG Chat
 * Plugin URI: https://example.com/wp-gpt-rag-chat
 * Description: A production-ready WordPress plugin that delivers OpenAI + Pinecone retrieval-augmented chatbot over WordPress content.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-gpt-rag-chat
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_GPT_RAG_CHAT_VERSION', '1.0.0');
define('WP_GPT_RAG_CHAT_PLUGIN_FILE', __FILE__);
define('WP_GPT_RAG_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_GPT_RAG_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_GPT_RAG_CHAT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Requirement check functions
function wp_gpt_rag_chat_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('WP GPT RAG Chat requires PHP 7.4 or higher. Please upgrade your PHP version.', 'wp-gpt-rag-chat');
    echo '</p></div>';
}

function wp_gpt_rag_chat_wp_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('WP GPT RAG Chat requires WordPress 5.0 or higher. Please upgrade WordPress.', 'wp-gpt-rag-chat');
    echo '</p></div>';
}

// Minimum requirements check
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', 'wp_gpt_rag_chat_php_version_notice');
    return;
}

if (version_compare(get_bloginfo('version'), '5.0', '<')) {
    add_action('admin_notices', 'wp_gpt_rag_chat_wp_version_notice');
    return;
}

// Autoloader function
function wp_gpt_rag_chat_autoloader($class) {
    $prefix = 'WP_GPT_RAG_Chat\\';
    $base_dir = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        try {
            require $file;
        } catch (Exception $e) {
            error_log('WP GPT RAG Chat autoloader error: ' . $e->getMessage());
        }
    }
}

// Register autoloader
spl_autoload_register('wp_gpt_rag_chat_autoloader');

// Plugin initialization functions
function wp_gpt_rag_chat_init() {
    try {
        // Load text domain
        if (function_exists('load_plugin_textdomain')) {
            load_plugin_textdomain('wp-gpt-rag-chat', false, dirname(WP_GPT_RAG_CHAT_PLUGIN_BASENAME) . '/languages');
        }
        
        // Initialize main plugin class
        if (class_exists('WP_GPT_RAG_Chat\\Plugin')) {
            new WP_GPT_RAG_Chat\Plugin();
        }
    } catch (Exception $e) {
        error_log('WP GPT RAG Chat initialization error: ' . $e->getMessage());
    }
}

function wp_gpt_rag_chat_activate() {
    if (class_exists('WP_GPT_RAG_Chat\\Plugin')) {
        WP_GPT_RAG_Chat\Plugin::activate();
    }
}

function wp_gpt_rag_chat_deactivate() {
    if (class_exists('WP_GPT_RAG_Chat\\Plugin')) {
        WP_GPT_RAG_Chat\Plugin::deactivate();
    }
}

function wp_gpt_rag_chat_uninstall() {
    if (class_exists('WP_GPT_RAG_Chat\\Plugin')) {
        WP_GPT_RAG_Chat\Plugin::uninstall();
    }
}

// Initialize the plugin
add_action('plugins_loaded', 'wp_gpt_rag_chat_init');

// Activation hook
register_activation_hook(__FILE__, 'wp_gpt_rag_chat_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_gpt_rag_chat_deactivate');

// Uninstall hook
register_uninstall_hook(__FILE__, 'wp_gpt_rag_chat_uninstall');

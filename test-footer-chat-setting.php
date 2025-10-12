<?php
/**
 * Test script to verify the footer chat widget setting works correctly
 * 
 * This script tests that when show_chat_in_footer is set to false,
 * the floating chat widget is not displayed.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../wp-load.php');
}

// Include the plugin classes
require_once('includes/class-settings.php');
require_once('includes/class-chat.php');

use WP_GPT_RAG_Chat\Settings;
use WP_GPT_RAG_Chat\Chat;

echo "<h2>Testing Footer Chat Widget Setting</h2>\n";

// Test 1: Check current settings
$settings = Settings::get_settings();
echo "<h3>Current Settings:</h3>\n";
echo "<pre>";
print_r([
    'show_chat_in_footer' => $settings['show_chat_in_footer'] ?? 'not set',
    'enable_chatbot' => $settings['enable_chatbot'] ?? 'not set',
    'chat_visibility' => $settings['chat_visibility'] ?? 'not set'
]);
echo "</pre>\n";

// Test 2: Simulate form submission with unchecked checkbox
echo "<h3>Testing Unchecked Checkbox Handling:</h3>\n";

// Simulate form data without show_chat_in_footer (unchecked checkbox)
$test_input = [
    'enable_chatbot' => '1',
    'chat_visibility' => 'everyone',
    'widget_placement' => 'floating',
    'greeting_text' => 'Hello! How can I help you today?',
    'enable_history' => '1',
    'max_conversation_length' => '10',
    'allow_anonymous' => '1',
    'response_mode' => 'hybrid',
    // Note: show_chat_in_footer is NOT included (simulating unchecked checkbox)
];

// Create a new Settings instance to test sanitization
$settings_instance = new Settings();

// Use reflection to access the private sanitize_settings method
$reflection = new ReflectionClass($settings_instance);
$sanitize_method = $reflection->getMethod('sanitize_settings');
$sanitize_method->setAccessible(true);

$sanitized = $sanitize_method->invoke($settings_instance, $test_input);

echo "<strong>Input data (show_chat_in_footer not included):</strong><br>\n";
echo "<pre>" . print_r($test_input, true) . "</pre>\n";

echo "<strong>Sanitized result:</strong><br>\n";
echo "<pre>" . print_r($sanitized, true) . "</pre>\n";

echo "<strong>show_chat_in_footer value:</strong> " . ($sanitized['show_chat_in_footer'] ? 'true' : 'false') . "<br>\n";

if ($sanitized['show_chat_in_footer'] === false) {
    echo "<span style='color: green;'>✅ SUCCESS: show_chat_in_footer is correctly set to false when checkbox is unchecked</span><br>\n";
} else {
    echo "<span style='color: red;'>❌ FAILED: show_chat_in_footer should be false when checkbox is unchecked</span><br>\n";
}

// Test 3: Simulate form submission with checked checkbox
echo "<h3>Testing Checked Checkbox Handling:</h3>\n";

$test_input_checked = $test_input;
$test_input_checked['show_chat_in_footer'] = '1'; // Simulate checked checkbox

$sanitized_checked = $sanitize_method->invoke($settings_instance, $test_input_checked);

echo "<strong>Input data (show_chat_in_footer = '1'):</strong><br>\n";
echo "<pre>" . print_r($test_input_checked, true) . "</pre>\n";

echo "<strong>Sanitized result:</strong><br>\n";
echo "<pre>" . print_r($sanitized_checked, true) . "</pre>\n";

echo "<strong>show_chat_in_footer value:</strong> " . ($sanitized_checked['show_chat_in_footer'] ? 'true' : 'false') . "<br>\n";

if ($sanitized_checked['show_chat_in_footer'] === true) {
    echo "<span style='color: green;'>✅ SUCCESS: show_chat_in_footer is correctly set to true when checkbox is checked</span><br>\n";
} else {
    echo "<span style='color: red;'>❌ FAILED: show_chat_in_footer should be true when checkbox is checked</span><br>\n";
}

// Test 4: Test the render_floating_chat_widget method
echo "<h3>Testing render_floating_chat_widget Method:</h3>\n";

$chat = new Chat();

// Test with show_chat_in_footer = false
$original_settings = get_option('wp_gpt_rag_chat_settings', []);
update_option('wp_gpt_rag_chat_settings', array_merge($original_settings, ['show_chat_in_footer' => false]));

echo "<strong>Testing with show_chat_in_footer = false:</strong><br>\n";
ob_start();
$chat->render_floating_chat_widget();
$output = ob_get_clean();

if (empty($output)) {
    echo "<span style='color: green;'>✅ SUCCESS: No output when show_chat_in_footer is false</span><br>\n";
} else {
    echo "<span style='color: red;'>❌ FAILED: Should not output anything when show_chat_in_footer is false</span><br>\n";
    echo "<strong>Output:</strong><br>\n";
    echo "<pre>" . htmlspecialchars($output) . "</pre>\n";
}

// Test with show_chat_in_footer = true
update_option('wp_gpt_rag_chat_settings', array_merge($original_settings, ['show_chat_in_footer' => true]));

echo "<strong>Testing with show_chat_in_footer = true:</strong><br>\n";
ob_start();
$chat->render_floating_chat_widget();
$output = ob_get_clean();

if (!empty($output)) {
    echo "<span style='color: green;'>✅ SUCCESS: Output generated when show_chat_in_footer is true</span><br>\n";
} else {
    echo "<span style='color: red;'>❌ FAILED: Should output chat widget when show_chat_in_footer is true</span><br>\n";
}

// Restore original settings
update_option('wp_gpt_rag_chat_settings', $original_settings);

echo "<h3>Test Complete!</h3>\n";
echo "<p>If all tests show ✅ SUCCESS, then the footer chat widget setting is working correctly.</p>\n";
?>

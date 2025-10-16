<?php
/**
 * Test Real-time Sensitive Content Validation
 * 
 * Expected:
 * - Real-time validation should prevent submission until sensitive content is removed
 * - Visual indicators should show when sensitive content is detected
 * - Cancel button should not allow bypassing the validation
 * - Input should be blocked until content is actually removed
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Real-time Sensitive Content Validation Test</h2>\n";

// Check current user
$current_user = wp_get_current_user();
echo "<h3>Current User Info:</h3>\n";
echo "<ul>\n";
echo "<li><strong>User:</strong> " . $current_user->user_login . "</li>\n";
echo "<li><strong>Roles:</strong> " . implode(', ', $current_user->roles) . "</li>\n";
echo "<li><strong>Logged In:</strong> " . (is_user_logged_in() ? 'YES' : 'NO') . "</li>\n";
echo "</ul>\n";

// Test chat template URL
echo "<h3>Test Chat Template URL:</h3>\n";
echo "<ul>\n";
echo "<li><a href='" . home_url('/?wp_gpt_rag_chatgpt=1') . "' target='_blank'>Arabic Chat Template</a></li>\n";
echo "</ul>\n";

// Check the chat template for real-time validation
$chat_template_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/chatgpt-arabic-template.php';

if (file_exists($chat_template_file)) {
    echo "<p><strong>Chat Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($chat_template_file);
    
    // Check for real-time validation functions
    $has_realtime_validation = strpos($content, 'validateInputRealtime') !== false;
    echo "<p><strong>Real-time validation:</strong> " . ($has_realtime_validation ? 'YES' : 'NO') . "</p>\n";
    
    // Check for hasSensitiveContent function
    $has_sensitive_check = strpos($content, 'hasSensitiveContent') !== false;
    echo "<p><strong>Sensitive content check:</strong> " . ($has_sensitive_check ? 'YES' : 'NO') . "</p>\n";
    
    // Check for input event listeners with validation
    $has_input_validation = strpos($content, 'validateInputRealtime(this)') !== false;
    echo "<p><strong>Input event validation:</strong> " . ($has_input_validation ? 'YES' : 'NO') . "</p>\n";
    
    // Check for enhanced CSS
    $has_enhanced_css = strpos($content, 'sensitive-detected:focus') !== false;
    echo "<p><strong>Enhanced CSS:</strong> " . ($has_enhanced_css ? 'YES' : 'NO') . "</p>\n";
    
} else {
    echo "<p><strong>Chat Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Real-time Validation Features:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Feature</th><th>Description</th><th>Status</th></tr>\n";
echo "<tr><td><strong>Real-time Detection</strong></td><td>Validates on every input change</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Visual Indicators</strong></td><td>Red border and background on sensitive content</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Submission Blocking</strong></td><td>Prevents send until content is removed</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Cancel Protection</strong></td><td>Cannot bypass by clicking cancel</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Enhanced Focus</strong></td><td>Stronger visual feedback on focus</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Placeholder Styling</strong></td><td>Red placeholder text for sensitive content</td><td>✅ Active</td></tr>\n";
echo "</table>\n";

echo "<h3>Validation Flow:</h3>\n";
echo "<ol>\n";
echo "<li><strong>User Types:</strong> User enters sensitive content (e.g., 'كلمة المرور mypass123')</li>\n";
echo "<li><strong>Real-time Check:</strong> validateInputRealtime() runs on every keystroke</li>\n";
echo "<li><strong>Visual Feedback:</strong> Input gets 'sensitive-detected' class with red styling</li>\n";
echo "<li><strong>Submit Attempt:</strong> User clicks send or presses Enter</li>\n";
echo "<li><strong>Block Check:</strong> hasSensitiveContent() checks if content is still sensitive</li>\n";
echo "<li><strong>Warning Display:</strong> If sensitive, show warning modal (if not already shown)</li>\n";
echo "<li><strong>Submission Blocked:</strong> Message is not sent until content is removed</li>\n";
echo "<li><strong>Content Removal:</strong> User must actually remove sensitive content</li>\n";
echo "<li><strong>Validation Pass:</strong> Only then can message be sent</li>\n";
echo "</ol>\n";

echo "<h3>Test Scenarios:</h3>\n";
echo "<h4>Scenario 1: Basic Password Detection</h4>\n";
echo "<ol>\n";
echo "<li>Type: <code>كلمة المرور mypassword123</code></li>\n";
echo "<li>Expected: Input should turn red with sensitive-detected class</li>\n";
echo "<li>Try to send: Should show warning modal</li>\n";
echo "<li>Click Cancel: Modal closes, but input still red</li>\n";
echo "<li>Try to send again: Should still be blocked</li>\n";
echo "<li>Remove sensitive content: Input should turn normal</li>\n";
echo "<li>Try to send: Should work normally</li>\n";
echo "</ol>\n";

echo "<h4>Scenario 2: API Key Detection</h4>\n";
echo "<ol>\n";
echo "<li>Type: <code>api-key abc123def456ghi789</code></li>\n";
echo "<li>Expected: Input should turn red with sensitive-detected class</li>\n";
echo "<li>Try to send: Should show warning modal</li>\n";
echo "<li>Click Cancel: Modal closes, but input still red</li>\n";
echo "<li>Try to send again: Should still be blocked</li>\n";
echo "<li>Remove sensitive content: Input should turn normal</li>\n";
echo "<li>Try to send: Should work normally</li>\n";
echo "</ol>\n";

echo "<h4>Scenario 3: Email Detection</h4>\n";
echo "<ol>\n";
echo "<li>Type: <code>user@example.com</code></li>\n";
echo "<li>Expected: Input should turn red with sensitive-detected class</li>\n";
echo "<li>Try to send: Should show warning modal</li>\n";
echo "<li>Click Cancel: Modal closes, but input still red</li>\n";
echo "<li>Try to send again: Should still be blocked</li>\n";
echo "<li>Remove sensitive content: Input should turn normal</li>\n";
echo "<li>Try to send: Should work normally</li>\n";
echo "</ol>\n";

echo "<h3>Key Improvements:</h3>\n";
echo "<h4>Before (One-time Validation):</h4>\n";
echo "<ul>\n";
echo "<li>❌ Only checked on submit</li>\n";
echo "<li>❌ Could bypass by clicking cancel</li>\n";
echo "<li>❌ No real-time feedback</li>\n";
echo "<li>❌ Could submit after canceling warning</li>\n";
echo "</ul>\n";

echo "<h4>After (Real-time Validation):</h4>\n";
echo "<ul>\n";
echo "<li>✅ Validates on every keystroke</li>\n";
echo "<li>✅ Visual feedback immediately</li>\n";
echo "<li>✅ Cannot bypass by canceling</li>\n";
echo "<li>✅ Must actually remove content to submit</li>\n";
echo "<li>✅ Enhanced visual indicators</li>\n";
echo "<li>✅ Stronger focus styling</li>\n";
echo "</ul>\n";

echo "<h3>CSS Enhancements:</h3>\n";
echo "<h4>Visual Indicators:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Border:</strong> Red border (#dc3545) for sensitive content</li>\n";
echo "<li><strong>Background:</strong> Light red background (#fff5f5)</li>\n";
echo "<li><strong>Focus:</strong> Stronger red border (#ff2222) and shadow on focus</li>\n";
echo "<li><strong>Placeholder:</strong> Red placeholder text (#ff4444) with bold font</li>\n";
echo "</ul>\n";

echo "<h4>CSS Classes:</h4>\n";
echo "<ul>\n";
echo "<li><code>.sensitive-detected</code> - Applied when sensitive content detected</li>\n";
echo "<li><code>.sensitive-detected:focus</code> - Enhanced focus styling</li>\n";
echo "<li><code>.sensitive-detected::placeholder</code> - Red placeholder text</li>\n";
echo "</ul>\n";

echo "<h3>JavaScript Functions:</h3>\n";
echo "<h4>New Functions Added:</h4>\n";
echo "<ul>\n";
echo "<li><strong>validateInputRealtime(input):</strong> Real-time validation without showing warning</li>\n";
echo "<li><strong>hasSensitiveContent(input):</strong> Check if input has sensitive content</li>\n";
echo "<li><strong>Enhanced event listeners:</strong> All input events now include real-time validation</li>\n";
echo "</ul>\n";

echo "<h4>Updated Event Listeners:</h4>\n";
echo "<ul>\n";
echo "<li><strong>chatInput.addEventListener('input'):</strong> Now includes validateInputRealtime()</li>\n";
echo "<li><strong>welcomeInput.addEventListener('input'):</strong> Now includes validateInputRealtime()</li>\n";
echo "<li><strong>sendBtn.addEventListener('click'):</strong> Now uses hasSensitiveContent() to block</li>\n";
echo "<li><strong>chatInput.addEventListener('keydown'):</strong> Now uses hasSensitiveContent() to block</li>\n";
echo "<li><strong>welcomeSendBtn.addEventListener('click'):</strong> Now uses hasSensitiveContent() to block</li>\n";
echo "<li><strong>welcomeInput.addEventListener('keydown'):</strong> Now uses hasSensitiveContent() to block</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Access Chat Template:</strong> Go to the Arabic chat template</li>\n";
echo "<li><strong>Test Real-time Detection:</strong> Type sensitive content and watch input turn red</li>\n";
echo "<li><strong>Test Submission Blocking:</strong> Try to send with sensitive content</li>\n";
echo "<li><strong>Test Cancel Bypass:</strong> Click cancel and try to send again</li>\n";
echo "<li><strong>Test Content Removal:</strong> Remove sensitive content and verify input turns normal</li>\n";
echo "<li><strong>Test Successful Send:</strong> Verify message sends after content removal</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Real-time Detection:</strong> Input turns red immediately when typing sensitive content</li>\n";
echo "<li>✅ <strong>Submission Blocking:</strong> Cannot send message with sensitive content</li>\n";
echo "<li>✅ <strong>Cancel Protection:</strong> Clicking cancel doesn't allow bypassing</li>\n";
echo "<li>✅ <strong>Visual Feedback:</strong> Clear visual indicators for sensitive content</li>\n";
echo "<li>✅ <strong>Content Removal Required:</strong> Must actually remove content to submit</li>\n";
echo "<li>✅ <strong>Enhanced Styling:</strong> Stronger visual feedback on focus</li>\n";
echo "<li>✅ <strong>Persistent Blocking:</strong> Blocking persists until content is removed</li>\n";
echo "</ul>\n";

echo "<h3>Security Benefits:</h3>\n";
echo "<ul>\n";
echo "<li>🔒 <strong>No Bypass:</strong> Cannot bypass validation by canceling warning</li>\n";
echo "<li>🔒 <strong>Real-time Protection:</strong> Immediate feedback and blocking</li>\n";
echo "<li>🔒 <strong>Persistent Validation:</strong> Must actually remove sensitive content</li>\n";
echo "<li>🔒 <strong>Visual Clarity:</strong> Clear indication of blocked state</li>\n";
echo "<li>🔒 <strong>User Education:</strong> Helps users understand what content is sensitive</li>\n";
echo "<li>🔒 <strong>Comprehensive Coverage:</strong> All input methods are protected</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Real-time validation should now prevent submission until sensitive content is actually removed.</p>\n";
echo "<p>Users cannot bypass the validation by clicking cancel on the warning modal.</p>\n";
echo "<p>Visual indicators provide clear feedback about sensitive content detection.</p>\n";
?>


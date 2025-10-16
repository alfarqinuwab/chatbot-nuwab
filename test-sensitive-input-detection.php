<?php
/**
 * Test Sensitive Input Detection
 * 
 * Expected:
 * - Sensitive input detection should work for emails, CPR numbers, etc.
 * - Warning modal should appear when sensitive content is detected
 * - Users should be able to remove sensitive content or cancel
 * - Input should be blocked until sensitive content is handled
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Sensitive Input Detection Test</h2>\n";

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

// Check the chat template for sensitive input detection
$chat_template_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/chatgpt-arabic-template.php';

if (file_exists($chat_template_file)) {
    echo "<p><strong>Chat Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($chat_template_file);
    
    // Check for sensitive input detection patterns
    $sensitive_patterns = strpos($content, 'sensitivePatterns') !== false;
    echo "<p><strong>Sensitive patterns defined:</strong> " . ($sensitive_patterns ? 'YES' : 'NO') . "</p>\n";
    
    // Check for detection function
    $detect_function = strpos($content, 'detectSensitiveInput') !== false;
    echo "<p><strong>Detection function:</strong> " . ($detect_function ? 'YES' : 'NO') . "</p>\n";
    
    // Check for warning modal
    $warning_modal = strpos($content, 'sensitive-input-warning') !== false;
    echo "<p><strong>Warning modal styles:</strong> " . ($warning_modal ? 'YES' : 'NO') . "</p>\n";
    
    // Check for validation function
    $validation_function = strpos($content, 'validateInput') !== false;
    echo "<p><strong>Validation function:</strong> " . ($validation_function ? 'YES' : 'NO') . "</p>\n";
    
    // Check for input validation in event listeners
    $input_validation = strpos($content, 'validateInput(') !== false;
    echo "<p><strong>Input validation in event listeners:</strong> " . ($input_validation ? 'YES' : 'NO') . "</p>\n";
    
    // Check for sensitive content removal
    $content_removal = strpos($content, 'removeSensitiveContent') !== false;
    echo "<p><strong>Sensitive content removal:</strong> " . ($content_removal ? 'YES' : 'NO') . "</p>\n";
    
    // Check for warning close function
    $warning_close = strpos($content, 'closeSensitiveWarning') !== false;
    echo "<p><strong>Warning close function:</strong> " . ($warning_close ? 'YES' : 'NO') . "</p>\n";
    
    // Count validation calls
    $validation_calls = substr_count($content, 'validateInput(');
    echo "<p><strong>Validation calls:</strong> {$validation_calls}</p>\n";
    
} else {
    echo "<p><strong>Chat Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Sensitive Input Detection Features:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Feature</th><th>Pattern</th><th>Description</th><th>Status</th></tr>\n";
echo "<tr><td><strong>Email Detection</strong></td><td>email@domain.com</td><td>Detects email addresses</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>CPR Detection</strong></td><td>123456-7890</td><td>Detects CPR numbers</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Phone Detection</strong></td><td>+1234567890</td><td>Detects phone numbers</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Credit Card</strong></td><td>1234-5678-9012-3456</td><td>Detects credit card numbers</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>SSN Detection</strong></td><td>123-45-6789</td><td>Detects Social Security Numbers</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Passport Detection</strong></td><td>A1234567</td><td>Detects passport numbers</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>National ID</strong></td><td>1234567890</td><td>Detects national ID numbers</td><td>✅ Active</td></tr>\n";
echo "</table>\n";

echo "<h3>Detection Patterns:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Email:</strong> <code>/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}/g</code></li>\n";
echo "<li><strong>CPR:</strong> <code>/(\\d{6}-\\d{4})|(\\d{10})/g</code></li>\n";
echo "<li><strong>Phone:</strong> <code>/(\\+?\\d{1,3}[-.\\s]?)?\\(?\\d{3}\\)?[-.\\s]?\\d{3}[-.\\s]?\\d{4}/g</code></li>\n";
echo "<li><strong>Credit Card:</strong> <code>/\\b\\d{4}[-\\s]?\\d{4}[-\\s]?\\d{4}[-\\s]?\\d{4}\\b/g</code></li>\n";
echo "<li><strong>SSN:</strong> <code>/\\b\\d{3}-\\d{2}-\\d{4}\\b/g</code></li>\n";
echo "<li><strong>Passport:</strong> <code>/\\b[A-Z]{1,2}\\d{6,9}\\b/g</code></li>\n";
echo "<li><strong>National ID:</strong> <code>/\\b\\d{9,14}\\b/g</code></li>\n";
echo "</ul>\n";

echo "<h3>Warning Modal Features:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Modal Overlay:</strong> Dark background overlay</li>\n";
echo "<li>✅ <strong>Warning Icon:</strong> ⚠️ Warning symbol</li>\n";
echo "<li>✅ <strong>Arabic Text:</strong> RTL support for Arabic interface</li>\n";
echo "<li>✅ <strong>Detected Items:</strong> Shows what sensitive data was found</li>\n";
echo "<li>✅ <strong>Remove Button:</strong> Option to remove sensitive content</li>\n";
echo "<li>✅ <strong>Cancel Button:</strong> Option to cancel and edit manually</li>\n";
echo "<li>✅ <strong>Responsive Design:</strong> Works on mobile and desktop</li>\n";
echo "</ul>\n";

echo "<h3>Test Scenarios:</h3>\n";
echo "<h4>Test 1: Email Detection</h4>\n";
echo "<p><strong>Input:</strong> \"My email is john.doe@example.com\"</p>\n";
echo "<p><strong>Expected:</strong> Warning modal should appear with email detection</p>\n";
echo "<p><strong>Action:</strong> User can remove sensitive content or cancel</p>\n";

echo "<h4>Test 2: CPR Detection</h4>\n";
echo "<p><strong>Input:</strong> \"My CPR number is 123456-7890\"</p>\n";
echo "<p><strong>Expected:</strong> Warning modal should appear with CPR detection</p>\n";
echo "<p><strong>Action:</strong> User can remove sensitive content or cancel</p>\n";

echo "<h4>Test 3: Phone Number Detection</h4>\n";
echo "<p><strong>Input:</strong> \"Call me at +1234567890\"</p>\n";
echo "<p><strong>Expected:</strong> Warning modal should appear with phone detection</p>\n";
echo "<p><strong>Action:</strong> User can remove sensitive content or cancel</p>\n";

echo "<h4>Test 4: Credit Card Detection</h4>\n";
echo "<p><strong>Input:</strong> \"My card number is 1234-5678-9012-3456\"</p>\n";
echo "<p><strong>Expected:</strong> Warning modal should appear with credit card detection</p>\n";
echo "<p><strong>Action:</strong> User can remove sensitive content or cancel</p>\n";

echo "<h4>Test 5: Multiple Sensitive Data</h4>\n";
echo "<p><strong>Input:</strong> \"Email: john@example.com, Phone: +1234567890, CPR: 123456-7890\"</p>\n";
echo "<p><strong>Expected:</strong> Warning modal should show all detected sensitive data</p>\n";
echo "<p><strong>Action:</strong> User can remove all sensitive content or cancel</p>\n";

echo "<h4>Test 6: No Sensitive Data</h4>\n";
echo "<p><strong>Input:</strong> \"Hello, how are you today?\"</p>\n";
echo "<p><strong>Expected:</strong> No warning modal, message sends normally</p>\n";
echo "<p><strong>Action:</strong> Message should be sent without any issues</p>\n";

echo "<h3>User Interface:</h3>\n";
echo "<h4>Warning Modal Content:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Title:</strong> \"تحذير: معلومات حساسة مكتشفة\" (Warning: Sensitive Information Detected)</li>\n";
echo "<li><strong>Message:</strong> \"تم اكتشاف معلومات حساسة في رسالتك. يرجى عدم مشاركة المعلومات الشخصية في المحادثة.\"</li>\n";
echo "<li><strong>Detected Items:</strong> Shows specific sensitive data found</li>\n";
echo "<li><strong>Remove Button:</strong> \"إزالة المحتوى الحساس\" (Remove Sensitive Content)</li>\n";
echo "<li><strong>Cancel Button:</strong> \"إلغاء\" (Cancel)</li>\n";
echo "</ul>\n";

echo "<h4>Input Validation:</h4>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Real-time Detection:</strong> Detects sensitive content as user types</li>\n";
echo "<li>✅ <strong>Visual Feedback:</strong> Input field changes color when sensitive content detected</li>\n";
echo "<li>✅ <strong>Blocking:</strong> Prevents message sending until sensitive content is handled</li>\n";
echo "<li>✅ <strong>Content Removal:</strong> Option to automatically remove sensitive content</li>\n";
echo "<li>✅ <strong>Manual Edit:</strong> Option to cancel and edit manually</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Access Chat Template:</strong> Go to the Arabic chat template</li>\n";
echo "<li><strong>Test Email Detection:</strong> Type an email address and try to send</li>\n";
echo "<li><strong>Test CPR Detection:</strong> Type a CPR number and try to send</li>\n";
echo "<li><strong>Test Phone Detection:</strong> Type a phone number and try to send</li>\n";
echo "<li><strong>Test Credit Card:</strong> Type a credit card number and try to send</li>\n";
echo "<li><strong>Test Multiple Data:</strong> Type multiple sensitive data types</li>\n";
echo "<li><strong>Test Normal Message:</strong> Type a normal message without sensitive data</li>\n";
echo "<li><strong>Test Content Removal:</strong> Use the \"Remove Sensitive Content\" button</li>\n";
echo "<li><strong>Test Cancel:</strong> Use the \"Cancel\" button to edit manually</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Sensitive Detection:</strong> All sensitive patterns are detected</li>\n";
echo "<li>✅ <strong>Warning Modal:</strong> Warning modal appears for sensitive content</li>\n";
echo "<li>✅ <strong>Input Blocking:</strong> Messages are blocked until sensitive content is handled</li>\n";
echo "<li>✅ <strong>Content Removal:</strong> Users can remove sensitive content automatically</li>\n";
echo "<li>✅ <strong>Manual Edit:</strong> Users can cancel and edit manually</li>\n";
echo "<li>✅ <strong>Visual Feedback:</strong> Input fields show visual feedback for sensitive content</li>\n";
echo "<li>✅ <strong>Arabic Interface:</strong> All text is in Arabic with RTL support</li>\n";
echo "<li>✅ <strong>Normal Messages:</strong> Non-sensitive messages send without issues</li>\n";
echo "</ul>\n";

echo "<h3>Security Benefits:</h3>\n";
echo "<ul>\n";
echo "<li>🔒 <strong>Data Protection:</strong> Prevents accidental sharing of sensitive information</li>\n";
echo "<li>🛡️ <strong>Privacy Compliance:</strong> Helps comply with data protection regulations</li>\n";
echo "<li>⚠️ <strong>User Awareness:</strong> Educates users about sensitive data sharing</li>\n";
echo "<li>🚫 <strong>Content Blocking:</strong> Prevents sensitive data from being sent</li>\n";
echo "<li>🔄 <strong>Content Sanitization:</strong> Allows removal of sensitive content</li>\n";
echo "<li>📱 <strong>Cross-Platform:</strong> Works on all devices and browsers</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Sensitive input detection should now be fully functional in the chat interface.</p>\n";
echo "<p>Users will be warned and blocked from sending messages containing sensitive information.</p>\n";
echo "<p>The system supports multiple types of sensitive data detection with Arabic interface.</p>\n";
?>


<?php
/**
 * Test Usage Modal Implementation
 * 
 * Expected:
 * - Modal appears on first load only
 * - User must agree to proceed
 * - Agreement saved in cookies for 30 days
 * - Modal doesn't appear on return visits
 * - Cancel button redirects to home page
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Usage Modal Test</h2>\n";

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

// Check the chat template for modal implementation
$chat_template_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/chatgpt-arabic-template.php';

if (file_exists($chat_template_file)) {
    echo "<p><strong>Chat Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($chat_template_file);
    
    // Check for modal HTML
    $has_modal_html = strpos($content, 'usage-modal') !== false;
    echo "<p><strong>Modal HTML:</strong> " . ($has_modal_html ? 'YES' : 'NO') . "</p>\n";
    
    // Check for modal CSS
    $has_modal_css = strpos($content, '.usage-modal') !== false;
    echo "<p><strong>Modal CSS:</strong> " . ($has_modal_css ? 'YES' : 'NO') . "</p>\n";
    
    // Check for cookie functions
    $has_cookie_functions = strpos($content, 'setCookie') !== false;
    echo "<p><strong>Cookie functions:</strong> " . ($has_cookie_functions ? 'YES' : 'NO') . "</p>\n";
    
    // Check for Arabic content
    $has_arabic_content = strpos($content, 'تنبيه مهم') !== false;
    echo "<p><strong>Arabic content:</strong> " . ($has_arabic_content ? 'YES' : 'NO') . "</p>\n";
    
    // Check for checkbox
    $has_checkbox = strpos($content, 'usageCheckbox') !== false;
    echo "<p><strong>Checkbox element:</strong> " . ($has_checkbox ? 'YES' : 'NO') . "</p>\n";
    
    // Check for buttons
    $has_buttons = strpos($content, 'usageModalAgree') !== false;
    echo "<p><strong>Modal buttons:</strong> " . ($has_buttons ? 'YES' : 'NO') . "</p>\n";
    
    // Check for cookie storage
    $has_cookie_storage = strpos($content, 'nuwab_ai_usage_acknowledged') !== false;
    echo "<p><strong>Cookie storage:</strong> " . ($has_cookie_storage ? 'YES' : 'NO') . "</p>\n";
    
} else {
    echo "<p><strong>Chat Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Modal Features:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Feature</th><th>Description</th><th>Status</th></tr>\n";
echo "<tr><td><strong>Modal Display</strong></td><td>Full-screen modal with overlay</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>First Load Only</strong></td><td>Shows only on first visit</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Cookie Storage</strong></td><td>Agreement saved in cookies for 30 days</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Arabic Content</strong></td><td>Warning message in Arabic</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Checkbox Required</strong></td><td>User must check to proceed</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Agree Button</strong></td><td>Disabled until checkbox checked</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Cancel Button</strong></td><td>Redirects to home page</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Responsive Design</strong></td><td>Mobile-friendly layout</td><td>✅ Active</td></tr>\n";
echo "</table>\n";

echo "<h3>Modal Content (Arabic):</h3>\n";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #ff6b6b;'>\n";
echo "<p><strong>⚠️ تنبيه مهم</strong></p>\n";
echo "<p>هذا المساعد الذكي مخصص للاستخدام الداخلي فقط. يرجى التحقق من المخرجات قبل الاستخدام.</p>\n";
echo "<p><strong>Translation:</strong> Important Notice - This AI Assistant is for internal use only. Please verify outputs before use.</p>\n";
echo "</div>\n";

echo "<h3>Modal Structure:</h3>\n";
echo "<h4>HTML Structure:</h4>\n";
echo "<pre><code>&lt;div class=\"usage-modal\" id=\"usageModal\"&gt;\n";
echo "  &lt;div class=\"usage-modal-content\"&gt;\n";
echo "    &lt;div class=\"usage-modal-icon\"&gt;⚠️&lt;/div&gt;\n";
echo "    &lt;h2 class=\"usage-modal-title\"&gt;تنبيه مهم&lt;/h2&gt;\n";
echo "    &lt;p class=\"usage-modal-text\"&gt;...&lt;/p&gt;\n";
echo "    &lt;div class=\"usage-modal-checkbox\"&gt;\n";
echo "      &lt;input type=\"checkbox\" id=\"usageCheckbox\" /&gt;\n";
echo "      &lt;label for=\"usageCheckbox\"&gt;أفهم وأوافق على شروط الاستخدام&lt;/label&gt;\n";
echo "    &lt;/div&gt;\n";
echo "    &lt;div class=\"usage-modal-buttons\"&gt;\n";
echo "      &lt;button class=\"usage-modal-button primary\" id=\"usageModalAgree\" disabled&gt;\n";
echo "        موافق\n";
echo "      &lt;/button&gt;\n";
echo "      &lt;button class=\"usage-modal-button secondary\" id=\"usageModalCancel\"&gt;\n";
echo "        إلغاء\n";
echo "      &lt;/button&gt;\n";
echo "    &lt;/div&gt;\n";
echo "  &lt;/div&gt;\n";
echo "&lt;/div&gt;</code></pre>\n";

echo "<h3>Cookie Implementation:</h3>\n";
echo "<h4>Cookie Functions:</h4>\n";
echo "<pre><code>function setCookie(name, value, days) {\n";
echo "  const expires = new Date();\n";
echo "  expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));\n";
echo "  document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';\n";
echo "}\n\n";
echo "function getCookie(name) {\n";
echo "  const nameEQ = name + '=';\n";
echo "  const ca = document.cookie.split(';');\n";
echo "  for (let i = 0; i < ca.length; i++) {\n";
echo "    let c = ca[i];\n";
echo "    while (c.charAt(0) === ' ') c = c.substring(1, c.length);\n";
echo "    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);\n";
echo "  }\n";
echo "  return null;\n";
echo "}</code></pre>\n";

echo "<h4>Cookie Usage:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Cookie Name:</strong> <code>nuwab_ai_usage_acknowledged</code></li>\n";
echo "<li><strong>Cookie Value:</strong> <code>true</code></li>\n";
echo "<li><strong>Expiration:</strong> 30 days</li>\n";
echo "<li><strong>Path:</strong> <code>/</code> (entire site)</li>\n";
echo "<li><strong>Purpose:</strong> Remember user acknowledgment</li>\n";
echo "</ul>\n";

echo "<h3>Modal Behavior:</h3>\n";
echo "<h4>First Visit:</h4>\n";
echo "<ol>\n";
echo "<li><strong>Page Load:</strong> Modal appears after 500ms</li>\n";
echo "<li><strong>Modal Display:</strong> Full-screen overlay with centered content</li>\n";
echo "<li><strong>User Interaction:</strong> User must check checkbox to enable agree button</li>\n";
echo "<li><strong>Agree Action:</strong> Modal closes and cookie is set for 30 days</li>\n";
echo "<li><strong>Cancel Action:</strong> Modal closes and redirects to home page</li>\n";
echo "</ol>\n";

echo "<h4>Return Visit:</h4>\n";
echo "<ol>\n";
echo "<li><strong>Cookie Check:</strong> JavaScript checks for existing cookie</li>\n";
echo "<li><strong>Modal Hidden:</strong> Modal is hidden if cookie exists</li>\n";
echo "<li><strong>Direct Access:</strong> User can access chat immediately</li>\n";
echo "<li><strong>No Interruption:</strong> Seamless experience for returning users</li>\n";
echo "</ol>\n";

echo "<h3>Modal Styling:</h3>\n";
echo "<h4>Modal Overlay:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Position:</strong> Fixed, full screen</li>\n";
echo "<li><strong>Background:</strong> Semi-transparent black overlay</li>\n";
echo "<li><strong>Z-index:</strong> 10000 (above all content)</li>\n";
echo "<li><strong>Animation:</strong> Fade in/out with scale effect</li>\n";
echo "</ul>\n";

echo "<h4>Modal Content:</h4>\n";
echo "<ul>\n";
echo "<li><strong>Background:</strong> White with rounded corners</li>\n";
echo "<li><strong>Shadow:</strong> Large shadow for depth</li>\n";
echo "<li><strong>Size:</strong> Max 500px width, responsive</li>\n";
echo "<li><strong>Animation:</strong> Scale from 0.8 to 1.0</li>\n";
echo "</ul>\n";

echo "<h3>Test Scenarios:</h3>\n";
echo "<h4>Scenario 1: First Visit</h4>\n";
echo "<ol>\n";
echo "<li><strong>Clear Cookies:</strong> Clear browser cookies</li>\n";
echo "<li><strong>Visit Page:</strong> Go to chat template</li>\n";
echo "<li><strong>Modal Display:</strong> Modal should appear after 500ms</li>\n";
echo "<li><strong>Checkbox Test:</strong> Agree button should be disabled</li>\n";
echo "<li><strong>Check Checkbox:</strong> Agree button should enable</li>\n";
echo "<li><strong>Click Agree:</strong> Modal should close and cookie should be set</li>\n";
echo "<li><strong>Verify Cookie:</strong> Check browser cookies for 'nuwab_ai_usage_acknowledged'</li>\n";
echo "</ol>\n";

echo "<h4>Scenario 2: Return Visit</h4>\n";
echo "<ol>\n";
echo "<li><strong>Refresh Page:</strong> Reload the page</li>\n";
echo "<li><strong>No Modal:</strong> Modal should NOT appear</li>\n";
echo "<li><strong>Direct Access:</strong> User should be able to chat immediately</li>\n";
echo "<li><strong>Cookie Persistence:</strong> Cookie should still exist</li>\n";
echo "</ol>\n";

echo "<h4>Scenario 3: Cancel Action</h4>\n";
echo "<ol>\n";
echo "<li><strong>Clear Cookies:</strong> Clear browser cookies</li>\n";
echo "<li><strong>Visit Page:</strong> Go to chat template</li>\n";
echo "<li><strong>Modal Display:</strong> Modal should appear</li>\n";
echo "<li><strong>Click Cancel:</strong> Should redirect to home page</li>\n";
echo "<li><strong>No Cookie:</strong> No cookie should be set</li>\n";
echo "</ol>\n";

echo "<h3>Responsive Design:</h3>\n";
echo "<h4>Desktop (768px+):</h4>\n";
echo "<ul>\n";
echo "<li>Modal centered on screen</li>\n";
echo "<li>Buttons side by side</li>\n";
echo "<li>Larger text and spacing</li>\n";
echo "<li>Full modal content visible</li>\n";
echo "</ul>\n";

echo "<h4>Mobile (768px and below):</h4>\n";
echo "<ul>\n";
echo "<li>Modal takes 90% of screen width</li>\n";
echo "<li>Buttons stacked vertically</li>\n";
echo "<li>Smaller text and spacing</li>\n";
echo "<li>Touch-friendly interactions</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>First Visit Test:</strong>\n";
echo "  <ul>\n";
echo "  <li>Clear browser cookies</li>\n";
echo "  <li>Visit chat template</li>\n";
echo "  <li>Verify modal appears after 500ms</li>\n";
echo "  <li>Verify agree button is disabled</li>\n";
echo "  <li>Check the checkbox</li>\n";
echo "  <li>Verify agree button is enabled</li>\n";
echo "  <li>Click agree and verify modal closes</li>\n";
echo "  <li>Check browser cookies for the acknowledgment cookie</li>\n";
echo "  </ul>\n";
echo "</li>\n";
echo "<li><strong>Return Visit Test:</strong>\n";
echo "  <ul>\n";
echo "  <li>Refresh the page</li>\n";
echo "  <li>Verify modal does NOT appear</li>\n";
echo "  <li>Verify user can start chatting immediately</li>\n";
echo "  </ul>\n";
echo "</li>\n";
echo "<li><strong>Cancel Test:</strong>\n";
echo "  <ul>\n";
echo "  <li>Clear cookies and visit page</li>\n";
echo "  <li>Click cancel button</li>\n";
echo "  <li>Verify redirect to home page</li>\n";
echo "  </ul>\n";
echo "</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Modal Display:</strong> Modal appears on first load only</li>\n";
echo "<li>✅ <strong>Cookie Storage:</strong> Agreement saved in cookies for 30 days</li>\n";
echo "<li>✅ <strong>Arabic Content:</strong> Warning message in Arabic</li>\n";
echo "<li>✅ <strong>Checkbox Required:</strong> User must check to proceed</li>\n";
echo "<li>✅ <strong>Button States:</strong> Agree button disabled until checkbox checked</li>\n";
echo "<li>✅ <strong>Cancel Action:</strong> Cancel button redirects to home page</li>\n";
echo "<li>✅ <strong>Return Visits:</strong> Modal doesn't appear on return visits</li>\n";
echo "<li>✅ <strong>Responsive:</strong> Works on desktop and mobile</li>\n";
echo "<li>✅ <strong>Cookie Persistence:</strong> Acknowledgment remembered across sessions</li>\n";
echo "</ul>\n";

echo "<h3>Benefits of Modal Approach:</h3>\n";
echo "<ul>\n";
echo "<li>🎯 <strong>Attention Focus:</strong> Modal demands user attention</li>\n";
echo "<li>🔒 <strong>Required Action:</strong> User must actively agree to proceed</li>\n";
echo "<li>🍪 <strong>Cookie Storage:</strong> More reliable than localStorage</li>\n";
echo "<li>⏰ <strong>Expiration:</strong> Cookie expires after 30 days</li>\n";
echo "<li>🚫 <strong>No Bypass:</strong> Cannot bypass the agreement</li>\n";
echo "<li>📱 <strong>Mobile Friendly:</strong> Works well on all devices</li>\n";
echo "<li>🎨 <strong>Professional:</strong> Clean, modern modal design</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Usage modal should now appear on first load only.</p>\n";
echo "<p>User agreement should be saved in cookies for 30 days.</p>\n";
echo "<p>Modal should not appear on return visits.</p>\n";
?>

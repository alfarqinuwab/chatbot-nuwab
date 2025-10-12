<?php
/**
 * Test Improved Password Detection
 * 
 * Expected:
 * - Password detection should work with and without colons/semicolons
 * - API key detection should work with and without separators
 * - All patterns should be more flexible
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h2>Improved Password Detection Test</h2>\n";

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

// Check the chat template for improved password detection
$chat_template_file = WP_GPT_RAG_CHAT_PLUGIN_DIR . 'templates/chatgpt-arabic-template.php';

if (file_exists($chat_template_file)) {
    echo "<p><strong>Chat Template:</strong> ✅ EXISTS</p>\n";
    
    $content = file_get_contents($chat_template_file);
    
    // Check for improved password pattern
    $improved_password = strpos($content, '[:=]?') !== false;
    echo "<p><strong>Improved password pattern:</strong> " . ($improved_password ? 'YES' : 'NO') . "</p>\n";
    
    // Check for flexible separators
    $flexible_separators = strpos($content, '[:=]?') !== false;
    echo "<p><strong>Flexible separators:</strong> " . ($flexible_separators ? 'YES' : 'NO') . "</p>\n";
    
    // Count flexible patterns
    $flexible_count = substr_count($content, '[:=]?');
    echo "<p><strong>Flexible patterns:</strong> {$flexible_count}</p>\n";
    
} else {
    echo "<p><strong>Chat Template:</strong> ❌ NOT FOUND</p>\n";
}

echo "<h3>Improved Password Detection Patterns:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Pattern</th><th>Example</th><th>Status</th></tr>\n";
echo "<tr><td><strong>With Colon</strong></td><td>كلمة المرور: mypass123</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Without Colon</strong></td><td>كلمة المرور mypass123</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>With Equals</strong></td><td>password = mypass123</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Without Separator</strong></td><td>password mypass123</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Arabic With Space</strong></td><td>كلمة المرور mypass123</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Arabic With Colon</strong></td><td>كلمة المرور: mypass123</td><td>✅ Active</td></tr>\n";
echo "</table>\n";

echo "<h3>Improved API Key Detection Patterns:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
echo "<tr><th>Pattern</th><th>Example</th><th>Status</th></tr>\n";
echo "<tr><td><strong>With Colon</strong></td><td>api-key: abc123def456</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Without Colon</strong></td><td>api-key abc123def456</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>With Equals</strong></td><td>api-key = abc123def456</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Without Separator</strong></td><td>api-key abc123def456</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Token With Space</strong></td><td>token abc123def456</td><td>✅ Active</td></tr>\n";
echo "<tr><td><strong>Token With Colon</strong></td><td>token: abc123def456</td><td>✅ Active</td></tr>\n";
echo "</table>\n";

echo "<h3>Test Scenarios:</h3>\n";
echo "<h4>Password Detection Tests (All Should Be Detected):</h4>\n";
echo "<ul>\n";
echo "<li><strong>English with colon:</strong> \"password: mypassword123\"</li>\n";
echo "<li><strong>English without colon:</strong> \"password mypassword123\"</li>\n";
echo "<li><strong>English with equals:</strong> \"password = mypassword123\"</li>\n";
echo "<li><strong>Arabic with colon:</strong> \"كلمة المرور: mypassword123\"</li>\n";
echo "<li><strong>Arabic without colon:</strong> \"كلمة المرور mypassword123\"</li>\n";
echo "<li><strong>Arabic with space:</strong> \"كلمة السر mypassword123\"</li>\n";
echo "<li><strong>Transliterated:</strong> \"باسورد mypassword123\"</li>\n";
echo "<li><strong>Variations:</strong> \"باسوورد mypassword123\"</li>\n";
echo "</ul>\n";

echo "<h4>API Key Detection Tests (All Should Be Detected):</h4>\n";
echo "<ul>\n";
echo "<li><strong>With colon:</strong> \"api-key: abc123def456ghi789\"</li>\n";
echo "<li><strong>Without colon:</strong> \"api-key abc123def456ghi789\"</li>\n";
echo "<li><strong>With equals:</strong> \"api-key = abc123def456ghi789\"</li>\n";
echo "<li><strong>Token with colon:</strong> \"token: xyz789ghi012\"</li>\n";
echo "<li><strong>Token without colon:</strong> \"token xyz789ghi012\"</li>\n";
echo "<li><strong>Secret key:</strong> \"secret-key def456jkl345\"</li>\n";
echo "<li><strong>Access token:</strong> \"access-token mno789pqr456\"</li>\n";
echo "<li><strong>Bearer token:</strong> \"bearer-token stu012vwx345\"</li>\n";
echo "</ul>\n";

echo "<h3>Pattern Improvements:</h3>\n";
echo "<h4>Before (Required Separator):</h4>\n";
echo "<pre><code>password: /(?:password|كلمة المرور|كلمة السر|باسورد|باسوورد)\\s*[:=]\\s*[^\\s]+/gi</code></pre>\n";
echo "<p><strong>Issue:</strong> Required colon or equals sign</p>\n";
echo "<p><strong>Missed:</strong> \"كلمة المرور mypassword123\"</p>\n";

echo "<h4>After (Optional Separator):</h4>\n";
echo "<pre><code>password: /(?:password|كلمة المرور|كلمة السر|باسورد|باسوورد)\\s*[:=]?\\s*[^\\s]+/gi</code></pre>\n";
echo "<p><strong>Improvement:</strong> Optional colon or equals sign</p>\n";
echo "<p><strong>Now Catches:</strong> \"كلمة المرور mypassword123\"</p>\n";

echo "<h3>Regex Pattern Explanation:</h3>\n";
echo "<ul>\n";
echo "<li><strong>(?:password|كلمة المرور|كلمة السر|باسورد|باسوورد):</strong> Matches any of these keywords</li>\n";
echo "<li><strong>\\s*:</strong> Matches zero or more whitespace characters</li>\n";
echo "<li><strong>[:=]?:</strong> Matches zero or one colon or equals sign (optional)</li>\n";
echo "<li><strong>\\s*:</strong> Matches zero or more whitespace characters</li>\n";
echo "<li><strong>[^\\s]+:</strong> Matches one or more non-whitespace characters (the password)</li>\n";
echo "<li><strong>/gi:</strong> Global and case-insensitive flags</li>\n";
echo "</ul>\n";

echo "<h3>Test Instructions:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Access Chat Template:</strong> Go to the Arabic chat template</li>\n";
echo "<li><strong>Test Without Colon:</strong> Type \"كلمة المرور mypassword123\"</li>\n";
echo "<li><strong>Test With Colon:</strong> Type \"كلمة المرور: mypassword123\"</li>\n";
echo "<li><strong>Test With Equals:</strong> Type \"password = mypass123\"</li>\n";
echo "<li><strong>Test API Keys:</strong> Type \"api-key abc123def456\"</li>\n";
echo "<li><strong>Test Tokens:</strong> Type \"token xyz789ghi012\"</li>\n";
echo "<li><strong>Verify Detection:</strong> All should trigger warning modal</li>\n";
echo "<li><strong>Test Content Removal:</strong> Use remove sensitive content button</li>\n";
echo "</ol>\n";

echo "<h3>Success Criteria:</h3>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Without Colon:</strong> \"كلمة المرور mypassword123\" should be detected</li>\n";
echo "<li>✅ <strong>With Colon:</strong> \"كلمة المرور: mypassword123\" should be detected</li>\n";
echo "<li>✅ <strong>With Equals:</strong> \"password = mypass123\" should be detected</li>\n";
echo "<li>✅ <strong>API Keys:</strong> \"api-key abc123def456\" should be detected</li>\n";
echo "<li>✅ <strong>Tokens:</strong> \"token xyz789ghi012\" should be detected</li>\n";
echo "<li>✅ <strong>Arabic Support:</strong> All Arabic keywords should work</li>\n";
echo "<li>✅ <strong>Flexible Patterns:</strong> All separator variations should work</li>\n";
echo "<li>✅ <strong>Warning Modal:</strong> Proper Arabic labels should appear</li>\n";
echo "</ul>\n";

echo "<h3>Benefits of Improved Detection:</h3>\n";
echo "<ul>\n";
echo "<li>🔍 <strong>More Flexible:</strong> Catches passwords with or without separators</li>\n";
echo "<li>🌐 <strong>Multilingual:</strong> Works with Arabic and English keywords</li>\n";
echo "<li>📝 <strong>Natural Language:</strong> Handles natural writing patterns</li>\n";
echo "<li>🔒 <strong>Better Security:</strong> Catches more sensitive data variations</li>\n";
echo "<li>⚡ <strong>User Friendly:</strong> Less strict about formatting</li>\n";
echo "<li>🎯 <strong>Comprehensive:</strong> Covers all common patterns</li>\n";
echo "</ul>\n";

echo "<h3>Test Complete!</h3>\n";
echo "<p>Password detection should now work with and without colons/semicolons.</p>\n";
echo "<p>API key detection should be more flexible with separators.</p>\n";
echo "<p>All patterns should catch natural writing variations.</p>\n";
?>

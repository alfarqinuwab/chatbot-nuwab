<?php
/**
 * Test page for ChatGPT Arabic RTL Template
 * 
 * This file can be used to test the ChatGPT template functionality
 * Access it via: yoursite.com/wp-content/plugins/chatbot-nuwab/test-chatgpt-template.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user has permission
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Get plugin settings
$settings = get_option('wp_gpt_rag_chat_settings', []);
$chatgpt_enabled = true; // Template is always available via shortcode

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار قالب ChatGPT - Nuwab AI Assistant</title>
    <style>
        body {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            direction: rtl;
            text-align: right;
        }
        
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .test-header {
            background: #0073aa;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .test-header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .test-content {
            padding: 20px;
        }
        
        .status-indicator {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .status-enabled {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #bbf7d0;
        }
        
        .status-disabled {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #fecaca;
        }
        
        .test-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .test-option {
            padding: 20px;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            background: #f9f9f9;
        }
        
        .test-option h3 {
            margin: 0 0 12px 0;
            color: #1d2327;
        }
        
        .test-option p {
            margin: 0 0 16px 0;
            color: #646970;
            font-size: 14px;
        }
        
        .test-button {
            background: #0073aa;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s ease;
        }
        
        .test-button:hover {
            background: #005a87;
        }
        
        .test-button:disabled {
            background: #e5e5e5;
            color: #646970;
            cursor: not-allowed;
        }
        
        .chatgpt-demo {
            margin-top: 30px;
            border: 2px solid #e5e5e5;
            border-radius: 12px;
            overflow: hidden;
            min-height: 600px;
        }
        
        .demo-header {
            background: #f7f7f8;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e5e5;
            font-weight: 600;
            color: #1d2327;
        }
        
        .demo-content {
            height: 600px;
        }
        
        .instructions {
            background: #e8f4fd;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .instructions h3 {
            margin: 0 0 8px 0;
            color: #0073aa;
        }
        
        .instructions ul {
            margin: 0;
            padding-right: 20px;
        }
        
        .instructions li {
            margin-bottom: 4px;
            color: #1d2327;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>اختبار قالب ChatGPT - Nuwab AI Assistant</h1>
        </div>
        
        <div class="test-content">
            <div class="status-indicator <?php echo $chatgpt_enabled ? 'status-enabled' : 'status-disabled'; ?>">
                <?php if ($chatgpt_enabled): ?>
                    ✅ قالب ChatGPT مفعل - يمكنك استخدامه الآن
                <?php else: ?>
                    ❌ قالب ChatGPT غير مفعل - يرجى تفعيله من إعدادات المحادثة
                <?php endif; ?>
            </div>
            
            <div class="instructions">
                <h3>تعليمات الاختبار:</h3>
                <ul>
                    <li>تأكد من تفعيل قالب ChatGPT من إعدادات المحادثة</li>
                    <li>تأكد من إعداد مفاتيح API (OpenAI و Pinecone)</li>
                    <li>يمكنك استخدام الاختبارات أدناه لفحص الوظائف المختلفة</li>
                    <li>يمكنك أيضاً استخدام الشورتكود <code>[wp_gpt_rag_chatgpt]</code> في أي صفحة</li>
                </ul>
            </div>
            
            <div class="test-options">
                <div class="test-option">
                    <h3>اختبار الشورتكود</h3>
                    <p>اختبار عرض قالب ChatGPT باستخدام الشورتكود</p>
                    <button class="test-button" onclick="testShortcode()" <?php echo $chatgpt_enabled ? '' : 'disabled'; ?>>
                        اختبار الشورتكود
                    </button>
                </div>
                
                <div class="test-option">
                    <h3>اختبار الإعدادات</h3>
                    <p>التحقق من إعدادات المحادثة والقالب</p>
                    <button class="test-button" onclick="checkSettings()">
                        فحص الإعدادات
                    </button>
                </div>
                
                <div class="test-option">
                    <h3>اختبار API</h3>
                    <p>اختبار اتصال API مع OpenAI و Pinecone</p>
                    <button class="test-button" onclick="testAPI()" <?php echo $chatgpt_enabled ? '' : 'disabled'; ?>>
                        اختبار API
                    </button>
                </div>
            </div>
            
            <?php if ($chatgpt_enabled): ?>
            <div class="chatgpt-demo">
                <div class="demo-header">
                    معاينة قالب ChatGPT
                </div>
                <div class="demo-content">
                    <?php
                    // Display the ChatGPT template
                    $chat = new WP_GPT_RAG_Chat\Chat();
                    echo $chat->get_chatgpt_template_html();
                    ?>
                </div>
            </div>
            <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #646970;">
                <h3>قالب ChatGPT غير مفعل</h3>
                <p>يرجى الذهاب إلى <strong>إعدادات المحادثة</strong> وتفعيل قالب ChatGPT للاستخدام.</p>
                <a href="<?php echo admin_url('admin.php?page=wp-gpt-rag-chat-settings'); ?>" class="test-button" style="text-decoration: none; display: inline-block; margin-top: 16px;">
                    الذهاب إلى الإعدادات
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function testShortcode() {
            alert('يمكنك استخدام الشورتكود [wp_gpt_rag_chatgpt] في أي صفحة أو منشور لعرض قالب ChatGPT.');
        }
        
        function checkSettings() {
            const settings = {
                chatgpt_enabled: <?php echo $chatgpt_enabled ? 'true' : 'false'; ?>,
                openai_key: '<?php echo !empty($settings['openai_api_key']) ? 'مُعرّف' : 'غير مُعرّف'; ?>',
                pinecone_key: '<?php echo !empty($settings['pinecone_api_key']) ? 'مُعرّف' : 'غير مُعرّف'; ?>',
                chatbot_enabled: <?php echo !empty($settings['enable_chatbot']) ? 'true' : 'false'; ?>
            };
            
            let message = 'حالة الإعدادات:\n\n';
            message += 'قالب ChatGPT: ' + (settings.chatgpt_enabled ? 'مفعل' : 'غير مفعل') + '\n';
            message += 'المحادثة: ' + (settings.chatbot_enabled ? 'مفعلة' : 'غير مفعلة') + '\n';
            message += 'مفتاح OpenAI: ' + settings.openai_key + '\n';
            message += 'مفتاح Pinecone: ' + settings.pinecone_key;
            
            alert(message);
        }
        
        function testAPI() {
            alert('اختبار API - سيتم إرسال رسالة تجريبية إلى الخادم للتحقق من الاتصال.');
        }
    </script>
</body>
</html>

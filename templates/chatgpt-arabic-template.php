<?php
/**
 * ChatGPT-style Arabic RTL Template
 * 
 * This template provides a ChatGPT-like interface with Arabic RTL support
 * for the Nuwab AI Assistant plugin.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$settings = WP_GPT_RAG_Chat\Settings::get_settings();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('مساعدك الذكي', 'wp-gpt-rag-chat'); ?></title>
    
    <!-- Google Fonts - Arabic Support -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            color: #1d2327;
            direction: rtl;
            text-align: right;
            line-height: 1.6;
            overflow: hidden;
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* Main Container */
        .chatgpt-container {
            display: flex;
            height: 100vh;
            max-width: 100vw;
        }

        /* Sidebar */
        .chatgpt-sidebar {
            width: 260px;
            background: #f7f7f8;
            border-left: 1px solid #e5e5e5;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .chatgpt-sidebar.collapsed {
            transform: translateX(100%);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e5e5e5;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .sidebar-logo img {
            width: 32px;
            height: 32px;
            border-radius: 6px;
        }

        .sidebar-logo h1 {
            font-size: 18px;
            font-weight: 600;
            color: #1d2327;
        }

        .new-chat-btn {
            width: 100%;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            color: #1d2327;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .new-chat-btn:hover {
            background: #f0f0f0;
            border-color: #d0d0d0;
        }

        .sidebar-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .sidebar-action {
            flex: 1;
            padding: 8px;
            background: transparent;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            color: #646970;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-action:hover {
            background: #f0f0f0;
            color: #1d2327;
        }

        .chat-history {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .chat-history h3 {
            font-size: 14px;
            font-weight: 600;
            color: #646970;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chat-item {
            padding: 12px 16px;
            margin-bottom: 8px;
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            color: #1d2327;
        }

        .chat-item:hover {
            background: #f0f0f0;
            border-color: #d0d0d0;
        }

        .chat-item.active {
            background: #e8f4fd;
            border-color: #d1a85f;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #e5e5e5;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-profile:hover {
            background: #f0f0f0;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
            color: #1d2327;
        }

        .user-status {
            font-size: 12px;
            color: #646970;
        }

        /* Main Chat Area */
        .chatgpt-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .chatgpt-main::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/images/left-bg.png'); ?>');
            background-repeat: no-repeat;
            background-position: -150px -92px;
            background-size: 60% auto;
            opacity: 0.1;
            filter: grayscale(100%);
            z-index: 0;
            pointer-events: none;
        }

        .chat-header {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        .chat-logo {
            display: flex;
            align-items: center;
        }

        .chat-logo-img {
            width: 183px;
            height: 60px;
            object-fit: contain;
        }

        .chat-logo-default {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .chat-logo-default-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-title {
            font-size: 20px;
            font-weight: 600;
            color: #1d2327;
        }

        .chat-actions {
            display: flex;
            gap: 12px;
        }

        .chat-action {
            padding: 8px;
            background: transparent;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            color: #646970;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-action:hover {
            background: #f0f0f0;
            color: #1d2327;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0;
            background: #faf9f7;
            width: 100%;
            margin: 0;
            box-sizing: border-box;
        }

        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f5f3f0;
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #d1a85f;
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: #c89a4f;
        }

        .message {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            font-family: 'Tajawal', sans-serif;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .message:last-child {
            margin-bottom: 0;
        }

        .message.user {
            align-items: flex-start;
        }

        .message.assistant {
            align-items: flex-end;
        }

        .message-avatar {
            display: none;
        }

        .message.user .message-avatar {
            background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
            color: white;
        }

        .message.assistant .message-avatar {
            background: #f0f0f0;
            color: #646970;
        }

        .message-content {
            /* Content styling is handled by .message-text */
        }

        .message.user .message-content {
            text-align: right;
        }

        .message.assistant .message-content {
            text-align: left;
        }

        .message.assistant .message-text[dir="rtl"] {
            text-align: right;
        }

        .message-text {
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
            font-family: 'Tajawal', sans-serif;
            font-size: 15px;
            line-height: 1.6;
            display: inline-block;
        }

        .message.user .message-text {
            background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
            color: #fff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 8px rgba(209, 168, 95, 0.25);
        }

        .message.assistant {
            margin-bottom: 20px;
        }

        .message.assistant .message-text {
            background: transparent;
            color: #1d2327;
            border: none;
            box-shadow: none;
            padding: 16px 0;
            font-size: 16px;
        }

        /* Source Link Styling - Same as original chat widget */
        .message-text .source-link {
            display: block;
            margin-top: 10px;
            padding: 8px 14px;
            background: #f0ece5;
            color: #333 !important;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            width: fit-content;
        }

        .message-text .source-link:hover {
            background: #e5e0d8;
            transform: translateY(-1px);
        }

        /* Source Links List Styling */
        .message-text .source-links-list {
            margin: 15px 0;
            padding: 0;
            list-style: none;
            background: #f8f6f2;
            border-radius: 8px;
            border: 1px solid #e5e0d8;
            overflow: hidden;
        }

        .message-text .source-links-list li {
            margin: 0;
            padding: 0;
            border-bottom: 1px solid #e5e0d8;
        }

        .message-text .source-links-list li:last-child {
            border-bottom: none;
        }

        .message-text .source-links-list .source-link {
            display: block;
            margin: 0;
            padding: 12px 16px;
            background: transparent;
            color: #333 !important;
            text-decoration: none;
            border-radius: 0;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .message-text .source-links-list .source-link:hover {
            background: #e5e0d8;
            transform: none;
            color: #d1a85f !important;
        }

        .message-text .source-links-list .source-link:before {
            content: "🔗";
            margin-left: 8px;
            font-size: 12px;
        }

        /* Content Separator Styling */
        .message-text .content-separator {
            height: 1px;
            background: linear-gradient(to right, transparent, #d1a85f, transparent);
            margin: 20px 0;
            border: none;
        }

        .message-time {
            display: none;
        }

        /* Rating Buttons - Exact copy from original chat widget */
        .message-rating {
            display: flex;
            gap: 8px;
            padding: 0 0 0 16px;
            justify-content: flex-start;
        }

        .message.user .message-rating {
            justify-content: flex-end;
        }

        .message.assistant .message-rating {
            justify-content: flex-start;
        }

        .message-rate-btn {
            background: transparent;
            border: 1px solid #e1e5e9;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            padding: 0;
            color: #7a7a7a;
        }

        .message-rate-btn i {
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .message-rate-btn:hover {
            background: #f5f5f5;
            border-color: #d1a85f;
            transform: scale(1.1);
            color: #d1a85f;
        }

        .message-rate-btn.rating-selected {
            background: #d1a85f;
            border-color: #d1a85f;
            transform: scale(1.15);
            color: #fff;
        }

        .message-rate-btn.rating-selected i {
            /* Change from outline to solid when selected */
            font-weight: 900;
        }

        .message-rate-btn.rating-saved {
            animation: rating-pulse 0.5s ease;
        }

        @keyframes rating-pulse {
            0%, 100% {
                transform: scale(1.15);
            }
            50% {
                transform: scale(1.3);
            }
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 16px 20px;
            background: #f7f7f8;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            max-width: 80px;
            margin-right: auto;
        }

        .typing-dot {
            width: 10px;
            height: 10px;
            background: #d1a85f;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0s;
        }

        @keyframes typing {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Input Area */
        .chat-input-container {
            padding: 12px 24px 24px 24px;
            background: transparent;
            border: none;
            display: none; /* Initially hidden, shown after first message */
            position: relative;
            z-index: 1;
        }

        .chat-input-wrapper {
            position: relative;
            width: 800px;
            margin: 0 auto 35px auto;
            box-sizing: border-box;
        }

        .chat-input {
            width: 100%;
            padding: 16px 20px 16px 60px;
            border: 2px solid #e5e5e5;
            border-radius: 24px;
            font-size: 15px;
            font-family: 'Tajawal', sans-serif;
            background: #ffffff;
            color: #1d2327;
            resize: none;
            overflow: hidden;
            min-height: 56px;
            max-height: 200px;
            transition: all 0.2s ease;
            direction: rtl;
            text-align: right;
        }

        .chat-input.extended {
            border-radius: 12px;
        }

        .chat-input:focus {
            outline: none;
            border-color: #d1a85f;
            box-shadow: 0 0 0 3px rgba(209, 168, 95, 0.1);
        }

        .chat-input::placeholder {
            color: #646970;
        }

        .chat-input-actions {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 8px;
        }

        .input-action {
            width: 32px;
            height: 32px;
            background: transparent;
            border: none;
            border-radius: 50%;
            color: #646970;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-action:hover {
            background: #f0f0f0;
            color: #1d2327;
        }

        .send-button {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-button:hover {
            background: linear-gradient(135deg, #c89a4f 0%, #b88a3f 100%);
        }

        .send-button:disabled {
            background: #e5e5e5;
            color: #646970;
            cursor: not-allowed;
        }

        /* Stop Button Styles */
        .send-button.stop {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .send-button.stop:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
        }

        .send-button.stop i {
            transform: rotate(0deg);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .chatgpt-sidebar {
                position: fixed;
                top: 0;
                right: 0;
                height: 100vh;
                width: 280px;
                transform: translateX(100%);
                z-index: 1000;
            }

            .chatgpt-sidebar.open {
                transform: translateX(0);
            }

            .chatgpt-main {
                width: 100%;
            }

            .chat-header {
                padding: 16px 20px;
            }

            .chat-messages {
                padding: 16px 20px;
            }

            .chat-input-container {
                padding: 16px 20px;
            }

            .message-text {
                font-size: 14px;
                padding: 12px 16px;
            }
        }

        /* Welcome Screen */
        .welcome-screen {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 20px 40px;
            transform: translateY(-60px);
            width: 800px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .welcome-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 24px;
        }

        .welcome-avatar-img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #d1a85f;
        }

        /* Footer */
        .chat-footer {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 12px;
            color: #999999;
            font-weight: normal;
            font-family: 'Tajawal', sans-serif;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 12px;
        }

        .welcome-subtitle {
            font-size: 16px;
            color: #646970;
            margin-bottom: 32px;
            max-width: 500px;
            font-family: 'Tajawal', sans-serif;
        }

        /* Centered Input for Welcome State */
        .welcome-input-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .welcome-input-wrapper {
            position: relative;
            width: 100%;
        }

        .welcome-input {
            width: 100%;
            padding: 16px 20px 16px 60px;
            border: 2px solid #e5e5e5;
            border-radius: 55px;
            font-size: 16px;
            font-family: 'Tajawal', sans-serif;
            background: #ffffff;
            color: #1d2327;
            resize: none;
            overflow: hidden;
            min-height: 56px;
            max-height: 200px;
            transition: all 0.2s ease;
            direction: rtl;
            text-align: right;
        }

        .welcome-input.extended {
            border-radius: 12px;
        }

        .welcome-input:focus {
            outline: none;
            border-color: #d1a85f;
            box-shadow: 0 0 0 3px rgba(209, 168, 95, 0.1);
        }

        .welcome-input::placeholder {
            color: #646970;
            text-align: right;
        }

        .welcome-send-button {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #d1a85f 0%, #c89a4f 100%);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-send-button:hover {
            background: linear-gradient(135deg, #c89a4f 0%, #b88a3f 100%);
        }

        .welcome-send-button:disabled {
            background: #e5e5e5;
            color: #646970;
            cursor: not-allowed;
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Error States */
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin: 16px 0;
        }

        /* Success States */
        .success-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin: 16px 0;
        }
    </style>
</head>
<body>
    <div class="chatgpt-container">
        <!-- Sidebar -->
        <div class="chatgpt-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar_small.png'); ?>" alt="AI Assistant">
                    <h1><?php esc_html_e('مساعدك الذكي', 'wp-gpt-rag-chat'); ?></h1>
                </div>
                <button class="new-chat-btn" id="newChatBtn">
                    <i class="fas fa-plus"></i>
                    <?php esc_html_e('محادثة جديدة', 'wp-gpt-rag-chat'); ?>
                </button>
                <div class="sidebar-actions">
                    <button class="sidebar-action" id="editBtn" title="<?php esc_attr_e('تحرير', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="sidebar-action" id="searchBtn" title="<?php esc_attr_e('بحث', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="sidebar-action" id="historyBtn" title="<?php esc_attr_e('السجل', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-history"></i>
                    </button>
                </div>
            </div>
            
            <div class="chat-history">
                <h3><?php esc_html_e('المحادثات الأخيرة', 'wp-gpt-rag-chat'); ?></h3>
                <div id="chatHistoryList">
                    <!-- Chat history will be populated here -->
                </div>
            </div>
            
            <div class="sidebar-footer">
                <div class="user-profile" id="userProfile">
                    <div class="user-avatar"><?php echo esc_html(substr(wp_get_current_user()->display_name ?? 'مستخدم', 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo esc_html(wp_get_current_user()->display_name ?? 'مستخدم'); ?></div>
                        <div class="user-status"><?php esc_html_e('متصل', 'wp-gpt-rag-chat'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chatgpt-main">
            <div class="chat-header">
                <div class="chat-logo">
                    <?php 
                    $settings = \WP_GPT_RAG_Chat\Settings::get_settings();
                    if (!empty($settings['chat_logo'])): ?>
                        <img src="<?php echo esc_url($settings['chat_logo']); ?>" alt="<?php esc_attr_e('Chat Logo', 'wp-gpt-rag-chat'); ?>" class="chat-logo-img" />
                    <?php else: ?>
                        <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar_small.png'); ?>" alt="<?php esc_attr_e('Chat Avatar', 'wp-gpt-rag-chat'); ?>" class="chat-logo-default-img" />
                    <?php endif; ?>
                </div>
                <div class="chat-actions">
                    <button class="chat-action" id="newChatHeaderBtn" title="<?php esc_attr_e('محادثة جديدة', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="chat-action" id="shareBtn" title="<?php esc_attr_e('مشاركة', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-share"></i>
                    </button>
                    <button class="chat-action" id="menuBtn" title="<?php esc_attr_e('القائمة', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <!-- Welcome Screen -->
                <div class="welcome-screen" id="welcomeScreen">
                    <div class="welcome-icon">
                        <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar_small.png'); ?>" alt="<?php esc_attr_e('Chat Avatar', 'wp-gpt-rag-chat'); ?>" class="welcome-avatar-img" />
                    </div>
                    <h1 class="welcome-title"><?php esc_html_e('مرحباً! أنا مساعدك الذكي', 'wp-gpt-rag-chat'); ?></h1>
                    <p class="welcome-subtitle"><?php esc_html_e('يمكنني مساعدتك في الإجابة على أسئلتك والبحث في محتوى الموقع.', 'wp-gpt-rag-chat'); ?></p>
                    
                    <div class="welcome-input-container">
                        <div class="welcome-input-wrapper">
                            <textarea 
                                class="welcome-input" 
                                id="welcomeInput" 
                                placeholder="<?php esc_attr_e('اكتب رسالتك هنا...', 'wp-gpt-rag-chat'); ?>"
                                rows="1"
                            ></textarea>
                            <button class="welcome-send-button" id="welcomeSendBtn" title="<?php esc_attr_e('إرسال', 'wp-gpt-rag-chat'); ?>">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="chat-footer">
                    جميع الحقوق محفوظة © مجلس النواب 2025
                </div>
            </div>

            <div class="chat-input-container">
                <div class="chat-input-wrapper">
                    <textarea 
                        class="chat-input" 
                        id="chatInput" 
                        placeholder="<?php esc_attr_e('اكتب رسالتك هنا...', 'wp-gpt-rag-chat'); ?>"
                        rows="1"
                    ></textarea>
                    <div class="chat-input-actions" id="inputActions">
                        <button class="input-action" id="micBtn" title="<?php esc_attr_e('تسجيل صوتي', 'wp-gpt-rag-chat'); ?>">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                    <button class="send-button" id="sendBtn" title="<?php esc_attr_e('إرسال', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatInput = document.getElementById('chatInput');
            const sendBtn = document.getElementById('sendBtn');
            const chatMessages = document.getElementById('chatMessages');
            const welcomeScreen = document.getElementById('welcomeScreen');
            const newChatBtn = document.getElementById('newChatBtn');
            const welcomeInput = document.getElementById('welcomeInput');
            const welcomeSendBtn = document.getElementById('welcomeSendBtn');
            
            // Abort controller for cancelling requests
            let currentAbortController = null;
            
            // Load chat history from session storage
            loadChatHistory();
            
            // Auto-resize textarea
            chatInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 200) + 'px';
                
                // Add extended class when height increases beyond single line
                if (this.scrollHeight > 80) {
                    this.classList.add('extended');
                } else {
                    this.classList.remove('extended');
                }
            });

            // Auto-resize welcome textarea
            welcomeInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 200) + 'px';
                
                // Add extended class when height increases beyond single line
                if (this.scrollHeight > 80) {
                    this.classList.add('extended');
                } else {
                    this.classList.remove('extended');
                }
            });

            // Send message function
            function sendMessage(message) {
                if (!message.trim()) return;

                // Hide welcome screen and show chat interface when chat starts
                welcomeScreen.style.display = 'none';
                welcomeScreen.style.visibility = 'hidden';
                welcomeScreen.style.opacity = '0';
                
                // Show the bottom input container
                document.querySelector('.chat-input-container').style.display = 'block';

                // Add user message
                addMessage('user', message);
                
                // Show typing indicator
                showTypingIndicator();

                // Change send button to stop button
                changeToStopButton();

                // Send to backend
                sendToBackend(message);
            }

            // Add message to chat
            function addMessage(role, content, logId = null) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${role}`;
                
                const avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.textContent = role === 'user' ? 'أنت' : 'AI';
                
                const messageContent = document.createElement('div');
                messageContent.className = 'message-content';
                
                const messageText = document.createElement('div');
                messageText.className = 'message-text';
                messageText.innerHTML = formatMessage(content);
                
                // Detect Arabic text for AI responses
                if (role === 'assistant') {
                    const isArabic = /[\u0600-\u06FF]/.test(content);
                    if (isArabic) {
                        messageText.setAttribute('dir', 'rtl');
                    }
                }
                
                const messageTime = document.createElement('div');
                messageTime.className = 'message-time';
                messageTime.textContent = new Date().toLocaleTimeString('ar-SA');
                
                // Add message text first
                messageContent.appendChild(messageText);
                
                // Add rating buttons AFTER message text (below the AI response)
                if (role === 'assistant' && logId && logId !== 'temp') {
                    const ratingDiv = document.createElement('div');
                    ratingDiv.className = 'message-rating';
                    ratingDiv.innerHTML = `
                        <button class="message-rate-btn" data-log-id="${logId}" data-rating="1" aria-label="Thumbs Up" title="مفيد">
                            <i class="fa-regular fa-thumbs-up"></i>
                        </button>
                        <button class="message-rate-btn" data-log-id="${logId}" data-rating="-1" aria-label="Thumbs Down" title="غير مفيد">
                            <i class="fa-regular fa-thumbs-down"></i>
                        </button>
                    `;
                    messageContent.appendChild(ratingDiv);
                }
                
                // Add timestamp last
                messageContent.appendChild(messageTime);
                
                messageDiv.appendChild(avatar);
                messageDiv.appendChild(messageContent);
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Save chat history after adding message
                saveChatHistory();
            }

            // Format message content (convert markdown links to HTML)
            function formatMessage(text) {
                // First escape HTML to prevent XSS
                let escaped = escapeHtml(text);
                
                // Convert separator lines to styled dividers BEFORE processing links
                escaped = escaped.replace(/━━━━━━━━━━━━━━━━/g, '<div class="content-separator"></div>');
                
                // Find and group consecutive links to create a list
                // Look for patterns like: 🔗 [link1](url1) 🔗 [link2](url2) etc.
                const linkPattern = /(🔗\s*\[([^\]]+)\]\(([^)]+)\)(?:\s*🔗\s*\[([^\]]+)\]\(([^)]+)\))*)/g;
                
                escaped = escaped.replace(linkPattern, function(match) {
                    // Extract all links from the match
                    const links = [];
                    const linkRegex = /🔗\s*\[([^\]]+)\]\(([^)]+)\)/g;
                    let linkMatch;
                    
                    while ((linkMatch = linkRegex.exec(match)) !== null) {
                        links.push({
                            text: linkMatch[1],
                            url: linkMatch[2]
                        });
                    }
                    
                    // If we have multiple links, create a list
                    if (links.length > 1) {
                        let listHtml = '<ul class="source-links-list">';
                        links.forEach(function(link) {
                            listHtml += '<li><a href="' + link.url + '" target="_blank" rel="noopener noreferrer" class="source-link">' + link.text + '</a></li>';
                        });
                        listHtml += '</ul>';
                        return listHtml;
                    } else if (links.length === 1) {
                        // Single link - just return the link without list
                        return '<a href="' + links[0].url + '" target="_blank" rel="noopener noreferrer" class="source-link">' + links[0].text + '</a>';
                    }
                    
                    return match; // Fallback
                });
                
                // Convert line breaks to <br> tags
                escaped = escaped.replace(/\n/g, '<br>');
                
                // Remove excessive <br> tags (more than 2 consecutive)
                escaped = escaped.replace(/(<br>\s*){3,}/g, '<br><br>');
                
                // Remove <br> tags immediately before and after content separators
                escaped = escaped.replace(/<br>\s*<div class="content-separator"><\/div>\s*<br>/g, '<div class="content-separator"></div>');
                
                // Remove <br> tags immediately before and after source links lists
                escaped = escaped.replace(/<br>\s*<ul class="source-links-list">/g, '<ul class="source-links-list">');
                escaped = escaped.replace(/<\/ul>\s*<br>/g, '</ul>');
                
                // Remove <br> tags immediately before and after single source links
                escaped = escaped.replace(/<br>\s*(<a[^>]*class="source-link")/g, '$1');
                escaped = escaped.replace(/(<a[^>]*class="source-link"[^>]*><\/a>)\s*<br>/g, '$1');
                
                // Remove <br> tags at the very end
                escaped = escaped.replace(/(<br>\s*)+$/g, '');
                
                return escaped;
            }

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                
                return text.replace(/[&<>"']/g, function(m) {
                    return map[m];
                });
            }

            // Show typing indicator
            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'message assistant';
                typingDiv.id = 'typingIndicator';
                
                const avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.textContent = 'AI';
                
                const messageContent = document.createElement('div');
                messageContent.className = 'message-content';
                
                const typingIndicator = document.createElement('div');
                typingIndicator.className = 'typing-indicator';
                typingIndicator.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
                
                messageContent.appendChild(typingIndicator);
                typingDiv.appendChild(avatar);
                typingDiv.appendChild(messageContent);
                
                chatMessages.appendChild(typingDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Hide typing indicator
            function hideTypingIndicator() {
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            // Send to backend
            // Change send button to stop button
            function changeToStopButton() {
                const sendBtn = document.getElementById('sendBtn');
                const sendIcon = sendBtn.querySelector('i');
                
                sendBtn.classList.add('stop');
                sendBtn.title = 'إيقاف';
                sendIcon.className = 'fas fa-stop';
                
                // Remove existing event listeners and add stop functionality
                sendBtn.replaceWith(sendBtn.cloneNode(true));
                const newSendBtn = document.getElementById('sendBtn');
                newSendBtn.addEventListener('click', stopGeneration);
            }

            // Change stop button back to send button
            function changeToSendButton() {
                const sendBtn = document.getElementById('sendBtn');
                const sendIcon = sendBtn.querySelector('i');
                
                sendBtn.classList.remove('stop');
                sendBtn.title = 'إرسال';
                sendIcon.className = 'fas fa-paper-plane';
                
                // Remove existing event listeners and add send functionality
                sendBtn.replaceWith(sendBtn.cloneNode(true));
                const newSendBtn = document.getElementById('sendBtn');
                newSendBtn.addEventListener('click', function() {
                    const message = chatInput.value.trim();
                    if (message) {
                        sendMessage(message);
                        chatInput.value = '';
                        chatInput.style.height = 'auto';
                        chatInput.classList.remove('extended');
                    }
                });
            }

            // Stop generation function
            function stopGeneration() {
                // Abort the current request if it exists
                if (currentAbortController) {
                    currentAbortController.abort();
                    currentAbortController = null;
                }
                
                // Hide typing indicator
                hideTypingIndicator();
                
                // Change back to send button
                changeToSendButton();
                
                // Add a message indicating the generation was stopped
                addMessage('assistant', 'تم إيقاف التوليد.');
            }

            function sendToBackend(message) {
                // Create new abort controller for this request
                currentAbortController = new AbortController();
                
                const formData = new FormData();
                formData.append('action', 'wp_gpt_rag_chat_query');
                formData.append('query', message);
                formData.append('nonce', '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData,
                    signal: currentAbortController.signal
                })
                .then(response => response.json())
                .then(data => {
                    hideTypingIndicator();
                    
                    // Clear abort controller
                    currentAbortController = null;
                    
                    // Change back to send button
                    changeToSendButton();
                    
                    if (data.success) {
                        addMessage('assistant', data.data.response, data.data.log_id);
                    } else {
                        addMessage('assistant', 'عذراً، حدث خطأ في المعالجة. يرجى المحاولة مرة أخرى.');
                    }
                })
                .catch(error => {
                    hideTypingIndicator();
                    
                    // Clear abort controller
                    currentAbortController = null;
                    
                    // Change back to send button
                    changeToSendButton();
                    
                    // Check if the request was aborted
                    if (error.name === 'AbortError') {
                        // Request was cancelled, don't show error message
                        console.log('Request was cancelled by user');
                        return;
                    }
                    
                    addMessage('assistant', 'عذراً، حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.');
                    console.error('Error:', error);
                });
            }

            // Event listeners
            sendBtn.addEventListener('click', function() {
                const message = chatInput.value.trim();
                if (message) {
                    sendMessage(message);
                    chatInput.value = '';
                    chatInput.style.height = 'auto';
                    chatInput.classList.remove('extended');
                }
            });

            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const message = this.value.trim();
                    if (message) {
                        sendMessage(message);
                        this.value = '';
                        this.style.height = 'auto';
                        this.classList.remove('extended');
                    }
                }
            });

            // Welcome input event listeners
            welcomeSendBtn.addEventListener('click', function() {
                const message = welcomeInput.value.trim();
                if (message) {
                    // Hide welcome screen when chat starts
                    welcomeScreen.style.display = 'none';
                    welcomeScreen.style.visibility = 'hidden';
                    welcomeScreen.style.opacity = '0';
                    
                    // Show chat input container
                    document.querySelector('.chat-input-container').style.display = 'block';
                    
                    sendMessage(message);
                    welcomeInput.value = '';
                    welcomeInput.style.height = 'auto';
                    welcomeInput.classList.remove('extended');
                }
            });

            welcomeInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const message = this.value.trim();
                    if (message) {
                        // Hide welcome screen when chat starts
                        welcomeScreen.style.display = 'none';
                        welcomeScreen.style.visibility = 'hidden';
                        welcomeScreen.style.opacity = '0';
                        
                        // Show chat input container
                        document.querySelector('.chat-input-container').style.display = 'block';
                        
                        sendMessage(message);
                        this.value = '';
                        this.style.height = 'auto';
                        this.classList.remove('extended');
                    }
                }
            });

            newChatBtn.addEventListener('click', function() {
                // Clear only the chat messages, not the welcome screen
                const existingMessages = chatMessages.querySelectorAll('.message');
                existingMessages.forEach(msg => msg.remove());
                
                // Clear sidebar chat history list
                const chatHistoryList = document.getElementById('chatHistoryList');
                if (chatHistoryList) {
                    chatHistoryList.innerHTML = '';
                }
                
                // Force show welcome screen - change from display:none to display:flex
                welcomeScreen.style.display = 'flex';
                welcomeScreen.style.visibility = 'visible';
                welcomeScreen.style.opacity = '1';
                
                // Remove any inline styles that might be hiding it
                welcomeScreen.removeAttribute('style');
                welcomeScreen.style.display = 'flex';
                
                // Hide chat input container
                const chatInputContainer = document.querySelector('.chat-input-container');
                if (chatInputContainer) {
                    chatInputContainer.style.display = 'none';
                }
                
                // Clear input fields
                chatInput.value = '';
                chatInput.style.height = 'auto';
                welcomeInput.value = '';
                welcomeInput.style.height = 'auto';
                
                // Clear chat history from session storage
                clearChatHistory();
                
                // Scroll to top
                chatMessages.scrollTop = 0;
                
                // Force a reflow to ensure display changes take effect
                welcomeScreen.offsetHeight;
            });

            // New chat button in header
            const newChatHeaderBtn = document.getElementById('newChatHeaderBtn');
            if (newChatHeaderBtn) {
                newChatHeaderBtn.addEventListener('click', function() {
                    console.log('New chat button clicked'); // Debug log
                    
                    // Clear only the chat messages, not the welcome screen
                    const existingMessages = chatMessages.querySelectorAll('.message');
                    existingMessages.forEach(msg => msg.remove());
                    
                    // Clear sidebar chat history list
                    const chatHistoryList = document.getElementById('chatHistoryList');
                    if (chatHistoryList) {
                        chatHistoryList.innerHTML = '';
                    }
                    
                    // Force show welcome screen - change from display:none to display:flex
                    welcomeScreen.style.display = 'flex';
                    welcomeScreen.style.visibility = 'visible';
                    welcomeScreen.style.opacity = '1';
                    welcomeScreen.style.position = 'relative';
                    welcomeScreen.style.zIndex = '1';
                    
                    // Remove any inline styles that might be hiding it
                    welcomeScreen.removeAttribute('style');
                    welcomeScreen.style.display = 'flex';
                    
                    // Hide chat input container
                    const chatInputContainer = document.querySelector('.chat-input-container');
                    if (chatInputContainer) {
                        chatInputContainer.style.display = 'none';
                    }
                    
                    // Clear input fields
                    chatInput.value = '';
                    chatInput.style.height = 'auto';
                    welcomeInput.value = '';
                    welcomeInput.style.height = 'auto';
                    
                    // Clear chat history from session storage
                    clearChatHistory();
                    
                    // Scroll to top
                    chatMessages.scrollTop = 0;
                    
                    // Force a reflow and check if welcome screen is visible
                    welcomeScreen.offsetHeight;
                    
                    console.log('Welcome screen display:', welcomeScreen.style.display); // Debug log
                    console.log('Welcome screen visibility:', welcomeScreen.style.visibility); // Debug log
                    
                    // Use setTimeout to ensure the welcome screen shows after DOM updates
                    setTimeout(function() {
                        welcomeScreen.style.display = 'flex';
                        welcomeScreen.style.visibility = 'visible';
                        welcomeScreen.style.opacity = '1';
                        console.log('Welcome screen forced display after timeout'); // Debug log
                    }, 100);
                });
            }

            // Mobile sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.getElementById('menuBtn');
            
            if (menuBtn) {
                menuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                        sidebar.classList.remove('open');
                    }
                }
            });

            // Rate response (thumbs up/down)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.message-rate-btn')) {
                    e.preventDefault();
                    const button = e.target.closest('.message-rate-btn');
                    const logId = button.getAttribute('data-log-id');
                    const rating = button.getAttribute('data-rating');
                    rateResponse(logId, rating, button);
                }
            });

            // Rate response function
            function rateResponse(logId, rating, button) {
                // Visual feedback
                button.classList.add('rating-selected');
                // Switch icon from outline to solid
                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                }
                
                // Reset siblings
                const siblings = button.parentElement.querySelectorAll('.message-rate-btn');
                siblings.forEach(sibling => {
                    if (sibling !== button) {
                        sibling.classList.remove('rating-selected');
                        const siblingIcon = sibling.querySelector('i');
                        if (siblingIcon) {
                            siblingIcon.classList.remove('fa-solid');
                            siblingIcon.classList.add('fa-regular');
                        }
                    }
                });
                
                // Send rating to backend
                const formData = new FormData();
                formData.append('action', 'wp_gpt_rag_chat_rate_response');
                formData.append('log_id', logId);
                formData.append('rating', rating);
                formData.append('nonce', '<?php echo wp_create_nonce('wp_gpt_rag_chat_nonce'); ?>');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show brief confirmation
                        button.classList.add('rating-saved');
                        setTimeout(function() {
                            button.classList.remove('rating-saved');
                        }, 1000);
                    }
                })
                .catch(error => {
                    // Revert visual feedback on error
                    button.classList.remove('rating-selected');
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                    }
                    console.error('Rating error:', error);
                });
            }

            // Chat history functions
            function saveChatHistory() {
                // Only save actual chat messages, not the welcome screen
                const messageElements = chatMessages.querySelectorAll('.message');
                let messagesHtml = '';
                messageElements.forEach(msg => {
                    messagesHtml += msg.outerHTML;
                });
                sessionStorage.setItem('chatgpt_chat_history', messagesHtml);
            }

            function loadChatHistory() {
                const savedHistory = sessionStorage.getItem('chatgpt_chat_history');
                if (savedHistory && savedHistory.trim() !== '') {
                    console.log('Loading chat history'); // Debug log
                    // Hide welcome screen and show chat interface
                    welcomeScreen.style.display = 'none';
                    welcomeScreen.style.visibility = 'hidden';
                    welcomeScreen.style.opacity = '0';
                    document.querySelector('.chat-input-container').style.display = 'block';
                    
                    // Load saved messages (only the message elements, not the welcome screen)
                    const messageElements = chatMessages.querySelectorAll('.message');
                    messageElements.forEach(msg => msg.remove()); // Remove any existing messages first
                    chatMessages.insertAdjacentHTML('beforeend', savedHistory);
                    
                    // Scroll to bottom
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                } else {
                    console.log('No chat history found, showing welcome screen'); // Debug log
                    // Ensure welcome screen is visible if no history
                    welcomeScreen.style.display = 'flex';
                    welcomeScreen.style.visibility = 'visible';
                    welcomeScreen.style.opacity = '1';
                }
            }

            function clearChatHistory() {
                sessionStorage.removeItem('chatgpt_chat_history');
            }
        });
    </script>
</body>
</html>

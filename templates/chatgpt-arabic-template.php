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
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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

        /* Dropdown Menu Styles */
        .chat-actions {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            z-index: 1000;
            display: none;
            margin-top: 8px;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #f0f0f0;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            color: #1d2327;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
            color: #646970;
        }

        .dropdown-item:hover i {
            color: #1d2327;
        }

        #reportProblemBtn:hover i {
            color: #f39c12;
        }

        #deleteChatBtn:hover i {
            color: #e74c3c;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            display: none;
            z-index: 10000;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .modal-overlay.show {
            display: block;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease-out;
            box-sizing: border-box;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .modal-header {
            position: relative;
            padding: 20px 24px;
            border-bottom: 1px solid #e5e5e5;
            min-height: 60px;
        }

        .modal-header h3 {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            margin: 0;
            font-family: 'Tajawal', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #1d2327;
        }

        .modal-close {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 18px;
            color: #646970;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: #f0f0f0;
            color: #1d2327;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #1d2327;
        }

        .form-group select,
        .form-group textarea,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }

        .form-group select:focus,
        .form-group textarea:focus,
        .form-group input:focus {
            outline: none;
            border-color: #d1a85f;
            box-shadow: 0 0 0 2px rgba(209, 168, 95, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
        }

        .btn-cancel,
        .btn-submit {
            padding: 10px 20px;
            border-radius: 6px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-cancel {
            background: #f0f0f0;
            color: #646970;
        }

        .btn-cancel:hover {
            background: #e5e5e5;
            color: #1d2327;
        }

        .btn-submit {
            background: #d1a85f;
            color: white;
        }

        .btn-submit:hover {
            background: #c89a4f;
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-delete:hover {
            background: #c0392b;
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

        /* Disabled input state during AI processing */
        .chat-input:disabled {
            background: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .chat-input:disabled::placeholder {
            color: #ccc;
        }

        .chat-input-container.processing .chat-input {
            background: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .chat-input-container.processing .chat-input::placeholder {
            color: #ccc;
        }

        /* Sensitive input warning styles */
        .sensitive-input-warning {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            text-align: center;
            direction: rtl;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sensitive-input-warning h3 {
            color: #dc3545;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sensitive-input-warning p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sensitive-input-warning .warning-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .sensitive-input-warning .warning-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sensitive-input-warning .warning-btn-danger {
            background: #dc3545;
            color: white;
        }

        .sensitive-input-warning .warning-btn-danger:hover {
            background: #c82333;
        }

        .sensitive-input-warning .warning-btn-secondary {
            background: #6c757d;
            color: white;
        }

        .sensitive-input-warning .warning-btn-secondary:hover {
            background: #5a6268;
        }

        .sensitive-input-warning .detected-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
            text-align: right;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sensitive-input-warning .detected-info strong {
            color: #dc3545;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sensitive-input-warning .detected-info small {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .sensitive-input-warning .warning-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 15px;
        }

        .sensitive-input-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
        }

        /* Input validation styles */
        .chat-input.sensitive-detected {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .chat-input.sensitive-detected::placeholder {
            color: #dc3545;
        }

        .welcome-input.sensitive-detected {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .welcome-input.sensitive-detected::placeholder {
            color: #dc3545;
        }
        
        /* Enhanced sensitive content detection styles */
        .chat-input.sensitive-detected:focus,
        .welcome-input.sensitive-detected:focus {
            border-color: #ff2222 !important;
            box-shadow: 0 0 0 3px rgba(255, 68, 68, 0.3) !important;
        }
        
        .chat-input.sensitive-detected::placeholder,
        .welcome-input.sensitive-detected::placeholder {
            color: #ff4444 !important;
            font-weight: 500;
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

        /* Usage modal styles */
        .usage-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .usage-modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .usage-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90vw;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .usage-modal.show .usage-modal-content {
            transform: translate(-50%, -50%) scale(1);
        }
        
        .usage-modal-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .usage-modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .usage-modal-text {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .usage-modal-checkbox {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .usage-modal-checkbox:hover {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }
        
        .usage-modal-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #007cba;
            background: white;
            border: 2px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
            display: inline-block;
            vertical-align: middle;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        .usage-modal-checkbox input[type="checkbox"]:hover {
            border-color: #007cba;
            background: white;
        }
        
        .usage-modal-checkbox input[type="checkbox"]:checked {
            background: #007cba;
            border-color: #007cba;
            position: relative;
        }
        
        .usage-modal-checkbox input[type="checkbox"]:checked::after {
            content: "✓";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 14px;
            font-weight: bold;
        }
        
        .usage-modal-checkbox label {
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            margin: 0;
            color: #333;
        }
        
        .usage-modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .usage-modal-button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .usage-modal-button.primary {
            background: #ff6b6b;
            color: white;
        }
        
        .usage-modal-button.primary:hover {
            background: #ff5252;
        }
        
        .usage-modal-button.primary:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .usage-modal-button.secondary {
            background: #6c757d;
            color: white;
        }
        
        .usage-modal-button.secondary:hover {
            background: #5a6268;
        }
        
        /* Responsive modal styles */
        @media (max-width: 768px) {
            .usage-modal-content {
                padding: 20px;
                max-width: 90%;
            }
            
            .usage-modal-title {
                font-size: 20px;
            }
            
            .usage-modal-text {
                font-size: 14px;
            }
            
            .usage-modal-buttons {
                flex-direction: column;
            }
            
            .usage-modal-button {
                width: 100%;
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
    <!-- Usage Modal -->
    <div class="usage-modal" id="usageModal">
        <div class="usage-modal-content">
            <div class="usage-modal-icon">⚠️</div>
            <h2 class="usage-modal-title">تنبيه مهم</h2>
            <p class="usage-modal-text">
                هذا المساعد الذكي مخصص للاستخدام الداخلي فقط. يرجى التحقق من المخرجات قبل الاستخدام.
            </p>
            <div class="usage-modal-checkbox">
                <input type="checkbox" id="usageCheckbox" />
                <label for="usageCheckbox">أوافق على شروط الاستخدام</label>
            </div>
            <div class="usage-modal-buttons">
                <button class="usage-modal-button primary" id="usageModalAgree" disabled>
                    موافق
                </button>
                <button class="usage-modal-button secondary" id="usageModalCancel">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

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
                    // Try multiple ways to get the settings
                    $settings = \WP_GPT_RAG_Chat\Settings::get_settings();
                    $chat_logo = $settings['chat_logo'] ?? '';
                    
                    // Alternative: Try getting from WordPress options directly
                    if (empty($chat_logo)) {
                        $wp_settings = get_option('wp_gpt_rag_chat_settings', array());
                        $chat_logo = $wp_settings['chat_logo'] ?? '';
                    }
                    
                    // Debug: Add console log to check settings
                    echo '<!-- Debug: chat_logo = ' . esc_html($chat_logo) . ' -->';
                    echo '<!-- Debug: settings array = ' . esc_html(print_r($settings, true)) . ' -->';
                    
                    if (!empty($chat_logo) && filter_var($chat_logo, FILTER_VALIDATE_URL)): ?>
                        <img src="<?php echo esc_url($chat_logo); ?>" alt="<?php esc_attr_e('Chat Logo', 'wp-gpt-rag-chat'); ?>" class="chat-logo-img" />
                    <?php else: ?>
                        <!-- Debug: Show default avatar when no logo is set -->
                        <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/images/avatar_small.png'); ?>" alt="<?php esc_attr_e('Chat Avatar', 'wp-gpt-rag-chat'); ?>" class="chat-logo-default-img" />
                    <?php endif; ?>
                </div>
                <div class="chat-actions">
                    <button class="chat-action" id="newChatHeaderBtn" title="<?php esc_attr_e('محادثة جديدة', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="chat-action" id="menuBtn" title="<?php esc_attr_e('القائمة', 'wp-gpt-rag-chat'); ?>">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu" id="dropdownMenu">
                        <div class="dropdown-item" id="reportProblemBtn">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span><?php esc_html_e('الإبلاغ عن مشكلة', 'wp-gpt-rag-chat'); ?></span>
                        </div>
                        <div class="dropdown-item" id="deleteChatBtn">
                            <i class="fas fa-trash"></i>
                            <span><?php esc_html_e('حذف المحادثة', 'wp-gpt-rag-chat'); ?></span>
                        </div>
                    </div>
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
                    مجلس النواب - للاستخدام الداخلي فقط - الإصدار 1.0.0 <br>
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

    <!-- Report Problem Modal -->
    <div class="modal-overlay" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php esc_html_e('الإبلاغ عن مشكلة', 'wp-gpt-rag-chat'); ?></h3>
                <button class="modal-close" id="closeReportModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="form-group">
                        <label for="problemType"><?php esc_html_e('نوع المشكلة', 'wp-gpt-rag-chat'); ?></label>
                        <select id="problemType" name="problem_type" required>
                            <option value=""><?php esc_html_e('اختر نوع المشكلة', 'wp-gpt-rag-chat'); ?></option>
                            <option value="wrong_information"><?php esc_html_e('معلومات خاطئة', 'wp-gpt-rag-chat'); ?></option>
                            <option value="bias"><?php esc_html_e('تحيز أو عدم موضوعية', 'wp-gpt-rag-chat'); ?></option>
                            <option value="hallucination"><?php esc_html_e('معلومات غير صحيحة أو وهمية', 'wp-gpt-rag-chat'); ?></option>
                            <option value="inappropriate_response"><?php esc_html_e('رد غير مناسب', 'wp-gpt-rag-chat'); ?></option>
                            <option value="technical_issue"><?php esc_html_e('مشكلة تقنية', 'wp-gpt-rag-chat'); ?></option>
                            <option value="privacy_concern"><?php esc_html_e('مخاوف الخصوصية', 'wp-gpt-rag-chat'); ?></option>
                            <option value="parliamentary_procedure"><?php esc_html_e('خطأ في الإجراءات البرلمانية', 'wp-gpt-rag-chat'); ?></option>
                            <option value="legal_advice"><?php esc_html_e('نصيحة قانونية خاطئة', 'wp-gpt-rag-chat'); ?></option>
                            <option value="other"><?php esc_html_e('أخرى', 'wp-gpt-rag-chat'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="problemDescription"><?php esc_html_e('وصف المشكلة', 'wp-gpt-rag-chat'); ?></label>
                        <textarea 
                            id="problemDescription" 
                            name="problem_description" 
                            rows="4" 
                            placeholder="<?php esc_attr_e('يرجى وصف المشكلة بالتفصيل...', 'wp-gpt-rag-chat'); ?>"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="userEmail"><?php esc_html_e('البريد الإلكتروني (اختياري)', 'wp-gpt-rag-chat'); ?></label>
                        <input 
                            type="email" 
                            id="userEmail" 
                            name="user_email" 
                            placeholder="<?php esc_attr_e('example@domain.com', 'wp-gpt-rag-chat'); ?>"
                        />
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelReport"><?php esc_html_e('إلغاء', 'wp-gpt-rag-chat'); ?></button>
                        <button type="submit" class="btn-submit"><?php esc_html_e('إرسال التقرير', 'wp-gpt-rag-chat'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Chat Confirmation Modal -->
    <div class="modal-overlay" id="deleteConfirmationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php esc_html_e('حذف المحادثة', 'wp-gpt-rag-chat'); ?></h3>
                <button class="modal-close" id="closeDeleteModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px 0;">
                    <div style="font-size: 48px; color: #e74c3c; margin-bottom: 20px;">
                        <i class="fas fa-trash"></i>
                    </div>
                    <h4 style="margin-bottom: 15px; color: #1d2327;">
                        <?php esc_html_e('هل أنت متأكد من حذف هذه المحادثة؟', 'wp-gpt-rag-chat'); ?>
                    </h4>
                    <p style="color: #646970; margin-bottom: 25px;">
                        <?php esc_html_e('سيتم حذف جميع الرسائل في هذه المحادثة ولن تتمكن من استردادها.', 'wp-gpt-rag-chat'); ?>
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button class="btn-cancel" id="cancelDelete">
                            <?php esc_html_e('إلغاء', 'wp-gpt-rag-chat'); ?>
                        </button>
                        <button class="btn-delete" id="confirmDelete">
                            <?php esc_html_e('حذف المحادثة', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php esc_html_e('تم الإرسال بنجاح', 'wp-gpt-rag-chat'); ?></h3>
                <button class="modal-close" id="closeSuccessModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px 0;">
                    <div style="font-size: 48px; color: #28a745; margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4 style="margin-bottom: 15px; color: #1d2327;">
                        <?php esc_html_e('تم إرسال التقرير بنجاح', 'wp-gpt-rag-chat'); ?>
                    </h4>
                    <p style="color: #646970; margin-bottom: 25px;">
                        <?php esc_html_e('شكراً لك على إرسال التقرير. سنقوم بمراجعة المشكلة والعمل على حلها في أقرب وقت ممكن.', 'wp-gpt-rag-chat'); ?>
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: center;">
                        <button class="btn-submit" id="closeSuccessModalBtn">
                            <?php esc_html_e('موافق', 'wp-gpt-rag-chat'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Usage modal elements
            const usageModal = document.getElementById('usageModal');
            const usageCheckbox = document.getElementById('usageCheckbox');
            const usageModalAgree = document.getElementById('usageModalAgree');
            const usageModalCancel = document.getElementById('usageModalCancel');
            
            // Cookie functions
            function setCookie(name, value, days) {
                const expires = new Date();
                expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
            }
            
            function getCookie(name) {
                const nameEQ = name + '=';
                const ca = document.cookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
            
            // Check if user has already acknowledged
            if (getCookie('nuwab_ai_usage_acknowledged') === 'true') {
                usageModal.style.display = 'none';
            } else {
                // Show modal on first load
                setTimeout(() => {
                    usageModal.classList.add('show');
                }, 500);
            }
            
            // Handle checkbox change
            usageCheckbox.addEventListener('change', function() {
                usageModalAgree.disabled = !this.checked;
            });
            
            // Handle agree button
            usageModalAgree.addEventListener('click', function() {
                if (usageCheckbox.checked) {
                    usageModal.classList.remove('show');
                    // Store acknowledgment in cookie for 30 days
                    setCookie('nuwab_ai_usage_acknowledged', 'true', 30);
                }
            });
            
            // Handle cancel button
            usageModalCancel.addEventListener('click', function() {
                usageModal.classList.remove('show');
                // Redirect to home page or close window
                window.location.href = '/';
            });
            
            const chatInput = document.getElementById('chatInput');
            const sendBtn = document.getElementById('sendBtn');
            const chatMessages = document.getElementById('chatMessages');
            const welcomeScreen = document.getElementById('welcomeScreen');
            const newChatBtn = document.getElementById('newChatBtn');
            const welcomeInput = document.getElementById('welcomeInput');
            const welcomeSendBtn = document.getElementById('welcomeSendBtn');
            
            // Abort controller for cancelling requests
            let currentAbortController = null;
            
            // Sensitive input detection patterns
            const sensitivePatterns = {
                email: /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g,
                cpr: /(\d{6}-\d{4})|(\d{10})/g,
                phone: /(\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/g,
                creditCard: /\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/g,
                ssn: /\b\d{3}-\d{2}-\d{4}\b/g,
                passport: /\b[A-Z]{1,2}\d{6,9}\b/g,
                nationalId: /\b\d{9,14}\b/g,
                password: /(?:password|كلمة المرور|كلمة السر|باسورد|باسوورد)\s*[:=]?\s*[^\s]+/gi,
                apiKey: /(?:api[_-]?key|api[_-]?token|access[_-]?token|bearer[_-]?token|secret[_-]?key)\s*[:=]?\s*[a-zA-Z0-9_\-\.]{20,}/gi,
                secretKey: /(?:secret[_-]?key|private[_-]?key|encryption[_-]?key)\s*[:=]?\s*[a-zA-Z0-9_\-\.]{20,}/gi,
                token: /(?:token|رمز|توكين)\s*[:=]?\s*[a-zA-Z0-9_\-\.]{20,}/gi,
                authToken: /(?:auth[_-]?token|authorization[_-]?token)\s*[:=]?\s*[a-zA-Z0-9_\-\.]{20,}/gi,
                jwt: /eyJ[a-zA-Z0-9_\-]+\.[a-zA-Z0-9_\-]+\.[a-zA-Z0-9_\-]+/g,
                awsKey: /AKIA[0-9A-Z]{16}/g,
                googleApiKey: /AIza[0-9A-Za-z_-]{35}/g,
                openaiKey: /sk-[a-zA-Z0-9]{48}/g,
                githubToken: /ghp_[a-zA-Z0-9]{36}/g,
                slackToken: /xox[baprs]-[a-zA-Z0-9-]+/g,
                discordToken: /[MN][A-Za-z\d]{23}\.[\w-]{6}\.[\w-]{27}/g
            };
            
            // Sensitive input detection function
            function detectSensitiveInput(text) {
                const detected = [];
                
                for (const [type, pattern] of Object.entries(sensitivePatterns)) {
                    const matches = text.match(pattern);
                    if (matches) {
                        detected.push({
                            type: type,
                            matches: matches,
                            count: matches.length
                        });
                    }
                }
                
                return detected;
            }
            
            // Show sensitive input warning
            function showSensitiveWarning(detectedItems) {
                // Create overlay
                const overlay = document.createElement('div');
                overlay.className = 'sensitive-input-overlay';
                overlay.id = 'sensitiveInputOverlay';
                
                // Create warning modal
                const warning = document.createElement('div');
                warning.className = 'sensitive-input-warning';
                warning.id = 'sensitiveInputWarning';
                
                // Build detected items list
                let detectedList = '';
                detectedItems.forEach(item => {
                    const typeNames = {
                        email: 'البريد الإلكتروني',
                        cpr: 'رقم الهوية الشخصية (CPR)',
                        phone: 'رقم الهاتف',
                        creditCard: 'رقم البطاقة الائتمانية',
                        ssn: 'رقم الضمان الاجتماعي',
                        passport: 'رقم جواز السفر',
                        nationalId: 'رقم الهوية الوطنية',
                        password: 'كلمة المرور',
                        apiKey: 'مفتاح API',
                        secretKey: 'المفتاح السري',
                        token: 'الرمز المميز',
                        authToken: 'رمز المصادقة',
                        jwt: 'رمز JWT',
                        awsKey: 'مفتاح AWS',
                        googleApiKey: 'مفتاح Google API',
                        openaiKey: 'مفتاح OpenAI',
                        githubToken: 'رمز GitHub',
                        slackToken: 'رمز Slack',
                        discordToken: 'رمز Discord'
                    };
                    
                    detectedList += `
                        <div class="detected-info">
                            <strong>${typeNames[item.type] || item.type}:</strong> ${item.count} ${item.count === 1 ? 'مطابقة' : 'مطابقات'}
                            <br><small>${item.matches.slice(0, 3).join(', ')}${item.matches.length > 3 ? '...' : ''}</small>
                        </div>
                    `;
                });
                
                warning.innerHTML = `
                    <div class="warning-icon">⚠️</div>
                    <h3>تحذير: معلومات حساسة مكتشفة</h3>
                    <p>تم اكتشاف معلومات حساسة في رسالتك. يرجى عدم مشاركة المعلومات الشخصية في المحادثة.</p>
                    ${detectedList}
                    <div class="warning-buttons">
                        <button class="warning-btn warning-btn-danger" onclick="removeSensitiveContent()">
                            إزالة المحتوى الحساس
                        </button>
                        <button class="warning-btn warning-btn-secondary" onclick="closeSensitiveWarning()">
                            إلغاء
                        </button>
                    </div>
                `;
                
                // Add to page
                document.body.appendChild(overlay);
                document.body.appendChild(warning);
                
                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            }
            
            // Remove sensitive content function
            window.removeSensitiveContent = function() {
                const chatInput = document.getElementById('chatInput');
                const welcomeInput = document.getElementById('welcomeInput');
                
                let currentInput = chatInput && chatInput.style.display !== 'none' ? chatInput : welcomeInput;
                
                if (currentInput) {
                    let text = currentInput.value;
                    
                    // Remove sensitive patterns
                    for (const [type, pattern] of Object.entries(sensitivePatterns)) {
                        text = text.replace(pattern, '[معلومات حساسة]');
                    }
                    
                    currentInput.value = text;
                    currentInput.classList.remove('sensitive-detected');
                }
                
                closeSensitiveWarning();
            };
            
            // Close sensitive warning function
            window.closeSensitiveWarning = function() {
                const overlay = document.getElementById('sensitiveInputOverlay');
                const warning = document.getElementById('sensitiveInputWarning');
                
                if (overlay) overlay.remove();
                if (warning) warning.remove();
                
                // Restore body scroll
                document.body.style.overflow = '';
            };
            
            // Validate input for sensitive content
            function validateInput(input) {
                const text = input.value.trim();
                if (!text) return true;
                
                const detected = detectSensitiveInput(text);
                if (detected.length > 0) {
                    input.classList.add('sensitive-detected');
                    showSensitiveWarning(detected);
                    return false;
                } else {
                    input.classList.remove('sensitive-detected');
                    return true;
                }
            }
            
            // Real-time validation on input change
            function validateInputRealtime(input) {
                const text = input.value.trim();
                if (!text) {
                    input.classList.remove('sensitive-detected');
                    return true;
                }
                
                const detected = detectSensitiveInput(text);
                if (detected.length > 0) {
                    input.classList.add('sensitive-detected');
                    return false;
                } else {
                    input.classList.remove('sensitive-detected');
                    return true;
                }
            }
            
            // Check if input has sensitive content (without showing warning)
            function hasSensitiveContent(input) {
                const text = input.value.trim();
                if (!text) return false;
                
                const detected = detectSensitiveInput(text);
                return detected.length > 0;
            }
            
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
                
                // Real-time sensitive content validation
                validateInputRealtime(this);
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
                
                // Real-time sensitive content validation
                validateInputRealtime(this);
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
                
                // Disable input during processing
                disableInput();
                
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

            // Disable input during AI processing
            function disableInput() {
                const chatInput = document.getElementById('chatInput');
                const welcomeInput = document.getElementById('welcomeInput');
                const inputContainer = document.querySelector('.chat-input-container');
                
                // Disable chat input
                if (chatInput) {
                    chatInput.disabled = true;
                    chatInput.placeholder = 'جاري المعالجة...';
                }
                
                // Disable welcome input
                if (welcomeInput) {
                    welcomeInput.disabled = true;
                    welcomeInput.placeholder = 'جاري المعالجة...';
                }
                
                // Add processing class to container
                if (inputContainer) {
                    inputContainer.classList.add('processing');
                }
            }

            // Enable input after AI processing
            function enableInput() {
                const chatInput = document.getElementById('chatInput');
                const welcomeInput = document.getElementById('welcomeInput');
                const inputContainer = document.querySelector('.chat-input-container');
                
                // Enable chat input
                if (chatInput) {
                    chatInput.disabled = false;
                    chatInput.placeholder = 'اكتب رسالتك هنا...';
                }
                
                // Enable welcome input
                if (welcomeInput) {
                    welcomeInput.disabled = false;
                    welcomeInput.placeholder = 'اكتب رسالتك هنا...';
                }
                
                // Remove processing class from container
                if (inputContainer) {
                    inputContainer.classList.remove('processing');
                }
            }

            // Force stop AI processing (used when New Chat is clicked)
            function forceStopAIProcessing() {
                // Abort any ongoing request
                if (currentAbortController) {
                    currentAbortController.abort();
                    currentAbortController = null;
                }
                
                // Hide typing indicator immediately
                hideTypingIndicator();
                
                // Enable input immediately
                enableInput();
                
                // Change stop button back to send button
                changeToSendButton();
                
                // Clear any pending AI response
                console.log('AI processing force stopped by New Chat');
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
                
                // Enable input after processing
                enableInput();
                
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
                
                // Enable input after stopping
                enableInput();
                
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
                    
                    // Enable input after processing
                    enableInput();
                    
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
                    
                    // Enable input after error
                    enableInput();
                    
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
                // Don't send if input is disabled
                if (chatInput.disabled) return;
                
                const message = chatInput.value.trim();
                if (message) {
                    // Check for sensitive content (block if present)
                    if (hasSensitiveContent(chatInput)) {
                        // Show warning if not already shown
                        if (!document.querySelector('.sensitive-input-warning')) {
                            const detected = detectSensitiveInput(message);
                            showSensitiveWarning(detected);
                        }
                        return; // Block submission
                    }
                    
                    sendMessage(message);
                    chatInput.value = '';
                    chatInput.style.height = 'auto';
                    chatInput.classList.remove('extended');
                }
            });

            chatInput.addEventListener('keydown', function(e) {
                // Don't send if input is disabled
                if (this.disabled) return;
                
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const message = this.value.trim();
                    if (message) {
                        // Check for sensitive content (block if present)
                        if (hasSensitiveContent(this)) {
                            // Show warning if not already shown
                            if (!document.querySelector('.sensitive-input-warning')) {
                                const detected = detectSensitiveInput(message);
                                showSensitiveWarning(detected);
                            }
                            return; // Block submission
                        }
                        
                        sendMessage(message);
                        this.value = '';
                        this.style.height = 'auto';
                        this.classList.remove('extended');
                    }
                }
            });

            // Welcome input event listeners
            welcomeSendBtn.addEventListener('click', function() {
                // Don't send if input is disabled
                if (welcomeInput.disabled) return;
                
                const message = welcomeInput.value.trim();
                if (message) {
                    // Check for sensitive content (block if present)
                    if (hasSensitiveContent(welcomeInput)) {
                        // Show warning if not already shown
                        if (!document.querySelector('.sensitive-input-warning')) {
                            const detected = detectSensitiveInput(message);
                            showSensitiveWarning(detected);
                        }
                        return; // Block submission
                    }
                    
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
                // Don't send if input is disabled
                if (this.disabled) return;
                
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const message = this.value.trim();
                    if (message) {
                        // Check for sensitive content (block if present)
                        if (hasSensitiveContent(this)) {
                            // Show warning if not already shown
                            if (!document.querySelector('.sensitive-input-warning')) {
                                const detected = detectSensitiveInput(message);
                                showSensitiveWarning(detected);
                            }
                            return; // Block submission
                        }
                        
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
                // Force stop any ongoing AI processing
                forceStopAIProcessing();
                
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
                    
                    // Force stop any ongoing AI processing
                    forceStopAIProcessing();
                    
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
            const dropdownMenu = document.getElementById('dropdownMenu');
            
            if (menuBtn) {
                menuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (dropdownMenu && !menuBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });

            // Dropdown menu item handlers
            const reportProblemBtn = document.getElementById('reportProblemBtn');
            const deleteChatBtn = document.getElementById('deleteChatBtn');

            if (reportProblemBtn) {
                reportProblemBtn.addEventListener('click', function() {
                    dropdownMenu.classList.remove('show');
                    // Show the report modal
                    const reportModal = document.getElementById('reportModal');
                    if (reportModal) {
                        reportModal.classList.add('show');
                    }
                });
            }

            if (deleteChatBtn) {
                deleteChatBtn.addEventListener('click', function() {
                    dropdownMenu.classList.remove('show');
                    // Show delete confirmation modal
                    showDeleteConfirmationModal();
                });
            }

            // Modal event handlers
            const reportModal = document.getElementById('reportModal');
            const closeReportModal = document.getElementById('closeReportModal');
            const cancelReport = document.getElementById('cancelReport');
            const reportForm = document.getElementById('reportForm');

            // Close modal handlers
            if (closeReportModal) {
                closeReportModal.addEventListener('click', function() {
                    reportModal.classList.remove('show');
                });
            }

            if (cancelReport) {
                cancelReport.addEventListener('click', function() {
                    reportModal.classList.remove('show');
                });
            }

            // Close modal when clicking overlay
            if (reportModal) {
                reportModal.addEventListener('click', function(e) {
                    if (e.target === reportModal) {
                        reportModal.classList.remove('show');
                    }
                });
            }

            // Form submission handler
            if (reportForm) {
                reportForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = reportForm.querySelector('.btn-submit');
                    const originalText = submitBtn.textContent;
                    
                    // Disable submit button and show loading
                    submitBtn.disabled = true;
                    submitBtn.textContent = '<?php echo esc_js(__('جاري الإرسال...', 'wp-gpt-rag-chat')); ?>';
                    
                    // Get form data
                    const formData = new FormData(reportForm);
                    formData.append('action', 'submit_incident_report');
                    formData.append('nonce', '<?php echo wp_create_nonce('incident_report_nonce'); ?>');
                    
                    // Submit via AJAX
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success modal instead of alert
                            const successModal = document.getElementById('successModal');
                            if (successModal) {
                                successModal.classList.add('show');
                            }
                            reportModal.classList.remove('show');
                            reportForm.reset();
                        } else {
                            alert('<?php echo esc_js(__('حدث خطأ في إرسال التقرير', 'wp-gpt-rag-chat')); ?>: ' + (data.data || ''));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('<?php echo esc_js(__('حدث خطأ في إرسال التقرير', 'wp-gpt-rag-chat')); ?>');
                    })
                    .finally(() => {
                        // Re-enable submit button
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
                });
            }

            // Delete confirmation modal handlers
            const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
            const closeDeleteModal = document.getElementById('closeDeleteModal');
            const cancelDelete = document.getElementById('cancelDelete');
            const confirmDelete = document.getElementById('confirmDelete');

            // Close delete modal handlers
            if (closeDeleteModal) {
                closeDeleteModal.addEventListener('click', function() {
                    deleteConfirmationModal.classList.remove('show');
                });
            }

            if (cancelDelete) {
                cancelDelete.addEventListener('click', function() {
                    deleteConfirmationModal.classList.remove('show');
                });
            }

            // Close delete modal when clicking overlay
            if (deleteConfirmationModal) {
                deleteConfirmationModal.addEventListener('click', function(e) {
                    if (e.target === deleteConfirmationModal) {
                        deleteConfirmationModal.classList.remove('show');
                    }
                });
            }

            // Confirm delete handler
            if (confirmDelete) {
                confirmDelete.addEventListener('click', function() {
                    deleteConfirmationModal.classList.remove('show');
                    performChatDeletion();
                });
            }

            // Success modal handlers
            const successModal = document.getElementById('successModal');
            const closeSuccessModal = document.getElementById('closeSuccessModal');
            const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');

            // Close success modal handlers
            if (closeSuccessModal) {
                closeSuccessModal.addEventListener('click', function() {
                    successModal.classList.remove('show');
                });
            }

            if (closeSuccessModalBtn) {
                closeSuccessModalBtn.addEventListener('click', function() {
                    successModal.classList.remove('show');
                });
            }

            // Close success modal when clicking overlay
            if (successModal) {
                successModal.addEventListener('click', function(e) {
                    if (e.target === successModal) {
                        successModal.classList.remove('show');
                    }
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

        // Function to show delete confirmation modal
        function showDeleteConfirmationModal() {
            const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
            if (deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('show');
            }
        }

        // Function to perform chat deletion
        function performChatDeletion() {
            // Clear chat messages and show welcome screen
            const chatMessages = document.getElementById('chatMessages');
            const welcomeScreen = document.getElementById('welcomeScreen');
            
            if (chatMessages && welcomeScreen) {
                // Clear all messages
                chatMessages.innerHTML = '';
                
                // Add the welcome screen back
                chatMessages.appendChild(welcomeScreen);
                
                // Ensure welcome screen is visible and properly styled
                welcomeScreen.style.display = 'flex';
                welcomeScreen.style.visibility = 'visible';
                welcomeScreen.style.opacity = '1';
                welcomeScreen.style.flexDirection = 'column';
                welcomeScreen.style.alignItems = 'center';
                welcomeScreen.style.justifyContent = 'center';
                welcomeScreen.style.height = '100%';
                welcomeScreen.style.textAlign = 'center';
                welcomeScreen.style.padding = '20px 40px';
            }
            
            // Clear any stored chat data
            localStorage.removeItem('chatHistory');
            localStorage.removeItem('chatSession');
            localStorage.removeItem('conversationId');
            sessionStorage.removeItem('chatgpt_chat_history');
            
            // Reset any chat-related state
            if (typeof clearChatHistory === 'function') {
                clearChatHistory();
            }
            
            // Hide any typing indicators or other chat elements
            const typingIndicator = document.querySelector('.typing-indicator');
            if (typingIndicator) {
                typingIndicator.style.display = 'none';
            }
            
            // Reset input field
            const chatInput = document.getElementById('chatInput');
            if (chatInput) {
                chatInput.value = '';
                chatInput.style.height = 'auto';
            }
            
            // Reset welcome input if it exists
            const welcomeInput = document.getElementById('welcomeInput');
            if (welcomeInput) {
                welcomeInput.value = '';
                welcomeInput.style.height = 'auto';
            }
            
            // Hide the chat input container to show only welcome screen
            const chatInputContainer = document.querySelector('.chat-input-container');
            if (chatInputContainer) {
                chatInputContainer.style.display = 'none';
            }
            
            console.log('Chat deleted successfully - Welcome screen displayed');
        }
    </script>
</body>
</html>

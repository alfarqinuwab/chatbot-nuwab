/**
 * Frontend JavaScript for WP GPT RAG Chat
 */
(function($) {
    'use strict';
    
    var ChatWidget = {
        init: function() {
            this.bindEvents();
            this.initializeWidget();
        },
        
        bindEvents: function() {
            var self = this;
            
            // Toggle chat widget
            $(document).on('click', '.wp-gpt-rag-chat-toggle', function() {
                self.toggleWidget();
            });
            
            // Send message
            $(document).on('click', '#wp-gpt-rag-chat-send', function() {
                self.sendMessage();
            });
            
            // Send message on Enter (but not Shift+Enter)
            $(document).on('keydown', '#wp-gpt-rag-chat-input', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });
            
            // Auto-resize textarea
            $(document).on('input', '#wp-gpt-rag-chat-input', function() {
                self.autoResizeTextarea(this);
            });
            
            // Clear chat
            $(document).on('click', '.wp-gpt-rag-chat-clear', function() {
                self.clearChat();
            });
        },
        
        initializeWidget: function() {
            // Check if widget exists
            if ($('#wp-gpt-rag-chat-widget').length === 0) {
                return;
            }
            
            // Initialize widget state
            this.isOpen = false;
            this.isLoading = false;
            this.conversationHistory = [];
            
            // Show initial state
            this.updateWidgetState();
        },
        
        toggleWidget: function() {
            this.isOpen = !this.isOpen;
            this.updateWidgetState();
            
            if (this.isOpen) {
                $('#wp-gpt-rag-chat-input').focus();
            }
        },
        
        updateWidgetState: function() {
            var $widget = $('#wp-gpt-rag-chat-widget');
            var $body = $('.wp-gpt-rag-chat-body');
            var $icon = $('.wp-gpt-rag-chat-icon');
            
            if (this.isOpen) {
                $widget.addClass('wp-gpt-rag-chat-open');
                $body.slideDown(300);
                $icon.text('×');
            } else {
                $widget.removeClass('wp-gpt-rag-chat-open');
                $body.slideUp(300);
                $icon.text('💬');
            }
        },
        
        sendMessage: function() {
            if (this.isLoading) {
                return;
            }
            
            var $input = $('#wp-gpt-rag-chat-input');
            var $consent = $('#wp-gpt-rag-chat-consent');
            var message = $input.val().trim();
            
            if (!message) {
                return;
            }
            
            // Check consent if required
            if ($consent.length && !$consent.is(':checked')) {
                this.showError(wpGptRagChat.strings.consentRequired);
                return;
            }
            
            // Add user message to chat
            this.addMessage('user', message);
            
            // Clear input
            $input.val('');
            this.autoResizeTextarea($input[0]);
            
            // Show loading state
            this.setLoading(true);
            
            // Send to server
            this.sendToServer(message);
        },
        
        sendToServer: function(message) {
            var self = this;
            
            $.ajax({
                url: wpGptRagChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_gpt_rag_chat_query',
                    query: message,
                    consent: $('#wp-gpt-rag-chat-consent').is(':checked'),
                    nonce: wpGptRagChat.nonce
                },
                success: function(response) {
                    self.setLoading(false);
                    
                    if (response.success) {
                        self.addMessage('assistant', response.data.response);
                        self.conversationHistory.push(
                            { role: 'user', content: message },
                            { role: 'assistant', content: response.data.response }
                        );
                    } else {
                        self.showError(response.data.message || wpGptRagChat.strings.error);
                    }
                },
                error: function() {
                    self.setLoading(false);
                    self.showError(wpGptRagChat.strings.error);
                }
            });
        },
        
        addMessage: function(role, content) {
            var $messages = $('#wp-gpt-rag-chat-messages');
            var messageClass = role === 'user' ? 'wp-gpt-rag-chat-message-user' : 'wp-gpt-rag-chat-message-assistant';
            
            var $message = $('<div class="wp-gpt-rag-chat-message ' + messageClass + '">' +
                '<div class="wp-gpt-rag-chat-message-content">' +
                this.escapeHtml(content) +
                '</div>' +
                '<div class="wp-gpt-rag-chat-message-time">' +
                this.getCurrentTime() +
                '</div>' +
                '</div>');
            
            $messages.append($message);
            this.scrollToBottom();
        },
        
        showError: function(message) {
            var $messages = $('#wp-gpt-rag-chat-messages');
            var $error = $('<div class="wp-gpt-rag-chat-message wp-gpt-rag-chat-message-error">' +
                '<div class="wp-gpt-rag-chat-message-content">' +
                this.escapeHtml(message) +
                '</div>' +
                '</div>');
            
            $messages.append($error);
            this.scrollToBottom();
            
            // Remove error after 5 seconds
            setTimeout(function() {
                $error.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        setLoading: function(loading) {
            this.isLoading = loading;
            var $sendButton = $('#wp-gpt-rag-chat-send');
            var $input = $('#wp-gpt-rag-chat-input');
            
            if (loading) {
                $sendButton.prop('disabled', true).text(wpGptRagChat.strings.loading);
                $input.prop('disabled', true);
                
                // Add loading message
                var $loading = $('<div class="wp-gpt-rag-chat-message wp-gpt-rag-chat-message-assistant wp-gpt-rag-chat-message-loading">' +
                    '<div class="wp-gpt-rag-chat-message-content">' +
                    '<div class="wp-gpt-rag-chat-typing-indicator">' +
                    '<span></span><span></span><span></span>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
                
                $('#wp-gpt-rag-chat-messages').append($loading);
                this.scrollToBottom();
            } else {
                $sendButton.prop('disabled', false).text('Send');
                $input.prop('disabled', false);
                
                // Remove loading message
                $('.wp-gpt-rag-chat-message-loading').remove();
            }
        },
        
        clearChat: function() {
            var $messages = $('#wp-gpt-rag-chat-messages');
            $messages.empty();
            
            // Add system message
            $messages.append('<div class="wp-gpt-rag-chat-message wp-gpt-rag-chat-message-system">' +
                '<div class="wp-gpt-rag-chat-message-content">' +
                'Hello! I can help you find information from this website. What would you like to know?' +
                '</div>' +
                '</div>');
            
            this.conversationHistory = [];
        },
        
        autoResizeTextarea: function(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        },
        
        scrollToBottom: function() {
            var $messages = $('#wp-gpt-rag-chat-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        },
        
        getCurrentTime: function() {
            var now = new Date();
            return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
        
        escapeHtml: function(text) {
            var map = {
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
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        ChatWidget.init();
    });
    
    // Make ChatWidget available globally for debugging
    window.WPGptRagChatWidget = ChatWidget;
    
})(jQuery);

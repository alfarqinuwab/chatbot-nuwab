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
            
            // Open chat widget (FAB button)
            $(document).on('click', '.wp-gpt-rag-chat-fab-button', function() {
                self.toggleWidget();
            });
            
            // Close chat widget (toggle button)
            $(document).on('click', '.wp-gpt-rag-chat-toggle', function() {
                self.toggleWidget();
            });
            
            // Send message
            $(document).on('click', '#wp-gpt-rag-chat-send', function() {
                self.sendMessage();
            });
            
            // Send message on Enter
            $(document).on('keydown', '#wp-gpt-rag-chat-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    self.sendMessage();
                }
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
            this.fabMessages = [
                'مرحباً، كيف يمكنني مساعدتك؟',
                'اسألني عن أي شيء في الموقع!',
                'هل تحتاج إلى مساعدة معينة؟',
                'أنا هنا لدعمك في البحث عن المعلومات.',
                'تفضل بكتابة استفسارك وسأجيب فوراً.'
            ];
            
            // Show initial state
            this.updateWidgetState();
            this.startFabMessages();
        },
        
        toggleWidget: function() {
            this.isOpen = !this.isOpen;
            this.updateWidgetState();
            
            if (this.isOpen) {
                $('#wp-gpt-rag-chat-input').focus();
            }
        },

        startFabMessages: function() {
            var self = this;
            var $bubble = $('.wp-gpt-rag-chat-fab-bubble');
            if ($bubble.length === 0 || this.fabMessages.length === 0) {
                return;
            }
            
            var showMessage = function() {
                if (self.isOpen) {
                    scheduleNext();
                    return;
                }
                var message = self.getRandomMessage();
                $bubble.text(message).addClass('show');
                setTimeout(function() {
                    $bubble.removeClass('show');
                    scheduleNext();
                }, 3500);
            };
            
            var scheduleNext = function() {
                setTimeout(showMessage, 10000);
            };
            
            scheduleNext();
        },

        getRandomMessage: function() {
            var index = Math.floor(Math.random() * this.fabMessages.length);
            return this.fabMessages[index];
        },
        
        updateWidgetState: function() {
            var $widget = $('#wp-gpt-rag-chat-widget');
            
            if (this.isOpen) {
                $widget.addClass('wp-gpt-rag-chat-open');
                // Focus on input when opened
                setTimeout(function() {
                    $('#wp-gpt-rag-chat-input').focus();
                }, 300);
            } else {
                $widget.removeClass('wp-gpt-rag-chat-open');
            }
        },
        
        sendMessage: function() {
            if (this.isLoading) {
                return;
            }
            
            var $input = $('#wp-gpt-rag-chat-input');
            var message = $input.val().trim();
            
            if (!message) {
                return;
            }
            
            // Add user message to chat
            this.addMessage('user', message);
            
            // Clear input
            $input.val('');
            
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
                $sendButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
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
                $sendButton.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
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
                'مرحباً! يمكنني مساعدتك في إيجاد المعلومات من هذا الموقع. كيف يمكنني مساعدتك؟' +
                '</div>' +
                '</div>');
            
            this.conversationHistory = [];
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

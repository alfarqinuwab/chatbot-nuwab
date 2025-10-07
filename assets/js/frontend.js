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
            $(document).on('click', '.cornuwab-wp-gpt-rag-chat-fab-button', function() {
                self.toggleWidget();
            });
            
            // Close chat widget (toggle button)
            $(document).on('click', '.cornuwab-wp-gpt-rag-chat-toggle', function() {
                self.toggleWidget();
            });

            // Expand chat widget to modal view
            $(document).on('click', '.cornuwab-wp-gpt-rag-chat-expand', function(e) {
                e.stopPropagation();
                self.toggleExpand(this);
            });
            
            // Close chat when clicking overlay
            $(document).on('click', '.cornuwab-wp-gpt-rag-chat-overlay', function() {
                if (self.isExpanded) {
                    // If expanded, just collapse to small window
                    self.setExpanded(false);
                } else {
                    // If in small window, close the chat entirely
                    self.toggleWidget();
                }
            });
            
            // Handle escape key to close chat
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (self.isExpanded) {
                        // If expanded, just collapse to small window
                        self.setExpanded(false);
                    } else if (self.isOpen) {
                        // If in small window, close the chat entirely
                        self.toggleWidget();
                    }
                }
            });
            
            // Send message
            $(document).on('click', '#cornuwab-wp-gpt-rag-chat-send', function() {
                self.sendMessage();
            });
            
            // Send message on Enter
            $(document).on('keydown', '#cornuwab-wp-gpt-rag-chat-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    self.sendMessage();
                }
            });
            
            // Refresh chat (clear history)
            $(document).on('click', '.cornuwab-wp-gpt-rag-chat-refresh', function(e) {
                e.stopPropagation();
                self.refreshChat();
            });
            
            // Rate response (thumbs up/down)
            $(document).on('click', '.cornuwab-wp-gpt-rag-chat-rate-btn', function(e) {
                e.preventDefault();
                var logId = $(this).data('log-id');
                var rating = $(this).data('rating');
                self.rateResponse(logId, rating, $(this));
            });
        },
        
        initializeWidget: function() {
            // Check if widget exists
            if ($('#cornuwab-wp-gpt-rag-chat-widget').length === 0) {
                return;
            }
            
            // Initialize widget state
            this.isLoading = false;
            this.chatId = null;
            this.turnNumber = 1;
            this.fabMessages = [
                'مرحباً، كيف يمكنني مساعدتك؟',
                'اسألني عن أي شيء في الموقع!',
                'هل تحتاج إلى مساعدة معينة؟',
                'أنا هنا لدعمك في البحث عن المعلومات.',
                'تفضل بكتابة استفسارك وسأجيب فوراً.'
            ];
            this.isExpanded = false;
            
            // Restore state from localStorage
            this.restoreState();
            
            // Show initial state
            this.updateWidgetState();
            this.startFabMessages();
        },
        
        toggleWidget: function() {
            this.isOpen = !this.isOpen;
            this.saveOpenState();
            this.updateWidgetState();
            if (!this.isOpen) {
                this.setExpanded(false);
            }
            
            if (this.isOpen) {
                $('#cornuwab-wp-gpt-rag-chat-input').focus();
                // Scroll to bottom when opening the widget
                setTimeout(function() {
                    this.scrollToBottom();
                }.bind(this), 150);
            }
        },
        
        toggleExpand: function(button) {
            var isExpanded = this.isExpanded = !this.isExpanded;
            this.setExpanded(isExpanded);
            if (button) {
                $(button).attr('aria-expanded', isExpanded);
                var $icon = $(button).find('i');
                if ($icon.length) {
                    $icon.toggleClass('fa-up-right-and-down-left-from-center', !isExpanded);
                    $icon.toggleClass('fa-down-left-and-up-right-to-center', isExpanded);
                }
            }
        },

        setExpanded: function(expanded) {
            var $widget = $('#cornuwab-wp-gpt-rag-chat-widget');
            this.isExpanded = expanded;
            $widget.toggleClass('cornuwab-wp-gpt-rag-chat-expanded', expanded);
            if (!expanded) {
                $('.cornuwab-wp-gpt-rag-chat-expand').attr('aria-expanded', 'false');
                $('.cornuwab-wp-gpt-rag-chat-expand i')
                    .removeClass('fa-down-left-and-up-right-to-center')
                    .addClass('fa-up-right-and-down-left-from-center');
            } else {
                // When expanding, scroll to bottom after a short delay
                setTimeout(function() {
                    this.scrollToBottom();
                }.bind(this), 200);
            }
        },

        startFabMessages: function() {
            var self = this;
            var $bubble = $('.cornuwab-wp-gpt-rag-chat-fab-bubble');
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
            var $widget = $('#cornuwab-wp-gpt-rag-chat-widget');
            
            if (this.isOpen) {
                $widget.addClass('cornuwab-wp-gpt-rag-chat-open');
                // Focus on input when opened
                setTimeout(function() {
                    $('#cornuwab-wp-gpt-rag-chat-input').focus();
                }, 300);
            } else {
                $widget.removeClass('cornuwab-wp-gpt-rag-chat-open');
            }
        },
        
        sendMessage: function() {
            if (this.isLoading) {
                return;
            }
            
            var $input = $('#cornuwab-wp-gpt-rag-chat-input');
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
            
            // Detect language from user message
            var detectedLanguage = this.detectLanguage(message);
            console.log('CORNUWB: Detected language:', detectedLanguage, 'for message:', message.substring(0, 50));
            
            $.ajax({
                url: wpGptRagChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_gpt_rag_chat_query',
                    query: message,
                    chat_id: self.chatId,
                    turn_number: self.turnNumber,
                    detected_language: detectedLanguage,
                    nonce: wpGptRagChat.nonce
                },
                success: function(response) {
                    self.setLoading(false);
                    
                    if (response.success) {
                        // Store chat_id for session continuity
                        self.chatId = response.data.chat_id;
                        
                        // Add assistant message with rating buttons
                        self.addMessage('assistant', response.data.response, true, response.data.log_id);
                        
                        // Increment turn number
                        self.turnNumber++;
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
        
        addMessage: function(role, content, saveToHistory = true, logId = null) {
            var $messages = $('#cornuwab-wp-gpt-rag-chat-messages');
            var messageClass = role === 'user' ? 'cornuwab-wp-gpt-rag-chat-message-user' : 'cornuwab-wp-gpt-rag-chat-message-assistant';
            var timestamp = this.getCurrentTime();
            
            // Format content with links and line breaks
            var formattedContent = this.formatMessage(content);
            
            var messageHtml = '<div class="cornuwab-wp-gpt-rag-chat-message ' + messageClass + '">' +
                '<div class="cornuwab-wp-gpt-rag-chat-message-content">' +
                formattedContent +
                '</div>';
            
            // Add rating buttons for assistant messages
            if (role === 'assistant' && logId) {
                messageHtml += '<div class="cornuwab-wp-gpt-rag-chat-message-rating">' +
                    '<button class="cornuwab-wp-gpt-rag-chat-rate-btn" data-log-id="' + logId + '" data-rating="1" aria-label="Thumbs Up" title="مفيد"><i class="fa-regular fa-thumbs-up"></i></button>' +
                    '<button class="cornuwab-wp-gpt-rag-chat-rate-btn" data-log-id="' + logId + '" data-rating="-1" aria-label="Thumbs Down" title="غير مفيد"><i class="fa-regular fa-thumbs-down"></i></button>' +
                    '</div>';
            }
            
            messageHtml += '<div class="cornuwab-wp-gpt-rag-chat-message-time">' +
                timestamp +
                '</div></div>';
            
            var $message = $(messageHtml);
            $messages.append($message);
            this.scrollToBottom();
            
            // Save to localStorage
            if (saveToHistory) {
                this.saveMessageToHistory(role, content, timestamp, logId);
            }
        },
        
        showError: function(message) {
            var $messages = $('#cornuwab-wp-gpt-rag-chat-messages');
            var $error = $('<div class="cornuwab-wp-gpt-rag-chat-message cornuwab-wp-gpt-rag-chat-message-error">' +
                '<div class="cornuwab-wp-gpt-rag-chat-message-content">' +
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
            var $sendButton = $('#cornuwab-wp-gpt-rag-chat-send');
            var $input = $('#cornuwab-wp-gpt-rag-chat-input');
            
            if (loading) {
                $sendButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $input.prop('disabled', true);
                
                // Add loading message
                var $loading = $('<div class="cornuwab-wp-gpt-rag-chat-message cornuwab-wp-gpt-rag-chat-message-assistant cornuwab-wp-gpt-rag-chat-message-loading">' +
                    '<div class="cornuwab-wp-gpt-rag-chat-message-content">' +
                    '<div class="cornuwab-wp-gpt-rag-chat-typing-indicator">' +
                    '<span></span><span></span><span></span>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
                
                $('#cornuwab-wp-gpt-rag-chat-messages').append($loading);
                this.scrollToBottom();
            } else {
                $sendButton.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
                $input.prop('disabled', false);
                
                // Remove loading message
                $('.cornuwab-wp-gpt-rag-chat-message-loading').remove();
            }
        },
        
        refreshChat: function() {
            var self = this;
            
            // Add confirmation with animation
            var $messages = $('#cornuwab-wp-gpt-rag-chat-messages');
            var $confirm = $('<div class="cornuwab-wp-gpt-rag-chat-message cornuwab-wp-gpt-rag-chat-message-system">' +
                '<div class="cornuwab-wp-gpt-rag-chat-message-content">' +
                'جاري مسح المحادثة...' +
                '</div>' +
                '</div>');
            
            $messages.append($confirm);
            this.scrollToBottom();
            
            setTimeout(function() {
                self.clearChatHistory();
            }, 500);
        },
        
        clearChatHistory: function() {
            var $messages = $('#cornuwab-wp-gpt-rag-chat-messages');
            $messages.empty();
            
            // Add system message
            $messages.append('<div class="cornuwab-wp-gpt-rag-chat-message cornuwab-wp-gpt-rag-chat-message-system">' +
                '<div class="cornuwab-wp-gpt-rag-chat-message-content">' +
                'مرحباً! يمكنني مساعدتك في إيجاد المعلومات من هذا الموقع. كيف يمكنني مساعدتك؟' +
                '</div>' +
                '</div>');
            
            this.conversationHistory = [];
            this.chatId = null; // Reset chat session
            this.turnNumber = 1; // Reset turn counter
            this.saveChatHistory();
        },
        
        saveChatHistory: function() {
            try {
                localStorage.setItem('wp_gpt_rag_chat_history', JSON.stringify(this.conversationHistory));
            } catch (e) {
                console.error('Failed to save chat history:', e);
            }
        },
        
        saveMessageToHistory: function(role, content, timestamp, logId) {
            if (!this.conversationHistory) {
                this.conversationHistory = [];
            }
            
            this.conversationHistory.push({
                role: role,
                content: content,
                timestamp: timestamp,
                logId: logId || null
            });
            
            this.saveChatHistory();
        },
        
        rateResponse: function(logId, rating, $button) {
            var self = this;
            
            // Visual feedback
            $button.addClass('rating-selected');
            // Switch icon from outline to solid
            $button.find('i').removeClass('fa-regular').addClass('fa-solid');
            
            // Reset siblings
            var $siblings = $button.siblings('.cornuwab-wp-gpt-rag-chat-rate-btn');
            $siblings.removeClass('rating-selected');
            $siblings.find('i').removeClass('fa-solid').addClass('fa-regular');
            
            $.ajax({
                url: wpGptRagChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_gpt_rag_chat_rate_response',
                    log_id: logId,
                    rating: rating,
                    nonce: wpGptRagChat.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Show brief confirmation
                        $button.addClass('rating-saved');
                        setTimeout(function() {
                            $button.removeClass('rating-saved');
                        }, 1000);
                    }
                },
                error: function() {
                    // Revert visual feedback on error
                    $button.removeClass('rating-selected');
                }
            });
        },
        
        restoreChatHistory: function() {
            try {
                var saved = localStorage.getItem('wp_gpt_rag_chat_history');
                if (saved) {
                    this.conversationHistory = JSON.parse(saved);
                    
                    // Restore messages to UI
                    var $messages = $('#cornuwab-wp-gpt-rag-chat-messages');
                    $messages.empty();
                    
                    if (this.conversationHistory.length === 0) {
                        // Add default welcome message
                        $messages.append('<div class="cornuwab-wp-gpt-rag-chat-message cornuwab-wp-gpt-rag-chat-message-system">' +
                            '<div class="cornuwab-wp-gpt-rag-chat-message-content">' +
                            'مرحباً! يمكنني مساعدتك في إيجاد المعلومات من هذا الموقع. كيف يمكنني مساعدتك؟' +
                            '</div>' +
                            '</div>');
                    } else {
                        // Restore chat messages
                        for (var i = 0; i < this.conversationHistory.length; i++) {
                            var msg = this.conversationHistory[i];
                            var messageClass = msg.role === 'user' ? 'cornuwab-wp-gpt-rag-chat-message-user' : 'cornuwab-wp-gpt-rag-chat-message-assistant';
                            
                            $messages.append('<div class="cornuwab-wp-gpt-rag-chat-message ' + messageClass + '">' +
                                '<div class="cornuwab-wp-gpt-rag-chat-message-content">' +
                                this.formatMessage(msg.content) +
                                '</div>' +
                                '<div class="cornuwab-wp-gpt-rag-chat-message-time">' +
                                msg.timestamp +
                                '</div>' +
                                '</div>');
                        }
                    }
                    
                    this.scrollToBottom();
                } else {
                    this.conversationHistory = [];
                }
            } catch (e) {
                console.error('Failed to restore chat history:', e);
                this.conversationHistory = [];
            }
        },
        
        saveOpenState: function() {
            try {
                localStorage.setItem('wp_gpt_rag_chat_open', this.isOpen ? '1' : '0');
            } catch (e) {
                console.error('Failed to save open state:', e);
            }
        },
        
        restoreOpenState: function() {
            try {
                var saved = localStorage.getItem('wp_gpt_rag_chat_open');
                this.isOpen = saved === '1';
            } catch (e) {
                console.error('Failed to restore open state:', e);
                this.isOpen = false;
            }
        },
        
        restoreState: function() {
            this.restoreOpenState();
            this.restoreChatHistory();
            
            // If chat is open after restoration, ensure it scrolls to bottom
            if (this.isOpen) {
                // Use setTimeout to ensure DOM is fully rendered
                setTimeout(function() {
                    this.scrollToBottom();
                }.bind(this), 100);
            }
        },
        
        scrollToBottom: function() {
            var $messages = $('#cornuwab-wp-gpt-rag-chat-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        },
        
        getCurrentTime: function() {
            var now = new Date();
            return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
        
        detectLanguage: function(text) {
            // Enhanced Arabic detection: check for Arabic characters and common Arabic words
            var arabicPattern = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
            var arabicWords = ['من', 'هو', 'ما', 'متى', 'أين', 'كيف', 'لماذا', 'هل', 'التي', 'الذي', 'هذه', 'ذلك', 'هذا', 'التي', 'التي', 'التي'];
            
            // Check for Arabic characters
            if (arabicPattern.test(text)) {
                return 'ar';
            }
            
            // Check for common Arabic words (fallback)
            for (var i = 0; i < arabicWords.length; i++) {
                if (text.indexOf(arabicWords[i]) !== -1) {
                    return 'ar';
                }
            }
            
            return 'en';
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
        },
        
        formatMessage: function(text) {
            // First escape HTML to prevent XSS
            var escaped = this.escapeHtml(text);
            
            // Convert separator lines to styled dividers BEFORE processing links
            escaped = escaped.replace(/━━━━━━━━━━━━━━━━/g, '<div class="content-separator"></div>');
            
            // Find and group consecutive links to create a list
            // Look for patterns like: 🔗 [link1](url1) 🔗 [link2](url2) etc.
            var linkPattern = /(🔗\s*\[([^\]]+)\]\(([^)]+)\)(?:\s*🔗\s*\[([^\]]+)\]\(([^)]+)\))*)/g;
            
            escaped = escaped.replace(linkPattern, function(match) {
                // Extract all links from the match
                var links = [];
                var linkRegex = /🔗\s*\[([^\]]+)\]\(([^)]+)\)/g;
                var linkMatch;
                
                while ((linkMatch = linkRegex.exec(match)) !== null) {
                    links.push({
                        text: linkMatch[1],
                        url: linkMatch[2]
                    });
                }
                
                // If we have multiple links, create a list
                if (links.length > 1) {
                    var listHtml = '<ul class="source-links-list">';
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
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        ChatWidget.init();
    });
    
    // Make ChatWidget available globally for debugging
    window.WPGptRagChatWidget = ChatWidget;
    
})(jQuery);

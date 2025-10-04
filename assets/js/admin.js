/**
 * Admin JavaScript for WP GPT RAG Chat
 */
(function($) {
    'use strict';
    
    var AdminInterface = {
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },
        
        bindEvents: function() {
            var self = this;
            
            // Bulk actions
            $(document).on('click', '.bulk-action-button', function() {
                self.handleBulkAction($(this));
            });
            
            // Individual post actions
            $(document).on('click', '.reindex-post', function() {
                self.handleReindexPost($(this));
            });
            
            $(document).on('click', '.toggle-include', function() {
                self.handleToggleInclude($(this));
            });
            
            // Test connection
            $(document).on('click', '#test-connection', function() {
                self.testConnection();
            });
            
            // Settings validation
            $(document).on('input', 'input[name*="wp_gpt_rag_chat_settings"]', function() {
                self.validateSettings();
            });
            
            // Dismiss error notices
            $(document).on('click', '.notice.is-dismissible .notice-dismiss', function() {
                var $notice = $(this).closest('.notice');
                if ($notice.hasClass('settings-error') || $notice.attr('id') === 'settings-validation-errors') {
                    self.dismissError($notice);
                }
            });
            
            // Chunking test
            $(document).on('click', '#test-chunking', function() {
                self.testChunking();
            });
        },
        
        initializeComponents: function() {
            // Initialize tooltips
            this.initTooltips();
            
            // Initialize progress bars
            this.initProgressBars();
            
            // Initialize data tables
            this.initDataTables();
        },
        
        initTooltips: function() {
            $('[data-tooltip]').each(function() {
                var $this = $(this);
                var tooltip = $this.data('tooltip');
                
                $this.attr('title', tooltip);
            });
        },
        
        initProgressBars: function() {
            $('.progress-bar').each(function() {
                var $this = $(this);
                var percentage = $this.data('percentage') || 0;
                
                $this.find('.progress-fill').css('width', percentage + '%');
            });
        },
        
        initDataTables: function() {
            if ($.fn.DataTable) {
                $('.wp-list-table').DataTable({
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: {
                        search: 'Search:',
                        lengthMenu: 'Show _MENU_ entries',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        paginate: {
                            first: 'First',
                            last: 'Last',
                            next: 'Next',
                            previous: 'Previous'
                        }
                    }
                });
            }
        },
        
        handleBulkAction: function($button) {
            var action = $button.data('action');
            var postIds = this.getSelectedPostIds();
            
            if (postIds.length === 0) {
                alert(wpGptRagChatAdmin.strings.selectPosts);
                return;
            }
            
            if (!confirm(wpGptRagChatAdmin.strings.confirmBulkAction)) {
                return;
            }
            
            this.performBulkAction(action, postIds, $button);
        },
        
        handleReindexPost: function($button) {
            var postId = $button.data('post-id');
            var originalText = $button.text();
            
            $button.prop('disabled', true).text(wpGptRagChatAdmin.strings.processing);
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_reindex',
                post_id: postId,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    AdminInterface.showNotice('success', response.data.message);
                    AdminInterface.refreshPostStatus(postId);
                } else {
                    AdminInterface.showNotice('error', response.data.message);
                }
            }).fail(function() {
                AdminInterface.showNotice('error', wpGptRagChatAdmin.strings.error);
            }).always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        },
        
        handleToggleInclude: function($button) {
            var postId = $button.data('post-id');
            var include = $button.data('include');
            var originalText = $button.text();
            
            $button.prop('disabled', true).text(wpGptRagChatAdmin.strings.processing);
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_toggle_include',
                post_id: postId,
                include: include,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    AdminInterface.showNotice('success', wpGptRagChatAdmin.strings.success);
                    AdminInterface.refreshPostStatus(postId);
                } else {
                    AdminInterface.showNotice('error', response.data.message);
                }
            }).fail(function() {
                AdminInterface.showNotice('error', wpGptRagChatAdmin.strings.error);
            }).always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        },
        
        performBulkAction: function(action, postIds, $button) {
            var originalText = $button.text();
            
            $button.prop('disabled', true).text(wpGptRagChatAdmin.strings.processing);
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_bulk_action',
                action_type: action,
                post_ids: postIds,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    AdminInterface.showNotice('success', response.data.message);
                    AdminInterface.refreshPostList();
                } else {
                    AdminInterface.showNotice('error', response.data.message);
                }
            }).fail(function() {
                AdminInterface.showNotice('error', wpGptRagChatAdmin.strings.error);
            }).always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        },
        
        testConnection: function() {
            var $button = $('#test-connection');
            var $result = $('#connection-test-result');
            var originalText = $button.text();
            
            $button.prop('disabled', true).text(wpGptRagChatAdmin.strings.testing);
            $result.hide();
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_test_connection',
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    $result.removeClass('error').addClass('success')
                        .html('<strong>' + wpGptRagChatAdmin.strings.success + '</strong> ' + response.data.message)
                        .show();
                } else {
                    $result.removeClass('success').addClass('error')
                        .html('<strong>' + wpGptRagChatAdmin.strings.error + '</strong> ' + response.data.message)
                        .show();
                }
            }).fail(function() {
                $result.removeClass('success').addClass('error')
                    .html('<strong>' + wpGptRagChatAdmin.strings.error + '</strong> ' + wpGptRagChatAdmin.strings.connectionFailed)
                    .show();
            }).always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        },
        
        validateSettings: function() {
            var $form = $('form[action*="options.php"]');
            var errors = [];
            
            // Validate API keys
            var openaiKey = $('input[name*="openai_api_key"]').val();
            var pineconeKey = $('input[name*="pinecone_api_key"]').val();
            
            if (openaiKey && !this.validateOpenAIKey(openaiKey)) {
                errors.push('Invalid OpenAI API key format');
            }
            
            if (pineconeKey && !this.validatePineconeKey(pineconeKey)) {
                errors.push('Invalid Pinecone API key format');
            }
            
            // Validate numeric fields
            var chunkSize = parseInt($('input[name*="chunk_size"]').val());
            var chunkOverlap = parseInt($('input[name*="chunk_overlap"]').val());
            
            if (chunkSize < 100 || chunkSize > 3000) {
                errors.push('Chunk size must be between 100 and 3000 characters');
            }
            
            if (chunkOverlap < 0 || chunkOverlap >= chunkSize) {
                errors.push('Chunk overlap must be less than chunk size');
            }
            
            // Show validation errors
            this.showValidationErrors(errors);
        },
        
        validateOpenAIKey: function(key) {
            // OpenAI API keys start with 'sk-' and can have various formats:
            // - Legacy: sk-[48 chars] (51 total)
            // - Project: sk-proj-[more chars] (varies)
            // - Organization: sk-[org]-[more chars] (varies)
            return /^sk-[a-zA-Z0-9\-_]{20,}$/.test(key);
        },
        
        validatePineconeKey: function(key) {
            // Pinecone API keys can be in different formats:
            // 1. Legacy UUID format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
            // 2. New format: pckey_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
            // 3. Other formats that might be valid
            
            // Check for legacy UUID format
            if (/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i.test(key)) {
                return true;
            }
            
            // Check for new pckey_ format
            if (/^pckey_[a-zA-Z0-9]{32,}$/.test(key)) {
                return true;
            }
            
            // Check for other potential valid formats (alphanumeric, reasonable length)
            if (/^[a-zA-Z0-9_-]{20,}$/.test(key)) {
                return true;
            }
            
            return false;
        },
        
        showValidationErrors: function(errors) {
            var $errorContainer = $('#settings-validation-errors');
            
            if (errors.length > 0) {
                if ($errorContainer.length === 0) {
                    $errorContainer = $('<div id="settings-validation-errors" class="notice notice-error is-dismissible"><p></p></div>');
                    $('form[action*="options.php"]').before($errorContainer);
                }
                
                $errorContainer.find('p').html('<strong>Settings Validation Errors:</strong><br>' + errors.join('<br>'));
                $errorContainer.show();
            } else {
                $errorContainer.hide();
            }
        },
        
        dismissError: function($errorElement) {
            var $this = this;
            var nonce = $('input[name="wp_gpt_rag_chat_settings_nonce"]').val();
            
            $.ajax({
                url: wpGptRagChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_gpt_rag_chat_dismiss_error',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $errorElement.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                },
                error: function() {
                    // Even if AJAX fails, still hide the error locally
                    $errorElement.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
        },
        
        testChunking: function() {
            var $button = $('#test-chunking');
            var $result = $('#chunking-test-result');
            var originalText = $button.text();
            
            $button.prop('disabled', true).text(wpGptRagChatAdmin.strings.testing);
            $result.hide();
            
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_test_chunking',
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<h4>Chunking Test Results</h4>';
                    html += '<p><strong>Original Length:</strong> ' + data.original_length + ' characters</p>';
                    html += '<p><strong>Number of Chunks:</strong> ' + data.chunk_count + '</p>';
                    html += '<p><strong>Chunk Size:</strong> ' + data.settings.chunk_size + ' characters</p>';
                    html += '<p><strong>Chunk Overlap:</strong> ' + data.settings.chunk_overlap + ' characters</p>';
                    html += '<h5>Chunks:</h5>';
                    html += '<ol>';
                    data.chunks.forEach(function(chunk, index) {
                        html += '<li>' + chunk.substring(0, 100) + (chunk.length > 100 ? '...' : '') + '</li>';
                    });
                    html += '</ol>';
                    
                    $result.removeClass('error').addClass('success').html(html).show();
                } else {
                    $result.removeClass('success').addClass('error')
                        .html('<strong>' + wpGptRagChatAdmin.strings.error + '</strong> ' + response.data.message)
                        .show();
                }
            }).fail(function() {
                $result.removeClass('success').addClass('error')
                    .html('<strong>' + wpGptRagChatAdmin.strings.error + '</strong> ' + wpGptRagChatAdmin.strings.testFailed)
                    .show();
            }).always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        },
        
        getSelectedPostIds: function() {
            var postIds = [];
            $('input[name="post[]"]:checked').each(function() {
                postIds.push($(this).val());
            });
            return postIds;
        },
        
        refreshPostStatus: function(postId) {
            $.post(wpGptRagChatAdmin.ajaxUrl, {
                action: 'wp_gpt_rag_chat_get_post_status',
                post_id: postId,
                nonce: wpGptRagChatAdmin.nonce
            }, function(response) {
                if (response.success) {
                    AdminInterface.updatePostStatusDisplay(postId, response.data);
                }
            });
        },
        
        updatePostStatusDisplay: function(postId, data) {
            var $row = $('tr[data-post-id="' + postId + '"]');
            
            if ($row.length) {
                $row.find('.vector-count').text(data.vector_count);
                $row.find('.last-updated').text(data.last_updated || 'Never');
                
                var $status = $row.find('.status-indicator span');
                if (data.vector_count > 0) {
                    $status.removeClass('status-not-indexed').addClass('status-indexed')
                        .text('✓ Indexed');
                } else {
                    $status.removeClass('status-indexed').addClass('status-not-indexed')
                        .text('⚠ Not Indexed');
                }
            }
        },
        
        refreshPostList: function() {
            location.reload();
        },
        
        showNotice: function(type, message) {
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        AdminInterface.init();
    });
    
    // Make AdminInterface available globally for debugging
    window.WPGptRagChatAdmin = AdminInterface;
    
})(jQuery);

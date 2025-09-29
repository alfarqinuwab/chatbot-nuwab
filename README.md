# WP GPT RAG Chat

A production-ready WordPress plugin that delivers OpenAI + Pinecone retrieval-augmented chatbot over WordPress content (Posts, Pages, Custom Post Types).

## Features

### 🔧 Core Functionality
- **RAG Indexing**: Idempotent & change-only indexing of WordPress content
- **Smart Chunking**: Automatic content chunking with configurable overlap
- **Vector Search**: Pinecone integration for semantic search
- **AI Chat**: OpenAI GPT integration for intelligent responses
- **Real-time Chat**: jQuery-based frontend chat interface

### 📊 Content Management
- **Per-post Controls**: Include/Exclude/Force Reindex metabox
- **Bulk Actions**: Mass operations on posts and pages
- **Custom Post Types**: Support for all public post types including "manuals"
- **Content Hashing**: Idempotent indexing with content change detection

### 🔒 Security & Privacy
- **Nonce Protection**: All AJAX requests protected with WordPress nonces
- **Capability Checks**: Proper user permission validation
- **Data Sanitization**: Input sanitization and output escaping
- **IP Anonymization**: Optional IP address anonymization for privacy
- **GDPR Compliance**: Data export/erasure support
- **Consent Management**: Configurable privacy consent requirements

### ⚡ Performance
- **Background Indexing**: WP-Cron for non-blocking content indexing
- **Transient Caching**: Intelligent caching of API responses
- **Batch Processing**: Efficient bulk operations
- **Log Retention**: Configurable log cleanup

### 🌐 Internationalization
- **Text Domain**: `wp-gpt-rag-chat`
- **Translation Ready**: All user-facing strings translatable
- **RTL Support**: Right-to-left language support

## Requirements

- **WordPress**: 6.6 or higher
- **PHP**: 7.4 or higher
- **OpenAI API Key**: For embeddings and chat generation
- **Pinecone Account**: For vector storage and retrieval

## Installation

1. Upload the plugin files to `/wp-content/plugins/wp-gpt-rag-chat/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure your OpenAI and Pinecone API keys in the settings
4. Index your content using the Indexing page
5. Add the chat widget to your posts/pages using the shortcode `[wp_gpt_rag_chat]`

## Configuration

### OpenAI Settings
- **API Key**: Your OpenAI API key
- **Embedding Model**: Choose from text-embedding-3-large, text-embedding-3-small, or text-embedding-ada-002
- **GPT Model**: Choose from GPT-4, GPT-4 Turbo, or GPT-3.5 Turbo
- **Max Tokens**: Maximum tokens for responses (100-4000)
- **Temperature**: Response creativity (0-2)

### Pinecone Settings
- **API Key**: Your Pinecone API key
- **Environment**: Your Pinecone environment (e.g., us-west1-gcp)
- **Index Name**: Your Pinecone index name
- **Top K Results**: Number of similar vectors to retrieve (1-20)
- **Similarity Threshold**: Minimum similarity score (0-1)

### Chunking Settings
- **Chunk Size**: Characters per chunk (500-2000)
- **Chunk Overlap**: Overlap between chunks (50-500)

### Privacy Settings
- **Log Retention**: Days to keep logs (1-365)
- **Anonymize IPs**: Enable IP address anonymization
- **Require Consent**: Require privacy consent for chat usage

## Usage

### Shortcodes

```php
// Basic chat widget
[wp_gpt_rag_chat]

// Disable chat for specific content
[wp_gpt_rag_chat enabled="0"]
```

### Hooks and Filters

```php
// Add custom post types to indexing
add_filter('wp_gpt_rag_chat_enabled_post_types', function($post_types) {
    $post_types[] = 'my_custom_post_type';
    return $post_types;
});

// Include custom fields in content
add_filter('wp_gpt_rag_chat_include_fields', function($fields) {
    $fields[] = 'my_custom_field';
    return $fields;
});

// Customize system message
add_filter('wp_gpt_rag_chat_system_message', function($message, $context) {
    return "You are a helpful assistant for " . get_bloginfo('name') . ". " . $message;
}, 10, 2);
```

### Programmatic Usage

```php
// Index a specific post
$indexing = new WP_GPT_RAG_Chat\Indexing();
$result = $indexing->index_post($post_id);

// Process a chat query
$chat = new WP_GPT_RAG_Chat\Chat();
$response = $chat->process_query('What is this website about?');

// Get indexing statistics
$stats = WP_GPT_RAG_Chat\Admin::get_indexing_stats();
```

## API Reference

### Classes

- **`WP_GPT_RAG_Chat\Plugin`**: Main plugin class
- **`WP_GPT_RAG_Chat\Settings`**: Settings management
- **`WP_GPT_RAG_Chat\Admin`**: Admin functionality
- **`WP_GPT_RAG_Chat\Metabox`**: Post metabox functionality
- **`WP_GPT_RAG_Chat\Chunking`**: Content chunking
- **`WP_GPT_RAG_Chat\OpenAI`**: OpenAI API integration
- **`WP_GPT_RAG_Chat\Pinecone`**: Pinecone API integration
- **`WP_GPT_RAG_Chat\Indexing`**: Indexing system
- **`WP_GPT_RAG_Chat\Chat`**: Chat functionality
- **`WP_GPT_RAG_Chat\Privacy`**: Privacy and compliance
- **`WP_GPT_RAG_Chat\Logger`**: Logging system

### Database Tables

- **`wp_gpt_rag_chat_logs`**: Chat interaction logs
- **`wp_gpt_rag_chat_vectors`**: Local vector metadata

## Troubleshooting

### Common Issues

1. **"OpenAI API key is not configured"**
   - Go to Settings → GPT RAG Chat → Settings
   - Enter your OpenAI API key

2. **"Pinecone API key is not configured"**
   - Go to Settings → GPT RAG Chat → Settings
   - Enter your Pinecone API key and environment

3. **"No content indexed"**
   - Go to Settings → GPT RAG Chat → Indexing
   - Click "Start Full Indexing"
   - Ensure posts are marked for inclusion in the metabox

4. **Chat widget not appearing**
   - Check if the shortcode `[wp_gpt_rag_chat]` is added to your content
   - Verify the post type is enabled for chat
   - Check browser console for JavaScript errors

### Debug Mode

Enable WordPress debug mode to see detailed error logs:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Privacy & Compliance

### Data Collection
- User queries and AI responses are logged for quality improvement
- IP addresses may be logged (can be anonymized)
- Data is processed by third-party services (OpenAI, Pinecone)

### GDPR Compliance
- Users can request data export
- Users can request data deletion
- Configurable data retention periods
- Privacy consent management

### Data Security
- All API keys are stored securely
- User data is sanitized and escaped
- Nonce protection on all AJAX requests
- Capability checks for all operations

## Performance Optimization

### Recommendations
- Use appropriate chunk sizes (1400-1600 characters)
- Set reasonable similarity thresholds (0.7-0.8)
- Enable log cleanup for better performance
- Use background indexing for large sites
- Consider using a CDN for static assets

### Monitoring
- Check the Logs page for usage statistics
- Monitor API usage in OpenAI and Pinecone dashboards
- Use the Indexing page to track content coverage

## Support

For support, feature requests, or bug reports, please contact the plugin developer or create an issue in the plugin repository.

## Changelog

### Version 1.0.0
- Initial release
- OpenAI and Pinecone integration
- RAG indexing system
- Chat interface
- Privacy and compliance features
- Admin dashboard
- Bulk operations
- Logging and analytics

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- OpenAI for the GPT and embedding models
- Pinecone for vector database services
- WordPress community for the platform

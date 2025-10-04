# Nuwab AI Assistant - WordPress Plugin

A production-ready WordPress plugin that delivers an OpenAI + Pinecone powered RAG (Retrieval Augmented Generation) chatbot for your WordPress content.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)

---

## 🌟 Features

### Core Functionality
- **🤖 AI-Powered Chat**: Intelligent chatbot using OpenAI GPT-4 with RAG architecture
- **🔍 Vector Search**: Pinecone integration for semantic content search
- **📚 Content Indexing**: Automatic indexing of WordPress posts, pages, and custom post types
- **📄 PDF Support**: Index and search PDF attachments
- **🌐 Multilingual**: Automatic language detection (Arabic/English) with appropriate responses
- **🎯 Smart Context**: Retrieval-augmented generation for accurate, context-aware answers

### Admin Features
- **📊 Analytics Dashboard**: Track conversations, API usage, and user interactions
- **🔧 Settings Management**: Easy configuration for OpenAI and Pinecone APIs
- **📈 Real-time Progress**: Live indexing progress with batch processing
- **🎛️ Bulk Operations**: Sync all content or specific post types
- **🔗 Manual Source Linking**: Link specific sources to questions for improved accuracy
- **⚡ Emergency Stop**: Pause indexing operations when needed
- **📉 Content Gap Analysis**: Identify questions the AI couldn't answer

### Advanced Features
- **🔄 Persistent Indexing**: Background processing with WP-Cron
- **🎨 Customizable UI**: Beautiful, responsive chat widget
- **🔒 Privacy Controls**: GDPR-compliant with IP anonymization and PII masking
- **📝 Conversation Logging**: Track all interactions for improvement
- **⭐ Rating System**: Users can rate responses (thumbs up/down)
- **🏷️ Custom Tagging**: Organize conversations with tags
- **🔄 Auto-Retry**: Intelligent error handling with exponential backoff
- **💾 Database Optimization**: Efficient storage with content hashing

---

## 📋 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **OpenAI API Key**: [Get one here](https://platform.openai.com/api-keys)
- **Pinecone API Key**: [Get one here](https://www.pinecone.io/)

---

## 🚀 Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin as a ZIP file
2. Go to **WordPress Admin → Plugins → Add New**
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now**
5. Click **Activate Plugin**

### Method 2: Manual Installation

1. Clone this repository or download as ZIP:
   ```bash
   git clone https://github.com/yourusername/wp-nuwab-chatgpt.git
   ```

2. Upload the `wp-nuwab-chatgpt` folder to your WordPress `wp-content/plugins/` directory

3. Go to **WordPress Admin → Plugins**

4. Find **Nuwab AI Assistant** and click **Activate**

### Method 3: Git Clone Directly

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/yourusername/wp-nuwab-chatgpt.git
```

Then activate via WordPress Admin.

---

## ⚙️ Configuration

### 1. Database Tables

**✅ Automatic Setup**: All database tables are created automatically when you activate the plugin!

The plugin creates these tables:
- `wp_gpt_rag_chat_logs` - Conversation logs
- `wp_gpt_rag_chat_vectors` - Vector embeddings and content
- `wp_gpt_rag_chat_api_usage` - API usage tracking
- `wp_gpt_rag_sitemap_urls` - Sitemap URLs for fallback
- `wp_gpt_rag_content_gaps` - Content gap analysis

**No manual database setup required!** Just activate the plugin and you're ready to go.

### 2. API Keys Setup

1. Go to **WordPress Admin → Nuwab AI Assistant → Settings**

2. **OpenAI Configuration**:
   - Enter your OpenAI API Key
   - Select model (default: `gpt-4`)
   - Configure max tokens, temperature, etc.

3. **Pinecone Configuration**:
   - Enter your Pinecone API Key
   - Enter Pinecone Host URL (e.g., `https://your-index-xxxxx.svc.pinecone.io`)
   - Enter Index Name
   - Select embedding dimensions (1536 or 3072)

4. Click **Save Settings**

### 3. Index Your Content

1. Go to **Nuwab AI Assistant → Indexing**

2. Choose indexing method:
   - **Sync All**: Index all published content
   - **Sync by Post Type**: Index specific post types
   - **Sync Single Post**: Index individual posts

3. Click **Sync All** to start indexing

4. Monitor progress in real-time with the progress bar

---

## 📖 Usage

### Frontend Chat Widget

The chat widget automatically appears on your website. Users can:
- Ask questions about your content
- Get AI-powered responses with source links
- Rate responses (thumbs up/down)
- View conversation history

### Admin Dashboard

**Analytics** (`Nuwab AI Assistant → Analytics`):
- View all conversations
- Track API usage and costs
- Analyze content gaps
- Link manual sources to questions

**Indexing** (`Nuwab AI Assistant → Indexing`):
- View indexed content statistics
- Manage indexed items
- Bulk sync operations
- Generate XML sitemaps

**Settings** (`Nuwab AI Assistant → Settings`):
- Configure API keys
- Adjust AI parameters
- Set privacy options
- Customize chat behavior

---

## 🔧 Advanced Configuration

### Embedding Dimensions

Choose based on your needs:
- **1536 dimensions**: Faster, lower cost, good for most use cases
- **3072 dimensions**: Higher accuracy, better for complex content

### Chunking Settings

- **Chunk Size**: Default 1400 characters
- **Chunk Overlap**: Default 150 characters
- Adjust based on your content structure

### Response Settings

- **Top K**: Number of similar chunks to retrieve (default: 5)
- **Similarity Threshold**: Minimum similarity score (default: 0.7)
- **Temperature**: AI creativity (0.0-1.0, default: 0.7)

---

## 🛠️ Troubleshooting

### Plugin Activation Issues

If tables aren't created:
1. Deactivate the plugin
2. Reactivate it
3. Check error logs in `wp-content/debug.log`

### Indexing Errors

**500 Internal Server Error**:
- Plugin automatically reduces batch size
- Check PHP memory limit (recommended: 512M)
- Check PHP max execution time (recommended: 300s)

**Emergency Stop Active**:
- Click "Resume Indexing" to continue
- Click "Confirm Stop" to permanently stop

### Chat Not Responding

1. Verify API keys are correct
2. Check API usage limits
3. Ensure content is indexed
4. Check browser console for errors

### Database Issues

Run the migration script:
```bash
wp-admin/admin.php?page=wp-gpt-rag-chat-settings
```

Or check database health:
```php
WP_GPT_RAG_Chat\Migration::check_database_health();
```

---

## 📊 Database Schema

### Core Tables

**wp_gpt_rag_chat_logs**
- Stores all conversation logs
- Tracks user queries and AI responses
- Includes ratings, sources, and metadata

**wp_gpt_rag_chat_vectors**
- Stores vector IDs and content hashes
- Links to WordPress posts
- Includes actual chunk content for retrieval

**wp_gpt_rag_chat_api_usage**
- Tracks OpenAI and Pinecone API calls
- Records tokens used and costs
- Helps monitor usage limits

---

## 🔒 Privacy & Security

### GDPR Compliance

- **IP Anonymization**: Optional IP address masking
- **PII Masking**: Automatic detection and masking of personal data
- **Consent Management**: Require user consent before chat
- **Data Retention**: Configurable log retention period
- **Right to Deletion**: Easy data cleanup on uninstall

### Security Features

- **Nonce Verification**: All AJAX requests protected
- **Capability Checks**: Role-based access control
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: All output properly escaped
- **API Key Encryption**: Secure storage of credentials

---

## 🎨 Customization

### Custom Prompts

Edit system prompts in `includes/class-openai.php`:
```php
$system_prompt = "You are an official AI assistant...";
```

### Chat Widget Styling

Customize CSS in `assets/css/frontend.css`:
```css
.wp-gpt-rag-chat-widget {
    /* Your custom styles */
}
```

### Hooks & Filters

```php
// Modify AI response before sending
add_filter('wp_gpt_rag_chat_response', function($response, $query) {
    // Your modifications
    return $response;
}, 10, 2);

// Customize indexing behavior
add_action('wp_gpt_rag_chat_before_index', function($post_id) {
    // Your custom logic
});
```

---

## 📝 Changelog

### Version 1.0.0 (2025-01-XX)

**Initial Release**
- ✅ OpenAI GPT-4 integration
- ✅ Pinecone vector database integration
- ✅ RAG architecture implementation
- ✅ Multilingual support (Arabic/English)
- ✅ Admin dashboard with analytics
- ✅ Real-time indexing with progress tracking
- ✅ PDF support
- ✅ Manual source linking
- ✅ API usage tracking
- ✅ GDPR compliance features
- ✅ Persistent background indexing
- ✅ Emergency stop functionality
- ✅ Content gap analysis
- ✅ Automatic database table creation

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## 👨‍💻 Author

**Nuwab**
- Website: [https://nuwab.com](https://nuwab.com)
- GitHub: [@yourusername](https://github.com/yourusername)

---

## 🙏 Acknowledgments

- OpenAI for GPT-4 API
- Pinecone for vector database
- WordPress community for excellent documentation
- All contributors and testers

---

## 📞 Support

- **Documentation**: [Plugin Wiki](https://github.com/yourusername/wp-nuwab-chatgpt/wiki)
- **Issues**: [GitHub Issues](https://github.com/yourusername/wp-nuwab-chatgpt/issues)
- **Email**: support@nuwab.com

---

## 🌟 Show Your Support

If you find this plugin helpful, please:
- ⭐ Star this repository
- 🐛 Report bugs
- 💡 Suggest new features
- 📢 Share with others

---

**Made with ❤️ for the WordPress community**
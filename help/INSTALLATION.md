# 🚀 Quick Installation Guide

## For New Installation on Another PC

### Step 1: Prerequisites

Before installing, make sure you have:
- ✅ WordPress 5.0 or higher installed
- ✅ PHP 7.4 or higher
- ✅ OpenAI API Key ([Get one here](https://platform.openai.com/api-keys))
- ✅ Pinecone API Key ([Get one here](https://www.pinecone.io/))
- ✅ Pinecone Index created with dimensions: **1536** or **3072**

---

### Step 2: Upload Plugin Files

#### Option A: Via Git Clone (Recommended)

```bash
# Navigate to your WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Clone the repository
git clone https://github.com/yourusername/wp-nuwab-chatgpt.git

# Or if you already have it locally, copy the folder
cp -r /path/to/local/wp-nuwab-chatgpt /path/to/wordpress/wp-content/plugins/
```

#### Option B: Via WordPress Admin

1. Zip the plugin folder on your current PC:
   ```bash
   cd wp-content/plugins/
   zip -r wp-nuwab-chatgpt.zip wp-nuwab-chatgpt/
   ```

2. On the new PC:
   - Go to **WordPress Admin → Plugins → Add New**
   - Click **Upload Plugin**
   - Select the ZIP file
   - Click **Install Now**

#### Option C: Via FTP/SFTP

1. Connect to your server via FTP/SFTP
2. Navigate to `wp-content/plugins/`
3. Upload the entire `wp-nuwab-chatgpt` folder

---

### Step 3: Activate Plugin

1. Go to **WordPress Admin → Plugins**
2. Find **Nuwab AI Assistant**
3. Click **Activate**

**✅ IMPORTANT**: When you activate the plugin, it will **automatically**:
- Create all required database tables
- Set up default settings
- Schedule background tasks

**No manual database setup needed!**

---

### Step 4: Verify Database Tables

After activation, these tables should be created automatically:

- `wp_gpt_rag_chat_logs`
- `wp_gpt_rag_chat_vectors`
- `wp_gpt_rag_chat_api_usage`
- `wp_gpt_rag_sitemap_urls`
- `wp_gpt_rag_content_gaps`

To verify:
```sql
SHOW TABLES LIKE 'wp_gpt_rag_chat%';
```

Or check in **phpMyAdmin** → Your Database → Tables

---

### Step 5: Configure API Keys

1. Go to **WordPress Admin → Nuwab AI Assistant → Settings**

2. **OpenAI Configuration**:
   ```
   API Key: sk-proj-xxxxxxxxxxxxxxxxxxxxx
   Model: gpt-4 (or gpt-4-turbo)
   Max Tokens: 1000
   Temperature: 0.7
   ```

3. **Pinecone Configuration**:
   ```
   API Key: pckey-xxxxxxxxxxxxxxxxxxxxx
   Host: https://your-index-xxxxx.svc.pinecone.io
   Index Name: your-index-name
   Embedding Dimensions: 1536 or 3072
   ```

4. Click **Save Settings**

---

### Step 6: Index Your Content

1. Go to **Nuwab AI Assistant → Indexing**

2. Click **Sync All** to index all content

3. Wait for the indexing to complete (progress bar shows real-time status)

4. Verify indexed items in the table below

---

### Step 7: Test the Chat

1. Visit your website frontend
2. Look for the chat widget (usually bottom-right corner)
3. Ask a question about your content
4. Verify you get a relevant response

**Test Query Examples**:
- "What topics can you help with?"
- "Tell me about [your content topic]"
- Arabic: "ما هي المواضيع المتوفرة؟"

---

## 🔧 Troubleshooting

### Issue: Tables Not Created

**Solution**:
1. Deactivate the plugin
2. Reactivate it
3. Check WordPress debug log: `wp-content/debug.log`

Enable debugging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Issue: "Invalid API Key Format"

**Solution**:
- OpenAI keys start with: `sk-proj-` or `sk-`
- Pinecone keys start with: `pckey-` or are UUID format
- Check for extra spaces or line breaks

### Issue: Indexing Fails with 500 Error

**Solution**:
1. Increase PHP memory limit in `wp-config.php`:
   ```php
   define('WP_MEMORY_LIMIT', '512M');
   ```

2. Increase max execution time in `.htaccess`:
   ```apache
   php_value max_execution_time 300
   ```

### Issue: Chat Widget Not Showing

**Solution**:
1. Check if plugin is activated
2. Clear browser cache
3. Check browser console for JavaScript errors
4. Verify no theme conflicts

---

## 📊 Database Migration (If Needed)

If you're moving from an old installation and tables are missing columns:

1. Go to **Nuwab AI Assistant → Settings**
2. The plugin will auto-detect and run migrations
3. Or manually run:
   ```php
   WP_GPT_RAG_Chat\Migration::run_migrations();
   ```

---

## 🔄 Updating the Plugin

### Via Git Pull

```bash
cd wp-content/plugins/wp-nuwab-chatgpt/
git pull origin main
```

### Via WordPress Admin

1. Deactivate the plugin
2. Delete the old version
3. Upload the new version
4. Reactivate (migrations run automatically)

---

## 📦 What Gets Installed

### Files Structure
```
wp-nuwab-chatgpt/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── includes/
│   ├── class-*.php
│   └── Plugin.php
├── templates/
│   ├── settings-page.php
│   ├── indexing-page.php
│   └── analytics-page.php
├── languages/
├── README.md
├── INSTALLATION.md
└── wp-gpt-rag-chat.php
```

### Database Tables
```
wp_gpt_rag_chat_logs          (Conversation logs)
wp_gpt_rag_chat_vectors       (Vector embeddings)
wp_gpt_rag_chat_api_usage     (API tracking)
wp_gpt_rag_sitemap_urls       (Sitemap data)
wp_gpt_rag_content_gaps       (Content analysis)
```

### WordPress Options
```
wp_gpt_rag_chat_settings      (Plugin settings)
wp_gpt_rag_chat_version       (Version tracking)
```

---

## ✅ Post-Installation Checklist

- [ ] Plugin activated successfully
- [ ] All database tables created
- [ ] OpenAI API key configured and validated
- [ ] Pinecone API key configured and validated
- [ ] Content indexed (at least 1 post)
- [ ] Chat widget appears on frontend
- [ ] Test query returns relevant response
- [ ] Admin dashboard accessible
- [ ] No PHP errors in debug log

---

## 🆘 Need Help?

- **Documentation**: Check `README.md`
- **GitHub Issues**: [Report a bug](https://github.com/yourusername/wp-nuwab-chatgpt/issues)
- **Email Support**: support@nuwab.com

---

**Installation should take 5-10 minutes. Indexing time depends on content volume.**

**Happy chatting! 🎉**

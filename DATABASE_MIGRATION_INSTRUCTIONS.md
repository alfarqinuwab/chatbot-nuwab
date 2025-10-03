# Database Migration Instructions

## Issue
You're seeing this error:
```
WordPress database error: [Table 'nuwab.wp_wp_gpt_rag_chat_api_usage' doesn't exist]
```

This happens because the new Error Logs and API Usage tracking tables haven't been created yet.

## Solution Options

### Option 1: Automatic Migration (Recommended)
The migration should run automatically when you visit the Analytics page. If it doesn't work, try:

1. Go to **WordPress Admin → GPT RAG Chat → Analytics & Logs**
2. The migration should run automatically
3. If you see any errors, try refreshing the page

### Option 2: Manual Migration Script
If the automatic migration doesn't work:

1. Navigate to: `wp-content/plugins/wp-nuwab-chatgpt/run-migration.php`
2. Open it in your browser: `https://yoursite.com/wp-content/plugins/wp-nuwab-chatgpt/run-migration.php`
3. Follow the instructions on the page
4. Delete the `run-migration.php` file after successful migration

### Option 3: Direct SQL Execution
If you have database access:

1. Open your database management tool (phpMyAdmin, etc.)
2. Run the SQL script: `create-analytics-tables.sql`
3. Or copy and paste the SQL commands from that file

### Option 4: WordPress CLI (if available)
```bash
wp option update wp_gpt_rag_chat_db_version 2.2.0
```

## What Gets Created

The migration creates two new tables:

1. **`wp_wp_gpt_rag_chat_errors`** - Stores error logs for API failures and invalid responses
2. **`wp_wp_gpt_rag_chat_api_usage`** - Tracks API usage for OpenAI and Pinecone

## Verification

After running the migration:

1. Go to **WordPress Admin → GPT RAG Chat → Analytics & Logs**
2. You should see 4 tabs: Logs, Dashboard, Error Logs, API Usage
3. The Error Logs and API Usage tabs should work without errors

## Troubleshooting

If you still see errors:

1. Check that your database user has CREATE TABLE permissions
2. Ensure the WordPress database prefix is correct (usually `wp_`)
3. Check the WordPress error logs for more details
4. Try deactivating and reactivating the plugin

## Support

If none of these solutions work, please check:
- WordPress error logs
- Database permissions
- Plugin file permissions
- PHP error logs

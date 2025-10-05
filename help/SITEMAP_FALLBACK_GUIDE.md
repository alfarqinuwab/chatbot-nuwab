# Sitemap Fallback Feature Guide

## Overview
The Sitemap Fallback feature provides intelligent page suggestions when the RAG system cannot find relevant answers in the knowledge base. Instead of returning a generic "no answer" message, the chatbot searches your sitemap and suggests the most relevant pages for users to visit.

## How It Works

1. **User asks a question** → RAG searches Pinecone knowledge base
2. **No relevant content found** → Sitemap fallback activates
3. **Semantic search** → Searches indexed sitemap URLs using AI embeddings
4. **Returns suggestions** → Shows 3-5 most relevant pages with titles, descriptions, and links

## Setup Instructions

### Step 1: Configure Settings

1. Go to **WP GPT RAG Chat** → **Settings**
2. Navigate to the **General** tab → **Chatbot Behavior** section
3. Scroll down to **Sitemap Fallback Suggestions**
4. Configure:
   - ✅ **Enable Sitemap Fallback**: Check to enable
   - **Sitemap URL**: Enter your sitemap URL (e.g., `sitemap.xml` or full URL)
   - **Number of Suggestions**: Choose how many page suggestions to show (1-10, default: 5)
5. Click **Save Changes**

### Step 2: Index Your Sitemap

1. Go to **WP GPT RAG Chat** → **Diagnostics**
2. Scroll to the **🗺️ Sitemap Fallback Index** section
3. Click **Index Sitemap Now**
4. Wait for indexing to complete (may take a few minutes depending on sitemap size)
5. Verify the "Indexed URLs" count

### Step 3: Test the Feature

1. Ask the chatbot a question that is NOT in your indexed content
2. The chatbot should respond with relevant page suggestions
3. Example response:
   ```
   عذراً، لم أجد معلومات كافية للإجابة على سؤالك في قاعدة المعرفة. 
   ولكن قد تجد ما تبحث عنه في الصفحات التالية:
   
   1. **Page Title**
      Page description
      https://yoursite.com/page-url
   
   2. **Another Page**
      Another description
      https://yoursite.com/another-page
   ```

## Features

### Semantic Search
- Uses OpenAI embeddings to understand query meaning
- Matches user questions to page titles and descriptions
- Returns most relevant pages, not just keyword matches

### Automatic Updates
- Re-index your sitemap anytime content changes
- Clear and rebuild index with one click
- Tracks last indexing date and URL count

### Fallback Hierarchy
1. **Primary**: RAG knowledge base (Pinecone)
2. **Secondary**: Sitemap fallback (when RAG finds nothing)
3. **Tertiary**: Generic "no answer" message (if sitemap disabled or no matches)

## Technical Details

### Database Table
New table: `wp_gpt_rag_sitemap_urls`
Stores:
- URL, title, description, content snippet
- OpenAI embedding vector for semantic search
- Priority and last modified date from sitemap
- WordPress post_id (if URL is internal)

### Sitemap Support
- ✅ XML sitemaps (standard format)
- ✅ Sitemap indexes (multiple sitemaps)
- ✅ Local and remote URLs
- ✅ WordPress native sitemaps
- ✅ SEO plugin sitemaps (Yoast, Rank Math, etc.)

### Performance
- Embeddings created once during indexing
- Fast vector similarity search
- Cached results for common queries
- Minimal impact on chat response time

## Maintenance

### Re-indexing
Re-index your sitemap when:
- You publish new pages/posts
- You update page titles or descriptions
- Your sitemap structure changes

### Clearing Index
Clear the index to:
- Fix indexing errors
- Remove old/deleted pages
- Start fresh with new sitemap

## Settings Reference

| Setting | Description | Default |
|---------|-------------|---------|
| `enable_sitemap_fallback` | Enable/disable the feature | `true` |
| `sitemap_url` | Path or URL to your sitemap | `sitemap.xml` |
| `sitemap_suggestions_count` | Number of suggestions to show | `5` |

## Troubleshooting

### No Suggestions Shown
**Problem**: Fallback returns generic message  
**Solutions**:
1. Check that sitemap fallback is enabled in Settings
2. Verify sitemap URL is correct
3. Index or re-index the sitemap
4. Check Diagnostics page for errors

### Irrelevant Suggestions
**Problem**: Suggested pages don't match the question  
**Solutions**:
1. Improve page titles and descriptions in your content
2. Re-index sitemap after updating
3. Adjust `sitemap_suggestions_count` to show fewer results

### Indexing Fails
**Problem**: Error when indexing sitemap  
**Solutions**:
1. Verify sitemap URL is accessible
2. Check that OpenAI API key is valid
3. View error log in Diagnostics page
4. Try with full URL instead of relative path

## Best Practices

1. **Use descriptive page titles** - AI matches based on titles
2. **Add meta descriptions** - Provides context for matching
3. **Re-index regularly** - Weekly or after major content updates
4. **Monitor suggestions** - Check analytics to see which pages are suggested
5. **Combine with RAG** - Index important pages in Pinecone for better answers

## Related Features

- **RAG Improvements**: Query expansion, re-ranking, few-shot learning
- **Content Gaps**: Tracks unanswered questions
- **Analytics**: Monitor when fallback is triggered

## Support

For issues or questions:
1. Check the **Diagnostics** page for system status
2. Review error logs in the Diagnostics section
3. Verify all settings are configured correctly
4. Test with different queries and compare results

---

**Feature Status**: ✅ Implemented and ready to use  
**Version**: 1.0  
**Last Updated**: October 2, 2025


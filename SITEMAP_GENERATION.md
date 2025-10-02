# Sitemap Generation & Content Indexing to Pinecone

## Overview
This feature allows you to generate an XML sitemap of all your WordPress content and ensure everything is indexed in Pinecone for your RAG chatbot.

## Features

### 1. Generate XML Sitemap
- Creates a standards-compliant XML sitemap (sitemap.org format)
- Includes all published pages, posts, and custom post types
- Automatically calculates:
  - Last modified dates
  - Priority values (homepage: 1.0, pages: 0.8, posts: 0.6)
  - Change frequency (posts: weekly, pages: monthly)
- Option to filter by specific post types
- Downloads as a file with timestamp in name

### 2. View All Indexable Content
- Lists all content that can be indexed
- Shows which items are already indexed in Pinecone vs. not indexed
- Displays:
  - Post ID
  - Title
  - Post Type
  - Status (publish/private)
  - Indexing status (indexed or not)
  - Last modified date
  - Link to view content
- Summary statistics:
  - Total items
  - Indexed count
  - Unindexed count

### 3. Integration with Pinecone Indexing
- Generated sitemap shows all your content
- Use the existing "Sync All" button to index everything to Pinecone
- Content is chunked automatically for optimal RAG performance
- Embeddings created using OpenAI
- Vectors stored in Pinecone for semantic search

## How to Use

### Step 1: Generate Sitemap
1. Go to **Admin → Chatbot GPT RAG → Indexing**
2. Find the "Generate XML Sitemap & Index to Pinecone" section
3. Select content types (or choose "All Post Types")
4. Click **"Generate & Download Sitemap"**
5. Sitemap XML file will be generated and downloaded
6. File is saved to: `/wp-content/uploads/wp-gpt-rag-chat-sitemaps/`

### Step 2: View Content Status
1. Click **"View All Content"** to see a modal with all indexable items
2. Check which items are indexed (green checkmark) vs. not indexed (orange X)
3. Review the summary statistics

### Step 3: Index to Pinecone
1. In the "Index All Content" section above the sitemap section
2. Select post types (or "All Post Types")
3. Click **"Sync All"** to index everything to Pinecone
4. Monitor progress in the UI
5. All content will be chunked, embedded, and stored in Pinecone

## Files Added/Modified

### New Methods in `class-indexing.php`:
- `generate_xml_sitemap($post_types)` - Generates XML sitemap
- `save_sitemap_to_file($xml_content)` - Saves sitemap to file
- `get_all_indexable_content()` - Returns list of all content with indexing status
- `is_post_indexed($post_id)` - Checks if a post is indexed

### New AJAX Handlers in `Plugin.php`:
- `handle_generate_sitemap()` - Generates and returns sitemap
- `handle_get_indexable_content()` - Returns content list with indexing status

### UI Added in `indexing-page.php`:
- New "Generate XML Sitemap & Index to Pinecone" section
- "Generate & Download Sitemap" button
- "View All Content" button with modal display
- Results display area for sitemap info and download link

## Technical Details

### Sitemap XML Format
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://example.com/page</loc>
    <lastmod>2024-10-02T12:00:00+00:00</lastmod>
    <priority>0.8</priority>
    <changefreq>monthly</changefreq>
  </url>
  ...
</urlset>
```

### Priority Calculation
- Homepage: 1.0
- Pages: 0.8
- Posts: 0.6

### Change Frequency
- Posts: weekly
- Pages: monthly

### Storage Location
Sitemaps are saved to:
```
/wp-content/uploads/wp-gpt-rag-chat-sitemaps/sitemap-YYYY-MM-DD-HHMMSS.xml
```

## Benefits

1. **SEO Compatibility**: Generated sitemaps follow standard XML sitemap format
2. **Content Visibility**: Easily see what content exists and what's indexed
3. **Complete Coverage**: Ensures all content can be found by the RAG chatbot
4. **Download & Share**: Save sitemap files for external use or documentation
5. **Real-time Status**: View live indexing status for all content
6. **Selective Indexing**: Filter by post type to focus on specific content

## Workflow Example

1. **Site Administrator**:
   - Generates sitemap to document all site content
   - Downloads XML file for SEO submission or records
   - Views content list to identify gaps

2. **Content Indexing**:
   - Reviews which items aren't indexed yet
   - Uses "Sync All" to index everything to Pinecone
   - Monitors progress and verifies completion

3. **Chatbot Quality**:
   - All site content is now searchable by the RAG chatbot
   - Embeddings stored in Pinecone enable semantic search
   - Users get better, more comprehensive answers

## Notes

- Generated sitemaps include all publish and private content
- Attachments are automatically excluded
- Custom post types are included by default
- Sitemap files are timestamped for version control
- Multiple sitemaps can be generated without overwriting previous ones


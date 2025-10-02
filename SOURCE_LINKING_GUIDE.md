# Source Linking & Indexing Guide

## Overview

The **Source Linking** feature allows admins to manually associate chat responses with the correct source content (pages, posts, or PDFs). This is crucial for AI improvement and helps ensure future queries retrieve the right information.

---

## How It Works

### 1. **Real-Time Search**
When you open the "Link Correct Source" modal:
- Type keywords into the search field
- Results appear automatically after 500ms (debounced)
- Pages, posts, and PDFs are searched simultaneously
- Results show:
  - **Title** and **Excerpt**
  - **Type** (PAGE, POST, PDF)
  - **Indexing Status** (✓ Indexed / Not Indexed)
  - **View Link** to preview the content

### 2. **Selecting Sources**
- **Checkboxes**: Select one or multiple sources to link
- The "Link Selected" button shows the count of selected items
- You can link multiple sources to a single chat response

### 3. **Re-indexing Logic**

#### What is Re-indexing?
Re-indexing means converting the content into vector embeddings and storing them in Pinecone (the vector database). This allows the AI to find and retrieve this content for future queries.

#### When to Re-index?
✅ **Check "Re-index content after linking" if:**
- The content was recently updated
- The content is brand new
- You want to ensure the latest version is in the vector database

❌ **Leave it unchecked if:**
- The content is already indexed (shown by "✓ Indexed" badge)
- You're just linking for reference purposes
- You want to save API costs and processing time

#### Automatic Indexing Check
The system **automatically checks** if content is already indexed:
- If you check "Re-index" but the content is **already indexed**, it will **skip re-indexing** to avoid duplication
- If the content is **not indexed**, it will be indexed to Pinecone
- You'll see a message like: `"Source linked successfully. (Content was already indexed)"`

---

## Best Practices

### 1. **When to Link Sources?**

Link sources when:
- ✅ The AI gave a **correct answer** → Link the page that contains this info
- ✅ The AI gave a **wrong answer** → Link the page that should have been used
- ✅ The AI said **"I don't know"** → Link the page that answers this question

**Why?** This creates a training dataset showing which content should answer which questions.

### 2. **Should New Content Be Indexed?**

**Yes, absolutely!** Here's how:

#### For Pages/Posts:
- When you publish a new page, it should be indexed **automatically** (if auto-indexing is enabled in settings)
- If not, go to **RAG Chat → Content Manager** and click "Index" on the page
- Or use the "Link Correct Source" feature and check "Re-index"

#### For PDFs:
- Upload the PDF to Media Library
- Go to **RAG Chat → Content Manager** and index it manually
- Or link it via the "Link Correct Source" modal with "Re-index" checked

### 3. **Managing Vector Database**

The plugin stores vector embeddings in:
- **WordPress Database**: Metadata (post ID, chunk ID, etc.) in `wp_gpt_rag_chat_vectors` table
- **Pinecone**: Actual vector embeddings

**Important:**
- Don't delete vectors manually unless you know what you're doing
- Re-indexing a page will **update** existing vectors, not duplicate them
- The system checks the database to avoid unnecessary re-indexing

---

## Workflow Example

### Scenario: User asks about "refund policy" but got wrong answer

1. **Go to Analytics → Logs**
2. Find the conversation where the wrong answer was given
3. Click **📎 Link** next to the assistant's message
4. **Search** for "refund policy" in the modal
5. **Select** the correct refund policy page
6. **Check status**:
   - ✓ Indexed → Just link it (uncheck re-index)
   - Not Indexed → Check "Re-index" and link
7. Click **"Link Selected"**
8. Done! The system now knows this page should answer refund questions

---

## Technical Details

### Database Tables

#### `wp_gpt_rag_chat_logs`
Stores chat logs with linked sources in the `rag_sources` JSON field:
```json
[
  {
    "type": "post",
    "id": 123,
    "title": "Refund Policy",
    "url": "https://example.com/refund-policy",
    "post_type": "page",
    "manually_linked": true,
    "linked_at": "2025-10-02 14:30:00"
  }
]
```

#### `wp_gpt_rag_chat_vectors`
Tracks which content is indexed:
- `post_id`: WordPress post/page/attachment ID
- `pinecone_id`: Unique vector ID in Pinecone
- `chunk_id`: Section of content (for long documents)
- `indexed_at`: Timestamp

### Indexing Check Logic

```php
private function is_post_indexed($post_id) {
    global $wpdb;
    $vectors_table = $wpdb->prefix . 'wp_gpt_rag_chat_vectors';
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$vectors_table} WHERE post_id = %d",
        $post_id
    ));
    
    return $count > 0;
}
```

This checks if **any vectors exist** for the given post ID. If yes, it's indexed.

---

## FAQ

### Q: Can I link multiple sources to one answer?
**A:** Yes! Select multiple checkboxes and click "Link Selected".

### Q: What happens if I re-index already indexed content?
**A:** The system automatically skips re-indexing if content is already indexed (to save API costs).

### Q: How do I know if a page is indexed?
**A:** Look for the badge:
- ✓ Indexed (green) = Already in vector database
- Not Indexed (grey) = Needs indexing

### Q: Should I always re-index when linking?
**A:** No. Only re-index if:
- Content is new or recently updated
- Content shows "Not Indexed"
- You suspect the vectors are outdated

### Q: Can I link PDFs?
**A:** Yes! PDFs are searchable and linkable just like pages and posts.

### Q: Do I need to re-link sources for every conversation?
**A:** No. Linking once creates a reference. The AI learns from **all linked sources** across all conversations.

---

## Admin Workflow

### Daily Monitoring:
1. Check **Analytics → Logs** for low-rated responses (👎)
2. Review conversations and link correct sources
3. Use tags to mark issues: "needs_doc_update", "wrong_source"

### Weekly Improvements:
1. Check **Dashboard → Top User Queries** for common questions
2. Ensure those topics have indexed content
3. Link sources for any unanswered questions

### Monthly Review:
1. Export logs to CSV
2. Analyze which sources are most frequently linked
3. Update or expand those pages
4. Re-index updated content

---

## Summary

✅ **Link sources** to teach the AI which content answers which questions  
✅ **Index new content** (pages, posts, PDFs) so the AI can find it  
✅ **Check indexing status** before re-indexing to save costs  
✅ **Use real-time search** to quickly find and link correct sources  
✅ **Monitor analytics** to continuously improve AI accuracy  

The system is designed to make it easy to train the AI and improve its accuracy over time. The more you link correct sources, the better the AI becomes! 🚀


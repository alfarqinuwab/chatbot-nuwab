# 🎯 AI Improvement & Source Linking Guide

## Overview

This guide explains how to continuously improve your AI chatbot's accuracy using the **source linking** feature - a critical part of the feedback loop that makes your RAG (Retrieval Augmented Generation) system smarter over time.

---

## 📚 How RAG Works

### Current Flow:
```
User Question
    ↓
Create Embedding
    ↓
Search Pinecone (Vector DB)
    ↓
Retrieve Top 5 Similar Documents
    ↓
Send to OpenAI with Context
    ↓
Generate Answer
```

### The Problem:
- AI might not find the right document
- Document might not be indexed yet
- Document needs better content
- Similar queries should match specific pages

---

## ✅ Solution: Source Linking + Re-indexing

### The Feedback Loop:
```
1. User asks question
2. AI gives wrong/incomplete answer
3. Admin reviews in Analytics
4. Admin clicks "📎 Link" button
5. Admin searches for correct page/PDF
6. System optionally re-indexes that content
7. Future similar queries → correct source
```

---

## 🎯 Best Practices

### **When to Link Sources:**

✅ **Link a source when:**
- AI gave incorrect information
- AI said "I don't know" but answer exists
- AI used wrong document
- Answer was incomplete
- You want to boost a specific page for similar queries

❌ **Don't link when:**
- Answer was correct (just add positive tags instead)
- Question was off-topic
- No relevant content exists on your site

### **Should You Re-index?**

**✅ Re-index (checkbox ON) when:**
- Page content was recently updated
- Page wasn't indexed before (shows "Not Indexed" badge)
- You want to ensure latest content is in Pinecone
- **Recommended for most cases**

**❌ Skip re-indexing (checkbox OFF) when:**
- Page is already indexed and unchanged
- Just want to link for reference (not for future queries)
- Saving processing time/costs

---

## 🚀 How to Use

### Step 1: Find Problematic Conversations

1. Go to `GPT RAG Chat > Analytics & Logs`
2. Filter by:
   - 👎 **Negative ratings** (thumbs down)
   - **Tags** like "hallucination", "incorrect", "incomplete"
3. Click **"View Chat"** to see full conversation

### Step 2: Link the Correct Source

1. On the log entry (assistant response), click **"📎 Link"** button
2. **Modal opens** with search box
3. **Search** for the correct page/PDF:
   - Type keywords (e.g., "pricing", "installation guide")
   - Press Enter or click 🔍 Search
4. **Results show:**
   - 📰 Posts
   - 📝 Pages  
   - 📄 PDFs
   - ✓ **Indexed** badge (green) or **Not Indexed** (yellow)
5. **Review** the result:
   - Read title/excerpt
   - Click "View →" to verify it's correct
6. Click **"Link This"** button
7. **Re-index checkbox** (checked by default):
   - ✅ Keep it checked to re-index
   - ⬜ Uncheck if page is already up-to-date

### Step 3: Verify

1. Page reloads
2. **Sources** column now shows count increased
3. Click **"View Chat"** to see linked source in conversation
4. Future similar queries will retrieve this page

---

## 💡 Real-World Examples

### Example 1: Missing Product Documentation

**Scenario:**
- User asks: "How do I install the plugin?"
- AI responds: "I don't have information about installation"
- But you have an installation guide page

**Solution:**
1. Click "📎 Link" on that response
2. Search "installation"
3. Select "Installation Guide" page
4. ✅ Keep re-index checked
5. Click "Link This"

**Result:**  
Next time someone asks about installation, AI will retrieve that page!

### Example 2: Outdated Information

**Scenario:**
- User asks: "What's the pricing?"
- AI gives old pricing
- You just updated the pricing page

**Solution:**
1. Tag it: "needs_doc_update"
2. Click "📎 Link"
3. Search "pricing"
4. Select "Pricing Page"
5. ✅ Re-index checkbox ON (important!)
6. Link it

**Result:**  
Latest pricing is now in Pinecone. Future queries get correct info.

### Example 3: PDF Manual

**Scenario:**
- User asks technical question
- AI doesn't know
- Answer is in your PDF manual

**Solution:**
1. Upload PDF to Media Library (if not already)
2. Click "📎 Link" on the response
3. Search PDF name
4. Select the PDF (📄 icon)
5. Link it

**Note:** PDFs show "Not Indexed" because they need special PDF processing (future enhancement).

---

## 🔄 What Happens After Linking?

### Immediate Effects:
- ✅ Source added to `rag_sources` in database
- ✅ `sources_count` incremented
- ✅ Visible in conversation view
- ✅ Shows "manually_linked": true

### If Re-indexing:
- ✅ Page content extracted
- ✅ Split into chunks (respecting chunk_size setting)
- ✅ Embeddings created via OpenAI
- ✅ Vectors upserted to Pinecone
- ✅ Future queries can retrieve it

### Long-term Benefits:
- 📈 Improved accuracy over time
- 🎯 Better document retrieval
- 📊 Training data for similar queries
- 🔍 Transparency (admins see what docs AI used)

---

## 📊 Monitoring Improvements

### Track Your Progress:

**Dashboard KPIs:**
- **Satisfaction Rate**: Should increase over time
- **👍 Thumbs Up**: Monitor growth
- **👎 Thumbs Down**: Should decrease

**Tag Analysis:**
- Count of "hallucination" tags (should decrease)
- Count of "good_answer" tags (should increase)
- "needs_doc_update" → action items

**Source Coverage:**
- Check `sources_count` column
- More sources = better grounded responses
- Review manually_linked sources in conversations

---

## 🎓 Advanced Strategies

### 1. Proactive Linking

Don't wait for complaints:
- Review **all thumbs down** weekly
- Link sources preventively for FAQ topics
- Index all important docs before launch

### 2. Content Gap Analysis

If you link the same source repeatedly:
- ✅ Good: That page is valuable
- ⚠️ Consider: Is it detailed enough?
- 💡 Action: Expand that page's content

### 3. Query Pattern Recognition

Use "Top User Queries" in Dashboard:
- See what people ask most
- Ensure those topics are well-indexed
- Create dedicated pages if missing

### 4. Seasonal Updates

When content changes:
- Price changes → Re-index pricing page
- New products → Index product pages
- Policy updates → Re-index policy pages
- Use "📎 Link" with re-index for quick updates

---

## 🔧 Technical Details

### Database Schema:

```sql
rag_sources JSON Column:
[
  {
    "type": "post",
    "id": 123,
    "title": "Installation Guide",
    "url": "https://example.com/install",
    "post_type": "page",
    "manually_linked": true,
    "linked_at": "2025-10-02 08:30:00"
  }
]
```

### AJAX Endpoints:

1. **`wp_gpt_rag_chat_search_content`**
   - Searches posts, pages, attachments
   - Returns: title, excerpt, indexed status, URL

2. **`wp_gpt_rag_chat_link_source`**
   - Links source to log entry
   - Optionally triggers re-indexing
   - Updates `rag_sources` and `sources_count`

### Re-indexing Process:

```php
$indexing = new Indexing();
$indexing->index_post($source_id);
```

This triggers:
1. Content extraction
2. Chunking (based on settings)
3. Embedding generation (OpenAI)
4. Vector upsert (Pinecone)

---

## ⚠️ Important Notes

### Costs:
- **Re-indexing** uses OpenAI API (embedding costs)
- **Pinecone** storage (vectors)
- Small cost per page, but can add up if re-indexing frequently

### Performance:
- Re-indexing is **asynchronous** for large pages
- May take 10-60 seconds depending on page size
- Don't spam re-index button

### Limitations:
- PDFs need special processing (use PDF Import feature first)
- Very large pages (>10K words) split into multiple chunks
- Old Pinecone vectors aren't automatically deleted (use Clear Vectors if needed)

---

## 🎯 Success Metrics

### Goal: 80%+ Satisfaction Rate

**Track Monthly:**
- Thumbs up / Total rated
- Average sources per response
- "hallucination" tag frequency
- "good_answer" tag frequency

**Target:**
- < 5% hallucination rate
- > 2 sources average per response
- < 10% "needs improvement" tags

---

## 🆘 Troubleshooting

### "Search returns no results"
- Check if content is published (not draft)
- Try different keywords
- Verify post type is enabled in settings

### "Re-index doesn't seem to work"
- Check OpenAI API key is valid
- Check Pinecone connection
- Look at indexing logs in debug mode
- Go to Indexing page to manually index

### "AI still gives wrong answer after linking"
- May take a few moments for Pinecone to sync
- Try more specific user query
- Check if multiple sources conflict
- Review chunk size settings (may need adjustment)

---

## 📖 Related Features

- **Tags**: Categorize responses for later review
- **Ratings**: User feedback (thumbs up/down)
- **Conversation View**: See full context
- **Analytics Dashboard**: Track overall trends
- **Indexing Page**: Bulk re-index content

---

## 🎉 Summary

**The source linking feature is your SECRET WEAPON for:**
- ✅ Closing the AI feedback loop
- ✅ Continuously improving accuracy
- ✅ Ensuring correct documents are retrieved
- ✅ Building a smarter chatbot over time

**Best Practice:**
Review analytics weekly → Link correct sources → Monitor satisfaction → Repeat!

---

*Last updated: October 2, 2025*


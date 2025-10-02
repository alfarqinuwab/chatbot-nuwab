# 🔄 Auto-Indexing Feature Documentation

## Overview
Added comprehensive control system for automatic background indexing when content is saved or published in WordPress.

## ✨ Features Added

### 1. **Settings Controls** (`Settings` tab → `Indexing Settings` → `Automatic Indexing`)

#### Enable/Disable Toggle
- **Setting**: `enable_auto_indexing`
- **Default**: `true` (enabled)
- **Description**: Master switch to enable/disable automatic indexing globally

#### Post Type Selection
- **Setting**: `auto_index_post_types`
- **Default**: `['post', 'page']`
- **Options**: All public post types (Posts, Pages, Custom Post Types)
- **UI**: Multi-checkbox list showing all available post types

#### Indexing Delay
- **Setting**: `auto_index_delay`
- **Default**: `30` seconds
- **Range**: 10-600 seconds (10 minutes max)
- **Purpose**: Prevents re-indexing during rapid edits

---

## 🎯 How It Works

### Flow Diagram
```
Post Save/Publish
    ↓
Check: Is auto-indexing enabled?
    ↓ YES
Check: Is post type in allowed list?
    ↓ YES
Check: Is post marked for inclusion?
    ↓ YES
Schedule WP-Cron Job (with configured delay)
    ↓
Wait {delay} seconds
    ↓
Index Post to Pinecone (background)
```

### Code Flow
1. **`save_post` hook triggered** → `Plugin::handle_post_save()`
2. **Check settings** → Get `enable_auto_indexing`, `auto_index_post_types`, `auto_index_delay`
3. **Validate conditions**:
   - Not revision/autosave
   - Post status is `publish` or `private`
   - Auto-indexing is enabled
   - Post type is in allowed list
   - Post meta `_wp_gpt_rag_chat_include` is true
4. **Schedule cron** → `wp_schedule_single_event(time() + {delay}, 'wp_gpt_rag_chat_index_content', [$post_id])`
5. **Cron executes** → `Plugin::cron_index_content()` → `Indexing::index_post()`

---

## 📊 Live Indexed Items Counter

### Location
- **Page**: `admin.php?page=wp-gpt-rag-chat-indexing`
- **Position**: Next to page title "Content Indexing"
- **Format**: `Indexed Items: 150`

### Features
- ✅ **Real-time updates** during active indexing (every 3 seconds)
- ✅ **Immediate updates** from batch sync responses
- ✅ **Visual animation** when count changes:
  - Scales up to 1.2x
  - Turns green (#00a32a)
  - Adds subtle glow effect
  - Returns to blue (#2271b1) after 500ms
- ✅ **Comma formatting** (e.g., 1,234)

### AJAX Endpoint
- **Action**: `wp_gpt_rag_chat_get_stats`
- **Returns**: `total_posts`, `total_vectors`, `recent_activity`, `by_post_type`

---

## 🎨 UI Components

### Settings Page Section
```
┌─────────────────────────────────────────────┐
│  🔄 Automatic Indexing                      │
├─────────────────────────────────────────────┤
│  Control automatic background indexing...   │
│                                             │
│  ☑ Enable Auto-Indexing                    │
│     Automatically index content to          │
│     Pinecone when posts are saved...        │
│                                             │
│  Auto-Index Post Types:                     │
│    ☑ Posts (post)                           │
│    ☑ Pages (page)                           │
│    ☐ Media (attachment)                     │
│    ☐ Your CPT (your_cpt)                    │
│                                             │
│  Indexing Delay: [30] seconds               │
│     Time to wait before indexing...         │
│                                             │
│  ℹ️ Note: Auto-indexing uses WordPress      │
│     Cron (WP-Cron)...                       │
└─────────────────────────────────────────────┘
```

### Indexing Page Header
```
Content Indexing  [Indexed Items: 1,234]
                  ↑ Updates in real-time
```

---

## ⚙️ Settings Reference

### Database Keys
```php
[
    'enable_auto_indexing' => true,              // boolean
    'auto_index_post_types' => ['post', 'page'], // array
    'auto_index_delay' => 30,                    // int (seconds)
]
```

### Accessing Settings
```php
$settings = WP_GPT_RAG_Chat\Settings::get_settings();
$enabled = $settings['enable_auto_indexing'];
$post_types = $settings['auto_index_post_types'];
$delay = $settings['auto_index_delay'];
```

---

## 🚀 User Guide

### How to Enable/Disable Auto-Indexing
1. Go to **WP Admin** → **GPT RAG Chat** → **Settings**
2. Click **Indexing Settings** tab
3. Scroll to **"Automatic Indexing"** section
4. Toggle **"Enable Auto-Indexing"** checkbox
5. Click **Save Changes**

### How to Select Post Types
1. In the same section, find **"Auto-Index Post Types"**
2. Check/uncheck the post types you want to auto-index
3. Custom post types will appear automatically if they're public
4. Click **Save Changes**

### How to Change Delay
1. Find **"Indexing Delay"** field
2. Enter a value between 10-600 seconds
3. Recommended: 30-60 seconds for most sites
4. Higher values (120-300s) for sites with frequent edits
5. Click **Save Changes**

### How to Monitor Indexing
1. Go to **Content Indexing** page
2. Watch the **"Indexed Items: X"** counter in the header
3. It updates automatically during indexing with a green pulse animation

---

## 🔧 Troubleshooting

### Auto-indexing not working?
1. **Check WP-Cron**: Make sure WP-Cron is enabled (not disabled in `wp-config.php`)
2. **Check settings**: Verify auto-indexing is enabled
3. **Check post type**: Ensure the post type is selected in settings
4. **Check post meta**: Post must have `_wp_gpt_rag_chat_include` = true
5. **Check error logs**: Look for PHP errors in `wp-content/debug.log`

### Counter not updating?
1. **Hard refresh**: Press Ctrl+Shift+R (or Cmd+Shift+R on Mac)
2. **Check AJAX**: Open browser console, look for AJAX errors
3. **Check permissions**: User must have `edit_posts` capability

### Delay too short/long?
- **Too short** (< 10s): May index before user finishes editing
- **Optimal** (30-60s): Good balance for most sites
- **Too long** (> 300s): Content may be stale, users may leave site before cron runs

---

## 📝 Code References

### Files Modified
1. `includes/class-settings.php` - Added settings fields and callbacks
2. `includes/Plugin.php` - Updated `handle_post_save()` to respect settings
3. `templates/settings-page.php` - Added UI section for auto-indexing
4. `templates/indexing-page.php` - Added live counter in header

### Key Functions
- `Settings::get_settings()` - Retrieve all settings with defaults
- `Plugin::handle_post_save()` - Triggered on save_post hook
- `Plugin::cron_index_content()` - Executes the actual indexing
- `Admin::get_indexing_stats()` - Returns current indexing statistics

---

## 📚 Related Documentation
- [Content Indexing](./INDEXING.md)
- [Sitemap Generation](./SITEMAP_GENERATION.md)
- [RAG Improvements](./RAG_IMPROVEMENTS.md)

---

**Last Updated**: 2025-10-02  
**Version**: 1.0  
**Author**: AI Assistant


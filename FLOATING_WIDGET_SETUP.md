# Floating Chat Widget Setup - إعداد نافذة الشات العائمة

## Overview - نظرة عامة

The chat widget now appears as a **floating button** on all pages of your website. It stays fixed in the bottom-left corner and expands when clicked.

تظهر نافذة الشات الآن كـ **زر عائم** في جميع صفحات موقعك. تبقى ثابتة في الزاوية السفلية اليسرى وتتوسع عند النقر عليها.

---

## 🎯 Features - المميزات

### ✅ Site-Wide Availability
- **Appears on ALL pages** automatically
- No need to add shortcodes
- No need to enable per-post
- Always accessible to visitors

**متاح في جميع الموقع**
- **يظهر في جميع الصفحات** تلقائياً
- لا حاجة لإضافة رموز مختصرة (shortcodes)
- لا حاجة للتفعيل لكل منشور
- دائماً متاح للزوار

### ✅ Floating Button Design
- **Fixed position** at bottom-left corner
- **Beautiful gold design** matching your website
- **Animated pulse effect** to attract attention
- **Smooth transitions** when opening/closing

**تصميم زر عائم**
- **موضع ثابت** في الزاوية السفلية اليسرى
- **تصميم ذهبي جميل** يتناسب مع موقعك
- **تأثير نبض متحرك** لجذب الانتباه
- **انتقالات سلسة** عند الفتح/الإغلاق

### ✅ Smart Behavior
- **Starts collapsed** - doesn't block content
- **Expands when clicked** - full chat interface
- **Remembers state** - stays open/closed as user prefers
- **Hover effects** - visual feedback

**سلوك ذكي**
- **يبدأ مطوياً** - لا يحجب المحتوى
- **يتوسع عند النقر** - واجهة شات كاملة
- **يتذكر الحالة** - يبقى مفتوحاً/مغلقاً حسب تفضيل المستخدم
- **تأثيرات عند التمرير** - تغذية راجعة بصرية

---

## 📍 Position & Style - الموضع والنمط

### Default Position
```css
position: fixed;
bottom: 20px;
left: 20px;
z-index: 9999;
```

### Collapsed State (Button Only)
- **Width**: Auto (fits content)
- **Height**: Auto (just the header)
- **Border Radius**: 50px (pill shape)
- **Animation**: Gentle pulse effect

**الحالة المطوية (الزر فقط)**
- **العرض**: تلقائي (يناسب المحتوى)
- **الارتفاع**: تلقائي (الرأس فقط)
- **انحناء الحواف**: 50px (شكل حبة)
- **الحركة**: تأثير نبض لطيف

### Expanded State (Full Chat)
- **Width**: 380px
- **Height**: 500px
- **Border Radius**: 16px
- **Content**: Messages, input, footer all visible

**الحالة الموسعة (الشات الكامل)**
- **العرض**: 380px
- **الارتفاع**: 500px
- **انحناء الحواف**: 16px
- **المحتوى**: الرسائل، الإدخال، التذييل كلها مرئية

---

## 🎨 Visual Effects - التأثيرات البصرية

### Pulse Animation
The collapsed button has a subtle pulse animation to attract user attention:
```css
@keyframes pulse-gold {
    0%, 100% { box-shadow: 0 8px 24px rgba(209, 168, 95, 0.35); }
    50% { box-shadow: 0 8px 32px rgba(209, 168, 95, 0.6); }
}
```

**حركة النبض**
الزر المطوي له حركة نبض خفيفة لجذب انتباه المستخدم

### Hover Effect
When user hovers over the collapsed button:
- Scales up to 108% size
- Moves up slightly (2px)
- Shadow becomes more prominent
- Smooth transition (0.3s)

**تأثير التمرير**
عندما يمرر المستخدم فوق الزر المطوي:
- يتكبر إلى 108% من الحجم
- يتحرك للأعلى قليلاً (2px)
- يصبح الظل أكثر بروزاً
- انتقال سلس (0.3 ثانية)

### Click Effect
When clicked:
- Scales down briefly to 102%
- Gives tactile feedback
- Then expands to full chat window

**تأثير النقر**
عند النقر:
- يتقلص لفترة وجيزة إلى 102%
- يعطي ردة فعل لمسية
- ثم يتوسع إلى نافذة الشات الكاملة

---

## ⚙️ How It Works - كيف يعمل

### Backend (PHP)
The widget is added to the footer using WordPress hooks:

```php
// In Plugin.php
add_action('wp_footer', [$chat, 'render_floating_chat_widget']);

// In class-chat.php
public function render_floating_chat_widget() {
    // Don't show in admin area
    if (is_admin()) {
        return;
    }
    
    // Output the chat widget
    echo $this->get_chat_widget_html();
}
```

### Frontend (JavaScript)
The widget initializes automatically:

```javascript
// Widget starts collapsed
this.isOpen = false;

// Toggle on click
$('.wp-gpt-rag-chat-toggle').on('click', function() {
    self.toggleWidget();
});
```

---

## 🔧 Customization - التخصيص

### Change Position

#### Move to Right Side
Edit `frontend.css`:
```css
.wp-gpt-rag-chat-widget {
    bottom: 20px;
    right: 20px;  /* Change from 'left' to 'right' */
    left: auto;   /* Add this line */
}
```

#### Move to Top
Edit `frontend.css`:
```css
.wp-gpt-rag-chat-widget {
    top: 20px;    /* Change from 'bottom' to 'top' */
    bottom: auto; /* Add this line */
    left: 20px;
}
```

### تغيير الموضع

#### نقل إلى الجانب الأيمن
عدّل `frontend.css`:
```css
.wp-gpt-rag-chat-widget {
    bottom: 20px;
    right: 20px;  /* غيّر من 'left' إلى 'right' */
    left: auto;   /* أضف هذا السطر */
}
```

#### نقل إلى الأعلى
عدّل `frontend.css`:
```css
.wp-gpt-rag-chat-widget {
    top: 20px;    /* غيّر من 'bottom' إلى 'top' */
    bottom: auto; /* أضف هذا السطر */
    left: 20px;
}
```

### Disable Pulse Animation
If you don't want the pulse effect:

Edit `frontend.css` and remove or comment out:
```css
/* Remove this block to disable pulse */
.wp-gpt-rag-chat-widget:not(.wp-gpt-rag-chat-open) .wp-gpt-rag-chat-header {
    animation: pulse-gold 2s ease-in-out infinite;
}
```

### تعطيل حركة النبض
إذا كنت لا تريد تأثير النبض:

عدّل `frontend.css` واحذف أو علّق على:
```css
/* احذف هذا الجزء لتعطيل النبض */
.wp-gpt-rag-chat-widget:not(.wp-gpt-rag-chat-open) .wp-gpt-rag-chat-header {
    animation: pulse-gold 2s ease-in-out infinite;
}
```

### Change Button Text
The button shows "💬 اسأل سؤالاً" (Ask a Question)

To change it, edit `includes/class-chat.php`:
```php
<h3>💬 اسأل سؤالاً</h3>
```

Change to whatever text you want:
```php
<h3>💬 تحدث معنا</h3>  // Chat with us
<h3>💬 هل تحتاج مساعدة؟</h3>  // Need help?
<h3>💬 اسألني</h3>  // Ask me
```

### تغيير نص الزر
الزر يظهر "💬 اسأل سؤالاً"

لتغييره، عدّل `includes/class-chat.php`:
```php
<h3>💬 اسأل سؤالاً</h3>
```

غيّره إلى أي نص تريد:
```php
<h3>💬 تحدث معنا</h3>  // تحدث معنا
<h3>💬 هل تحتاج مساعدة؟</h3>  // هل تحتاج مساعدة؟
<h3>💬 اسألني</h3>  // اسألني
```

---

## 📱 Responsive Behavior - السلوك المتجاوب

### On Desktop (>768px)
- Full 380px width when expanded
- Positioned 20px from edges
- Smooth animations

### On Tablet (768px - 480px)
- Adapts to screen width
- Maintains readability
- Touch-friendly size

### On Mobile (<480px)
- Full width when expanded
- Rounds only top corners
- Optimized for touch
- 70% of screen height

### على سطح المكتب (>768px)
- عرض كامل 380px عند التوسع
- موضع 20px من الحواف
- حركات سلسة

### على الأجهزة اللوحية (768px - 480px)
- يتكيف مع عرض الشاشة
- يحافظ على الوضوح
- حجم مناسب للمس

### على الهاتف المحمول (<480px)
- عرض كامل عند التوسع
- يدور الزوايا العلوية فقط
- محسّن للمس
- 70% من ارتفاع الشاشة

---

## 🚀 Activation Steps - خطوات التفعيل

### Automatic Activation
The floating widget is **automatically active** once you:

1. ✅ Enable chatbot in Settings
2. ✅ Configure API keys (OpenAI & Pinecone)
3. ✅ Set chat visibility preferences

That's it! The widget will appear on all pages.

### التفعيل التلقائي
نافذة الشات العائمة **نشطة تلقائياً** بمجرد:

1. ✅ تفعيل الشات في الإعدادات
2. ✅ تكوين مفاتيح API (OpenAI و Pinecone)
3. ✅ ضبط تفضيلات ظهور الشات

هذا كل شيء! ستظهر النافذة في جميع الصفحات.

### Manual Placement (Optional)
If you prefer to manually place the chat using shortcode instead:

1. Go to Settings → Chat Settings
2. The floating widget still appears automatically
3. You can also use `[wp_gpt_rag_chat]` shortcode anywhere
4. Both methods work simultaneously

### الوضع اليدوي (اختياري)
إذا كنت تفضل وضع الشات يدوياً باستخدام رمز مختصر بدلاً من ذلك:

1. اذهب إلى الإعدادات ← إعدادات الشات
2. النافذة العائمة لا تزال تظهر تلقائياً
3. يمكنك أيضاً استخدام رمز `[wp_gpt_rag_chat]` في أي مكان
4. كلا الطريقتين تعملان في نفس الوقت

---

## ⚠️ Important Notes - ملاحظات مهمة

### Widget Won't Show If:
- ❌ Chatbot is disabled in settings
- ❌ API keys are not configured
- ❌ User doesn't match visibility settings
- ❌ You're on admin pages (backend)

### لن تظهر النافذة إذا:
- ❌ تم تعطيل الشات في الإعدادات
- ❌ لم يتم تكوين مفاتيح API
- ❌ المستخدم لا يطابق إعدادات الظهور
- ❌ أنت في صفحات الإدارة (الواجهة الخلفية)

### Cache Considerations
After making changes:
1. Clear WordPress cache
2. Clear browser cache (Ctrl+F5)
3. Clear CDN cache if applicable
4. Test in incognito mode

### اعتبارات الذاكرة المؤقتة
بعد إجراء التغييرات:
1. امسح ذاكرة WordPress المؤقتة
2. امسح ذاكرة المتصفح المؤقتة (Ctrl+F5)
3. امسح ذاكرة CDN المؤقتة إن وجدت
4. اختبر في وضع التصفح الخفي

---

## 🎯 User Experience Flow - تدفق تجربة المستخدم

### First Visit
1. User lands on any page
2. Sees floating gold button with pulse
3. Button says "💬 اسأل سؤالاً"
4. Curiosity is piqued

### الزيارة الأولى
1. المستخدم يصل إلى أي صفحة
2. يرى زر ذهبي عائم بنبض
3. الزر يقول "💬 اسأل سؤالاً"
4. الفضول يثار

### Interaction
1. User clicks the button
2. Widget expands smoothly
3. Welcome message appears
4. Input field is ready
5. User can start chatting

### التفاعل
1. المستخدم ينقر على الزر
2. تتوسع النافذة بسلاسة
3. تظهر رسالة الترحيب
4. حقل الإدخال جاهز
5. المستخدم يمكنه البدء في الدردشة

### After Chat
1. User can minimize (click × or header)
2. Widget collapses back to button
3. User continues browsing
4. Widget is always available
5. Can reopen anytime

### بعد الدردشة
1. المستخدم يمكنه التصغير (نقر × أو الرأس)
2. النافذة تطوى إلى زر
3. المستخدم يواصل التصفح
4. النافذة دائماً متاحة
5. يمكن إعادة الفتح في أي وقت

---

## 🔍 Troubleshooting - استكشاف الأخطاء

### Widget Not Appearing
**Check:**
1. ✅ Is chatbot enabled? (Settings → Chat Settings)
2. ✅ Are API keys configured?
3. ✅ Does user match visibility settings?
4. ✅ Clear all caches
5. ✅ Check browser console (F12) for errors

### النافذة لا تظهر
**تحقق:**
1. ✅ هل تم تفعيل الشات؟ (الإعدادات ← إعدادات الشات)
2. ✅ هل تم تكوين مفاتيح API؟
3. ✅ هل المستخدم يطابق إعدادات الظهور؟
4. ✅ امسح جميع ذاكرات التخزين المؤقت
5. ✅ تحقق من وحدة تحكم المتصفح (F12) للأخطاء

### Widget Appears But Won't Open
**Check:**
1. ✅ JavaScript errors in console?
2. ✅ jQuery loaded properly?
3. ✅ No theme/plugin conflicts?
4. ✅ Try disabling other plugins temporarily

### النافذة تظهر لكن لا تفتح
**تحقق:**
1. ✅ أخطاء JavaScript في وحدة التحكم؟
2. ✅ تم تحميل jQuery بشكل صحيح؟
3. ✅ لا توجد تعارضات في القالب/الإضافة؟
4. ✅ حاول تعطيل الإضافات الأخرى مؤقتاً

### Widget Covers Important Content
**Solution:**
Change position in CSS (see Customization section above)

### النافذة تغطي محتوى مهم
**الحل:**
غيّر الموضع في CSS (انظر قسم التخصيص أعلاه)

---

## 📊 Performance - الأداء

### Optimizations
- ✅ Widget only loads when needed
- ✅ No impact on page load speed
- ✅ CSS and JS are minified
- ✅ Font loaded asynchronously
- ✅ Images optimized

### التحسينات
- ✅ النافذة تحمّل فقط عند الحاجة
- ✅ لا تأثير على سرعة تحميل الصفحة
- ✅ CSS و JS مصغّرة
- ✅ الخط يحمّل بشكل غير متزامن
- ✅ الصور محسّنة

---

## 📝 Summary - الملخص

The floating chat widget provides:
- ✅ **Always available** on all pages
- ✅ **Non-intrusive** when collapsed
- ✅ **Beautiful design** matching your site
- ✅ **Easy to use** - one click to open
- ✅ **Mobile friendly** - works on all devices
- ✅ **Customizable** - change position, colors, text

توفر نافذة الشات العائمة:
- ✅ **متاحة دائماً** في جميع الصفحات
- ✅ **غير مزعجة** عند الطي
- ✅ **تصميم جميل** يتناسب مع موقعك
- ✅ **سهلة الاستخدام** - نقرة واحدة للفتح
- ✅ **متوافقة مع الموبايل** - تعمل على جميع الأجهزة
- ✅ **قابلة للتخصيص** - غيّر الموضع، الألوان، النص

---

**Enjoy your new floating chat widget! استمتع بنافذة الشات العائمة الجديدة! 💬✨**



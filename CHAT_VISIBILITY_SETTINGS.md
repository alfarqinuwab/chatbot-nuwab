# Chat Visibility Settings - دليل إعدادات ظهور الشات

## Overview - نظرة عامة

This document explains the new chat visibility settings that allow you to control who can see and use the chat widget on your WordPress website.

يشرح هذا المستند إعدادات ظهور الشات الجديدة التي تتيح لك التحكم في من يمكنه رؤية واستخدام نافذة الشات على موقع WordPress الخاص بك.

---

## Features - المميزات

### 1. Master Enable/Disable Switch
**Enable Chatbot Checkbox**
- Location: Settings → Chat Settings → Chat Widget Customization
- Purpose: Main on/off switch for the entire chat system
- When disabled: Chat widget will not appear anywhere on the site

**تفعيل/تعطيل الشات الرئيسي**
- الموقع: الإعدادات ← إعدادات الشات ← تخصيص نافذة الشات
- الغرض: المفتاح الرئيسي لتشغيل/إيقاف نظام الشات بالكامل
- عند التعطيل: لن تظهر نافذة الشات في أي مكان على الموقع

---

### 2. Chat Visibility Options
**Three visibility modes:**

#### Option 1: Show to Everyone (Default)
**Show to Everyone (Visitors & Logged-in Users)**
- Chat is visible to all website visitors
- Works for both guests and registered users
- Recommended for public-facing websites

**إظهار للجميع (الزوار والمستخدمين المسجلين)**
- يظهر الشات لجميع زوار الموقع
- يعمل للضيوف والمستخدمين المسجلين
- موصى به للمواقع العامة

#### Option 2: Logged-in Users Only
**Show to Logged-in Users Only**
- Chat only appears for users who are logged into WordPress
- Visitors who are not logged in will NOT see the chat widget
- Perfect for membership sites, intranets, or private communities
- Adds an extra layer of exclusivity

**إظهار للمستخدمين المسجلين فقط**
- يظهر الشات فقط للمستخدمين المسجلين في WordPress
- الزوار غير المسجلين لن يروا نافذة الشات
- مثالي لمواقع العضويات، الشبكات الداخلية، أو المجتمعات الخاصة
- يضيف طبقة إضافية من الحصرية

#### Option 3: Visitors Only
**Show to Visitors Only (Not Logged-in)**
- Chat only appears for non-logged-in visitors
- Logged-in users will NOT see the chat widget
- Useful when you want to provide support only to potential customers
- Existing members can use other support channels

**إظهار للزوار فقط (غير المسجلين)**
- يظهر الشات فقط للزوار غير المسجلين
- المستخدمون المسجلون لن يروا نافذة الشات
- مفيد عندما تريد تقديم الدعم فقط للعملاء المحتملين
- الأعضاء الحاليون يمكنهم استخدام قنوات دعم أخرى

---

## How to Configure - كيفية الضبط

### Step 1: Access Settings
1. Log into WordPress Admin Dashboard
2. Go to **GPT RAG Chat** → **Settings**
3. Click on the **Chat Settings** tab

### الخطوة 1: الوصول للإعدادات
1. سجل الدخول إلى لوحة تحكم WordPress
2. اذهب إلى **GPT RAG Chat** ← **الإعدادات**
3. انقر على تبويب **إعدادات الشات**

### Step 2: Enable Chat
1. Check the **"Enable chatbot on front-end"** checkbox
2. This must be enabled for the chat to work

### الخطوة 2: تفعيل الشات
1. ضع علامة على خيار **"تفعيل الشات في الواجهة الأمامية"**
2. يجب تفعيل هذا الخيار لكي يعمل الشات

### Step 3: Choose Visibility
1. Find the **"Chat Visibility"** dropdown
2. Select your preferred option:
   - **Everyone** - For public access
   - **Logged-in Users Only** - For members only
   - **Visitors Only** - For non-members only

### الخطوة 3: اختيار الظهور
1. ابحث عن قائمة **"ظهور الشات"**
2. اختر الخيار المفضل لديك:
   - **الجميع** - للوصول العام
   - **المستخدمون المسجلون فقط** - للأعضاء فقط
   - **الزوار فقط** - لغير الأعضاء فقط

### Step 4: Save Settings
1. Scroll to the bottom of the page
2. Click **"Save Settings"**
3. Wait for the success message

### الخطوة 4: حفظ الإعدادات
1. انتقل إلى أسفل الصفحة
2. انقر على **"حفظ الإعدادات"**
3. انتظر رسالة النجاح

---

## Security Features - مميزات الأمان

### Server-Side Validation
The plugin validates chat visibility on both:
- **Frontend Display**: Chat widget won't render for unauthorized users
- **AJAX Requests**: API calls are blocked if user doesn't have permission

This prevents:
- Unauthorized access attempts
- Users bypassing frontend restrictions using browser tools
- API abuse

### التحقق من جانب الخادم
يتحقق الإضافة من ظهور الشات في كلا من:
- **عرض الواجهة الأمامية**: لن يتم عرض نافذة الشات للمستخدمين غير المصرح لهم
- **طلبات AJAX**: يتم حظر استدعاءات API إذا لم يكن لدى المستخدم إذن

هذا يمنع:
- محاولات الوصول غير المصرح بها
- المستخدمين من تجاوز قيود الواجهة الأمامية باستخدام أدوات المتصفح
- إساءة استخدام API

---

## Use Cases - حالات الاستخدام

### Membership Sites
**Logged-in Users Only**
- Provide AI assistance exclusively to paying members
- Add value to premium membership tiers
- Reduce support load for non-members

**المستخدمون المسجلون فقط**
- توفير مساعدة AI حصرياً للأعضاء المدفوعين
- إضافة قيمة لمستويات العضوية المميزة
- تقليل عبء الدعم لغير الأعضاء

### E-Commerce Sites
**Visitors Only**
- Help potential customers before they purchase
- Guide them through product selection
- Answer pre-sales questions
- Members can contact you directly

**الزوار فقط**
- مساعدة العملاء المحتملين قبل الشراء
- إرشادهم خلال اختيار المنتج
- الإجابة على أسئلة ما قبل البيع
- الأعضاء يمكنهم الاتصال بك مباشرة

### Public Information Sites
**Everyone**
- Maximum accessibility
- Answer general questions for all visitors
- Provide 24/7 automated assistance

**الجميع**
- أقصى قدر من إمكانية الوصول
- الإجابة على الأسئلة العامة لجميع الزوار
- توفير مساعدة آلية على مدار الساعة

---

## Technical Details - التفاصيل التقنية

### Settings Storage
```php
// Setting name: chat_visibility
// Possible values:
'everyone'         // Default - show to all
'logged_in_only'   // Only logged-in users
'visitors_only'    // Only non-logged-in visitors
```

### Permission Check Function
```php
/**
 * Check if chat should be displayed
 * @return bool True if chat should display
 */
private function should_display_chat() {
    $settings = $this->settings;
    $chat_visibility = $settings['chat_visibility'] ?? 'everyone';
    $is_user_logged_in = is_user_logged_in();
    
    switch ($chat_visibility) {
        case 'logged_in_only':
            return $is_user_logged_in;
        case 'visitors_only':
            return !$is_user_logged_in;
        case 'everyone':
        default:
            return true;
    }
}
```

### AJAX Security Check
The plugin validates permissions before processing any chat query:
```php
// Validates on every chat message sent
if (!$can_use_chat) {
    wp_send_json_error([
        'message' => __('You do not have permission to use the chat.')
    ]);
}
```

---

## Troubleshooting - استكشاف الأخطاء

### Chat Not Showing for Anyone
**Problem**: Chat doesn't appear for any users

**Solution**:
1. Check **"Enable chatbot on front-end"** is checked
2. Verify Chat Visibility is not set to an incompatible option
3. Clear browser cache (Ctrl+F5)
4. Check WordPress cache plugin settings

**المشكلة**: الشات لا يظهر لأي مستخدمين

**الحل**:
1. تحقق من تفعيل **"تفعيل الشات في الواجهة الأمامية"**
2. تحقق من أن إعداد ظهور الشات ليس مضبوطاً على خيار غير متوافق
3. امسح ذاكرة التخزين المؤقت للمتصفح (Ctrl+F5)
4. تحقق من إعدادات إضافة التخزين المؤقت في WordPress

### Chat Shows When It Shouldn't
**Problem**: Chat appears for users it shouldn't

**Solution**:
1. Double-check the Chat Visibility setting
2. Verify user login status matches your expectation
3. Save settings again
4. Test in incognito/private browsing mode

**المشكلة**: يظهر الشات للمستخدمين الذين لا ينبغي أن يظهر لهم

**الحل**:
1. تحقق مرة أخرى من إعداد ظهور الشات
2. تحقق من حالة تسجيل دخول المستخدم تطابق توقعاتك
3. احفظ الإعدادات مرة أخرى
4. اختبر في وضع التصفح الخفي/الخاص

### Permission Error When Sending Message
**Problem**: Error message "You do not have permission to use the chat"

**Solution**:
1. This is working correctly - the user doesn't match visibility settings
2. Either:
   - Change visibility setting to include this user type
   - Ask user to log in (if set to logged-in only)
   - Ask user to log out (if set to visitors only)

**المشكلة**: رسالة خطأ "ليس لديك إذن لاستخدام الشات"

**الحل**:
1. هذا يعمل بشكل صحيح - المستخدم لا يطابق إعدادات الظهور
2. إما:
   - غيّر إعداد الظهور ليشمل هذا النوع من المستخدمين
   - اطلب من المستخدم تسجيل الدخول (إذا كان مضبوطاً على المسجلين فقط)
   - اطلب من المستخدم تسجيل الخروج (إذا كان مضبوطاً على الزوار فقط)

---

## Compatibility - التوافق

### WordPress Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- Works with any theme
- Compatible with caching plugins (after cache clear)

### متطلبات WordPress
- WordPress 5.0 أو أحدث
- PHP 7.4 أو أحدث
- يعمل مع أي قالب
- متوافق مع إضافات التخزين المؤقت (بعد مسح الذاكرة المؤقتة)

### User Roles Supported
- **Logged-in**: All WordPress user roles (Administrator, Editor, Author, Contributor, Subscriber, Custom Roles)
- **Visitors**: Any non-logged-in user

### أدوار المستخدمين المدعومة
- **مسجلون**: جميع أدوار مستخدمي WordPress (مدير، محرر، كاتب، مساهم، مشترك، أدوار مخصصة)
- **زوار**: أي مستخدم غير مسجل

---

## Best Practices - أفضل الممارسات

### 1. Test Before Going Live
- Test each visibility option in a staging environment
- Verify behavior for both logged-in and logged-out states
- Check mobile and desktop views

### 1. اختبر قبل النشر
- اختبر كل خيار ظهور في بيئة اختبار
- تحقق من السلوك لكل من الحالات المسجلة وغير المسجلة
- تحقق من عروض الموبايل وسطح المكتب

### 2. Communicate Changes
- If restricting chat access, inform your users
- Add a note on login page if chat requires authentication
- Provide alternative support channels

### 2. التواصل بشأن التغييرات
- إذا قمت بتقييد الوصول للشات، أبلغ مستخدميك
- أضف ملاحظة على صفحة تسجيل الدخول إذا كان الشات يتطلب مصادقة
- وفّر قنوات دعم بديلة

### 3. Monitor Usage
- Check Analytics to see chat usage patterns
- Adjust visibility settings based on user behavior
- Review logs regularly

### 3. راقب الاستخدام
- تحقق من التحليلات لرؤية أنماط استخدام الشات
- اضبط إعدادات الظهور بناءً على سلوك المستخدم
- راجع السجلات بانتظام

---

## Future Enhancements - التحسينات المستقبلية

Possible future additions:
- Schedule-based visibility (show chat only during business hours)
- Role-based visibility (show to specific user roles)
- Page-specific visibility rules
- Geographic restrictions
- Device-based visibility (mobile vs desktop)

إضافات محتملة مستقبلاً:
- ظهور بناءً على الجدول الزمني (إظهار الشات فقط خلال ساعات العمل)
- ظهور بناءً على الدور (إظهار لأدوار محددة من المستخدمين)
- قواعد ظهور خاصة بالصفحات
- قيود جغرافية
- ظهور بناءً على الجهاز (موبايل مقابل سطح المكتب)

---

## Support - الدعم

For issues or questions about chat visibility settings:
1. Check this documentation first
2. Review the troubleshooting section
3. Check your WordPress and plugin versions
4. Contact support with:
   - Your visibility setting
   - User login status
   - Browser console errors (F12)
   - Screenshots if possible

للمشاكل أو الأسئلة حول إعدادات ظهور الشات:
1. راجع هذا التوثيق أولاً
2. راجع قسم استكشاف الأخطاء
3. تحقق من إصدارات WordPress والإضافة
4. اتصل بالدعم مع:
   - إعداد الظهور الخاص بك
   - حالة تسجيل دخول المستخدم
   - أخطاء وحدة تحكم المتصفح (F12)
   - لقطات شاشة إن أمكن

---

**Last Updated**: October 2025  
**Version**: 2.0  
**Feature**: Chat Visibility Settings  

---

## Summary - الملخص

The chat visibility settings give you complete control over who can see and use your AI chat assistant. Whether you want to:
- Provide support to everyone
- Create an exclusive member benefit
- Focus on pre-sales support

These settings make it easy to configure your chat exactly how you need it.

تمنحك إعدادات ظهور الشات تحكماً كاملاً في من يمكنه رؤية واستخدام مساعد الشات AI. سواء كنت تريد:
- تقديم الدعم للجميع
- إنشاء ميزة حصرية للأعضاء
- التركيز على دعم ما قبل البيع

تجعل هذه الإعدادات من السهل تكوين الشات الخاص بك بالضبط كما تحتاج.

**🎉 Enjoy your new chat visibility controls! استمتع بضوابط ظهور الشات الجديدة! 🎉**



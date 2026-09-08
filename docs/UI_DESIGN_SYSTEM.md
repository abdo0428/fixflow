# نظام التصميم في FixFlow

هذا المستند يشرح الهوية البصرية ومكونات الواجهة المشتركة المستخدمة في مشروع FixFlow. الهدف هو جعل التطبيق يبدو كمنتج SaaS عملي لشركات الصيانة والخدمات الميدانية، مع تقليل التكرار داخل صفحات Blade.

## الهوية البصرية

FixFlow يخدم فرق تشغيل يومية: إدارة طلبات، جدولة زيارات، فنيون ميدانيون، عملاء، فواتير وتقارير. لذلك تم اختيار أسلوب بصري هادئ وواضح:

- خلفية عامة فاتحة من درجات `slate`.
- بطاقات بيضاء بحدود خفيفة وظلال بسيطة.
- لون أساسي تقني هادئ يعتمد على `cyan`.
- ألوان حالة واضحة للنجاح والتحذير والخطر.
- نصف قطر حدود معتدل قريب من `8px`.
- واجهات قابلة للمسح السريع على الشاشات الكبيرة، وتتحول إلى أعمدة مريحة على الجوال.

## الألوان

الألوان معرفة كمتغيرات CSS داخل `resources/css/app.css`:

- `--ff-primary`: اللون الأساسي للأوامر الرئيسية والروابط المهمة.
- `--ff-primary-dark`: حالة hover أو التأكيد البصري للون الأساسي.
- `--ff-success`: الحالات الناجحة مثل `completed` و`paid`.
- `--ff-warning`: الحالات التي تحتاج متابعة مثل `waiting_parts` أو `draft`.
- `--ff-danger`: الحالات الخطرة أو النهائية السلبية مثل `cancelled` و`rejected`.
- `--ff-surface`: سطح البطاقات والنماذج.
- `--ff-muted`: الخلفية العامة الهادئة.
- `--ff-border`: حدود الجداول والبطاقات والحقول.

## مكونات الواجهة

تم إنشاء المكونات داخل `resources/views/components/ui` وتستخدم في الصفحات عبر بادئة `x-ui`.

### Card

يستخدم لتجميع محتوى مرتبط مثل جدول، نموذج، أو قائمة مختصرة.

```blade
<x-ui.card title="أحدث الطلبات" subtitle="آخر طلبات الصيانة داخل الشركة">
    محتوى البطاقة
</x-ui.card>
```

### Button

يدعم الروابط والأزرار العادية مع متغيرات لونية وحجمية.

```blade
<x-ui.button :href="route('service-requests.create')">
    طلب جديد
</x-ui.button>

<x-ui.button type="submit" variant="success">
    حفظ
</x-ui.button>
```

القيم المتاحة لـ `variant`:

- `primary`
- `secondary`
- `success`
- `warning`
- `danger`
- `ghost`

القيم المتاحة لـ `size`:

- `sm`
- `md`
- `lg`

### Badge

يعرض حالة أو أولوية بلون مناسب.

```blade
<x-ui.badge status="completed" />
<x-ui.badge status="urgent" />
<x-ui.badge variant="info">ساري الضمان</x-ui.badge>
```

أمثلة الحالات المدعومة:

- `active`, `completed`, `paid`
- `new`, `in_progress`, `on_the_way`
- `under_review`, `scheduled`, `issued`
- `waiting_parts`, `draft`, `high`
- `cancelled`, `rejected`, `inactive`, `suspended`, `urgent`

### Input

يستخدم للحقول النصية والتاريخية والرقمية.

```blade
<x-ui.input id="title" name="title" :value="old('title')" required />
<x-ui.input id="preferred_date" name="preferred_date" type="date" />
```

### Empty State

يستخدم عندما لا توجد بيانات في جدول أو قائمة.

```blade
<x-ui.empty-state
    title="لا توجد طلبات بعد."
    message="ابدأ بإضافة طلب صيانة جديد."
    :href="route('service-requests.create')"
    action="طلب جديد"
/>
```

### Stat Card

يستخدم للإحصائيات المختصرة في لوحات التحكم.

```blade
<x-ui.stat-card title="Open Requests" value="24" />
<x-ui.stat-card title="طلبات متأخرة" value="6" tone="danger" />
```

القيم المتاحة لـ `tone`:

- `primary`
- `info`
- `success`
- `warning`
- `danger`

### Page Header

يعرض عنوان الصفحة ووصفًا مختصرًا وأوامر سريعة.

```blade
<x-ui.page-header title="طلبات الصيانة" subtitle="متابعة الطلبات حسب الحالة والأولوية.">
    <x-slot name="actions">
        <x-ui.button :href="route('service-requests.create')">طلب جديد</x-ui.button>
    </x-slot>
</x-ui.page-header>
```

### Table

يوحد شكل الجداول مع عنوان اختياري وfooter للترقيم.

```blade
<x-ui.table
    title="الفواتير"
    :columns="['رقم الفاتورة', 'الطلب', 'الإجمالي', 'الحالة', '']"
    :footer="$invoices->links()"
>
    @foreach ($invoices as $invoice)
        <tr>
            <td>{{ $invoice->invoice_number }}</td>
            <td>{{ $invoice->serviceRequest?->title }}</td>
            <td>{{ number_format((float) $invoice->total, 2) }}</td>
            <td><x-ui.badge :status="$invoice->status" /></td>
            <td>...</td>
        </tr>
    @endforeach
</x-ui.table>
```

### Status Timeline

يعرض أحداث `AuditLog` المرتبطة بطلب الصيانة أو الزيارة.

```blade
<x-ui.status-timeline :items="$timeline" />
```

## Classes مشتركة

تم تعريف classes قابلة لإعادة الاستخدام داخل `resources/css/app.css`:

- `ff-shell`: خلفية التطبيق العامة.
- `ff-container`: حاوية موحدة للصفحات.
- `ff-page`: مسافات رأسية موحدة.
- `ff-card`: شكل البطاقة الأساسي.
- `ff-button`: قاعدة الأزرار.
- `ff-form-label`: عنوان الحقل.
- `ff-form-input`: حقول النماذج.
- `ff-badge`: قاعدة الشارات.
- `ff-table`: شكل الجداول.
- `ff-alert-success` و`ff-alert-danger`: رسائل الحالة.
- `ff-empty-state`: حالة عدم وجود بيانات.

## إرشادات الاستخدام

- لا تضع queries داخل Blade. استخدم Controller أو Service ثم مرر البيانات للواجهة.
- استخدم `x-ui.badge` لأي status أو priority بدل كتابة ألوان مباشرة في الصفحة.
- استخدم `x-ui.table` للجداول الإدارية مثل الطلبات والفواتير والزيارات.
- استخدم `x-ui.stat-card` فقط للأرقام المختصرة في Dashboard.
- حافظ على `company_id` والصلاحيات في Backend كما هي؛ نظام التصميم لا يحل محل Policies أو Middleware.
- عند إنشاء صفحة جديدة، ابدأ بـ `x-ui.page-header` ثم استخدم `ff-page` و`ff-container`.


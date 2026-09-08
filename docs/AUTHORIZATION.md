# المصادقة والصلاحيات في FixFlow

يعتمد FixFlow على Laravel Breeze للمصادقة، وعلى Spatie Laravel Permission للأدوار والصلاحيات. الحماية لا تعتمد على إخفاء الأزرار فقط؛ بل توجد ثلاث طبقات خلفية:

1. Middleware على المسارات مثل `auth`, `active.user`, `role`, و`can`.
2. Policies لكل موديل مهم.
3. Global scope باسم `company` على الموديلات التشغيلية لمنع تسريب بيانات شركة إلى أخرى عند تنفيذ الاستعلامات العادية.

## الأدوار

- `super_admin`: يرى ويدير كل الشركات على مستوى المنصة.
- `company_admin`: يدير بيانات شركته فقط.
- `dispatcher`: يدير العملاء والأجهزة وطلبات الصيانة والجدولة داخل شركته فقط.
- `technician`: يرى الطلبات المرتبطة بزيارات مسندة له، ويحدث زياراته المسندة فقط.
- `customer`: يرى أجهزته وطلبات الصيانة والفواتير المرتبطة بملفه فقط.

## الصلاحيات

الصلاحيات الأساسية المزروعة عبر `RolePermissionSeeder`:

- `manage companies`
- `manage company users`
- `manage customers`
- `manage assets`
- `manage service requests`
- `assign technicians`
- `update assigned visits`
- `manage parts`
- `manage invoices`
- `view reports`
- `manage settings`

## طبقة عزل الشركات

تم إنشاء trait باسم `App\Models\Concerns\BelongsToCompany` وإضافته إلى الموديلات التشغيلية مثل:

- `Customer`
- `ServiceAsset`
- `ServiceRequest`
- `ServiceVisit`
- `Part`
- `PartUsed`
- `ServiceReport`
- `Invoice`
- `AuditLog`

عند وجود مستخدم مسجل وليس `super_admin`، يضيف الـ global scope شرطًا تلقائيًا:

```php
where('{table}.company_id', auth()->user()->company_id)
```

هذا لا يغني عن السياسات، لكنه يقلل خطر نسيان شرط `company_id` داخل الاستعلامات.

## حماية المسارات

أمثلة من `routes/web.php`:

```php
Route::get('/dispatch/service-requests', [DashboardController::class, 'dispatcher'])
    ->middleware(['role:dispatcher', 'can:viewAny,'.ServiceRequest::class])
    ->name('dispatch.service-requests.index');

Route::get('/technician/visits', [DashboardController::class, 'technician'])
    ->middleware(['role:technician', 'can:viewAny,'.ServiceVisit::class])
    ->name('technician.visits.index');
```

وجود `role` يمنع الدخول بالدور الخطأ، ووجود `can` يجعل Policy هي القرار النهائي.

## التوجيه بعد تسجيل الدخول

بعد تسجيل الدخول، يستخدم Breeze الآن `DashboardRoute` لتوجيه المستخدم:

- `super_admin` إلى `/platform/dashboard`.
- `company_admin` إلى `/company/dashboard`.
- `dispatcher` إلى `/dispatch/service-requests`.
- `technician` إلى `/technician/visits`.
- `customer` إلى `/customer/portal`.

إذا لم يكن للمستخدم دور، يبقى في `/dashboard` الافتراضية.

## مثال: منع technician من رؤية طلب غير مسند له

داخل `ServiceRequestPolicy`:

```php
if ($user->hasRole('technician')) {
    return $serviceRequest->visits()
        ->where('technician_id', $user->id)
        ->exists();
}
```

المعنى العملي: الفني لا يرى كل طلبات الشركة، بل يرى الطلب فقط إذا وُجدت زيارة مرتبطة بالطلب ومسندة إلى هذا الفني.

يوجد اختبار يغطي هذا السلوك في `AuthorizationAccessTest`:

```php
$this->assertFalse($technician->can('view', $request));

ServiceVisit::factory()->create([
    'service_request_id' => $request->id,
    'technician_id' => $technician->id,
]);

$this->assertTrue($technician->can('view', $request));
```

## مثال: منع company_admin من رؤية بيانات شركة أخرى

داخل السياسات، يتم التحقق من الشركة:

```php
return $user->company_id !== null
    && (int) $user->company_id === (int) $model->company_id;
```

هذا يعني أن `company_admin` يستطيع إدارة العملاء أو الأجهزة أو الطلبات التابعة لشركته فقط. وإذا نُفذ استعلام عادي مثل:

```php
Customer::query()->count();
```

فإن global scope يضيف شرط الشركة تلقائيًا للمستخدم غير `super_admin`.

## مثال: كيف يرى customer طلباته فقط

داخل `DashboardController@customer`:

```php
$customer = Customer::where('user_id', $request->user()->id)->first();

$requests = $customer
    ? ServiceRequest::with(['serviceAsset', 'invoice'])
        ->where('customer_id', $customer->id)
        ->latest()
        ->limit(8)
        ->get()
    : collect();
```

وداخل `ServiceRequestPolicy`:

```php
if ($user->hasRole('customer')) {
    return (int) $serviceRequest->customer?->user_id === (int) $user->id;
}
```

بهذا يكون العميل محميًا من الجهتين: الاستعلام يجلب طلباته فقط، والـ Policy تمنع الوصول المباشر إلى طلب لا يخصه.

## الاختبارات

الاختبارات الحالية تغطي:

- زرع الأدوار والصلاحيات.
- منع الفني من رؤية طلب غير مسند له.
- منع مدير شركة من رؤية عميل شركة أخرى.
- تأكيد أن customer portal يعرض طلبات وأجهزة العميل نفسه فقط.
- منع الدخول لمسارات workflow بدور غير مناسب.
- توجيه المستخدم بعد تسجيل الدخول حسب دوره.
- عرض صفحة dashboard الخاصة بكل دور.
- منع تسجيل دخول المستخدم غير النشط.

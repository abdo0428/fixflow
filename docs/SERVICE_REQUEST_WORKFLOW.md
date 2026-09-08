# دورة حياة طلب الصيانة

يدير FixFlow طلب الصيانة كـ workflow واضح، وليس كـ CRUD بسيط. كل انتقال حالة يمر عبر `ServiceRequestWorkflowService` حتى تطبق القواعد نفسها من أي واجهة.

## الحالات

| الحالة | المعنى |
| --- | --- |
| `new` | طلب جديد وصل من عميل أو موظف. |
| `under_review` | الطلب قيد المراجعة من فريق الشركة. |
| `scheduled` | تم تحديد موعد أو تعيين فني. |
| `in_progress` | الفني بدأ التنفيذ. |
| `waiting_parts` | التنفيذ متوقف مؤقتًا بانتظار قطع غيار. |
| `completed` | الطلب مكتمل ومغلق. |
| `cancelled` | الطلب ملغى. |
| `rejected` | الطلب مرفوض. |

## قواعد الانتقال

| من | إلى |
| --- | --- |
| `new` | `under_review`, `rejected` |
| `under_review` | `scheduled`, `rejected` |
| `scheduled` | `in_progress`, `cancelled` |
| `in_progress` | `waiting_parts`, `completed` |
| `waiting_parts` | `in_progress`, `completed` |
| `completed` | لا ينتقل عادة، ولا يعدل إلا مدير الشركة. |
| `cancelled` | حالة نهائية. |
| `rejected` | حالة نهائية. |

## من يستطيع تغيير الحالة

- `company_admin`: يدير طلبات شركته، ويستطيع تعديل الطلب المكتمل عند الحاجة.
- `dispatcher`: يراجع الطلبات ويجدولها ويرفضها أو يلغيها داخل شركته فقط.
- `technician`: يحدث الطلبات المسندة إليه فقط إلى `in_progress`, `waiting_parts`, أو `completed`.
- `customer`: ينشئ الطلب ويتابع حالته، ولا يغير workflow من الخلفية.
- `super_admin`: يرى المنصة كاملة لأغراض الإدارة العامة.

## مثال عملي

1. العميل يفتح طلب صيانة لمكيف من بوابة العميل.
2. ينشأ الطلب بحالة `new`.
3. يصل Notification إلى الـ dispatcher داخل نفس الشركة.
4. يحول الـ dispatcher الطلب إلى `under_review`.
5. يعين الـ dispatcher أو `company_admin` فنيًا للطلب.
6. ينشئ النظام `ServiceVisit` ويحول الطلب إلى `scheduled`.
7. يرى الفني الزيارة في بوابته.
8. يبدأ الفني الزيارة، فيتحول الطلب إلى `in_progress`.
9. إذا احتاج قطع غيار يمكن تحويله إلى `waiting_parts`.
10. عند إكمال العمل يضيف الفني التشخيص والحل والقطع المستخدمة والصور.
11. ينشئ النظام `ServiceReport` ويغلق الطلب بحالة `completed`.
12. يرى العميل التقرير النهائي والفاتورة من صفحة طلباته.

## السجل الزمني

الأحداث المهمة تسجل في `audit_logs`، مثل:

- `service_request_created`
- `service_request_status_changed`
- `technician_assigned`
- `service_report_added`
- `service_visit_started`
- `service_visit_completed`
- `service_request_closed`

يعتمد الـ Timeline في صفحات التفاصيل على هذه الأحداث بدل تخزين نصوص منفصلة داخل الواجهة.

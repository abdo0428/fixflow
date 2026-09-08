<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <title>Service Report #{{ $serviceRequest->id }}</title>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                direction: rtl;
                color: #111827;
                font-size: 12px;
                line-height: 1.6;
            }

            .header {
                border-bottom: 2px solid #111827;
                margin-bottom: 18px;
                padding-bottom: 12px;
            }

            h1, h2 {
                margin: 0;
            }

            h1 {
                font-size: 22px;
            }

            h2 {
                font-size: 15px;
                margin-bottom: 8px;
            }

            .grid {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 14px;
            }

            .grid td, .grid th {
                border: 1px solid #d1d5db;
                padding: 7px;
                vertical-align: top;
            }

            .label {
                color: #6b7280;
                font-weight: bold;
                width: 28%;
            }

            .section {
                margin-bottom: 16px;
            }

            .total {
                font-weight: bold;
                background: #f3f4f6;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>تقرير صيانة</h1>
            <div>طلب رقم: #{{ $serviceRequest->id }}</div>
            <div>تاريخ التوليد: {{ now()->format('Y-m-d H:i') }}</div>
        </div>

        <div class="section">
            <h2>بيانات الشركة</h2>
            <table class="grid">
                <tr>
                    <td class="label">الشركة</td>
                    <td>{{ $serviceRequest->company?->name }}</td>
                </tr>
                <tr>
                    <td class="label">البريد</td>
                    <td>{{ $serviceRequest->company?->email }}</td>
                </tr>
                <tr>
                    <td class="label">الهاتف</td>
                    <td>{{ $serviceRequest->company?->phone }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>بيانات العميل والجهاز</h2>
            <table class="grid">
                <tr>
                    <td class="label">العميل</td>
                    <td>{{ $serviceRequest->customer?->name }}</td>
                </tr>
                <tr>
                    <td class="label">الجهاز</td>
                    <td>{{ $serviceRequest->serviceAsset?->name }}</td>
                </tr>
                <tr>
                    <td class="label">النوع</td>
                    <td>{{ $serviceRequest->serviceAsset?->type }}</td>
                </tr>
                <tr>
                    <td class="label">الماركة والموديل</td>
                    <td>{{ $serviceRequest->serviceAsset?->brand }} {{ $serviceRequest->serviceAsset?->model }}</td>
                </tr>
                <tr>
                    <td class="label">الرقم التسلسلي</td>
                    <td>{{ $serviceRequest->serviceAsset?->serial_number }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>تفاصيل الطلب</h2>
            <table class="grid">
                <tr>
                    <td class="label">عنوان المشكلة</td>
                    <td>{{ $serviceRequest->title }}</td>
                </tr>
                <tr>
                    <td class="label">وصف المشكلة</td>
                    <td>{{ $serviceRequest->description }}</td>
                </tr>
                <tr>
                    <td class="label">التشخيص</td>
                    <td>{{ $report->diagnosis }}</td>
                </tr>
                <tr>
                    <td class="label">الحل</td>
                    <td>{{ $report->solution }}</td>
                </tr>
                <tr>
                    <td class="label">الفني</td>
                    <td>{{ $report->technician?->name }}</td>
                </tr>
                <tr>
                    <td class="label">تاريخ الزيارة</td>
                    <td>{{ optional($visit?->scheduled_at)->format('Y-m-d H:i') ?: 'غير محدد' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>قطع الغيار المستخدمة</h2>
            <table class="grid">
                <thead>
                    <tr>
                        <th>القطعة</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($serviceRequest->partsUsed as $partUsed)
                        <tr>
                            <td>{{ $partUsed->part?->name }}</td>
                            <td>{{ $partUsed->quantity }}</td>
                            <td>{{ number_format((float) $partUsed->unit_price, 2) }}</td>
                            <td>{{ number_format($partUsed->quantity * (float) $partUsed->unit_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">لا توجد قطع غيار مستخدمة.</td>
                        </tr>
                    @endforelse
                    <tr class="total">
                        <td colspan="3">إجمالي قطع الغيار</td>
                        <td>{{ number_format($partsTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>توقيع العميل</h2>
            <div>{{ $report->customer_signature ?: 'لا يوجد توقيع مسجل.' }}</div>
        </div>
    </body>
</html>


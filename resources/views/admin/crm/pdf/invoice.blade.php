<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    @include('admin.crm.documents.styles-invoice', ['documentMode' => 'pdf'])
    <style>
        @page { margin: 28px 30px 36px; }
        body { margin: 0; padding: 0; }
    </style>
</head>
<body>
@include('admin.crm.documents.invoice-body', ['documentMode' => 'pdf', 'doc' => $doc ?? null, 'organization' => $organization ?? null])
</body>
</html>

<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<title>Invoice</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
  <tr>
    <td align="center">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;border:1px solid #e5e7eb;">
        <!-- Header -->
        <tr>
          <td style="padding:24px;border-bottom:1px solid #e5e7eb;">
            <table width="100%">
              <tr>
                <td align="left">
                  <table cellpadding="0" cellspacing="0">
                    <tr>
                      <!-- <td style="background:#0ea5a4;color:#fff;font-weight:bold;font-size:20px;padding:16px 20px;border-radius:8px;">TI</td> -->
                       <td>
                        <img src="https://register.alrushd.co.uk/frontend/assets/img/logo.png" alt="" style="width: 150px;">
                       </td>
                      <td style="padding-left:10px;">
                        <div style="font-size:18px;font-weight:bold;color:#0f172a;">Al-rushd Online School</div>
                        <div style="font-size:13px;color:#6b7280;">Address: Unit 8, Church Road Studios 62 Church Road London E12 6AF</div>
                        <div style="font-size:13px;color:#6b7280;">admin@alrushd.co.uk</div>
                        <div style="font-size:13px;color:#6b7280;">+442036330757</div>
                      </td>
                    </tr>
                  </table>
                </td>
                <td align="right" style="font-size:13px;color:#6b7280;">
                  <div style="font-weight:bold;font-size:16px;color:#0f172a;">INVOICE</div>
                  <div>Invoice #: <strong>INV-{{ $submission->id }}</strong></div>
                  <div>Issue Date: <strong>{{ $submission->payment_date ? $submission->payment_date : '' }}</strong></div>
                  <div>Status: <strong>{{ ucfirst($submission->status) }}</strong></div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Customer Info -->
        <tr>
          <td style="padding:24px;font-size:14px;color:#0f172a;">
            <strong>Customer Information:</strong><br>
            {{ $submission->fname }} {{ $submission->lname }}<br>
            Email: {{ $submission->email }}<br>
            Phone: {{ $submission->mobile_number }}<br>
            Address: {{ $submission->address }}, {{ $submission->apartment }}, {{ $submission->city }}, {{ $submission->province }}, {{ $submission->postal_code }}, {{ $submission->country }}<br>
            Card Holder: {{ $submission->card_holder_name }}<br>
            Currency: {{ $submission->currency }}<br>
            Transaction ID: {{ $submission->transaction_id }}
          </td>
        </tr>

        <!-- Student Info -->
        @if($submission->students->count())
        <tr>
          <td style="padding:0 24px 24px 24px;">
            <strong>Students Information:</strong>
            <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse:collapse;font-size:14px;border:1px solid #e5e7eb;">
              <thead>
                <tr style="background:#f9fafb;">
                  <th align="left" style="padding:5px;border-bottom:1px solid #e5e7eb;">Name</th>
                  <th align="left" style="padding:5px;border-bottom:1px solid #e5e7eb;">DOB</th>
                  <th align="left" style="padding:5px;border-bottom:1px solid #e5e7eb;">Gender</th>
                  <th align="left" style="padding:5px;border-bottom:1px solid #e5e7eb;">Nationality</th>
                  <th align="left" style="padding:5px;border-bottom:1px solid #e5e7eb;">Start Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach($submission->students as $student)
                <tr>
                  <td>{{ $student->fname }} {{ $student->lname }}</td>
                  <td>{{ $student->dob }}</td>
                  <td>{{ $student->gender }}</td>
                  <td>{{ $student->nationality }}</td>
                  <td>{{ $student->start_date }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </td>
        </tr>
        @endif

        <!-- Total Amount -->
        <tr>
          <td style="padding:0 24px 24px 24px;">
            <table align="right" cellpadding="0" cellspacing="0" style="width:280px;font-size:14px;">
              <tr><td style="color:#6b7280;">Total Amount:</td><td align="right">৳ {{ number_format($submission->total_amount,2) }}</td></tr>
              <tr><td style="color:#6b7280;">Paid Amount:</td><td align="right">৳ {{ number_format($submission->paid_amount,2) }}</td></tr>
              <tr><td style="border-top:1px solid #e5e7eb;font-weight:bold;color:#0f172a;">Due Amount:</td><td align="right" style="font-weight:bold;color:#0f172a;">৳ {{ number_format($submission->total_amount - $submission->paid_amount,2) }}</td></tr>
            </table>
          </td>
        </tr>

        <!-- Notes -->
        <tr>
          <td style="padding:20px;background:#f9fafb;font-size:13px;color:#6b7280;">
            <strong>Notes:</strong> Your payment has been received successfully. Keep this invoice for your records.<br><br>
            <em>Prepared by:</em> Al-Rushd Online School

          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>


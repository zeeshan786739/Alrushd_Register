@foreach($leads as $lead)
    @include('admin.crm.leads.partials.list-row', [
        'lead' => $lead,
        'statusInlineOptions' => $statusInlineOptions,
        'assigneeInlineOptions' => $assigneeInlineOptions,
    ])
@endforeach

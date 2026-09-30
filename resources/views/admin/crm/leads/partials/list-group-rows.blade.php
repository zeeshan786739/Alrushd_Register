@foreach($leads as $lead)
    @include('admin.crm.leads.partials.list-row', [
        'lead' => $lead,
        'statusInlineOptions' => $statusInlineOptions,
        'priorityInlineOptions' => $priorityInlineOptions,
        'assigneeInlineOptions' => $assigneeInlineOptions,
    ])
@endforeach

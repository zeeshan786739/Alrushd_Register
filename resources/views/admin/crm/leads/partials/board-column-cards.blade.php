@foreach($leads as $lead)
    @include('admin.crm.leads.partials.board-card', [
        'lead' => $lead,
        'priorityInlineOptions' => $priorityInlineOptions,
        'assigneeInlineOptions' => $assigneeInlineOptions,
    ])
@endforeach

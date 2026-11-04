@foreach($leads as $lead)
    @include('admin.crm.leads.partials.board-card', [
        'lead' => $lead,
        'assigneeInlineOptions' => $assigneeInlineOptions,
    ])
@endforeach

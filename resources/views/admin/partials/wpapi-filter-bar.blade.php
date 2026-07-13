@include('admin.partials.filter-bar', [
    'action' => $action,
    'resetUrl' => $resetUrl ?? $action,
    'fields' => [
        ['name' => 'search', 'label' => 'Search', 'placeholder' => $searchPlaceholder ?? 'Search name, email, or date…'],
        ['name' => 'start_date', 'label' => 'From', 'type' => 'date'],
        ['name' => 'end_date', 'label' => 'To', 'type' => 'date'],
    ],
])

<?php

return [
    'max_bytes' => (int) env('LEAD_IMPORT_MAX_BYTES', 10 * 1024 * 1024),
    'max_rows' => (int) env('LEAD_IMPORT_MAX_ROWS', 10000),
    'preview_rows' => (int) env('LEAD_IMPORT_PREVIEW_ROWS', 50),
    'disk' => env('LEAD_IMPORT_DISK', 'local'),
];

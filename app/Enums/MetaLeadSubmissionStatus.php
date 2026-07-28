<?php

namespace App\Enums;

enum MetaLeadSubmissionStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Unmapped = 'unmapped';
    case Failed = 'failed';
}

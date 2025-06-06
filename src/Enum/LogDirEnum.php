<?php

namespace App\Enum;

enum LogDirEnum: string
{
    case EXCEL_IMPORT = '/imports';
    case LOG_ENTRY = '/logs';
}

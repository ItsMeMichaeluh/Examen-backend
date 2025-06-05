<?php

namespace App\Enum;

enum LogTypeEnum: string
{
    case FILE_IMPORT = 'file_import';
    case FILE_EXPORT = 'file_export';
}

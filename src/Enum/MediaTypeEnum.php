<?php

namespace App\Enum;

enum MediaTypeEnum: string
{
    case EXCEL_IMPORT = 'excel_import';
    case LOG_ENTRY = 'log_entry';
    case PDF_EXPORT = 'pdf_export';
}

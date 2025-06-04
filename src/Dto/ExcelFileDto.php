<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class ExcelFileDto {
    #[Assert\NotNull(message: 'No file provided.')]
    #[Assert\File(
        maxSize: '10M',
        mimeTypes: [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet'
        ],
        maxSizeMessage: 'Maximum file size is 10MB.',
        mimeTypesMessage: 'Allowed types: .xlsx and .ods'
    )]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'format' => 'binary',
        ]
    )]
    public ?UploadedFile $file = null;
}

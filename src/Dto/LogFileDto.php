<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class LogFileDto {
    #[Assert\NotNull(message: 'No file provided.')]
    #[Assert\File(
        maxSize: '10M',
        mimeTypes: [
            'text/plain',
            'text/csv',
            'application/octet-stream',
            'application/vnd.ms-excel',
        ],
        maxSizeMessage: 'Maximum file size is 10MB.',
        mimeTypesMessage: 'Please upload a valid log or spreadsheet file.',
    )]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'format' => 'binary',
        ]
    )]
    public ?UploadedFile $file = null;
}

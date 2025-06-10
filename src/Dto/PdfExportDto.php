<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

class PdfExportDto {
    #[Groups(['media_object:write'])]
    public ?array $students = null;
}

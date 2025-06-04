<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetProcessor implements ProcessorInterface
{
    public function __construct(
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $fileName = $data->getFileName();

        $formats = [
            \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
            \PhpOffice\PhpSpreadsheet\IOFactory::READER_ODS,
        ];
    }
}

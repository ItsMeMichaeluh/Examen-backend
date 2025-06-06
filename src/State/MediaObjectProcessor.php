<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MediaObject;
use App\Enum\MediaTypeEnum;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class MediaObjectProcessor implements ProcessorInterface
{
    public function __construct(
        private ExcelImportProcessor $excelImportProcessor,
        private LogEntryProcessor    $logEntryProcessor,
        private RequestStack         $requestStack
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MediaObject
    {
        $request = $this->requestStack->getCurrentRequest();
        switch (MediaTypeEnum::tryFrom($request->get('type'))) {
            case MediaTypeEnum::EXCEL_IMPORT:
                return $this->excelImportProcessor->process($data, $operation, $uriVariables, $context);
            case MediaTypeEnum::LOG_ENTRY:
                return $this->logEntryProcessor->process($data, $operation, $uriVariables, $context);
            case null:
                dump('hello');
                $request->files->get('file');
        }

        throw new BadRequestHttpException('invalid data?');
    }
}

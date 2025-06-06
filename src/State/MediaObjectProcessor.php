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
        return match (MediaTypeEnum::tryFrom($request->get('type'))) {
            MediaTypeEnum::EXCEL_IMPORT => $this->excelImportProcessor->process($data, $operation, $uriVariables, $context),
            MediaTypeEnum::LOG_ENTRY => $this->logEntryProcessor->process($data, $operation, $uriVariables, $context),
            default => throw new BadRequestHttpException('invalid data?'),
        };
    }
}

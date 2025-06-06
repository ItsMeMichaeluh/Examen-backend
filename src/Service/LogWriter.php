<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\MediaObject;
use App\Enum\LogTypeEnum;
use App\Enum\MediaTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

readonly class LogWriter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function CreateEntry(string $fileName, MediaTypeEnum $type, array $logLines): void
    {
        $fs = new Filesystem();
        $path = sprintf('%s/%s-%s.txt', sys_get_temp_dir(), LogTypeEnum::EXCEL_IMPORT->value, $fileName);

        $normalizedLogLines = array_map(
        /**
         * @throws \JsonException
         */
        function ($line) {
            if (is_scalar($line)) {
                return (string)$line;
            }
            return json_encode($line, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }, $logLines);
        $fs->dumpFile($path, implode("\n", $normalizedLogLines));

        $mediaObject = new MediaObject();
        $mediaObject->setFile(new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $path,
            $fileName,
            'text/plain',
            null,
            true // $test = true allows non-HTTP-uploaded files
        ));

        // Optionally set the type if MediaObject has a type or similar flag
        $mediaObject->setType($type);

        $this->entityManager->persist($mediaObject);

        $log = new Log();
        $log->setMediaObject($mediaObject);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $fs->remove($path);
    }
}

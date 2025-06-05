<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\MediaObject;
use App\Enum\LogTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class LogWriter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function CreateEntry(LogTypeEnum $type, array $logLines)
    {
        $log = new Log();
        $log->setType($type);

        $mediaObject = new MediaObject();
        $fs = new Filesystem();
        $fs->dumpFile(__DIR__.'/var/temp/log.txt', ...$logLines);
        $fs->remove(__DIR__.'/var/temp/log.txt');
        // create log file and set mediaObject
        // link mediaobject to log
    }
}

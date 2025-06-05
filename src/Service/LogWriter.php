<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

readonly class LogWriter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        string                         $logDir = __DIR__.'/srv/'
    ) {}
}

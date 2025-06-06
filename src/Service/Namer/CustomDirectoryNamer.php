<?php

namespace App\Service\Namer;

use App\Entity\MediaObject;
use App\Enum\MediaTypeEnum;
use JetBrains\PhpStorm\NoReturn;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;


class CustomDirectoryNamer implements DirectoryNamerInterface
{

    /**
     * @inheritDoc
     * @throws \ErrorException
     */
    #[NoReturn]
    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if (!$object instanceof MediaObject) {
            throw new \ErrorException('invalid object received');
        }

        return match($object->getType()) {
            MediaTypeEnum::LOG_ENTRY => 'logs',
            MediaTypeEnum::EXCEL_IMPORT => 'imports',
            default => '/media'
        };
    }
}

<?php

namespace App\Service\Namer;

use JetBrains\PhpStorm\NoReturn;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;


class CustomDirectoryNamer implements DirectoryNamerInterface
{

    /**
     * @inheritDoc
     */
    #[NoReturn]
    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        dump($object, $mapping);
    }
}

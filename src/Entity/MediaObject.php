<?php
// api/src/Entity/MediaObject.php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Dto\ExcelFileDto;
use App\State\ExcelImportProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            uriTemplate: '/media_objects/import',
            inputFormats: ['multipart' => ['multipart/form-data']],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            ),
            input: ExcelFileDto::class,
            deserialize: false,
            processor: ExcelImportProcessor::class
        ),
    ],
    outputFormats: ['jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['media_object:read']]
)]
class MediaObject
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ApiProperty(writable: false, types: ['https://schema.org/contentUrl'])]
    #[Groups(['media_object:read'])]
    public ?string $contentUrl = null;

    #[Vich\UploadableField(mapping: 'app_data', fileNameProperty: 'filePath')]
    #[Assert\NotNull]
    public ?File $file = null;

    #[ApiProperty(writable: false)]
    #[ORM\Column(nullable: true)]
    public ?string $filePath = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}

<?php
// api/src/Entity/MediaObject.php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Dto\ExcelFileDto;
use App\Dto\PdfExportDto;
use App\Enum\MediaTypeEnum;
use App\State\MediaObjectProcessor;
use App\State\PdfExportProcessor;
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
                                    ],
                                    'type' => [
                                        'type' => 'string',
                                        'enum' => [
                                            MediaTypeEnum::EXCEL_IMPORT->value,
                                            MediaTypeEnum::LOG_ENTRY->value
                                        ]
                                    ]
                                ],
                                'required' => ['file', 'type']
                            ]
                        ]
                    ])
                )
            ),
            input: ExcelFileDto::class,
            deserialize: false,
            processor: MediaObjectProcessor::class
        ),
        new Post(
            uriTemplate: '/media_objects/export_pdf',
            normalizationContext: ['groups' => ['media_object:read']],
            denormalizationContext: ['groups' => ['media_object:write']],
            input: PdfExportDto::class,
            output: MediaObject::class,
            deserialize: true,
            processor: PdfExportProcessor::class,
        )
    ],
    outputFormats: ['jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['media_object:read']]
)]
#[ApiFilter(SearchFilter::class, strategy: 'exact', properties: ['type', 'filePath'])]
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

    #[ORM\Column(type: 'string', enumType: MediaTypeEnum::class)]
    #[Groups(['media_object:read'])]
    #[Assert\NotNull]
    public ?MediaTypeEnum $type = null;

    #[ApiProperty(writable: false)]
    #[ORM\Column(nullable: true)]
    #[Groups(['log:read', 'media_object:read'])]
    public ?string $filePath = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        if ($this->getCreatedAt() === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    public function getType(): ?MediaTypeEnum
    {
        return $this->type;
    }

    public function setType(?MediaTypeEnum $type): void
    {
        $this->type = $type;
    }

    public function getContentUrl(): ?string
    {
        return $this->filePath ? '/app-data/' . match ($this->getType()) {
                MediaTypeEnum::EXCEL_IMPORT => 'imports/',
                MediaTypeEnum::LOG_ENTRY => 'logs/',
                MediaTypeEnum::PDF_EXPORT => 'exports/',
            } . ltrim($this->filePath, '/') : null;
    }

    public function setContentUrl(?string $contentUrl): void
    {
        $this->contentUrl = $contentUrl;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

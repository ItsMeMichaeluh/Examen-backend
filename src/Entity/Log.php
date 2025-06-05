<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\LogTypeEnum;
use App\Repository\LogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogRepository::class)]
#[ApiResource]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?MediaObject $mediaObject = null;

    #[ORM\Column(type: 'string', enumType: LogTypeEnum::class)]
    private ?LogTypeEnum $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMediaObject(): ?MediaObject
    {
        return $this->mediaObject;
    }

    public function setMediaObject(MediaObject $mediaObject): static
    {
        $this->mediaObject = $mediaObject;

        return $this;
    }

    public function getType(): ?LogTypeEnum
    {
        return $this->type;
    }

    public function setType(?LogTypeEnum $type): void
    {
        $this->type = $type;
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

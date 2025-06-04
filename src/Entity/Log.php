<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
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

    private ?string $type = null;

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
}

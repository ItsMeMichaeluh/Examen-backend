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

    #[ORM\Column]
    private ?int $mediaobjectId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMediaobjectId(): ?int
    {
        return $this->mediaobjectId;
    }

    public function setMediaobjectId(int $mediaobjectId): static
    {
        $this->mediaobjectId = $mediaobjectId;

        return $this;
    }
}

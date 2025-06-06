<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Dto\BulkStudentInputDto;
use App\Repository\StudentRepository;
use App\State\StudentActivityProcessor;
use App\State\StudentGroupProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Patch(
            uriTemplate: '/students/{studentNumber}/start',
            input: false,
            processor: StudentActivityProcessor::class,
            extraProperties: ['action' => 'start'],
        ),
        new Patch(
            uriTemplate: '/students/{studentNumber}/stop',
            input: false,
            processor: StudentActivityProcessor::class,
            extraProperties: ['action' => 'stop'],
        ),
    ],
    normalizationContext: [
        'groups' => ['student:read']
    ],
)]
#[ApiFilter(BooleanFilter::class, properties: ['active'])]
class Student
{
    #[ORM\Id]
    #[ORM\Column(length: 45)]
    #[Groups(['student:read', 'group:read'])]
    #[ApiProperty(identifier: true)]
    private ?string $studentNumber = null;

    #[ORM\Column]
    #[Groups(['student:read', 'group:read'])]
    private bool $active = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $StoppedAt = null;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'student', fetch: "EAGER")]
    #[Groups(['student:read', 'group:read'])]
    private Collection $attendances;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    #[SerializedName('group')]
    #[Groups(['student:read'])]
    private ?Group $referencedGroup = null;

    public function __construct()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable("now");
        }
        $this->updatedAt = new \DateTimeImmutable("now");
        $this->attendances = new ArrayCollection();
    }

    public function getStudentNumber(): ?string
    {
        return $this->studentNumber;
    }

    public function setStudentNumber(?string $studentNumber): void
    {
        $this->studentNumber = $studentNumber;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getStoppedAt(): ?\DateTimeImmutable
    {
        return $this->StoppedAt;
    }

    public function setStoppedAt(?\DateTimeImmutable $StoppedAt): void
    {
        $this->StoppedAt = $StoppedAt;
    }

    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function setAttendances(Collection $attendances): void
    {
        $this->attendances = $attendances;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getReferencedGroup(): ?Group
    {
        return $this->referencedGroup;
    }

    public function setReferencedGroup(?Group $referencedGroup): void
    {
        $this->referencedGroup = $referencedGroup;
    }
}

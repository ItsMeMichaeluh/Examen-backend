<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
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
        new Patch(
            uriTemplate: '/students/{studentNumber}/group/{groupId}',
            processor: StudentGroupProcessor::class,
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
    #[Groups(['student:read'])]
    private ?string $studentNumber = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private bool $active = true;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'student', fetch: "EAGER")]
    #[Groups(['student:read'])]
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

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $StoppedAt = null;

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

    public function setStudentNumber(string $studentNumber): static
    {
        $this->studentNumber = $studentNumber;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): static
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances->add($attendance);
            $attendance->setStudent($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): static
    {
        if ($this->attendances->removeElement($attendance)) {
            // set the owning side to null (unless already changed)
            if ($attendance->getStudent() === $this) {
                $attendance->setStudent(null);
            }
        }

        return $this;
    }

    public function getReferencedGroup(): ?Group
    {
        return $this->referencedGroup;
    }

    public function setReferencedGroup(?Group $referencedGroup): void
    {
        $this->referencedGroup = $referencedGroup;
    }

    public function getStoppedAt(): ?\DateTimeImmutable
    {
        return $this->StoppedAt;
    }

    public function setStoppedAt(?\DateTimeImmutable $StoppedAt): static
    {
        $this->StoppedAt = $StoppedAt;

        return $this;
    }
}

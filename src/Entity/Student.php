<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ApiResource(
    normalizationContext: [
        'groups' => ['student:read']
    ]
)]
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
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AttendanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AttendanceRepository::class)]
#[ApiResource]
#[ORM\UniqueConstraint(fields: ['student', 'year', 'week'])]
class Attendance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'attendances')]
    #[ORM\JoinColumn(referencedColumnName: 'student_number', nullable: false)]
    private ?Student $student = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $year = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $week = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $scheduled = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $logged = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(int $week): static
    {
        $this->week = $week;

        return $this;
    }

    public function getScheduled(): ?int
    {
        return $this->scheduled;
    }

    public function setScheduled(int $scheduled): static
    {
        $this->scheduled = $scheduled;

        return $this;
    }

    public function getLogged(): ?int
    {
        return $this->logged;
    }

    public function setLogged(int $logged): static
    {
        $this->logged = $logged;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;

        return $this;
    }
}

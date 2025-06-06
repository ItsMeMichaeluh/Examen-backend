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
    private ?int $logged = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $scheduled = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $week = null;

    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $year = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): void
    {
        $this->student = $student;
    }

    public function getLogged(): ?int
    {
        return $this->logged;
    }

    public function setLogged(?int $logged): void
    {
        $this->logged = $logged;
    }

    public function getScheduled(): ?int
    {
        return $this->scheduled;
    }

    public function setScheduled(?int $scheduled): void
    {
        $this->scheduled = $scheduled;
    }

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(?int $week): void
    {
        $this->week = $week;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): void
    {
        $this->year = $year;
    }
}

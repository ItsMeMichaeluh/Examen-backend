<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\BulkStudentInputDto;
use App\Repository\GroupRepository;
use App\State\StudentGroupProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`group`')]
#[ORM\UniqueConstraint(name: 'UNIQ_GROUP_NAME', columns: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'The name is already in use.')]
#[ApiResource(
    operations: [
        new Post(),
        new Get(),
        new GetCollection(),
        new Patch(
            uriTemplate: '/groups/{id}/add-students',
            input: BulkStudentInputDto::class,
            processor: StudentGroupProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['group:read']],
    denormalizationContext: ['groups' => ['group:write']],
)]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: true)]
    private int $id;

    #[ORM\Column(length: 45)]
    #[Groups(['group:read', 'group:write'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Student>
     */
    #[ORM\OneToMany(targetEntity: Student::class, mappedBy: 'referencedGroup')]
    #[Groups(['group:read'])]
    private Collection $students;

    public function __construct()
    {
        $this->students = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }
}

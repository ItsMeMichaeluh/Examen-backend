<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\BulkStudentInputDto;
use App\Entity\Student;
use App\Repository\GroupRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class StudentGroupProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StudentRepository      $studentRepository,
        private GroupRepository        $groupRepository,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $groupId = (int) $uriVariables['id'];
        $group = $this->groupRepository->find($groupId);

        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        if (!is_a($data, BulkStudentInputDto::class)) {
            throw new BadRequestHttpException('Invalid data format');
        }

        if (empty($data->studentNumbers)) {
            throw new BadRequestHttpException('No student numbers provided.');
        }

        $updatedStudents = [];

        foreach ($data->studentNumbers as $studentNumber) {
            $student = $this->studentRepository->find($studentNumber);

            if (!$student) {
                continue;
            }

            if ($student->getReferencedGroup() !== $group) {
                $student->setReferencedGroup($group);
                $student->setUpdatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($student);
                $updatedStudents[] = $student;
            }
        }

        $this->entityManager->flush();

        return $updatedStudents;
    }
}

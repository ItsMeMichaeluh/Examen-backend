<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Student;
use App\Repository\GroupRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StudentGroupProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StudentRepository $studentRepository,
        private GroupRepository $groupRepository,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Student
    {
        $studentNumber = $uriVariables['studentNumber'];
        $groupId = $uriVariables['groupId'];

        $student = $this->studentRepository->find($studentNumber);
        $group = $this->groupRepository->find($groupId);

        if (!$student) {
            throw new NotFoundHttpException('Student not found');
        }

        if (!$group) {
            throw new NotFoundHttpException('Group not found');
        }

        if ($student->getReferencedGroup() !== $group) {
            $student->setReferencedGroup($group);
            $student->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($student);
            $this->entityManager->flush();
        }

        return $student;
    }
}

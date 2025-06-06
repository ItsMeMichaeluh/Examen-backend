<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StudentActivityProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StudentRepository      $studentRepository,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Student
    {
        $action = $operation->getExtraProperties()['action'] ?? null;
        $studentNumber = $uriVariables['studentNumber'];
        if (!$studentNumber) {
            throw new BadRequestHttpException('Student number is missing');
        }

        $student = $this->studentRepository->find($studentNumber);
        if (!$student) {
            throw new BadRequestHttpException('Student not found.');
        }

        $changed = false;
        if ($action === 'start' && $student->getActive() === false) {
            $student->setActive(true);
            $student->setStoppedAt(null);
            $changed = true;
        }

        if (($action === 'stop') && $student->getActive() === true) {
            $student->setActive(false);
            $student->setStoppedAt(new \DateTimeImmutable());
            $student->setReferencedGroup(null);
            $changed = true;
        }

        if ($changed) {
            $student->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($student);
            $this->entityManager->flush();
        }

        return $student;
    }
}

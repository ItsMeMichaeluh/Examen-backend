<?php
// src/State/MediaObjectStateProcessor.php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

use App\Dto\ExcelFileDto;
use App\Entity\Attendance;
use App\Entity\MediaObject;
use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ExcelImportProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack           $requestStack,
        private ValidatorInterface $validator,
        private StudentRepository $studentRepository,
    )
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): MediaObject
    {
        $request = $this->requestStack->getCurrentRequest();
        $uploadedFile = $request->files->get('file');

        $dto = new ExcelFileDto();
        $dto->file = $uploadedFile;

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new BadRequestHttpException(implode("\n", $errorMessages));
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new BadRequestHttpException('"file" is required and must be an uploaded file.');
        }

        if (!preg_match_all('/^AAR_\d{4}_W\d{2}_.+\.(ods|xlsx)$/', $uploadedFile->getClientOriginalName())) {
            throw new BadRequestHttpException('Dit AARbestand volgt niet de vaste naamgevingsconventie. Verwacht: AAR_[JAAR]_W[WEEK]_[CODE].[extensie]');
        }

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;



        $fileName = $uploadedFile->getPathname();
        $formats = [
            \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
            \PhpOffice\PhpSpreadsheet\IOFactory::READER_ODS,
        ];

        $spreadsheet = IOFactory::load($fileName, IReader::READ_DATA_ONLY, $formats);
        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];
        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            $columns = [
                'student_number',
                'year',
                'week',
                'scheduled',
                'logged',
            ];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $sheetData[] = array_combine($columns, $rowData);
        }

        foreach ($sheetData as $row) {
            if (!$this->studentRepository->findOneBy(['studentNumber' => $row['student_number']])) {
                $student = new Student();
                $student->setStudentNumber($row['student_number']);

                $this->entityManager->persist($student);
                $this->entityManager->flush();
            }

            $attendance = new Attendance();
            $attendance->setStudent($this->studentRepository->find($row['student_number']));
            $attendance->setYear($row['year']);
            $attendance->setWeek($row['week']);
            $attendance->setScheduled($row['scheduled']);
            $attendance->setLogged($row['logged']);

            $this->entityManager->persist($attendance);
        }

        $this->entityManager->persist($mediaObject);
        $this->entityManager->flush();

        return $mediaObject;
    }
}

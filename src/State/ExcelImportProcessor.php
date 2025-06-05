<?php
// src/State/MediaObjectStateProcessor.php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

use App\Dto\ExcelFileDto;
use App\Dto\ExcelImportDto;
use App\Entity\Attendance;
use App\Entity\MediaObject;
use App\Entity\Student;
use App\Repository\AttendanceRepository;
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
        private AttendanceRepository $attendanceRepository,
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

        $sheetData = [];
        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $rowData = [
                'student_number' => $worksheet->getCell('A' . $rowIndex)->getValue(),
                'year' => $worksheet->getCell('B' . $rowIndex)->getValue(),
                'week' => $worksheet->getCell('C' . $rowIndex)->getValue(),
                'scheduled' => $worksheet->getCell('D' . $rowIndex)->getValue(),
                'logged' => $worksheet->getCell('E' . $rowIndex)->getValue(),
            ];
            $sheetData[] = $rowData;
        }

        foreach ($sheetData as $row) {
            if (count(array_filter($row)) === 0) { continue; }
            $errorMessages = [];

            $dto = new ExcelImportDto();
            $dto->studentNumber = $row['student_number'];
            $dto->year = $row['year'];
            $dto->week = $row['week'];
            $dto->scheduled = $row['scheduled'];
            $dto->logged = $row['logged'];

            $errors = $this->validator->validate($dto);
            $skipRow = false;
            if ($errors->count() > 0) {
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
                }
                $skipRow = true;
            }

            if ($skipRow) {
                dump($errorMessages);
                continue;
            }

            $student = $this->studentRepository->findOneBy(['studentNumber' => $row['student_number']]);
            if (!$student) {
                $student = new Student();
                $student->setStudentNumber($row['student_number']);

                $this->entityManager->persist($student);
                $this->entityManager->flush();
            }

            $attendance = $this->attendanceRepository->findOneBy([
                'student' => $student,
                'year' => $row['year'],
                'week' => $row['week'],
            ]);

            if ($attendance) {
                if ($attendance->getScheduled() !== $row['scheduled'] || $attendance->getLogged() !== $row['logged']) {
                    $attendance->setScheduled($row['scheduled']);
                    $attendance->setLogged($row['logged']);
                }
            } else {
                $attendance = new Attendance();
                $attendance->setStudent($student);
                $attendance->setYear($row['year']);
                $attendance->setWeek($row['week']);
                $attendance->setScheduled($row['scheduled']);
                $attendance->setLogged($row['logged']);
            }
            $this->entityManager->persist($attendance);
        }

        $this->entityManager->persist($mediaObject);
        $this->entityManager->flush();

        return $mediaObject;
    }
}

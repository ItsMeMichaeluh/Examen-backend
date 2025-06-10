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
use App\Enum\MediaTypeEnum;
use App\Repository\AttendanceRepository;
use App\Repository\StudentRepository;
use App\Service\LogWriter;
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
        private ValidatorInterface     $validator,
        private StudentRepository      $studentRepository,
        private AttendanceRepository   $attendanceRepository,
        private LogWriter $logWriter
    ) {}

    /**
     * @throws \JsonException
     */
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

        if (!preg_match_all('/^AAR_(\d{4})_W(\d{2})_.+\.(ods|xlsx)$/', $uploadedFile->getClientOriginalName(), $matches)) {
            throw new BadRequestHttpException('Dit AARbestand volgt niet de vaste naamgevingsconventie. Verwacht: AAR_[JAAR]_W[WEEK]_[CODE].[extensie]');
        }

        $fileNameYear = (int)$matches[1][0];
        $fileNameWeek = (int)$matches[2][0];

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;
        $mediaObject->type = MediaTypeEnum::tryFrom($request->get('type'));

        $fileName = $uploadedFile->getPathname();
        $formats = [
            \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLSX,
            \PhpOffice\PhpSpreadsheet\IOFactory::READER_ODS,
        ];

        $spreadsheet = IOFactory::load($fileName, IReader::READ_DATA_ONLY, $formats);
        $worksheet = $spreadsheet->getActiveSheet();

        $logLines = [];
        $logLines[] = sprintf('[%s] Start processing file: %s', (new \DateTimeImmutable())->format('c'), $uploadedFile->getClientOriginalName());

        $sheetData = [];
        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowIndex = $row->getRowIndex();
            $rowData = [
                'row_index' => $rowIndex,
                'student_number' => $worksheet->getCell('A' . $rowIndex)->getValue(),
                'logged' => $worksheet->getCell('B' . $rowIndex)->getValue(),
                'scheduled' => $worksheet->getCell('C' . $rowIndex)->getValue(),
                'week' => $worksheet->getCell('D' . $rowIndex)->getValue(),
                'year' => $worksheet->getCell('E' . $rowIndex)->getValue(),
            ];
            $sheetData[] = $rowData;
        }

        // Counters for summary
        $stats = [
            'total' => count($sheetData),
            'skipped_empty' => 0,
            'skipped_validation' => 0,
            'skipped_year_week_mismatch' => 0,
            'new_students' => 0,
            'updated_attendance' => 0,
            'skipped_attendance' => 0,
            'new_attendance' => 0,
        ];

        foreach ($sheetData as $row) {
            $timestamp = (new \DateTimeImmutable())->format('c');

            if (count(array_filter($row)) === 0) {
                $logLines[] = sprintf('[%s] [Row %d] skipped: empty row.', $timestamp, $row['row_index']);
                $stats['skipped_empty']++;
                continue;
            }

            $dto = new ExcelImportDto();
            $dto->studentNumber = $row['student_number'];
            $dto->year = $row['year'];
            $dto->week = $row['week'];
            $dto->scheduled = $row['scheduled'];
            $dto->logged = $row['logged'];

            $errors = $this->validator->validate($dto);
            if ($errors->count() > 0) {
                foreach ($errors as $error) {
                    $logLines[] = sprintf('[%s] [Row %d] validation error - %s: %s', $timestamp, $row['row_index'], $error->getPropertyPath(), $error->getMessage());
                }
                $stats['skipped_validation']++;
                continue;
            }

            if ($dto->year !== $fileNameYear || $dto->week !== $fileNameWeek) {
                $logLines[] = sprintf('[%s] [Row %d] skipped: year/week mismatch (got Y%d/W%d, expected Y%d/W%d)', $timestamp, $row['row_index'], $dto->year, $dto->week, $fileNameYear, $fileNameWeek);
                $stats['skipped_year_week_mismatch']++;
                continue;
            }

            $student = $this->studentRepository->findOneBy(['studentNumber' => $dto->studentNumber]);
            if (!$student) {
                $student = new Student();
                $student->setStudentNumber($dto->studentNumber);
                $this->entityManager->persist($student);
                $this->entityManager->flush();
                $logLines[] = sprintf('[%s] [Row %d] new student created (%s)', $timestamp, $row['row_index'], $dto->studentNumber);
                $stats['new_students']++;
            }

            $attendance = $this->attendanceRepository->findOneBy([
                'student' => $student,
                'year' => $dto->year,
                'week' => $dto->week,
            ]);

            if ($attendance) {
                $existingScheduled = $attendance->getScheduled();
                $existingLogged = $attendance->getLogged();

                $existingPercentage = ($existingScheduled > 0) ? ($existingLogged / $existingScheduled) * 100 : 0;
                $newPercentage = ($dto->scheduled > 0) ? ($dto->logged / $dto->scheduled) * 100 : 0;

                if ($newPercentage > $existingPercentage) {
                    $attendance->setScheduled($dto->scheduled);
                    $attendance->setLogged($dto->logged);
                    $logLines[] = sprintf(
                        '[%s] [Row %d] Attendance updated for student %s (%.2f%% → %.2f%%)',
                        $timestamp,
                        $row['row_index'],
                        $dto->studentNumber,
                        $existingPercentage,
                        $newPercentage
                    );
                    $stats['updated_attendance']++;
                } else {
                    $logLines[] = sprintf(
                        '[%s] [Row %d] Attendance skipped for student %s (existing %.2f%% ≥ new %.2f%%)',
                        $timestamp,
                        $row['row_index'],
                        $dto->studentNumber,
                        $existingPercentage,
                        $newPercentage
                    );
                    $stats['skipped_attendance']++;
                }
            } else {
                $attendance = new Attendance();
                $attendance->setStudent($student);
                $attendance->setYear($dto->year);
                $attendance->setWeek($dto->week);
                $attendance->setScheduled($dto->scheduled);
                $attendance->setLogged($dto->logged);
                $logLines[] = sprintf('[%s] [Row %d] new attendance record created for %s', $timestamp, $row['row_index'], $dto->studentNumber);
                $stats['new_attendance']++;
            }

            $this->entityManager->persist($attendance);
        }

        $logLines[] = sprintf('[%s] Processing finished.', (new \DateTimeImmutable())->format('c'));
        $logLines[] = sprintf('[%s] Summary: total rows=%d, empty skipped=%d, validation skipped=%d, year/week mismatch=%d, new students=%d, attendance updated=%d, attendance skipped=%d, new attendance=%d',
            (new \DateTimeImmutable())->format('c'),
            $stats['total'],
            $stats['skipped_empty'],
            $stats['skipped_validation'],
            $stats['skipped_year_week_mismatch'],
            $stats['new_students'],
            $stats['updated_attendance'],
            $stats['skipped_attendance'],
            $stats['new_attendance']
        );

        $this->logWriter->CreateEntry(
            pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
            MediaTypeEnum::LOG_ENTRY,
            $logLines
        );

        $this->entityManager->persist($mediaObject);
        $this->entityManager->flush();

        return $mediaObject;
    }
}

<?php

namespace App\State;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PdfExportDto;
use App\Entity\MediaObject;
use App\Enum\MediaTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class PdfExportProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private Environment            $twig,
        private IriConverterInterface $iriConverter
    )
    {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MediaObject
    {
        /** @var PdfExportDto $data */

        $students = [];
        foreach ($data->students as $studentIri) {
            $student = $this->iriConverter->getResourceFromIri($studentIri);
            if ($student) {
                $students[] = $student;
            }
        }

        $html = $this->twig->render('pdf/student_attendance.html.twig', ['students' => $students]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_export') . '.pdf';
        file_put_contents($tempFile, $dompdf->output());

        $media = new MediaObject();
        $media->setType(MediaTypeEnum::PDF_EXPORT);
        $media->setFile(new UploadedFile(
            $tempFile,
            sprintf('pdf_export_%s', (new \DateTimeImmutable('now'))->format('YmdHis')),
            'application/pdf',
            null,
            true
        ));

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }
}

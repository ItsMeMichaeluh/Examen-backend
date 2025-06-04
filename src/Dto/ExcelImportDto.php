<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Student;
use Symfony\Component\Validator\Constraints as Assert;

final class ExcelImportDto
{
    #[Assert\Length(max: 45, maxMessage: 'Studentnummer te lang'), Assert\Type('string')]
    public string $studentNumber;

    #[Assert\NotNull(message: 'Jaar niet doorgegeven'), Assert\Type('integer', message: 'Jaar niet in correcte formaat doorgegeven')]
    public int $year;

    #[Assert\NotNull(message: 'Week niet doorgegeven'), Assert\Type('integer', message: 'Week niet in correcte formaat doorgegeven')]
    public int $week;

    #[Assert\NotNull(message: 'Roosterminuten niet doorgegeven'), Assert\Type('integer', message: 'Roosterminuten niet in correcte formaat doorgegeven')]
    public int $scheduled;

    #[Assert\NotNull(message: 'Aanwezigheid niet doorgegeven'), Assert\Type('integer', message: 'Aanwezigheid niet in correcte formaat doorgegeven')]
    public int $logged;
}

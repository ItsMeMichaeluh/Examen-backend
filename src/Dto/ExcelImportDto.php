<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ExcelImportDto
{
    #[Assert\NotNull(message: 'Studentnummer niet doorgegeven'), Assert\Length(max: 45, maxMessage: 'Studentnummer te lang'), Assert\Type('string')]
    public mixed $studentNumber;

    #[Assert\NotNull(message: 'Jaar niet doorgegeven'), Assert\Type('integer', message: 'Jaar niet in correcte formaat doorgegeven')]
    public mixed $year;

    #[Assert\NotNull(message: 'Week niet doorgegeven'), Assert\Type('integer', message: 'Week niet in correcte formaat doorgegeven')]
    public mixed $week;

    #[Assert\NotNull(message: 'Roosterminuten niet doorgegeven'), Assert\Type('integer', message: 'Roosterminuten niet in correcte formaat doorgegeven')]
    public mixed $scheduled;

    #[Assert\NotNull(message: 'Aanwezigheid niet doorgegeven'), Assert\Type('integer', message: 'Aanwezigheid niet in correcte formaat doorgegeven')]
    public mixed $logged;
}

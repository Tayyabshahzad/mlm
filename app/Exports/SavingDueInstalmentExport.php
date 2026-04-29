<?php

namespace App\Exports;

use App\Models\SavingInstalment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SavingDueInstalmentExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected Carbon $from;
    protected Carbon $to;
    protected ?int   $userId;

    public function __construct(?string $from = null, ?string $to = null, ?int $userId = null)
    {
        $this->from   = $from ? Carbon::parse($from)->startOfDay() : Carbon::today()->startOfDay();
        $this->to     = $to   ? Carbon::parse($to)->endOfDay()     : Carbon::today()->endOfDay();
        $this->userId = $userId;
    }

    public function title(): string
    {
        return 'Due Instalments';
    }

    /**
     * Each row = one user who has at least one pending instalment due within the date range.
     */
    public function collection(): Collection
    {
        $query = SavingInstalment::where('status', 'pending')
            ->whereDate('due_date', '>=', $this->from)
            ->whereDate('due_date', '<=', $this->to);

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        $userIds = $query->pluck('user_id')->unique();

        return User::whereIn('id', $userIds)
            ->with([
                'savingInstalments' => fn($q) => $q
                    ->where('status', 'pending')
                    ->whereDate('due_date', '>=', $this->from)
                    ->whereDate('due_date', '<=', $this->to)
                    ->orderBy('instalment_number'),
                'savingSponsor',
            ])
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Name',
            'Username',
            'Phone',
            'Parent / Sponsor',
            'Pending Instalments',
            'Total Due Amount ($)',
            'Oldest Due Date',
            'Latest Due Date',
        ];
    }

    public function map($user): array
    {
        static $row = 0;
        $row++;

        $dueInstalments = $user->savingInstalments; // already filtered in with()

        $sponsor     = $user->savingSponsor->first();
        $sponsorName = $sponsor ? $sponsor->name . ' (@' . $sponsor->username . ')' : '—';

        $totalDue    = $dueInstalments->sum('amount');
        $count       = $dueInstalments->count();
        $oldestDue   = $dueInstalments->min('due_date');
        $latestDue   = $dueInstalments->max('due_date');

        return [
            $row,
            $user->name,
            $user->username,
            $user->phone_number ?? '—',
            $sponsorName,
            $count,
            number_format($totalDue, 2),
            $oldestDue  ? Carbon::parse($oldestDue)->format('d-m-Y')  : '—',
            $latestDue  ? Carbon::parse($latestDue)->format('d-m-Y')  : '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC3545'],
                ],
            ],
        ];
    }
}

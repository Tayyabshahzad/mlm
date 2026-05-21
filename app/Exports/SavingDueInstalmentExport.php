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
        // Include instalments within the range AND overdue ones before the start date
        $query = SavingInstalment::where('status', 'pending')
            ->whereDate('due_date', '<=', $this->to);

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        $userIds = $query->pluck('user_id')->unique();

        return User::whereIn('id', $userIds)
            ->with([
                'savingInstalments' => fn($q) => $q
                    ->where('status', 'pending')
                    ->whereDate('due_date', '<=', $this->to)
                    ->orderBy('due_date'),
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
            'Overdue Instalments',
            'In-Range Instalments',
            'Overdue Amount ($)',
            'In-Range Amount ($)',
            'Total Due Amount ($)',
            'Oldest Due Date',
            'Latest Due Date',
        ];
    }

    public function map($user): array
    {
        static $row = 0;
        $row++;

        $all     = $user->savingInstalments;
        $overdue = $all->filter(fn($i) => Carbon::parse($i->due_date)->lt($this->from));
        $inRange = $all->filter(fn($i) => !Carbon::parse($i->due_date)->lt($this->from));

        $sponsor     = $user->savingSponsor->first();
        $sponsorName = $sponsor ? $sponsor->name . ' (@' . $sponsor->username . ')' : '—';

        return [
            $row,
            $user->name,
            $user->username,
            $user->phone_number ?? '—',
            $sponsorName,
            $overdue->count(),
            $inRange->count(),
            number_format($overdue->sum('amount'), 2),
            number_format($inRange->sum('amount'), 2),
            number_format($all->sum('amount'), 2),
            $all->min('due_date') ? Carbon::parse($all->min('due_date'))->format('d-m-Y') : '—',
            $all->max('due_date') ? Carbon::parse($all->max('due_date'))->format('d-m-Y') : '—',
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

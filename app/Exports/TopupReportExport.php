<?php

namespace App\Exports;

use App\Models\UserInvestment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TopupReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $month;
    protected $year;

    public function __construct($startDate = null, $endDate = null, $month = null, $year = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $query = UserInvestment::with(['investor', 'user'])
            ->orderBy('created_at', 'desc');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        } elseif ($this->month && $this->year) {
            $query->whereMonth('created_at', $this->month)
                  ->whereYear('created_at', $this->year);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'S#',
            'User ID',
            'Username',
            'Full Name',
            'Email',
            'Amount ($)',
            'Type',
            'Description',
            'Committed Amount (2X)',
            'Total Earnings',
            'ROI Status',
            'Invested By',
            'Transaction Date',
            'Time',
        ];
    }

    public function map($investment): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $investment->investor->id ?? 'N/A',
            $investment->investor->username ?? 'N/A',
            $investment->investor->name ?? 'N/A',
            $investment->investor->email ?? 'N/A',
            number_format($investment->amount, 2),
            ucfirst($investment->type),
            $investment->description ?? '-',
            number_format($investment->committed_amount, 2),
            number_format($investment->total_earnings, 2),
            ucfirst($investment->roi_status ?? 'active'),
            $investment->user->username ?? 'Self',
            Carbon::parse($investment->created_at)->format('Y-m-d'),
            Carbon::parse($investment->created_at)->format('H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
            ],
        ];
    }
}

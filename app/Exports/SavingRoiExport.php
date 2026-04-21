<?php

namespace App\Exports;

use App\Models\Wallet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SavingRoiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $userId;   // null = all users (admin), int = specific user

    public function __construct($startDate = null, $endDate = null, $userId = null)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->userId    = $userId;
    }

    public function collection()
    {
        $query = Wallet::with('user')
            ->where('wallet_type', 'saving_roi')
            ->orderBy('created_at', 'desc');

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['#', 'User ID', 'Username', 'Full Name', 'ROI Amount ($)', 'Description', 'Date', 'Time'];
    }

    public function map($entry): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $entry->user->id       ?? 'N/A',
            $entry->user->username ?? 'N/A',
            $entry->user->name     ?? 'N/A',
            number_format($entry->balance, 2),
            $entry->description ?? 'Daily saving account ROI',
            Carbon::parse($entry->created_at)->format('Y-m-d'),
            Carbon::parse($entry->created_at)->format('H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1BC5BD'],
                ],
            ],
        ];
    }
}

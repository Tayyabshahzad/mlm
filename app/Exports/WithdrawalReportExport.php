<?php

namespace App\Exports;

use App\Models\WithDrawalequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class WithdrawalReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $month;
    protected $year;
    protected $status;

    public function __construct($startDate = null, $endDate = null, $month = null, $year = null, $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->month = $month;
        $this->year = $year;
        $this->status = $status;
    }

    public function collection()
    {
        $query = WithDrawalequest::with(['user', 'user.profile'])
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

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
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
            'Phone',
            'Amount ($)',
            'Transfer Fee',
            'Net Amount',
            'Request Type',
            'Status',
            'Bank Name',
            'Account Title',
            'Account Number',
            'IBAN',
            'CNIC',
            'Request Date',
            'Time',
        ];
    }

    public function map($withdrawal): array
    {
        static $counter = 0;
        $counter++;

        $netAmount = $withdrawal->amount - ($withdrawal->transfer_fee ?? 0);

        return [
            $counter,
            $withdrawal->user->id ?? 'N/A',
            $withdrawal->user->username ?? 'N/A',
            $withdrawal->user->name ?? 'N/A',
            $withdrawal->user->email ?? 'N/A',
            $withdrawal->user->profile->phone ?? 'N/A',
            number_format($withdrawal->amount, 2),
            number_format($withdrawal->transfer_fee ?? 0, 2),
            number_format($netAmount, 2),
            ucfirst($withdrawal->request_type ?? 'N/A'),
            ucfirst($withdrawal->status),
            $withdrawal->user->profile->bank_name ?? 'N/A',
            $withdrawal->user->profile->account_title ?? 'N/A',
            $withdrawal->user->profile->account_number ?? 'N/A',
            $withdrawal->user->profile->ibn_number ?? 'N/A',
            $withdrawal->user->profile->cnic ?? 'N/A',
            Carbon::parse($withdrawal->created_at)->format('Y-m-d'),
            Carbon::parse($withdrawal->created_at)->format('H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E74C3C']
                ],
            ],
        ];
    }
}

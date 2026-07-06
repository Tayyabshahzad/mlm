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
    protected $fromDate;
    protected $toDate;
    protected $username;
    protected $minAmount;
    protected $maxAmount;
    protected $status;

    public function __construct(
        $fromDate  = null,
        $toDate    = null,
        $username  = null,
        $minAmount = null,
        $maxAmount = null,
        $status    = null
    ) {
        $this->fromDate  = $fromDate;
        $this->toDate    = $toDate;
        $this->username  = $username;
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
        $this->status    = $status;
    }

    public function collection()
    {
        $query = WithDrawalequest::with(['user', 'user.profile', 'user.parent'])
            ->orderBy('created_at', 'desc');

        if ($this->fromDate) {
            $query->whereDate('created_at', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('created_at', '<=', $this->toDate);
        }
        if ($this->username) {
            $query->whereHas('user', fn ($q) =>
                $q->where('username', 'like', '%'.$this->username.'%')
                  ->orWhere('name', 'like', '%'.$this->username.'%')
            );
        }
        if ($this->minAmount !== null && $this->minAmount !== '') {
            $query->where('amount', '>=', (float) $this->minAmount);
        }
        if ($this->maxAmount !== null && $this->maxAmount !== '') {
            $query->where('amount', '<=', (float) $this->maxAmount);
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
            'Username',
            'Full Name',
            'Phone',
            'Bank Name',
            'Account Title',
            'IBAN / Account No',
            'USDT Address',
            'Amount ($)',
            'Request Type',
            'Status',
            'Sponsor (Parent)',
            'Sponsor Username',
            'Request Date',
        ];
    }

    public function map($withdrawal): array
    {
        static $counter = 0;
        $counter++;

        $profile = $withdrawal->user->profile;
        $sponsor = $withdrawal->user->parent;

        return [
            $counter,
            $withdrawal->user->username ?? 'N/A',
            $withdrawal->user->name ?? 'N/A',
            $profile->phone ?? 'N/A',
            $profile->bank_name ?? 'N/A',
            $profile->account_title ?? 'N/A',
            $profile->ibn_number ?? 'N/A',
            $profile->account_number ?? 'N/A',
            number_format($withdrawal->amount, 2),
            ucfirst($withdrawal->request_type ?? 'N/A'),
            ucfirst($withdrawal->status),
            $sponsor->name ?? 'N/A',
            $sponsor->username ?? 'N/A',
            Carbon::parse($withdrawal->created_at)->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50'],
                ],
            ],
        ];
    }
}

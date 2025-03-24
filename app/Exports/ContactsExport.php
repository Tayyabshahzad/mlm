<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class ContactsExport implements FromCollection
{
    protected $startDate, $endDate;
    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::select('name', 'email', 'phone_number')
            ->whereBetween('created_at', [
                \Carbon\Carbon::parse($this->startDate)->startOfDay(),
                \Carbon\Carbon::parse($this->endDate)->endOfDay()
            ])
            ->get();
    }

    public function headings(): array
    {
        return ["Name", "Email", "Phone"];
    }
}

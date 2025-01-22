<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class TeamHierarchyExport implements FromCollection
{
    protected $hierarchy;

    public function __construct(Collection $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    /**
     * Return the collection of data for export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->hierarchy;
    }
}

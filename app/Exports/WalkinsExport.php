<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class WalkinsExport
{
    protected $walkins;

    public function __construct($walkins)
    {
        $this->walkins = $walkins;
    }

    public function collection()
    {
        return $this->walkins->map(function($walkin) {
            return [
                'Full Name' => $walkin->fullname,
                'Address' => $walkin->address,
                'Contact' => $walkin->contact_number ?? '',
                'Purpose' => $walkin->purpose,
                'Branch' => $walkin->branch ?? 'Diffun Branch Office',
                'Date & Time' => $walkin->date_time ? date('Y-m-d g:i A', strtotime($walkin->date_time)) : 'N/A',
                'Created At' => date('Y-m-d', strtotime($walkin->created_at)),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Full Name',
            'Address',
            'Contact',
            'Purpose',
            'Branch',
            'Date & Time',
            'Created At'
        ];
    }
}
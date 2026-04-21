<?php

namespace App\Exports;

use App\Models\VehicleLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TrafficLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = VehicleLog::with(['vehicleRegistration', 'vehicle']);

        // Apply Search
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->whereHas('vehicleRegistration', function($qr) use ($search) {
                    $qr->where('full_name', 'like', "%$search%")
                      ->orWhere('university_id', 'like', "%$search%")
                      ->orWhere('plate_number', 'like', "%$search%");
                })->orWhereHas('vehicle', function($qv) use ($search) {
                    $qv->where('plate_number', 'like', "%$search%")
                      ->orWhere('rfid_tag', 'like', "%$search%");
                });
            });
        }

        // Apply Status
        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('type', $this->filters['status']);
        }

        // Apply Date Range
        $from = !empty($this->filters['from']) ? Carbon::parse($this->filters['from'])->startOfDay() : Carbon::today()->startOfDay();
        $to = !empty($this->filters['to']) ? Carbon::parse($this->filters['to'])->endOfDay() : Carbon::today()->endOfDay();
        $query->whereBetween('timestamp', [$from, $to]);

        return $query->orderByDesc('timestamp')->get();
    }

    public function headings(): array
    {
        return [
            'Date/Time',
            'Tag ID',
            'Owner Name',
            'Vehicle Plate',
            'Log Type'
        ];
    }

    public function map($log): array
    {
        return [
            $log->timestamp->format('Y-m-d H:i:s'),
            $log->rfid_tag_id,
            $log->vehicleRegistration?->full_name ?? 'VISITOR',
            $log->vehicle?->plate_number ?? $log->vehicleRegistration?->plate_number ?? 'N/A',
            strtoupper($log->type),
        ];
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A7:E7' => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Branded Header
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                $sheet->setCellValue('A1', 'REPUBLIC OF THE PHILIPPINES');

                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->setCellValue('A2', 'EASTERN VISAYAS STATE UNIVERSITY');

                $sheet->mergeCells('A3:E3');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');
                $sheet->setCellValue('A3', 'Campus Safety & Site Management Office');

                $sheet->mergeCells('A5:E5');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setUnderline(true);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal('center');
                
                $dateRange = "Date Range: " . ($this->filters['from'] ?? date('Y-m-d')) . " to " . ($this->filters['to'] ?? date('Y-m-d'));
                $sheet->setCellValue('A5', 'VEHICLE TRAFFIC LOG REPORT (' . $dateRange . ')');

                // Auto Fit
                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $highestRow = $sheet->getHighestRow();
                
                // Add Zebra Stripes (Alternating Row Colors)
                for ($row = 8; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                    }
                }

                // Apply Borders
                $cellRange = 'A7:E' . $highestRow;
                $sheet->getStyle($cellRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}

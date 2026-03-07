<?php

namespace App\Services;

use App\Exports\DashboardExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function __construct(
        private ?StatsCalculator $statsCalculator = null,
        private ?BookingReportGenerator $bookingReportGenerator = null,
        private ?LoanReportGenerator $loanReportGenerator = null,
    ) {
        $this->statsCalculator ??= new StatsCalculator;
        $this->bookingReportGenerator ??= new BookingReportGenerator;
        $this->loanReportGenerator ??= new LoanReportGenerator;
    }

    private function sanitize($value)
    {
        if (is_null($value)) {
            return null;
        }
        if (is_string($value)) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

            return $value;
        }
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        return $value;
    }

    public function generateDashboardReport()
    {
        $data = $this->getDashboardData();

        $pdf = Pdf::loadView('reports.dashboard', $data)
            ->setPaper('a4', 'portrait')
            ->output();

        $filename = 'reporte_dashboard_'.Carbon::now()->format('Y-m-d_His').'.pdf';

        return response()->make($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function getDashboardData(): array
    {
        $stats = $this->statsCalculator->calculate();

        return [
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
            'stats' => $stats['stats'],
            'bookingsByLaboratory' => $stats['bookingsByLaboratory'],
            'bookingsByStatus' => $stats['bookingsByStatus'],
            'bookingsByProjectType' => $stats['bookingsByProjectType'],
            'bookingsByMonth' => $stats['bookingsByMonth'],
            'loansByStatus' => $stats['loansByStatus'],
            'loans' => $stats['loans'],
            'topProductsLoans' => $stats['topProductsLoans'],
            'recentBookings' => $this->bookingReportGenerator->getRecentBookings(),
            'recentLoans' => $this->loanReportGenerator->getRecentLoans(),
            'products' => $stats['products'],
            'laboratories' => $stats['laboratories'],
            'usersByRole' => $stats['usersByRole'],
            'pendingBookings' => $this->bookingReportGenerator->getPendingBookings(),
        ];
    }

    public function generateExcelReport()
    {
        $data = $this->getDashboardData();

        $export = new DashboardExport($data);

        $filename = 'reporte_dashboard_'.Carbon::now()->format('Y-m-d_His');

        return Excel::download($export, $filename.'.xlsx');
    }
}

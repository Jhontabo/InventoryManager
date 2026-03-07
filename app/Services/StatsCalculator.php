<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Loan;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsCalculator
{
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

    public function calculate(): array
    {
        $productsCount = Product::count();
        $laboratoriesCount = Laboratory::count();
        $usersActive = User::where('status', true)->count();
        $usersTotal = User::count();
        $totalBookings = Booking::count();
        $totalLoans = Loan::count();

        $bookingsByLaboratoryRaw = Laboratory::query()
            ->leftJoin('bookings', 'laboratories.id', '=', 'bookings.laboratory_id')
            ->select(
                'laboratories.id',
                'laboratories.name',
                'laboratories.location',
                'laboratories.capacity',
                DB::raw('COUNT(bookings.id) as total_bookings')
            )
            ->groupBy('laboratories.id', 'laboratories.name', 'laboratories.location', 'laboratories.capacity')
            ->orderBy('total_bookings', 'desc')
            ->get();

        $bookingsByLaboratory = [];
        foreach ($bookingsByLaboratoryRaw as $item) {
            $bookingsByLaboratory[] = [
                'id' => $item->id,
                'name' => $this->sanitize($item->name),
                'location' => $this->sanitize($item->location),
                'capacity' => $item->capacity,
                'total_bookings' => (int) $item->total_bookings,
            ];
        }

        $bookingsByStatusRaw = Booking::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $bookingsByStatus = [];
        foreach ($bookingsByStatusRaw as $item) {
            $bookingsByStatus[$this->sanitize($item->status)] = (int) $item->total;
        }

        $bookingsByProjectTypeRaw = Booking::select('project_type', DB::raw('count(*) as total'))
            ->whereNotNull('project_type')
            ->groupBy('project_type')
            ->get();

        $bookingsByProjectType = [];
        foreach ($bookingsByProjectTypeRaw as $item) {
            $bookingsByProjectType[$this->sanitize($item->project_type)] = (int) $item->total;
        }

        $bookingsByMonthRaw = Booking::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $bookingsByMonth = [];
        foreach ($bookingsByMonthRaw as $item) {
            $monthName = Carbon::createFromDate($item->year, $item->month, 1)->format('M Y');
            $bookingsByMonth[$monthName] = (int) $item->total;
        }

        $loansByStatusRaw = Loan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $loansByStatus = [];
        foreach ($loansByStatusRaw as $item) {
            $loansByStatus[$this->sanitize($item->status)] = (int) $item->total;
        }

        $pendingLoans = Loan::where('status', Loan::STATUS_PENDING)->count();
        $approvedLoans = Loan::where('status', Loan::STATUS_APPROVED)->count();
        $returnedLoans = Loan::where('status', Loan::STATUS_RETURNED)->count();
        $rejectedLoans = Loan::where('status', Loan::STATUS_REJECTED)->count();

        $overdueLoans = Loan::where('status', Loan::STATUS_APPROVED)
            ->where('estimated_return_at', '<', Carbon::now())
            ->count();

        $topProductsLoansRaw = Product::select(
            'products.id',
            'products.name',
            'products.available_quantity',
            DB::raw('COUNT(loans.id) as total_loans')
        )
            ->leftJoin('loans', 'products.id', '=', 'loans.product_id')
            ->groupBy('products.id', 'products.name', 'products.available_quantity')
            ->orderBy('total_loans', 'desc')
            ->limit(10)
            ->get();

        $topProductsLoans = [];
        foreach ($topProductsLoansRaw as $item) {
            $topProductsLoans[] = [
                'name' => $this->sanitize($item->name),
                'available_quantity' => (int) $item->available_quantity,
                'total_loans' => (int) $item->total_loans,
            ];
        }

        $productsRaw = Product::select([
            'id',
            'name',
            'description',
            'available_quantity',
            'brand',
            'model',
            'serial_number',
            'location',
            'status',
            'available_for_loan',
            'acquisition_date',
            'unit_cost',
        ])
            ->orderBy('name')
            ->get();

        $products = [];
        foreach ($productsRaw as $product) {
            $products[] = [
                'id' => $product->id,
                'name' => $this->sanitize($product->name),
                'description' => $this->sanitize($product->description),
                'available_quantity' => (int) $product->available_quantity,
                'brand' => $this->sanitize($product->brand),
                'model' => $this->sanitize($product->model),
                'serial_number' => $this->sanitize($product->serial_number),
                'location' => $this->sanitize($product->location),
                'status' => $this->sanitize($product->status),
                'available_for_loan' => $product->available_for_loan,
                'acquisition_date' => $product->acquisition_date ? Carbon::parse($product->acquisition_date)->format('d/m/Y') : null,
                'unit_cost' => $product->unit_cost ? number_format($product->unit_cost, 2) : null,
            ];
        }

        $laboratoriesRaw = Laboratory::select([
            'id',
            'name',
            'location',
            'capacity',
            'user_id',
        ])
            ->orderBy('name')
            ->get();

        $laboratories = [];
        foreach ($laboratoriesRaw as $lab) {
            $laboratories[] = [
                'id' => $lab->id,
                'name' => $this->sanitize($lab->name),
                'location' => $this->sanitize($lab->location),
                'capacity' => $lab->capacity,
                'status' => 'Activo',
            ];
        }

        $usersByRoleRaw = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name as role_name', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        $usersByRole = [];
        foreach ($usersByRoleRaw as $item) {
            $usersByRole[$this->sanitize($item->role_name)] = (int) $item->total;
        }

        return [
            'stats' => [
                'products' => $productsCount,
                'laboratories' => $laboratoriesCount,
                'usersActive' => $usersActive,
                'usersTotal' => $usersTotal,
                'totalBookings' => $totalBookings,
                'totalLoans' => $totalLoans,
                'overdueLoans' => $overdueLoans,
            ],
            'bookingsByLaboratory' => $bookingsByLaboratory,
            'bookingsByStatus' => $bookingsByStatus,
            'bookingsByProjectType' => $bookingsByProjectType,
            'bookingsByMonth' => $bookingsByMonth,
            'loansByStatus' => $loansByStatus,
            'loans' => [
                'pending' => $pendingLoans,
                'approved' => $approvedLoans,
                'returned' => $returnedLoans,
                'rejected' => $rejectedLoans,
                'overdue' => $overdueLoans,
            ],
            'topProductsLoans' => $topProductsLoans,
            'products' => $products,
            'laboratories' => $laboratories,
            'usersByRole' => $usersByRole,
        ];
    }
}

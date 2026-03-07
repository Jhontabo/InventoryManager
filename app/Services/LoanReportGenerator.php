<?php

namespace App\Services;

use App\Models\Loan;
use Carbon\Carbon;

class LoanReportGenerator
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

    public function getRecentLoans(): array
    {
        $recentLoansRaw = Loan::select([
            'loans.id',
            'loans.status',
            'loans.requested_at',
            'loans.approved_at',
            'loans.estimated_return_at',
            'loans.actual_return_at',
            'loans.observations',
            'users.name as user_name',
            'users.email as user_email',
            'products.name as product_name',
        ])
            ->leftJoin('users', 'loans.user_id', '=', 'users.id')
            ->leftJoin('products', 'loans.product_id', '=', 'products.id')
            ->orderBy('loans.created_at', 'desc')
            ->limit(20)
            ->get();

        $recentLoans = [];
        foreach ($recentLoansRaw as $loan) {
            $recentLoans[] = [
                'id' => $loan->id,
                'status' => $this->sanitize($loan->status),
                'requested_at' => $loan->requested_at ? Carbon::parse($loan->requested_at)->format('d/m/Y H:i') : null,
                'approved_at' => $loan->approved_at ? Carbon::parse($loan->approved_at)->format('d/m/Y H:i') : null,
                'estimated_return_at' => $loan->estimated_return_at ? Carbon::parse($loan->estimated_return_at)->format('d/m/Y') : null,
                'actual_return_at' => $loan->actual_return_at ? Carbon::parse($loan->actual_return_at)->format('d/m/Y H:i') : null,
                'observations' => $this->sanitize($loan->observations),
                'user_name' => $this->sanitize($loan->user_name),
                'user_email' => $this->sanitize($loan->user_email),
                'product_name' => $this->sanitize($loan->product_name),
            ];
        }

        return $recentLoans;
    }
}

<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function request(User $user, int $productId): Loan
    {
        return DB::transaction(function () use ($user, $productId): Loan {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($productId);

            if (! $lockedProduct->available_for_loan || ! in_array($lockedProduct->status, ['new', 'used'], true)) {
                throw new DomainException('El producto no está disponible para préstamo.');
            }

            if ($lockedProduct->available_quantity < 1) {
                throw new DomainException('No hay unidades disponibles para préstamo.');
            }

            $alreadyRequested = Loan::query()
                ->where('product_id', $lockedProduct->id)
                ->where('user_id', $user->id)
                ->whereIn('status', [Loan::STATUS_PENDING, Loan::STATUS_APPROVED])
                ->exists();

            if ($alreadyRequested) {
                throw new DomainException('Ya tienes una solicitud activa para este producto.');
            }

            return Loan::query()->create([
                'product_id' => $lockedProduct->id,
                'user_id' => $user->id,
                'status' => Loan::STATUS_PENDING,
                'requested_at' => now(),
            ]);
        });
    }

    public function approve(Loan $loan, ?string $estimatedReturnAt = null): Loan
    {
        return DB::transaction(function () use ($loan, $estimatedReturnAt): Loan {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->status !== Loan::STATUS_PENDING) {
                throw new DomainException('Solo se pueden aprobar préstamos pendientes.');
            }

            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($lockedLoan->product_id);

            if ($lockedProduct->available_quantity < 1) {
                throw new DomainException('No hay unidades disponibles para aprobar este préstamo.');
            }

            $lockedLoan->update([
                'status' => Loan::STATUS_APPROVED,
                'approved_at' => now(),
                'estimated_return_at' => $estimatedReturnAt ? Carbon::parse($estimatedReturnAt)->endOfDay() : now()->addWeek(),
            ]);

            $lockedProduct->decrement('available_quantity');

            if ($lockedProduct->fresh()->available_quantity < 1) {
                $lockedProduct->update(['available_for_loan' => false]);
            }

            return $lockedLoan->fresh(['user', 'product']);
        });
    }

    public function reject(Loan $loan, ?string $observations = null): Loan
    {
        return DB::transaction(function () use ($loan, $observations): Loan {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->status !== Loan::STATUS_PENDING) {
                throw new DomainException('Solo se pueden rechazar préstamos pendientes.');
            }

            $lockedLoan->update([
                'status' => Loan::STATUS_REJECTED,
                'observations' => $observations,
                'estimated_return_at' => null,
            ]);

            return $lockedLoan->fresh(['user', 'product']);
        });
    }

    public function markAsReturned(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan): Loan {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->status !== Loan::STATUS_APPROVED) {
                throw new DomainException('Solo se pueden devolver préstamos aprobados.');
            }

            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($lockedLoan->product_id);

            $lockedLoan->update([
                'status' => Loan::STATUS_RETURNED,
                'actual_return_at' => now(),
            ]);

            $lockedProduct->increment('available_quantity');
            $lockedProduct->update(['available_for_loan' => true]);

            return $lockedLoan->fresh(['user', 'product']);
        });
    }
}

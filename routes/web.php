<?php

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/', function () {
    return Auth::check() ? redirect('/admin') : view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        return redirect('/admin');
    }

    throw ValidationException::withMessages([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ]);
})->middleware(['guest', 'throttle:5,1'])->name('custom.login');

Route::get('/dashboard', function () {
    return redirect('/admin');
})->name('dashboard');

Route::middleware(['auth'])->get('/reports/dashboard', function () {
    $reportService = new ReportService;

    return $reportService->generateDashboardReport();
})->name('reports.dashboard.download');

Route::middleware(['auth'])->get('/reports/excel', function () {
    $reportService = new ReportService;

    return $reportService->generateExcelReport();
})->name('reports.excel.download');

Route::middleware(['web', 'auth'])->get('/calendar-only', fn () => view('filament.pages.booking-calendar'));

Route::redirect('/admin/reservation-historys', '/admin/reservation-histories')
    ->middleware(['auth']);

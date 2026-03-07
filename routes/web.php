<?php

use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect('/admin') : view('auth.login');
})->name('login');

Route::post('/login', function () {
    $credentials = request()->only('email', 'password');

    if (Auth::attempt($credentials)) {
        request()->session()->regenerate();

        return redirect('/admin');
    }

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ]);
})->name('custom.login');

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

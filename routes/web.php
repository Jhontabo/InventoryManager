<?php

use App\Filament\Pages\Reports;
use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect('/admin') : redirect('/admin/login');
})->name('login');

Route::redirect('/login', '/admin/login');

Route::get('/dashboard', function () {
    return redirect('/admin');
})->name('dashboard');

Route::middleware(['auth'])->get('/reports/dashboard', function () {
    abort_unless(Reports::canDownloadReports(Auth::user()), 403);

    $reportService = new ReportService;

    return $reportService->generateDashboardReport();
})->name('reports.dashboard.download');

Route::middleware(['auth'])->get('/reports/excel', function () {
    abort_unless(Reports::canDownloadReports(Auth::user()), 403);

    $reportService = new ReportService;

    return $reportService->generateExcelReport();
})->name('reports.excel.download');

Route::middleware(['web', 'auth'])->get('/calendar-only', fn () => view('filament.pages.booking-calendar'));

Route::redirect('/admin/reservation-historys', '/admin/reservation-histories')
    ->middleware(['auth']);

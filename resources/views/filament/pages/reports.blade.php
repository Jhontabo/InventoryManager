<x-filament-panels::page>
    <div class="space-y-5">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-blue-50 p-6 shadow-sm dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Centro de Reportes</h2>
                    <p class="mt-1 max-w-2xl text-sm text-slate-600 dark:text-slate-300">
                        Exporta reportes ejecutivos y analíticos para seguimiento operativo y toma de decisiones.
                    </p>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <x-filament::button
                        tag="a"
                        :href="route('reports.dashboard.download')"
                        icon="heroicon-o-document-arrow-down"
                        color="primary"
                        size="lg"
                    >
                        Descargar PDF Ejecutivo
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="route('reports.excel.download')"
                        icon="heroicon-o-table-cells"
                        color="gray"
                        size="lg"
                    >
                        Descargar Excel Analítico
                    </x-filament::button>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Productos</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $stats['products'] ?? 0 }}</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Laboratorios</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $stats['laboratories'] ?? 0 }}</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Reservas</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $stats['bookings'] ?? 0 }}</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Préstamos</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $stats['loans'] ?? 0 }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-3 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-presentation-chart-line" style="width: 1rem; height: 1rem;" class="text-blue-600 dark:text-blue-300" />
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">Contenido PDF Ejecutivo</h3>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    KPIs, gráficos de reservas y préstamos, tendencia mensual y resumen operativo para comité.
                </p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-3 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-circle-stack" style="width: 1rem; height: 1rem;" class="text-slate-600 dark:text-slate-300" />
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">Contenido Excel Analítico</h3>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    Datos detallados por módulo para filtros, tablas dinámicas y seguimiento por periodo.
                </p>
            </article>
        </section>
    </div>
</x-filament-panels::page>

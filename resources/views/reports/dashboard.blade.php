<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Ejecutivo - InventoryManager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 1.3cm;
            size: A4;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 10px;
            line-height: 1.35;
        }

        .header {
            border: 1px solid #dbe4f0;
            background: #f8fbff;
            padding: 14px;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 16px;
            color: #0f4c81;
            margin-bottom: 2px;
        }

        .header p {
            color: #4b5563;
            font-size: 10px;
        }

        .generated {
            margin-top: 4px;
            font-size: 9px;
            color: #6b7280;
        }

        .section {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f4c81;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 6px;
        }

        .kpi-card {
            border: 1px solid #dbe4f0;
            background: #f8fbff;
            padding: 8px;
            text-align: center;
            border-radius: 6px;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #0f4c81;
        }

        .kpi-label {
            margin-top: 2px;
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .insight-box {
            border-left: 3px solid #0f4c81;
            background: #eff6ff;
            padding: 8px;
            margin-top: 8px;
        }

        .insight-title {
            font-weight: bold;
            margin-bottom: 4px;
            color: #0f4c81;
            font-size: 10px;
        }

        .insight-list {
            margin-left: 14px;
        }

        .insight-list li {
            margin-bottom: 3px;
        }

        .chart-block {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 6px;
        }

        .chart-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #111827;
        }

        .bar-row {
            margin-bottom: 5px;
            font-size: 9px;
        }

        .bar-head {
            width: 100%;
            margin-bottom: 2px;
        }

        .bar-label {
            float: left;
            color: #374151;
        }

        .bar-value {
            float: right;
            color: #111827;
            font-weight: bold;
        }

        .clearfix {
            clear: both;
        }

        .bar-track {
            width: 100%;
            height: 9px;
            background: #edf2f7;
            border-radius: 3px;
        }

        .bar-fill {
            height: 9px;
            border-radius: 3px;
        }

        .bar-blue { background: #3b82f6; }
        .bar-green { background: #10b981; }
        .bar-amber { background: #f59e0b; }
        .bar-red { background: #ef4444; }
        .bar-indigo { background: #6366f1; }

        .two-col {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8.8px;
        }

        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        .table th {
            background: #f3f4f6;
            color: #111827;
            font-size: 8px;
            text-transform: uppercase;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: bold;
            color: #fff;
        }

        .badge-success { background: #16a34a; }
        .badge-warning { background: #d97706; }
        .badge-danger { background: #dc2626; }
        .badge-gray { background: #6b7280; }

        .footer {
            margin-top: 14px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
        }

        .page-break {
            page-break-before: always;
        }

        .no-data {
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    @php
        $bookingLabels = [
            'pending' => 'Pendientes',
            'approved' => 'Aprobadas',
            'reserved' => 'Reservadas',
            'rejected' => 'Rechazadas',
            'cancelled' => 'Canceladas',
        ];

        $loanLabels = [
            'pending' => 'Pendientes',
            'approved' => 'Aprobados',
            'returned' => 'Devueltos',
            'rejected' => 'Rechazados',
            'overdue' => 'Vencidos',
        ];

        $topLabs = array_slice($bookingsByLaboratory ?? [], 0, 6);
        $lastMonths = array_slice($bookingsByMonth ?? [], -6, 6, true);

        $maxBookingStatus = max(array_values($bookingsByStatus ?: ['base' => 1]));
        $maxLoanStatus = max(array_values($loans ?: ['base' => 1]));
        $maxLabBookings = max(array_map(fn ($lab) => $lab['total_bookings'] ?? 0, $topLabs) ?: [1]);
        $maxMonthlyBookings = max(array_values($lastMonths ?: ['base' => 1]));

        $topLabName = $topLabs[0]['name'] ?? 'Sin datos';
        $topLabCount = $topLabs[0]['total_bookings'] ?? 0;
        $overdueRatio = ($stats['totalLoans'] ?? 0) > 0
            ? round((($stats['overdueLoans'] ?? 0) / $stats['totalLoans']) * 100, 1)
            : 0;
    @endphp

    <div class="header">
        <h1>Reporte Ejecutivo de Laboratorios</h1>
        <p>InventoryManager - Indicadores para toma de decisiones</p>
        <div class="generated">Generado: {{ $generatedAt }}</div>
    </div>

    <div class="section">
        <div class="section-title">1. KPIs Principales</div>

        <table class="kpi-grid">
            <tr>
                <td class="kpi-card">
                    <div class="kpi-value">{{ $stats['products'] ?? 0 }}</div>
                    <div class="kpi-label">Productos</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ $stats['laboratories'] ?? 0 }}</div>
                    <div class="kpi-label">Laboratorios</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ $stats['usersActive'] ?? 0 }}</div>
                    <div class="kpi-label">Usuarios activos</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ $stats['totalBookings'] ?? 0 }}</div>
                    <div class="kpi-label">Reservas totales</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ $stats['totalLoans'] ?? 0 }}</div>
                    <div class="kpi-label">Préstamos totales</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value" style="color: #dc2626;">{{ $stats['overdueLoans'] ?? 0 }}</div>
                    <div class="kpi-label">Préstamos vencidos</div>
                </td>
            </tr>
        </table>

        <div class="insight-box">
            <div class="insight-title">Lectura rápida para decisión</div>
            <ul class="insight-list">
                <li>Laboratorio con mayor carga: <strong>{{ $topLabName }}</strong> ({{ $topLabCount }} reservas).</li>
                <li>Riesgo por vencimiento de préstamos: <strong>{{ $overdueRatio }}%</strong> del total de préstamos.</li>
                <li>Si hay más de 10% de vencidos, priorizar seguimiento de devoluciones esta semana.</li>
            </ul>
        </div>
    </div>

    <table class="two-col">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="section">
                    <div class="section-title">2. Gráfico - Reservas por Estado</div>
                    <div class="chart-block">
                        @forelse($bookingsByStatus as $status => $count)
                            @php
                                $label = $bookingLabels[$status] ?? ucfirst($status);
                                $percent = $maxBookingStatus > 0 ? round(($count / $maxBookingStatus) * 100, 2) : 0;
                            @endphp
                            <div class="bar-row">
                                <div class="bar-head">
                                    <span class="bar-label">{{ $label }}</span>
                                    <span class="bar-value">{{ $count }}</span>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill bar-blue" style="width: {{ $percent }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="no-data">Sin datos de reservas.</p>
                        @endforelse
                    </div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="section">
                    <div class="section-title">3. Gráfico - Préstamos por Estado</div>
                    <div class="chart-block">
                        @forelse($loans as $status => $count)
                            @php
                                $label = $loanLabels[$status] ?? ucfirst($status);
                                $percent = $maxLoanStatus > 0 ? round(($count / $maxLoanStatus) * 100, 2) : 0;
                                $barClass = $status === 'overdue' ? 'bar-red' : 'bar-green';
                            @endphp
                            <div class="bar-row">
                                <div class="bar-head">
                                    <span class="bar-label">{{ $label }}</span>
                                    <span class="bar-value">{{ $count }}</span>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill {{ $barClass }}" style="width: {{ $percent }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="no-data">Sin datos de préstamos.</p>
                        @endforelse
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="two-col">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="section">
                    <div class="section-title">4. Gráfico - Top Laboratorios por Reservas</div>
                    <div class="chart-block">
                        @forelse($topLabs as $lab)
                            @php
                                $value = $lab['total_bookings'] ?? 0;
                                $percent = $maxLabBookings > 0 ? round(($value / $maxLabBookings) * 100, 2) : 0;
                            @endphp
                            <div class="bar-row">
                                <div class="bar-head">
                                    <span class="bar-label">{{ $lab['name'] }}</span>
                                    <span class="bar-value">{{ $value }}</span>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill bar-indigo" style="width: {{ $percent }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="no-data">No hay laboratorios con reservas.</p>
                        @endforelse
                    </div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="section">
                    <div class="section-title">5. Gráfico - Tendencia Mensual de Reservas</div>
                    <div class="chart-block">
                        @forelse($lastMonths as $month => $count)
                            @php
                                $percent = $maxMonthlyBookings > 0 ? round(($count / $maxMonthlyBookings) * 100, 2) : 0;
                            @endphp
                            <div class="bar-row">
                                <div class="bar-head">
                                    <span class="bar-label">{{ $month }}</span>
                                    <span class="bar-value">{{ $count }}</span>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill bar-amber" style="width: {{ $percent }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="no-data">Sin histórico mensual disponible.</p>
                        @endforelse
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">6. Operación - Reservas por Laboratorio (Detalle)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Laboratorio</th>
                    <th>Ubicación</th>
                    <th>Capacidad</th>
                    <th>Total Reservas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookingsByLaboratory as $lab)
                    <tr>
                        <td>{{ $lab['id'] }}</td>
                        <td>{{ $lab['name'] }}</td>
                        <td>{{ $lab['location'] ?: 'N/A' }}</td>
                        <td>{{ $lab['capacity'] ?: 'N/A' }}</td>
                        <td>{{ $lab['total_bookings'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-data">Sin datos de reservas por laboratorio.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">7. Inventario Crítico (Top 20)</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Cantidad</th>
                    <th>Ubicación</th>
                    <th>Disponibilidad</th>
                </tr>
            </thead>
            <tbody>
                @forelse(array_slice($products ?? [], 0, 20) as $product)
                    <tr>
                        <td>{{ $product['name'] }}</td>
                        <td>{{ $product['brand'] ?: 'N/A' }}</td>
                        <td>{{ $product['model'] ?: 'N/A' }}</td>
                        <td>{{ $product['available_quantity'] }}</td>
                        <td>{{ $product['location'] ?: 'N/A' }}</td>
                        <td>
                            @if($product['available_quantity'] > 3)
                                <span class="badge badge-success">Disponible</span>
                            @elseif($product['available_quantity'] > 0)
                                <span class="badge badge-warning">Limitado</span>
                            @else
                                <span class="badge badge-danger">Agotado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">Sin inventario registrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">8. Últimas Reservas</div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Solicitante</th>
                    <th>Laboratorio</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Inicio</th>
                </tr>
            </thead>
            <tbody>
                @forelse(array_slice($recentBookings ?? [], 0, 20) as $booking)
                    <tr>
                        <td>{{ $booking['id'] }}</td>
                        <td>{{ trim(($booking['name'] ?? '').' '.($booking['last_name'] ?? '')) }}</td>
                        <td>{{ $booking['laboratory_name'] ?? 'N/A' }}</td>
                        <td>{{ $booking['project_type'] ?? 'N/A' }}</td>
                        <td>{{ ucfirst($booking['status'] ?? 'N/A') }}</td>
                        <td>{{ $booking['start_at'] ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">Sin reservas recientes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">9. Últimos Préstamos</div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Producto</th>
                    <th>Estado</th>
                    <th>Aprobado</th>
                    <th>Devolución Est.</th>
                </tr>
            </thead>
            <tbody>
                @forelse(array_slice($recentLoans ?? [], 0, 20) as $loan)
                    <tr>
                        <td>{{ $loan['id'] }}</td>
                        <td>{{ $loan['user_name'] ?? 'N/A' }}</td>
                        <td>{{ $loan['product_name'] ?? 'N/A' }}</td>
                        <td>{{ ucfirst($loan['status'] ?? 'N/A') }}</td>
                        <td>{{ $loan['approved_at'] ?? 'N/A' }}</td>
                        <td>{{ $loan['estimated_return_at'] ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">Sin préstamos recientes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        InventoryManager - Sistema de Gestión de Inventarios
        <br>
        Documento generado automáticamente para seguimiento operativo y estratégico.
    </div>
</body>
</html>

@extends('tenant.layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Panel CRM</h1>
            <small class="text-muted">Resumen de tu embudo comercial</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tenant.crm.leads.create') }}"
               class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                <i class="fas fa-plus fa-fw" aria-hidden="true"></i>
                <span>Nuevo lead</span>
            </a>
            <a href="{{ route('tenant.crm.opportunities') }}"
               class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
                <i class="fas fa-list fa-fw" aria-hidden="true"></i>
                <span>Ver embudo</span>
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- EMBUDO: tus oportunidades activas           --}}
    {{-- ============================================ --}}
    <h2 class="h6 text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
        <i class="fas fa-filter fa-fw me-1" aria-hidden="true"></i>Embudo
    </h2>
    <div class="row g-2 mb-3">
        <div class="col-md-4 col-sm-6">
            <a href="{{ route('tenant.crm.leads') }}"
               class="text-decoration-none d-block h-100">
                <div class="card border-start border-primary border-4 shadow-sm h-100 crm-kpi-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                Leads nuevos
                            </span>
                            <span class="text-primary" aria-hidden="true">
                                <i class="fas fa-user-plus fa-lg"></i>
                            </span>
                        </div>
                        <p class="h2 fw-bold mb-0 text-dark lh-1">{{ $kpiLeadsNew ?? 0 }}</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="{{ route('tenant.crm.opportunities') }}"
               class="text-decoration-none d-block h-100">
                <div class="card border-start border-info border-4 shadow-sm h-100 crm-kpi-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                Negocios abiertos
                            </span>
                            <span class="text-info" aria-hidden="true">
                                <i class="fas fa-handshake fa-lg"></i>
                            </span>
                        </div>
                        <p class="h2 fw-bold mb-0 text-dark lh-1">{{ $kpiDealsOpen ?? 0 }}</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6">
            <a href="{{ route('tenant.crm.opportunities') }}"
               class="text-decoration-none d-block h-100">
                <div class="card border-start border-secondary border-4 shadow-sm h-100 crm-kpi-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                Cotizaciones pendientes
                            </span>
                            <span class="text-secondary" aria-hidden="true">
                                <i class="fas fa-file-invoice fa-lg"></i>
                            </span>
                        </div>
                        <p class="h2 fw-bold mb-0 text-dark lh-1">{{ $kpiPendingQuotations ?? 0 }}</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- ACCIÓN URGENTE: tareas vencidas              --}}
    {{-- ============================================ --}}
    @php
        $overdue = (int) ($kpiOverdueTasks ?? 0);
    @endphp
    @if ($overdue > 0)
        <div class="card border-start border-danger border-4 shadow-sm mb-3 bg-danger-subtle">
            <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-danger" aria-hidden="true">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </span>
                    <div>
                        <strong class="text-danger-emphasis">
                            {{ $overdue }} {{ $overdue === 1 ? 'tarea vencida' : 'tareas vencidas' }}
                        </strong>
                        <small class="d-block text-muted">Requieren atención inmediata</small>
                    </div>
                </div>
                <a href="{{ route('tenant.crm.opportunities') }}"
                   class="btn btn-danger btn-sm d-inline-flex align-items-center gap-1">
                    <i class="fas fa-tasks fa-fw" aria-hidden="true"></i>
                    <span>Ver mis tareas</span>
                </a>
            </div>
        </div>
    @else
        <div class="card border-start border-success border-4 shadow-sm mb-3 bg-success-subtle">
            <div class="card-body p-3 d-flex align-items-center gap-2">
                <span class="text-success" aria-hidden="true">
                    <i class="fas fa-check-circle fa-lg"></i>
                </span>
                <div>
                    <strong class="text-success-emphasis">Sin tareas vencidas</strong>
                    <small class="d-block text-muted">Todo al día. Buen trabajo.</small>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- RESULTADOS: rendimiento del mes              --}}
    {{-- ============================================ --}}
    <h2 class="h6 text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.05em;">
        <i class="fas fa-chart-line fa-fw me-1" aria-hidden="true"></i>Resultados del mes
    </h2>
    <div class="row g-2 mb-3">
        <div class="col-md-6 col-sm-6">
            <a href="{{ route('tenant.crm.opportunities') }}"
               class="text-decoration-none d-block h-100">
                <div class="card border-start border-success border-4 shadow-sm h-100 crm-kpi-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                Ganadas este mes
                            </span>
                            <span class="text-success" aria-hidden="true">
                                <i class="fas fa-trophy fa-lg"></i>
                            </span>
                        </div>
                        <p class="h2 fw-bold mb-0 text-dark lh-1">{{ $kpiWonMonth ?? 0 }}</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-sm-6">
            <a href="{{ route('tenant.crm.opportunities') }}"
               class="text-decoration-none d-block h-100">
                <div class="card border-start border-danger border-4 shadow-sm h-100 crm-kpi-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                Perdidas este mes
                            </span>
                            <span class="text-danger" aria-hidden="true">
                                <i class="fas fa-times-circle fa-lg"></i>
                            </span>
                        </div>
                        <p class="h2 fw-bold mb-0 text-dark lh-1">{{ $kpiLostMonth ?? 0 }}</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .crm-kpi-card {
        transition: transform 150ms ease-out, box-shadow 150ms ease-out;
    }
    .crm-kpi-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.08) !important;
        cursor: pointer;
    }
    @media (prefers-reduced-motion: reduce) {
        .crm-kpi-card,
        .crm-kpi-card:hover {
            transition: none;
            transform: none;
        }
    }
</style>
@endpush
@extends('tenant.layouts.app')

@section('content')
    @php
        $opp = $opportunity;
        $stage = $opp->crmStage;

        $notes      = $activities->where('type', 'note')->values();
        $calls      = $activities->where('type', 'call')->values();
        $tasks      = $activities->where('type', 'task')->values();
        $pendingT   = $tasks->where('status', 'pending')->count();
        $overdueT   = $tasks->where('status', 'pending')->filter(fn($t) => $t->due_date && $t->due_date->isPast())->count();
    @endphp

    @push('styles')
<style>
        /* ponytail: estilos del detalle de lead — limpio, agrupado, sin Bootstrap por defecto */
        .crm-detail { max-width: 1280px; margin: 0 auto; }

        .crm-header {
            background: #fff;
            border: 1px solid #e5e9f0;
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 18px;
            box-shadow: 0 1px 2px rgba(20,30,50,0.04);
        }
        .crm-header .crm-id {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            color: #95a5a6;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .crm-header .crm-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2d3d;
            margin: 0 0 6px 0;
        }
        .crm-header .crm-meta {
            color: #5a6b7c;
            font-size: 14px;
        }
        .crm-header .crm-meta strong { color: #1f2d3d; }
        .crm-header .crm-stage-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .crm-toolbar {
            background: #fff;
            border: 1px solid #e5e9f0;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 18px;
            box-shadow: 0 1px 2px rgba(20,30,50,0.04);
        }
        .crm-toolbar-group {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 12px;
            border-right: 1px solid #eef0f3;
        }
        .crm-toolbar-group:last-child { border-right: none; margin-left: auto; }
        .crm-toolbar-group .crm-toolbar-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #95a5a6;
            margin-right: 4px;
        }

        .crm-btn-primary {
            background: #4a90e2;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.15s ease;
            position: relative;
        }
        .crm-btn-primary:hover { background: #3a7bc8; color: #fff; }
        .crm-btn-primary .crm-counter {
            background: rgba(255,255,255,0.25);
            color: #fff;
            font-size: 11px;
            padding: 1px 6px;
            border-radius: 10px;
            margin-left: 4px;
        }

        .crm-btn-ghost {
            background: transparent;
            color: #4a90e2;
            border: 1px solid #cfd8e3;
            padding: 7px 11px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .crm-btn-ghost:hover { background: #eaf2fb; border-color: #4a90e2; }

        .crm-btn-info {
            background: #17a2b8; color: #fff; border: none; padding: 8px 14px; border-radius: 8px;
            font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
        }
        .crm-btn-info:hover { background: #138496; color: #fff; }
        .crm-btn-outline-info {
            background: #fff; color: #17a2b8; border: 1px solid #17a2b8; padding: 7px 13px; border-radius: 8px;
            font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
        }
        .crm-btn-outline-info:hover { background: #e6f6f8; }

        .crm-btn-won {
            background: #28a745; color: #fff; border: none; padding: 8px 14px; border-radius: 8px;
            font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
        }
        .crm-btn-won:hover { background: #218838; color: #fff; }
        .crm-btn-lost {
            background: #fff; color: #dc3545; border: 1px solid #dc3545; padding: 7px 13px; border-radius: 8px;
            font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
        }
        .crm-btn-lost:hover { background: #fdf2f3; }

        .crm-stage-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .crm-card {
            background: #fff;
            border: 1px solid #e5e9f0;
            border-radius: 12px;
            margin-bottom: 18px;
            box-shadow: 0 1px 2px rgba(20,30,50,0.04);
            overflow: hidden;
        }
        .crm-card-header {
            padding: 14px 18px;
            border-bottom: 1px solid #eef0f3;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .crm-card-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: #1f2d3d;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .crm-card-body { padding: 16px 18px; }

        .crm-timeline {
            position: relative;
            padding-left: 22px;
        }
        .crm-timeline::before {
            content: '';
            position: absolute;
            left: 7px; top: 6px; bottom: 6px;
            width: 2px;
            background: #e5e9f0;
        }
        .crm-timeline-item {
            position: relative;
            padding: 0 0 16px 0;
        }
        .crm-timeline-item:last-child { padding-bottom: 0; }
        .crm-timeline-dot {
            position: absolute;
            left: -22px;
            top: 2px;
            width: 16px; height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #4a90e2;
            z-index: 1;
        }
        .crm-timeline-content {
            background: #f8fafc;
            border: 1px solid #eef0f3;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .crm-timeline-meta {
            font-size: 12px;
            color: #95a5a6;
            margin-bottom: 4px;
        }
        .crm-timeline-text {
            font-size: 14px;
            color: #1f2d3d;
        }

        .crm-empty {
            text-align: center;
            padding: 32px 12px;
            color: #95a5a6;
        }
        .crm-empty i { font-size: 28px; opacity: 0.4; display: block; margin-bottom: 8px; }

        .crm-info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f5;
            font-size: 14px;
        }
        .crm-info-row:last-child { border-bottom: none; }
        .crm-info-label {
            width: 110px;
            color: #95a5a6;
            font-weight: 500;
        }
        .crm-info-value { color: #1f2d3d; flex: 1; }

        .crm-history-item {
            background: #f8fafc;
            border: 1px solid #eef0f3;
            border-left: 3px solid #4a90e2;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }
        .crm-history-item.is-pending { border-left-color: #f0ad4e; background: #fffaf2; }
        .crm-history-item.is-overdue { border-left-color: #dc3545; background: #fdf2f3; }
        .crm-history-meta {
            font-size: 12px;
            color: #95a5a6;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .crm-history-text {
            font-size: 14px;
            color: #1f2d3d;
            white-space: pre-wrap;
            margin-bottom: 4px;
        }
        .crm-history-extra {
            font-size: 13px;
            color: #5a6b7c;
            margin-bottom: 6px;
        }

        .crm-modal-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
        }
        .crm-modal-alert i { margin-top: 2px; }
        .crm-modal-alert-warning { background: #fff8e1; border: 1px solid #ffe082; color: #8a6d00; }
        .crm-modal-alert-info    { background: #e7f3fe; border: 1px solid #b6dcff; color: #1c5d99; }
        .crm-modal-alert-danger  { background: #fdecea; border: 1px solid #f5b5b0; color: #a82b1f; }
    </style>
@endpush

    <div class="crm-detail">

        {{-- ====================== HEADER ====================== --}}
        <div class="crm-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="crm-id">Oportunidad #{{ $opp->id }}</div>
                    <h1 class="crm-title">{{ optional($opp->person)->name ?? 'Sin cliente' }}</h1>
                    <div class="crm-meta">
                        Vendedor: <strong>{{ optional($opp->user)->name ?? '—' }}</strong>
                        &nbsp;·&nbsp;
                        Creado: {{ $opp->created_at?->format('Y-m-d H:i') ?? '—' }}
                        @if($pendingT > 0)
                            &nbsp;·&nbsp;
                            <span class="text-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $pendingT }} tarea{{ $pendingT > 1 ? 's' : '' }} pendiente{{ $pendingT > 1 ? 's' : '' }}{{ $overdueT > 0 ? " ({$overdueT} vencida".($overdueT>1?'s':'').")" : '' }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="crm-stage-wrap">
                    @include('sale::crm.partials.stage-badge', ['stage' => $stage])
                </div>
            </div>
        </div>

        {{-- ====================== TOOLBAR ====================== --}}
        <div class="crm-toolbar d-flex flex-wrap align-items-center">
            {{-- Grupo: crear actividad --}}
            <div class="crm-toolbar-group">
                <span class="crm-toolbar-label">Actividad</span>
                <button type="button" class="crm-btn-primary" data-bs-toggle="modal" data-bs-target="#noteModal">
                    <i class="fas fa-sticky-note"></i> Nota
                </button>
                <button type="button" class="crm-btn-primary" data-bs-toggle="modal" data-bs-target="#callModal">
                    <i class="fas fa-phone"></i> Llamada
                </button>
                <button type="button" class="crm-btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
                    <i class="fas fa-check-square"></i> Tarea
                </button>
            </div>

            {{-- Grupo: historial --}}
            <div class="crm-toolbar-group">
                <span class="crm-toolbar-label">Historial</span>
                <button type="button" class="crm-btn-ghost" data-bs-toggle="modal" data-bs-target="#noteHistoryModal" title="Notas">
                    <i class="fas fa-sticky-note"></i>
                    @if($notes->count() > 0)<span class="badge bg-secondary ms-1">{{ $notes->count() }}</span>@endif
                </button>
                <button type="button" class="crm-btn-ghost" data-bs-toggle="modal" data-bs-target="#callHistoryModal" title="Llamadas">
                    <i class="fas fa-phone"></i>
                    @if($calls->count() > 0)<span class="badge bg-secondary ms-1">{{ $calls->count() }}</span>@endif
                </button>
                <button type="button" class="crm-btn-ghost" data-bs-toggle="modal" data-bs-target="#taskHistoryModal" title="Tareas">
                    <i class="fas fa-check-square"></i>
                    @if($tasks->count() > 0)<span class="badge bg-secondary ms-1">{{ $tasks->count() }}</span>@endif
                </button>
            </div>

            {{-- Grupo: cotización --}}
            <div class="crm-toolbar-group">
                <span class="crm-toolbar-label">Cotización</span>
                <button type="button" class="crm-btn-info" data-bs-toggle="modal" data-bs-target="#quotationFileModal">
                    <i class="fas fa-paperclip"></i> Adjuntar
                </button>
                <form action="{{ route('tenant.crm.quotation', ['id' => $opp->id]) }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="crm-btn-outline-info">
                        <i class="fas fa-file-invoice-dollar"></i> Crear
                    </button>
                </form>
            </div>

            {{-- Grupo: etapa (derecha) --}}
            <div class="crm-toolbar-group">
                <span class="crm-toolbar-label">Etapa</span>
                @php
                    $currentStageCode = $stage->code ?? null;
                    $stageTransitions = [
                        'contacted'   => ['label' => 'Contactado',   'icon' => 'fa-phone'],
                        'interested'  => ['label' => 'Interesado',   'icon' => 'fa-thumbs-up'],
                        'proposal'    => ['label' => 'Propuesta',    'icon' => 'fa-file-invoice'],
                        'negotiation' => ['label' => 'Negociación',  'icon' => 'fa-handshake'],
                    ];
                @endphp
                <div class="dropdown">
                    <button class="crm-btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            {{ $currentStageCode === 'won' || $currentStageCode === 'lost' ? 'disabled' : '' }}>
                        <i class="fas fa-exchange-alt"></i> Cambiar
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($stageTransitions as $code => $info)
                            @if($currentStageCode !== $code)
                                <li><a class="dropdown-item crm-stage-change" href="#" data-stage="{{ $code }}" data-opp-id="{{ $opp->id }}">
                                    <i class="fas {{ $info['icon'] }} fa-fw me-2"></i>{{ $info['label'] }}
                                </a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @if($currentStageCode !== 'won')
                    <form action="{{ route('tenant.crm.won', ['id' => $opp->id]) }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="crm-btn-won"><i class="fas fa-trophy"></i> Ganado</button>
                    </form>
                @endif
                @if($currentStageCode !== 'lost')
                    <button type="button" class="crm-btn-lost" data-bs-toggle="modal" data-bs-target="#lostModal">
                        <i class="fas fa-times-circle"></i> Perdido
                    </button>
                @endif
            </div>
        </div>

        {{-- ====================== CONTENIDO ====================== --}}
        <div class="row">
            {{-- Columna izquierda: Timeline --}}
            <div class="col-md-7">
                <div class="crm-card">
                    <div class="crm-card-header">
                        <h3><i class="fas fa-stream text-primary"></i> Cambios de etapa</h3>
                        <small class="text-muted">{{ $timelineActivities->count() }} evento{{ $timelineActivities->count() !== 1 ? 's' : '' }}</small>
                    </div>
                    <div class="crm-card-body">
                        @if($timelineActivities->count() > 0)
                            <div class="crm-timeline">
                                @foreach($timelineActivities as $a)
                                    <div class="crm-timeline-item">
                                        <div class="crm-timeline-dot"></div>
                                        <div class="crm-timeline-content">
                                            <div class="crm-timeline-meta">
                                                <i class="fas fa-user"></i> {{ optional($a->user)->name ?? 'system' }}
                                                &nbsp;·&nbsp;
                                                <i class="fas fa-clock"></i> {{ $a->created_at->format('Y-m-d H:i') }}
                                            </div>
                                            <div class="crm-timeline-text">{{ $a->description }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="crm-empty">
                                <i class="fas fa-stream"></i>
                                Aún no se cambió la etapa de esta oportunidad.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Archivos adjuntos --}}
                @if($opp->files && $opp->files->count() > 0)
                    <div class="crm-card">
                        <div class="crm-card-header">
                            <h3><i class="fas fa-paperclip text-info"></i> Cotizaciones adjuntas</h3>
                            <small class="text-muted">{{ $opp->files->count() }} archivo{{ $opp->files->count() !== 1 ? 's' : '' }}</small>
                        </div>
                        <div class="crm-card-body p-0">
                            @foreach($opp->files as $f)
                                @php
                                    $ext = strtolower(pathinfo($f->filename, PATHINFO_EXTENSION));
                                    $iconClass = match($ext) {
                                        'pdf' => 'fa-file-pdf text-danger',
                                        'doc', 'docx' => 'fa-file-word text-primary',
                                        'xls', 'xlsx' => 'fa-file-excel text-success',
                                        'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image text-info',
                                        default => 'fa-file text-secondary',
                                    };
                                @endphp
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas {{ $iconClass }} fa-lg"></i>
                                        <span>{{ $f->filename }}</span>
                                    </div>
                                    <a href="{{ route('tenant.crm.quotation.file.download', ['id' => $opp->id, 'filename' => $f->filename]) }}"
                                       class="crm-btn-ghost">
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Columna derecha: Datos del cliente / Items / Cotización --}}
            <div class="col-md-5">
                <div class="crm-card">
                    <div class="crm-card-header">
                        <h3><i class="fas fa-user text-primary"></i> Cliente</h3>
                    </div>
                    <div class="crm-card-body">
                        <div class="crm-info-row">
                            <div class="crm-info-label">Nombre</div>
                            <div class="crm-info-value"><strong>{{ optional($opp->person)->name ?? '—' }}</strong></div>
                        </div>
                        <div class="crm-info-row">
                            <div class="crm-info-label">Documento</div>
                            <div class="crm-info-value">{{ optional($opp->person)->number ?? '—' }}</div>
                        </div>
                        @if(optional($opp->person)->address)
                            <div class="crm-info-row">
                                <div class="crm-info-label">Dirección</div>
                                <div class="crm-info-value">{{ $opp->person->address }}</div>
                            </div>
                        @endif
                        @if(optional($opp->person)->telephone)
                            <div class="crm-info-row">
                                <div class="crm-info-label">Teléfono</div>
                                <div class="crm-info-value">{{ $opp->person->telephone }}</div>
                            </div>
                        @endif
                        @if(optional($opp->person)->email)
                            <div class="crm-info-row">
                                <div class="crm-info-label">Email</div>
                                <div class="crm-info-value">{{ $opp->person->email }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="crm-card">
                    <div class="crm-card-header">
                        <h3><i class="fas fa-boxes-stacked text-primary"></i> Productos / Servicios</h3>
                        <small class="text-muted">{{ $opp->items->count() }}</small>
                    </div>
                    <div class="crm-card-body p-0">
                        @forelse($opp->items as $item)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <div>
                                    <i class="fas fa-box text-muted me-2"></i>
                                    <strong>{{ optional($item->item)->description ?? '(item #' . $item->item_id . ')' }}</strong>
                                    @if(optional($item->item)->internal_id)
                                        <small class="text-muted ms-2">({{ $item->item->internal_id }})</small>
                                    @endif
                                </div>
                                <span class="badge bg-secondary">× {{ $item->quantity ?? 1 }}</span>
                            </div>
                        @empty
                            <div class="crm-empty">
                                <i class="fas fa-box-open"></i>
                                Sin productos/servicios asociados.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="crm-card">
                    <div class="crm-card-header">
                        <h3><i class="fas fa-file-invoice-dollar text-primary"></i> Cotización relacionada</h3>
                    </div>
                    <div class="crm-card-body">
                        @if($opp->quotation)
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $opp->quotation->prefix ?? '' }}-{{ $opp->quotation->id ?? '' }}</strong>
                                    @if($opp->quotation->total)
                                        <div class="text-muted small">Total: S/ {{ number_format($opp->quotation->total, 2) }}</div>
                                    @endif
                                </div>
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        @else
                            <div class="crm-empty">
                                <i class="fas fa-file-invoice"></i>
                                Aún no se ha creado una cotización.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================== MODALES ====================== --}}
    @include('sale::crm.partials.note-modal', ['opportunity' => $opp])
    @include('sale::crm.partials.call-modal', ['opportunity' => $opp])
    @include('sale::crm.partials.task-modal', ['opportunity' => $opp, 'pendingTasksCount' => $pendingTasksCount])

    @include('sale::crm.partials.history-modal', [
        'modalId'        => 'noteHistoryModal',
        'type'           => 'note',
        'title'          => 'Historial de notas',
        'iconClass'      => 'fas fa-sticky-note',
        'items'          => $notes,
    ])
    @include('sale::crm.partials.history-modal', [
        'modalId'        => 'callHistoryModal',
        'type'           => 'call',
        'title'          => 'Historial de llamadas',
        'iconClass'      => 'fas fa-phone',
        'items'          => $calls,
    ])
    @include('sale::crm.partials.history-modal', [
        'modalId'        => 'taskHistoryModal',
        'type'           => 'task',
        'title'          => 'Historial de tareas',
        'iconClass'      => 'fas fa-check-square',
        'items'          => $tasks,
        'completeRoute'  => 'tenant.crm.activities.complete',
    ])

    {{-- Modal: adjuntar cotización --}}
    <div class="modal fade" id="quotationFileModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="quotationFileForm"
                  action="{{ route('tenant.crm.quotation.file', ['id' => $opp->id]) }}"
                  data-ajax-submit="1"
                  class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paperclip fa-fw me-1"></i>Adjuntar cotización
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="quotationFileErrors" class="crm-modal-alert crm-modal-alert-danger" style="display:none;"></div>
                    <div class="form-group mb-3">
                        <label for="quotation_file" class="form-label">Archivo de cotización</label>
                        <input type="file" name="quotation_file" id="quotation_file"
                               class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        <small class="form-text text-muted">Formatos: PDF, Word, Excel. Tamaño máximo: 10 MB.</small>
                    </div>
                    <div class="crm-modal-alert crm-modal-alert-info">
                        <i class="fas fa-info-circle"></i>
                        <div>El archivo se guarda en la oportunidad y aparece en la lista de cotizaciones adjuntas.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="crm-btn-info"><i class="fas fa-upload"></i> Subir</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: perdido --}}
    <div class="modal fade" id="lostModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('tenant.crm.lost', ['id' => $opp->id]) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-times-circle text-danger me-1"></i>Marcar como perdido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label for="lost_reason">Motivo</label>
                    <textarea name="lost_reason" id="lost_reason" class="form-control" required maxlength="500" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ponytail: handler para adjuntar cotización (file upload via fetch)
    var qForm = document.getElementById('quotationFileForm');
    if (qForm) {
        qForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var submitBtn = qForm.querySelector('button[type="submit"]');
            var origHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin fa-fw me-1"></i>Subiendo...';
            var errBox = document.getElementById('quotationFileErrors');
            errBox.textContent = ''; errBox.style.display = 'none';
            var fd = new FormData(qForm); fd.delete('_token');
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(qForm.action, {
                method: 'POST', body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                credentials: 'same-origin'
            }).then(function (resp) {
                return resp.json().then(function (data) {
                    if (resp.ok && data && data.success) {
                        var m = document.getElementById('quotationFileModal');
                        if (m) { m.classList.remove('show'); m.style.display = 'none'; document.body.classList.remove('modal-open'); var bd = document.querySelector('.modal-backdrop'); if (bd) bd.remove(); }
                        window.location.reload();
                    } else {
                        errBox.textContent = (data && data.message) ? (typeof data.message === 'string' ? data.message : JSON.stringify(data.message)) : 'Error ' + resp.status;
                        errBox.style.display = 'block';
                        submitBtn.disabled = false; submitBtn.innerHTML = origHtml;
                    }
                });
            }).catch(function (err) {
                errBox.textContent = 'Error de red: ' + String(err.message || err);
                errBox.style.display = 'block';
                submitBtn.disabled = false; submitBtn.innerHTML = origHtml;
            });
        }, true);
    }

    // ponytail: handler para cambiar etapa
    document.querySelectorAll('.crm-stage-change').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault(); e.stopPropagation();
            var oppId = link.getAttribute('data-opp-id');
            var stageCode = link.getAttribute('data-stage');
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            link.style.pointerEvents = 'none'; link.style.opacity = '0.5';
            fetch('/crm/opportunities/' + oppId + '/stage', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                credentials: 'same-origin',
                body: JSON.stringify({ stage_code: stageCode })
            }).then(function (resp) {
                return resp.json().then(function (data) {
                    if (resp.ok && data && data.success) window.location.reload();
                    else { alert('Error: ' + (data && data.message ? data.message : 'Status ' + resp.status)); link.style.pointerEvents = ''; link.style.opacity = ''; }
                });
            }).catch(function (err) { alert('Error de red: ' + String(err.message || err)); link.style.pointerEvents = ''; link.style.opacity = ''; });
        }, true);
    });
});
</script>
@endpush
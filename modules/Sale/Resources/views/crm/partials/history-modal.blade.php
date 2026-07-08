@php
    $modalId        = $modalId        ?? 'historyModal';
    $type           = $type           ?? 'note';
    $title          = $title          ?? 'Historial';
    $iconClass      = $iconClass      ?? 'fas fa-clock-rotate-left';
    $items          = $items          ?? collect();
    $completeRoute  = $completeRoute  ?? null;
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="{{ $iconClass }} fa-fw me-1"></i>{{ $title }}
                    <span class="badge bg-secondary ms-2">{{ $items->count() }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @forelse($items as $a)
                    @php
                        $cls = '';
                        if ($type === 'task' && $a->status === 'pending') {
                            $cls = $a->due_date && $a->due_date->isPast() ? 'is-overdue' : 'is-pending';
                        }
                    @endphp
                    <div class="crm-history-item {{ $cls }}">
                        <div class="crm-history-meta">
                            <span><i class="fas fa-user"></i> {{ optional($a->user)->name ?? 'system' }}</span>
                            <span><i class="fas fa-clock"></i> {{ $a->created_at->format('Y-m-d H:i') }}</span>
                            @if($a->status === 'pending')
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @elseif($a->status === 'done' && $a->completed_at)
                                <span class="badge bg-success">Hecha {{ $a->completed_at->format('Y-m-d H:i') }}</span>
                            @endif
                        </div>

                        @if($type === 'call' && isset($a->payload['result']))
                            <div class="crm-history-extra">
                                <strong>Resultado:</strong>
                                <span class="badge bg-info">{{ $a->payload['result'] }}</span>
                            </div>
                        @endif

                        @if($type === 'task' && $a->due_date)
                            <div class="crm-history-extra">
                                <strong>Vence:</strong>
                                <span class="{{ $a->due_date->isPast() && $a->status === 'pending' ? 'text-danger fw-bold' : '' }}">
                                    {{ $a->due_date->format('Y-m-d H:i') }}
                                </span>
                                @if($a->due_date->isPast() && $a->status === 'pending')
                                    <span class="badge bg-danger ms-1">Vencida</span>
                                @endif
                            </div>
                        @endif

                        <div class="crm-history-text">{{ $a->description }}</div>

                        @if($type === 'task' && $a->status === 'pending' && $completeRoute)
                            <form action="{{ route($completeRoute, ['id' => $a->id]) }}" method="POST" class="mt-2 d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-check fa-fw me-1"></i>Marcar hecha
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="crm-empty">
                        <i class="{{ $iconClass }}"></i>
                        Sin {{ strtolower($title) }} registradas aún.
                    </div>
                @endforelse
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
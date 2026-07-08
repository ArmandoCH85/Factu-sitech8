@extends('tenant.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Oportunidades</h2>
    </div>

    <form method="GET" action="{{ route('tenant.crm.opportunities') }}" class="form-inline mb-3">
        <select name="stage" class="form-control mr-2">
            <option value="">-- Etapa --</option>
            <option value="proposal" {{ request('stage') === 'proposal' ? 'selected' : '' }}>Propuesta</option>
            <option value="negotiation" {{ request('stage') === 'negotiation' ? 'selected' : '' }}>Negociación</option>
            <option value="won" {{ request('stage') === 'won' ? 'selected' : '' }}>Ganado</option>
            <option value="lost" {{ request('stage') === 'lost' ? 'selected' : '' }}>Perdido</option>
        </select>
        <label class="form-check-label mr-2">
            <input type="checkbox" name="current_month" value="1" {{ request('current_month') ? 'checked' : '' }} /> Mes actual
        </label>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Stage</th>
                <th>Monto</th>
                <th>Fecha emisión</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($opportunities as $opp)
                <tr>
                    <td>{{ optional($opp->person)->name ?? '-' }}</td>
                    <td>
                        @include('sale::crm.partials.stage-badge', ['stage' => $opp->crmStage])
                    </td>
                    <td>{{ number_format($opp->total, 2) }}</td>
                    <td>{{ optional($opp->date_of_issue)?->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('tenant.crm.show', ['id' => $opp->id]) }}" class="btn btn-sm btn-info">Ver</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Sin oportunidades registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $opportunities->links() }}
@endsection

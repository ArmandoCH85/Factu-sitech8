@extends('tenant.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Leads</h2>
        <div>
            <a href="{{ route('tenant.crm.leads.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Lead
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('tenant.crm.leads') }}" class="form-inline mb-3">
        <input type="text" name="customer_name" value="{{ request('customer_name') }}" placeholder="Buscar por cliente" class="form-control mr-2" />
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    @include('sale::crm.partials.stage-badge', ['stages' => $stages])

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Stage</th>
                <th>Monto</th>
                <th>Vendedor</th>
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
                    <td>{{ optional($opp->user)->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('tenant.crm.show', ['id' => $opp->id]) }}" class="btn btn-sm btn-info">Ver</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Sin leads registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $opportunities->links() }}
@endsection

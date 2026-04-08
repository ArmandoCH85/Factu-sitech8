@extends('system.layouts.auth')

@section('content')
    <div style="--brand-color: {{ $brand['color'] ?? '#0d8796' }}; --action-color: #101d3f; background: linear-gradient(rgba(160, 178, 198, 0.62), rgba(150, 170, 192, 0.66)), url('/images/fondo-busqueda.png') center / cover no-repeat; min-height: 100vh; padding: 32px 12px;">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 18px;">
                    <div class="card-header" style="background: #ffffff; color: #0f2942; padding: 1.1rem 1.25rem; border-top: 4px solid var(--brand-color); border-bottom: 1px solid #e5edf3;">
                        <div class="text-center">
                            <div class="mb-2">
                                @if(!empty($brand['logo']))
                                    <img src="{{ $brand['logo'] }}" alt="Logo {{ $brand['name'] }}" style="height: 108px; width: auto; max-width: 360px; object-fit: contain; background: #f1f6fa; padding: 8px 12px; border-radius: 12px; border: 1px solid #e2ebf2;">
                                @else
                                    <div style="height: 108px; width: 108px; display:inline-flex; align-items:center; justify-content:center; background: #f1f6fa; border-radius: 16px; font-weight: 700; font-size: 1.75rem; color: var(--brand-color); border: 1px solid #e2ebf2;">
                                        {{ strtoupper(substr($brand['name'] ?? 'C', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h3 class="my-0" style="color:#163a5c; font-weight:700; letter-spacing:.02em;">BUSCAR COMPROBANTE ELECTRONICO</h3>
                                <small class="d-block mt-1" style="color:#5b728b; font-weight:500;">{{ $brand['name'] ?? 'Consulta pública de comprobantes' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="background: #ffffff;">
                        <div class="mb-4 p-3 rounded-lg" style="background: #f8fbfc; border: 1px solid rgba(0,0,0,.05); box-shadow: inset 0 1px 0 rgba(255,255,255,.85); border-left: 4px solid var(--brand-color);">
                            <strong class="d-block" style="color: var(--brand-color); font-size: 1rem;">{{ $brand['name'] ?? 'Buscador público' }}</strong>
                            <small class="text-muted d-block mt-1">Consulta de validez de comprobantes electrónicos de pago. Ingrese los datos exactos del comprobante para obtener XML y PDF.</small>
                        </div>

                        <form method="POST" action="{{ $tenantSlug ? route('system.public_search.widget.search', ['slug' => $tenantSlug]) : route('system.public_search.search') }}" autocomplete="off">
                            @csrf

                            @if($tenantSlug)
                                <input type="hidden" name="tenant_slug" value="{{ $tenantSlug }}">
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ruc_emisor">RUC Emisor</label>
                                        <input
                                            type="text"
                                            name="ruc_emisor"
                                            id="ruc_emisor"
                                            maxlength="11"
                                            class="form-control @error('ruc_emisor') is-invalid @enderror {{ $tenantSlug ? 'bg-light' : '' }}"
                                            value="{{ old('ruc_emisor', $tenantSlug ? ($brand['ruc'] ?? $form['ruc_emisor']) : $form['ruc_emisor']) }}"
                                            placeholder="Ej: 20123456789"
                                        >
                                        @error('ruc_emisor')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="document_type_id">Tipo Documento</label>
                                        <select name="document_type_id" id="document_type_id" class="form-control @error('document_type_id') is-invalid @enderror">
                                            @foreach($documentTypes as $id => $label)
                                                <option value="{{ $id }}" {{ old('document_type_id', $form['document_type_id']) === $id ? 'selected' : '' }}>{{ mb_strtoupper($label, 'UTF-8') }}</option>
                                            @endforeach
                                        </select>
                                        @error('document_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date_of_issue">Fecha Emisión</label>
                                        <input type="date" name="date_of_issue" id="date_of_issue" class="form-control @error('date_of_issue') is-invalid @enderror" value="{{ old('date_of_issue', $form['date_of_issue']) }}">
                                        @error('date_of_issue')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="series">Serie</label>
                                        <input type="text" name="series" id="series" maxlength="10" class="form-control @error('series') is-invalid @enderror" value="{{ old('series', $form['series']) }}" placeholder="Ej: F001">
                                        @error('series')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="number">Número</label>
                                        <input type="text" name="number" id="number" maxlength="20" class="form-control @error('number') is-invalid @enderror" value="{{ old('number', $form['number']) }}" placeholder="Ej: 12345">
                                        @error('number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="total">Monto Total</label>
                                        <input type="text" name="total" id="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total', $form['total']) }}" placeholder="Ej: 100.00">
                                        @error('total')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label for="customer_number">Doc. Cliente (RUC/DNI)</label>
                                        <input type="text" name="customer_number" id="customer_number" maxlength="15" class="form-control @error('customer_number') is-invalid @enderror" value="{{ old('customer_number', $form['customer_number']) }}" placeholder="Número de documento">
                                        @error('customer_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button class="btn text-white px-5 py-2" style="background: var(--action-color); border-radius: 10px; min-width: 160px; box-shadow: 0 8px 18px rgba(16,29,63,.28); border-color: var(--action-color);" type="submit">BUSCAR</button>
                            </div>
                        </form>

                        @if($statusMessage)
                            <div class="alert mt-4 {{ $statusType === 'warning' ? 'alert-warning' : 'alert-info' }} mb-0" style="border-radius: 12px;">
                                {{ $statusMessage }}
                            </div>
                        @endif

                        <div class="table-responsive mt-4">
                            <table class="table table-borderless mb-0">
                                <thead style="background:#f3f7fa; border-radius: 12px; overflow: hidden;">
                                    <tr>
                                        <th class="py-2" style="font-size:.8rem; letter-spacing:.03em; color:#334155;">CLIENTE</th>
                                        <th class="py-2" style="font-size:.8rem; letter-spacing:.03em; color:#334155;">NÚMERO</th>
                                        <th class="py-2 text-right" style="font-size:.8rem; letter-spacing:.03em; color:#334155;">TOTAL</th>
                                        <th class="py-2 text-right" style="font-size:.8rem; letter-spacing:.03em; color:#334155;">DESCARGAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($result)
                                        <tr style="background:#fff; border-radius: 12px; box-shadow: 0 4px 14px rgba(15,23,42,.04);">
                                            <td class="py-3">{{ $result['customer'] }}</td>
                                            <td class="py-3">{{ $result['number'] }}</td>
                                            <td class="py-3 text-right">{{ $result['total'] }}</td>
                                            <td class="py-3 text-right">
                                                <div class="d-inline-flex align-items-center justify-content-end" style="gap: 8px;">
                                                    <a
                                                        href="{{ $result['download_xml'] }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="btn btn-sm text-white px-3 py-1"
                                                        style="background: #2f6df6; border-radius: 999px; box-shadow: 0 6px 14px rgba(47,109,246,.22); font-size: .78rem;"
                                                    >
                                                        XML
                                                    </a>
                                                    <a
                                                        href="{{ $result['download_pdf'] }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="btn btn-sm text-white px-3 py-1"
                                                        style="background: #2f6df6; border-radius: 999px; box-shadow: 0 6px 14px rgba(47,109,246,.22); font-size: .78rem;"
                                                    >
                                                        PDF
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Ingrese los datos del comprobante para visualizar los resultados aquí.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection

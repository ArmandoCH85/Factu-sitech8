@extends('system.layouts.auth')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header text-white" style="background: #0d8796;">
                        <h3 class="my-0">BUSCAR COMPROBANTE ELECTRONICO</h3>
                        <small class="d-block mt-1">Consulta de validez de comprobantes electronicos de pago.</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('system.public_search.search') }}" autocomplete="off">
                            @csrf

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ruc_emisor">RUC Emisor</label>
                                        <input type="text" name="ruc_emisor" id="ruc_emisor" maxlength="11" class="form-control @error('ruc_emisor') is-invalid @enderror" value="{{ old('ruc_emisor', $form['ruc_emisor']) }}" placeholder="Ej: 20123456789">
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
                                                <option value="{{ $id }}" {{ old('document_type_id', $form['document_type_id']) === $id ? 'selected' : '' }}>{{ strtoupper($label) }}</option>
                                            @endforeach
                                        </select>
                                        @error('document_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="date_of_issue">Fecha Emision</label>
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
                                        <label for="number">Numero</label>
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
                                        <input type="text" name="customer_number" id="customer_number" maxlength="15" class="form-control @error('customer_number') is-invalid @enderror" value="{{ old('customer_number', $form['customer_number']) }}" placeholder="Numero documento">
                                        @error('customer_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button class="btn text-white px-5" style="background: #0d8796;" type="submit">BUSCAR</button>
                            </div>
                        </form>

                        @if($statusMessage)
                            <div class="alert mt-4 {{ $statusType === 'warning' ? 'alert-warning' : 'alert-info' }} mb-0">
                                {{ $statusMessage }}
                            </div>
                        @endif

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>CLIENTE</th>
                                        <th>NUMERO</th>
                                        <th class="text-right">TOTAL</th>
                                        <th class="text-right">DESCARGAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($result)
                                        <tr>
                                            <td>{{ $result['customer'] }}</td>
                                            <td>{{ $result['number'] }}</td>
                                            <td class="text-right">{{ $result['total'] }}</td>
                                            <td class="text-right">
                                                <a href="{{ $result['download_xml'] }}" target="_blank" rel="noopener">XML</a>
                                                <span class="mx-1">|</span>
                                                <a href="{{ $result['download_pdf'] }}" target="_blank" rel="noopener">PDF</a>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Ingrese los datos del comprobante para visualizar los resultados aqui.</td>
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
@endsection

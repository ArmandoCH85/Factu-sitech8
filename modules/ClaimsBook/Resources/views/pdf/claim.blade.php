<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* ── Encabezado ── */
        .header {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header-title {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-subtitle {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        .header-meta {
            font-size: 10px;
            color: #333;
            margin-top: 6px;
        }
        .header-meta strong { color: #000; }

        /* ── Badge tipo (reclamo / queja) ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .badge-reclamo { background: #fee2e2; color: #991b1b; }
        .badge-queja   { background: #fef3c7; color: #92400e; }

        /* ── Secciones ── */
        .section {
            margin-bottom: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .section-title {
            background: #f4f4f5;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #555;
            padding: 5px 10px;
            border-bottom: 1px solid #ddd;
        }
        .section-body {
            padding: 10px;
        }

        /* ── Grid de campos ── */
        table.fields {
            width: 100%;
            border-collapse: collapse;
        }
        table.fields td {
            padding: 4px 8px 4px 0;
            vertical-align: top;
            width: 50%;
        }
        table.fields td.full { width: 100%; }
        .field-label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: .4px;
            display: block;
            margin-bottom: 1px;
        }
        .field-value {
            font-size: 11px;
            color: #1a1a1a;
        }
        .field-value.text-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 3px;
            padding: 6px 8px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* ── Estado / badge de estado ── */
        .status-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        /* ── Código público ── */
        .public-code-bar {
            background: #f0f9eb;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 11px;
        }
        .public-code-bar strong { font-size: 13px; color: #155724; }

        /* ── Pie ── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 9px;
            color: #aaa;
            text-align: center;
        }

        /* ── Resolución ── */
        .resolution-block {
            background: #fffbf0;
            border-left: 3px solid #f59e0b;
            padding: 8px 10px;
            font-size: 11px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="header">
        <div class="header-title">Libro de Reclamaciones — Constancia</div>
        <div class="header-subtitle">Según lo dispuesto por el Código de Protección y Defensa del Consumidor (Ley N° 29571)</div>
        <div class="header-meta">
            <strong>Código:</strong> {{ $claim->code }}
            &nbsp;&nbsp;
            <strong>Fecha de registro:</strong> {{ $claim->created_at ? $claim->created_at->format('d/m/Y H:i') : '—' }}
            &nbsp;&nbsp;
            <strong>Tipo:</strong>
            <span class="badge {{ $claim->claim_type === 'reclamo' ? 'badge-reclamo' : 'badge-queja' }}">
                {{ $claim->claim_type === 'reclamo' ? 'Reclamo' : 'Queja' }}
            </span>
        </div>
    </div>

    {{-- Código público de consulta --}}
    @if($claim->public_code)
    <div class="public-code-bar">
        Código de consulta: <strong>{{ $claim->public_code }}</strong>
    </div>
    @endif

    {{-- Sección 1: Datos del reclamante --}}
    <div class="section">
        <div class="section-title">1. Datos del reclamante</div>
        <div class="section-body">
            <table class="fields">
                <tr>
                    <td>
                        <span class="field-label">Tipo de documento</span>
                        <span class="field-value">
                            {{ $claim->identityDocumentType ? $claim->identityDocumentType->description : $claim->identity_document_type }}
                        </span>
                    </td>
                    <td>
                        <span class="field-label">N° de documento</span>
                        <span class="field-value">{{ $claim->identity_document_number ?? '—' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Nombre completo</span>
                        <span class="field-value">{{ $claim->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Email</span>
                        <span class="field-value">{{ $claim->email ?? '—' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Teléfono</span>
                        <span class="field-value">{{ $claim->phone ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Distrito</span>
                        <span class="field-value">{{ $claim->district ? $claim->district->description : '—' }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="full" colspan="2">
                        <span class="field-label">Dirección</span>
                        <span class="field-value">{{ $claim->address ?? '—' }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Sección 2: Bien contratado --}}
    <div class="section">
        <div class="section-title">2. Bien o servicio contratado</div>
        <div class="section-body">
            <table class="fields">
                <tr>
                    <td>
                        <span class="field-label">Tipo de bien</span>
                        <span class="field-value">{{ $claim->asset_type === 'producto' ? 'Producto' : 'Servicio' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Fecha de contratación</span>
                        <span class="field-value">{{ $claim->asset_date ? $claim->asset_date->format('d/m/Y') : '—' }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="full" colspan="2">
                        <span class="field-label">Descripción del bien / servicio</span>
                        <span class="field-value">{{ $claim->asset_description ?? '—' }}</span>
                    </td>
                </tr>
                @if($claim->has_receipt)
                <tr>
                    <td>
                        <span class="field-label">N° de comprobante</span>
                        <span class="field-value">{{ $claim->receipt_series }}-{{ $claim->receipt_number }}</span>
                    </td>
                    <td>
                        <span class="field-label">Monto reclamado</span>
                        <span class="field-value">{{ $claim->receipt_currency }} {{ number_format($claim->receipt_amount, 2) }}</span>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Sección 3: Detalle del reclamo --}}
    <div class="section">
        <div class="section-title">3. Detalle del reclamo</div>
        <div class="section-body">
            <table class="fields">
                <tr>
                    <td>
                        <span class="field-label">Canal de atención</span>
                        <span class="field-value">{{ $claim->channel ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Número de seguimiento</span>
                        <span class="field-value">{{ $claim->tracking_number ?? '0' }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="full" colspan="2">
                        <span class="field-label">Detalle del reclamo</span>
                        <div class="field-value text-block">{{ $claim->detail ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="full" colspan="2" style="padding-top:8px;">
                        <span class="field-label">Pedido / Resultado esperado</span>
                        <div class="field-value text-block">{{ $claim->expected_result ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Sección 4: Estado y fechas --}}
    <div class="section">
        <div class="section-title">4. Estado y plazos</div>
        <div class="section-body">
            <table class="fields">
                <tr>
                    <td>
                        <span class="field-label">Estado actual</span>
                        <span class="field-value">
                            @if($claim->statusClaim)
                                <span class="status-pill" style="background:{{ $claim->statusClaim->color ?? '#e5e7eb' }};color:#1a1a1a;">
                                    {{ $claim->statusClaim->description }}
                                </span>
                            @else
                                —
                            @endif
                        </span>
                    </td>
                    <td>
                        <span class="field-label">¿Cerrado?</span>
                        <span class="field-value">{{ $claim->is_closed ? 'Sí' : 'No' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="field-label">Fecha de registro</span>
                        <span class="field-value">{{ $claim->created_at ? $claim->created_at->format('d/m/Y') : '—' }}</span>
                    </td>
                    <td>
                        <span class="field-label">Fecha límite (15 días hábiles)</span>
                        <span class="field-value">{{ $claim->due_date ? $claim->due_date->format('d/m/Y') : '—' }}</span>
                    </td>
                </tr>
                @if($claim->assignedUser)
                <tr>
                    <td class="full" colspan="2">
                        <span class="field-label">Responsable asignado</span>
                        <span class="field-value">{{ $claim->assignedUser->name }}</span>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Sección 5: Resolución (si existe) --}}
    @if($claim->resolution)
    <div class="section">
        <div class="section-title">5. Resolución adoptada</div>
        <div class="section-body">
            <div class="resolution-block">{{ $claim->resolution }}</div>
        </div>
    </div>
    @endif

    {{-- Pie de página --}}
    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i:s') }}
        &nbsp;·&nbsp;
        Código: {{ $claim->code }}
        &nbsp;·&nbsp;
        {{ config('app.name') }}
    </div>

</body>
</html>

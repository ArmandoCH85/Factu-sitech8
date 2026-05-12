<template>
    <div class="card">
        <div class="card-header bg-info bg-info-customer-admin">
            <h3 class="my-0">Visibilidad de columnas por defecto</h3>
        </div>
        <div class="card-body px-3 pt-3 pb-2">
            <p class="text-muted mb-3" style="font-size:0.85rem;">
                Configure qué columnas se mostrarán por defecto en cada listado para usuarios nuevos o sin configuración guardada.
            </p>

            <el-collapse v-model="openSections" class="vc-collapse">
                <el-collapse-item
                    v-for="section in sections"
                    :key="section.key"
                    :name="section.key"
                    class="vc-collapse-item"
                >
                    <template slot="title">
                        <div class="vc-section__header">
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <div class="vc-section__icon" :style="{ background: section.color + '18', color: section.color }" v-html="section.svg"></div>
                                <span class="vc-section__title">{{ section.label }}</span>
                            </div>
                            <span class="vc-section__count">
                                {{ section.modules.length }} {{ section.modules.length === 1 ? 'listado' : 'listados' }}
                            </span>
                        </div>
                    </template>

                    <div
                        v-for="(moduleKey, idx) in section.modules"
                        :key="moduleKey"
                        class="vc-module-item"
                        :class="{ 'vc-module-item--border': idx > 0 }"
                    >
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <span class="vc-module-item__dot" :style="{ background: section.color }"></span>
                            <span class="vc-module-item__label">{{ moduleLabel(moduleKey) }}</span>
                        </div>
                        <el-button size="small" @click="openDialog(moduleKey)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;vertical-align:-2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/></svg>
                            Editar columnas
                        </el-button>
                    </div>
                </el-collapse-item>
            </el-collapse>

            <el-dialog
                :title="'Columnas por defecto — ' + editingModuleLabel"
                :visible.sync="dialogVisible"
                width="560px"
                :close-on-click-modal="false"
            >
                <p class="text-muted mb-3" style="font-size:0.85rem;">
                    Las columnas marcadas se mostrarán por defecto a usuarios que aún no hayan personalizado su vista.
                </p>
                <div class="columns-grid">
                    <div v-for="(col, key) in editingColumns" :key="key">
                        <el-checkbox v-model="col.visible">{{ col.title }}</el-checkbox>
                    </div>
                </div>

                <div class="preview-wrap">
                    <p class="preview-label">Vista previa</p>
                    <div class="preview-scroll">
                        <table class="preview-table">
                            <thead>
                                <tr>
                                    <th v-for="col in visibleColumnsList" :key="col.key">{{ col.title }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, i) in sampleRows" :key="i">
                                    <td v-for="col in visibleColumnsList" :key="col.key">{{ row[col.key] }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="visibleColumnsList.length === 0" class="preview-empty">
                            Sin columnas visibles
                        </p>
                    </div>
                </div>

                <span slot="footer">
                    <el-button @click="dialogVisible = false">Cancelar</el-button>
                    <el-button type="primary" :loading="saving" @click="saveModule">
                        Guardar
                    </el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>

<script>
const SAMPLE_BY_TYPE = {
    id:       [1, 2, 3],
    code:     ['P001', 'P002', 'S001'],
    unit:     ['NIU', 'NIU', 'ZZ'],
    image:    ['🖼', '🖼', '🖼'],
    text:     ['Ejemplo 1', 'Ejemplo 2', 'Ejemplo 3'],
    longtext: ['Descripción del producto A', 'Descripción del producto B', 'Descripción del servicio'],
    number:   [24, 80, 0],
    price:    ['2,500.00', '45.00', '120.00'],
    boolean:  ['Sí', 'Sí', 'No'],
    date:     ['01/05/2026', '05/05/2026', '10/05/2026'],
    document: ['F001-00001', 'B001-00002', 'F002-00003'],
    customer: ['Juan Pérez García', 'Empresa SAC', 'María López'],
    sunat:    ['43211503', '43211706', '81112100'],
    status:   ['Pendiente', 'En proceso', 'Cerrado'],
    currency: ['PEN', 'USD', 'PEN'],
    exchange: ['3.75', '3.80', '3.72'],
    action:   ['···', '···', '···'],
};

const MODULES = {
    order_notes_index: {
        label: 'Pedidos',
        columns: {
            date_of_issue:    { title: 'Fecha Emisión',       visible: true,  type: 'date'     },
            delivery_date:    { title: 'F.Entrega',           visible: true,  type: 'date'     },
            seller:           { title: 'Vendedor',            visible: true,  type: 'text'     },
            customer:         { title: 'Cliente',             visible: true,  type: 'customer' },
            state_type:       { title: 'Estado',              visible: true,  type: 'status'   },
            identifier:       { title: 'Pedido',              visible: true,  type: 'document' },
            documents:        { title: 'Comprobantes',        visible: true,  type: 'document' },
            sale_notes:       { title: 'Notas de venta',      visible: true,  type: 'document' },
            quotation:        { title: 'Cotización',          visible: false, type: 'document' },
            dispatches:       { title: 'Guías de Remisión',   visible: false, type: 'document' },
            mi_tienda_pe:     { title: 'Pedido MiTienda.Pe',  visible: false, type: 'text'     },
            currency_type:    { title: 'Moneda',              visible: true,  type: 'currency' },
            total_exportation:{ title: 'T.Exportación',       visible: false, type: 'price'    },
            total_unaffected: { title: 'T.Inafecto',          visible: false, type: 'price'    },
            total_exonerated: { title: 'T.Exonerado',         visible: false, type: 'price'    },
            total_taxed:      { title: 'T.Gravado',           visible: true,  type: 'price'    },
            total_igv:        { title: 'T.IGV',               visible: true,  type: 'price'    },
            balance:          { title: 'Saldo',               visible: true,  type: 'price'    },
            total:            { title: 'Total',               visible: true,  type: 'price'    },
            pdf:              { title: 'PDF',                  visible: true,  type: 'action'   },
            actions:          { title: 'Acciones',            visible: true,  type: 'action'   },
        },
    },
    contracts_index: {
        label: 'Contratos',
        columns: {
            date_of_issue:   { title: 'Fecha Emisión', visible: true,  type: 'date'     },
            delivery_date:   { title: 'F.Entrega',     visible: false, type: 'date'     },
            seller:          { title: 'Vendedor',      visible: true,  type: 'text'     },
            customer:        { title: 'Cliente',       visible: true,  type: 'customer' },
            state_type:      { title: 'Estado',        visible: true,  type: 'status'   },
            number:          { title: 'Contrato',      visible: true,  type: 'document' },
            quotation:       { title: 'Cotización',    visible: true,  type: 'document' },
            currency_type:   { title: 'Moneda',        visible: true,  type: 'currency' },
            total_exportation:{ title: 'T.Exportación',visible: false, type: 'price'    },
            total_free:      { title: 'T.Gratuito',    visible: false, type: 'price'    },
            total_unaffected:{ title: 'T.Inafecto',    visible: false, type: 'price'    },
            total_exonerated:{ title: 'T.Exonerado',   visible: false, type: 'price'    },
            total_taxed:     { title: 'T.Gravado',     visible: true,  type: 'price'    },
            total_igv:       { title: 'T.Igv',         visible: true,  type: 'price'    },
            total:           { title: 'Total',         visible: true,  type: 'price'    },
            actions:         { title: 'Acciones',      visible: true,  type: 'action'   },
        },
    },
    quotations_index: {
        label: 'Cotizaciones',
        columns: {
            date_of_issue:           { title: 'Fecha Emisión',      visible: true,  type: 'date'     },
            delivery_date:           { title: 'T.Entrega',          visible: false, type: 'date'     },
            registered_by:           { title: 'Registrado por',     visible: false, type: 'text'     },
            seller:                  { title: 'Vendedor',           visible: false, type: 'text'     },
            customer:                { title: 'Cliente',            visible: true,  type: 'customer' },
            state_type:              { title: 'Estado',             visible: true,  type: 'status'   },
            identifier:              { title: 'Cotización',         visible: true,  type: 'document' },
            documents:               { title: 'Comprobantes',       visible: false, type: 'document' },
            sale_notes:              { title: 'Notas de venta',     visible: false, type: 'document' },
            order_note:              { title: 'Pedidos',            visible: false, type: 'document' },
            sale_opportunity:        { title: 'Oportunidad Venta',  visible: false, type: 'document' },
            referential_information: { title: 'Inf.Referencial',    visible: false, type: 'text'     },
            contract:                { title: 'Contrato',           visible: false, type: 'text'     },
            exchange_rate_sale:      { title: 'Tipo de cambio',     visible: false, type: 'exchange' },
            currency_type_id:        { title: 'Moneda',             visible: false, type: 'currency' },
            payments:                { title: 'Pagos',              visible: true,  type: 'price'    },
            total_exportation:       { title: 'T.Exportación',      visible: false, type: 'price'    },
            total_free:              { title: 'T.Gratuito',         visible: false, type: 'price'    },
            total_unaffected:        { title: 'T.Inafecto',         visible: false, type: 'price'    },
            total_exonerated:        { title: 'T.Exonerado',        visible: false, type: 'price'    },
            total_taxed:             { title: 'T.Gravado',          visible: true,  type: 'price'    },
            total_igv:               { title: 'T.Igv',              visible: true,  type: 'price'    },
            total:                   { title: 'Total',              visible: true,  type: 'price'    },
            pdf:                     { title: 'PDF',                visible: true,  type: 'action'   },
            actions:                 { title: 'Acciones',           visible: true,  type: 'action'   },
        },
    },
    purchases_index: {
        label: 'Listado de compras',
        columns: {
            date_of_issue:    { title: 'F. Emisión',       visible: true,  type: 'date'     },
            date_of_due:      { title: 'F. Vencimiento',   visible: false, type: 'date'     },
            supplier:         { title: 'Proveedor',        visible: true,  type: 'customer' },
            state_type:       { title: 'Estado',           visible: true,  type: 'status'   },
            payment_state:    { title: 'Estado de pago',   visible: true,  type: 'status'   },
            number:           { title: 'Número',           visible: true,  type: 'document' },
            products:         { title: 'Productos',        visible: true,  type: 'text'     },
            warehouse:        { title: 'Almacén',          visible: true,  type: 'text'     },
            payments:         { title: 'Pagos',            visible: true,  type: 'action'   },
            currency_type:    { title: 'Moneda',           visible: true,  type: 'currency' },
            guides:           { title: 'Guias',            visible: false, type: 'document' },
            purchase_order:   { title: 'Orden de Compra',  visible: false, type: 'document' },
            total_free:       { title: 'T.Gratuita',       visible: false, type: 'price'    },
            total_unaffected: { title: 'T.Inafecta',       visible: false, type: 'price'    },
            total_exonerated: { title: 'T.Exonerado',      visible: false, type: 'price'    },
            total_taxed:      { title: 'T.Gravado',        visible: false, type: 'price'    },
            total_igv:        { title: 'T.Igv',            visible: false, type: 'price'    },
            total_perception: { title: 'Percepción',       visible: false, type: 'price'    },
            total:            { title: 'Total',            visible: true,  type: 'price'    },
            actions:          { title: 'Acciones',         visible: true,  type: 'action'   },
        },
    },
    sale_notes_index: {
        label: 'Notas de venta',
        columns: {
            seller_name:        { title: 'Vendedor',             visible: false, type: 'text'     },
            date_of_issue:      { title: 'Fecha Emisión',        visible: true,  type: 'date'     },
            date_payment:       { title: 'Fecha de pago',        visible: false, type: 'date'     },
            customer:           { title: 'Cliente',              visible: true,  type: 'customer' },
            full_number:        { title: 'Nota de Venta',        visible: true,  type: 'document' },
            state_type:         { title: 'Estado',               visible: true,  type: 'status'   },
            exchange_rate_sale: { title: 'Tipo de cambio',       visible: false, type: 'exchange' },
            currency_type:      { title: 'Moneda',               visible: true,  type: 'currency' },
            due_date:           { title: 'Fecha de Vencimiento', visible: false, type: 'date'     },
            total_exportation:  { title: 'T.Exportación',        visible: false, type: 'price'    },
            total_free:         { title: 'T.Gratuito',           visible: false, type: 'price'    },
            total_unaffected:   { title: 'T.Inafecto',           visible: false, type: 'price'    },
            total_exonerated:   { title: 'T.Exonerado',          visible: false, type: 'price'    },
            total_taxed:        { title: 'T.Gravado',            visible: false, type: 'price'    },
            total_igv:          { title: 'T.IGV',                visible: false, type: 'price'    },
            total:              { title: 'Total',                visible: true,  type: 'price'    },
            total_paid:         { title: 'Pagado',               visible: false, type: 'price'    },
            total_pending_paid: { title: 'Por pagar',            visible: false, type: 'price'    },
            documents:          { title: 'Comprobantes',         visible: true,  type: 'document' },
            payment_status:     { title: 'Estado pago',          visible: true,  type: 'status'   },
            purchase_order:     { title: 'Orden de compra',      visible: true,  type: 'document' },
            payments:           { title: 'Pagos',                visible: true,  type: 'action'   },
            download:           { title: 'Descarga',             visible: true,  type: 'action'   },
            recurrence:         { title: 'Recurrencia',          visible: false, type: 'text'     },
            region:             { title: 'Región',               visible: false, type: 'text'     },
            dispatch_status:    { title: 'Estado de despacho',   visible: false, type: 'status'   },
            type_period:        { title: 'Tipo Periodo',         visible: true,  type: 'text'     },
            quantity_period:    { title: 'Cantidad Periodo',     visible: true,  type: 'number'   },
            paid:               { title: 'Estado de Pago',       visible: false, type: 'status'   },
            license_plate:      { title: 'Placa',                visible: true,  type: 'text'     },
            actions:            { title: 'Acciones',             visible: true,  type: 'action'   },
        },
    },
    document_index: {
        label: 'Boleta / Factura',
        columns: {
            soap_type:         { title: 'Soap',                       visible: false, type: 'text'     },
            date_of_issue:     { title: 'Emisión',                    visible: true,  type: 'date'     },
            date_payment:      { title: 'Fecha de pago',              visible: false, type: 'date'     },
            date_of_due:       { title: 'F. Vencimiento',             visible: false, type: 'date'     },
            customer:          { title: 'Cliente',                    visible: true,  type: 'customer' },
            number:            { title: 'Número',                     visible: true,  type: 'document' },
            notes:             { title: 'Notas C/D',                  visible: false, type: 'document' },
            dispatch:          { title: 'Guía de Remisión',           visible: false, type: 'document' },
            sales_note:        { title: 'Nota de ventas',             visible: false, type: 'document' },
            order_note:        { title: 'Pedidos',                    visible: false, type: 'document' },
            send_it:           { title: 'Correo enviado',             visible: false, type: 'boolean'  },
            state_type:        { title: 'Estado',                     visible: true,  type: 'status'   },
            user_name:         { title: 'Usuario',                    visible: false, type: 'text'     },
            exchange_rate_sale:{ title: 'Tipo de cambio',             visible: false, type: 'exchange' },
            currency_type_id:  { title: 'Moneda',                     visible: false, type: 'currency' },
            guides:            { title: 'Guías',                      visible: false, type: 'document' },
            plate_numbers:     { title: 'Placa',                      visible: false, type: 'text'     },
            total_exportation: { title: 'T.Exportación',              visible: false, type: 'price'    },
            total_free:        { title: 'T.Gratuito',                 visible: false, type: 'price'    },
            total_unaffected:  { title: 'T.Inafecto',                 visible: false, type: 'price'    },
            total_exonerated:  { title: 'T.Exonerado',                visible: false, type: 'price'    },
            total_charge:      { title: 'T.Cargos',                   visible: false, type: 'price'    },
            total_taxed:       { title: 'T.Gravado',                  visible: true,  type: 'price'    },
            total_igv:         { title: 'T.Igv',                      visible: true,  type: 'price'    },
            total:             { title: 'Total',                      visible: false, type: 'price'    },
            balance:           { title: 'Saldo',                      visible: true,  type: 'price'    },
            purchase_order:    { title: 'Orden de Compra',            visible: false, type: 'document' },
            downloads:         { title: 'Descargas (XML/PDF/CDR)',     visible: true,  type: 'action'   },
            actions:           { title: 'Acciones',                   visible: true,  type: 'action'   },
        },
    },
    sale_opportunities_index: {
        label: 'Oportunidad de venta',
        columns: {
            date_of_issue:    { title: 'Fecha Emisión', visible: true,  type: 'date'     },
            sale:             { title: 'Vendedor',      visible: false, type: 'text'     },
            customer:         { title: 'Cliente',       visible: true,  type: 'customer' },
            state_type:       { title: 'Estado',        visible: true,  type: 'status'   },
            number:           { title: 'O. Venta',      visible: true,  type: 'document' },
            quotation:        { title: 'Cotización',    visible: true,  type: 'document' },
            purchase_order:   { title: 'O. Compra',     visible: true,  type: 'document' },
            currency_type:    { title: 'Moneda',        visible: true,  type: 'currency' },
            files:            { title: 'Archivos',      visible: true,  type: 'action'   },
            total_exportation:{ title: 'T.Exportación', visible: false, type: 'price'    },
            total_unaffected: { title: 'T.Inafecto',    visible: false, type: 'price'    },
            total_exonerated: { title: 'T.Exonerado',   visible: false, type: 'price'    },
            total_taxed:      { title: 'T.Gravado',     visible: false, type: 'price'    },
            total_igv:        { title: 'T.IGV',         visible: false, type: 'price'    },
            total:            { title: 'Total',         visible: true,  type: 'price'    },
            actions:          { title: 'Acciones',      visible: true,  type: 'action'   },
        },
    },
    items_index: {
        label: 'Productos',
        columns: {
            id:                          { title: 'ID',                       visible: true,  type: 'id'       },
            internal_id:                 { title: 'Cód. Interno',             visible: true,  type: 'code'     },
            unit_type:                   { title: 'Unidad',                   visible: true,  type: 'unit'     },
            image:                       { title: 'Imagen',                   visible: true,  type: 'image'    },
            name:                        { title: 'Nombre',                   visible: true,  type: 'text'     },
            description:                 { title: 'Descripción',              visible: false, type: 'longtext' },
            model:                       { title: 'Modelo',                   visible: false, type: 'text'     },
            brand:                       { title: 'Marca',                    visible: false, type: 'text'     },
            item_code:                   { title: 'Cód. SUNAT',               visible: false, type: 'sunat'    },
            history:                     { title: 'Historial',                visible: true,  type: 'action'   },
            stock:                       { title: 'Stock',                    visible: true,  type: 'number'   },
            sale_unit_price:             { title: 'P. Unitario (Venta)',      visible: true,  type: 'price'    },
            purchase_unit_price:         { title: 'P. Unitario (Compra)',     visible: false, type: 'price'    },
            real_unit_price:             { title: 'P. Venta total (con IGV)', visible: false, type: 'price'    },
            has_igv:                     { title: 'Tiene IGV (Venta)',        visible: true,  type: 'boolean'  },
            purchase_has_igv_description:{ title: 'Tiene IGV (Compra)',       visible: false, type: 'boolean'  },
            actions:                     { title: 'Acciones',                 visible: true,  type: 'action'   },
        },
    },
};

export default {
    computed: {
        editingModuleLabel() {
            const mod = MODULES[this.editingModuleKey];
            return mod ? mod.label : '';
        },
        visibleColumnsList() {
            return Object.entries(this.editingColumns)
                .filter(([, col]) => col.visible)
                .map(([key, col]) => ({ key, title: col.title }));
        },
        sampleRows() {
            const mod = MODULES[this.editingModuleKey];
            if (!mod) return [];
            return [0, 1, 2].map(i => {
                const row = {};
                Object.entries(mod.columns).forEach(([key, col]) => {
                    row[key] = (SAMPLE_BY_TYPE[col.type] ?? ['—', '—', '—'])[i];
                });
                return row;
            });
        },
    },
    data() {
        return {
            dialogVisible: false,
            saving: false,
            editingModuleKey: null,
            editingColumns: {},
            savedConfigs: {},
            openSections: ['purchases'],
            sections: [
                {
                    key: 'purchases',
                    label: 'Compras',
                    color: '#f59e0b',
                    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-bag"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304" /><path d="M9 11v-5a3 3 0 0 1 6 0v5" /></svg>',
                    modules: ['purchases_index'],
                },
                {
                    key: 'sales',
                    label: 'Ventas',
                    color: '#10b981',
                    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-receipt-2"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" /><path d="M14 8h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5m2 0v1.5m0 -9v1.5" /></svg>',
                    modules: ['document_index', 'sale_notes_index'],
                },
                {
                    key: 'presale',
                    label: 'Preventa',
                    color: '#8b5cf6',
                    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415" /><path d="M16 5l3 3" /></svg>',
                    modules: ['sale_opportunities_index', 'quotations_index', 'contracts_index', 'order_notes_index'],
                },
                {
                    key: 'products',
                    label: 'Productos / Servicios',
                    color: '#ef4444',
                    svg: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-category"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M4 4h6v6h-6l0 -6" /><path d="M14 4h6v6h-6l0 -6" /><path d="M4 14h6v6h-6l0 -6" /><path d="M14 17a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /></svg>',
                    modules: ['items_index'],
                },
            ],
        };
    },
    created() {
        this.loadConfigs();
    },
    methods: {
        moduleLabel(key) {
            return MODULES[key] ? MODULES[key].label : key;
        },
        async loadConfigs() {
            try {
                const res = await this.$http.get('/configurations/column-visibility');
                if (res.data.success) {
                    this.savedConfigs = res.data.data;
                }
            } catch (_) {}
        },
        openDialog(moduleKey) {
            const mod = MODULES[moduleKey];
            if (!mod) return;

            this.editingModuleKey = moduleKey;

            const cols = JSON.parse(JSON.stringify(mod.columns));
            const saved = this.savedConfigs[moduleKey];
            if (saved && saved.columns) {
                Object.keys(saved.columns).forEach(key => {
                    if (cols[key] !== undefined) {
                        cols[key].visible = saved.columns[key].visible;
                    }
                });
            }

            this.editingColumns = cols;
            this.dialogVisible = true;
        },
        async saveModule() {

            try {
                await this.$confirm(
                    'Esta acción reemplazará la configuración de columnas de <strong>todos los usuarios</strong> que tienen su propia configuración guardada para este listado. ¿Deseas continuar?',
                    'Advertencia',
                    {
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar',
                        type: 'warning',
                        dangerouslyUseHTMLString: true,
                    }
                );
            } catch (_) {
                return;
            }

            this.saving = true;
            const columns = {};
            Object.keys(this.editingColumns).forEach(key => {
                columns[key] = { title: this.editingColumns[key].title, visible: this.editingColumns[key].visible };
            });

            try {
                const res = await this.$http.post(
                    `/configurations/column-visibility/${this.editingModuleKey}`,
                    { columns }
                );
                if (res.data.success) {
                    this.$message.success(res.data.message);
                    await this.loadConfigs();
                    this.dialogVisible = false;
                } else {
                    this.$message.error(res.data.message);
                }
            } catch (_) {
                this.$message.error('Error al guardar la configuración');
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>

<style scoped>
/* el-collapse overrides */
.vc-collapse {
    border: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.vc-collapse >>> .el-collapse-item {
    border: 1px solid var(--accent-color);
    border-radius: 10px;
    overflow: hidden;
    margin: 0;
}

.vc-collapse >>> .el-collapse-item__header {
    height: 56px;
    padding: 0 16px;
    border-bottom: none;
    border-radius: 10px;
    font-size: 0.9rem;
}

.vc-collapse >>> .el-collapse-item__header.is-active {
    border-radius: 10px 10px 0 0;
    border-bottom: none;
    background-color: var(--accent-color);
}

.vc-collapse >>> .el-collapse-item__arrow {
    margin-left: 8px;
}

.vc-collapse >>> .el-collapse-item__wrap {
    border-bottom: none;
    border-radius: 0 0 10px 10px;
}

.vc-collapse >>> .el-collapse-item__content {
    padding: 0;
}

/* Section header layout */
.vc-section__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex: 1;
    padding-right: 4px;
}

.vc-section__icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.vc-section__title {
    font-weight: 600;
}

.vc-section__count {
    font-size: 0.78rem;
}

/* Module items */
.vc-module-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 10px 10px 15px;
    border: 1px solid var(--accent-color);
    margin: 10px;
    border-radius: 6px;
    background-color: var(--light-color);
}

.vc-module-item:hover {
    background-color: #fff;
}
.vc-module-item__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
    opacity: 0.3;
}

.vc-module-item:hover .vc-module-item__dot {
    opacity: 1;
}
.vc-module-item__label {
    font-size: 0.875rem;
}


.columns-grid {
    display: grid;
    grid-template-rows: repeat(6, auto);
    grid-auto-flow: column;
    grid-auto-columns: 1fr;
    gap: 8px 16px;
}

.preview-wrap {
    margin-top: 20px;
    border-top: 1px solid var(--accent-color);
    padding-top: 12px;
}

.preview-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}

.preview-scroll {
    overflow-x: auto;
    border: 1px solid var(--accent-color);
    border-radius: 4px;
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
    white-space: nowrap;
}

.preview-table thead th {
    background: var(--light-color);
    padding: 5px 10px;
    border-bottom: 1px solid var(--accent-color);
    color: var(--dark-color);
    font-weight: 600;
    text-align: left;
}

.preview-table tbody td {
    padding: 4px 10px;
    border-bottom: 1px solid var(--accent-color);
    color: var(--dark-color);
}

.preview-table tbody tr:last-child td {
    border-bottom: none;
}

.preview-empty {
    text-align: center;
    color: var(--muted);
    font-size: 0.8rem;
    padding: 12px;
    margin: 0;
}
</style>

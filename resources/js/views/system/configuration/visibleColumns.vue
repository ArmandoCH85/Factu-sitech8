<template>
    <div class="card">
        <div class="card-header bg-info bg-info-customer-admin">
            <h3 class="my-0">Visibilidad de columnas por defecto</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Configure qué columnas se mostrarán por defecto en cada listado para usuarios sin configuración previa.
            </p>

            <div class="section-group mb-3">
                <p class="fw-bold mb-1 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">Compras</p>
                <div class="d-flex align-items-center justify-content-between border rounded p-2">
                    <span>Listado de compras</span>
                    <el-button size="small" type="primary" @click="openDialog('purchases_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
            </div>

            <div class="section-group mb-3">
                <p class="fw-bold mb-1 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">Ventas</p>
                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1">
                    <span>Boleta / Factura</span>
                    <el-button size="small" type="primary" @click="openDialog('document_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
                <div class="d-flex align-items-center justify-content-between border rounded p-2">
                    <span>Notas de venta</span>
                    <el-button size="small" type="primary" @click="openDialog('sale_notes_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
            </div>

            <div class="section-group mb-3">
                <p class="fw-bold mb-1 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">Preventa</p>
                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1">
                    <span>Oportunidad de venta</span>
                    <el-button size="small" type="primary" @click="openDialog('sale_opportunities_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1">
                    <span>Cotizaciones</span>
                    <el-button size="small" type="primary" @click="openDialog('quotations_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-1">
                    <span>Contratos</span>
                    <el-button size="small" type="primary" @click="openDialog('contracts_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
                <div class="d-flex align-items-center justify-content-between border rounded p-2">
                    <span>Pedidos</span>
                    <el-button size="small" type="primary" @click="openDialog('order_notes_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
            </div>

            <div class="section-group mb-3">
                <p class="fw-bold mb-1 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">Productos / Servicios</p>
                <div class="d-flex align-items-center justify-content-between border rounded p-2">
                    <span>Productos</span>
                    <el-button size="small" type="primary" @click="openDialog('items_index')">
                        <i class="fa fa-columns"></i> Editar columnas
                    </el-button>
                </div>
            </div>

            <el-dialog
                :title="'Columnas por defecto — ' + (editingModuleLabel)"
                :visible.sync="dialogVisible"
                width="560px"
                :close-on-click-modal="false"
            >
                <p class="text-muted" style="font-size:0.85rem;">
                    Las columnas marcadas se mostrarán por defecto a usuarios que aún no hayan personalizado su vista.
                </p>
                <div class="columns-grid">
                    <div
                        v-for="(col, key) in editingColumns"
                        :key="key"
                    >
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
        };
    },
    created() {
        this.loadConfigs();
    },
    methods: {
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
.columns-grid {
    display: grid;
    grid-template-rows: repeat(6, auto);
    grid-auto-flow: column;
    grid-auto-columns: 1fr;
    gap: 8px 16px;
}

.preview-wrap {
    margin-top: 20px;
    border-top: 1px solid #ebeef5;
    padding-top: 12px;
}

.preview-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #909399;
    margin-bottom: 8px;
}

.preview-scroll {
    overflow-x: auto;
    border: 1px solid #ebeef5;
    border-radius: 4px;
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
    white-space: nowrap;
}

.preview-table thead th {
    background: #f5f7fa;
    padding: 5px 10px;
    border-bottom: 1px solid #ebeef5;
    color: #606266;
    font-weight: 600;
    text-align: left;
}

.preview-table tbody td {
    padding: 4px 10px;
    border-bottom: 1px solid #f5f7fa;
    color: #c0c4cc;
}

.preview-table tbody tr:last-child td {
    border-bottom: none;
}

.preview-empty {
    text-align: center;
    color: #c0c4cc;
    font-size: 0.8rem;
    padding: 12px;
    margin: 0;
}
</style>

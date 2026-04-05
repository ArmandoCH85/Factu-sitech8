<template>
    <el-table
        :data="records"
        v-loading="loading"
        style="width: 100%"
        size="small"
        class="cb-table mt-3"
    >
        <!-- Código de reclamo -->
        <el-table-column label="Código" width="160" prop="code">
            <template slot-scope="{ row }">
                <span class="cb-code">{{ row.code }}</span>
            </template>
        </el-table-column>

        <!-- Datos del reclamante -->
        <el-table-column label="Cliente" min-width="180">
            <template slot-scope="{ row }">
                <div class="cb-customer-name">{{ row.name }}</div>
                <div class="cb-customer-doc">
                    {{ row.identity_document_type_label }}
                    </br> {{ row.identity_document_number }}
                </div>
            </template>
        </el-table-column>

        <!-- Tipo: queja o reclamo -->
        <el-table-column label="Tipo" width="90" align="center">
            <template slot-scope="{ row }">
                <el-tag
                    :type="row.claim_type === 'reclamo' ? 'danger' : 'warning'"
                    size="mini"
                    style="text-transform:capitalize"
                >
                    {{ row.claim_type }}
                </el-tag>
            </template>
        </el-table-column>

        <!-- Fecha de registro -->
        <el-table-column label="Fecha" width="100">
            <template slot-scope="{ row }">
                {{ formatDate(row.created_at) }}
            </template>
        </el-table-column>

        <!-- Monto del comprobante vinculado -->
        <el-table-column label="Monto" width="110" align="right">
            <template slot-scope="{ row }">
                <span v-if="row.has_receipt">
                    {{ row.receipt_currency }} {{ row.receipt_amount }}
                </span>
                <span v-else class="cb-none">—</span>
            </template>
        </el-table-column>

        <!-- Serie-Número del comprobante -->
        <el-table-column label="Comprobante" width="130">
            <template slot-scope="{ row }">
                <span v-if="row.receipt_series">
                    {{ row.receipt_series }}-{{ row.receipt_number }}
                </span>
                <span v-else class="cb-none">—</span>
            </template>
        </el-table-column>

        <!-- Selector inline de estado con dot de color -->
        <el-table-column label="Estado" width="200">
            <template slot-scope="{ row }">
                <el-select
                    :value="row.status_claim_id"
                    size="mini"
                    style="width:100%"
                    @change="newVal => $emit('status-change', row, newVal)"
                >
                    <el-option
                        v-for="s in statusClaims"
                        :key="s.id"
                        :label="s.description"
                        :value="s.id"
                    >
                        <span :style="{ color: s.color || '#909399' }">● </span>
                        {{ s.description }}
                    </el-option>
                </el-select>
            </template>
        </el-table-column>

        <!-- Botón de detalle -->
        <el-table-column label="Opciones" width="90" align="center">
            <template slot-scope="{ row }">
                <el-button
                    size="mini"
                    type="primary"
                    icon="el-icon-view"
                    @click="$emit('view', row)"
                    title="Ver detalle"
                ></el-button>
            </template>
        </el-table-column>
    </el-table>
</template>

<script>
export default {
    name: 'ClaimsDataTable',

    props: {
        // Registros de reclamos a mostrar en la tabla
        records: {
            type: Array,
            default: () => []
        },
        // Listado de estados disponibles para el selector inline
        statusClaims: {
            type: Array,
            default: () => []
        },
        // Estado de carga para mostrar spinner sobre la tabla
        loading: {
            type: Boolean,
            default: false
        }
    },

    methods: {
        // Formatea una fecha ISO a DD/MM/YYYY usando moment si está disponible
        formatDate(dateStr) {
            if (!dateStr) return ''
            return moment ? moment(dateStr).format('DD/MM/YYYY') : dateStr.slice(0, 10)
        }
    }
}
</script>

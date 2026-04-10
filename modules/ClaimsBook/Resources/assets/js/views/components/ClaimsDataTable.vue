<template>
    <div class="table-responsive mt-3">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-start">Código</th>
                    <th class="text-start">Cliente</th>
                    <th class="text-start">Tipo</th>
                    <th class="text-start">Fecha</th>
                    <th class="text-start">Comprobante</th>
                    <th class="text-start">Monto</th>
                    <th class="text-start">Canal</th>
                    <th class="text-start">Estado</th>
                    <th class="text-start">Responsable</th>
                    <th class="text-end">Opciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading">
                    <td colspan="10" class="text-center py-4">
                        <i class="el-icon-loading"></i> Cargando...
                    </td>
                </tr>
                <tr v-else-if="records.length === 0">
                    <td colspan="10" class="text-center py-4 text-muted">
                        No se encontraron registros
                    </td>
                </tr>
                <tr v-for="row in records" :key="row.id" v-else>
                    <td>
                        <div class="cb-code">{{ row.public_code }}</div>
                        <small class="text-muted">{{ row.code }}</small>
                    </td>
                    <td>
                        <div class="cb-customer-name">{{ row.name }}</div>
                        <small class="text-muted">
                            {{ row.identity_document_type_label }}
                            {{ row.identity_document_number }}
                        </small>
                    </td>
                    <td class="text-start">
                        <span
                            class="badge"
                            :class="row.claim_type === 'reclamo' ? 'bg-danger' : 'bg-warning text-dark'"
                            style="text-transform: capitalize"
                        >
                            {{ row.claim_type }}
                        </span>
                    </td>
                    <td class="text-start">
                        {{ formatDate(row.created_at) }}<br>
                        <span class="text-danger" v-if="row.status_claim.is_initial && row.remaining_business_days">{{ row.remaining_business_days }} restantes por atender</span>
                    </td>
                    <td class="text-start">
                        <span v-if="row.receipt_series">
                            {{ row.receipt_series }}-{{ row.receipt_number }}
                        </span>
                        <span v-else class="text-muted">—</span>
                    </td>
                    <td class="text-start">
                        <span v-if="row.has_receipt">
                            {{ row.receipt_currency }} {{ row.receipt_amount }}
                        </span>
                        <span v-else class="text-muted">—</span>
                    </td>
                    <td class="text-start">
                        <span v-if="row.channel">{{ row.channel }}</span>
                        <span v-else class="text-muted">—</span>
                    </td>
                    <td class="text-start">
                        <el-select
                            :value="row.status_claim_id"
                            size="mini"
                            style="width: 100%; min-width: 150px"
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
                    </td>
                    <td class="text-start">
                        <el-select
                            v-model="row.assigned_user_id"
                            size="mini"
                            style="width: 100%; min-width: 150px"
                            @change="newVal => onAssign(row, newVal)"
                        >
                            <el-option
                                v-for="user in users"
                                :key="user.id"
                                :label="user.name"
                                :value="user.id"
                            ></el-option>
                        </el-select>
                    </td>
                    <td class="text-end">
                        <button
                            type="button"
                            class="btn waves-effect waves-light btn-xs btn-info"
                            @click="$emit('view', row)"
                            title="Ver detalle"
                        >
                            <i class="el-icon-view"></i> Ver
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="cb-pagination">
            <el-pagination
                    @current-change="onPageChange"
                    layout="total, prev, pager, next"
                    :total="pagination.total"
                    :current-page="pagination.current_page"
                    :page-size="pagination.per_page">
            </el-pagination>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ClaimsDataTable',

    data() {
        return {
            users: []
        }
    },

    props: {
        records: {
            type: Array,
            default: () => []
        },
        statusClaims: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        },
        pagination: {
            type: Object,
            default: () => ({ total: 0, per_page: 20, current_page: 1 })
        }
    },

    methods: {
        onPageChange(page) {
            this.$emit('page-change', page)
        },
        formatDate(dateStr) {
            if (!dateStr) return ''
            return moment ? moment(dateStr).format('DD/MM/YYYY') : dateStr.slice(0, 10)
        },
        onAssign(row, userId) {
            // Emitir al padre para que realice la actualización persistente
            this.$emit('assign-change', row, userId)
        }
    },

    mounted() {
        // Obtener usuarios para el selector (misma ruta usada en el modal)
        this.$http.get('/users/records').then(response => {
            this.users = response.data.data || []
        }).catch(() => { this.users = [] })
    }
}
</script>

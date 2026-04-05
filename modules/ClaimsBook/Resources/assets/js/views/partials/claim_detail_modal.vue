<template>
    <el-dialog
        :title="'Detalle de Reclamación — ' + (record ? record.code : '')"
        :visible.sync="showDialog"
        width="720px"
        @close="close"
    >
        <div v-if="record" v-loading="loading">
            <!-- Sección 1: Identificación del reclamante -->
            <div class="cd-section">
                <div class="cd-section-title">
                    <i class="el-icon-user"></i> Datos del reclamante
                </div>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Tipo de documento</label>
                            <span>{{ record.identity_document_type || '—' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>N° de documento</label>
                            <span>{{ record.identity_document_number || '—' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Nombre completo</label>
                            <span>{{ record.name }}</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Email</label>
                            <span>{{ record.email || '—' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Teléfono</label>
                            <span>{{ record.phone || '—' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Distrito</label>
                            <span>{{ record.district_name || '—' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="24">
                        <div class="cd-field">
                            <label>Dirección</label>
                            <span>{{ record.address || '—' }}</span>
                        </div>
                    </el-col>
                </el-row>
            </div>

            <!-- Sección 2: Bien contratado -->
            <div class="cd-section">
                <div class="cd-section-title">
                    <i class="el-icon-goods"></i> Bien contratado
                </div>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Tipo de bien</label>
                            <span>{{ record.asset_type === 'producto' ? 'Producto' : 'Servicio' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Fecha de contratación</label>
                            <span>{{ record.asset_date || '—' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="24">
                        <div class="cd-field">
                            <label>Descripción del bien</label>
                            <span>{{ record.asset_description }}</span>
                        </div>
                    </el-col>
                    <template v-if="record.has_receipt">
                        <el-col :span="8">
                            <div class="cd-field">
                                <label>Monto reclamado</label>
                                <span>{{ record.receipt_currency }} {{ record.receipt_amount }}</span>
                            </div>
                        </el-col>
                        <el-col :span="8">
                            <div class="cd-field">
                                <label>N° de comprobante</label>
                                <span>{{ record.receipt_series }}-{{ record.receipt_number }}</span>
                            </div>
                        </el-col>
                    </template>
                </el-row>
            </div>

            <!-- Sección 3: Detalle del reclamo -->
            <div class="cd-section">
                <div class="cd-section-title">
                    <i class="el-icon-document"></i> Detalle del reclamo
                </div>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Tipo</label>
                            <el-tag :type="record.claim_type === 'reclamo' ? 'danger' : 'warning'" size="small">
                                {{ record.claim_type === 'reclamo' ? 'Reclamo' : 'Queja' }}
                            </el-tag>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Canal de atención</label>
                            <span>{{ record.channel || '—' }}</span>
                        </div>
                    </el-col>
                    <el-col :span="24">
                        <div class="cd-field">
                            <label>Detalle</label>
                            <div class="cd-text-block">{{ record.detail }}</div>
                        </div>
                    </el-col>
                    <el-col :span="24">
                        <div class="cd-field">
                            <label>Pedido/resultado esperado</label>
                            <div class="cd-text-block">{{ record.expected_result || '—' }}</div>
                        </div>
                    </el-col>
                    <el-col v-if="record.attachment_url" :span="24">
                        <div class="cd-field">
                            <label>Adjunto</label>
                            <a :href="record.attachment_url" target="_blank" class="cd-attachment-link">
                                <i class="el-icon-paperclip"></i> Ver adjunto
                            </a>
                        </div>
                    </el-col>
                </el-row>
            </div>

            <!-- Sección 4: Estado actual y resolución -->
            <div class="cd-section">
                <div class="cd-section-title">
                    <i class="el-icon-tickets"></i> Estado y resolución
                </div>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Estado actual</label>
                            <el-tag
                                v-if="record.status_claim"
                                :color="record.status_claim.color || '#909399'"
                                size="small"
                                class="cd-status-tag"
                            >
                                {{ record.status_claim.description }}
                            </el-tag>
                            <span v-else>—</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Fecha de registro</label>
                            <span>{{ record.date_formatted }}</span>
                        </div>
                    </el-col>
                    <el-col v-if="record.resolution" :span="24">
                        <div class="cd-field">
                            <label>Resolución</label>
                            <div class="cd-text-block">{{ record.resolution }}</div>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </div>

        <div v-else-if="loading" class="cd-loading-placeholder">
            Cargando detalle...
        </div>

        <div slot="footer">
            <el-button size="small" @click="close">Cerrar</el-button>
        </div>
    </el-dialog>
</template>

<style scoped>
.cd-section {
    border: 1px solid #ebeef5;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 14px;
}
.cd-section-title {
    font-size: 13px;
    font-weight: 600;
    color: #303133;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #ebeef5;
}
.cd-field {
    margin-bottom: 10px;
}
.cd-field > label {
    display: block;
    font-size: 11px;
    color: #909399;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.cd-field > span {
    font-size: 13px;
    color: #303133;
}
.cd-text-block {
    font-size: 13px;
    color: #303133;
    white-space: pre-line;
    background: #f8f9fa;
    border-radius: 4px;
    padding: 8px 10px;
    line-height: 1.5;
}
.cd-attachment-link {
    font-size: 13px;
    color: #409EFF;
    text-decoration: none;
}
.cd-attachment-link:hover {
    text-decoration: underline;
}
.cd-status-tag {
    border: none !important;
    color: #fff !important;
}
.cd-loading-placeholder {
    text-align: center;
    padding: 40px 0;
    color: #909399;
}
</style>

<script>
export default {
    props: {
        showDialog: {
            type: Boolean,
            default: false
        },
        claimId: {
            type: Number,
            default: null
        }
    },

    data() {
        return {
            record: null,
            loading: false,
        }
    },

    watch: {
        claimId(val) {
            if (val && this.showDialog) {
                this.loadDetail(val)
            }
        },
        showDialog(val) {
            if (val && this.claimId) {
                this.loadDetail(this.claimId)
            }
            if (!val) {
                this.record = null
            }
        }
    },

    methods: {
        loadDetail(id) {
            this.loading = true
            this.$http.get(`/claims/${id}`)
                .then(response => {
                    this.record = response.data
                })
                .finally(() => { this.loading = false })
        },

        close() {
            this.record = null
            this.$emit('update:showDialog', false)
        }
    }
}
</script>

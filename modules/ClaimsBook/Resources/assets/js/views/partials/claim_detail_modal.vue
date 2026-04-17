<template>
    <el-dialog
        :title="dialogTitle"
        :visible.sync="showDialog"
        width="760px"
        @close="close"
    >
        <div v-if="record" v-loading="loading">

            <!-- Código público de consulta -->
            <div class="cd-public-code-bar" v-if="record.public_code">
                <i class="el-icon-key"></i>
                Código público: <strong>{{ record.public_code }}</strong>
                <span class="cd-public-code-hint">El reclamante puede usar este código para consultar el estado de su reclamo.</span>
            </div>

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

            <!-- Sección 3: Detalle del reclamo + adjuntos del reclamante -->
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

                    <!-- Datos de la sucursal (solo cuando el canal fue sincronizado desde un establecimiento) -->
                    <template v-if="record.channel_establishment && hasEstablishmentData(record.channel_establishment)">
                        <el-col :span="24" v-if="buildEstablishmentAddress(record.channel_establishment)">
                            <div class="cd-field">
                                <label>Dirección</label>
                                <span>{{ buildEstablishmentAddress(record.channel_establishment) }}</span>
                            </div>
                        </el-col>
                        <el-col :span="12" v-if="record.channel_establishment.telephone && record.channel_establishment.telephone !== '-'">
                            <div class="cd-field">
                                <label>Teléfono</label>
                                <span>{{ record.channel_establishment.telephone }}</span>
                            </div>
                        </el-col>
                        <el-col :span="12" v-if="record.channel_establishment.email && record.channel_establishment.email !== '-'">
                            <div class="cd-field">
                                <label>Email</label>
                                <span>{{ record.channel_establishment.email }}</span>
                            </div>
                        </el-col>
                    </template>

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

                    <!-- Archivos adjuntos del reclamante -->
                    <el-col :span="24" v-if="record.attachments && record.attachments.length">
                        <div class="cd-field">
                            <label>Archivos adjuntos del reclamante</label>
                            <div class="cd-attachments-list">
                                <a
                                    v-for="(path, idx) in record.attachments"
                                    :key="'att-' + idx"
                                    :href="fileUrl(path)"
                                    target="_blank"
                                    class="cd-attachment-item"
                                >
                                    <i class="el-icon-paperclip"></i> {{ fileName(path) }}
                                </a>
                            </div>
                        </div>
                    </el-col>

                    <!-- Constancia PDF generada -->
                    <el-col :span="24">
                        <div class="cd-field">
                            <label>Constancia PDF</label>
                            <a
                                v-if="record.pdf_url"
                                :href="record.pdf_url"
                                target="_blank"
                                class="cd-attachment-item"
                            >
                                <i class="el-icon-document"></i> Descargar constancia PDF
                            </a>
                            <el-button v-else size="mini" icon="el-icon-document" disabled>
                                PDF aún no generado
                            </el-button>
                        </div>
                    </el-col>
                </el-row>
            </div>

            <!-- Sección 4: Formulario de gestión -->
            <div class="cd-section cd-form-section">
                <div class="cd-section-title">
                    <i class="el-icon-edit"></i> Gestión del reclamo
                </div>
                <el-row :gutter="16">
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Estado</label>
                            <el-select
                                v-model="form.status_claim_id"
                                size="small"
                                placeholder="Seleccionar estado"
                                style="width:100%"
                                :disabled="saving"
                            >
                                <el-option
                                    v-for="s in statusClaims"
                                    :key="s.id"
                                    :label="s.description"
                                    :value="s.id"
                                ></el-option>
                            </el-select>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Responsable</label>
                            <el-select
                                v-model="form.assigned_user_id"
                                size="small"
                                placeholder="Asignar responsable"
                                style="width:100%"
                                :disabled="saving"
                                clearable
                            >
                                <el-option
                                    v-for="u in activeUsers"
                                    :key="u.id"
                                    :label="u.name"
                                    :value="u.id"
                                ></el-option>
                            </el-select>
                        </div>
                    </el-col>
                    <el-col :span="24">
                        <div class="cd-field">
                            <label>
                                Resolución / Notas de respuesta
                                <span v-if="selectedStatusIsFinal" class="cd-required">*</span>
                            </label>
                            <el-input
                                v-model="form.resolution"
                                type="textarea"
                                :rows="3"
                                placeholder="Resolución adoptada o notas internas..."
                                :disabled="saving"
                            ></el-input>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Fecha de registro</label>
                            <span>{{ record.date_formatted }}</span>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="cd-field">
                            <label>Fecha límite (15 días hábiles)</label>
                            <span>{{ record.due_date_formatted || '—' }}</span>
                        </div>
                    </el-col>
                </el-row>
            </div>

            <!-- Sección 5: Archivos de respuesta -->
            <div class="cd-section">
                <div class="cd-section-title">
                    <i class="el-icon-paperclip"></i> Archivos de respuesta
                </div>

                <!-- Archivos de respuesta ya guardados -->
                <div v-if="record.response_attachments && record.response_attachments.length" class="cd-attachments-list mb-2">
                    <a
                        v-for="(path, idx) in record.response_attachments"
                        :key="'resp-' + idx"
                        :href="fileUrl(path)"
                        target="_blank"
                        class="cd-attachment-item"
                    >
                        <i class="el-icon-paperclip"></i> {{ fileName(path) }}
                    </a>
                </div>
                <p v-else class="cd-none-label">Sin archivos de respuesta adjuntos.</p>

                <!-- Uploader para agregar nuevos archivos -->
                <el-upload
                    ref="uploadRef"
                    action="#"
                    :auto-upload="false"
                    :on-change="handleFileChange"
                    :on-remove="handleFileRemove"
                    :limit="5"
                    :on-exceed="() => $message.warning('Máximo 5 archivos permitidos')"
                    multiple
                    accept=".pdf,.png,.jpg,.jpeg,.mp4"
                    :disabled="saving || maxResponseAttachmentsReached"
                >
                    <el-button size="small" icon="el-icon-upload2" :disabled="saving || maxResponseAttachmentsReached">
                        Adjuntar archivos de respuesta
                    </el-button>
                    <div slot="tip" class="el-upload__tip">
                        PDF, PNG, JPG, MP4 · máx. 32 MB por archivo · máx. 5 archivos
                    </div>
                </el-upload>
            </div>
        </div>

        <div v-else-if="loading" class="cd-loading-placeholder">
            Cargando detalle...
        </div>

        <div slot="footer" class="d-flex justify-content-end">
            <el-button size="small" @click="close" :disabled="saving">Cerrar</el-button>
            <el-button
                v-if="record"
                size="small"
                type="primary"
                class="ms-2"
                :loading="saving"
                @click="saveUpdate"
            >
                Guardar cambios
            </el-button>
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
.cd-form-section {
    background: #fafcff;
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
    margin-bottom: 4px;
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
.cd-attachment-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #409EFF;
    text-decoration: none;
    margin-right: 12px;
    margin-bottom: 4px;
}
.cd-attachment-item:hover {
    text-decoration: underline;
}
.cd-attachments-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
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
.cd-public-code-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f0f9eb;
    border: 1px solid #e1f3d8;
    border-radius: 6px;
    padding: 8px 14px;
    margin-bottom: 14px;
    font-size: 13px;
    color: #67c23a;
}
.cd-public-code-hint {
    font-size: 11px;
    color: #909399;
    margin-left: 8px;
}
.cd-required {
    color: #f56c6c;
    margin-left: 2px;
}
.cd-none-label {
    font-size: 12px;
    color: #c0c4cc;
    margin-bottom: 8px;
}
.mb-2 {
    margin-bottom: 8px;
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
        },
        // Lista de estados disponibles (pasada desde el componente padre)
        statusClaims: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            record: null,
            loading: false,
            saving: false,
            users: [],

            // Formulario de gestión editable
            form: {
                status_claim_id:  null,
                assigned_user_id: null,
                resolution:       '',
            },
        }
    },

    computed: {
        dialogTitle() {
            if (!this.record) return 'Detalle de Reclamación'
            return `Detalle — ${this.record.code}`
        },
        // Indica si el estado seleccionado en el formulario es un estado final
        selectedStatusIsFinal() {
            if (!this.form.status_claim_id || !this.statusClaims) return false
            const status = this.statusClaims.find(s => s.id === this.form.status_claim_id)
            return status ? !!status.is_final : false
        },
        // Indica si ya hay 5 archivos de respuesta guardados (deshabilita uploader)
        maxResponseAttachmentsReached() {
            return this.record && this.record.response_attachments && this.record.response_attachments.length >= 5
        },
        activeUsers() {
            return this.users.filter(u => u.active)
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
                this.loadUsers()
            }
            if (!val) {
                this.record = null
                this.resetForm()
            }
        }
    },

    methods: {
        loadDetail(id) {
            this.loading = true
            this.$http.get(`/claims/${id}`)
                .then(response => {
                    this.record = response.data
                    // Pre-cargar el formulario con los valores actuales del reclamo
                    this.form.status_claim_id  = response.data.status_claim_id  || null
                    this.form.assigned_user_id = response.data.assigned_user_id || null
                    this.form.resolution       = response.data.resolution       || ''
                })
                .finally(() => { this.loading = false })
        },

        loadUsers() {
            if (this.users.length > 0) return
            this.$http.get('/users/records').then(response => {
                this.users = response.data.data || response.data || []
            })
        },

        // Guarda los cambios de gestión: estado, responsable, resolución y nuevos adjuntos
        saveUpdate() {
            // Validar resolución obligatoria si el estado seleccionado es final
            if (this.selectedStatusIsFinal && !this.form.resolution.trim()) {
                this.$message.error('La resolución es obligatoria para cerrar el reclamo')
                return
            }

            this.saving = true

            const fd = new FormData()

            if (this.form.status_claim_id) {
                fd.append('status_claim_id', this.form.status_claim_id)
            }
            if (this.form.resolution) {
                fd.append('resolution', this.form.resolution)
            }
            // assigned_user_id puede ser null (para quitar responsable)
            if (this.form.assigned_user_id !== null && this.form.assigned_user_id !== undefined) {
                fd.append('assigned_user_id', this.form.assigned_user_id)
            }

            // Adjuntar nuevos archivos de respuesta
            const uploadFiles = this.$refs.uploadRef ? this.$refs.uploadRef.uploadFiles : []
            uploadFiles.forEach((f, i) => {
                if (f.raw) fd.append(`response_attachments[${i}]`, f.raw)
            })

            this.$http.post(`/claims/${this.record.id}/update`, fd, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            .then(response => {
                this.$message.success('Reclamo actualizado correctamente')
                // Limpiar archivo pendientes del uploader
                this.$refs.uploadRef && this.$refs.uploadRef.clearFiles()
                // Recargar detalle para reflejar los cambios
                this.loadDetail(this.record.id)
                this.$emit('updated')
            })
            .catch(err => {
                const msg = (err.response && err.response.data && err.response.data.message)
                    ? err.response.data.message
                    : 'Error al actualizar el reclamo'
                this.$message.error(msg)
            })
            .finally(() => { this.saving = false })
        },

        // Valida el tamaño del archivo antes de agregarlo a la lista
        handleFileChange(file, fileList) {
            const maxSize = 32 * 1024 * 1024
            if (file.raw && file.raw.size > maxSize) {
                this.$message.error(`"${file.name}" supera el tamaño máximo de 32 MB`)
                // Eliminar el archivo excedido de la lista interna del uploader
                const idx = fileList.findIndex(f => f.uid === file.uid)
                if (idx !== -1) fileList.splice(idx, 1)
            }
        },

        handleFileRemove() {
            // El uploader gestiona la lista internamente; no se requiere acción extra
        },

        // Genera la URL pública de un archivo — el backend ya devuelve URLs públicas
        fileUrl(path) {
            if (!path) return ''
            // Si ya es URL absoluta o ruta iniciando con '/', usar tal cual
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) return path
            // Devolver tal cual por compatibilidad (backend debería devolver URLs)
            return path
        },

        // Extrae el nombre del archivo desde su ruta
        fileName(path) {
            if (!path) return ''
            return path.split('/').pop()
        },

        // Verifica si el establecimiento tiene al menos un dato válido para mostrar
        hasEstablishmentData(est) {
            if (!est) return false
            const addr = this.buildEstablishmentAddress(est)
            const tel  = est.telephone && est.telephone !== '-'
            const mail = est.email     && est.email     !== '-'
            return !!(addr || tel || mail)
        },

        // Construye la dirección legible del establecimiento (igual que en el PDF)
        buildEstablishmentAddress(est) {
            if (!est) return ''
            const parts = [
                (est.address && est.address !== '-') ? est.address : null,
                (est.district_id  && est.district_id  !== '-' && est.district)  ? est.district.description  : null,
                (est.province_id  && est.province_id  !== '-' && est.province)  ? est.province.description  : null,
                (est.department_id && est.department_id !== '-' && est.department) ? est.department.description : null,
            ]
            return parts.filter(Boolean).join(', ')
        },

        resetForm() {
            this.form.status_claim_id  = null
            this.form.assigned_user_id = null
            this.form.resolution       = ''
            this.$nextTick(() => {
                this.$refs.uploadRef && this.$refs.uploadRef.clearFiles()
            })
        },

        close() {
            this.record = null
            this.resetForm()
            this.$emit('update:showDialog', false)
        }
    }
}
</script>

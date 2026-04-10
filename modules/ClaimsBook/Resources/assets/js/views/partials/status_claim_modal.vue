<template>
    <el-dialog
        title="Estados del libro de reclamaciones"
        :visible.sync="showDialog"
        width="680px"
        @open="getRecords"
        @close="close"
    >
        <!-- Subtítulo informativo -->
        <p class="sc-subtitle">
            El orden define el flujo mostrado al reclamante
        </p>

        <!-- Lista de estados via collapse -->
        <el-collapse v-model="activePanel" accordion class="sc-collapse">
            <el-collapse-item
                v-for="(status, index) in statuses"
                :key="status.id"
                :name="String(status.id)"
            >
                <!-- Cabecera del ítem colapsado -->
                <template slot="title">
                    <div class="sc-item-header">
                        <!-- Dot de color -->
                        <span
                            class="sc-color-dot"
                            :style="{ background: status.color || '#909399' }"
                        ></span>
                        <!-- Nombre del estado -->
                        <span class="sc-item-name">{{ status.description }}</span>
                        <!-- Chips de acciones y flags activos -->
                        <span class="sc-chips">
                            <el-tag
                                v-if="status.action_send_email"
                                size="mini"
                                :color="'#8B5CF6'"
                                class="sc-chip"
                            >Notificar cliente</el-tag>
                            <el-tag v-if="status.is_initial" size="mini" type="info" class="sc-chip sc-chip--gray">
                                Inicial
                            </el-tag>
                            <el-tag v-if="status.is_final" size="mini" type="danger" class="sc-chip sc-chip--gray">
                                Final
                            </el-tag>
                        </span>
                        <!-- Botones de reorden -->
                        <span class="sc-order-btns" @click.stop>
                            <el-button
                                size="mini"
                                icon="el-icon-top"
                                :disabled="index === 0"
                                @click.stop="moveUp(index)"
                            ></el-button>
                            <el-button
                                size="mini"
                                icon="el-icon-bottom"
                                :disabled="index === statuses.length - 1"
                                @click.stop="moveDown(index)"
                            ></el-button>
                        </span>
                    </div>
                </template>

                <!-- Contenido expandido: formulario de edición -->
                <div class="sc-form">
                    <!-- Nombre del estado -->
                    <div class="sc-field">
                        <label>Nombre del estado</label>
                        <el-input v-model="status.description" size="small"></el-input>
                    </div>

                    <div class="row">
                        <!-- Paleta de colores -->
                        <div class="sc-field col-6">
                            <label>Color del estado</label>
                            <div class="sc-color-palette">
                                <span
                                    v-for="c in colorPalette"
                                    :key="c"
                                    class="sc-palette-dot"
                                    :class="{ 'sc-palette-dot--active': status.color === c }"
                                    :style="{ background: c }"
                                    @click="status.color = c"
                                ></span>
                            </div>
                        </div>
                        <!-- select de usuario asignado por defecto -->
                        <div class="sc-field col-6" v-if="status.is_initial">
                            <label>Responsable por defecto</label>
                            <el-select v-model="status.assigned_user_id" placeholder="Seleccionar usuario" size="small">
                                <el-option
                                    v-for="user in users"
                                    :key="user.id"
                                    :label="user.name"
                                    :value="user.id"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>

                    <!-- Acciones automáticas -->
                    <div class="sc-field">
                        <label class="sc-actions-label">ACCIONES AUTOMÁTICAS DEL SISTEMA</label>
                        <div class="sc-actions-grid">
                            <div class="sc-action-item">
                                <el-checkbox v-model="status.action_send_email">
                                    <span class="sc-action-name">Notificar al cliente</span>
                                </el-checkbox>
                                <span class="sc-action-desc">Envía email al reclamante al entrar en este estado</span>
                            </div>
                        </div>
                    </div>

                    <!-- Flags especiales: is_initial / is_final (mutuamente excluyentes) -->
                    <div class="sc-field sc-field--flags">
                        <el-checkbox
                            v-model="status.is_initial"
                            @change="onSetInitial(status)"
                        >
                            Usar como estado inicial (apertura)
                        </el-checkbox>
                        <el-checkbox
                            v-model="status.is_final"
                            @change="onSetFinal(status)"
                        >
                            Usar como estado final (cierre)
                        </el-checkbox>
                    </div>

                    <!-- Acciones del panel -->
                    <div class="sc-panel-actions">
                        <el-button
                            type="danger"
                            size="mini"
                            plain
                            @click="destroy(status)"
                        >
                            <i class="el-icon-delete"></i> Eliminar
                        </el-button>
                        <el-button
                            type="primary"
                            size="small"
                            :loading="saving === status.id"
                            @click="update(status)"
                        >
                            Listo
                        </el-button>
                    </div>
                </div>
            </el-collapse-item>
        </el-collapse>

        <!-- Pie del dialog: agregar nuevo estado -->
        <div slot="footer" class="sc-footer">
            <el-input
                v-model="newDescription"
                placeholder="Nombre del nuevo estado..."
                size="small"
                style="width: 300px"
                @keyup.enter.native="store"
            ></el-input>
            <el-button
                type="primary"
                size="small"
                :loading="storing"
                @click="store"
            >
                <i class="el-icon-plus"></i> Nuevo estado
            </el-button>
        </div>
    </el-dialog>
</template>

<style scoped>
.sc-subtitle {
    font-size: 12px;
    color: #909399;
    margin: -10px 0 12px;
}
.sc-collapse {
    border-top: 1px solid #ebeef5;
}
.sc-collapse >>> .el-collapse-item__header {
    height: auto;
    min-height: 48px;
    padding: 6px 12px;
    line-height: 1.4;
}
.sc-item-header {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
    padding-right: 8px;
}
.sc-color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}
.sc-item-name {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sc-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    flex: 1;
    min-width: 0;
}
.sc-chip {
    border: none !important;
    color: #fff !important;
    font-size: 11px;
}
.sc-chip--gray {
    color: #909399 !important;
}
.sc-order-btns {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
    margin-left: auto;
}
.sc-form {
    padding: 4px 0 0;
}
.sc-field {
    margin-bottom: 16px;
}
.sc-field > label {
    display: block;
    font-size: 12px;
    color: #606266;
    margin-bottom: 6px;
}
.sc-field--flags {
    display: flex;
    gap: 24px;
}
.sc-color-palette {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.sc-palette-dot {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid transparent;
    transition: transform .15s, border-color .15s;
}
.sc-palette-dot:hover {
    transform: scale(1.15);
}
.sc-palette-dot--active {
    border-color: #303133;
    transform: scale(1.15);
}
.sc-actions-label {
    font-size: 11px !important;
    color: #909399 !important;
    letter-spacing: .5px;
    text-transform: uppercase;
}
.sc-actions-grid {
    margin-top: 4px;
}
.sc-action-item {
    border: 1px solid #ebeef5;
    border-radius: 6px;
    padding: 8px 10px;
    background: #fafafa;
}
.sc-action-name {
    font-weight: 500;
    font-size: 13px;
}
.sc-action-desc {
    display: block;
    font-size: 11px;
    color: #909399;
    margin-top: 2px;
    padding-left: 22px;
}
.sc-panel-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #ebeef5;
    margin-top: 4px;
}
.sc-footer {
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: flex-end;
}
</style>

<script>
export default {
    props: {
        showDialog: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            statuses: [],
            activePanel: null,
            newDescription: '',
            storing: false,
            saving: null,
            users: [],

            // Paleta de colores predefinidos
            colorPalette: [
                '#909399',
                '#409EFF',
                '#67C23A',
                '#F56C6C',
                '#E6A23C',
                '#8B5CF6',
                '#10B981',
                '#EF4444',
                '#EC4899',
            ],
        }
    },

    methods: {
        getRecords() {
            this.$http.get('/statusClaim/records').then(response => {
                this.statuses = response.data.map(s => this.normalize(s))
                this.activePanel = null
            })
            this.$http.get('/users/records').then(response => {
                this.users = response.data.data
            })
        },

        // Normaliza valores 0/1 del backend a boolean
        normalize(status) {
            const booleans = ['is_initial', 'is_final', 'action_send_email']
            const s = { ...status }
            booleans.forEach(k => { s[k] = Boolean(s[k]) })
            return s
        },

        store() {
            if (!this.newDescription.trim())
                return this.$message.error('Ingrese un nombre para el estado')

            this.storing = true
            this.$http.post('/statusClaim/store', { description: this.newDescription })
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.newDescription = ''
                        this.getRecords()
                    }
                })
                .finally(() => { this.storing = false })
        },

        update(status) {
            if (!status.description.trim())
                return this.$message.error('Ingrese un nombre para el estado')

            this.saving = status.id
            const payload = {
                description: status.description,
                color: status.color,
                is_initial: status.is_initial,
                is_final: status.is_final,
                action_send_email: status.action_send_email,
                assigned_user_id: status.is_initial ? status.assigned_user_id : null
            }

            this.$http.put(`/statusClaim/update/${status.id}`, payload)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.activePanel = null
                        this.getRecords()
                    }
                })
                .finally(() => { this.saving = null })
        },

        // Marca un estado como inicial y quita el flag en los demás localmente
        onSetInitial(status) {
            if (status.is_initial) {
                this.statuses.forEach(s => {
                    if (s.id !== status.id) s.is_initial = false
                })
                // Un estado no puede ser inicial y final a la vez
                status.is_final = false
            }
        },

        // Marca un estado como final y quita el flag en los demás localmente
        onSetFinal(status) {
            if (status.is_final) {
                this.statuses.forEach(s => {
                    if (s.id !== status.id) s.is_final = false
                })
                // Un estado no puede ser final e inicial a la vez
                status.is_initial = false
            }
        },

        destroy(status) {
            this.$confirm(
                `¿Desea eliminar el estado "${status.description}"?`,
                'Eliminar estado',
                { confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar', type: 'warning' }
            ).then(() => {
                this.$http.delete(`/statusClaim/destroy/${status.id}`)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.getRecords()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
            }).catch(() => {})
        },

        moveUp(index) {
            if (index === 0) return
            const list = [...this.statuses]
            ;[list[index - 1], list[index]] = [list[index], list[index - 1]]
            this.statuses = list
            this.persistOrder()
        },

        moveDown(index) {
            if (index === this.statuses.length - 1) return
            const list = [...this.statuses]
            ;[list[index], list[index + 1]] = [list[index + 1], list[index]]
            this.statuses = list
            this.persistOrder()
        },

        // Persiste el nuevo sort_order en el backend
        persistOrder() {
            const order = this.statuses.map((s, i) => ({ id: s.id, sort_order: i }))
            this.$http.post('/statusClaim/reorder', { order })
                .then(response => {
                    if (!response.data.success) {
                        this.$message.error('Error al guardar el orden')
                        this.getRecords()
                    }
                })
                .catch(() => {
                    this.$message.error('Error al guardar el orden')
                    this.getRecords()
                })
        },

        close() {
            this.activePanel = null
            this.$emit('update:showDialog', false)
            this.$eventHub.$emit('statusClaimsUpdated')
        }
    }
}
</script>

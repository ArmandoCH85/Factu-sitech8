<template>
    <el-dialog
        title="Canales de atención"
        :visible.sync="showDialog"
        width="520px"
        @open="loadEstablishments"
        @close="close"
    >
        <!-- Nota informativa -->
        <el-alert
            title="Los canales de atención se generan a partir de los establecimientos del sistema."
            type="info"
            :closable="false"
            show-icon
            class="ch-info-alert"
        ></el-alert>

        <!-- Establecimientos del sistema con indicador de sincronización -->
        <el-table
            :data="establishments"
            v-loading="loading"
            size="small"
            style="width: 100%"
            class="ch-table"
        >
            <el-table-column label="#" width="50" type="index" align="center"></el-table-column>
            <el-table-column prop="code" label="Código" width="100"></el-table-column>
            <el-table-column prop="description" label="Establecimiento" min-width="200"></el-table-column>
            <el-table-column label="Canal registrado" width="130" align="center">
                <template slot-scope="{ row }">
                    <el-tag
                        v-if="isSynced(row)"
                        type="success"
                        size="mini"
                        effect="plain"
                    >
                        <i class="el-icon-check"></i> Activo
                    </el-tag>
                    <span v-else class="ch-pending">—</span>
                </template>
            </el-table-column>
        </el-table>

        <el-divider class="ch-divider"></el-divider>

        <!-- Canales registrados: incluye los sincronizados y los extras manuales -->
        <div class="ch-channels-header">
            <span class="ch-section-title">Canales registrados</span>
        </div>
        <el-table
            :data="channels"
            size="small"
            style="width: 100%"
            class="ch-table"
        >
            <el-table-column prop="name" label="Canal"></el-table-column>
            <el-table-column width="70" align="center">
                <template slot-scope="{ row }">
                    <el-button
                        size="mini"
                        type="danger"
                        icon="el-icon-delete"
                        plain
                        @click="destroy(row)"
                    ></el-button>
                </template>
            </el-table-column>
        </el-table>

        <!-- Agregar canal extra manualmente -->
        <div class="ch-add-section">
            <el-input
                v-model="newName"
                placeholder="Nombre del canal extra..."
                size="small"
                @keyup.enter.native="store"
            ></el-input>
            <el-button
                type="primary"
                size="small"
                :loading="storing"
                @click="store"
            >
                <i class="el-icon-plus"></i> Agregar
            </el-button>
        </div>

        <div slot="footer" class="ch-footer">
            <el-button size="small" @click="close">Cerrar</el-button>
            <el-button
                size="small"
                type="primary"
                plain
                :loading="syncing"
                @click="syncChannels"
            >
                <i class="el-icon-refresh"></i> Sincronizar sucursales
            </el-button>            
        </div>
    </el-dialog>
</template>

<style scoped>
.ch-info-alert {
    margin-bottom: 14px;
}
.ch-table {
    margin-top: 4px;
}
.ch-pending {
    color: #c0c4cc;
    font-size: 13px;
}
.ch-divider {
    margin: 16px 0 10px;
}
.ch-channels-header {
    margin-bottom: 6px;
}
.ch-section-title {
    font-size: 13px;
    font-weight: 600;
    color: #606266;
}
.ch-add-section {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 12px;
}
.ch-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
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
            // Establecimientos cargados desde /establishments/records
            establishments: [],
            // Canales ya registrados en claim_channels
            channels: [],
            // Nombres en minúsculas para comparación rápida de sincronización
            syncedNames: [],
            loading: false,
            syncing: false,
            storing: false,
            newName: '',
        }
    },

    methods: {
        // Carga establecimientos y canales registrados al abrir el modal
        loadEstablishments() {
            this.loading = true
            Promise.all([
                this.$http.get('/establishments/records'),
                this.$http.get('/claimChannels/records'),
            ]).then(([estResp, chanResp]) => {
                this.establishments = estResp.data.data ?? estResp.data
                this.channels       = chanResp.data ?? []
                this.syncedNames    = this.channels.map(c => c.name.toLowerCase())
            }).finally(() => { this.loading = false })
        },

        // Comprueba si un establecimiento ya está registrado como canal
        isSynced(establishment) {
            return this.syncedNames.includes(establishment.description.toLowerCase())
        },

        // Agrega un canal extra manualmente
        store() {
            if (!this.newName.trim())
                return this.$message.error('Ingrese un nombre para el canal')

            this.storing = true
            this.$http.post('/claimChannels/store', { name: this.newName })
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.newName = ''
                        this.refreshChannels()
                    }
                })
                .finally(() => { this.storing = false })
        },

        // Elimina un canal registrado
        destroy(channel) {
            this.$confirm(
                `¿Desea eliminar el canal "${channel.name}"?`,
                'Eliminar canal',
                { confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar', type: 'warning' }
            ).then(() => {
                this.$http.delete(`/claimChannels/destroy/${channel.id}`)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.refreshChannels()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
            }).catch(() => {})
        },

        // Llama al backend para sincronizar los establecimientos como canales
        syncChannels() {
            this.syncing = true
            this.$http.post('/claimChannels/sync')
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.refreshChannels()
                    }
                })
                .catch(() => {
                    this.$message.error('Error al sincronizar los canales')
                })
                .finally(() => { this.syncing = false })
        },

        // Refresca la lista de canales y actualiza el índice de sincronizados
        refreshChannels() {
            this.$http.get('/claimChannels/records').then(res => {
                this.channels    = res.data ?? []
                this.syncedNames = this.channels.map(c => c.name.toLowerCase())
            })
        },

        close() {
            this.$emit('update:showDialog', false)
        }
    }
}
</script>

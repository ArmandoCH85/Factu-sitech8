<template>
    <!-- Dialog unificado para resolución final y reapertura de reclamo -->
    <el-dialog
        :title="dialogTitle"
        :visible.sync="internalVisible"
        :width="mode === 'resolution' ? '480px' : '420px'"
        :close-on-click-modal="false"
        append-to-body
        @close="$emit('cancel')"
    >
        <!-- Modo resolución: requiere texto obligatorio para cerrar el reclamo -->
        <template v-if="mode === 'resolution'">
            <p class="csd-hint">
                Este estado cierra el reclamo. Ingrese la resolución o respuesta al reclamante.
            </p>
            <el-input
                v-model="resolution"
                type="textarea"
                :rows="4"
                placeholder="Describa la resolución adoptada..."
            ></el-input>
        </template>

        <!-- Modo reapertura: advertencia de que el reclamo ya fue cerrado -->
        <template v-if="mode === 'reopen'">
            <el-alert
                title="El reclamo ya fue cerrado. ¿Desea reabrirlo cambiando el estado?"
                type="warning"
                :closable="false"
                show-icon
            ></el-alert>
        </template>

        <div slot="footer">
            <el-button size="small" @click="cancel">Cancelar</el-button>
            <el-button
                size="small"
                :type="mode === 'reopen' ? 'warning' : 'primary'"
                :loading="loading"
                @click="confirm"
            >
                {{ confirmLabel }}
            </el-button>
        </div>
    </el-dialog>
</template>

<script>
export default {
    name: 'ClaimStatusChangeDialog',

    props: {
        // Controla la visibilidad del dialog (.sync)
        visible: {
            type: Boolean,
            default: false
        },
        // 'resolution' = cerrado con texto obligatorio | 'reopen' = advertencia de reapertura
        mode: {
            type: String,
            default: 'resolution',
            validator: v => ['resolution', 'reopen'].includes(v)
        },
        // Estado de carga del botón confirmar
        loading: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            resolution: ''
        }
    },

    computed: {
        internalVisible: {
            get() { return this.visible },
            set(val) { this.$emit('update:visible', val) }
        },
        dialogTitle() {
            return this.mode === 'resolution' ? 'Registrar resolución final' : 'Advertencia'
        },
        confirmLabel() {
            return this.mode === 'resolution' ? 'Confirmar y cerrar' : 'Sí, reabrir'
        }
    },

    watch: {
        // Limpiar el texto al cerrar el dialog
        visible(val) {
            if (!val) this.resolution = ''
        }
    },

    methods: {
        confirm() {
            this.$emit('confirm', this.resolution)
        },
        cancel() {
            this.$emit('update:visible', false)
            this.$emit('cancel')
        }
    }
}
</script>

<style scoped>
.csd-hint {
    font-size: 13px;
    color: #606266;
    margin-bottom: 12px;
}
</style>

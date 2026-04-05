<template>
    <el-dialog
        title="Código de integración — Libro de Reclamaciones"
        :visible.sync="visible"
        width="640px"
        :close-on-click-modal="false"
        @open="buildCode"
        @close="$emit('update:visible', false)"
    >
        <div class="cem-body">
            <p class="cem-description">
                Copia y pega el siguiente tag <code>&lt;script&gt;</code> en tu sitio web externo.
                El formulario de reclamos se insertará automáticamente en esa posición.
            </p>

            <!-- Previa de la URL pública -->
            <div class="cem-url-preview">
                <span class="cem-label">URL del widget:</span>
                <a :href="widgetUrl" target="_blank" class="cem-link">{{ widgetUrl }}</a>
            </div>

            <!-- Bloque de código -->
            <div class="cem-code-block">
                <pre ref="codeBlock" class="cem-pre">{{ embedCode }}</pre>
                <el-button
                    size="mini"
                    type="primary"
                    icon="el-icon-document-copy"
                    class="cem-copy-btn"
                    @click="copyCode"
                >Copiar</el-button>
            </div>

            <p class="cem-note">
                <i class="el-icon-info"></i>
                El script detecta automáticamente el subdominio; no necesitas configurar nada más.
            </p>
        </div>

        <span slot="footer">
            <el-button size="small" @click="$emit('update:visible', false)">Cerrar</el-button>
        </span>
    </el-dialog>
</template>

<style scoped>
.cem-body {
    font-size: 13px;
    color: #606266;
}

.cem-description {
    margin: 0 0 16px;
    line-height: 1.6;
}

.cem-url-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f4f4f5;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 16px;
    overflow: hidden;
}

.cem-label {
    flex-shrink: 0;
    font-weight: 600;
    color: #303133;
}

.cem-link {
    color: #409EFF;
    word-break: break-all;
    font-size: 12px;
}

.cem-code-block {
    position: relative;
    background: #1e1e1e;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 12px;
}

.cem-pre {
    margin: 0;
    color: #d4d4d4;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 12px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-all;
}

.cem-copy-btn {
    position: absolute;
    top: 8px;
    right: 8px;
}

.cem-note {
    margin: 0;
    font-size: 12px;
    color: #909399;
}

.cem-note code {
    background: #f0f2f5;
    padding: 1px 4px;
    border-radius: 3px;
    color: #e6a23c;
}
</style>

<script>
export default {
    name: 'ClaimEmbedModal',

    props: {
        visible: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            widgetUrl: '',
            embedCode: '',
        }
    },

    methods: {
        // Construye la URL del widget y el snippet del script loader a partir del host actual
        buildCode() {
            const origin    = window.location.origin   // ej: https://1.facturaloperu-pro8.oo
            const slug      = window.location.hostname // ej: 1.facturaloperu-pro8.oo
            const scriptUrl = `${origin}/claims/embed.js`

            this.widgetUrl = `${origin}/claims/widget/${slug}`

            // Una sola línea de script; el JS crea e inserta el iframe automáticamente
            this.embedCode = [
                `<!-- Libro de Reclamaciones — copie y pegue esta línea donde quiera mostrar el formulario -->`,
                `<script src="${scriptUrl}"><\/script>`,
            ].join('\n')
        },

        // Copia el código al portapapeles del usuario
        copyCode() {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(this.embedCode).then(() => {
                    this.$message.success('Código copiado al portapapeles')
                }).catch(() => {
                    this.fallbackCopy()
                })
            } else {
                this.fallbackCopy()
            }
        },

        // Fallback para entornos sin Clipboard API
        fallbackCopy() {
            const el = document.createElement('textarea')
            el.value = this.embedCode
            el.style.position = 'fixed'
            el.style.opacity  = '0'
            document.body.appendChild(el)
            el.select()
            try {
                document.execCommand('copy')
                this.$message.success('Código copiado al portapapeles')
            } catch {
                this.$message.error('No se pudo copiar automáticamente. Seleccione el código manualmente.')
            }
            document.body.removeChild(el)
        },
    },
}
</script>

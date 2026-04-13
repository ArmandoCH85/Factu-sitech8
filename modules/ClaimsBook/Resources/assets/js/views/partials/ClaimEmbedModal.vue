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

            <!-- Selector de color primario -->
            <div class="cem-color-row">
                <span class="cem-label">Color primario:</span>
                <el-color-picker
                    v-model="primaryColor"
                    size="medium"
                    style="min-width: 40px;"
                    @change="onColorChange"
                ></el-color-picker>
                <span class="cem-color-hint">{{ primaryColor }}</span>
                <el-button
                    size="mini"
                    type="text"
                    class="cem-reset-color"
                    @click="resetColor"
                >Restablecer</el-button>
            </div>

            <!-- Toggle datos de empresa -->
            <div class="cem-color-row">
                <span class="cem-label">Mostrar datos de la empresa:</span>
                <el-switch v-model="showCompany" active-color="#18181b" @change="buildCode"></el-switch>
                <span class="cem-color-hint" style="margin-left:4px;">
                    {{ showCompany ? 'Logo, razón social y RUC visibles' : 'Se mostrará el ícono del libro' }}
                </span>
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
        </div>

        <span slot="footer">
            <el-button size="small" class="ms-auto" @click="$emit('update:visible', false)">Cerrar</el-button>
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

.cem-color-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f4f4f5;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 16px;
}

.cem-color-hint {
    font-size: 12px;
    color: #606266;
    font-family: 'Consolas', monospace;
}

.cem-reset-color {
    margin-left: auto;
    font-size: 12px;
    color: #909399;
    padding: 0;
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
            primaryColor: '#18181b',
            showCompany: true,
        }
    },

    created() {
        // Recuperar el color guardado previamente; si no existe usar el valor por defecto
        const saved = localStorage.getItem('claims_widget_primary_color')
        if (saved && /^#[0-9A-Fa-f]{6}$/.test(saved)) {
            this.primaryColor = saved
        }
        const savedCompany = localStorage.getItem('claims_widget_show_company')
        if (savedCompany !== null) {
            this.showCompany = savedCompany !== 'false'
        }
    },

    methods: {
        // Persiste el color en localStorage y regenera el código
        onColorChange() {
            localStorage.setItem('claims_widget_primary_color', this.primaryColor)
            this.buildCode()
        },

        // Construye la URL del widget y el snippet del script loader a partir del host actual
        buildCode() {
            const origin    = window.location.origin
            const slug      = window.location.hostname
            const scriptUrl = `${origin}/claims/embed.js`
            const isDefault = this.primaryColor === '#18181b'

            const params = []
            if (!isDefault) params.push(`color=${encodeURIComponent(this.primaryColor)}`)
            if (!this.showCompany) params.push(`show_company=0`)
            const query = params.length ? '?' + params.join('&') : ''

            this.widgetUrl = `${origin}/claims/widget/${slug}${query}`

            const colorAttr   = isDefault ? '' : ` data-color="${this.primaryColor}"`
            const companyAttr = this.showCompany ? '' : ` data-show-company="false"`

            this.embedCode = [
                `<!-- Libro de Reclamaciones — Copie y pegue para mostrar el formulario -->`,
                `<script src="${scriptUrl}"${colorAttr}${companyAttr}><\/script>`,
            ].join('\n')

            localStorage.setItem('claims_widget_show_company', String(this.showCompany))
        },

        // Restablece el color primario al valor por defecto, limpia localStorage y regenera el código
        resetColor() {
            this.primaryColor = '#18181b'
            localStorage.removeItem('claims_widget_primary_color')
            this.buildCode()
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

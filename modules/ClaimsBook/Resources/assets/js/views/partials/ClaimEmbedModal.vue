<template>
    <el-dialog
        title="Código de integración — Libro de Reclamaciones"
        :visible.sync="visible"
        width="640px"
        :close-on-click-modal="false"
        @open="onOpen"
        @close="$emit('update:visible', false)"
    >
        <div class="cem-body">
            <!-- 1. Personalización -->
            <div class="cem-section">
                <div class="cem-section-header">
                    <i class="el-icon-magic-stick cem-section-icon"></i>
                    <span class="cem-section-title">Personalización</span>
                </div>
                <div class="cem-section-body">
                    <div class="cem-option-row">
                        <span class="cem-option-label">Color primario</span>
                        <div class="cem-option-control">
                            <input
                                type="color"
                                v-model="primaryColor"
                                class="cem-color-input"
                                @change="onColorChange"
                            />
                            <span class="cem-color-hint">{{ primaryColor }}</span>
                            <el-button size="mini" type="text" class="cem-reset-color" @click="resetColor">
                                Restablecer
                            </el-button>
                        </div>
                    </div>
                    <div class="cem-option-row cem-option-row--last">
                        <span class="cem-option-label">Datos de la empresa</span>
                        <div class="cem-option-control">
                            <el-switch v-model="showCompany" active-color="#18181b" @change="onShowCompanyChange"></el-switch>
                            <span class="cem-color-hint">
                                {{ showCompany ? 'Logo, razón social y RUC visibles' : 'Se mostrará el ícono del libro' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Código de integración -->
            <div class="cem-section">
                <div class="cem-section-header">
                    <i class="el-icon-s-platform cem-section-icon"></i>
                    <span class="cem-section-title">Código de integración</span>
                </div>
                <div class="cem-section-body">
                    <p class="cem-description">
                        Copia y pega el siguiente tag <code>&lt;script&gt;</code> en tu sitio web externo.
                        El formulario de reclamos se insertará automáticamente en esa posición.
                    </p>
                    <div class="cem-code-block">
                        <pre ref="codeBlock" class="cem-pre">{{ embedCode }}</pre>
                        <el-button
                            size="mini"
                            type="primary"
                            icon="el-icon-document-copy"
                            class="cem-copy-btn btn btn-sm"
                            @click="copyCode"
                        >Copiar</el-button>
                    </div>
                </div>
            </div>

            <!-- 3. URL pública -->
            <div class="cem-section">
                <div class="cem-section-header">
                    <i class="el-icon-link cem-section-icon"></i>
                    <span class="cem-section-title">URL pública del libro</span>
                </div>
                <div class="cem-section-body">
                    <!-- URL activa (default o personalizada) -->
                    <div class="cem-url-row">
                        <span class="cem-url-label">Enlace público</span>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="cem-url-text">{{ activeWidgetUrl }}</span>
                            <el-button size="mini" icon="el-icon-document-copy" class="btn btn-sm" @click="copyWidgetUrl">
                                Copiar
                            </el-button>
                        </div>
                    </div>

                    <!-- Descargar aviso PDF -->
                    <div class="d-flex align-items-center justify-content-start gap-2 mt-2">
                        <button
                            class="btn btn-sm btn-outline-info"
                            @click="openWidgetUrl"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-external-link" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                            Abrir
                        </button>
                        <button
                            class="btn btn-sm btn-outline-success"
                            @click="downloadAvisoPdf"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-text" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" /><path d="M9 9l1 0" /><path d="M9 13l6 0" /><path d="M9 17l6 0" /></svg>
                            Imprimir aviso
                        </button>
                    </div>

                    <!-- Toggle URL personalizada -->
                    <div class="cem-option-row cem-option-row--last" style="margin-top:10px;">
                        <div>
                            <span class="cem-option-label">Usar URL personalizada</span>
                            <p class="cem-url-hint">Si tu sitio web está en otro dominio, indica su URL para que el widget cargue correctamente.</p>
                        </div>
                        <el-switch v-model="useCustomUrl" active-color="#18181b" @change="onCustomUrlToggle"></el-switch>
                    </div>

                    <!-- Input URL personalizada -->
                    <div v-if="useCustomUrl" class="cem-custom-url-input">
                        <el-input
                            v-model="customUrl"
                            size="small"
                            placeholder="https://miempresa.com"
                            prefix-icon="el-icon-link"
                            clearable
                            @input="buildCode"
                        ></el-input>
                        <div class="cem-save-row">
                            <el-button
                                size="mini"
                                type="primary"
                                :loading="savingUrl"
                                @click="saveWidgetSettings"
                            >Guardar</el-button>
                        </div>
                    </div>
                </div>
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
    margin: 0 0 12px;
    line-height: 1.6;
    color: #909399;
    font-size: 12px;
}

/* Secciones */
.cem-section {
    border: 1px solid #e4e7ed;
    border-radius: 8px;
    margin-bottom: 16px;
    overflow: hidden;
}

.cem-section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f7f8fa;
    border-bottom: 1px solid #e4e7ed;
    padding: 9px 14px;
}

.cem-section-icon {
    font-size: 14px;
    color: #409EFF;
}

.cem-section-title {
    font-weight: 600;
    font-size: 13px;
    color: #303133;
}

.cem-section-body {
    padding: 12px 14px;
}

/* URL */
.cem-url-row {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.cem-url-label {
    font-weight: 500;
    font-size: 12px;
    color: #909399;
}

.cem-url-field {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f4f4f5;
    border: 1px solid #e4e7ed;
    border-radius: 6px;
    padding: 7px 12px;
    overflow: hidden;
}

.cem-url-text {
    flex: 1;
    font-size: 12px;
    color: #303133;
    word-break: break-all;
    font-family: 'Consolas', monospace;
}

.cem-url-action {
    flex-shrink: 0;
    font-size: 12px;
    color: #409EFF;
    text-decoration: none;
    white-space: nowrap;
}

.cem-url-action:hover {
    text-decoration: underline;
}

.cem-url-hint {
    margin: 3px 0 0;
    font-size: 11px;
    color: #c0c4cc;
    line-height: 1.4;
}

.cem-custom-url-input {
    margin-top: 10px;
}

.cem-save-row {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
}

.cem-pdf-row {
    display: flex;
    align-items: center;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f0f2f5;
}

.cem-link {
    color: #409EFF;
    word-break: break-all;
    font-size: 12px;
}

/* Opciones de personalización */
.cem-option-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f2f5;
}

.cem-option-row--last {
    border-bottom: none;
    padding-bottom: 2px;
}

.cem-option-label {
    font-weight: 500;
    color: #303133;
    font-size: 13px;
    flex-shrink: 0;
}

.cem-option-control {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cem-color-hint {
    font-size: 12px;
    color: #909399;
    font-family: 'Consolas', monospace;
}

.cem-color-input {
    width: 75px;
    height: 32px;
    padding: 2px;
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    cursor: pointer;
    background: none;
}

.cem-reset-color {
    font-size: 12px;
    color: #c0c4cc;
    padding: 0;
}

.cem-reset-color:hover {
    color: #909399;
}

/* Código */
.cem-code-block {
    position: relative;
    background: #1e1e1e;
    border-radius: 6px;
    padding: 14px 16px;
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
            useCustomUrl: false,
            customUrl: '',
            savingUrl: false,
        }
    },

    computed: {
        activeWidgetUrl() {
            return this.useCustomUrl && this.customUrl.trim()
                ? this.customUrl.trim()
                : this.widgetUrl
        },
    },

    methods: {
        hexToOklch(hex) {
            const r = parseInt(hex.slice(1,3),16)/255, g = parseInt(hex.slice(3,5),16)/255, b = parseInt(hex.slice(5,7),16)/255
            const toLinear = c => c <= 0.04045 ? c/12.92 : ((c+0.055)/1.055)**2.4
            const lr = toLinear(r), lg = toLinear(g), lb = toLinear(b)
            const l = 0.4122214708*lr + 0.5363325363*lg + 0.0514459929*lb
            const m = 0.2119034982*lr + 0.6806995451*lg + 0.1073969566*lb
            const s = 0.0883024619*lr + 0.2817188376*lg + 0.6299787005*lb
            const l_ = Math.cbrt(l), m_ = Math.cbrt(m), s_ = Math.cbrt(s)
            const L  = 0.2104542553*l_ + 0.7936177850*m_ - 0.0040720468*s_
            const a  = 1.9779984951*l_ - 2.4285922050*m_ + 0.4505937099*s_
            const bv = 0.0259040371*l_ + 0.7827717662*m_ - 0.8086757660*s_
            const C  = Math.sqrt(a*a + bv*bv)
            let   H  = Math.atan2(bv, a) * 180 / Math.PI
            if (H < 0) H += 360
            return { l: +L.toFixed(4), c: +C.toFixed(4), h: +H.toFixed(2) }
        },

        oklchToHex(l, c, h) {
            const hRad = h * Math.PI / 180
            const a = c * Math.cos(hRad), bv = c * Math.sin(hRad)
            const l_ = l + 0.3963377774*a + 0.2158037573*bv
            const m_ = l - 0.1055613458*a - 0.0638541728*bv
            const s_ = l - 0.0894841775*a - 1.2914855480*bv
            const lv = l_**3, mv = m_**3, sv = s_**3
            const r  = +4.0767416621*lv - 3.3077115913*mv + 0.2309699292*sv
            const g  = -1.2684380046*lv + 2.6097574011*mv - 0.3413193965*sv
            const b  = -0.0041960863*lv - 0.7034186147*mv + 1.7076147010*sv
            const toSrgb = v => { const cl = Math.max(0, Math.min(1, v)); return cl <= 0.0031308 ? 12.92*cl : 1.055*cl**(1/2.4) - 0.055 }
            const toHex  = v => Math.round(toSrgb(v) * 255).toString(16).padStart(2, '0')
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`
        },

        async onOpen() {
            try {
                const { data } = await this.$http.get('/claims/widget-settings')
                this.customUrl    = data.widget_custom_url || ''
                this.useCustomUrl = !!data.widget_custom_url_active
                if (data.primary_color && /^#[0-9A-Fa-f]{6}$/.test(data.primary_color)) {
                    this.primaryColor = data.primary_color
                }
                if (data.show_company !== undefined) {
                    this.showCompany = !!data.show_company
                }
            } catch {
                this.customUrl    = ''
                this.useCustomUrl = false
                // Fallback a localStorage si la API falla
                const savedOklch = localStorage.getItem('claims_widget_primary_oklch')
                if (savedOklch) {
                    try {
                        const { l, c, h } = JSON.parse(savedOklch)
                        this.primaryColor = this.oklchToHex(l, c, h)
                    } catch {}
                } else {
                    const saved = localStorage.getItem('claims_widget_primary_color')
                    if (saved && /^#[0-9A-Fa-f]{6}$/.test(saved)) this.primaryColor = saved
                }
                const savedCompany = localStorage.getItem('claims_widget_show_company')
                if (savedCompany !== null) this.showCompany = savedCompany !== 'false'
            }

            this.buildCode()
        },

        async saveWidgetSettings() {
            this.savingUrl = true
            try {
                await this.$http.put('/claims/widget-settings', {
                    widget_custom_url:        this.customUrl.trim() || null,
                    widget_custom_url_active: this.useCustomUrl,
                })
                this.$message.success('URL personalizada guardada')
                this.buildCode()
            } catch (e) {
                const msg = e?.response?.data?.message
                    || e?.response?.data?.errors?.widget_custom_url?.[0]
                    || 'No se pudo guardar la configuración'
                this.$message.error(msg)
            } finally {
                this.savingUrl = false
            }
        },

        async onShowCompanyChange() {
            this.buildCode()
            try {
                await this.$http.put('/claims/widget-settings', { show_company: this.showCompany })
            } catch {
                this.$message.error('No se pudo guardar la configuración')
            }
        },

        async onColorChange() {
            const oklch = this.hexToOklch(this.primaryColor)
            localStorage.setItem('claims_widget_primary_oklch', JSON.stringify(oklch))
            localStorage.setItem('claims_widget_primary_color', this.primaryColor)
            this.buildCode()
            try {
                await this.$http.put('/claims/widget-settings', { primary_color: this.primaryColor })
            } catch {
                this.$message.error('No se pudo guardar el color')
            }
        },

        buildCode() {
            const origin    = window.location.origin
            const slug      = window.location.hostname
            const scriptUrl = `${origin}/claims/embed.js`
            const isDefault = this.primaryColor === '#18181b'

            const params = []
            let colorAttrs = ''
            if (!isDefault) {
                const { l, c, h } = this.hexToOklch(this.primaryColor)
                colorAttrs = ` data-color-l="${l}" data-color-c="${c}" data-color-h="${h}"`
                params.push(`color_l=${l}`, `color_c=${c}`, `color_h=${h}`)
            }
            if (!this.showCompany) params.push(`show_company=0`)
            const query = params.length ? '?' + params.join('&') : ''

            this.widgetUrl = `${origin}/claims/widget/${slug}${query}`

            const companyAttr = this.showCompany ? '' : ` data-show-company="false"`
            const urlAttr     = (this.useCustomUrl && this.customUrl.trim())
                ? ` data-url="${this.customUrl.trim()}"`
                : ''

            this.embedCode = [
                `<!-- Libro de Reclamaciones — Copie y pegue para mostrar el formulario -->`,
                `<script src="${scriptUrl}"${colorAttrs}${companyAttr}${urlAttr}><\/script>`,
            ].join('\n')

            localStorage.setItem('claims_widget_show_company', String(this.showCompany))
        },

        async onCustomUrlToggle() {
            this.buildCode()
            try {
                await this.$http.put('/claims/widget-settings', {
                    widget_custom_url_active: this.useCustomUrl,
                })
                this.$message.success(this.useCustomUrl ? 'URL personalizada activada' : 'URL personalizada desactivada')
            } catch {
                this.$message.error('No se pudo guardar el cambio')
                this.useCustomUrl = !this.useCustomUrl
            }
        },

        openWidgetUrl() {
            window.open(this.activeWidgetUrl, '_blank')
        },

        downloadAvisoPdf() {
            window.open('/claims/complaints-book-notice', '_blank')
        },

        copyWidgetUrl() {
            const url = this.activeWidgetUrl
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    this.$message.success('URL copiada al portapapeles')
                }).catch(() => this.$message.error('No se pudo copiar'))
            } else {
                const el = document.createElement('textarea')
                el.value = url
                el.style.position = 'fixed'
                el.style.opacity = '0'
                document.body.appendChild(el)
                el.select()
                try { document.execCommand('copy'); this.$message.success('URL copiada al portapapeles') }
                catch { this.$message.error('No se pudo copiar') }
                document.body.removeChild(el)
            }
        },

        async resetColor() {
            this.primaryColor = '#18181b'
            localStorage.removeItem('claims_widget_primary_color')
            localStorage.removeItem('claims_widget_primary_oklch')
            this.buildCode()
            try {
                await this.$http.put('/claims/widget-settings', { primary_color: '#18181b' })
            } catch {
                this.$message.error('No se pudo restablecer el color')
            }
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

<template>
    <div>
        <div class="card">
            <div class="card-header bg-info bg-info-customer-admin">
                <h3 class="my-0">Configuración de correo</h3>
            </div>
            <form class="row card-body px-0" autocomplete="off" @submit.prevent="submit">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="mail_host">Dirección del host de correo</label>
                        <el-input id="mail_host" v-model="form.mail_host"></el-input>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="mail_port">Puerto del host de correo</label>
                        <el-input id="mail_port" v-model="form.mail_port"></el-input>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="mail_username">Nombre de usuario de correo</label>
                        <el-input id="mail_username" v-model="form.mail_username"></el-input>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="mail_password">Contraseña del usuario de correo</label>
                        <el-input id="mail_password" v-model="form.mail_password" type="password"></el-input>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="mail_encryption">Encriptación de correo</label>
                        <el-select id="mail_encryption" v-model="form.mail_encryption" style="width: 100%">
                            <el-option label="SSL" value="ssl"></el-option>
                            <el-option label="TLS" value="tls"></el-option>
                            <el-option label="Ninguna" value=""></el-option>
                        </el-select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group pt-3">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            @click="openMailManual"
                        >
                            Para correos Gmail verificar el manual
                        </button>
                    </div>
                </div>
                <div class="col-md-12 text-end pt-2">
                    <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
                </div>
            </form>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-info bg-info-customer-admin">
                <h3 class="my-0">Buscador de documentos embebido</h3>
            </div>
            <div class="card-body">
                    <p class="text-muted mb-3">
                        Copia este script y pégalo en la web del cliente para mostrar el buscador de comprobantes.
                    </p>

                    <div class="form-group mb-3">
                        <label class="control-label" for="consultation_embed_code">Script de integración</label>
                        <div class="bg-dark text-light rounded p-3 position-relative border" style="border-color: #111827 !important; box-shadow: 0 6px 16px rgba(2,6,23,.24);">
                            <pre id="consultation_embed_code" class="mb-0 text-break" style="white-space: pre-wrap; word-break: break-word; font-size: 12px; color: #f8fafc;">{{ consultationEmbedCode }}</pre>
                            <el-button
                                size="mini"
                                type="primary"
                                icon="el-icon-document-copy"
                                class="position-absolute"
                                style="top: 10px; right: 10px;"
                                @click="copyConsultationCode(consultationEmbedCode)"
                            >Copiar</el-button>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Este es el código que el cliente debe pegar en su web para mostrar el buscador embebido.
                        </small>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="form-group mb-0">
                                <label class="control-label" for="consultation_url">URL pública</label>
                                <el-input id="consultation_url" v-model="consultationUrl" readonly>
                                    <el-button slot="append" icon="el-icon-document-copy" @click="copyConsultationCode(consultationUrl)">Copiar</el-button>
                                </el-input>
                                <small class="text-muted d-block mt-1">
                                    URL por cliente (tenant): incluye el dominio del cliente en la ruta del widget.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="control-label" for="consultation_api_url">API pública</label>
                                <el-input id="consultation_api_url" v-model="consultationApiUrl" readonly>
                                    <el-button slot="append" icon="el-icon-document-copy" @click="copyConsultationCode(consultationApiUrl)">Copiar</el-button>
                                </el-input>
                                <small class="text-muted d-block mt-1">
                                    Consumir con <strong>POST</strong> enviando los datos en JSON.
                                </small>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-3">
                        El script detecta automáticamente el dominio principal y muestra el formulario dentro de un iframe.
                    </small>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['configuration'],
    created() {
        this.initForm();
        this.buildConsultationIntegration();
    },
    data() {
        return {
            loading_submit: false,
            resource: 'configurations',
            errors: {},
            consultationUrl: '',
            consultationApiUrl: '',
            consultationEmbedCode: '',
            form: {
                mail_host: '',
                mail_port: '',
                mail_username: '',
                mail_password: '',
                mail_encryption: ''
            }
        }
    },
    methods: {
        initForm() {
            this.form = {
                mail_host: this.configuration.mail_host || '',
                mail_port: this.configuration.mail_port || '',
                mail_username: this.configuration.mail_username || '',
                mail_password: this.configuration.mail_password || '',
                mail_encryption: this.configuration.mail_encryption || ''
            };
        },
        buildConsultationIntegration() {
            const origin = globalThis.location.origin;
            const slug = globalThis.location.hostname;
            this.consultationUrl = `${origin}/consultas/widget/${slug}`;
            this.consultationApiUrl = `${origin}/api/consultas/search`;
            this.consultationEmbedCode = [
                '<!-- Consulta de comprobantes — copie y pegue esta línea donde quiera mostrar el formulario -->',
                '<script src="' + origin + '/consultas/embed.js?tenant=' + slug + '"><' + '/script>',
            ].join('\n');
        },
        openMailManual() {
            globalThis.open('https://manual.uio.la/Pro7/guias-adicionales/configuracion-smtp-segura', '_blank', 'noopener');
        },
        copyConsultationCode(text) {
            if (navigator.clipboard && globalThis.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(() => this.$message.success('Código copiado al portapapeles'))
                    .catch(() => this.fallbackCopy(text));
            } else {
                this.fallbackCopy(text);
            }
        },
        fallbackCopy(text) {
            const el = document.createElement('textarea');
            el.value = text;
            el.style.position = 'fixed';
            el.style.opacity = '0';
            document.body.appendChild(el);
            el.select();
            try {
                document.execCommand('copy');
                this.$message.success('Código copiado al portapapeles');
            } catch (error) {
                console.error(error);
                this.$message.error('No se pudo copiar automáticamente. Seleccione el texto manualmente.');
            }
            el.remove();
        },
        submit() {
            this.loading_submit = true;
            // Logic to submit the form data
            // After submission, reset loading state
            this.$http.post(`${this.resource}/emails`, this.form)
                .then(response => {
                    if (response.data.success) {
                        return this.$message.success('Configuración guardada correctamente');
                    }
                })
                .catch(error => {
                    this.errors = error.response.data.errors || {};
                    this.$message.error('Error al guardar la configuración');
                })
                .finally(() => {
                    this.loading_submit = false;
                });

            this.loading_submit = false;
        }
    },
}
</script>

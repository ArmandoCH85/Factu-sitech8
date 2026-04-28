<template>
<div class="card">
    <div class="card-header bg-info bg-info-customer-admin">
        <h3 class="my-0">Temas del sistema</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <p class="mb-3">Administra los temas CSS del sistema. Los temas subidos desde aquí se propagan a todos los tenants.</p>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tema</th>
                                <th>Archivo</th>
                                <th class="text-center">Origen</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(skin, index) in skins" :key="skin.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ skin.name }}</td>
                                <td>{{ skin.filename }}</td>
                                <td class="text-center">
                                    <span v-if="skin.is_default" class="badge badge-info">Por defecto</span>
                                    <span v-else class="badge badge-success">Personalizado</span>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-info btn-sm mr-1"
                                       :href="'/storage/skins/' + skin.filename"
                                       :download="skin.filename"
                                       title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button v-if="!skin.is_default"
                                            class="btn btn-danger btn-sm"
                                            title="Eliminar"
                                            :disabled="loading_delete"
                                            @click.prevent="deleteSkin(skin)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 mt-3">
                <h6>Subir tema personalizado</h6>
                <el-upload
                    ref="upload"
                    accept=".css"
                    :headers="headers"
                    :action="uploadAction"
                    :auto-upload="false"
                    :multiple="false"
                    :limit="1"
                    :on-change="onFileChange"
                    :on-remove="onFileRemove"
                    :on-success="onUploadSuccess"
                    :on-error="onUploadError"
                    :show-file-list="true">
                    <el-button slot="trigger" size="small" type="primary">
                        <i class="fas fa-folder-open mr-1"></i> Seleccionar archivo .css
                    </el-button>
                    <div slot="tip" class="el-upload__tip mt-1">Solo archivos <strong>.css</strong></div>
                </el-upload>
                <div class="mt-3">
                    <el-button
                        size="small"
                        type="success"
                        :disabled="!selectedFile || loading_upload"
                        :loading="loading_upload"
                        @click.prevent="submitUpload">
                        <i class="fas fa-upload mr-1"></i> Subir tema
                    </el-button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    data() {
        return {
            skins: [],
            selectedFile: null,
            loading_upload: false,
            loading_delete: false,
            headers: headers_token,
            uploadAction: '/configurations/system-skins/upload',
        };
    },
    created() {
        this.loadSkins();
    },
    methods: {
        loadSkins() {
            this.$http.get('configurations/system-skins').then(response => {
                if (response.data.success) {
                    this.skins = response.data.skins;
                }
            });
        },
        onFileChange(file) {
            this.selectedFile = file;
        },
        onFileRemove() {
            this.selectedFile = null;
        },
        submitUpload() {
            this.loading_upload = true;
            this.$refs.upload.submit();
        },
        onUploadSuccess(response) {
            this.loading_upload = false;
            if (response.success) {
                this.$message.success(response.message);
                this.skins = response.skins;
                this.selectedFile = null;
                this.$refs.upload.clearFiles();
            } else {
                this.$message.error(response.message);
            }
        },
        onUploadError() {
            this.loading_upload = false;
            this.$message.error('Error al subir el archivo');
        },
        deleteSkin(skin) {
            this.$confirm(`¿Eliminar el tema "${skin.name}"? Se eliminará de todos los tenants.`, 'Confirmar', {
                type: 'warning',
            }).then(() => {
                this.loading_delete = true;
                this.$http.post('configurations/system-skins/delete', { id: skin.id }).then(response => {
                    this.loading_delete = false;
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.skins = response.data.skins;
                    } else {
                        this.$message.error(response.data.message);
                    }
                }).catch(() => {
                    this.loading_delete = false;
                    this.$message.error('Error al eliminar el tema');
                });
            }).catch(() => {});
        },
    },
};
</script>

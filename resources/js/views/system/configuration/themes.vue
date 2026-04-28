<template>
<div class="card">
    <div class="card-header bg-info bg-info-customer-admin">
        <h3 class="my-0">Temas del sistema</h3>
    </div>
    <div class="card-body">

        <div class="fw-bold text-muted mb-3">
            <span>Temas disponibles</span>
            <el-tag type="primary" class="ms-1">{{ skins.length }}</el-tag>
        </div>

        <div v-if="!skins || skins.length === 0"
             class="text-center py-4 px-2 text-muted"
             style="border: 1px dashed #dcdfe6; border-radius: 4px;">
            <i class="el-icon-picture-outline" style="font-size: 24px; margin-bottom: 10px;"></i>
            <p style="margin: 0; font-size: 14px;">No hay temas cargados aún</p>
        </div>

        <div v-else>
            <div
                v-for="skin in skins"
                :key="skin.id"
                class="d-flex align-items-center justify-content-between p-2 mb-2 template-skin-item">
                <div style="flex: 1;">
                    <span style="font-size: 14px;" class="fw-medium">
                        {{ skin.name }}
                        <small v-if="skin.is_default" class="text-muted ms-1">(Tema del sistema)</small>
                    </span>
                    <div style="font-size: 12px;" class="mt-1">
                        <i class="el-icon-document me-2"></i>{{ skin.filename }}
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <el-button
                        size="mini"
                        type="primary"
                        plain
                        @click="() => { window.open('/storage/skins/' + skin.filename, '_blank') }">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Descargar
                    </el-button>
                    <el-button
                        v-if="!skin.is_default"
                        size="mini"
                        type="danger"
                        plain
                        :loading="loading_delete"
                        @click.prevent="confirmDelete(skin)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                        Eliminar
                    </el-button>
                </div>
            </div>
        </div>

        <el-divider>Subir nuevo tema</el-divider>

        <div>
            <p style="font-size: 12px;" class="mb-2">
                <i class="el-icon-warning-outline"></i> Solo se aceptan archivos <strong>.css</strong>. Los temas subidos se propagan a todos los tenants.
            </p>
            <el-upload
                ref="upload"
                :headers="headers"
                :action="uploadAction"
                :multiple="false"
                :on-remove="onFileRemove"
                :on-success="onUploadSuccess"
                :on-error="onUploadError"
                :limit="1"
                drag
                accept=".css"
                style="width: 100%;">
                <i class="el-icon-upload"></i>
                <div class="el-upload__text">Arrastra tu archivo CSS aquí o <em>haz clic para subir</em></div>
            </el-upload>
        </div>

    </div>
</div>
</template>
<script>
export default {
    data() {
        return {
            skins: [],
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
        onFileRemove() {},
        onUploadSuccess(response) {
            if (response.success) {
                this.$message.success(response.message);
                this.skins = response.skins;
                this.$refs.upload.clearFiles();
            } else {
                this.$refs.upload.clearFiles();
                this.$message.error(response.message);
            }
        },
        onUploadError() {
            this.$message.error('Error al subir el archivo');
        },
        confirmDelete(skin) {
            this.$confirm(`¿Estás seguro de eliminar el tema "${skin.name}"? Se eliminará de todos los tenants.`, 'Confirmar', {
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                type: 'warning',
            }).then(() => {
                this.deleteSkin(skin);
            }).catch(() => {});
        },
        deleteSkin(skin) {
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
        },
    },
};
</script>

<style scoped>
.el-upload {
    width: 100%;
}
::v-deep .el-upload-dragger {
    width: 100%;
}
</style>

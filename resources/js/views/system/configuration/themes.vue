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
                <div class="skin-info me-2">
                    <span style="font-size: 14px;" class="fw-medium">
                        {{ skin.name }}
                        <small v-if="skin.is_default" class="text-muted ms-1">(Tema del sistema)</small>
                    </span>
                    <div style="font-size: 12px;" class="mt-1">
                        <div v-if="skin.is_replaced" class="d-flex align-items-center gap-1">
                            <div class="d-flex align-items-center">
                                <i class="el-icon-document me-2"></i>
                                <span class="crossed-out">{{ skin.filename }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-1" style="color:#e6a23c;margin-top:-2px;display:inline-block"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M15 16l4 -4"/><path d="M15 8l4 4"/></svg>
                                <span class="fw-medium text-warning">{{ skin.active_filename }}</span>
                            </div>   
                        </div> 
                        <div v-else class="d-flex align-items-center">
                            <i class="el-icon-document me-2"></i>
                            <span>{{ skin.filename }}</span>
                        </div>                                               
                    </div>
                </div>

                <div class="d-flex skin-actions">
                    <el-button
                        size="mini"
                        type="primary"
                        plain
                        @click="() => { window.open('/storage/skins/' + skin.filename, '_blank') }">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Descargar
                    </el-button>
                    <el-button
                        v-if="skin.is_default"
                        size="mini"
                        type="warning"
                        plain
                        @click.prevent="openReplaceDialog(skin)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/><path d="M7 9l5 -5l5 5"/><path d="M12 4l0 12"/></svg>
                        Reemplazar
                    </el-button>
                    <el-button
                        v-if="skin.is_replaced"
                        size="mini"
                        type="info"
                        plain
                        :loading="loading_revert"
                        @click.prevent="confirmRevert(skin)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg>
                        Restaurar
                    </el-button>
                    <!-- <el-tooltip v-if="skin.is_default && !skin.is_replaced" content="Fuerza a todos los tenants a usar el archivo original (útil si el tema quedó en estado inconsistente)" placement="top">
                        <el-button
                            size="mini"
                            type="danger"
                            plain
                            :loading="loading_sync === skin.id"
                            @click.prevent="confirmSync(skin)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top: -2px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>
                            Reparar
                        </el-button>
                    </el-tooltip> -->
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

        <el-divider>Tema por defecto para nuevos tenants</el-divider>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div style="font-size: 13px;" class="text-muted me-2">
                Skin que se asignará al crear una nueva empresa:
            </div>
            <el-select
                v-model="selectedTenantDefaultId"
                size="small"
                placeholder="Seleccionar tema"
                style="width: 180px;">
                <el-option
                    v-for="skin in skins"
                    :key="skin.id"
                    :label="skin.name"
                    :value="skin.id">
                </el-option>
            </el-select>
            <el-button
                size="small"
                type="primary"
                :loading="loading_set_default"
                :disabled="selectedTenantDefaultId === currentTenantDefaultId"
                @click="saveTenantDefault">
                Guardar
            </el-button>
        </div>

        <el-divider>Forzar tema a todas las empresas</el-divider>

        <div>
            <p style="font-size: 12px;" class="mb-3 text-muted">
                <i class="el-icon-warning-outline"></i>
                Selecciona un tema y presiona <strong>Forzar</strong> para que todas las empresas existentes cambien inmediatamente a ese tema.
                <span v-if="currentForcedSkin" class="ms-1">
                    Tema actualmente forzado: <strong>{{ currentForcedSkin.name }}</strong>.
                </span>
            </p>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <el-select
                    v-model="selectedForceId"
                    size="small"
                    placeholder="Seleccionar tema"
                    style="width: 180px;">
                    <el-option
                        v-for="skin in skins"
                        :key="skin.id"
                        :label="skin.name"
                        :value="skin.id">
                        <span>{{ skin.name }}</span>
                        <el-tag v-if="skin.is_forced" size="mini" type="warning" class="ms-1">Forzado</el-tag>
                    </el-option>
                </el-select>
                <el-button
                    size="small"
                    type="danger"
                    :loading="loading_force"
                    :disabled="!selectedForceId"
                    @click="confirmForce">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top:-2px;margin-right:3px;display:inline-block"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11"/></svg>
                    Forzar
                </el-button>
            </div>
        </div>

        <el-divider>Subir nuevo tema</el-divider>

        <div>
            <p style="font-size: 12px;" class="mb-2">
                <i class="el-icon-warning-outline"></i> Solo se aceptan archivos <strong>.css</strong>. Los temas subidos se propagan a todos los tenants.
            </p>
            <el-upload
                ref="upload"
                :auto-upload="false"
                :multiple="false"
                :on-change="onFileChange"
                :on-remove="onFileRemove"
                :limit="1"
                drag
                accept=".css"
                action=""
                style="width: 100%;">
                <i class="el-icon-upload"></i>
                <div class="el-upload__text">Arrastra tu archivo CSS aquí o <em>haz clic para subir</em></div>
            </el-upload>
        </div>

        <!-- Diálogo de conflicto de nombre -->
        <el-dialog
            title="Nombre de archivo en uso"
            :visible.sync="showRenameDialog"
            width="420px"
            :close-on-click-modal="false"
            @close="onRenameDialogClose">
            <div>
                <el-alert
                    :title="`El archivo &quot;${conflictFilename}&quot; ya existe en el sistema.`"
                    type="warning"
                    :closable="false"
                    show-icon
                    class="mb-3">
                </el-alert>
                <p style="font-size: 13px;" class="mb-2">Puedes subir el archivo con un nombre diferente:</p>
                <el-input
                    v-model="renameValue"
                    placeholder="Nuevo nombre (sin extensión)"
                    @keyup.enter.native="confirmRename">
                    <template slot="append">.css</template>
                </el-input>
                <p v-if="renameError" style="font-size: 12px; color: #f56c6c;" class="mt-1">{{ renameError }}</p>
            </div>
            <span slot="footer">
                <el-button @click="onRenameDialogClose">Cancelar</el-button>
                <el-button type="primary" :loading="loading_upload" @click="confirmRename">Subir con este nombre</el-button>
            </span>
        </el-dialog>

        <!-- Diálogo de reemplazo de tema por defecto -->
        <el-dialog
            :title="`Reemplazar tema: ${replacingSkin ? replacingSkin.name : ''}`"
            :visible.sync="showReplaceDialog"
            width="460px"
            :close-on-click-modal="false"
            @close="onReplaceDialogClose">
            <div v-if="replacingSkin">
                <el-alert
                    type="info"
                    :closable="false"
                    show-icon
                    class="mb-3">
                    <template slot="title">
                        El archivo original <strong>{{ replacingSkin.filename }}</strong> no se modificará.
                        Los tenants usarán el nuevo CSS con el mismo nombre <strong>"{{ replacingSkin.name }}"</strong>.
                        Para volver al original usa <em>Restaurar original</em>.
                    </template>
                </el-alert>
                <el-upload
                    ref="uploadReplace"
                    :auto-upload="false"
                    :multiple="false"
                    :on-change="onReplaceFileChange"
                    :on-remove="onReplaceFileRemove"
                    :limit="1"
                    drag
                    accept=".css"
                    action=""
                    style="width: 100%;">
                    <i class="el-icon-upload"></i>
                    <div class="el-upload__text">Arrastra el nuevo CSS aquí o <em>haz clic para seleccionar</em></div>
                </el-upload>
            </div>
            <span slot="footer">
                <el-button @click="onReplaceDialogClose">Cancelar</el-button>
                <el-button
                    type="warning"
                    :loading="loading_replace"
                    :disabled="!pendingReplaceFile"
                    @click="confirmReplace">
                    Reemplazar
                </el-button>
            </span>
        </el-dialog>

    </div>
</div>
</template>

<script>
export default {
    data() {
        return {
            skins: [],
            loading_delete: false,
            loading_upload: false,
            loading_replace: false,
            loading_revert: false,
            loading_sync: null,
            loading_set_default: false,
            headers: headers_token,
            pendingFile: null,
            showRenameDialog: false,
            conflictFilename: '',
            renameValue: '',
            renameError: '',
            showReplaceDialog: false,
            replacingSkin: null,
            pendingReplaceFile: null,
            selectedTenantDefaultId: null,
            currentTenantDefaultId: null,
            selectedForceId: null,
            loading_force: false,
        };
    },
    computed: {
        currentForcedSkin() {
            return this.skins.find(s => s.is_forced) || null;
        },
    },
    created() {
        this.loadSkins();
    },
    methods: {
        loadSkins(skins = null) {
            if (skins) {
                this.skins = skins;
                this.syncTenantDefault();
                return;
            }
            this.$http.get('configurations/system-skins').then(response => {
                if (response.data.success) {
                    this.skins = response.data.skins;
                    this.syncTenantDefault();
                }
            });
        },
        syncTenantDefault() {
            const def = this.skins.find(s => s.is_tenant_default);
            if (def) {
                this.currentTenantDefaultId = def.id;
                this.selectedTenantDefaultId = def.id;
            }
        },
        saveTenantDefault() {
            this.loading_set_default = true;
            this.$http.post('configurations/system-skins/set-tenant-default', { skin_id: this.selectedTenantDefaultId }).then(response => {
                this.loading_set_default = false;
                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.skins = response.data.skins;
                    this.syncTenantDefault();
                } else {
                    this.$message.error(response.data.message);
                }
            }).catch(() => {
                this.loading_set_default = false;
                this.$message.error('Error al actualizar el tema por defecto');
            });
        },

        confirmForce() {
            const skin = this.skins.find(s => s.id === this.selectedForceId);
            if (!skin) return;
            this.$confirm(
                `¿Forzar el tema "${skin.name}" en todas las empresas existentes? El tema activo de cada empresa se cambiará automáticamente.`,
                'Forzar tema',
                { confirmButtonText: 'Forzar', cancelButtonText: 'Cancelar', type: 'warning' }
            ).then(() => {
                this.loading_force = true;
                this.$http.post('configurations/system-skins/force', { skin_id: this.selectedForceId }).then(response => {
                    this.loading_force = false;
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.skins = response.data.skins;
                    } else {
                        this.$message.error(response.data.message);
                    }
                }).catch(() => {
                    this.loading_force = false;
                    this.$message.error('Error al forzar el tema');
                });
            }).catch(() => {});
        },

        onFileChange(file) {
            if (!file) return;
            this.pendingFile = file.raw;
            this.$http.get('configurations/system-skins/check', { params: { filename: file.name } }).then(response => {
                if (response.data.exists) {
                    this.conflictFilename = file.name;
                    this.renameValue = response.data.suggested;
                    this.renameError = '';
                    this.showRenameDialog = true;
                } else {
                    this.submitFile();
                }
            });
        },
        onFileRemove() {
            this.pendingFile = null;
        },
        submitFile(renameTo = null) {
            this.loading_upload = true;
            const formData = new FormData();
            formData.append('file', this.pendingFile);
            if (renameTo) formData.append('rename_to', renameTo);

            this.$http.post('configurations/system-skins/upload', formData, {
                headers: { ...this.headers, 'Content-Type': 'multipart/form-data' },
            }).then(response => {
                this.loading_upload = false;
                const data = response.data;
                if (data.success) {
                    this.$message.success(data.message);
                    this.skins = data.skins;
                } else {
                    this.$message.error(data.message);
                }
                this.$refs.upload.clearFiles();
                this.pendingFile = null;
            }).catch(() => {
                this.loading_upload = false;
                this.$refs.upload.clearFiles();
                this.pendingFile = null;
                this.$message.error('Error al subir el archivo');
            });
        },
        confirmRename() {
            const name = this.renameValue.trim();
            if (!name) {
                this.renameError = 'El nombre no puede estar vacío.';
                return;
            }
            if (!/^[a-zA-Z0-9_\-]+$/.test(name)) {
                this.renameError = 'Solo letras, números, guiones y guiones bajos.';
                return;
            }
            this.renameError = '';
            this.showRenameDialog = false;
            this.submitFile(name);
        },
        onRenameDialogClose() {
            this.showRenameDialog = false;
            this.renameError = '';
            this.$refs.upload.clearFiles();
            this.pendingFile = null;
        },

        openReplaceDialog(skin) {
            this.replacingSkin = skin;
            this.pendingReplaceFile = null;
            this.showReplaceDialog = true;
        },
        onReplaceFileChange(file) {
            this.pendingReplaceFile = file ? file.raw : null;
        },
        onReplaceFileRemove() {
            this.pendingReplaceFile = null;
        },
        confirmReplace() {
            if (!this.pendingReplaceFile) return;
            this.loading_replace = true;
            const formData = new FormData();
            formData.append('file', this.pendingReplaceFile);
            formData.append('skin_id', this.replacingSkin.id);

            this.$http.post('configurations/system-skins/replace', formData, {
                headers: { ...this.headers, 'Content-Type': 'multipart/form-data' },
            }).then(response => {
                this.loading_replace = false;
                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.skins = response.data.skins;
                    this.showReplaceDialog = false;
                    this.$refs.uploadReplace.clearFiles();
                    this.pendingReplaceFile = null;
                    this.replacingSkin = null;
                } else {
                    this.$message.error(response.data.message);
                }
            }).catch(() => {
                this.loading_replace = false;
                this.$message.error('Error al reemplazar el tema');
            });
        },
        onReplaceDialogClose() {
            this.showReplaceDialog = false;
            this.pendingReplaceFile = null;
            this.replacingSkin = null;
            if (this.$refs.uploadReplace) {
                this.$refs.uploadReplace.clearFiles();
            }
        },

        confirmRevert(skin) {
            this.$confirm(
                `¿Restaurar "${skin.name}" al CSS original? Los tenants volverán a usar "${skin.filename}".`,
                'Restaurar original',
                { confirmButtonText: 'Restaurar', cancelButtonText: 'Cancelar', type: 'info' }
            ).then(() => {
                this.loading_revert = true;
                this.$http.post('configurations/system-skins/revert', { skin_id: skin.id }).then(response => {
                    this.loading_revert = false;
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.skins = response.data.skins;
                    } else {
                        this.$message.error(response.data.message);
                    }
                }).catch(() => {
                    this.loading_revert = false;
                    this.$message.error('Error al restaurar el tema');
                });
            }).catch(() => {});
        },

        // --- Reparar tema por defecto con estado inconsistente ---
        confirmSync(skin) {
            this.$confirm(
                `¿Forzar a todos los tenants a usar el archivo original "${skin.filename}" para el tema "${skin.name}"? Esto repara estados inconsistentes.`,
                'Reparar tema',
                { confirmButtonText: 'Reparar', cancelButtonText: 'Cancelar', type: 'warning' }
            ).then(() => {
                this.loading_sync = skin.id;
                this.$http.post('configurations/system-skins/sync', { skin_id: skin.id }).then(response => {
                    this.loading_sync = null;
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.skins = response.data.skins;
                    } else {
                        this.$message.error(response.data.message);
                    }
                }).catch(() => {
                    this.loading_sync = null;
                    this.$message.error('Error al reparar el tema');
                });
            }).catch(() => {});
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

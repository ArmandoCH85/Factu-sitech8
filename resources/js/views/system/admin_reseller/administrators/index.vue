<template>
    <div>
        <header class="page-header">
            <h2>
                <a href="/dashboard">
                    <i class="fas fa-user-shield"></i>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>Admin Reseller / Administradores</span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">
                <button class="btn btn-custom btn-sm mt-2 me-2 mb-3 primary-buton" type="button" @click="openCreate">
                    <i class="fa fa-plus-circle"></i> Nuevo Administrador
                </button>
            </div>
        </header>

        <div class="card">
            <div class="card-body mx-2">
                <div class="btn-filter-content mb-3 d-flex">
                    <el-button type="secondary" class="btn-show-filter" :class="{ shift: isFiltersVisible }" @click="toggleFilters">
                        {{ isFiltersVisible ? 'Ocultar filtros' : 'Mostrar filtros' }}
                    </el-button>
                    <el-button v-if="searchQuery" type="secondary" @click="clearFilters">Limpiar Filtros</el-button>
                </div>

                <div v-if="isFiltersVisible" class="filter-section mb-3">
                    <div class="row">
                        <div class="form-group col-lg-4 col-md-6 col-sm-12 mb-2">
                            <label class="control-label mb-1">Buscar:</label>
                            <el-input v-model="searchQuery" placeholder="Nombre o correo" prefix-icon="el-icon-search"></el-input>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in filteredRecords" :key="row.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ row.name }}</td>
                                <td>{{ row.email }}</td>
                                <td class="text-center">
                                    <el-switch v-model="row.status" @change="changeStatus(row)"></el-switch>
                                </td>
                                <td class="text-end">
                                    <el-button type="text" @click="openEdit(row)">Editar</el-button>
                                    <el-button type="text" class="text-danger" @click="remove(row)">Eliminar</el-button>
                                </td>
                            </tr>
                            <tr v-if="filteredRecords.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">No hay administradores registrados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <el-dialog :title="form.id ? 'Editar Administrador' : 'Nuevo Administrador'" :visible.sync="showDialog" width="780px">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group" :class="{ 'has-danger': errors.name }">
                        <label class="control-label">Nombre</label>
                        <el-input v-model="form.name"></el-input>
                        <small class="form-control-feedback" v-if="errors.name">{{ errors.name[0] }}</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" :class="{ 'has-danger': errors.email }">
                        <label class="control-label">Email</label>
                        <el-input v-model="form.email"></el-input>
                        <small class="form-control-feedback" v-if="errors.email">{{ errors.email[0] }}</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group" :class="{ 'has-danger': errors.password }">
                        <label class="control-label">{{ form.id ? 'Password (opcional)' : 'Password' }}</label>
                        <el-input v-model="form.password" show-password placeholder="En edición, dejar vacío para no cambiar"></el-input>
                        <small class="form-control-feedback" v-if="errors.password">{{ errors.password[0] }}</small>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12">
                    <h4 class="border-bottom pb-2 mb-3">Permisos de Módulos</h4>
                    <p class="text-muted small mb-2">Marque los módulos del panel a los que podrá acceder este administrador.</p>
                    <div :class="{ 'has-danger': errors.module_permissions }">
                        <el-checkbox-group v-model="form.module_permissions" class="row">
                            <div
                                v-for="opt in moduleOptions"
                                :key="opt.key"
                                class="col-md-6 center-el-checkbox mb-2">
                                <el-checkbox :label="opt.key">{{ opt.label }}</el-checkbox>
                            </div>
                        </el-checkbox-group>
                        <small class="form-control-feedback d-block" v-if="errors.module_permissions">{{ errors.module_permissions[0] }}</small>
                    </div>
                </div>
            </div>

            <span slot="footer" class="dialog-footer">
                <el-button @click="showDialog = false">Cancelar</el-button>
                <el-button type="primary" :loading="loadingSubmit" @click="submit">Guardar</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
export default {
    data() {
        return {
            resource: 'admin-reseller/administrators',
            records: [],
            showDialog: false,
            loadingSubmit: false,
            isFiltersVisible: true,
            searchQuery: '',
            errors: {},
            form: {},
            moduleOptions: [
                { key: 'payment-orders', label: 'Pagos' },
                { key: 'multi-users', label: 'Multi Usuarios' },
                { key: 'plans', label: 'Planes' },
                { key: 'massive-invoice', label: 'Facturación Masiva' },
                { key: 'accounting', label: 'Contabilidad' },
                { key: 'auto-update', label: 'Actualización' },
                { key: 'backup', label: 'Backup' },
                { key: 'information', label: 'Información' },
                { key: 'logs', label: 'Logs' },
                { key: 'reports', label: 'Reportes' },
            ],
        };
    },
    computed: {
        filteredRecords() {
            if (!this.searchQuery) return this.records;
            const query = this.searchQuery.toLowerCase();
            return this.records.filter((row) =>
                (row.name || '').toLowerCase().includes(query) ||
                (row.email || '').toLowerCase().includes(query)
            );
        },
    },
    created() {
        this.initForm();
        this.getData();
    },
    methods: {
        initForm() {
            this.errors = {};
            this.form = {
                id: null,
                name: null,
                email: null,
                password: null,
                status: true,
                module_permissions: [],
            };
        },
        toggleFilters() {
            this.isFiltersVisible = !this.isFiltersVisible;
        },
        clearFilters() {
            this.searchQuery = '';
        },
        normalizePermissions(item) {
            const p = item.module_permissions;
            if (Array.isArray(p)) {
                return [...p];
            }
            if (p && typeof p === 'object') {
                return Object.values(p);
            }
            return [];
        },
        getData() {
            this.$http.get(`/${this.resource}/records`).then((response) => {
                this.records = (response.data.data || []).map((item) => ({
                    ...item,
                    status: !!item.status,
                    module_permissions: this.normalizePermissions(item),
                }));
            });
        },
        openCreate() {
            this.initForm();
            this.showDialog = true;
        },
        openEdit(row) {
            this.initForm();
            this.form = {
                id: row.id,
                name: row.name,
                email: row.email,
                password: null,
                status: !!row.status,
                module_permissions: this.normalizePermissions(row),
            };
            this.showDialog = true;
        },
        submit() {
            this.loadingSubmit = true;
            this.errors = {};

            const payload = {
                name: this.form.name,
                email: this.form.email,
                password: this.form.password || undefined,
                status: this.form.status,
                module_permissions: this.form.module_permissions || [],
            };

            const request = this.form.id
                ? this.$http.put(`/${this.resource}/${this.form.id}`, payload)
                : this.$http.post(`/${this.resource}`, payload);

            request
                .then((response) => {
                    this.$message.success(response.data.message);
                    this.showDialog = false;
                    this.getData();
                })
                .catch((error) => {
                    if (error.response && error.response.status === 422) {
                        this.errors = error.response.data;
                        return;
                    }
                    this.$message.error('No se pudo guardar el registro.');
                })
                .finally(() => {
                    this.loadingSubmit = false;
                });
        },
        changeStatus(row) {
            this.$http
                .put(`/${this.resource}/${row.id}`, {
                    name: row.name,
                    email: row.email,
                    status: row.status,
                    module_permissions: row.module_permissions || [],
                })
                .then((response) => {
                    this.$message.success(response.data.message);
                })
                .catch(() => {
                    row.status = !row.status;
                    this.$message.error('No se pudo actualizar el estado.');
                });
        },
        remove(row) {
            this.$confirm(`Se eliminará el administrador ${row.name}.`, 'Confirmación', {
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                type: 'warning',
            })
                .then(() => this.$http.delete(`/${this.resource}/${row.id}`))
                .then((response) => {
                    this.$message.success(response.data.message);
                    this.getData();
                })
                .catch(() => {});
        },
    },
};
</script>

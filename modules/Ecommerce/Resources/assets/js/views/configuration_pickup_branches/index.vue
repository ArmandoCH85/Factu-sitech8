<template>
  <div>
    <!-- Botón sincronizar con establecimientos -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <small class="text-muted">
        Gestiona las sucursales donde los clientes pueden recoger sus pedidos.
        Puedes sincronizarlas desde los establecimientos del sistema o crearlas manualmente.
      </small>
      <el-button
        type="default"
        size="small"
        :loading="syncing"
        @click="syncFromEstablishments"
        class="ms-3 text-nowrap"
      >
        <i class="fa fa-refresh"></i> Sincronizar establecimientos
      </el-button>
    </div>

    <!-- Tabla de sucursales existentes -->
    <div class="table-responsive mb-3">
      <table class="table table-sm">
        <thead>
          <tr>
            <th class="text-center" style="width: 70px;">Activo</th>
            <th>Sucursal</th>
            <th>Dirección</th>
            <th class="text-end" style="width: 80px;">Opciones</th>
          </tr>
        </thead>
        <tbody v-if="loading">
          <tr>
            <td colspan="4" class="text-center py-3">
              <i class="fa fa-spinner fa-spin"></i> Cargando...
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="branches.length === 0">
          <tr>
            <td colspan="4" class="text-center py-3 text-muted">
              No hay sucursales registradas. Sincroniza los establecimientos o agrega una manualmente.
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr v-for="branch in branches" :key="branch.id">
            <td class="text-center">
              <el-switch
                :value="branch.active"
                @change="toggleActive(branch)"
              ></el-switch>
            </td>
            <td>{{ branch.name }}</td>
            <td class="text-muted">{{ branch.address || '—' }}</td>
            <td class="text-end">
              <el-button
                type="danger"
                size="mini"
                icon="el-icon-delete"
                plain
                @click="deleteBranch(branch)"
              ></el-button>
            </td>
          </tr>
        </tbody>

        <!-- Fila de nueva sucursal -->
        <tfoot>
          <tr>
            <td class="text-center">
              <el-switch v-model="newRow.active"></el-switch>
            </td>
            <td>
              <el-input
                v-model="newRow.name"
                placeholder="Nombre de la sucursal"
                size="small"
              ></el-input>
            </td>
            <td>
              <el-input
                v-model="newRow.address"
                placeholder="Dirección (opcional)"
                size="small"
              ></el-input>
            </td>
            <td class="text-end">
              <el-button
                type="primary"
                size="small"
                :loading="saving"
                @click="saveBranch"
              >
                <i class="fa fa-plus"></i> Agregar
              </el-button>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PickupBranches',
  data() {
    return {
      branches: [],
      loading: false,
      saving: false,
      syncing: false,
      newRow: {
        name: '',
        address: '',
        active: true,
      },
    };
  },
  created() {
    this.fetchRecords();
  },
  methods: {
    async fetchRecords() {
      this.loading = true;
      try {
        const res = await this.$http.get('/ecommerce/pickup-branches/records');
        this.branches = res.data.data || [];
      } catch (e) {
        this.$message.error('Error al cargar las sucursales.');
      } finally {
        this.loading = false;
      }
    },

    async saveBranch() {
      if (!this.newRow.name.trim()) {
        this.$message.warning('El nombre de la sucursal es obligatorio.');
        return;
      }
      this.saving = true;
      try {
        const res = await this.$http.post('/ecommerce/pickup-branches', {
          name: this.newRow.name.trim(),
          address: this.newRow.address.trim() || null,
        });
        if (res.data.success) {
          this.branches.push(res.data.data);
          this.newRow = { name: '', address: '', active: true };
          this.$message.success(res.data.message || 'Sucursal creada.');
        }
      } catch (e) {
        this.$message.error('Error al guardar la sucursal.');
      } finally {
        this.saving = false;
      }
    },

    async toggleActive(branch) {
      try {
        const res = await this.$http.post(`/ecommerce/pickup-branches/${branch.id}/status`, {
          active: !branch.active,
        });
        if (res.data.success) {
          branch.active = res.data.data.active;
        }
      } catch (e) {
        this.$message.error('Error al actualizar el estado.');
      }
    },

    async deleteBranch(branch) {
      try {
        await this.$confirm(`¿Eliminar la sucursal "${branch.name}"?`, 'Confirmar', {
          confirmButtonText: 'Eliminar',
          cancelButtonText: 'Cancelar',
          type: 'warning',
        });
      } catch {
        return; // El usuario canceló
      }

      try {
        const res = await this.$http.delete(`/ecommerce/pickup-branches/${branch.id}`);
        if (res.data.success) {
          this.branches = this.branches.filter(b => b.id !== branch.id);
          this.$message.success(res.data.message || 'Sucursal eliminada.');
        }
      } catch (e) {
        this.$message.error('Error al eliminar la sucursal.');
      }
    },

    async syncFromEstablishments() {
      this.syncing = true;
      try {
        const res = await this.$http.post('/ecommerce/pickup-branches/sync');
        if (res.data.success) {
          this.$message.success(res.data.message || 'Sincronización completada.');
          await this.fetchRecords();
        }
      } catch (e) {
        this.$message.error('Error al sincronizar establecimientos.');
      } finally {
        this.syncing = false;
      }
    },
  },
};
</script>

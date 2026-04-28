<template>
  <div>
    <!-- Encabezado y switch principal -->
    <div class="row mb-4">
      <div class="col-md-12">
        <h5><b>Integración con BuhoPrinter:</b></h5>
        <span class="text-muted">
          Activa esta opción para conectar la empresa con el servicio de impresión local BuhoPrinter.
          Al activar, el sistema detectará automáticamente el agente en esta red e intentará registrar
          las impresoras disponibles.
        </span>
      </div>
    </div>

    <div class="row align-items-end mb-4" v-loading="loading">
      <!-- Switch de activación -->
      <div class="col-md-6">
        <label class="control-label d-block">Activar impresión con BuhoPrinter</label>
        <el-switch
          v-model="form.printer_enabled"
          active-text="Activo"
          inactive-text="Inactivo"
          @change="onTogglePrinterEnabled">
        </el-switch>
      </div>

      <!-- Switch de impresión local -->
      <div class="col-md-6" v-if="form.printer_enabled">
        <label class="control-label d-block">
          Impresión local
          <el-tooltip
            content="Al activar, solo se podrán enviar órdenes de impresión desde la misma red pública donde está instalado BuhoPrinter."
            effect="dark"
            placement="top">
            <i class="fa fa-info-circle text-muted ml-1"></i>
          </el-tooltip>
        </label>
        <el-switch
          v-model="form.print_local_enabled"
          active-text="Activo"
          inactive-text="Inactivo">
        </el-switch>
      </div>
    </div>

    <!-- Indicador de estado (solo informativo) -->
    <div class="row mb-4" v-if="form.printer_enabled">
      <div class="col-md-12">
        <div class="alert" :class="statusAlertClass" role="alert">
          <i :class="statusIconClass" class="me-2"></i>
          <b>Estado de última verificación:</b>
          <span v-if="checking"> Verificando conexión con BuhoPrinter...</span>
          <span v-else-if="liveStatus === 'connected'"> BuhoPrinter respondió correctamente en la última verificación.</span>
          <span v-else-if="liveStatus === 'disconnected'"> No se pudo contactar a BuhoPrinter en la última verificación.</span>
          <span v-else-if="form.printer_status === 'connected'"> Última verificación guardada: conexión exitosa.</span>
          <span v-else-if="form.printer_status === 'disconnected'"> Última verificación guardada: sin conexión.</span>
          <span v-else> Sin verificación previa registrada.</span>
          <br>
          <small class="text-muted">
            Este indicador refleja el resultado de la última vez que se verificó la conexión desde este panel,
            no el estado en tiempo real del servicio de impresión.
          </small>
        </div>
      </div>
    </div>

    <!-- Lista de impresoras registradas -->
    <template v-if="form.printer_enabled && printers.length > 0">
      <div class="row mt-3">
        <div class="col-md-12">
          <h6 class="text-muted mb-2">
            <i class="fa fa-print mr-1"></i>
            Impresoras registradas ({{ printers.length }})
          </h6>
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Predeterminada</th>
                  <th>Última sincronización</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in printers" :key="p.name">
                  <td>{{ p.name }}</td>
                  <td>
                    <el-tag v-if="p.is_default" type="success" size="mini">Sí</el-tag>
                    <span v-else class="text-muted">—</span>
                  </td>
                  <td class="text-muted">{{ p.last_seen_at | formatDate }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>

    <!-- Mensaje cuando no hay impresoras aún -->
    <div class="row" v-else-if="form.printer_enabled && printers.length === 0 && !checking">
      <div class="col-md-12">
        <div class="alert alert-info">
          <i class="fa fa-info-circle mr-1"></i>
          No hay impresoras registradas. Activa la opción y usa
          <b>"Verificar y actualizar"</b> para detectar las impresoras disponibles.
        </div>
      </div>
    </div>

    <!-- Botones de acción -->
    <div class="row mt-4">
      <div class="col-md-12">
        <el-button
          type="success"
          :loading="saving"
          :disabled="loading"
          @click="saveConfig">Guardar configuración</el-button>
        <el-button
          type="primary"
          class="ms-2"
          :loading="checking"
          :disabled="!form.printer_enabled || loading"
          @click="checkAndSync">Verificar y actualizar</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import { buhoprinter } from '@mixins/buhoprinter'

export default {
  name: 'PrintConfig',

  mixins: [buhoprinter],

  filters: {
    formatDate(value) {
      if (!value) return '—'
      return value.toString().replace('T', ' ').substring(0, 16)
    }
  },

  data() {
    return {
      resource: 'restaurant',
      form: {
        printer_enabled:         false,
        printer_status:          null,
        printer_public_ip:       null,
        print_local_enabled:     false,
      },
      printers: [],
      liveStatus: null,
      checking: false,
      saving: false,
      loading: false,
    }
  },

  computed: {
    statusAlertClass() {
      const status = this.liveStatus || this.form.printer_status
      if (status === 'connected')    return 'alert-success'
      if (status === 'disconnected') return 'alert-danger'
      return 'alert-secondary'
    },

    statusIconClass() {
      const status = this.liveStatus || this.form.printer_status
      if (status === 'connected')    return 'fa fa-check-circle text-success'
      if (status === 'disconnected') return 'fa fa-times-circle text-danger'
      return 'fa fa-question-circle text-muted'
    },
  },

  mounted() {
    this.loadConfig()
  },

  methods: {
    async loadConfig() {
      this.loading = true

      try {
        const { data } = await this.$http.get(`/${this.resource}/printers/config`)
        if (data.success) {
          const d = data.data
          this.form.printer_enabled     = d.printer_enabled
          this.form.printer_status      = d.printer_status
          this.form.printer_public_ip   = d.printer_public_ip
          this.form.print_local_enabled = d.print_local_enabled
          this.printers                 = d.printers || []
        }
      } catch (error) {
        console.error('Error al cargar config de impresión:', error)
      } finally {
        this.loading = false
      }
    },

    async onTogglePrinterEnabled(value) {
      if (value) {
        await this.checkAndSync()
      } else {
        await this.saveConfig(false)
      }
    },

    async checkAndSync(silent = false) {
      this.checking = true
      this.liveStatus = null

      try {
        await this.startConnectionBuho()

        if (!this.isBuhoActive) {
          throw new Error('BuhoPrinter no está disponible en esta red')
        }

        this.liveStatus = 'connected'

        const clientPublicIp = await this.fetchClientPublicIp()
        await this.$http.post(`/${this.resource}/printers/status`, {
          printer_status:    'connected',
          printer_public_ip: clientPublicIp || null,
        })
        this.form.printer_status    = 'connected'
        this.form.printer_public_ip = clientPublicIp || null

        const buhoUrl = this.getBuhoBaseUrl()
        const configRes = await this.$http.post(`/${this.resource}/printers/configure`, {
          buhoprinter_host: buhoUrl,
        })

        if (configRes.data.success) {
          await fetch(`${buhoUrl}/configure`, {
            method : 'POST',
            headers: { 'Content-Type': 'text/plain' },
            body   : configRes.data.payload,
          })
        }

        const rawPrinters = await this.getBuhoPrintersWithDefaults()

        const syncRes = await this.$http.post(`/${this.resource}/printers/sync`, {
          printers: rawPrinters
        })

        if (syncRes.data.success) {
          this.printers = syncRes.data.printers
          if (!silent) {
            this.$message.success('BuhoPrinter conectado. Impresoras actualizadas.')
          }
        }

      } catch (error) {
        this.liveStatus = 'disconnected'

        await this.$http.post(`/${this.resource}/printers/status`, { printer_status: 'disconnected' })
          .catch(() => {})
        this.form.printer_status = 'disconnected'

        if (!silent) {
          this.$message.error('No se pudo conectar con BuhoPrinter. Verifica que el servicio esté corriendo en esta PC.')
        }
        console.error('Error al verificar BuhoPrinter:', error)
      } finally {
        this.checking = false
      }
    },

    async saveConfig(showMessage = true) {
      this.saving = true
      try {
        const res = await this.$http.post(`/${this.resource}/printers/config`, {
          printer_enabled:     this.form.printer_enabled,
          print_local_enabled: this.form.print_local_enabled,
        })

        if (res.data.success && showMessage) {
          this.$message.success(res.data.message)
        }
      } catch (error) {
        this.$message.error('Error al guardar la configuración de impresión.')
        console.error(error)
      } finally {
        this.saving = false
      }
    },
  }
}
</script>

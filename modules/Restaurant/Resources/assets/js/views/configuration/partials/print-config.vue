<template>
  <div>
    <!-- Encabezado y switch principal -->
    <div class="row mb-4">
      <div class="col-md-12">
        <h5><b>Integración con BuhoPrinter:</b></h5>
        <span class="text-muted">
          Activa esta opción para conectar el restaurante con el servicio de impresión local BuhoPrinter.
          Al activar, el sistema detectará automáticamente el agente en esta red e intentará registrar
          las impresoras disponibles.
        </span>
      </div>
    </div>

    <div class="row align-items-end mb-4">
      <!-- Switch de activación -->
      <div class="col-md-4">
        <label class="control-label d-block">Activar impresión con BuhoPrinter</label>
        <el-switch
          v-model="form.printer_enabled"
          active-text="Activo"
          inactive-text="Inactivo"
          @change="onTogglePrinterEnabled">
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

    <!-- Configuración de impresoras por propósito (solo si hay impresoras sincronizadas) -->
    <template v-if="form.printer_enabled && printers.length > 0">
      <div class="row mb-3">
        <div class="col-md-12">
          <h5><b>Asignación de impresoras:</b></h5>
          <span class="text-muted">Selecciona qué impresora se usará para cada tipo de salida.</span>
        </div>
      </div>

      <div class="row">
        <!-- Impresora para Comanda -->
        <div class="col-md-4">
          <div class="form-group">
            <label class="control-label">
              Impresora - Comanda
              <el-tooltip
                content="Impresora donde se imprimirán las comandas enviadas a los mozos"
                effect="dark"
                placement="top">
                <i class="fa fa-info-circle text-muted ml-1"></i>
              </el-tooltip>
            </label>
            <el-select
              v-model="form.printer_name_comanda"
              clearable
              placeholder="Seleccionar impresora"
              class="w-100">
              <el-option
                v-for="p in printers"
                :key="p.name"
                :label="p.name + (p.is_default ? ' (predeterminada)' : '')"
                :value="p.name">
              </el-option>
            </el-select>
          </div>
        </div>

        <!-- Impresora para Documents -->
        <div class="col-md-4">
          <div class="form-group">
            <label class="control-label">
              Impresora - Documents
              <el-tooltip
                content="Impresora asignada para impresión de documentos"
                effect="dark"
                placement="top">
                <i class="fa fa-info-circle text-muted ml-1"></i>
              </el-tooltip>
            </label>
            <el-select
              v-model="form.printer_name_documents"
              clearable
              placeholder="Seleccionar impresora"
              class="w-100">
              <el-option
                v-for="p in printers"
                :key="p.name"
                :label="p.name + (p.is_default ? ' (predeterminada)' : '')"
                :value="p.name">
              </el-option>
            </el-select>
          </div>
        </div>

        <!-- Impresora para Pre-cuenta -->
        <div class="col-md-4">
          <div class="form-group">
            <label class="control-label">
              Impresora - Precuenta
              <el-tooltip
                content="Impresora donde se imprimirá la pre-cuenta para el cliente"
                effect="dark"
                placement="top">
                <i class="fa fa-info-circle text-muted ml-1"></i>
              </el-tooltip>
            </label>
            <el-select
              v-model="form.printer_name_precuenta"
              clearable
              placeholder="Seleccionar impresora"
              class="w-100">
              <el-option
                v-for="p in printers"
                :key="p.name"
                :label="p.name + (p.is_default ? ' (predeterminada)' : '')"
                :value="p.name">
              </el-option>
            </el-select>
          </div>
        </div>
      </div>

      <!-- Lista de impresoras registradas -->
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
        <el-button type="success" :loading="saving" @click="saveConfig">Guardar configuración</el-button>
        <el-button
          type="primary"
          class="ms-2"
          :loading="checking"
          :disabled="!form.printer_enabled"
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
      // muestra solo fecha y hora sin zona horaria
      return value.toString().replace('T', ' ').substring(0, 16)
    }
  },

  data() {
    return {
      resource: 'restaurant',
      // estado del formulario de configuración
      form: {
        printer_enabled:         false,
        printer_status:          null,
        printer_name_comanda:    null,
        printer_name_documents:  null,
        printer_name_precuenta:  null,
      },
      // impresoras registradas en BD
      printers: [],
      // estado en vivo de la última verificación de esta sesión (no persiste)
      liveStatus: null,
      checking: false,
      saving: false,
    }
  },

  computed: {
    /**
     * Clase de alerta según el estado en vivo o el último guardado en BD.
     */
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
    /**
     * Carga la configuración de impresión desde el backend.
     * Si la impresión ya está activa, también intenta verificar el estado de BuhoPrinter.
     */
    async loadConfig() {
      try {
        const { data } = await this.$http.get(`/${this.resource}/printers/config`)
        if (data.success) {
          const d = data.data
          this.form.printer_enabled        = d.printer_enabled
          this.form.printer_status         = d.printer_status
          this.form.printer_name_comanda   = d.printer_name_comanda
          this.form.printer_name_documents  = d.printer_name_documents
          this.form.printer_name_precuenta  = d.printer_name_precuenta
          this.printers                    = d.printers || []

          // si ya está activo al entrar al tab, actualiza la lista de impresoras en background (silencioso)
          if (this.form.printer_enabled) {
            this.checkAndSync(true)
          }
        }
      } catch (error) {
        console.error('Error al cargar config de impresión:', error)
      }
    },

    /**
     * Maneja el cambio del switch principal.
     * Al activar: inicia verificación y sincronización si hay host configurado.
     * Al desactivar: guarda el estado desactivado sin hacer llamadas a BuhoPrinter.
     */
    async onTogglePrinterEnabled(value) {
      if (value) {
        await this.checkAndSync()
      } else {
        // desactivar: guardar directamente
        await this.saveConfig(false)
      }
    },

    /**
     * Verifica la conexión con BuhoPrinter usando el mixin (auto-detección de puerto en localhost)
     * y sincroniza la lista de impresoras con el backend.
     *
     * @param {boolean} silent - si es true, no muestra notificaciones de éxito/error al usuario
     */
    async checkAndSync(silent = false) {
      this.checking = true
      this.liveStatus = null

      try {
        // 1. Delegar la conexión al mixin — escanea puertos 8181-8484 en localhost
        await this.startConnectionBuho()

        if (!this.isBuhoActive) {
          throw new Error('BuhoPrinter no está disponible en esta red')
        }

        this.liveStatus = 'connected'

        // 2. Registrar estado exitoso en BD (solo informativo)
        await this.$http.post(`/${this.resource}/printers/status`, { printer_status: 'connected' })
        this.form.printer_status = 'connected'

        // 3. Enviar el host descubierto al backend para que persista printer_host y devuelva
        //    el payload de configuración con datos sensibles (Redis, fqdn, api_token).
        //    El frontend reenvía ese payload directamente a BuhoPrinter /configure porque
        //    el servidor VPS no puede alcanzar localhost del cliente.
        const buhoUrl = this.getBuhoBaseUrl()
        const configRes = await this.$http.post(`/${this.resource}/printers/configure`, {
          buhoprinter_host: buhoUrl,
        })

        if (configRes.data.success) {
          // El payload es un blob cifrado (string opaco). Se envía como texto plano —
          // BuhoPrinter espera recibir la cadena cifrada directamente, no un JSON wrapper.
          await fetch(`${buhoUrl}/configure`, {
            method : 'POST',
            headers: { 'Content-Type': 'text/plain' },
            body   : configRes.data.payload,
          })
        }

        // 4. Obtener impresoras con su campo is_default desde BuhoPrinter
        const rawPrinters = await this.getBuhoPrintersWithDefaults()

        // 5. Sincronizar con el backend — se envían objetos {name, is_default}
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

        // Registrar fallo en BD (solo informativo)
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

    /**
     * Persiste la configuración de impresión en el backend.
     * @param {boolean} showMessage - mostrar o no el mensaje de éxito
     */
    async saveConfig(showMessage = true) {
      this.saving = true
      try {
        const res = await this.$http.post(`/${this.resource}/printers/config`, {
          printer_enabled:         this.form.printer_enabled,
          printer_name_comanda:   this.form.printer_name_comanda,
          printer_name_documents:  this.form.printer_name_documents,
          printer_name_precuenta:  this.form.printer_name_precuenta,
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

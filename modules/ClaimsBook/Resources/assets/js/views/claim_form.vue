<template>
  <div class="cf-wrapper" :class="{ 'cf-embedded': embedded }">
    <!-- Encabezado del formulario -->
    <div class="cf-header">
      <h3 class="cf-title">Libro de Reclamaciones</h3>
      <p class="cf-subtitle">
        Conforme a lo establecido en el Código de Protección y Defensa del Consumidor
        (Ley N° 29571), usted tiene derecho a presentar su queja o reclamo.
      </p>
    </div>

    <p class="cf-step-hint">
      Si ya presentó un reclamo y desea realizar un seguimiento o presentar una nueva acción relacionada, ingrese el
      código anterior.
    </p>
    <div class="cf-field">
      <label>Código de reclamo previo (opcional)</label>
      <div class="cf-inline">
        <el-input v-model="form.previous_code" placeholder="Ej: R032500-0001-00" size="small"
          :disabled="lookingUp"></el-input>
        <el-button size="small" type="primary" :loading="lookingUp" @click="lookupPreviousCode">Verificar</el-button>
      </div>
    </div>

    <!-- Indicador de pasos -->
    <el-steps :active="activeStep" finish-status="success" simple class="cf-steps">
      <el-step v-if="previousClaim" title="Reclamo previo"></el-step>
      <el-step title="Datos"></el-step>
      <el-step title="Bien contratado"></el-step>
      <el-step title="Detalle"></el-step>
    </el-steps>

    <!-- ──────────── Paso 0: Código de reclamo previo ──────────── -->
    <div v-if="currentStep === 0" class="cf-step-content">
      <div class="cf-field">
        <div v-if="previousClaim" class="cf-previous-info">
          <el-alert
            :title="`Reclamo anterior: ${previousClaim.code} — Estado: ${previousClaim.status ? previousClaim.status.description : 'Sin estado'}`"
            type="info" :closable="false" show-icon></el-alert>
          <div v-if="previousClaim.is_closed" class="cf-closed-warning">
            <el-alert title="Este reclamo ya fue cerrado con resolución." type="warning" :closable="false"
              show-icon></el-alert>
            <br></br>
            <el-card class="box-card">
              Resolución: <small>{{previousClaim.resolution ? previousClaim.resolution : 'Sin resolución'}}</small>
            </el-card>
          </div>
        </div>
      </div>
      <div class="cf-step-actions">
        <el-button size="small" type="primary" @click="goNext">
          Continuar
        </el-button>
      </div>
    </div>

    <!-- ──────────── Paso 1: Datos del reclamante ──────────── -->
    <div v-if="currentStep === 1" class="cf-step-content">
      <el-form ref="formStep1" :model="form" :rules="rulesStep1" label-position="top" size="small">
        <el-row :gutter="16">
          <el-col :span="12" :xs="24">
            <el-form-item label="Tipo de documento" prop="identity_document_type">
              <el-select v-model="form.identity_document_type" placeholder="Seleccione" style="width:100%">
                <el-option v-for="item in tables.identity_document_types" :key="item.id" :label="item.description"
                  :value="item.id"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12" :xs="24">
            <el-form-item label="Número de documento" prop="identity_document_number">
              <el-input v-model="form.identity_document_number" placeholder="12345678"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Nombre completo" prop="name">
              <el-input v-model="form.name" placeholder="Nombres y apellidos"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Email" prop="email">
              <el-input v-model="form.email" placeholder="correo@ejemplo.com" type="email"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12" :xs="24">
            <el-form-item label="Teléfono">
              <el-input v-model="form.phone" placeholder="987654321"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Departamento / Provincia / Distrito">
              <el-cascader v-model="form.location_cascade" :options="tables.locations"
                :props="{ expandTrigger: 'hover', value: 'value', label: 'label', children: 'children' }"
                placeholder="Seleccione su ubicación" filterable clearable style="width:100%"></el-cascader>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Dirección">
              <el-input v-model="form.address" placeholder="Av. Ejemplo 123..."></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div class="cf-step-actions">
        <el-button size="small" @click="goPrev">Atrás</el-button>
        <el-button size="small" type="primary" @click="validateAndNext('formStep1')">Continuar</el-button>
      </div>
    </div>

    <!-- ──────────── Paso 2: Bien contratado ──────────── -->
    <div v-if="currentStep === 2" class="cf-step-content">
      <el-form ref="formStep2" :model="form" :rules="rulesStep2" label-position="top" size="small">
        <el-row :gutter="16">
          <el-col :span="12" :xs="24">
            <el-form-item label="Tipo de bien" prop="asset_type">
              <el-select v-model="form.asset_type" placeholder="Seleccione" style="width:100%">
                <el-option value="producto" label="Producto"></el-option>
                <el-option value="servicio" label="Servicio"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12" :xs="24">
            <el-form-item label="Fecha de contratación" prop="asset_date">
              <el-date-picker v-model="form.asset_date" type="date" placeholder="Seleccione fecha"
                value-format="yyyy-MM-dd" style="width:100%"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Descripción del bien o servicio" prop="asset_description">
              <el-input v-model="form.asset_description" type="textarea" :rows="3"
                placeholder="Describa el producto o servicio contratado..."></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item>
              <el-switch v-model="form.has_receipt" active-text="Cuenta con comprobante de pago"></el-switch>
            </el-form-item>
          </el-col>

          <!-- Campos de comprobante condicionales -->
          <template v-if="form.has_receipt">
            <el-col :span="8" :xs="24">
              <el-form-item label="Monto reclamado" prop="receipt_amount">
                <el-input v-model="form.receipt_amount" type="number" min="0" placeholder="0.00"></el-input>
              </el-form-item>
            </el-col>
            <el-col :span="8" :xs="24">
              <el-form-item label="Moneda">
                <el-select v-model="form.receipt_currency" style="width:100%">
                  <el-option value="PEN" label="Soles (PEN)"></el-option>
                  <el-option value="USD" label="Dólares (USD)"></el-option>
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="8" :xs="24">
              <el-form-item label="Tipo de documento">
                <el-select v-model="form.receipt_document_type" placeholder="Seleccione" style="width:100%">
                  <el-option value="01" label="Factura"></el-option>
                  <el-option value="03" label="Boleta de venta"></el-option>
                  <el-option value="07" label="Nota de crédito"></el-option>
                  <el-option value="08" label="Nota de débito"></el-option>
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12" :xs="24">
              <el-form-item label="Serie">
                <el-input v-model="form.receipt_series" placeholder="B001"></el-input>
              </el-form-item>
            </el-col>
            <el-col :span="12" :xs="24">
              <el-form-item label="N° de comprobante">
                <el-input v-model="form.receipt_number" placeholder="00000001"></el-input>
              </el-form-item>
            </el-col>
          </template>
        </el-row>
      </el-form>
      <div class="cf-step-actions">
        <el-button size="small" @click="goPrev">Atrás</el-button>
        <el-button size="small" type="primary" @click="validateAndNext('formStep2')">Continuar</el-button>
      </div>
    </div>

    <!-- ──────────── Paso 3: Detalle del reclamo ──────────── -->
    <div v-if="currentStep === 3" class="cf-step-content">
      <el-form ref="formStep3" :model="form" :rules="rulesStep3" label-position="top" size="small">
        <el-row :gutter="16">
          <el-col :span="12" :xs="24">
            <el-form-item label="Tipo de registro" prop="claim_type">
              <el-select v-model="form.claim_type" placeholder="Seleccione" style="width:100%">
                <el-option value="queja" label="Queja - Insatisfacción sin afectación económica"></el-option>
                <el-option value="reclamo" label="Reclamo - Insatisfacción con afectación económica"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12" :xs="24">
            <el-form-item label="Canal de atención">
              <el-select v-model="form.channel" placeholder="Seleccione un canal" clearable style="width:100%">
                <el-option v-for="ch in tables.claim_channels" :key="ch.id" :label="ch.name"
                  :value="ch.name"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Detalle de la queja o reclamo" prop="detail">
              <el-input v-model="form.detail" type="textarea" :rows="4"
                placeholder="Describa con detalle el motivo de su queja o reclamo..."></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Pedido o resultado esperado" prop="expected_result">
              <el-input v-model="form.expected_result" type="textarea" :rows="3"
                placeholder="¿Qué solución espera recibir?"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Adjunto (opcional, máx. 1 MB: jpg, jpeg, png, pdf)">
              <el-upload ref="uploader" action="#" :http-request="() => { }" :on-change="onFileChange"
                :on-remove="onFileRemove" :file-list="fileList" :limit="1" :auto-upload="false"
                accept=".jpg,.jpeg,.png,.pdf">
                <el-button size="small" icon="el-icon-paperclip">Adjuntar archivo</el-button>
              </el-upload>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item prop="terms_accepted">
              <el-checkbox v-model="form.terms_accepted">
                Acepto que los datos brindados son verídicos y me responsabilizo
                de la información consignada en esta declaración.
              </el-checkbox>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div class="cf-step-actions">
        <el-button size="small" @click="goPrev">Atrás</el-button>
        <el-button size="small" type="primary" :loading="submitting" @click="submit">
          Enviar reclamo
        </el-button>
      </div>
    </div>

    <!-- ──────────── Modal de resultado (éxito / error) ──────────── -->
    <claim-result-dialog
      :visible.sync="showResultDialog"
      :success="resultSuccess"
      :code="submittedCode"
      :error-msg="resultErrorMsg"
      @closed="reset"
    ></claim-result-dialog>
  </div>
</template>

<style scoped>
.cf-wrapper {
  max-width: 700px;
  margin: 0 auto;
  padding: 24px;
  font-family: inherit;
}

.cf-embedded {
  padding: 16px;
}

.cf-header {
  margin-bottom: 20px;
  text-align: center;
}

.cf-title {
  font-size: 20px;
  font-weight: 700;
  color: #303133;
  margin: 0 0 6px;
}

.cf-subtitle {
  font-size: 12px;
  color: #909399;
  margin: 0;
  line-height: 1.5;
}

.cf-steps {
  margin-bottom: 24px;
}

.cf-steps .el-step__title {
  font-size: 12px;
  line-height: 1.3;
  white-space: normal;
  word-break: break-word;
  overflow-wrap: break-word;
}

.cf-steps .el-step__main {
  overflow: visible;
}

.cf-step-content {
  padding: 8px 0;
}

.cf-step-hint {
  font-size: 13px;
  color: #606266;
  margin-bottom: 16px;
}

.cf-field {
  margin-bottom: 16px;
}

.cf-field>label {
  display: block;
  font-size: 13px;
  color: #606266;
  margin-bottom: 6px;
}

.cf-inline {
  display: flex;
  gap: 8px;
  align-items: center;
}

.cf-previous-info {
  margin-top: 10px;
}

.cf-closed-warning {
  margin-top: 8px;
}

.cf-step-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #ebeef5;
}
</style>

<script>
import ClaimResultDialog from './partials/ClaimResultDialog.vue'

export default {
  components: { ClaimResultDialog },

  props: {
    // Modo embebido para el widget (iframe en sitio externo)
    embedded: {
      type: Boolean,
      default: false
    },
    // Slug del tenant — requerido cuando embedded = true
    tenantSlug: {
      type: String,
      default: ''
    }
  },

  data() {
    return {
      currentStep: 1,
      submitting: false,
      // Control del modal de resultado
      showResultDialog: false,
      resultSuccess: false,
      submittedCode: '',
      resultErrorMsg: '',
      lookingUp: false,
      previousClaim: null,
      fileList: [],
      attachmentFile: null,

      // Tablas auxiliares
      tables: {
        identity_document_types: [],
        claim_channels: [],
        locations: [],
      },

      // Datos del formulario (todos los pasos)
      form: {
        previous_code: '',
        // Paso 1
        identity_document_type: '',
        identity_document_number: '',
        name: '',
        email: '',
        phone: '',
        location_cascade: [],
        address: '',
        // Paso 2
        asset_type: '',
        asset_description: '',
        asset_date: '',
        has_receipt: false,
        receipt_amount: '',
        receipt_currency: 'PEN',
        receipt_document_type: '03',
        receipt_series: '',
        receipt_number: '',
        // Paso 3
        claim_type: '',
        detail: '',
        expected_result: '',
        channel: '',
        terms_accepted: false,
      },

      // Reglas de validación por paso
      rulesStep1: {
        identity_document_type: [{ required: true, message: 'Seleccione un tipo de documento', trigger: 'change' }],
        identity_document_number: [{ required: true, message: 'Ingrese el número de documento', trigger: 'blur' }],
        name: [{ required: true, message: 'Ingrese su nombre completo', trigger: 'blur' }],
        email: [
          { required: true, message: 'Ingrese su email', trigger: 'blur' },
          { type: 'email', message: 'Formato de email inválido', trigger: 'blur' },
        ],
      },
      rulesStep2: {
        asset_type: [{ required: true, message: 'Seleccione el tipo de bien', trigger: 'change' }],
        asset_date: [{ required: true, message: 'Seleccione la fecha', trigger: 'change' }],
        asset_description: [{ required: true, message: 'Describa el bien o servicio', trigger: 'blur' }],
        receipt_amount: [{ required: true, message: 'Ingrese el monto reclamado', trigger: 'blur' }],
      },
      rulesStep3: {
        claim_type: [{ required: true, message: 'Seleccione el tipo de registro', trigger: 'change' }],
        detail: [{ required: true, message: 'Describa su queja o reclamo', trigger: 'blur' }],
        expected_result: [{ required: true, message: 'Indique el resultado esperado', trigger: 'blur' }],
        terms_accepted: [
          {
            validator: (rule, value, callback) => {
              if (!value) callback(new Error('Debe aceptar la declaración'))
              else callback()
            },
            trigger: 'change'
          }
        ],
      },
    }
  },

  computed: {
    // Determina la URL base de los endpoints según el modo (widget o panel)
    baseUrl() {
      return this.embedded ? '' : ''
    },

    // Calcula el índice activo del stepper según si el paso 0 está visible
    activeStep() {
      return this.previousClaim ? this.currentStep : this.currentStep - 1
    },

    // Extrae el district_id final de la cascada de ubicación
    districtId() {
      return this.form.location_cascade && this.form.location_cascade.length === 3
        ? this.form.location_cascade[2]
        : null
    },
  },

  created() {
    this.loadTables()
  },

  methods: {
    // Carga los datos auxiliares desde el endpoint correspondiente
    loadTables() {
      if (this.embedded && this.tenantSlug) {
        // Widget: endpoint público con tenant_slug
        this.$http.get(`/claims/remote/tables/${this.tenantSlug}`)
          .then(response => {
            this.tables = response.data
          })
      } else if (!this.embedded) {
        // Panel admin: endpoint autenticado
        this.$http.get('/claims/tables')
          .then(response => {
            this.tables = response.data
          })
      }
    },

    // Verifica el código de reclamo previo en el backend
    lookupPreviousCode() {
      const code = (this.form.previous_code || '').trim()
      if (!code) {
        this.previousClaim = null
        return
      }
      this.lookingUp = true
      this.$http.get(`/claims/lookup/${encodeURIComponent(code)}`)
        .then(response => {
          this.previousClaim = response.data
          // Autorrellenar todos los campos del paso 1 con los datos del reclamo anterior
          const d = response.data
          if (d.identity_document_type)   this.form.identity_document_type   = d.identity_document_type
          if (d.identity_document_number) this.form.identity_document_number = d.identity_document_number
          if (d.name)                     this.form.name                     = d.name
          if (d.email)                    this.form.email                    = d.email
          if (d.phone)                    this.form.phone                    = d.phone
          if (d.address)                  this.form.address                  = d.address
          // Reconstruir la cascada de ubicación a partir del district_id (ubigeo 6 dígitos)
          // Nivel 1: departamento (2 dígitos), nivel 2: provincia (4 dígitos), nivel 3: distrito (6 dígitos)
          if (d.district_id) {
            const distStr = String(d.district_id)
            this.form.location_cascade = [
              distStr.substring(0, 2),
              distStr.substring(0, 4),
              distStr,
            ]
          }
          // Mostrar el step de reclamo previo solo cuando hay resultado
          this.currentStep = 0
        })
        .catch(() => {
          this.previousClaim = null
          // Si el usuario estaba en el paso 0, regresarlo al paso 1
          if (this.currentStep === 0) this.currentStep = 1
          this.$message.warning('Código no encontrado')
        })
        .finally(() => { this.lookingUp = false })
    },

    goNext() {
      this.currentStep++
    },

    goPrev() {
      // El paso mínimo accesible es 0 solo si hay un reclamo previo cargado
      const minStep = this.previousClaim ? 0 : 1
      if (this.currentStep > minStep) this.currentStep--
    },

    // Valida el formulario del paso actual antes de avanzar
    validateAndNext(refName) {
      this.$refs[refName].validate(valid => {
        if (valid) this.currentStep++
      })
    },

    onFileChange(file) {
      // Verificar tamaño: máx. 1 MB
      if (file.size > 1024 * 1024) {
        this.$message.error('El archivo no debe superar 1 MB')
        this.$refs.uploader.clearFiles()
        this.attachmentFile = null
        this.fileList = []
        return false
      }
      this.attachmentFile = file.raw
    },

    onFileRemove() {
      this.attachmentFile = null
    },

    // Construye FormData y envía el reclamo al backend
    submit() {
      this.$refs.formStep3.validate(valid => {
        if (!valid) return

        this.submitting = true
        const fd = this.buildFormData()

        // Seleccionar endpoint según modo
        const url = this.embedded
          ? '/claims/remote/store'
          : '/claims/store'

        this.$http.post(url, fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
          .then(response => {
            if (response.data.success) {
              this.submittedCode   = response.data.code
              this.resultSuccess   = true
              this.showResultDialog = true
            }
          })
          .catch(error => {
            // Extraer el primer mensaje de error de validación del backend
            if (error.response && error.response.data && error.response.data.errors) {
              const firstError = Object.values(error.response.data.errors)[0]
              this.resultErrorMsg = firstError[0] || 'Error al enviar el reclamo'
            } else {
              this.resultErrorMsg = 'Ocurrió un error al registrar el reclamo. Inténtelo nuevamente.'
            }
            this.resultSuccess   = false
            this.showResultDialog = true
          })
          .finally(() => { this.submitting = false })
      })
    },

    // Construye el FormData con todos los campos del formulario
    buildFormData() {
      const fd = new FormData()

      // Datos del modo widget
      if (this.embedded && this.tenantSlug) {
        fd.append('tenant_slug', this.tenantSlug)
      }

      // Reclamo previo
      if (this.form.previous_code) {
        fd.append('previous_code', this.form.previous_code)
      }

      // Paso 1
      fd.append('identity_document_type', this.form.identity_document_type)
      fd.append('identity_document_number', this.form.identity_document_number)
      fd.append('name', this.form.name)
      fd.append('email', this.form.email)
      if (this.form.phone) fd.append('phone', this.form.phone)
      if (this.districtId) fd.append('district_id', this.districtId)
      if (this.form.address) fd.append('address', this.form.address)

      // Paso 2
      fd.append('asset_type', this.form.asset_type)
      fd.append('asset_description', this.form.asset_description)
      fd.append('asset_date', this.form.asset_date)
      fd.append('has_receipt', this.form.has_receipt ? '1' : '0')

      if (this.form.has_receipt) {
        fd.append('receipt_amount', this.form.receipt_amount)
        fd.append('receipt_currency', this.form.receipt_currency)
        fd.append('receipt_document_type', this.form.receipt_document_type)
        if (this.form.receipt_series) fd.append('receipt_series', this.form.receipt_series)
        if (this.form.receipt_number) fd.append('receipt_number', this.form.receipt_number)
      }

      // Paso 3
      fd.append('claim_type', this.form.claim_type)
      fd.append('detail', this.form.detail)
      fd.append('expected_result', this.form.expected_result)
      if (this.form.channel) fd.append('channel', this.form.channel)
      fd.append('terms_accepted', '1')

      if (this.attachmentFile) {
        fd.append('attachment', this.attachmentFile)
      }

      return fd
    },

    // Copia el código de seguimiento al portapapeles
    copyCode() {
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(this.submittedCode).then(() => {
          this.$message.success('Código copiado')
        }).catch(() => this.fallbackCopyCode())
      } else {
        this.fallbackCopyCode()
      }
    },

    fallbackCopyCode() {
      const el = document.createElement('textarea')
      el.value = this.submittedCode
      el.style.cssText = 'position:fixed;opacity:0'
      document.body.appendChild(el)
      el.select()
      try {
        document.execCommand('copy')
        this.$message.success('Código copiado')
      } catch {
        this.$message.error('No se pudo copiar automáticamente')
      }
      document.body.removeChild(el)
    },

    // Reinicia el formulario para registrar otro reclamo
    reset() {
      this.showResultDialog = false
      this.resultSuccess    = false
      this.submittedCode    = ''
      this.resultErrorMsg   = ''
      this.currentStep = 1
      this.previousClaim = null
      this.fileList = []
      this.attachmentFile = null
      this.form = {
        previous_code: '',
        identity_document_type: '',
        identity_document_number: '',
        name: '',
        email: '',
        phone: '',
        location_cascade: [],
        address: '',
        asset_type: '',
        asset_description: '',
        asset_date: '',
        has_receipt: false,
        receipt_amount: '',
        receipt_currency: 'PEN',
        receipt_document_type: '03',
        receipt_series: '',
        receipt_number: '',
        claim_type: '',
        detail: '',
        expected_result: '',
        channel: '',
        terms_accepted: false,
      }
      if (this.$refs.formStep1) this.$refs.formStep1.clearValidate()
      if (this.$refs.formStep2) this.$refs.formStep2.clearValidate()
      if (this.$refs.formStep3) this.$refs.formStep3.clearValidate()
    },
  }
}
</script>

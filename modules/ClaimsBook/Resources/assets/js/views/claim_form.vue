<template>
  <div class="cf-wrapper" :class="{ 'cf-embedded': embedded }">

    <!-- Encabezado -->
    <div class="cf-header">
      <div class="cf-header-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
          <polyline points="10 9 9 9 8 9"/>
        </svg>
      </div>
      <h3 class="cf-title">Libro de Reclamaciones</h3>
      <p class="cf-subtitle">
        Conforme a lo establecido en el Código de Protección y Defensa del Consumidor
        (Ley N° 29571), usted tiene derecho a presentar su queja o reclamo.
      </p>
    </div>

    <!-- Código de reclamo previo -->
    <div class="cf-prev-code-card">
      <p class="cf-prev-code-hint">
        ¿Ya presentó un reclamo? Ingrese su código para vincularlo o realizar seguimiento.
      </p>
      <div class="cf-inline">
        <el-input v-model="form.previous_code" placeholder="Ej: RJL76KQ2WO37U" size="small"
          :disabled="lookingUp"></el-input>
        <button class="cf-btn cf-btn-outline" :disabled="lookingUp" @click="lookupPreviousCode">
          <span v-if="lookingUp" class="cf-spinner"></span>
          <span v-else>Verificar</span>
        </button>
        <button class="cf-btn cf-btn-outline" :disabled="lookingUp" @click="clearForm">Limpiar</button>
      </div>
    </div>

    <!-- Stepper custom -->
    <div class="cf-stepper">
      <template v-if="previousClaim">
        <div class="cf-step-item" :class="{ active: activeStep === 0, done: activeStep > 0 }">
          <div class="cf-step-circle">
            <svg v-if="activeStep > 0" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span v-else>1</span>
          </div>
          <span class="cf-step-label">Reclamo previo</span>
        </div>
        <div class="cf-step-connector" :class="{ done: activeStep > 0 }"></div>
      </template>

      <div class="cf-step-item"
        :class="{ active: activeStep === (previousClaim ? 1 : 0), done: activeStep > (previousClaim ? 1 : 0) }">
        <div class="cf-step-circle">
          <svg v-if="activeStep > (previousClaim ? 1 : 0)" xmlns="http://www.w3.org/2000/svg" width="13" height="13"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span v-else>{{ previousClaim ? 2 : 1 }}</span>
        </div>
        <span class="cf-step-label">Datos</span>
      </div>
      <div class="cf-step-connector" :class="{ done: activeStep > (previousClaim ? 1 : 0) }"></div>

      <div class="cf-step-item"
        :class="{ active: activeStep === (previousClaim ? 2 : 1), done: activeStep > (previousClaim ? 2 : 1) }">
        <div class="cf-step-circle">
          <svg v-if="activeStep > (previousClaim ? 2 : 1)" xmlns="http://www.w3.org/2000/svg" width="13" height="13"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span v-else>{{ previousClaim ? 3 : 2 }}</span>
        </div>
        <span class="cf-step-label">Bien</span>
      </div>
      <div class="cf-step-connector" :class="{ done: activeStep > (previousClaim ? 2 : 1) }"></div>

      <div class="cf-step-item"
        :class="{ active: activeStep === (previousClaim ? 3 : 2), done: activeStep > (previousClaim ? 3 : 2) }">
        <div class="cf-step-circle">
          <svg v-if="activeStep > (previousClaim ? 3 : 2)" xmlns="http://www.w3.org/2000/svg" width="13" height="13"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span v-else>{{ previousClaim ? 4 : 3 }}</span>
        </div>
        <span class="cf-step-label">Detalle</span>
      </div>
    </div>

    <!-- ──────────── Paso 0: Código de reclamo previo ──────────── -->
    <div v-if="currentStep === 0" class="cf-step-content">
      <div v-if="previousClaim" class="cf-prev-result">

        <!-- Banner de estado: color dinámico según si está cerrado o en proceso -->
        <div class="cf-alert" :class="previousClaim.is_closed ? 'cf-alert-success' : 'cf-alert-info'">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <div>
            <div>Reclamo <strong>{{ previousClaim.code }}</strong> — Estado: <strong>{{ previousClaim.status ? previousClaim.status.description : 'Sin estado' }}</strong></div>
            <div style="font-size:12px;margin-top:3px;opacity:.85">
              <span v-if="previousClaim.is_closed">Reclamo cerrado y resuelto. Puede vincular un nuevo reclamo a continuación.</span>
              <span v-else>Su reclamo está en proceso de atención. Puede realizar seguimiento con el código indicado.</span>
            </div>
          </div>
        </div>

        <!-- Resumen compacto: igual para ambos flujos; incluye datos personales, bien y detalle -->
        <div class="cf-prev-summary">
          <div class="cf-summary-row" v-if="previousClaim.name">
            <span class="cf-summary-label">Nombre</span>
            <span>{{ previousClaim.name }}</span>
          </div>
          <div class="cf-summary-row" v-if="previousClaim.email">
            <span class="cf-summary-label">Email</span>
            <span>{{ maskEmail(previousClaim.email) }}</span>
          </div>
          <div class="cf-summary-row" v-if="previousClaim.phone">
            <span class="cf-summary-label">Teléfono</span>
            <span>{{ previousClaim.phone }}</span>
          </div>
          <div class="cf-summary-row" v-if="previousClaim.claim_type">
            <span class="cf-summary-label">Tipo</span>
            <span>{{ previousClaim.claim_type === 'queja' ? 'Queja' : 'Reclamo' }}</span>
          </div>
          <div class="cf-summary-row" v-if="previousClaim.channel">
            <span class="cf-summary-label">Canal</span>
            <span>{{ previousClaim.channel }}</span>
          </div>
        </div>

        <!-- Bien/servicio reclamado -->
        <div v-if="previousClaim.asset_type || previousClaim.asset_description" class="cf-info-block" style="margin-top:12px">
          <p class="cf-info-block-title">Bien o servicio reclamado</p>
          <div class="cf-summary-row" v-if="previousClaim.asset_type">
            <span class="cf-summary-label">Tipo</span>
            <span>{{ previousClaim.asset_type === 'producto' ? 'Producto' : 'Servicio' }}</span>
          </div>
          <div class="cf-summary-row" v-if="previousClaim.asset_description">
            <span class="cf-summary-label">Descripción</span>
            <span style="white-space:pre-wrap">{{ previousClaim.asset_description }}</span>
          </div>
        </div>

        <!-- Detalle del reclamo -->
        <div v-if="previousClaim.detail || previousClaim.expected_result" class="cf-info-block" style="margin-top:12px">
          <p class="cf-info-block-title">Detalle del reclamo</p>
          <div class="cf-summary-row" v-if="previousClaim.detail">
            <span class="cf-summary-label">Detalle</span>
            <span style="white-space:pre-wrap">{{ previousClaim.detail }}</span>
          </div>
          <div class="cf-summary-row" v-if="previousClaim.expected_result">
            <span class="cf-summary-label">Pedido</span>
            <span style="white-space:pre-wrap">{{ previousClaim.expected_result }}</span>
          </div>
        </div>

        <!-- Resolución (solo cuando está cerrado) -->
        <div v-if="previousClaim.is_closed" class="cf-resolution-box" style="margin-top:12px">
          <p class="cf-resolution-label">Resolución de la empresa</p>
          <p class="cf-resolution-text">{{ previousClaim.resolution || 'Sin resolución registrada.' }}</p>
        </div>

        <!-- Adjuntos del reclamante -->
        <div v-if="previousClaim.attachments && previousClaim.attachments.length" class="cf-receipt-section" style="margin-top:12px">
          <p class="cf-receipt-section-title">Archivos adjuntos del reclamante</p>
          <ul class="cf-file-list">
            <li v-for="(url, idx) in previousClaim.attachments" :key="'att-'+idx">
              <a :href="url" @click.prevent="downloadUrl(url)" :title="getFilenameFromUrl(url)" rel="noopener noreferrer">{{ getFilenameFromUrl(url) }}</a>
            </li>
          </ul>
        </div>

        <!-- Adjuntos de respuesta de la empresa -->
        <div v-if="previousClaim.response_attachments && previousClaim.response_attachments.length" class="cf-receipt-section" style="margin-top:12px">
          <p class="cf-receipt-section-title">Archivos adjuntos por la empresa</p>
          <ul class="cf-file-list">
            <li v-for="(url, idx) in previousClaim.response_attachments" :key="'resp-'+idx">
              <a :href="url" @click.prevent="downloadUrl(url)" :title="getFilenameFromUrl(url)" rel="noopener noreferrer">{{ getFilenameFromUrl(url) }}</a>
            </li>
          </ul>
        </div>

        <!-- Constancia PDF -->
        <div v-if="previousClaim.pdf_url" class="cf-receipt-section" style="margin-top:12px">
          <p class="cf-receipt-section-title">Constancia (PDF)</p>
          <ul class="cf-file-list">
            <li>
              <a :href="previousClaim.pdf_url" @click.prevent="downloadUrl(previousClaim.pdf_url)" :title="getFilenameFromUrl(previousClaim.pdf_url)" rel="noopener noreferrer">{{ getFilenameFromUrl(previousClaim.pdf_url) }}</a>
            </li>
          </ul>
        </div>

        <!-- Aviso cuando está en proceso (no se puede continuar) -->
        <div v-if="!previousClaim.is_closed" class="cf-alert cf-alert-warning" style="margin-top:14px">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
          <span>No es posible vincular un nuevo reclamo mientras el anterior está en proceso de atención.</span>
        </div>

      </div>
      <!-- Solo muestra Continuar cuando el reclamo previo fue cerrado -->
      <p style="text-align: right;"><small>Si el resultado no ha sido el esperado, puedes generar un reclamo relacionado.</small></p>
      <div class="cf-step-actions">
        <button v-if="previousClaim && previousClaim.is_closed" class="cf-btn cf-btn-primary" @click="goNext">
          Generar reclamo relacionado
        </button>
      </div>
    </div>

    <!-- ──────────── Paso 1: Datos del reclamante ──────────── -->
    <div v-if="currentStep === 1" class="cf-step-content">
      <p class="cf-section-title">Datos del reclamante</p>
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
          <el-col :span="16" :xs="24">
            <el-form-item label="Correo electrónico" prop="email">
              <el-input v-model="form.email" placeholder="correo@ejemplo.com" type="email"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="8" :xs="24">
            <el-form-item label="Teléfono">
              <el-input v-model="form.phone" placeholder="987654321"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="16" :xs="24">
            <el-form-item label="Dirección">
              <el-input v-model="form.address" placeholder="Av. Ejemplo 123..."></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="8" :xs="24">
            <el-form-item label="Departamento / Provincia / Distrito">
              <el-cascader v-model="form.location_cascade" :options="tables.locations"
                :props="{ expandTrigger: 'hover', value: 'value', label: 'label', children: 'children' }"
                placeholder="Seleccione su ubicación" filterable clearable style="width:100%"></el-cascader>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div class="cf-step-actions">
        <button class="cf-btn cf-btn-outline" @click="goPrev">Atrás</button>
        <button class="cf-btn cf-btn-primary" @click="validateAndNext('formStep1')">Continuar</button>
      </div>
    </div>

    <!-- ──────────── Paso 2: Bien contratado ──────────── -->
    <div v-if="currentStep === 2" class="cf-step-content">
      <p class="cf-section-title">Bien o servicio contratado</p>
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
              <div class="cf-switch-row">
                <el-switch v-model="form.has_receipt"></el-switch>
                <span class="cf-switch-label">Cuenta con comprobante de pago</span>
              </div>
            </el-form-item>
          </el-col>

          <!-- Campos de comprobante condicionales -->
          <template v-if="form.has_receipt">
            <el-col :span="24">
              <div class="cf-receipt-section">
                <p class="cf-receipt-section-title">Datos del comprobante</p>
                <el-row :gutter="16">
                  <el-col :span="5" :xs="24">
                    <el-form-item label="Moneda">
                      <el-select v-model="form.receipt_currency" style="width:100%">
                        <el-option value="PEN" label="Soles (PEN)"></el-option>
                        <el-option value="USD" label="Dólares (USD)"></el-option>
                      </el-select>
                    </el-form-item>
                  </el-col>
                  <el-col :span="4" :xs="24">
                    <el-form-item label="Monto" prop="receipt_amount">
                      <el-input v-model="form.receipt_amount" type="number" min="0" placeholder="0.00"></el-input>
                    </el-form-item>
                  </el-col>
                  <el-col :span="6" :xs="24">
                    <el-form-item label="Tipo de documento">
                      <el-select v-model="form.receipt_document_type" placeholder="Seleccione" style="width:100%">
                        <el-option value="01" label="Factura"></el-option>
                        <el-option value="03" label="Boleta de venta"></el-option>
                        <el-option value="07" label="Nota de crédito"></el-option>
                        <el-option value="08" label="Nota de débito"></el-option>
                      </el-select>
                    </el-form-item>
                  </el-col>
                  <el-col :span="3" :xs="24">
                    <el-form-item label="Serie">
                      <el-input v-model="form.receipt_series" placeholder="B001"></el-input>
                    </el-form-item>
                  </el-col>
                  <el-col :span="6" :xs="24">
                    <el-form-item label="N° de comprobante">
                      <el-input v-model="form.receipt_number" placeholder="00000001"></el-input>
                    </el-form-item>
                  </el-col>
                </el-row>
              </div>
            </el-col>
          </template>
        </el-row>
      </el-form>
      <div class="cf-step-actions">
        <button class="cf-btn cf-btn-outline" @click="goPrev">Atrás</button>
        <button class="cf-btn cf-btn-primary" @click="validateAndNext('formStep2')">Continuar</button>
      </div>
    </div>

    <!-- ──────────── Paso 3: Detalle del reclamo ──────────── -->
    <div v-if="currentStep === 3" class="cf-step-content">
      <p class="cf-section-title">Detalle del reclamo</p>
      <el-form ref="formStep3" :model="form" :rules="rulesStep3" label-position="top" size="small">
        <el-row :gutter="16">
          <el-col :span="12" :xs="24">
            <el-form-item label="Tipo de registro" prop="claim_type">
              <el-select v-model="form.claim_type" placeholder="Seleccione" style="width:100%">
                <el-option value="queja" label="Queja — sin afectación económica"></el-option>
                <el-option value="reclamo" label="Reclamo — con afectación económica"></el-option>
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
            <el-form-item label="Resultado esperado" prop="expected_result">
              <el-input v-model="form.expected_result" type="textarea" :rows="3"
                placeholder="¿Qué solución espera recibir?"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Adjunto (opcional · máx. 5 archivos · máx. 1 MB por archivo: jpg, jpeg, png, pdf)">
              <el-upload ref="uploader" action="#"
                :http-request="() => { }"
                :on-change="onFileChange"
                :on-remove="onFileRemove"
                :file-list="fileList"
                :limit="5"
                multiple
                :auto-upload="false"
                :on-exceed="() => $message.warning('Máximo 5 archivos permitidos')"
                accept=".jpg,.jpeg,.png,.pdf">
                <button type="button" class="cf-btn cf-btn-outline cf-btn-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                  </svg>
                  Adjuntar archivos
                </button>
              </el-upload>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item prop="terms_accepted">
              <div class="cf-checkbox-row">
                <el-checkbox v-model="form.terms_accepted"></el-checkbox>
                <span class="cf-checkbox-label">
                  Acepto que los datos brindados son verídicos y me responsabilizo
                  de la información consignada en esta declaración.
                </span>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div class="cf-step-actions">
        <button class="cf-btn cf-btn-outline" @click="goPrev">Atrás</button>
        <button class="cf-btn cf-btn-primary" :disabled="submitting" @click="submit">
          <span v-if="submitting" class="cf-spinner cf-spinner-white"></span>
          <span v-else>Enviar reclamo</span>
        </button>
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
/* ── Variables (shadcn/ui palette) ── */
.cf-wrapper {
  --cf-bg:          #ffffff;
  --cf-border:      #e4e4e7;
  --cf-ring:        #a1a1aa;
  --cf-foreground:  #09090b;
  --cf-muted:       #f4f4f5;
  --cf-muted-fg:    #71717a;
  --cf-primary:     #18181b;
  --cf-primary-fg:  #fafafa;
  --cf-radius:      0.5rem;
  --cf-shadow:      0 1px 3px 0 rgb(0 0 0/.08), 0 1px 2px -1px rgb(0 0 0/.08);

  max-width: 680px;
  margin: 0 auto;
  padding: 32px 24px;
  font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", sans-serif;
  color: var(--cf-foreground);
  font-size: 14px;
  line-height: 1.5;
}

.cf-embedded { padding: 20px 16px; }

/* ── Header ── */
.cf-header {
  text-align: center;
  margin-bottom: 28px;
}

.cf-header-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: var(--cf-radius);
  background: var(--cf-muted);
  color: var(--cf-foreground);
  margin-bottom: 14px;
}

.cf-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--cf-foreground);
  margin: 0 0 6px;
  letter-spacing: -0.02em;
}

.cf-subtitle {
  font-size: 13px;
  color: var(--cf-muted-fg);
  margin: 0;
  line-height: 1.6;
  max-width: 480px;
  margin-inline: auto;
}

/* ── Código de reclamo previo ── */
.cf-prev-code-card {
  border: 1px solid var(--cf-border);
  border-radius: var(--cf-radius);
  padding: 16px;
  background: var(--cf-muted);
  margin-bottom: 24px;
}

.cf-prev-code-hint {
  font-size: 12.5px;
  color: var(--cf-muted-fg);
  margin: 0 0 10px;
}

.cf-inline {
  display: flex;
  gap: 8px;
  align-items: center;
}

/* ── Stepper custom ── */
.cf-stepper {
  display: flex;
  align-items: center;
  margin-bottom: 28px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.cf-step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  min-width: 64px;
}

.cf-step-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1.5px solid var(--cf-border);
  background: var(--cf-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 500;
  color: var(--cf-muted-fg);
  transition: all 0.2s;
  flex-shrink: 0;
}

.cf-step-item.active .cf-step-circle {
  background: var(--cf-primary);
  border-color: var(--cf-primary);
  color: var(--cf-primary-fg);
}

.cf-step-item.done .cf-step-circle {
  background: var(--cf-primary);
  border-color: var(--cf-primary);
  color: var(--cf-primary-fg);
}

.cf-step-label {
  font-size: 11px;
  color: var(--cf-muted-fg);
  white-space: nowrap;
  font-weight: 400;
}

.cf-step-item.active .cf-step-label,
.cf-step-item.done .cf-step-label {
  color: var(--cf-foreground);
  font-weight: 500;
}

.cf-step-connector {
  flex: 1;
  height: 1px;
  background: var(--cf-border);
  min-width: 24px;
  margin-bottom: 18px;
  transition: background 0.2s;
}

.cf-step-connector.done {
  background: var(--cf-primary);
}

/* ── Step content ── */
.cf-step-content {
  animation: cf-fade-in 0.18s ease;
}

@keyframes cf-fade-in {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}

.cf-section-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--cf-muted-fg);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin: 0 0 16px;
}

/* ── Alertas ── */
.cf-alert {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 14px;
  border-radius: var(--cf-radius);
  font-size: 13px;
  line-height: 1.5;
  border: 1px solid transparent;
}

.cf-alert svg { flex-shrink: 0; margin-top: 1px; }

.cf-alert-info {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #1d4ed8;
}

.cf-alert-warning {
  background: #fffbeb;
  border-color: #fde68a;
  color: #92400e;
}

.cf-prev-result { margin-bottom: 16px; }

/* ── Resumen compacto del reclamo previo (paso 0) ── */
.cf-prev-summary {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 5px;
  padding: 12px 14px;
  border: 1px solid var(--cf-border);
  border-radius: var(--cf-radius);
  background: var(--cf-bg);
}

.cf-summary-row {
  display: flex;
  gap: 8px;
  align-items: baseline;
  font-size: 13px;
}

.cf-summary-label {
  min-width: 72px;
  color: var(--cf-muted-fg);
  font-weight: 500;
  font-size: 12px;
  flex-shrink: 0;
}

/* ── Lista de archivos en paso 0 ── */
.cf-file-list {
  margin: 0;
  padding-left: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.cf-file-list li a {
  font-size: 12.5px;
  color: #2563eb;
  text-decoration: none;
  word-break: break-all;
}

.cf-file-list li a:hover {
  text-decoration: underline;
}

/* ── Variante success para alerta de reclamo cerrado ── */
.cf-alert-success {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #15803d;
}

/* ── Bloque de info expandido (bien, detalle) ── */
.cf-info-block {
  border: 1px solid var(--cf-border);
  border-radius: var(--cf-radius);
  padding: 12px 14px;
  background: var(--cf-bg);
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.cf-info-block-title {
  font-size: 11px;
  font-weight: 600;
  color: var(--cf-muted-fg);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin: 0 0 6px;
}

.cf-resolution-box {
  margin-top: 12px;
  border: 1px solid var(--cf-border);
  border-radius: var(--cf-radius);
  padding: 12px 14px;
  background: var(--cf-bg);
}

.cf-resolution-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--cf-muted-fg);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin: 0 0 4px;
}

.cf-resolution-text {
  font-size: 13px;
  color: var(--cf-foreground);
  margin: 0;
}

/* ── Switch & Checkbox rows ── */
.cf-switch-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.cf-switch-label {
  font-size: 13.5px;
  color: var(--cf-foreground);
}

.cf-checkbox-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.cf-checkbox-label {
  font-size: 13px;
  color: var(--cf-foreground);
  line-height: 1.5;
  padding-top: 2px;
}

/* ── Receipt section ── */
.cf-receipt-section {
  border: 1px solid var(--cf-border);
  border-radius: var(--cf-radius);
  padding: 16px;
  background: var(--cf-muted);
  margin-bottom: 8px;
}

.cf-receipt-section-title {
  font-size: 12px;
  font-weight: 600;
  color: var(--cf-muted-fg);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin: 0 0 14px;
}

/* ── Botones ── */
.cf-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 0 14px;
  height: 36px;
  border-radius: var(--cf-radius);
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
  white-space: nowrap;
  font-family: inherit;
}

.cf-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cf-btn-primary {
  background: var(--cf-primary);
  color: var(--cf-primary-fg);
  border-color: var(--cf-primary);
}

.cf-btn-primary:hover:not(:disabled) {
  background: #27272a;
  border-color: #27272a;
}

.cf-btn-outline {
  background: var(--cf-bg);
  color: var(--cf-foreground);
  border-color: var(--cf-border);
}

.cf-btn-outline:hover:not(:disabled) {
  background: var(--cf-muted);
}

.cf-btn-sm {
  height: 30px;
  padding: 0 10px;
  font-size: 12.5px;
}

/* ── Acciones del paso ── */
.cf-step-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid var(--cf-border);
}

/* ── Spinner ── */
.cf-spinner {
  width: 14px;
  height: 14px;
  border: 2px solid var(--cf-border);
  border-top-color: var(--cf-foreground);
  border-radius: 50%;
  animation: cf-spin 0.6s linear infinite;
  display: inline-block;
}

.cf-spinner-white {
  border-color: rgba(255,255,255,0.35);
  border-top-color: #fff;
}

@keyframes cf-spin {
  to { transform: rotate(360deg); }
}

/* ── Override Element UI para match shadcn ── */
.cf-wrapper :deep(.el-input__inner),
.cf-wrapper :deep(.el-textarea__inner) {
  border-color: var(--cf-border);
  border-radius: var(--cf-radius);
  font-size: 13.5px;
  color: var(--cf-foreground);
  background: var(--cf-bg);
  transition: border-color 0.15s, box-shadow 0.15s;
}

.cf-wrapper :deep(.el-input__inner:focus),
.cf-wrapper :deep(.el-textarea__inner:focus) {
  border-color: var(--cf-primary);
  box-shadow: 0 0 0 2px rgb(24 24 27 / 0.08);
  outline: none;
}

.cf-wrapper :deep(.el-select .el-input__inner) {
  border-color: var(--cf-border);
}

.cf-wrapper :deep(.el-form-item__label) {
  font-size: 13px;
  font-weight: 500;
  color: var(--cf-foreground);
  padding-bottom: 6px;
  line-height: 1.4;
}

.cf-wrapper :deep(.el-form-item) {
  margin-bottom: 16px;
}

.cf-wrapper :deep(.el-form-item__error) {
  font-size: 12px;
  color: #ef4444;
}

.cf-wrapper :deep(.el-cascader .el-input__inner) {
  border-color: var(--cf-border);
}

.cf-wrapper :deep(.el-date-editor .el-input__inner) {
  border-color: var(--cf-border);
}

.cf-wrapper :deep(.el-upload .el-button) {
  display: none;
}

.cf-wrapper :deep(.el-checkbox__inner) {
  border-color: var(--cf-border);
  border-radius: 3px;
}

.cf-wrapper :deep(.el-checkbox__input.is-checked .el-checkbox__inner) {
  background-color: var(--cf-primary);
  border-color: var(--cf-primary);
}

.cf-wrapper :deep(.el-switch.is-checked .el-switch__core) {
  background-color: var(--cf-primary);
  border-color: var(--cf-primary);
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
    },
    // Color primario del widget (hex, ej: "#409EFF") — personalizable sin BD
    primaryColor: {
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

  mounted() {
    // Aplicar el color primario personalizado como variable CSS sobre el wrapper
    if (this.primaryColor && /^#[0-9A-Fa-f]{6}$/.test(this.primaryColor)) {
      this.$el.style.setProperty('--cf-primary', this.primaryColor)
    }
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
          const d = response.data

          // Siempre autorrellenar paso 1
          if (d.identity_document_type)   this.form.identity_document_type   = d.identity_document_type
          if (d.identity_document_number) this.form.identity_document_number = d.identity_document_number
          if (d.name)                     this.form.name                     = d.name
          if (d.email)                    this.form.email                    = d.email
          if (d.phone)                    this.form.phone                    = d.phone
          if (d.address)                  this.form.address                  = d.address
          // Reconstruir cascada de ubicación (ubigeo 6 dígitos)
          if (d.district_id) {
            const distStr = String(d.district_id)
            this.form.location_cascade = [
              distStr.substring(0, 2),
              distStr.substring(0, 4),
              distStr,
            ]
          }

          if (d.is_closed) {
            // Reclamo cerrado: autorrellenar pasos 2 y parcialmente 3, saltar al paso 3
            if (d.asset_type)        this.form.asset_type        = d.asset_type
            if (d.asset_description) this.form.asset_description = d.asset_description
            if (d.asset_date)        this.form.asset_date        = d.asset_date
            this.form.has_receipt = !!d.has_receipt
            if (d.has_receipt) {
              if (d.receipt_amount)   this.form.receipt_amount   = d.receipt_amount
              if (d.receipt_currency) this.form.receipt_currency = d.receipt_currency
              if (d.receipt_series)   this.form.receipt_series   = d.receipt_series
              if (d.receipt_number)   this.form.receipt_number   = d.receipt_number
            }
            // Paso 3 parcial: tipo y canal; detail, expected_result, archivos y aceptación quedan vacíos
            if (d.claim_type) this.form.claim_type = d.claim_type
            if (d.channel)    this.form.channel    = d.channel
            // Mostrar paso 0 primero (igual que en proceso) para que el usuario vea el resumen
            this.currentStep = 0
          } else {
            // Reclamo en proceso: mostrar info en paso 0, no permite continuar
            this.currentStep = 0
          }
        })
        .catch(() => {
          this.previousClaim = null
          if (this.currentStep === 0) this.currentStep = 1
          this.$message.warning('Código no encontrado')
        })
        .finally(() => { this.lookingUp = false })
    },

    goNext() {
      // Reclamo previo cerrado en paso 0: saltar directo al paso 3 (1 y 2 bloqueados)
      if (this.currentStep === 0 && this.previousClaim && this.previousClaim.is_closed) {
        this.currentStep = 3
        return
      }
      this.currentStep++
    },

    goPrev() {
      const minStep = this.previousClaim ? 0 : 1
      // Reclamo cerrado: desde paso 3, volver al 0 para ver info del reclamo previo
      if (this.previousClaim && this.previousClaim.is_closed && this.currentStep === 3) {
        this.currentStep = 0
        return
      }
      if (this.currentStep > minStep) this.currentStep--
    },

    // Reinicia completamente el formulario (borra código previo, previous claim y todos los campos)
    clearForm() {
      this.previousClaim  = null
      this.lookingUp      = false
      this.fileList       = []
      this.attachmentFile = null
      this.currentStep    = 1
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
      if (this.$refs.uploader)  this.$refs.uploader.clearFiles()
      if (this.$refs.formStep1) this.$refs.formStep1.clearValidate()
      if (this.$refs.formStep2) this.$refs.formStep2.clearValidate()
      if (this.$refs.formStep3) this.$refs.formStep3.clearValidate()
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

    // Enmascarar email: car****@gmail.com
    maskEmail(email) {
      if (!email) return ''
      const [local, domain] = String(email).split('@')
      if (!domain) return email
      const visible = local.length > 3 ? local.substring(0, 3) : local.substring(0, 1)
      return `${visible}****@${domain}`
    },

    // Obtener nombre de archivo desde una URL
    getFilenameFromUrl(url) {
      try {
        const parts = String(url).split('/')
        const last = parts[parts.length - 1] || ''
        return decodeURIComponent((last.split('?')[0]) || 'archivo')
      } catch {
        return 'archivo'
      }
    },

    // Forzar descarga del recurso (genera un enlace y dispara click)
    downloadUrl(url) {
      try {
        const a = document.createElement('a')
        a.href = url
        a.target = '_blank'
        a.download = this.getFilenameFromUrl(url) || ''
        document.body.appendChild(a)
        a.click()
        document.body.removeChild(a)
      } catch (e) {
        // fallback: abrir en nueva pestaña
        window.open(url, '_blank')
      }
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

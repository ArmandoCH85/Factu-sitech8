<template>
  <el-dialog
    :visible.sync="visible"
    :show-close="true"
    width="460px"
    :close-on-click-modal="false"
    @closed="$emit('closed')"
  >
    <!-- Éxito -->
    <template v-if="success">
      <div class="crd-body">
        <div class="crd-icon crd-icon--success">
          <i class="el-icon-circle-check"></i>
        </div>
        <h3 class="crd-title">¡Reclamo registrado!</h3>
        <p class="crd-subtitle">Su código de seguimiento es:</p>

        <!-- Código copiable -->
        <div class="crd-code-copy">
          <span class="crd-code-text">{{ code }}</span>
          <el-button
            size="mini"
            type="primary"
            icon="el-icon-document-copy"
            @click="copyCode"
          >Copiar</el-button>
        </div>

        <p class="crd-note">
          Guarde este código para consultar el estado de su reclamo.<br>
          El plazo máximo de respuesta es de 15 días hábiles.
        </p>
      </div>
    </template>

    <!-- Error -->
    <template v-else>
      <div class="crd-body">
        <div class="crd-icon crd-icon--error">
          <i class="el-icon-circle-close"></i>
        </div>
        <h3 class="crd-title">No se pudo registrar el reclamo</h3>
        <p class="crd-error-msg">{{ errorMsg }}</p>
      </div>
    </template>

    <span slot="footer">
      <el-button size="small" type="primary" @click="$emit('update:visible', false)">
        {{ success ? 'Aceptar' : 'Cerrar' }}
      </el-button>
    </span>
  </el-dialog>
</template>

<style scoped>
.crd-body {
  text-align: center;
  padding: 8px 0 4px;
}

.crd-icon {
  font-size: 52px;
  line-height: 1;
  margin-bottom: 12px;
}

.crd-icon--success {
  color: #67c23a;
}

.crd-icon--error {
  color: #f56c6c;
}

.crd-title {
  font-size: 18px;
  font-weight: 700;
  color: #303133;
  margin: 0 0 8px;
}

.crd-subtitle {
  font-size: 13px;
  color: #606266;
  margin: 0 0 12px;
}

.crd-code-copy {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: #f4f4f5;
  border-radius: 6px;
  padding: 8px 14px;
  margin-bottom: 16px;
}

.crd-code-text {
  font-family: 'Consolas', 'Monaco', monospace;
  font-size: 15px;
  font-weight: 700;
  color: #409EFF;
  letter-spacing: 1px;
}

.crd-note {
  font-size: 12px;
  color: #909399;
  line-height: 1.6;
  margin: 0;
}

.crd-error-msg {
  font-size: 13px;
  color: #f56c6c;
  margin: 8px 0 0;
  line-height: 1.5;
}
</style>

<script>
export default {
  name: 'ClaimResultDialog',

  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    // true = registro exitoso, false = error
    success: {
      type: Boolean,
      default: false,
    },
    // Código de seguimiento retornado por el backend
    code: {
      type: String,
      default: '',
    },
    // Mensaje de error a mostrar cuando success = false
    errorMsg: {
      type: String,
      default: 'Ocurrió un error al registrar el reclamo. Inténtelo nuevamente.',
    },
  },

  methods: {
    // Copia el código de seguimiento al portapapeles
    copyCode() {
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(this.code)
          .then(() => this.$message.success('Código copiado'))
          .catch(() => this.fallbackCopy())
      } else {
        this.fallbackCopy()
      }
    },

    fallbackCopy() {
      const el = document.createElement('textarea')
      el.value = this.code
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
  },
}
</script>

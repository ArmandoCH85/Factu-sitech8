# Fase 4 — Refactoring: reemplazar QZ Tray por BuhoPrinter

Metodología: cambios locales en el proyecto Vue existente. No se crea ningún paquete npm separado.
El objetivo es que los cuatro archivos funcionen igual que antes, pero consumiendo BuhoPrinter en lugar de QZ Tray.

---

## Análisis de uso actual de QZ Tray

### `qzFunctions.ts` (módulo central)

| Funcionalidad QZ Tray                                              | Para qué se usa                                     |
| ------------------------------------------------------------------ | --------------------------------------------------- |
| `qz.websocket.connect()`                                           | Establecer conexión con el agente QZ Tray           |
| `qz.api.getVersion()`                                              | Obtener versión del agente                          |
| `qz.printers.getDefault()`                                         | Obtener impresora por defecto                       |
| `qz.configs.create(printerName)`                                   | Crear objeto de configuración de impresora          |
| `cfg.reconfigure({...})`                                           | Configurar tamaño de papel (80mm), márgenes, copias |
| `qz.security.setCertificatePromise`                                | Autenticación del agente (certificado digital)      |
| `qz.security.setSignaturePromise`                                  | Firma de mensajes (clave privada RSA)               |
| **Exports**: `startConnection`, `getUpdatedConfig`, `displayError` | Usados en los tres componentes                      |

### `AvailablePrinterDialog.vue`

| Llamada QZ Tray           | Propósito                                      |
| ------------------------- | ---------------------------------------------- |
| `startConnection()`       | Conectar al abrir el diálogo                   |
| `qz.websocket.isActive()` | Verificar estado de conexión                   |
| `qz.printers.find()`      | Listar todas las impresoras disponibles del SO |

### `PrintOrdersRestaurant.vue`

| Llamada QZ Tray               | Propósito                                 |
| ----------------------------- | ----------------------------------------- |
| `startConnection()`           | Conectar al montar                        |
| `qz.websocket.isActive()`     | Verificar y mostrar estado                |
| `getUpdatedConfig()`          | Obtener config con nombre de impresora    |
| `qz.print(config, printData)` | Imprimir **PDF base64** (`event.pdf_b64`) |

### `CartPreSale.vue`

| Llamada QZ Tray               | Propósito                               |
| ----------------------------- | --------------------------------------- |
| `qz.websocket.isActive()`     | Solo lectura de estado (en `onMounted`) |
| `getUpdatedConfig()`          | Obtener config con nombre de impresora  |
| `qz.print(config, printData)` | Imprimir **HTML plano** (precuenta)     |

### `DocumentDialog.vue`

| Llamada QZ Tray                   | Propósito                                                          |
| --------------------------------- | ------------------------------------------------------------------ |
| `startConnection()`               | Conectar al montar                                                 |
| `qz.websocket.isActive()`         | Verificar y mostrar estado                                         |
| `getUpdatedConfig()`              | Obtener config con nombre de impresora                             |
| `qz.print(config, printData)` × 3 | Imprimir HTML (`autoPrint`) y PDF base64 (`autoPrintPdf`, `print`) |

---

## Mapa de equivalencias QZ Tray → BuhoPrinter

| QZ Tray                                                                | BuhoPrinter                                                                           |
| ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------- |
| `qz.websocket.connect()`                                               | `GET /status` — si responde, estamos conectados                                       |
| `qz.websocket.isActive()`                                              | Variable reactiva `isBuhoActive` (actualizada al hacer ping)                          |
| `qz.printers.find()`                                                   | `GET /printers` → array de `{ name, is_default }`                                     |
| `qz.printers.getDefault()`                                             | `GET /printers` → filtrar `is_default === true`                                       |
| `qz.configs.create(printer)`                                           | Objeto simple `{ printer: string }`                                                   |
| `cfg.reconfigure({...})`                                               | No necesario — BuhoPrinter maneja el formato en el servidor                           |
| `qz.print(config, [{type:'pixel',format:'pdf',flavor:'base64',data}])` | `POST /print` con `{ printer, data, format: 'pdf' }`                                  |
| `qz.print(config, [{type:'pixel',format:'html',flavor:'plain',data}])` | Convertir HTML → PDF base64 en cliente (jsPDF ya está instalado), luego `POST /print` |
| Certificados y firma RSA                                               | No necesario — BuhoPrinter corre en localhost, sin autenticación                      |

---

## Tareas de refactoring

### 4.1 — Crear módulo `buhoFunctions.ts`

**Archivo**: `src/utils/buhoFunctions.ts` (reemplaza `qzFunctions.ts`)

Debe exportar exactamente las mismas funciones que hoy consume el código:

- `startConnection(): Promise<void>` — hace `GET /status` en los puertos `[8181, 8282, 8383, 8484]`, guarda el puerto activo en una variable de módulo, actualiza `isBuhoActive`
- `getUpdatedConfig(): { printer: string }` — retorna objeto con `userSession.printerName`
- `displayError(err: unknown): void` — `console.error('[BuhoPrinter]', err)`

Variables exportadas adicionales:

- `isBuhoActive: Ref<boolean>` — estado reactivo de conexión (reemplaza `qz.websocket.isActive()`)
- `activePrinters: () => Promise<string[]>` — llama a `GET /printers`, retorna array de nombres

Función interna de envío:

```typescript
async function enviarImpresion(
  printer: string,
  data: string,
  format: 'pdf'
): Promise<void>
// POST http://localhost:{puertoActivo}/print
// Body: { printer, data, format }
```

Función interna para HTML:

```typescript
async function htmlToPdfBase64(html: string): Promise<string>
// Renderiza el html en un iframe oculto, lo captura con html2canvas + jsPDF
// Retorna base64 del PDF resultante
// Nota: jsPDF y html2canvas ya son dependencias del proyecto (usados en CartPreSale y DocumentDialog)
```

Función de alto nivel (usado por componentes para unificar los dos tipos de impresión):

```typescript
export async function imprimirDesdeConfig(
  config: { printer: string },
  printData: Array<{
    type: string
    format: string
    flavor: string
    data: string
    options?: any
  }>
): Promise<void>
// Detecta si el item es HTML o PDF, convierte si necesario, y llama a enviarImpresion
```

**No exportar** nada relativo a QZ Tray, certificados, ni WebSockets.

---

### 4.2 — Actualizar `AvailablePrinterDialog.vue`

Cambios:

1. Eliminar `import qz from 'qz-tray'`
2. Cambiar import de funciones:
   ```ts
   // Antes:
   import {
     startConnection,
     getUpdatedConfig,
     displayError,
   } from '/@src/utils/qzFunctions'
   // Después:
   import {
     startConnection,
     isBuhoActive,
     activePrinters,
   } from '/@src/utils/buhoFunctions'
   ```
3. `openDialogPrintAvailable`: reemplazar `qz.websocket.isActive()` con `isBuhoActive.value`
4. `getAllPrintersAvailable`: reemplazar `qz.printers.find()` con `await activePrinters()`
   - La API retorna `{ name, is_default }[]` — mapear a solo `name` para mantener compatibilidad con el template actual

Cambios en template:

- Ninguno — el template itera `printers` que sigue siendo `string[]`

---

### 4.3 — Actualizar `PrintOrdersRestaurant.vue`

Cambios:

1. Eliminar `import qz from 'qz-tray'`
2. Cambiar imports:
   ```ts
   // Antes:
   import { getUpdatedConfig, displayError } from '/@src/utils/qzFunctions'
   import { startConnection } from '/@src/utils/qzFunctions'
   // Después:
   import {
     startConnection,
     getUpdatedConfig,
     displayError,
     isBuhoActive,
     imprimirDesdeConfig,
   } from '/@src/utils/buhoFunctions'
   ```
3. En `printEvent`:
   - Reemplazar `qz.websocket.isActive()` con `isBuhoActive.value`
   - Reemplazar `await startConnection()` con `await startConnection()` (sin cambio de firma)
   - Reemplazar `await qz.print(config, printData)` con `await imprimirDesdeConfig(config, printData)`
4. En `onMounted`:
   - Reemplazar `qz.websocket.isActive()` con `isBuhoActive.value`

Cambios en template:

- Cambiar "QZ Tray:" por "BuhoPrinter:" en el badge de estado

---

### 4.4 — Actualizar `CartPreSale.vue`

Cambios:

1. Eliminar `import qz from 'qz-tray'`
2. Cambiar imports:
   ```ts
   // Antes:
   import {
     startConnection,
     getUpdatedConfig,
     displayError,
   } from '/@src/utils/qzFunctions'
   // Después:
   import {
     getUpdatedConfig,
     displayError,
     isBuhoActive,
     imprimirDesdeConfig,
   } from '/@src/utils/buhoFunctions'
   ```
3. En `print(html)`:
   - Reemplazar `qz.print(config, printData).catch(displayError)` con `imprimirDesdeConfig(config, printData).catch(displayError)`
4. En `onMounted`:
   - Reemplazar `qz.websocket.isActive()` con `isBuhoActive.value`

Nota: `CartPreSale` imprime HTML. `imprimirDesdeConfig` detectará `format: 'html'` y hará la conversión HTML→PDF automáticamente antes de enviar al servidor.

---

### 4.5 — Actualizar `DocumentDialog.vue`

Cambios:

1. Eliminar `import qz from 'qz-tray'`
2. Cambiar imports:
   ```ts
   // Antes:
   import {
     startConnection,
     getUpdatedConfig,
     displayError,
   } from '/@src/utils/qzFunctions'
   // Después:
   import {
     startConnection,
     getUpdatedConfig,
     displayError,
     isBuhoActive,
     imprimirDesdeConfig,
   } from '/@src/utils/buhoFunctions'
   ```
3. En `autoPrint(html)`:
   - Reemplazar `qz.print(config, printData).catch(displayError)` con `imprimirDesdeConfig(config, printData).catch(displayError)`
4. En `autoPrintPdf()`:
   - Igual, reemplazar `qz.print` con `imprimirDesdeConfig`
5. En `print(pdfBase64)`:
   - Igual, reemplazar `qz.print` con `imprimirDesdeConfig`
6. En `onMounted`:
   - Reemplazar `qz.websocket.isActive()` con `isBuhoActive.value`

---

### 4.6 — Limpieza de dependencias (opcional, al final)

Una vez verificado que todo funciona:

- Eliminar `qz-tray` del `package.json`
- Eliminar `jsrsasign` del `package.json` (solo se usaba para firmar mensajes QZ)
- Eliminar el archivo `qzFunctions.ts` original

---

## Orden de ejecución

```
4.1 → buhoFunctions.ts   (escribir el módulo nuevo, sin tocar nada más)
4.2 → AvailablePrinterDialog.vue
4.3 → PrintOrdersRestaurant.vue
4.4 → CartPreSale.vue
4.5 → DocumentDialog.vue
4.6 → limpieza de deps
```

Cada paso es independiente y puede verificarse de forma aislada antes de continuar.

---

## Verificación por archivo

### AvailablePrinterDialog

- [ ] Abre el diálogo → lista impresoras reales del SO
- [ ] Badge muestra "Conectado" cuando BuhoPrinter está activo

### PrintOrdersRestaurant

- [ ] Llega un evento SSE → se imprime el PDF en la impresora correcta
- [ ] Si BuhoPrinter está caído → badge muestra "Desconectado", error logeado

### CartPreSale

- [ ] Click "Imprimir" → genera HTML, convierte a PDF, imprime en `printerNamePreOrder`

### DocumentDialog

- [ ] Generar boleta/factura → `autoPrintPdf` imprime el ticket en `printerNameDocument`
- [ ] Generar comanda → `print(pdfBase64)` imprime en `printerNameCommand`
- [ ] `autoPrint(html)` imprime HTML convertido a PDF

---

## Notas de implementación

**Descubrimiento de puerto**: `startConnection` debe probar `[8181, 8282, 8383, 8484]` con `fetch('/status', { signal: AbortSignal.timeout(300) })` y quedarse con el primero que responde. El puerto activo se guarda como variable de módulo.

**Conversión HTML → PDF**: usar `jsPDF.html()` (ya disponible en el proyecto). El ancho del papel es 80mm = 226.77pt. La altura se calcula del DOM o se usa un valor suficientemente grande. Esto replica exactamente lo que ya hace `CartPreSale.showPdf()`.

**HTTPS vs HTTP**: BuhoPrinter sirve HTTPS con certificado autofirmado. Los navegadores modernos bloquean fetch a HTTPS con cert autofirmado desde una página HTTPS. Opciones en orden de preferencia:

1. Si la app Vue corre en HTTP en desarrollo → no hay problema
2. Si corre en HTTPS → usar `http://localhost` (BuhoPrinter puede servir también HTTP opcionalmente)
3. O instruir al usuario instalar el certificado de BuhoPrinter como CA de confianza

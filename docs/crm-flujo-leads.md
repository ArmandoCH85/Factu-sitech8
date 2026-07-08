# Manual del Flujo de Leads — CRM Comercial

> Documentación del flujo comercial del CRM Comercial KISS integrado al
> Facturador PRO 8. Cubre las 7 etapas del pipeline, las actividades de
> seguimiento y la relación con los demás sub-módulos de Preventa.

---

## Parte 1: El flujo general (de prospecto a cliente)

```
PROSPECTO (alguien pregunta por algo)
   │
   │  Vos lo captás como Lead o como Oportunidad de venta
   │
   ▼
LEAD NUEVO (etapa "new")
   │  Alguien preguntó, todavía ni le escribiste
   │
   ▼
LEAD CONTACTADO (etapa "contacted")
   │  Le mandaste WhatsApp, lo llamaste, o le escribiste
   │
   ▼
LEAD INTERESADO (etapa "interested")
   │  Te preguntó precios, pidió info detallada, está tibio
   │
   ▼
NEGOCIO EN PROPUESTA (etapa "proposal")
   │  Le mandaste cotización formal
   │  Se puede convertir en COTIZACIÓN
   │
   ▼
NEGOCIO EN NEGOCIACIÓN (etapa "negotiation")
   │  Está negociando precio, plazo, condiciones
   │
   ├── GANADO (etapa "won")   → Cliente firmado, listo para cobrar
   │       Se mueve a COTIZACIÓN → VENTA
   │
   └── PERDIDO (etapa "lost") → No se cerró, queda el motivo
           Aprendemos del motivo para no repetir errores
```

---

## Parte 2: Las 7 etapas en detalle

### Etapa 1 — NUEVO (`new`)

**Qué significa**: alguien preguntó por algo, pero todavía NO le contestaste
o ni siquiera miraste.

**Qué hacés vos**:

- Abrís `/crm/leads/create`
- Llenás: cliente, qué le interesa, monto estimado (si sabés),
  fuente (web, referido, etc.)
- Guardás → aparece en `/crm/leads`

**Datos típicos**:

- Cliente: Juan Pérez (preguntó por WhatsApp)
- Detalle: "Cotización 10 laptops"
- Monto: S/ 15,000
- Fuente: web

**Qué NO tiene todavía**: ninguna actividad registrada.

---

### Etapa 2 — CONTACTADO (`contacted`)

**Qué significa**: ya le escribiste, le mandaste info, o lo llamaste.
**No necesariamente respondió**.

**Qué hacés vos**:

- Click en "Nota" → escribís *"Le mandé catálogo por WhatsApp"*
- O click en "Llamada" → escribís *"Llamé, no contestó, vuelvo mañana"*

**El sistema automáticamente**:

- Registra la actividad con tu nombre y la fecha/hora
- Actualiza `last_activity_at` a ahora

---

### Etapa 3 — INTERESADO (`interested`)

**Qué significa**: el cliente respondió, preguntó más, mostró interés real.
Está **tibio**, no frío.

**Qué hacés vos**:

- Click en "Tarea" → agendás seguimiento con fecha:
  - *"Llamar el viernes para confirmar pedido"* → vence viernes →
    prioridad alta
- O agregás nota: *"Pidió precio por 15 unidades"*

**Lo importante**: si la tarea vence y no la completás, **cuenta en
"Tareas vencidas"** del dashboard (banner rojo).

---

### Etapa 4 — PROPUESTA (`proposal`)

**Qué significa**: ya le enviaste una **cotización formal** o propuesta seria.
Es un negocio real, no un lead tibio.

**Qué hacés vos**:

- Click en **"Crear cotización"** desde el detalle de la oportunidad
- Te redirige al formulario de cotización con cliente + productos
  YA prellenados desde la oportunidad
- Completás: cantidades, precios finales, condiciones de pago
- Guardás → la cotización queda **vinculada** a la oportunidad

**El sistema automáticamente**:

- Crea una actividad `quotation` en el timeline:
  *"Redirigido a crear cotización"*
- La oportunidad queda asociada con la cotización

---

### Etapa 5 — NEGOCIACIÓN (`negotiation`)

**Qué significa**: el cliente tiene tu cotización y está negociando
(precio, plazo, condiciones, forma de pago).

**Qué hacés vos**:

- Agregás notas con cada conversación:
  *"Quiere 5% descuento por pago al contado"*
- Agendás tareas de seguimiento
- Si cambian condiciones, podés **actualizar la cotización** y volver
  a mandarla

**No hay un botón automático de "mover a negociación"** — vos lo cambiás
manualmente cuando corresponda.

---

### Etapa 6 — GANADO (`won`)

**Qué significa**: ¡cerraste! El cliente firmó, confirmó, o aceptó la
propuesta.

**Qué hacés vos**:

- Click en **"Ganado"** (botón verde)
- Instantáneamente:
  - `won_at` se setea con la fecha/hora actual
  - Queda registrado en "Ganadas este mes" del dashboard

**Después**:

- Convertís la cotización en NOTA DE VENTA o COMPROBANTE (factura/boleta)
- Empieza el ciclo de **venta → facturación → cobranza**

---

### Etapa 7 — PERDIDO (`lost`)

**Qué significa**: no se cerró. El cliente eligió otro proveedor, no tenía
presupuesto, se cayó, etc.

**Qué hacés vos**:

- Click en **"Perdido"** (botón rojo a la derecha)
- Aparece un modal pidiendo **motivo**
- Escribís: *"Cliente eligió competitor con precio 20% menor"*
- Confirmás

**El sistema automáticamente**:

- `lost_at` se setea con la fecha/hora
- El motivo queda guardado en la actividad del timeline
- Aparece en "Perdidas este mes" del dashboard

**No es el fin**: el cliente queda en la base para **futuro contacto**
(Q1, próximo año, etc).

---

## Parte 3: Cómo se conectan los sub-módulos de Preventa

El menú **"Preventa"** en el sidebar contiene varios items. Así se
relacionan:

```
┌─────────────────────────────────────────────────────────────┐
│                    MÓDULO PREVENTA                            │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Panel CRM (dashboard)                                       │
│     Resumen general: 6 KPIs en 3 secciones                  │
│     Es el CENTRO: ves todo de un vistazo                     │
│                                                              │
│  Leads                                                       │
│     Opportunities con stage ∈ {new, contacted, interested}   │
│     Personas frías/tibias que todavía no compraron            │
│                                                              │
│  Oportunidades de venta                                      │
│     Opportunities con TODOS los stages (también leads)        │
│     Incluye won + lost                                       │
│     Es donde ves el funnel completo                          │
│                                                              │
│  Seguimientos (no implementado todavía)                       │
│     Vista agrupada de actividades tipo "tarea" pendientes    │
│                                                              │
│  Cotizaciones                                                 │
│     Quotations creadas desde oportunidad o standalone        │
│     Tienen FK sale_opportunity_id (vinculadas)               │
│                                                              │
│  Oportunidad de venta (el registro base)                     │
│     Es el MODELO de todo — todos los demás cuelgan de acá    │
│                                                              │
│  Contratos                                                   │
│     Siguen a la cotización cuando se cierra                  │
│                                                              │
│  Pedidos                                                     │
│     Se generan desde contrato o cotización                   │
│                                                              │
│  Servicio técnico                                            │
│     Post-venta, mantenimiento, garantía                      │
│                                                              │
│  Comisiones                                                  │
│     Pagos al vendedor, basadas en oportunidad ganada          │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Flujo de datos entre módulos (simplificado)

```
1. PROSPECTO pregunta
   └─> Vos creás OPORTUNIDAD DE VENTA con stage=new
       (esto lo hacés en /crm/leads/create)

2. Le escribís → stage="contacted"
   └─> Cada nota/llamada/tarea crea una fila en crm_activities

3. Muestra interés → stage="interested"
   └─> Agendás tarea con fecha de vencimiento

4. Le mandás propuesta → stage="proposal"
   └─> Click "Crear cotización" → abre form de Cotización
       └─> Cotización queda con FK sale_opportunity_id

5. Negocian → stage="negotiation"
   └─> Notas con cada ida y vuelta

6a. CIERRA → stage="won" + won_at=now()
   └─> Cotización → Nota de venta → Comprobante (factura/boleta)
       └─> Comisiones se calculan para el vendedor

6b. NO cierra → stage="lost" + lost_at=now() + motivo
   └─> Queda en base para re-contacto futuro
```

---

## Glosario rápido

| Término                | Qué es                                                                       |
| ---------------------- | ---------------------------------------------------------------------------- |
| **Lead**               | Prospecto tibio/frío, todavía no compró                                     |
| **Oportunidad de venta** | Registro formal del posible negocio (puede ser lead o deal)               |
| **Deal / Negocio**     | Oportunidad en etapa avanzada (proposal/negotiation)                        |
| **Stage**              | Etapa del pipeline (new → contacted → interested → proposal → negotiation → won/lost) |
| **Cotización**         | Documento formal con precios, plazo, condiciones                            |
| **Timeline**           | Lista cronológica de actividades de la oportunidad                          |
| **Actividad**          | Nota, llamada, tarea, cambio de estado, intento de cotización               |
| **Tarea**              | Actividad con fecha de vencimiento (puede ser "vencida" si pasa)            |
| **Won**                | Ganado, cerrado, venta concretada                                           |
| **Lost**               | Perdido, no se cerró, queda motivo                                          |
| **Preventa**           | Etapa comercial antes de la facturación                                    |
| **Multi-tenant**       | Cada cliente (sitio) tiene su propia base de datos aislada                  |
| **KPI**                | Indicador numérico (Leads nuevos, Tareas vencidas, etc)                     |

---

## Resumen ejecutivo

> **Lead entra como oportunidad → stage se mueve según tu gestión → gana o pierde → si gana, sigue a cotización → venta → comisión al vendedor.**

---

## Ver también

- `sdd/facturador8/crm-comercial-kiss/explore` — investigación previa
- `sdd/facturador8/crm-comercial-kiss/proposal` — propuesta aprobada
- `sdd/facturador8/crm-comercial-kiss/spec` — especificación funcional
- `sdd/facturador8/crm-comercial-kiss/design` — diseño técnico
- `sdd/facturador8/crm-comercial-kiss/tasks` — checklist de implementación
- `sdd/facturador8/crm-comercial-kiss/verify` — verificación final
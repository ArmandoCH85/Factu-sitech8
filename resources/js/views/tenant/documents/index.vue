<template>
    <div class="documents">
        <div class="page-header pe-0">
            <h2>
                <a href="/documents">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        style="margin-top: -5px;"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="feather feather-file-text"
                    >
                        <path
                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                        ></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Listado de comprobantes</span></li>
                <!-- <li><span class="text-muted">Facturas - Notas <small>(crédito y débito)</small> - Boletas - Anulaciones</span></li> -->
            </ol>
            <div
                class="right-wrapper pull-right"
                v-if="typeUser != 'integrator'"
            >
                <span v-if="import_documents == true">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm  mt-2 me-2"
                        @click.prevent="clickImport()"
                    >
                        <i class="fa fa-upload"></i> Importar Formato 1
                    </button>
                </span>
                <span v-if="import_documents_second == true">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm  mt-2 me-2"
                        @click.prevent="clickImportSecond()"
                    >
                        <i class="fa fa-upload"></i> Importar Formato 2
                    </button>
                </span>
                <span v-if="document_import_excel">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm  mt-2 me-2"
                        @click.prevent="clickImportExcel"
                    >
                        <i class="fa fa-upload"></i> Importar Formato
                    </button>
                </span>
                <a
                    :href="`/${resource}/create`"
                    class="btn btn-custom btn-sm  mt-2 me-2"
                    ><i class="fa fa-plus-circle"></i> Nuevo</a
                >
                <div class="btn-group flex-wrap dropdown">
                    <button
                        type="button"
                        class="btn btn-custom btn-sm  mt-2 me-2 dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="fa fa-money-bill-wave-alt"></i> Reporte de
                        Pagos <span class="caret"></span>
                    </button>
                    <!-- validadores apiperu  -->
                    <a
                        href="#"
                        @click.prevent="showDialogApiPeruDevValidate = true"
                        v-if="view_apiperudev_validator_cpe"
                        class="btn btn-custom btn-sm  mt-2 me-2"
                        ><i class="fa fa-check"></i> Validación masiva</a
                    >
                    <a
                        href="#"
                        @click.prevent="showDialogValidate = true"
                        v-if="view_validator_cpe"
                        class="btn btn-custom btn-sm  mt-2 me-2"
                        ><i class="fa fa-file"></i> Validar CPE</a
                    >

                    <div
                        class="dropdown-menu"
                        role="menu"
                        x-placement="bottom-start"
                        style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 42px, 0px);"
                    >
                        <a
                            class="dropdown-item text-1"
                            href="#"
                            @click.prevent="clickReportPayments()"
                            >Generar Reporte</a
                        >
                        <a
                            class="dropdown-item text-1"
                            href="#"
                            @click.prevent="clickDownloadReportPagos()"
                            >Descargar Excel</a
                        >
                    </div>
                </div>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <!--
            <div class="data-table-visible-columns">

                <el-dropdown :hide-on-click="false">
                    <el-button type="primary">
                        Mostrar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item v-for="(column, index) in columns"
                                          :key="index">
                            <el-checkbox v-model="column.visible">{{ column.title }}</el-checkbox>
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
            -->
            <div class="card-body card-body-invoice">
                <div class="data-table-visible-columns">
                    <el-dropdown :hide-on-click="false" slot="showhide">
                        <el-button type="secondary">
                            Mostrar columnas<i
                                class="el-icon-arrow-down el-icon--right"
                            ></i>
                        </el-button>
                        <el-dropdown-menu slot="dropdown" style="min-width: 220px;">
                            <div style="max-height: 520px; overflow-y: auto;">
                                <el-dropdown-item divided disabled v-if=" customFieldColumns.length > 0 ">
                                    <strong>Campos personalizados</strong>
                                </el-dropdown-item>
                                <el-dropdown-item
                                    v-for="field in customFieldColumns"
                                    :key="`custom-field-${field.id}`"

                                >
                                    <el-checkbox
                                        @change="updateCustomFieldColumns()"
                                        v-model="field.visible"
                                        >{{ field.name }}</el-checkbox
                                    >
                                </el-dropdown-item>
                                <el-dropdown-item divided v-if=" customFieldColumns.length > 0 "></el-dropdown-item>
                                <el-dropdown-item disabled>
                                    <strong>Seleccionar columnas</strong>
                                </el-dropdown-item>
                                <el-dropdown-item
                                    v-for="(column, index) in columns"
                                    :key="index"
                                >
                                    <el-checkbox
                                        @change="getColumnsToShow(1)"
                                        v-model="column.visible"
                                        >{{ column.title }}</el-checkbox
                                    >
                                </el-dropdown-item>
                            </div>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
                <data-table :resource="resource">
                    <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th v-if="columns.soap_type.visible">SOAP</th>
                        <th class="text-start" style="min-width: 95px;" v-if="columns.date_of_issue.visible">
                            Emisión
                        </th>
                        <th
                            v-if="columns.date_payment.visible"
                            class="text-center"
                            style="min-width: 95px;"
                        >
                            Fecha de pago
                        </th>
                        <th
                            class="text-center"
                            v-if="columns.date_of_due.visible"
                        >
                            Fecha Vencimiento
                        </th>
                        <th v-if="columns.customer.visible">Cliente</th>
                        <th v-if="columns.number.visible">Número</th>
                        <th v-if="columns.notes.visible">Notas C/D</th>
                        <th v-if="columns.dispatch.visible">
                            Guía de Remisión
                        </th>
                        <th v-if="columns.sales_note.visible">Nota de venta</th>
                        <th v-if="columns.order_note.visible">Pedidos</th>
                        <th v-if="columns.send_it.visible">Email Enviado</th>
                        <th v-if="columns.state_type.visible">Estado</th>
                        <th
                            v-for="field in customFieldColumns"
                            :key="field.id"
                            class="text-start"
                            v-if="field.visible"
                        >
                            {{ field.name }}
                        </th>
                        <th v-if="columns.user_name.visible">Usuario</th>
                        <th
                            class="text-end"
                            v-if="columns.exchange_rate_sale.visible"
                        >
                            T.C.
                        </th>
                        <th
                            class="text-center"
                            v-if="columns.currency_type_id.visible"
                        >
                            Moneda
                        </th>
                        <th class="text-end" v-if="columns.guides.visible">
                            Guia
                        </th>

                        <th
                            class="text-center"
                            v-if="columns.plate_numbers.visible"
                        >
                            Placa
                        </th>

                        <th
                            class="text-end"
                            v-if="columns.total_exportation.visible"
                        >
                            T.Exportación
                        </th>
                        <th
                            class="text-end"
                            v-if="columns.total_free.visible"
                        >
                            T.Gratuita
                        </th>
                        <th
                            class="text-end"
                            v-if="columns.total_unaffected.visible"
                        >
                            T.Inafecta
                        </th>
                        <th
                            class="text-end"
                            v-if="columns.total_exonerated.visible"
                        >
                            T.Exonerado
                        </th>
                        <th
                            class="text-end"
                            v-if="columns.total_charge.visible"
                        >
                            {{ columns.total_charge.title }}
                        </th>
                        <th class="text-end" v-if="columns.total_taxed.visible">T.Gravado</th>
                        <th class="text-end" v-if="columns.total_igv.visible">T.Igv</th>
                        <th class="text-end" v-if="columns.total.visible">
                            Total
                        </th>
                        <th class="text-end" v-if="columns.balance.visible">
                            Saldo
                        </th>
                        <th
                            class="text-center"
                            style="min-width: 95px;"
                            v-if="columns.purchase_order.visible"
                        >
                            Orden de compra
                        </th>
                        <th class="text-center" v-if="columns.downloads.visible"></th>
                        <th class="text-end" v-if="typeUser != 'integrator' && columns.actions.visible"></th>
                    </tr>
                    <tr
                        slot-scope="{ index, row }"
                        :class="{
                            'anulate_color': row.state_type_id === '11',
                            'text-warning': row.state_type_id === '13',
                            'border-light': row.state_type_id === '01',
                            'border-left border-info':
                                row.state_type_id === '03',
                            'border-left border-success':
                                row.state_type_id === '05',
                            'border-left border-secondary':
                                row.state_type_id === '07',
                            'border-left border-dark':
                                row.state_type_id === '09',
                            'border-left border-danger':
                                row.state_type_id === '11',
                            'border-left border-warning':
                                row.state_type_id === '13'
                        }"
                    >
                        <!-- <td>{{ index }}</td> -->
                        <td v-if="columns.soap_type.visible">
                            {{ row.soap_type_description }}
                        </td>
                        <td class="text-start" v-if="columns.date_of_issue.visible">{{ row.date_of_issue }}</td>
                        <td
                            class="text-center"
                            v-if="columns.date_payment.visible"
                        >
                            {{ formatDate(row.date_of_payment) }}
                        </td>
                        <td
                            class="text-center"
                            :class="{
                                'text-danger':
                                    row.balance > 0 &&
                                    isDateWarning(row.date_of_due)
                            }"
                            v-if="columns.date_of_due.visible"
                        >
                            {{ row.date_of_due }}
                        </td>
                        <td v-if="columns.customer.visible">
                            {{ row.customer_name }}<br /><small
                                v-text="row.customer_number"
                            ></small>
                        </td>
                        <td v-if="columns.number.visible">
                            {{ row.number }}<br />
                            <small
                                v-text="row.document_type_description"
                            ></small
                            ><br />
                            <small
                                v-if="row.affected_document"
                                v-text="row.affected_document"
                            ></small>
                        </td>
                        <td v-if="columns.notes.visible">
                            <template v-for="(row, index) in row.notes">
                                <label class="d-block :key="index"
                                    >{{ row.note_type_description }}:
                                    {{ row.description }}</label
                                >
                            </template>
                        </td>

                        <!-- <td v-if="columns.notes.visible">
                            <template v-for="(row, index) in row.notes">
                                <label class="d-block" :key="index"
                                    >{{ row.note_type_description }}:
                                    {{ row.description }}</label
                                >
                            </template>
                        </td> -->

                        <td v-if="columns.dispatch.visible">
                            <template v-for="(dispatch, index) in row.dispatches">
                                <label class="d-block" :key="index">{{
                                    dispatch.description
                                }}</label>
                            </template>
                        </td>

                        <!-- <td v-if="columns.dispatch.visible">
                            <template v-for="(row, index) in row.dispatches">
                                <label class="d-block" :key="index">{{
                                    row.description
                                }}</label>
                            </template>
                        </td> -->
                        
                        <td v-if="columns.sales_note.visible">
                            <template v-for="(salesNote, index) in row.sales_note">
                                <label class="d-block" :key="index"
                                    >{{ salesNote.number_full }} ({{
                                        salesNote.state_type_description
                                    }})</label
                                >
                            </template>
                        </td>

                        <!-- <td v-if="columns.sales_note.visible">
                            <template v-for="(row, index) in row.sales_note">
                                <label class="d-block" :key="index"
                                    >{{ row.number_full }} ({{
                                        row.state_type_description
                                    }})</label
                                >
                            </template>
                        </td> -->

                        <td v-if="columns.order_note.visible">
                            <template
                                v-if="
                                    row.order_note && row.order_note.identifier
                                "
                            >
                                {{ row.order_note.identifier }}
                            </template>
                        </td>
                        <td v-if="columns.send_it.visible">
                            <!--
                            <el-tooltip
                                        class="item"
                                        effect="dark"
                                        placement="bottom">
                                <div slot="content">
                                    <span v-for="(item, i) in row.email_send_it_array"
                                          :key="i">
                                        {{ (item.email_send_it === false)?'No enviado':'Enviado' }} - {{ item.email }}  - {{ item.send_date }} <br>
                                    </span>
                                </div>
                                <span class="badge "
                                      :class="
                                      {'text-danger': (row.email_send_it === false), 'text-success': (row.email_send_it === true), }">
                                    <i class="fas fa-lg"
                                       :class="{ 'fa-times': (row.email_send_it === false), 'fa-check': (row.email_send_it === true), }"
                                    ></i>
                                </span>
                            </el-tooltip>-->

                            <span
                                class="badge "
                                :class="{
                                    'text-muted': row.email_send_it === false,
                                    'text-success': row.email_send_it === true
                                }"
                            >
                                <i
                                    class="fas fa-lg"
                                    :class="{
                                        'fa-minus': row.email_send_it === false,
                                        'fa-check': row.email_send_it === true
                                    }"
                                ></i>
                            </span>
                        </td>

                        <td v-if="columns.state_type.visible">
                            <el-tooltip
                                v-if="tooltip(row, false)"
                                class="item"
                                effect="dark"
                                placement="bottom"
                            >
                                <div slot="content">{{ tooltip(row) }}</div>
                                <span
                                    class="badge bg-secondary text-white"
                                    :class="{
                                        'bg-danger': row.state_type_id === '11',
                                        'bg-warning':
                                            row.state_type_id === '13',
                                        'bg-secondary':
                                            row.state_type_id === '01',
                                        'bg-info': row.state_type_id === '03',
                                        'bg-success':
                                            row.state_type_id === '05',
                                        'bg-secondary':
                                            row.state_type_id === '07',
                                        'bg-dark': row.state_type_id === '09'
                                    }"
                                >
                                    {{ row.state_type_description }}
                                </span>
                            </el-tooltip>
                            <span
                                v-else
                                class="badge bg-secondary text-white"
                                :class="{
                                    'bg-danger': row.state_type_id === '11',
                                    'bg-warning': row.state_type_id === '13',
                                    'bg-secondary': row.state_type_id === '01',
                                    'bg-info': row.state_type_id === '03',
                                    'bg-success': row.state_type_id === '05',
                                    'bg-secondary': row.state_type_id === '07',
                                    'bg-dark': row.state_type_id === '09'
                                }"
                            >
                                {{ row.state_type_description }}
                            </span>
                            <a
                                v-if="row.state_type_id === '13'"
                                href="voided"
                                class="small"
                                ><br />Ir a anulaciones</a
                            >
                            <template
                                v-if="
                                    row.regularize_shipping &&
                                        row.state_type_id === '01'
                                "
                            >
                                <el-tooltip
                                    class="item"
                                    effect="dark"
                                    :content="row.message_regularize_shipping"
                                    placement="top-start"
                                >
                                    <i
                                        class="fas fa-exclamation-triangle fa-lg"
                                        style="color: #D2322D !important"
                                    ></i>
                                </el-tooltip>
                            </template>
                        </td>
                         <td
                            v-for="field in customFieldColumns"
                            :key="field.id"
                            class="text-start"
                            v-if="field.visible"
                        >
                            <template v-if="isEditableCustomField(field)">
                                <template v-if="field.type === 'text'">
                                    <el-input
                                        v-model="row.custom_fields_data[field.slug]"
                                        @blur="saveCustomFieldValue(row, field)"
                                        size="small"
                                        :placeholder="field.name"
                                    ></el-input>
                                </template>
                                <template v-else-if="field.type === 'number'">
                                    <el-input
                                        v-model.number="row.custom_fields_data[field.slug]"
                                        type="number"
                                        @blur="saveCustomFieldValue(row, field)"
                                        size="small"
                                        :placeholder="field.name"
                                    ></el-input>
                                </template>
                                <template v-else-if="field.type === 'textarea'">
                                    <el-input
                                        v-model="row.custom_fields_data[field.slug]"
                                        type="textarea"
                                        :rows="2"
                                        @blur="saveCustomFieldValue(row, field)"
                                        size="small"
                                        :placeholder="field.name"
                                    ></el-input>
                                </template>
                                <template v-else-if="field.type === 'select'">
                                    <el-select
                                        v-model="row.custom_fields_data[field.slug]"
                                        @change="saveCustomFieldValue(row, field)"
                                        size="small"
                                        clearable
                                        :placeholder="field.name"
                                    >
                                        <el-option
                                            v-for="option in normalizeOptions(field.options)"
                                            :key="option"
                                            :label="option"
                                            :value="option"
                                        ></el-option>
                                    </el-select>
                                </template>
                                <template v-else-if="field.type === 'checkbox'">
                                    <el-checkbox-group
                                        v-model="row.custom_fields_data[field.slug]"
                                        @change="saveCustomFieldValue(row, field)"
                                    >
                                        <el-checkbox
                                            v-for="option in normalizeOptions(field.options)"
                                            :key="option"
                                            :label="option"
                                            :value="option"
                                        >
                                            {{ option }}
                                        </el-checkbox>
                                    </el-checkbox-group>
                                </template>
                                <template v-else-if="field.type === 'date'">
                                    <el-date-picker
                                        v-model="row.custom_fields_data[field.slug]"
                                        type="date"
                                        format="yyyy-MM-dd"
                                        value-format="yyyy-MM-dd"
                                        @change="saveCustomFieldValue(row, field)"
                                        size="small"
                                        :placeholder="field.name"
                                    ></el-date-picker>
                                </template>
                                <template v-else>
                                    {{ formatCustomFieldValue(row.custom_fields_data[field.slug]) }}
                                </template>
                            </template>
                            <template v-else>
                                {{ formatCustomFieldValue(row.custom_fields_data[field.slug]) }}
                            </template>
                        </td>
                        <td v-if="columns.user_name.visible">
                            {{ row.user_name }}
                            <br /><small v-text="row.user_email"></small>
                        </td>
                        <td v-if="columns.exchange_rate_sale.visible">
                            {{ row.exchange_rate_sale }}
                        </td>
                        <td
                            class="text-center"
                            v-if="columns.currency_type_id.visible"
                        >
                            {{ row.currency_type_id }}
                        </td>
                        <td class="text-center" v-if="columns.guides.visible">
                            <span v-for="(item, i) in row.guides" :key="i">
                                {{ item.number }} <br />
                            </span>
                        </td>

                        <td
                            class="text-center"
                            v-if="columns.plate_numbers.visible"
                        >
                            <span
                                v-for="(item, i) in row.plate_numbers"
                                :key="i"
                            >
                                {{ item.description }} <br />
                            </span>
                        </td>

                        <td
                            class="text-end"
                            v-if="columns.total_exportation.visible"
                        >
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total_exportation) }}
                        </td>

                        <td
                            class="text-end"
                            v-if="columns.total_free.visible"
                        >
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total_free) }}
                        </td>

                        <td
                            class="text-end"
                            v-if="columns.total_unaffected.visible"
                        >
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total_unaffected) }}
                        </td>
                        <td
                            class="text-end"
                            v-if="columns.total_exonerated.visible"
                        >
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total_exonerated) }}
                        </td>
                        <td
                            class="text-end"
                            v-if="columns.total_charge.visible"
                        >
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total_charge) }}
                        </td>
                        <td class="text-end" v-if="columns.total_taxed.visible">
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total_taxed) }}</td>
                        <td class="text-end" v-if="columns.total_igv.visible">
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total_igv) }}</td>
                        <td class="text-end" v-if="columns.total.visible">
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.total) }}
                        </td>

                        <td
                            class="text-end"
                            v-if="columns.balance.visible"
                            :class="{
                                'text-warning': row.balance > 0,
                                'text-success': row.balance == 0
                            }"
                        >
                            {{row.currency_type_id === 'PEN' ? 'S/' : '$'}}
                            {{ formatDecimal(row.balance) }}
                        </td>
                        <td v-if="columns.purchase_order.visible">
                            {{ row.purchase_order }}
                        </td>
                        <td class="text-center" v-if="columns.downloads.visible">
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn waves-effect waves-light btn-xs btn-info m-1__2 me-2"
                                @click.prevent="clickDownload(row.download_xml)"
                                v-if="row.has_xml"
                            >
                                XML
                            </button>
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn waves-effect waves-light btn-xs btn-info m-1__2 me-2"
                                @click.prevent="clickDownload(row.download_pdf)"
                                v-if="row.has_pdf"
                            >
                                PDF
                            </button>
                            <button
                                type="button"
                                style="min-width: 41px"
                                class="btn waves-effect waves-light btn-xs btn-info m-1__2 me-2"
                                @click.prevent="clickDownload(row.download_cdr)"
                                v-if="row.has_cdr"
                            >
                                CDR
                            </button>
                        </td>

                        <td class="text-end" v-if="typeUser != 'integrator' && columns.actions.visible">
                            <el-dropdown trigger="click" size="small">
                                <el-button class="btn-dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                    <i class="fas fa-ellipsis-h" style="display: none;"></i>
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                  <el-dropdown-item
                                    v-if="configuration.permission_to_edit_cpe && row.state_type_id === '01' && userPermissionEditCpe && row.is_editable"
                                  >
                                    <a :href="`/documents/${row.id}/edit`" style="text-decoration: none; color: inherit;">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                      Editar
                                    </a>
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-else-if="row.state_type_id === '01' && userId == row.user_id && row.is_editable"
                                  >
                                    <a :href="`/documents/${row.id}/edit`" style="text-decoration: none; color: inherit;">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                      Editar
                                    </a>
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_resend && !isClient"
                                    @click.native="clickResend(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-forward-up me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 14l4 -4l-4 -4" /><path d="M19 10h-11a4 4 0 1 0 0 8h1" /></svg>
                                    Reenviar
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_recreate_document"
                                    @click.native="clickReStore(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-refresh-cw me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                                    Volver a recrear
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_change_to_registered_status"
                                    @click.native="clickChangeToRegisteredStatus(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-check-circle me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12l2 2l4 -4" /><circle cx="12" cy="12" r="9" /></svg>
                                    Cambiar a estado registrado
                                  </el-dropdown-item>

                                  <el-dropdown-item
                                      divided
                                      v-if="
                                          row.btn_change_to_registered_status ||
                                          row.btn_recreate_document ||
                                          (row.btn_resend && !isClient) ||
                                          (
                                              configuration.permission_to_edit_cpe &&
                                              row.state_type_id === '01' &&
                                              userPermissionEditCpe &&
                                              row.is_editable
                                          ) ||
                                          (
                                              row.state_type_id === '01' &&
                                              userId == row.user_id &&
                                              row.is_editable
                                          )
                                      "
                                  ></el-dropdown-item>
                    
                                  <el-dropdown-item v-if="row.btn_note">
                                    <a :href="`/${resource}/note/${row.id}`" style="text-decoration: none; color: inherit;">
                                      <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4v17l2 -2l2 2l2 -2l2 2l2 -2l2 2l2 -2v-17z"></path><path d="M14 8h-4"></path><path d="M14 12h-4"></path><path d="M14 16h-4"></path></svg>
                                      Nota
                                    </a>
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item v-if="row.btn_guide">
                                    <a :href="`/dispatches/create_new/document/${row.id}`" style="text-decoration: none; color: inherit;">
                                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-truck me-2">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"></path>
                                      </svg>
                                      Guía
                                    </a>
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_constancy_detraction"
                                    @click.native="clickCDetraction(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-text me-2">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                      <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                                      <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
                                      <line x1="9" y1="9" x2="10" y2="9"></line>
                                      <line x1="9" y1="13" x2="15" y2="13"></line>
                                      <line x1="9" y1="17" x2="15" y2="17"></line>
                                    </svg>
                                    C. Detracción
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="isClient && !row.send_server"
                                    @click.native="clickSendOnline(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-server-2 me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M3 12m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M7 8l0 .01" /><path d="M7 16l0 .01" /><path d="M11 8h6" /><path d="M11 16h6" /></svg>
                                    Enviar Servidor
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="isClient && row.send_server && (row.state_type_id === '01' || row.state_type_id === '03')"
                                    @click.native="clickCheckOnline(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-server-cog me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M12 20h-6a3 3 0 0 1 -3 -3v-2a3 3 0 0 1 3 -3h10.5" /><path d="M18 18m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M18 14.5v1.5" /><path d="M18 20v1.5" /><path d="M21.032 16.25l-1.299 .75" /><path d="M16.27 19l-1.3 .75" /><path d="M14.97 16.25l1.3 .75" /><path d="M19.733 19l1.3 .75" /><path d="M7 8v.01" /><path d="M7 16v.01" /></svg>
                                    Consultar Servidor
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item 
                                    divided
                                    v-if="row.btn_note || row.btn_guide || row.btn_constancy_detraction ||
                                     (isClient && !row.send_server) ||
                                      (isClient && row.send_server && (row.state_type_id === '01' || row.state_type_id === '03'))"
                                  />
                              
                                  <el-dropdown-item
                                    @click.native="clickPayment(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-cash me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 15h-3a1 1 0 0 1 -1 -1v-8a1 1 0 0 1 1 -1h12a1 1 0 0 1 1 1v3" /><path d="M7 9m0 1a1 1 0 0 1 1 -1h12a1 1 0 0 1 1 1v8a1 1 0 0 1 -1 1h-12a1 1 0 0 1 -1 -1z" /><path d="M12 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /></svg>
                                    Pagos
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_retention"
                                    @click.native="clickRetention(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-text me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M14 3v4a1 1 0 0 0 1 1h4"></path><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path><line x1="9" y1="9" x2="10" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line><line x1="9" y1="17" x2="15" y2="17"></line></svg>
                                    Retención
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item divided />
                              
                                  <el-dropdown-item
                                    @click.native="clickOptions(row.id)"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                    Opciones
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_voided"
                                    @click.native="clickVoided(row.id)"
                                    class="text-danger option-delete"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x-circle me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><line x1="15" y1="9" x2="9" y2="15" /><line x1="9" y1="9" x2="15" y2="15" /></svg>
                                    Anular
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_delete_doc_type_03"
                                    @click.native="clickDeleteDocument(row.id)"
                                    class="text-danger option-delete"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    Eliminar
                                  </el-dropdown-item>
                              
                                  <el-dropdown-item
                                    v-if="row.btn_force_send_by_summary && typeUser === 'admin'"
                                    @click.native="clickForceSendBySummary(row.id)"
                                    class="text-warning"
                                  >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-send me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="10" y1="14" x2="21" y2="3" /><path d="M21 3l-6.5 18a0.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a0.55 .55 0 0 1 0 -1l18 -6.5" /></svg>
                                    Enviar por resumen
                                  </el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                            <!-- funciona pero con funciones para cada boton, parametro command -->
                            <!-- <el-dropdown trigger="click" size="small">
                                <el-button size="mini" type="default" class="el-dropdown-selfdefine">
                                    <i class="fas fa-ellipsis-v"></i>
                                </el-button>
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item v-if="row.btn_recreate_document">Recrear</el-dropdown-item>
                                    <el-dropdown-item>Action 2</el-dropdown-item>
                                    <el-dropdown-item>Action 3</el-dropdown-item>
                                    <el-dropdown-item>Action 4</el-dropdown-item>
                                    <el-dropdown-item>Action 5</el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown> -->

                            <!-- <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-danger m-1__2"
                                    @click.prevent="clickDeleteDocument(row.id)"
                                    v-if="row.btn_delete_doc_type_03">
                                Eliminar
                            </button>
                            <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickChangeToRegisteredStatus(row.id)"
                                    v-if="row.btn_change_to_registered_status">
                                Cambiar a estado registrado
                            </button>
                            <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickReStore(row.id)"
                                    v-if="row.btn_recreate_document">
                                Volver a recrear
                            </button>
                            <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-danger m-1__2"
                                    @click.prevent="clickVoided(row.id)"
                                    v-if="row.btn_voided">
                                Anular
                            </button>
                            <a :href="`/${resource}/note/${row.id}`"
                               class="btn waves-effect waves-light btn-xs btn-warning m-1__2"
                               v-if="row.btn_note">
                                Nota
                            </a>
                            <a :href="`/dispatches/create/${row.id}`"
                               class="btn waves-effect waves-light btn-xs btn-warning m-1__2"
                               v-if="row.btn_guide">
                                Guía
                            </a>
                            <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickResend(row.id)"
                                    v-if="row.btn_resend && !isClient">
                                Reenviar
                            </button>
                            <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickSendOnline(row.id)"
                                    v-if="isClient && !row.send_server">
                                Enviar Servidor
                            </button>
                            <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickCheckOnline(row.id)"
                                    v-if="isClient && row.send_server && (row.state_type_id === '01' || row.state_type_id === '03')">
                                Consultar Servidor
                            </button>
                            <button type="button"
                                    class="btn waves-effect waves-light btn-xs btn-info m-1__2"
                                    @click.prevent="clickOptions(row.id)">
                                Opciones
                            </button>
                            <button type="button"
                                    v-if="row.btn_constancy_detraction"
                                    class="btn waves-effect waves-light btn-xs btn-success m-1__2"
                                    @click.prevent="clickCDetraction(row.id)">
                                C. Detracción
                            </button> -->
                        </td>
                    </tr>
                </data-table>
            </div>

            <documents-voided
                :showDialog.sync="showDialogVoided"
                :recordId="recordId"
            ></documents-voided>

            <items-import :showDialog.sync="showImportDialog"></items-import>

            <document-import-second
                :showDialog.sync="showImportSecondDialog"
            ></document-import-second>

            <document-options
                :showDialog.sync="showDialogOptions"
                :recordId="recordId"
                :showClose="true"
                :configuration="configuration"
            ></document-options>

            <document-payments
                :showDialog.sync="showDialogPayments"
                :documentId="recordId"
            ></document-payments>

            <document-constancy-detraction
                :showDialog.sync="showDialogCDetraction"
                :recordId="recordId"
            ></document-constancy-detraction>
            <report-payment
                :showDialog.sync="showDialogReportPayment"
            ></report-payment>

            <report-payment-complete
                :showDialog.sync="showDialogReportPaymentComplete"
            ></report-payment-complete>

            <DocumentValidate
                :showDialogValidate.sync="showDialogValidate"
            ></DocumentValidate>

            <massive-validate-cpe
                :showDialogValidate.sync="showDialogApiPeruDevValidate"
            ></massive-validate-cpe>

            <document-import-excel
                :showDialog.sync="showImportExcelDialog"
            ></document-import-excel>

            <document-retention
                :showDialog.sync="showDialogRetention"
                :documentId="recordId"
            ></document-retention>
        </div>
    </div>
</template>
<style>
.dropdown-menu.show {
    display: block;
    max-height: 140px;
    overflow-y: auto;
}
</style>
<script>
import DocumentsVoided from "./partials/voided.vue";
import DocumentOptions from "./partials/options.vue";
import DocumentPayments from "./partials/payments.vue";
import DocumentImportSecond from "./partials/import_second.vue";
import DocumentImportExcel from "./partials/ImportExcel.vue";
import DataTable from "../../../components/DataTableDocuments.vue";
import ItemsImport from "./import.vue";
import { deletable } from "../../../mixins/deletable";
import DocumentConstancyDetraction from "./partials/constancy_detraction.vue";
import ReportPayment from "./partials/report_payment.vue";
import ReportPaymentComplete from "./partials/report_payment_complete.vue";
import DocumentValidate from "./partials/validate.vue";
import MassiveValidateCpe from "../../../../../modules/ApiPeruDev/Resources/assets/js/components/MassiveValidateCPE.vue";
import { mapActions, mapState } from "vuex/dist/vuex.mjs";
import DocumentRetention from "./partials/retention.vue";
import moment from "moment";

export default {
    mixins: [deletable],
    props: [
        "isClient",
        "typeUser",
        "import_documents",
        "import_documents_second",
        "document_import_excel",
        "userId",
        "configuration",
        "userPermissionEditCpe",
        "view_apiperudev_validator_cpe",
        "view_validator_cpe"
    ],
    computed: {
        ...mapState(["config"])
    },
    components: {
        DocumentsVoided,
        ItemsImport,
        DocumentImportSecond,
        DocumentOptions,
        DocumentPayments,
        DataTable,
        DocumentConstancyDetraction,
        ReportPayment,
        ReportPaymentComplete,
        DocumentValidate,
        MassiveValidateCpe,
        DocumentImportExcel,
        DocumentRetention
    },
    data() {
        return {
            showDialogApiPeruDevValidate: false,
            showDialogValidate: false,
            showDialogReportPayment: false,
            showDialogReportPaymentComplete: false,
            showDialogVoided: false,
            showImportDialog: false,
            showDialogCDetraction: false,
            showImportSecondDialog: false,
            showImportExcelDialog: false,
            showDialogRetention: false,
            resource: "documents",
            recordId: null,
            showDialogOptions: false,
            showDialogPayments: false,
            columns: {
                soap_type: { title: "Soap", visible: false },
                date_of_issue: { title: "Emisión", visible: true },
                date_payment: { title: "Fecha de pago", visible: false },
                date_of_due: { title: "F. Vencimiento", visible: false },
                customer: { title: "Cliente", visible: true },
                number: { title: "Número", visible: true },
                notes: { title: "Notas C/D", visible: false },
                dispatch: { title: "Guía de Remisión", visible: false },
                sales_note: { title: "Nota de ventas", visible: false },
                order_note: { title: "Pedidos", visible: false },
                send_it: { title: "Correo enviado al destinatario", visible: false },
                state_type: { title: "Estado", visible: true },
                user_name: { title: "Usuario", visible: false },
                exchange_rate_sale: { title: "Tipo de cambio", visible: false },
                currency_type_id: { title: "Moneda", visible: false },
                guides: { title: "Guias", visible: false },
                plate_numbers: { title: "Placa", visible: false },
                total_exportation: { title: "T.Exportación", visible: false },
                total_free: { title: "T.Gratuito", visible: false },
                total_unaffected: { title: "T.Inafecto", visible: false },
                total_exonerated: { title: "T.Exonerado", visible: false },
                total_charge: { title: "T.Cargos", visible: false },
                total_taxed: { title: "T.Gravado", visible: true },
                total_igv: { title: "T.Igv", visible: true },
                total: { title: "Total", visible: false },
                balance: { title: "Saldo", visible: true },
                purchase_order: { title: "Orden de Compra", visible: false },
                downloads: { title: "Descargas (XML/PDF/CDR)", visible: true },
                actions: { title: "Acciones", visible: true },
            },
            customFieldColumns: [],
            decimal_quantity: 2,
        };
    },
    created() {
        this.$store.commit("setConfiguration", this.configuration);
        this.loadConfiguration();
        this.getColumnsToShow();
        this.loadCustomFieldsColumns();
        this.loadDecimalQuantity();
    },
    methods: {
        loadDecimalQuantity() {
            // Obtener la configuración general para los decimales
            this.$http ? this.$http.get('/configurations/record').then(response => {
                if (response.data && response.data.data && response.data.data.decimal_quantity) {
                    this.decimal_quantity = response.data.data.decimal_quantity;
                }
            }) :
            (window.axios && window.axios.get('/configurations/record').then(response => {
                if (response.data && response.data.data && response.data.data.decimal_quantity) {
                    this.decimal_quantity = response.data.data.decimal_quantity;
                }
            }));
        },
        formatDecimal(value) {
            if (value === undefined || value === null || isNaN(value)) return '';
            return Number(value).toLocaleString('en-US', { minimumFractionDigits: this.decimal_quantity, maximumFractionDigits: this.decimal_quantity });
        },
        formatDate(date) {
            if (!date) return null;
            return moment(date).format("DD-MM-YYYY");
        },
        ...mapActions(["loadConfiguration"]),

        getColumnsToShow(updated) {
            this.$http
                .post("/validate_columns", {
                    columns: this.columns,
                    report: "document_index",
                    updated: updated !== undefined
                })
                .then(response => {
                    if (updated === undefined) {
                        let currentCols = response.data.columns;
                        if (currentCols !== undefined) {
                            Object.keys(currentCols).forEach(key => {
                                if (this.columns[key] !== undefined) {
                                    this.columns[key].visible = currentCols[key].visible;
                                }
                            });
                        } else {
                            this.$http.get('/column-visibility/documents').then(res => {
                                if (res.data.success && res.data.data) {
                                    Object.keys(res.data.data).forEach(key => {
                                        if (this.columns[key] !== undefined) {
                                            this.columns[key].visible = res.data.data[key].visible;
                                        }
                                    });
                                }
                            }).catch(() => {});
                        }
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        },
        clickVoided(recordId = null) {
            this.recordId = recordId;
            this.showDialogVoided = true;
        },
        clickDownload(download) {
            window.open(download, "_blank");
        },
        clickResend(document_id) {
            this.$http
                .get(`/${this.resource}/send/${document_id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickSendOnline(document_id) {
            this.$http
                .get(`/${this.resource}/send_server/${document_id}/1`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(
                            "Se envio satisfactoriamente el comprobante."
                        );
                        this.$eventHub.$emit("reloadData");

                        this.clickCheckOnline(document_id);
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickCheckOnline(document_id) {
            this.$http
                .get(`/${this.resource}/check_server/${document_id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success("Consulta satisfactoria.");
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickCDetraction(recordId) {
            this.recordId = recordId;
            this.showDialogCDetraction = true;
        },
        clickOptions(recordId = null) {
            this.recordId = recordId;
            this.showDialogOptions = true;
        },
        clickReStore(document_id) {
            this.$http
                .get(`/${this.resource}/re_store/${document_id}`)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message);
                });
        },
        tooltip(row, message = true) {
            if (message) {
                if (row.shipping_status) return row.shipping_status.message;

                if (row.sunat_shipping_status)
                    return row.sunat_shipping_status.message;

                if (row.query_status) return row.query_status.message;
            }

            if (
                row.shipping_status ||
                row.sunat_shipping_status ||
                row.query_status
            )
                return true;

            return false;
        },
        clickPayment(recordId) {
            this.recordId = recordId;
            this.showDialogPayments = true;
        },
        clickChangeToRegisteredStatus(document_id) {
            this.$http
                .get(
                    `/${
                        this.resource
                    }/change_to_registered_status/${document_id}`
                )
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    this.$message.error(error.response.data.message);
                });
        },
        clickImport() {
            this.showImportDialog = true;
        },
        clickDownloadReportPagos() {
            this.showDialogReportPaymentComplete = true;
        },
        clickImportSecond() {
            this.showImportSecondDialog = true;
        },
        clickImportExcel() {
            this.showImportExcelDialog = true;
        },
        clickDeleteDocument(document_id) {
            this.destroy(
                `/${this.resource}/delete_document/${document_id}`
            ).then(() => this.$eventHub.$emit("reloadData"));
        },
        clickReportPayments() {
            this.showDialogReportPayment = true;
        },
        clickForceSendBySummary(id) {
            this.forceSendBySummary(`/${this.resource}/force-send-by-summary`, {
                id: id
            }).then(() => this.$eventHub.$emit("reloadData"));
        },
        clickRetention(recordId) {
            this.recordId = recordId;
            this.showDialogRetention = true;
        },
        isDateWarning(date_due) {
            let today = Date.now();
            return moment(date_due).isBefore(today);
        },
        go(url) {
          window.location.href = url;
        },
        async loadCustomFieldsColumns() {
            try {
                const response = await this.$http.get(
                    "/configurations/custom-fields/documents"
                );
                this.customFieldColumns = (response.data.data || []).map(field => ({
                    ...field,
                    visible: field.visible !== undefined ? field.visible : true
                }));
            } catch (error) {
                console.error("Error cargando columnas de campos personalizados:", error);
                this.customFieldColumns = [];
            }
        },
        updateCustomFieldColumns() {
            // Custom fields visibility is handled client-side for sale note columns.
            // Persist here if needed by backend later.
        },
        isEditableCustomField(field) {
            return [
                'text',
                'number',
                'textarea',
                'select',
                'checkbox',
                'date'
            ].includes(field.type);
        },
        normalizeOptions(options) {
            if (!options) return [];
            if (Array.isArray(options)) {
                if (options.length === 1 && typeof options[0] === 'string' && options[0].includes(',')) {
                    return options[0]
                        .split(',')
                        .map(opt => opt.trim())
                        .filter(opt => opt.length > 0);
                }
                return options;
            }
            if (typeof options === 'string') {
                return options
                    .split(/[,\n]/)
                    .map(opt => opt.trim())
                    .filter(opt => opt.length > 0);
            }
            return [];
        },
        ensureCustomFieldsData(row, field = null) {
            if (!row.custom_fields_data || typeof row.custom_fields_data !== 'object') {
                this.$set(row, 'custom_fields_data', {});
            }
            if (field && field.type === 'checkbox' && row.custom_fields_data[field.slug] === undefined) {
                this.$set(row.custom_fields_data, field.slug, []);
            }
            return row.custom_fields_data;
        },
        saveCustomFieldValue(row, field) {
            this.ensureCustomFieldsData(row, field);
            if (field.type === 'checkbox' && !Array.isArray(row.custom_fields_data[field.slug])) {
                this.$set(row.custom_fields_data, field.slug, []);
            }
            this.$http
                .post('/documents/custom-fields/update', {
                    id: row.id,
                    custom_fields_data: row.custom_fields_data
                })
                .then(response => {
                    if (response.data.success) {
                        if (response.data.data !== undefined) {
                            this.$set(row, 'custom_fields_data', response.data.data);
                        }
                        this.$message.success('Campo personalizado actualizado correctamente.');
                    }
                })
                .catch(error => {
                    console.error('Error guardando campo personalizado:', error);
                    this.$message.error('No se pudo actualizar el campo personalizado.');
                });
        },
        formatCustomFieldValue(value) {
            if (value === null || value === undefined || value === "") {
                return "";
            }
            if (Array.isArray(value)) {
                return value.join(", ");
            }
            if (typeof value === "object") {
                return JSON.stringify(value);
            }
            return value;
        },
    }
};
</script>

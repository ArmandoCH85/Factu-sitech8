<template>
    <div>
        <div class="page-header pe-0">
            <h2>
                <a href="/full_suscription/plans">
                    <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-month"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M7 14h.013" /><path d="M10.01 14h.005" /><path d="M13.01 14h.005" /><path d="M16.015 14h.005" /><path d="M13.015 17h.005" /><path d="M7.01 17h.005" /><path d="M10.01 17h.005" /></svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>
                        Planes
                    </span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">
                <button class="btn btn-custom btn-sm  mt-2 me-2"
                        type="button"
                        @click.prevent="clickShowPlan()">
                    <i class="fa fa-plus-circle">
                    </i>
                    Nuevo
                </button>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <div class="card-body">
                <data-table :periods="periods">
                    <tr slot="heading">
                        <th class="text-start">
                            Nombre
                        </th>
                        <th class="text-start">
                            Estado
                        </th>
                        <th class="text-start">
                            Frecuencia
                        </th>
                        <th class="text-end">
                            Cant. Cobros
                        </th>
                        <th class="text-end">
                            Periodo de Prueba
                        </th>
                        <th class="text-end">
                            Productos/Servicios
                        </th>
                        <th class="text-end">
                            Total
                        </th>
                        <th class="text-end">
                            Suscriptores
                        </th>
                        <th class="text-end">
                            Acciones
                        </th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td class="text-start">
                            {{ row.name }}
                        </td>
                        <td class="text-start">
                            <el-switch
                                v-model="row.status"
                                active-color="#13ce66"
                                inactive-color="#ff4949"
                                @change="toggleStatus(row)"
                            ></el-switch>
                        </td>
                        <td class="text-start">
                            <el-tag type="info">{{ row.period }}</el-tag>
                        </td>
                        <td class="text-end">
                            <el-tag v-if="row.unlimited">Ilimitado</el-tag>
                            <el-tag v-else type="info">{{ row.quantity_period }}</el-tag>
                        </td>
                        <td class="text-end">
                            <template v-if="row.trial_days > 0">
                                <el-tag type="warning">{{ row.trial_days }} días</el-tag>
                            </template>
                            <template v-else>
                                <el-tag type="info">Sin periodo de prueba</el-tag>
                            </template>
                        </td>
                        <td class="text-end">
                            {{ row.items.length }}
                        </td>
                        <td class="text-end font-weight-bold">
                            {{ row.currency.symbol }} {{ row.total }}
                        </td>
                        <td class="text-end">
                            {{ row.subscribers }}
                        </td>
                        <td class="text-end">
                            <button
                                class="btn btn-xs btn-info btn-shad me-1"
                                type="button"
                                @click.prevent="clickShowPlan(row)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415" /><path d="M16 5l3 3" /></svg>
                            </button>

                            <button
                                v-if="!row.hasSuscription"
                                class="btn light btn-xs btn-danger btn-shad"
                                type="button"
                                @click.prevent="clickDelete(row.id)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                            </button>

                        </td>
                    </tr>
                </data-table>
            </div>

            <plans-form
                :showDialog.sync="showDialog"
                @reload-data="sendReload"
            >
            </plans-form>
        </div>
    </div>
</template>
<style>
@media only screen and (max-width: 485px){
    .filter-container{
      margin-top: 0px;
      & .btn-filter-content, .btn-container-mobile{
        display: flex;
        align-items: center;
        justify-content: start;
      }
    }
  }
</style>
<script>
import {mapActions, mapState} from "vuex/dist/vuex.mjs";

import PlansForm from './form.vue'
import DataTable from '../components/SuscriptionsDataTable.vue'
import {deletable} from '../../../../../../resources/js/mixins/deletable'
import {exchangeRate} from "../../../../../../resources/js/mixins/functions";

export default {
    props: [
        'configuration',
        'date'
    ],
    mixins: [
        deletable,
        exchangeRate
    ],
    components: {
        PlansForm,
        DataTable
    },
    data() {
        return {
            showDialog: false,
        }
    },
    computed: {
        ...mapState([
            'config',
            'resource',
            'form_data',
            'exchange_rate',
            'periods',
            'affectation_igv_types',
            'item_search_extra_parameters',
            'unit_types',
            'payment_method_types',
        ]),
    },
    created() {
        this.loadConfiguration()

        this.$store.commit('setItemSearchExtraParameters', {'only_service': 0});

        this.$store.commit('setConfiguration', this.configuration)
        this.$store.commit('setResource', 'plans')
        this.$store.commit('setFormData', {
            periods: 'M',
            quantity_period: 12,
        })
        this.searchExchangeRateByDate(this.date).then(response => {
            this.$store.commit('setExchangeRate', response)
        });
        this.getCommonData();
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
            'clearFormData',
        ]),
        getCommonData() {
            this.$http.post('CommonData', {})
                .then((response) => {
                    this.$store.commit('setCurrencyTypes', response.data.currency_types)
                    this.$store.commit('setAffectationIgvTypes', response.data.affectation_igv_types)
                    this.$store.commit('setUnitTypes', response.data.unit_types)
                    this.$store.commit('setPaymentMethodTypes', response.data.payments_credit)
                })
        },

        sendReload() {
            this.$eventHub.$emit('reloadData')
        },

        clickShowPlan(row) {
            this.clearFormData();
            if (row === undefined) row = {};
            if(row.quantity_period === undefined) row.quantity_period = 12;
            if(row.periods === undefined) row.periods = 'M';

            this.$store.commit('setFormData', row)
            this.showDialog = true

        },

        clickDelete(id) {
            this.destroy(`/full_suscription/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        },
        toggleStatus(row) {
            // Enviar estado al servidor
            this.$http.post(`/full_suscription/${this.resource}/${row.id}/status`, {status: row.status})
                .then((response) => {
                    this.$message.success('Estado actualizado correctamente');
                })
                .catch((error) => {
                    row.status = !row.status;
                    this.$message.error('Error al actualizar el estado');
                })
        },
    },
};
</script>

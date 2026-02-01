<template>
    <div class="item-prices-table">
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th style="width: 50px;">Pos.</th>
                    <th>Etiqueta</th>
                    <th style="width: 150px;">Precio</th>
                    <th style="width: 80px;">Activo</th>
                    <th style="width: 60px;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(price, index) in localPrices" :key="index">
                    <td class="text-center">{{ price.position }}</td>
                    <td>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            v-model="price.label"
                            maxlength="50"
                            @input="emitChanges"
                        />
                    </td>
                    <td>
                        <input
                            type="number"
                            class="form-control form-control-sm"
                            v-model.number="price.price"
                            step="0.01"
                            min="0"
                            @input="emitChanges"
                        />
                    </td>
                    <td class="text-center">
                        <input
                            type="checkbox"
                            v-model="price.is_active"
                            @change="emitChanges"
                        />
                    </td>
                    <td class="text-center">
                        <button
                            type="button"
                            class="btn btn-sm btn-danger"
                            @click="removePrice(index)"
                            :disabled="localPrices.length === 1"
                            title="Eliminar precio"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button
            type="button"
            class="btn btn-sm btn-primary"
            @click="addPrice"
        >
            <i class="fa fa-plus"></i> Añadir precio
        </button>
    </div>
</template>

<script>
export default {
    name: 'ItemPricesTable',
    props: {
        value: {
            type: Array,
            default: () => []
        },
        priceLabels: {
            type: Object,
            default: () => ({
                price1_label: 'Precio 1',
                price2_label: 'Precio 2',
                price3_label: 'Precio 3'
            })
        }
    },
    data() {
        return {
            localPrices: [],
        }
    },
    watch: {
        value: {
            handler(newVal) {
                // Solo actualizar si hay un cambio real
                if (newVal && newVal.length > 0) {
                    this.localPrices = newVal.map(p => ({...p}));
                } else if (newVal === null || (Array.isArray(newVal) && newVal.length === 0 && this.localPrices.length === 0)) {
                    // Solo inicializar con valores por defecto si realmente no hay datos
                    this.localPrices = this.initializePrices(newVal);
                }
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        /**
         * Inicializa los precios desde el prop o crea estructura por defecto
         */
        initializePrices(prices) {
            if (prices && prices.length > 0) {
                return prices.map(p => ({...p}));
            }

            // Si no hay precios, crear 3 por defecto con labels desde configuration
            return [
                { position: 1, label: this.priceLabels.price1_label || 'Precio 1', price: 0, is_active: true },
                { position: 2, label: this.priceLabels.price2_label || 'Precio 2', price: 0, is_active: true },
                { position: 3, label: this.priceLabels.price3_label || 'Precio 3', price: 0, is_active: true }
            ];
        },

        /**
         * Añade un nuevo precio al final de la lista
         */
        addPrice() {
            const nextPosition = this.localPrices.length + 1;
            this.localPrices.push({
                position: nextPosition,
                label: `Precio ${nextPosition}`,
                price: 0,
                is_active: true
            });
            this.emitChanges();
        },

        /**
         * Elimina un precio y reorganiza las posiciones
         */
        removePrice(index) {
            if (this.localPrices.length === 1) {
                return; // No permitir eliminar si solo hay uno
            }

            this.localPrices.splice(index, 1);

            // Reorganizar posiciones
            this.localPrices.forEach((price, idx) => {
                price.position = idx + 1;
            });

            this.emitChanges();
        },

        /**
         * Emite los cambios al componente padre
         */
        emitChanges() {
            this.$emit('input', this.localPrices);
        }
    }
}
</script>

<style scoped>
.item-prices-table {
    margin: 15px 0;
}

.item-prices-table table {
    margin-bottom: 10px;
}

.item-prices-table input[type="text"],
.item-prices-table input[type="number"] {
    width: 100%;
}

.item-prices-table input[type="checkbox"] {
    cursor: pointer;
    width: 18px;
    height: 18px;
}
</style>

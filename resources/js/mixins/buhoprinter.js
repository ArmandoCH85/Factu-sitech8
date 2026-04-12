/**
 * buhoprinter.js
 * Mixin Vue 2 que expone la integración con BuhoPrinter a los componentes.
 * Delega toda la lógica al utils puro (buhoFunctions.js) y añade
 * reactividad de instancia mediante data.buhoActive.
 */
import {
    startConnection,
    isBuhoConnected,
    getUpdatedConfig,
    buhoPrint,
    buhoFetchAndPrint,
    getBuhoPrinters,
    displayError,
} from '../utils/buhoFunctions';

export const buhoprinter = {
    data() {
        return {
            // Estado reactivo de conexión — equivalente a qz.websocket.isActive()
            buhoActive: false,
        };
    },

    computed: {
        isBuhoActive() {
            return this.buhoActive;
        },
    },

    methods: {
        /**
         * Inicia la conexión con el agente BuhoPrinter y actualiza el estado reactivo.
         * Reemplaza: startConnection() + qz.websocket.connect()
         */
        async startConnectionBuho() {
            await startConnection();
            this.buhoActive = isBuhoConnected();
        },

        /**
         * Retorna la configuración de impresión activa (printer name).
         * Mantiene el mismo contrato que getUpdatedConfig() global de qztray.
         */
        getUpdatedConfig,

        /**
         * Envía un trabajo de impresión PDF base64 a BuhoPrinter.
         * Reemplaza: qz.print(config, printData)
         *
         * @param {object} config     - { printer: string }
         * @param {Array}  printData  - array de items con { type, format, data }
         */
        async buhoPrint(config, printData) {
            try {
                await buhoPrint(config, printData);
                this.$notify({ title: '', message: 'Impresión en proceso...', type: 'success' });
            } catch (err) {
                displayError(err);
            }
        },

        /**
         * Descarga un PDF desde una URL y lo imprime directamente.
         * Reemplaza: el patrón printPdfFromUrl + window.qz.print() de los componentes.
         *
         * @param {string} url - URL completa del PDF (usa credentials: 'include')
         */
        async printPdfFromUrl(url) {
            try {
                await buhoFetchAndPrint(url, this.getUpdatedConfig());
                this.$notify({ title: '', message: 'Impresión en proceso...', type: 'success' });
            } catch (err) {
                displayError(err);
            }
        },

        /**
         * Retorna el listado de impresoras disponibles del sistema.
         * Reemplaza: qz.printers.find()
         *
         * @returns {Promise<string[]>}
         */
        async getBuhoPrinters() {
            return getBuhoPrinters();
        },

        /**
         * Manejo de errores centralizado.
         * Reemplaza: displayError() global de qztray.
         */
        displayError,
    },
};

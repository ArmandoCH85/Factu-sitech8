<template>
    <div>
        <el-button type="primary" @click.prevent="submit">Pagar con Culqi</el-button>
        <script src="https://js.culqi.com/checkout-js"></script>
    </div>
</template>
<script>

/**
 * Culqi Checkout Integration Example
 * cfg -> { 
 *  publicKey: Clave pública de Culqi
 *  rsa: Clave RSA para encriptar datos sensibles (opcional)
 *  idrsa: Identificador de la clave RSA (opcional)
 * }
 * Form -> [
 *  amount: Monto a cobrar (en centavos)
 *  currency: Moneda (PEN o USD) 
 *  email: Correo del cliente
 *  description: Descripción del cargo
 * ]
 */

export default {
    props: {
        form: {
            type: Object,
            required: true
        },
        isTenant: {
            type: Boolean,
            default: false
        }
    },
    created() {
        if (!window.Culqi) {
            const script = document.createElement('script');
            script.src = "https://js.culqi.com/checkout-js";
            script.async = true;
            document.head.appendChild(script);

            this.loadConfiguration();
        }
    },
    data() {
        return {
            settings: {
                title: "Configuración de Culqi"
            },
            payment_methods: {
                tarjeta: true,
                yape: true,
                // billetera: true,
                bancaMovil: true,
                agente: true,
                // cuotealo: true,	
            },
            resource: '/payment-gateway/culqi',
            publicKey: null
        }
    },
    computed: {
    },
    methods:  {
        submit() {
            console.log(this.form);
            
            let config  = { 
                settings: {
                    title : this.form.description,
                    currency: this.form.currency,
                    amount: this.form.amount,
                },
                client: {
                    email: this.form.email,
                },
                options : {
                    modal: true,
                    // installments: true,
                    paymentMethods: {
                        tarjeta: true,
                        yape: true,
                        // billetera: true,
                        bancaMovil: true,
                        agente: true,
                        // cuotealo: true,	
                    },
                paymentMethodsSort: Object.keys({
                tarjeta: true,
                yape: true,
                // billetera: true,
                bancaMovil: true,
                agente: true,
                // cuotealo: true,	
            })
                }, 
                appearance : {
                    menuType: "sidebar",
                }
            }

            const Culqi = new CulqiCheckout(this.publicKey, config);

            Culqi.culqi = () =>  {
                if (Culqi.token) {
                const token = Culqi.token.id;
                    this.$http.post(`${this.resource}/charge`, {
                        source_id: token,
                        installments: this.form.installments,
                        description : this.form.description,
                        amount: this.form.amount,
                        email: this.form.email,
                        currency_code: this.form.currency,
                    }).then(response => {
                        if (response.data.success) {
                            Culqi.close();
                            this.$message.success('Pago realizado con éxito');
                        } else {
                            this.$message.error('Error en el pago: ' + response.data.message);
                        }
                    }).catch(error => {
                        this.$message.error('Error en el pago: ' + response.data.message);
                    });
                } else if (Culqi.order) {
                    const order = Culqi.order;
                } else {
                    console.log('Errorrr : ', Culqi.error);
                }
                
            }
            Culqi.open();

        },
        loadConfiguration() {
            this.$http.get(`${this.resource}/record?isTenant=${this.isTenant}`)
                .then(response => {
                    this.publicKey = response.data.publickey_culqi;
                })
        }
    }


}

</script>
<template>
    <article class="auth__form">
        <aside class="auth__plans-aside" v-if="!isRegistered && plans.length">
            <h4 class="auth__plans-title">Seleccionar plan</h4>
            <button
                type="button"
                class="btn-signin btn-block btn-signin--planes"
                @click="showPlansModal = true"
            >
                {{ selectedPlan ? selectedPlan.name : "PLANES" }}
            </button>
            <small
                v-if="errors.plan_id"
                class="invalid-feedback d-block mt-2 text-center"
                v-text="errors.plan_id[0]"
            ></small>
        </aside>

        <el-dialog
            title="Selecciona un plan"
            :visible.sync="showPlansModal"
            width="80%"
            custom-class="plans-modal"
            append-to-body
        >
            <div class="plans-grid">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="plan-card-mini"
                    :class="{
                        'plan-card-mini--selected': form.plan_id === plan.id,
                    }"
                >
                    <div class="plan-card-mini__head">
                        <h5 class="plan-card-mini__name">{{ plan.name }}</h5>
                        <div class="plan-card-mini__price">
                            S/ {{ plan.pricing }}
                        </div>
                        <div class="plan-card-mini__period">
                            Servicio facturado mensualmente
                        </div>
                    </div>
                    <ul class="plan-card-mini__features">
                        <li>
                            {{
                                plan.limit_users == 0
                                    ? "Usuarios ilimitados"
                                    : plan.limit_users + " usuarios"
                            }}
                        </li>
                        <li>
                            {{
                                plan.limit_documents == 0
                                    ? "Comprobantes ilimitados"
                                    : plan.limit_documents + " comprobantes"
                            }}
                        </li>
                        <li>
                            {{
                                plan.establishments_unlimited
                                    ? "Sucursales ilimitadas"
                                    : plan.establishments_limit + " sucursales"
                            }}
                        </li>
                        <li>
                            {{
                                plan.sales_unlimited
                                    ? "Ventas ilimitadas"
                                    : "Hasta S/ " +
                                      plan.sales_limit +
                                      " en ventas"
                            }}
                        </li>
                    </ul>
                    <button
                        type="button"
                        class="btn-signin btn-block btn-signin--card"
                        :class="{
                            'btn-signin--card-selected':
                                form.plan_id === plan.id,
                        }"
                        @click="selectPlan(plan)"
                    >
                        {{
                            form.plan_id === plan.id
                                ? "✓ Seleccionado"
                                : "Seleccionar"
                        }}
                    </button>
                </div>
            </div>
        </el-dialog>

        <div class="auth__form-content">
            <div class="d-flex justify-content-center">
                <div class="row">
                    <slot name="form-logo"></slot>
                </div>
            </div>
            <form autocomplete="off" @submit.prevent="submit">
                <div
                    class="row email-verificate"
                    v-if="isRegistered"
                    v-loading="loading_submit"
                >
                    <div class="col-md-12 text-center">
                        <h1 class="auth__title">Verificación de correo</h1>
                    </div>
                    <div class="col-md-12">
                        <p class="text-justify">
                            <span class="step-number">1</span>
                            <span class="step-text">
                                Revise su bandeja de entrada en el correo:
                                <b>{{ form.email }}</b> y haga clic en el enlace
                                "Verificar Email" para activar su cuenta.
                            </span>
                        </p>

                        <p class="text-justify">
                            <span class="step-number">2</span>
                            <span class="step-text">
                                Si registró mal su correo, escríbanos al
                                whatsapp para que el equipo de soporte lo
                                corrija por usted.
                            </span>
                        </p>

                        <p class="text-justify">
                            <span class="step-number">3</span>
                            <span class="step-text">
                                Si el correo indicado es correcto y no recibió
                                el correo de verificación en su bandeja de
                                entrada, verifique su bandeja de spam o
                                <a href="#" @click="clickResendEmail"
                                    ><b
                                        >Haga clic aquí para volver a enviar el
                                        correo de verificación.</b
                                    ></a
                                >
                            </span>
                        </p>
                        <p v-if="errors.key || errors.user_id || errors.email">
                            <span class="step-number error-step">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="14"
                                    height="14"
                                    fill="currentColor"
                                    viewBox="0 0 16 16"
                                >
                                    <path
                                        d="M7.938 2.016a.13.13 0 0 1 .125 0l6.857 11.856c.03.052.03.116 0 .168a.13.13 0 0 1-.125.06H1.205a.13.13 0 0 1-.125-.06.145.145 0 0 1 0-.168L7.938 2.016zM8 5c-.535 0-.954.462-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 5zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"
                                    />
                                </svg>
                            </span>
                            <span class="step-text">
                                <small
                                    v-if="errors.key"
                                    class="invalid-feedback"
                                    v-text="errors.key[0]"
                                ></small>
                                <small
                                    v-if="errors.user_id"
                                    class="invalid-feedback"
                                    v-text="errors.user_id[0]"
                                ></small>
                                <small
                                    v-if="errors.email"
                                    class="invalid-feedback"
                                    v-text="errors.email[0]"
                                ></small>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row" v-else>
                    <div class="col-md-12 text-center">
                        <h1 class="auth__title" style="font-weight: 700">
                            Regístrate gratis
                        </h1>
                    </div>

                    <div class="col-md-12">
                        <div
                            :class="{ 'has-danger': errors.number }"
                            class="form-group"
                        >
                            <label class="control-label">RUC</label>
                            <x-input-service-guest
                                v-model="form.number"
                                class="form-control form-top"
                                :identity_document_type_id="
                                    form.identity_document_type_id
                                "
                                @search="searchNumber"
                            ></x-input-service-guest>
                            <small
                                v-if="errors.number"
                                class="invalid-feedback"
                                v-text="errors.number[0]"
                            ></small>
                        </div>
                    </div>
                    <div class="col-md-12" v-if="rucVerified">
                        <div
                            :class="{ 'has-danger': errors.name }"
                            class="form-group"
                        >
                            <label class="control-label"
                                >Nombre de la empresa</label
                            >
                            <el-input
                                v-model="form.name"
                                class="form-control"
                                :disabled="true"
                            >
                            </el-input>
                            <small
                                v-if="errors.name"
                                class="invalid-feedback"
                                v-text="errors.name[0]"
                            ></small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div
                            :class="{
                                'has-danger': errors.subdomain || errors.uuid,
                            }"
                            class="form-group"
                        >
                            <label class="control-label">
                                Nombre de subdominio
                            </label>
                            <el-input
                                v-model="form.subdomain"
                                class="form-control form-top"
                            >
                                <template slot="append">{{ baseUrl }}</template>
                            </el-input>
                            <small
                                v-if="errors.subdomain"
                                class="invalid-feedback"
                                v-text="errors.subdomain[0]"
                            ></small>
                            <small
                                v-if="errors.uuid"
                                class="invalid-feedback"
                                v-text="errors.uuid[0]"
                            ></small>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div
                            :class="{ 'has-danger': errors.email }"
                            class="form-group"
                        >
                            <label class="control-label">
                                Correo de acceso
                                <el-tooltip
                                    class="item"
                                    content="Ingresa un correo válido: allí recibirás el aviso y el enlace de activación."
                                    effect="dark"
                                    placement="top-start"
                                >
                                    <i class="fa fa-info-circle"></i>
                                </el-tooltip>
                            </label>
                            <el-input
                                v-model="form.email"
                                class="form-control"
                                :disabled="form.is_update"
                            >
                            </el-input>
                            <small
                                v-if="errors.email"
                                class="invalid-feedback"
                                v-text="errors.email[0]"
                            ></small>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div
                            :class="{ 'has-danger': errors.password }"
                            class="form-group"
                        >
                            <label class="control-label"> Contraseña </label>
                            <el-input
                                v-model="form.password"
                                type="password"
                                class="form-control"
                                show-password
                            >
                            </el-input>
                            <small
                                v-if="errors.password"
                                class="invalid-feedback"
                                v-text="errors.password[0]"
                            ></small>
                        </div>
                    </div>

                    <div class="col-md-12 text-end pt-2">
                        <button
                            class="btn-signin btn-block mt-0"
                            :disabled="loading_submit || !form.plan_id"
                            type="submit"
                        >
                            <template v-if="loading_submit">
                                {{ button_text }}
                            </template>
                            <template v-else> CREAR CUENTA </template>
                        </button>
                    </div>

                    <div class="col-md-12 text-center mt-3">
                        <span style="font-size: 12px">
                            <strong
                                >✉ Se requiere confirmar el correo para acceder
                                a la plataforma.</strong
                            >
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </article>
</template>

<script>
import { serviceNumber } from "../../../mixins/functions";

export default {
    props: {
        baseUrl: {
            required: true,
        },
        plans: {
            type: Array,
            default: () => [],
        },
    },
    mixins: [serviceNumber],
    data() {
        return {
            resource: "guest-register",
            form: {},
            errors: {},
            loading_submit: false,
            button_text: null,
            isRegistered: false,
            rucVerified: false,
            showPlansModal: false,
        };
    },
    computed: {
        selectedPlan() {
            return this.plans.find((p) => p.id === this.form.plan_id) || null;
        },
    },
    created() {
        this.initForm();
    },
    methods: {
        selectPlan(plan) {
            this.form.plan_id = plan.id;
            this.showPlansModal = false;
        },
        async clickResendEmail() {
            this.loading_submit = true;

            const form = {
                user_id: this.form.guest_register.user_id,
                email: this.form.email,
                key: this.form.guest_register.key,
            };

            await this.$http
                .post(`${this.resource}/resend-email`, form)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        console.log(error.response);
                        this.$message.error(error.response.data.message);
                    }
                })
                .finally(() => {
                    this.loading_submit = false;
                });
        },
        async submit() {
            this.loading_submit = true;
            this.button_text = "CREANDO CUENTA...";

            await this.$http
                .post(`${this.resource}/register`, this.form)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.form.guest_register = response.data.guest_register;

                        if (response.data.payment_url) {
                            window.location.href = response.data.payment_url;
                            return;
                        }

                        this.isRegistered = true;
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        console.log(error.response);
                        this.$message.error(error.response.data.message);
                    }
                })
                .finally(() => {
                    this.loading_submit = false;
                });
        },
        searchNumber(data) {
            this.form.name = data.name;
        },
        initForm() {
            this.form = {
                name: null,
                email: null,
                identity_document_type_id: "6",
                number: "",
                password: null,
                subdomain: null,
                plan_id: null,
                guest_register: {},
            };

            this.errors = {};
        },
        searchNumber(data) {
            if (data && data.name) {
                this.form.name = data.name;
                this.rucVerified = true;
            } else {
                this.form.name = null;
                this.rucVerified = false;
            }
        },
    },
};
</script>

<style scoped>
.auth__form {
    position: relative;
    box-shadow: none;
}

.auth__form-content {
    width: 100%;
}

.auth__plans-aside {
    position: absolute;
    top: 34%;
    right: 100%;
    margin-right: -3rem;
    width: 200px;
    min-height: 132px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background-color: #f6f6f6;
    border: 1px solid #ebebeb;
    border-radius: 8px;
    padding: 1.25rem 1rem;
    z-index: 5;
}

.auth__plans-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1f2d3d;
    margin: 0 0 0.75rem 0;
    text-align: center;
}

.btn-signin--planes {
    margin-top: 0;
    padding: 1.25rem 0.75rem;
    font-size: 0.8rem;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    word-break: break-word;
    line-height: 1.2;
}

@media (max-width: 1024px) {
    .auth__plans-aside {
        width: 170px;
    }
    .btn-signin--planes {
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    .auth__plans-aside {
        position: static;
        width: 100%;
        margin: 0 0 1.5rem 0;
    }
}

/* Modal grid */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

@media (max-width: 1200px) {
    .plans-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .plans-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .plans-grid {
        grid-template-columns: 1fr;
    }
}

.plan-card-mini {
    display: flex;
    flex-direction: column;
    border: 1px solid #ebebeb;
    border-radius: 8px;
    padding: 1rem;
    transition: border-color 0.15s ease, background-color 0.15s ease;
    background-color: #fff;
}

.plan-card-mini--selected {
    border-color: #042a42;
    background-color: #f6f6f6;
}

.plan-card-mini__head {
    text-align: center;
    border-bottom: 1px solid #ebebeb;
    padding-bottom: 0.75rem;
    margin-bottom: 0.75rem;
}

.plan-card-mini__name {
    font-size: 1rem;
    font-weight: 700;
    color: #042a42;
    margin: 0 0 0.25rem 0;
    text-transform: capitalize;
}

.plan-card-mini__price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #042a42;
    line-height: 1;
}

.plan-card-mini__period {
    font-size: 0.75rem;
    color: #8a96a3;
    margin-top: 0.35rem;
}

.plan-card-mini__features {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem 0;
    flex: 1;
}

.plan-card-mini__features li {
    padding: 0.25rem 0;
    font-size: 0.8rem;
    color: #042a42;
}

.plan-card-mini__features li::before {
    content: "✓";
    color: #042a42;
    font-weight: 700;
    margin-right: 6px;
}

.btn-signin--card {
    margin-top: 0;
    padding: 0.6rem 0.75rem;
    font-size: 0.85rem;
    border: none;
    cursor: pointer;
}

.btn-signin--card-selected {
    cursor: default;
    opacity: 0.85;
}

.btn-signin--card-selected:hover {
    opacity: 0.85;
}
</style>

<style>
.plans-modal {
    border-radius: 16px;
    overflow: hidden;
}

.plans-modal .el-dialog__header {
    border-radius: 16px 16px 0 0;
    padding: 1.75rem 2rem 1rem;
    text-align: center;
}

.plans-modal .el-dialog__title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #042a42;
    line-height: 1.3;
}

.plans-modal .el-dialog__headerbtn {
    top: 1.25rem;
    right: 1.25rem;
}

.plans-modal .el-dialog__headerbtn .el-dialog__close {
    font-size: 1.25rem;
    color: #8a96a3;
}

.plans-modal .el-dialog__headerbtn:hover .el-dialog__close {
    color: #042a42;
}
</style>

<template>
    <div class="">

        <!-- Loading inicial -->
        <div v-if="loading" class="d-flex flex-column align-items-center justify-content-center" style="min-height:260px; gap:16px;">
            <div class="spinner-border text-secondary" role="status" style="width:2rem; height:2rem;"></div>
            <p class="text-muted mb-0" style="font-size:13px; font-weight:500;">Cargando...</p>
        </div>

        <template v-else>

        <div class="page-header pe-0">
            <h2>
                <a href="/affiliations/mp">
                    <svg xmlns="http://www.w3.org/2000/svg" style="margin-top:-5px;" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
                        <path d="M3.6 9h16.8"/><path d="M3.6 15h16.8"/>
                        <path d="M11.5 3a17 17 0 0 0 0 18"/><path d="M12.5 3a17 17 0 0 1 0 18"/>
                    </svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>MercadoPago</span></li>
            </ol>
        </div>

        <!-- ── Cargando ── -->
        <div v-if="loading" class="mp-loading">
            <div class="mp-loading__spinner"></div>
        </div>

        <!-- ── Estado: NO conectado ── -->
        <div v-else-if="!connected" class="mp-connect-screen">
            <img :src="logoUrl" alt="MercadoPago" class="mp-connect-logo" />
            <h2 class="mp-connect-title">Conecta tu cuenta de MercadoPago</h2>
            <p class="mp-connect-desc">
                Sincroniza clientes, planes y suscripciones automáticamente además podrás cobrar tus planes con MercadoPago y ofrecer a tus clientes una experiencia de pago confiable y rápida.
            </p>
            <button class="mp-btn-connect" @click="redirect">
                <svg data-v-1c611410="" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path data-v-1c611410="" stroke="none" d="M0 0h24v24H0z" fill="none"></path><path data-v-1c611410="" d="M7 12l5 5l-1.5 1.5a3.536 3.536 0 1 1 -5 -5l1.5 -1.5z"></path><path data-v-1c611410="" d="M17 12l-5 -5l1.5 -1.5a3.536 3.536 0 1 1 5 5l-1.5 1.5z"></path><path data-v-1c611410="" d="M3 21l2.5 -2.5"></path><path data-v-1c611410="" d="M18.5 5.5l2.5 -2.5"></path><path data-v-1c611410="" d="M10 11l-2 2"></path><path data-v-1c611410="" d="M13 14l-2 2"></path></svg>
                Conectar cuenta
            </button>
            <p class="mp-connect-secure">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z"/>
                    <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/>
                    <path d="M8 11v-4a4 4 0 1 1 8 0v4"/>
                </svg>
                Conexión segura mediante OAuth 2.0
            </p>
        </div>

        <!-- ── Estado: CONECTADO ── -->
        <template v-else>
            <div class="card-body bg-white" style="border-radius: 12px;">
                <!-- ── Cuenta conectada ── -->
                <div class="mp-account-bar mx-3">
                    <div class="mp-account-bar__left">
                        <img :src="logoUrl" alt="MercadoPago" class="mp-account-bar__logo" />
                        <div v-if="me_oauth && me_oauth.name" class="mp-account-bar__user">
                            <div class="mp-avatar">{{ initials }}</div>
                            <div>
                                <p class="mp-account-name">{{ me_oauth.name }}</p>
                                <p class="mp-account-email">
                                    {{ me_oauth.email }}
                                    <template v-if="token_expires_at">
                                        · Token válido hasta <strong>{{ token_expires_at }}</strong>
                                    </template>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mp-account-bar__right">
                        <a href="#" class="mp-link-danger" @click.prevent="revoke">Desconectar</a>
                        <span class="badge-connected">
                            <span class="badge-connected__dot"></span> Conectado
                        </span>
                    </div>
                </div>

                <!-- ── Sincronización ── -->
                <div>
                    <div class="mp-section__header">
                        <div class="mp-section__header-left">
                            <!-- Icono refresh -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="mp-section__icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/>
                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>
                            </svg>
                            <span>Sincronización</span>
                        </div>
                        <div class="mp-section__header-right">
                            <span v-if="sincronize_at" class="mp-sync-date">
                                Última sync: <strong>{{ sincronize_at }}</strong>
                            </span>
                            <el-button type="primary" size="small" @click="sincronize" :loading="syncing">
                                <svg v-if="!syncing" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px; vertical-align:-1px;">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/>
                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>
                                </svg>
                                Sincronizar ahora
                            </el-button>
                        </div>
                    </div>
                    <div class="mp-section__body">
                        <!-- KPIs -->
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="kpi-card kpi-card--blue">
                                    <div class="kpi-icon">
                                        <!-- Icono users -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
                                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>
                                        </svg>
                                    </div>
                                    <div class="kpi-body">
                                        <p class="kpi-value">{{ (statistics && statistics.total_client) || 0 }}</p>
                                        <p class="kpi-label">Clientes sincronizados</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="kpi-card kpi-card--green">
                                    <div class="kpi-icon">
                                        <!-- Icono layout-list -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M9 5h11"/><path d="M9 10h11"/><path d="M9 15h11"/><path d="M9 20h11"/>
                                            <path d="M5 5v.01"/><path d="M5 10v.01"/><path d="M5 15v.01"/><path d="M5 20v.01"/>
                                        </svg>
                                    </div>
                                    <div class="kpi-body">
                                        <p class="kpi-value">{{ (statistics && statistics.total_plans) || 0 }}</p>
                                        <p class="kpi-label">Planes sincronizados</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="kpi-card kpi-card--purple">
                                    <div class="kpi-icon">
                                        <!-- Icono repeat -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"/>
                                            <path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"/>
                                        </svg>
                                    </div>
                                    <div class="kpi-body">
                                        <p class="kpi-value">{{ (statistics && statistics.total_affiliations) || 0 }}</p>
                                        <p class="kpi-label">Suscripciones activas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        </template><!-- end v-else -->

    </div>
</template>

<style scoped>
/* ── Loading ── */
.mp-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 40vh;
}

.mp-loading__spinner {
    width: 28px;
    height: 28px;
    border: 2px solid #e5e7eb;
    border-top-color: #009ee3;
    border-radius: 50%;
    animation: mp-spin .7s linear infinite;
}

@keyframes mp-spin { to { transform: rotate(360deg); } }

/* ── Estado: no conectado ── */
.mp-connect-screen {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    text-align: center;
    padding: 4rem 1rem;
    background: #fff;
    border-radius: 12px;
}

.mp-connect-logo {
    width: 200px;
    height: auto;
    margin-bottom: 2rem;
    border-radius: 20px;
    background: var(--black-highlight);
    padding: 16px;
}

.mp-connect-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #344563;
    margin: 0 0 .75rem;
}

.mp-connect-desc {
    font-size: .9rem;
    color: #6b7280;
    max-width: 600px;
    line-height: 1.7;
    margin: 0 0 2rem;
}

.mp-btn-connect {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #009ee3;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 13px 28px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,158,227,.35);
    transition: background .15s, box-shadow .15s, transform .1s;
}
.mp-btn-connect:hover {
    background: #008ecf;
    box-shadow: 0 6px 16px rgba(0,158,227,.45);
}
.mp-btn-connect:active {
    background: #007db8;
    transform: translateY(1px);
    box-shadow: 0 2px 8px rgba(0,158,227,.3);
}

.mp-connect-secure {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 1rem 0 0;
    font-size: .75rem;
    color: #9ca3af;
}

.mp-section__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 16px;
    gap: 12px;
    flex-wrap: wrap;
}

.mp-section__header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.mp-section__header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.mp-section__icon {
    color: #6b7280;
    flex-shrink: 0;
}

.mp-section__body {
    padding: 16px;
}

/* ── Barra cuenta conectada ── */
.mp-account-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 0;
}

.mp-account-bar__left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.mp-account-bar__logo {
    height: 48px;
    object-fit: contain;
    background: var(--black-highlight);
    padding: 2px;
    border-radius: 8px;
}

.mp-account-bar__user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mp-account-bar__right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.mp-account-name {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
}

.mp-account-email {
    margin: 0;
    font-size: 12px;
    color: #6b7280;
}

.mp-link-danger {
    font-size: 12px;
    font-weight: 500;
    color: #dc2626;
    text-decoration: none;
}
.mp-link-danger:hover {
    color: #b91c1c;
    text-decoration: underline;
}

.mp-sync-date {
    font-size: 12px;
    color: #9ca3af;
}

/* ── Badge conectado ── */
.badge-connected {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
    color: #16a34a;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 99px;
    padding: 8px 16px;
}

.badge-connected__dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #16a34a;
    flex-shrink: 0;
}

/* ── Avatar ── */
.mp-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #344563;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── KPI cards ── */
.kpi-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    height: 100%;
}
.kpi-card--blue   { border-left: 3px solid #60a5fa; }
.kpi-card--green  { border-left: 3px solid #34d399; }
.kpi-card--purple { border-left: 3px solid #a78bfa; }

.kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.kpi-card--blue   .kpi-icon { background: #eff6ff; color: #3b82f6; }
.kpi-card--green  .kpi-icon { background: #ecfdf5; color: #059669; }
.kpi-card--purple .kpi-icon { background: #f5f3ff; color: #7c3aed; }

.kpi-body { flex: 1; min-width: 0; }
.kpi-value {
    margin: 0 0 2px;
    font-size: 1.625rem;
    font-weight: 700;
    color: #111827;
    line-height: 1;
    letter-spacing: -.02em;
}
.kpi-label {
    margin: 0;
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}
</style>

<script>
export default {
    data() {
        return {
            logoUrl: '/images/MercadoPago.svg',
            loading: true,
            connected: false,
            sincronize_at: null,
            token_expires_at: null,
            me_oauth: null,
            statistics: null,
            syncing: false,
        }
    },
    computed: {
        initials() {
            if (!this.me_oauth || !this.me_oauth.name) return '?';
            return this.me_oauth.name.trim().split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
        },
    },
    created() {
        this.loadTables();
    },
    methods: {
        redirect() {
            this.$http.get('/affiliations/mp/redirect').then(response => {
                if (!response.data.success) {
                    this.$message.error(response.data.message);
                } else {
                    window.location.href = response.data.url;
                }
            });
        },
        loadTables() {
            this.$http.get('/affiliations/mp/table').then(response => {
                this.connected        = response.data.connected;
                this.sincronize_at    = response.data.sincronize_at;
                this.token_expires_at = response.data.token_expires_at;
                this.me_oauth         = response.data.me_oauth;
                this.statistics       = response.data.statistics;
            }).finally(() => {
                this.loading = false;
            });
        },
        sincronize() {
            this.syncing = true;
            this.$http.get('/affiliations/mp/sincronize').then(response => {
                if (response.data.success) {
                    this.$message.success(response.data.message);
                    this.loadTables();
                } else {
                    this.$message.error(response.data.message);
                }
            }).finally(() => {
                this.syncing = false;
            });
        },
        revoke() {
                this.$confirm('¿Estás seguro de querer desconectar? No podrá realizar sincronización de datos, ni proceso de pagos.', 'Confirmar', {
                  confirmButtonText: 'Cerrar sin guardar',
                  cancelButtonText: 'Cancelar',
                  type: 'warning'
                }).then(() => {

                    this.$http.get('/affiliations/mp/revoke').then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message);
                            this.loadTables();
                        }
                    });

                }).catch(() => {

                });
        },
    },
}
</script>

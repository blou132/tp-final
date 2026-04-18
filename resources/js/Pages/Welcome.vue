<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
    laravelVersion: {
        type: String,
        default: '',
    },
    phpVersion: {
        type: String,
        default: '',
    },
});

const { t, locale, supportedLocales } = useI18n();

const localeSwitchHref = (localeCode) =>
    route('locale.switch', {
        locale: localeCode,
    });

const navLinks = computed(() => [
    { label: t('welcome.nav_features', 'Fonctionnalités'), href: '#features' },
    { label: t('welcome.nav_operations', 'Workflow'), href: '#workflow' },
    { label: t('welcome.nav_security', 'Sécurité'), href: '#stats' },
    { label: t('welcome.nav_faq', 'FAQ'), href: '#cta' },
]);

const dashboardPreviewMetrics = computed(() => [
    { label: t('welcome.live_item_1', 'Tickets ouverts'), value: '42', progress: 78, tone: 'tone-primary' },
    { label: t('welcome.live_item_2', 'Paiements échoués'), value: '18', progress: 42, tone: 'tone-danger' },
    { label: t('welcome.live_item_3', 'Collecté cette semaine'), value: '64 000€', progress: 88, tone: 'tone-success' },
    { label: t('welcome.live_item_4', 'Disponibilité système'), value: '99.95%', progress: 99, tone: 'tone-info' },
]);

const socialProof = computed(() => ['Nordlane', 'BrightOps', 'PulseDesk', 'Arc Finance', 'Flowstack']);

const featureCards = computed(() => [
    {
        title: t('welcome.module_tickets_title', 'Automatisation support'),
        description: t(
            'welcome.module_tickets_desc',
            'Priorisation intelligente des tickets, règles d’escalade et suivi des SLA dans un flux unifié.',
        ),
        icon: 'SO',
    },
    {
        title: t('welcome.module_payments_title', 'Suivi des paiements'),
        description: t(
            'welcome.module_payments_desc',
            'Vue temps réel des encaissements, rejets et transactions en attente avec contexte client immédiat.',
        ),
        icon: 'PA',
    },
    {
        title: t('welcome.module_audit_title', 'Audit & conformité'),
        description: t(
            'welcome.module_audit_desc',
            'Journalisation des actions critiques pour maintenance, conformité et pilotage de performance.',
        ),
        icon: 'AU',
    },
]);

const workflowSteps = computed(() => [
    {
        title: t('welcome.workflow_step_1_title', 'Centraliser'),
        description: t('welcome.workflow_step_1_desc', 'Tickets et paiements arrivent dans un cockpit unique.'),
    },
    {
        title: t('welcome.workflow_step_2_title', 'Prioriser'),
        description: t('welcome.workflow_step_2_desc', 'Les équipes traitent d’abord les files à impact élevé.'),
    },
    {
        title: t('welcome.workflow_step_3_title', 'Exécuter'),
        description: t('welcome.workflow_step_3_desc', 'Workflow guidé avec statuts, validations et responsabilités claires.'),
    },
    {
        title: t('welcome.workflow_step_4_title', 'Mesurer'),
        description: t('welcome.workflow_step_4_desc', 'Les KPI mettent en évidence la qualité opérationnelle en continu.'),
    },
]);

const statsCards = computed(() => [
    {
        label: t('welcome.metric_sla_label', 'SLA respecté'),
        value: '98%',
        note: t('welcome.metric_sla_note', 'Tickets clôturés dans les objectifs.'),
    },
    {
        label: t('welcome.metric_revenue_label', 'Collecte hebdo'),
        value: '€ 64K',
        note: t('welcome.metric_revenue_note', 'Flux transactions consolidé.'),
    },
    {
        label: t('welcome.metric_resolution_label', 'Résolution médiane'),
        value: '2.8h',
        note: t('welcome.metric_resolution_note', 'Temps moyen par incident.'),
    },
    {
        label: t('welcome.metric_uptime_label', 'Disponibilité'),
        value: '99.95%',
        note: t('welcome.metric_uptime_note', 'Monitoring plateforme en direct.'),
    },
]);

let revealObserver = null;
const revealNodes = new Set();

const registerReveal = (element) => {
    if (!element || revealNodes.has(element)) {
        return;
    }

    revealNodes.add(element);

    if (revealObserver) {
        revealObserver.observe(element);
    }
};

onMounted(() => {
    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.16,
        },
    );

    revealNodes.forEach((node) => revealObserver.observe(node));
});

onBeforeUnmount(() => {
    if (revealObserver) {
        revealObserver.disconnect();
    }
});
</script>

<template>
    <Head :title="t('app_name', 'Support & Paiements')" />

    <div class="landing-saas">
        <div class="landing-grid" />
        <div class="landing-glow landing-glow-left" />
        <div class="landing-glow landing-glow-right" />

        <div class="container-shell">
            <header class="top-nav reveal-node" :ref="registerReveal">
                <Link :href="route('home')" class="brand">
                    <span class="brand-mark">SP</span>
                    <span>
                        <small class="brand-kicker">Plateforme SaaS</small>
                        <strong class="brand-title">{{ t('app_name', 'Support & Paiements') }}</strong>
                    </span>
                </Link>

                <nav class="nav-center">
                    <a
                        v-for="item in navLinks"
                        :key="item.href"
                        :href="item.href"
                        class="nav-link"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="nav-actions">
                    <div class="locale-switch">
                        <Link
                            v-for="localeCode in supportedLocales"
                            :key="localeCode"
                            :href="localeSwitchHref(localeCode)"
                            :class="['locale-pill', locale === localeCode ? 'is-active' : '']"
                        >
                            {{ localeCode }}
                        </Link>
                    </div>

                    <Link v-if="canLogin" :href="route('login')" class="btn btn-ghost">
                        {{ t('welcome.cta_login', 'Accéder à l’espace') }}
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn btn-primary">
                        {{ t('welcome.cta_register', 'Créer un compte') }}
                    </Link>
                </div>
            </header>

            <section class="hero-section reveal-node" :ref="registerReveal">
                <article class="hero-copy">
                    <span class="hero-pill">Cockpit support & finance</span>
                    <h1>
                        Pilotez tickets et paiements
                        <span>depuis une seule console.</span>
                    </h1>
                    <p>
                        Un environnement opérationnel premium pour centraliser le support client, sécuriser les flux
                        de paiement et suivre la performance en temps réel.
                    </p>

                    <div class="hero-ctas">
                        <Link v-if="canLogin" :href="route('login')" class="btn btn-primary btn-xl">
                            {{ t('welcome.cta_login', 'Accéder à l’espace') }}
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="btn btn-ghost btn-xl">
                            {{ t('welcome.cta_register', 'Créer un compte') }}
                        </Link>
                    </div>
                </article>

                <aside class="preview-wrap">
                    <div class="preview-overlay" />
                    <div class="preview-card">
                        <div class="preview-head">
                            <div>
                                <p class="preview-kicker">Live dashboard</p>
                                <h3>Vue instantanée</h3>
                            </div>
                            <span class="live-dot">Live</span>
                        </div>

                        <div class="preview-metrics">
                            <article
                                v-for="metric in dashboardPreviewMetrics"
                                :key="metric.label"
                                class="preview-metric"
                            >
                                <div class="preview-metric-head">
                                    <span>{{ metric.label }}</span>
                                    <strong>{{ metric.value }}</strong>
                                </div>
                                <div class="preview-track">
                                    <div class="preview-fill" :class="metric.tone" :style="{ '--progress': `${metric.progress}%` }" />
                                </div>
                            </article>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="social-proof reveal-node" :ref="registerReveal">
                <p>Équipes qui opèrent déjà avec Support & Paiements</p>
                <div class="social-grid">
                    <span v-for="name in socialProof" :key="name">{{ name }}</span>
                </div>
            </section>

            <section id="features" class="features-section reveal-node" :ref="registerReveal">
                <header>
                    <p class="section-kicker">Fonctionnalités clés</p>
                    <h2>Un design système pensé pour l’exécution.</h2>
                </header>

                <div class="features-grid">
                    <article v-for="feature in featureCards" :key="feature.title" class="feature-card">
                        <span class="feature-icon">{{ feature.icon }}</span>
                        <h3>{{ feature.title }}</h3>
                        <p>{{ feature.description }}</p>
                    </article>
                </div>
            </section>

            <section id="workflow" class="workflow-section reveal-node" :ref="registerReveal">
                <header>
                    <p class="section-kicker">Comment ça marche</p>
                    <h2>De l’incident à la résolution, sans friction.</h2>
                </header>

                <div class="workflow-grid">
                    <article v-for="(step, index) in workflowSteps" :key="step.title" class="workflow-step">
                        <span class="step-index">0{{ index + 1 }}</span>
                        <h3>{{ step.title }}</h3>
                        <p>{{ step.description }}</p>
                    </article>
                </div>
            </section>

            <section id="stats" class="stats-section reveal-node" :ref="registerReveal">
                <header>
                    <p class="section-kicker">Indicateurs</p>
                    <h2>Performance opérationnelle lisible en un regard.</h2>
                </header>

                <div class="stats-grid">
                    <article v-for="stat in statsCards" :key="stat.label" class="stat-card">
                        <p>{{ stat.label }}</p>
                        <strong>{{ stat.value }}</strong>
                        <span>{{ stat.note }}</span>
                    </article>
                </div>
            </section>

            <section id="cta" class="final-cta reveal-node" :ref="registerReveal">
                <div>
                    <p class="section-kicker">Prêt à déployer</p>
                    <h2>Transformez vos opérations support et paiement.</h2>
                    <p>
                        Lancez un espace premium, sécurisé et orienté résultats pour vos équipes support, finance et produit.
                    </p>
                </div>

                <div class="final-cta-actions">
                    <Link v-if="canLogin" :href="route('login')" class="btn btn-primary btn-xl">
                        {{ t('welcome.cta_login', 'Accéder à l’espace') }}
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn btn-ghost btn-xl">
                        {{ t('welcome.cta_register', 'Créer un compte') }}
                    </Link>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.landing-saas {
    --primary: #4f46e5;
    --bg: #f9fafb;
    --card: #ffffff;
    --border: #e5e7eb;
    --title: #111827;
    --body: #374151;
    --secondary: #6b7280;
    min-height: 100vh;
    background: var(--bg);
    color: var(--body);
    position: relative;
    overflow: hidden;
    padding: 32px 20px 64px;
}

.landing-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(17, 24, 39, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(17, 24, 39, 0.04) 1px, transparent 1px);
    background-size: 28px 28px;
    mask-image: radial-gradient(circle at 50% 12%, rgba(0, 0, 0, 0.9) 32%, transparent 82%);
    pointer-events: none;
}

.landing-glow {
    position: absolute;
    border-radius: 999px;
    filter: blur(58px);
    pointer-events: none;
}

.landing-glow-left {
    width: 340px;
    height: 340px;
    top: -130px;
    left: -110px;
    background: rgba(79, 70, 229, 0.22);
}

.landing-glow-right {
    width: 360px;
    height: 360px;
    top: -90px;
    right: -130px;
    background: rgba(56, 189, 248, 0.2);
}

.container-shell {
    width: min(1200px, 100%);
    margin: 0 auto;
    position: relative;
    z-index: 1;
    display: grid;
    gap: 28px;
}

.top-nav {
    border: 1px solid var(--border);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    border-radius: 16px;
    min-height: 72px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 14px;
}

.brand {
    text-decoration: none;
    color: inherit;
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.brand-mark {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(140deg, #4338ca, var(--primary));
    display: grid;
    place-items: center;
    color: #fff;
    font-weight: 800;
    letter-spacing: 0.02em;
    box-shadow: 0 8px 20px -10px rgba(79, 70, 229, 0.7);
}

.brand-kicker {
    display: block;
    font-size: 10px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--secondary);
    font-weight: 700;
}

.brand-title {
    font-size: 25px;
    line-height: 1;
    font-weight: 800;
    color: var(--title);
    letter-spacing: -0.02em;
}

.nav-center {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 1px solid var(--border);
    background: #fff;
    border-radius: 10px;
    padding: 3px;
}

.nav-link {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--secondary);
    text-decoration: none;
    padding: 8px 10px;
    border-radius: 8px;
    transition: all 180ms ease;
}

.nav-link:hover {
    color: var(--title);
    background: #f3f4f6;
}

.nav-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.locale-switch {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    border: 1px solid var(--border);
    background: #fff;
    border-radius: 10px;
    padding: 3px;
}

.locale-pill {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--secondary);
    text-decoration: none;
    padding: 7px 9px;
    border-radius: 7px;
    transition: all 180ms ease;
}

.locale-pill:hover {
    background: #f3f4f6;
    color: var(--title);
}

.locale-pill.is-active {
    background: #111827;
    color: #fff;
}

.btn {
    border-radius: 8px;
    border: 1px solid transparent;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: -0.01em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease, border-color 180ms ease, color 180ms ease;
}

.btn:hover {
    transform: translateY(-1px) scale(1.01);
}

.btn-primary {
    background: linear-gradient(135deg, #4338ca, var(--primary));
    color: #fff;
    box-shadow: 0 12px 22px -14px rgba(79, 70, 229, 0.9);
}

.btn-primary:hover {
    box-shadow: 0 18px 28px -16px rgba(79, 70, 229, 0.95);
}

.btn-ghost {
    background: #fff;
    border-color: var(--border);
    color: var(--title);
}

.btn-ghost:hover {
    background: #f3f4f6;
}

.btn-xl {
    min-width: 180px;
    min-height: 44px;
}

.hero-section {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
    gap: 22px;
    align-items: stretch;
}

.hero-copy {
    border: 1px solid var(--border);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 22px 42px -30px rgba(17, 24, 39, 0.28);
    padding: 36px;
}

.hero-pill {
    display: inline-flex;
    border-radius: 999px;
    border: 1px solid rgba(79, 70, 229, 0.22);
    background: rgba(79, 70, 229, 0.08);
    color: var(--primary);
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
}

.hero-copy h1 {
    margin-top: 16px;
    color: var(--title);
    font-size: clamp(36px, 5vw, 58px);
    line-height: 1.04;
    letter-spacing: -0.045em;
    max-width: 12ch;
}

.hero-copy h1 span {
    display: block;
}

.hero-copy p {
    margin-top: 16px;
    max-width: 56ch;
    font-size: 16px;
    color: var(--body);
}

.hero-ctas {
    margin-top: 24px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.preview-wrap {
    position: relative;
    border: 1px solid #dbe2ff;
    border-radius: 20px;
    background:
        radial-gradient(circle at 84% 12%, rgba(79, 70, 229, 0.28), rgba(79, 70, 229, 0) 58%),
        linear-gradient(165deg, #131a29 0%, #1f2b44 100%);
    box-shadow: 0 24px 46px -32px rgba(15, 23, 42, 0.65);
    overflow: hidden;
    min-height: 420px;
    padding: 26px;
}

.preview-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(2, 6, 23, 0.1), rgba(2, 6, 23, 0.5));
    pointer-events: none;
}

.preview-card {
    position: relative;
    z-index: 1;
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 16px;
    backdrop-filter: blur(8px);
    background: rgba(15, 23, 42, 0.45);
    padding: 16px;
}

.preview-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.preview-kicker {
    color: #93c5fd;
    font-size: 11px;
    letter-spacing: 0.14em;
    font-weight: 700;
    text-transform: uppercase;
}

.preview-head h3 {
    color: #f8fafc;
    font-size: 24px;
    line-height: 1.1;
    margin-top: 4px;
    letter-spacing: -0.02em;
}

.live-dot {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #cbd5e1;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.live-dot::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #34d399;
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.45);
    animation: pulse-live 1.8s infinite;
}

.preview-metrics {
    display: grid;
    gap: 10px;
}

.preview-metric {
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.55);
    padding: 12px;
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
}

.preview-metric:hover {
    transform: translateY(-1px);
    border-color: rgba(148, 163, 184, 0.45);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.preview-metric-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 7px;
}

.preview-metric-head span {
    color: #cbd5e1;
    font-size: 12px;
    font-weight: 600;
}

.preview-metric-head strong {
    color: #ffffff;
    font-size: 14px;
    letter-spacing: -0.01em;
}

.preview-track {
    height: 7px;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.28);
    overflow: hidden;
}

.preview-fill {
    height: 100%;
    border-radius: 999px;
    width: var(--progress);
    transform-origin: left;
    animation: fill-grow 1000ms ease both;
}

.tone-primary {
    background: linear-gradient(90deg, #4f46e5, #818cf8);
}

.tone-danger {
    background: linear-gradient(90deg, #ef4444, #fb7185);
}

.tone-success {
    background: linear-gradient(90deg, #10b981, #34d399);
}

.tone-info {
    background: linear-gradient(90deg, #0ea5e9, #38bdf8);
}

.social-proof {
    border: 1px solid var(--border);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(8px);
    padding: 22px 26px;
}

.social-proof > p {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--secondary);
    font-weight: 700;
    margin-bottom: 14px;
}

.social-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 10px;
}

.social-grid span {
    min-height: 52px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #fff;
    display: grid;
    place-items: center;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.02em;
    transition: transform 180ms ease, box-shadow 180ms ease;
}

.social-grid span:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.features-section,
.workflow-section,
.stats-section,
.final-cta {
    border: 1px solid var(--border);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 16px 30px -28px rgba(15, 23, 42, 0.34);
    padding: 34px;
}

.section-kicker {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--secondary);
    font-weight: 700;
}

.features-section h2,
.workflow-section h2,
.stats-section h2,
.final-cta h2 {
    margin-top: 10px;
    color: var(--title);
    font-size: clamp(28px, 3.8vw, 42px);
    line-height: 1.08;
    letter-spacing: -0.035em;
    max-width: 16ch;
}

.features-grid {
    margin-top: 22px;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.feature-card {
    border: 1px solid var(--border);
    border-radius: 14px;
    background: #fff;
    padding: 20px;
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
}

.feature-card:hover {
    transform: translateY(-2px);
    border-color: #d0d9ff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.feature-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(79, 70, 229, 0.12);
    color: var(--primary);
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 12px;
}

.feature-card h3 {
    color: var(--title);
    font-size: 17px;
    letter-spacing: -0.01em;
}

.feature-card p {
    margin-top: 8px;
    color: var(--secondary);
    font-size: 14px;
    line-height: 1.6;
}

.workflow-grid {
    margin-top: 22px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.workflow-step {
    border: 1px solid var(--border);
    border-radius: 14px;
    background: #fff;
    padding: 18px;
    transition: transform 180ms ease, box-shadow 180ms ease;
}

.workflow-step:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.step-index {
    display: inline-flex;
    border: 1px solid var(--border);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    padding: 4px 10px;
}

.workflow-step h3 {
    margin-top: 12px;
    color: var(--title);
    font-size: 15px;
    letter-spacing: -0.01em;
}

.workflow-step p {
    margin-top: 8px;
    color: var(--secondary);
    font-size: 13px;
    line-height: 1.6;
}

.stats-grid {
    margin-top: 22px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.stat-card {
    border: 1px solid var(--border);
    border-radius: 14px;
    background: #fff;
    padding: 18px;
    transition: transform 180ms ease, box-shadow 180ms ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.stat-card p {
    font-size: 12px;
    color: var(--secondary);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.stat-card strong {
    display: block;
    margin-top: 10px;
    font-size: 32px;
    color: var(--title);
    line-height: 1;
    letter-spacing: -0.03em;
}

.stat-card span {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    color: var(--secondary);
}

.final-cta {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: end;
    gap: 22px;
}

.final-cta p {
    margin-top: 12px;
    font-size: 15px;
    line-height: 1.6;
    color: var(--secondary);
    max-width: 56ch;
}

.final-cta-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.reveal-node {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.45s ease, transform 0.45s ease;
}

.reveal-node.is-visible {
    opacity: 1;
    transform: translateY(0);
}

@keyframes pulse-live {
    70% {
        box-shadow: 0 0 0 10px rgba(52, 211, 153, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(52, 211, 153, 0);
    }
}

@keyframes fill-grow {
    from {
        transform: scaleX(0.25);
        opacity: 0.5;
    }
    to {
        transform: scaleX(1);
        opacity: 1;
    }
}

@media (max-width: 1100px) {
    .hero-section {
        grid-template-columns: 1fr;
    }

    .features-grid {
        grid-template-columns: 1fr 1fr;
    }

    .workflow-grid {
        grid-template-columns: 1fr 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }

    .social-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .final-cta {
        grid-template-columns: 1fr;
    }

    .final-cta-actions {
        justify-content: flex-start;
    }

    .nav-center {
        display: none;
    }
}

@media (max-width: 760px) {
    .landing-saas {
        padding-inline: 14px;
    }

    .top-nav {
        flex-wrap: wrap;
    }

    .nav-actions {
        width: 100%;
        justify-content: space-between;
    }

    .hero-copy,
    .features-section,
    .workflow-section,
    .stats-section,
    .final-cta {
        padding: 24px;
    }

    .features-grid,
    .workflow-grid,
    .stats-grid,
    .social-grid {
        grid-template-columns: 1fr;
    }

    .brand-title {
        font-size: 21px;
    }

    .hero-copy h1 {
        font-size: clamp(34px, 12vw, 46px);
    }
}
</style>

<template>
    <div class="home">
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Unified marketing + operations</p>
                <h1>Scale your property management business without multiplying headcount.</h1>
                <p class="subtitle">Savarix connects lead capture, onboarding, and tenant workflows into one automation layer. Close faster, collect sooner, and keep your team focused on clients.</p>
                <div class="hero-ctas">
                    <button class="primary" type="button" @click="openDemo">Book a guided demo</button>
                    <button class="ghost" type="button" @click="startTrial">Start free trial</button>
                </div>
                <ul class="proof-points">
                    <li>Lead-to-lease automation</li>
                    <li>Embedded KYC and AML checks</li>
                    <li>Insights across the full funnel</li>
                </ul>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <div class="stat-card">
                    <span class="label">Conversion</span>
                    <span class="value">+32%</span>
                    <span class="hint">vs previous quarter</span>
                </div>
                <div class="stat-card">
                    <span class="label">Time to onboard</span>
                    <span class="value">4m 12s</span>
                    <span class="hint">Automated identity verification</span>
                </div>
            </div>
        </section>

        <section id="solutions" class="section">
            <header>
                <h2>Everything you need to delight landlords and tenants</h2>
                <p>Operations, finance, inspections, and comms in a single workspace connected to your CRM.</p>
            </header>
            <FeatureGrid :features="features" />
        </section>

        <section id="integrations" class="section integrations">
            <header>
                <h2>Integrations that just work</h2>
                <p>Sync data with the systems your revenue, finance, and compliance teams already use.</p>
            </header>
            <ul class="integration-list">
                <li v-for="integration in integrations" :key="integration">{{ integration }}</li>
            </ul>
        </section>

        <LeadForm />

        <section id="pricing" class="section pricing">
            <header>
                <h2>Simple pricing that scales with you</h2>
                <p>No implementation fees. Spin up a proof-of-concept in minutes and graduate when you&apos;re ready.</p>
            </header>
            <div class="pricing-grid">
                <article class="pricing-card">
                    <h3>Growth</h3>
                    <p class="price">£249<span>/mo</span></p>
                    <ul>
                        <li>Up to 250 active units</li>
                        <li>Marketing automation suite</li>
                        <li>Digital onboarding with eSign + KYC</li>
                    </ul>
                </article>
                <article class="pricing-card highlighted">
                    <h3>Scale</h3>
                    <p class="price">Custom</p>
                    <ul>
                        <li>Unlimited portfolios</li>
                        <li>Dedicated CSM &amp; integrations</li>
                        <li>Advanced analytics workspace</li>
                    </ul>
                </article>
            </div>
        </section>
    </div>
</template>

<script setup>
import { inject, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import FeatureGrid from '../components/FeatureGrid.vue';
import LeadForm from '../components/LeadForm.vue';

const analytics = inject('analytics');
const sessionId = inject('marketingSession');
const route = useRoute();
const router = useRouter();

const features = [
    {
        title: 'Marketing CRM',
        emoji: '📈',
        description: 'Track every landlord and tenant lead, score intent automatically, and trigger nurture journeys.'
    },
    {
        title: 'Onboarding automation',
        emoji: '✅',
        description: 'Identity verification, document requests, and tenancy creation in one guided flow.'
    },
    {
        title: 'Payments & arrears',
        emoji: '💸',
        description: 'Direct debit, card, and instant bank transfers with live arrears tracking.'
    },
    {
        title: 'Maintenance intelligence',
        emoji: '🛠️',
        description: 'Route jobs automatically, capture evidence, and sync updates with landlords and tenants.'
    }
];

const integrations = [
    'HubSpot',
    'Salesforce',
    'Xero',
    'QuickBooks',
    'Rightmove',
    'Zoopla',
    'Stripe',
    'DocuSign'
];

function startTrial() {
    analytics?.track('marketing.cta_click', { target: 'hero_trial' }, sessionId);
    router.push({ name: 'signup' });
}

function openDemo() {
    analytics?.track('marketing.cta_click', { target: 'hero_demo' }, sessionId);
    const element = document.getElementById('demo-form');
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    } else {
        router.push({ name: 'home', query: { focus: 'demo' } });
    }
}

onMounted(() => {
    const focus = route.query.focus;
    if (focus) {
        requestAnimationFrame(() => {
            const element = document.getElementById(focus);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
            const nextQuery = { ...route.query };
            delete nextQuery.focus;
            router.replace({ query: nextQuery });
        });
    }
});
</script>

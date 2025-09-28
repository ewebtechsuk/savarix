<template>
    <div class="app-shell">
        <header class="app-header">
            <div class="app-header__inner">
                <router-link class="brand" to="/" @click="trackNav('logo')">
                    Ressapp
                </router-link>
                <nav class="primary-nav">
                    <button class="nav-link" type="button" @click="scrollTo('solutions')">Solutions</button>
                    <button class="nav-link" type="button" @click="scrollTo('integrations')">Integrations</button>
                    <button class="nav-link" type="button" @click="scrollTo('pricing')">Pricing</button>
                </nav>
                <div class="header-ctas">
                    <router-link class="secondary" to="/signup" @click="trackNav('header_login')">Start trial</router-link>
                    <button class="primary" type="button" @click="goToDemo">Book a demo</button>
                </div>
            </div>
        </header>
        <main class="app-main">
            <router-view />
        </main>
        <footer class="app-footer">
            <div class="app-footer__inner">
                <div>
                    <strong>Ressapp</strong>
                    <p>Automation platform for ambitious property teams.</p>
                </div>
                <div class="footer-links">
                    <router-link to="/" @click="trackNav('footer_home')">Home</router-link>
                    <router-link to="/signup" @click="trackNav('footer_signup')">Start trial</router-link>
                    <a href="mailto:sales@ressapp.com" @click="trackNav('footer_email')">Contact</a>
                </div>
            </div>
            <p class="app-footer__note">&copy; {{ new Date().getFullYear() }} Ressapp. All rights reserved.</p>
        </footer>
    </div>
</template>

<script setup>
import { inject } from 'vue';
import { useRouter } from 'vue-router';

const analytics = inject('analytics');
const sessionId = inject('marketingSession');
const router = useRouter();

function trackNav(target) {
    analytics?.track('marketing.navigation', { target }, sessionId);
}

function scrollTo(anchor) {
    analytics?.track('marketing.navigation', { target: `nav_${anchor}` }, sessionId);
    const element = document.getElementById(anchor);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    } else if (router.currentRoute.value.name !== 'home') {
        router.push({ name: 'home', query: { focus: anchor } });
    }
}

function goToDemo() {
    analytics?.track('marketing.cta_click', { target: 'header_demo' }, sessionId);
    const element = document.getElementById('demo');
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    } else {
        router.push({ name: 'home', query: { focus: 'demo' } });
    }
}
</script>

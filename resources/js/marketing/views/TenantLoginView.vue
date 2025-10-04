<template>
    <div class="tenant-login">
        <section class="tenant-login__hero">
            <p class="tenant-login__eyebrow">Aktonz tenant portal</p>
            <h1>Log in to your Ressapp tenant workspace</h1>
            <p>Securely access rent statements, maintenance updates, and documents shared by the Aktonz lettings team.</p>
        </section>

        <section class="tenant-login__card" aria-labelledby="tenant-login-card-title">
            <div class="tenant-login__card-body">
                <h2 id="tenant-login-card-title">Tenant app login</h2>
                <p>
                    Use the links below to open the Aktonz tenant login page in a new tab. Sign in with the email address and
                    password you received during onboarding.
                </p>
                <div class="tenant-login__links">
                    <a
                        class="primary"
                        :href="loginUrl"
                        target="_blank"
                        rel="noopener"
                        @click="trackLogin('primary')"
                    >
                        Open Aktonz login
                    </a>
                    <a
                        class="tenant-login__alt"
                        :href="fallbackUrl"
                        target="_blank"
                        rel="noopener"
                        @click="trackLogin('fallback')"
                    >
                        Open backup login (app.ressapp.com)
                    </a>
                </div>
                <p class="tenant-login__bookmark">
                    Bookmark the backup login at <code>{{ fallbackHost }}</code> so you can still sign in if the primary Aktonz
                    domain is unavailable.
                </p>
                <ul class="tenant-login__tips">
                    <li>Use a modern browser such as Chrome, Edge, or Safari for the best experience.</li>
                    <li>Select “Forgot password” on the login screen if you need to reset your credentials.</li>
                    <li>Keep your two-factor authentication device handy if your account has MFA enabled.</li>
                </ul>
            </div>
        </section>

        <section class="tenant-login__support" aria-labelledby="tenant-login-support-title">
            <h2 id="tenant-login-support-title">Need help?</h2>
            <p>
                Email the Aktonz support team at
                <a href="mailto:support@aktonz.com" @click="trackSupport">support@aktonz.com</a>
                or call your property manager if you run into issues signing in.
            </p>
        </section>
    </div>
</template>

<script setup>
import { inject, onMounted } from 'vue';

const analytics = inject('analytics');
const sessionId = inject('marketingSession');

const loginHost = 'aktonz.darkorange-chinchilla-918430.hostingersite.com';
const fallbackHost = 'app.ressapp.com';
const loginUrl = `https://${loginHost}/login`;
const fallbackUrl = `https://${fallbackHost}/login`;

function trackLogin(target) {
    analytics?.track(
        'marketing.tenant_login_click',
        {
            tenant: 'aktonz',
            target,
        },
        sessionId,
    );
}

function trackSupport() {
    analytics?.track(
        'marketing.tenant_login_support',
        {
            tenant: 'aktonz',
        },
        sessionId,
    );
}

onMounted(() => {
    analytics?.track(
        'marketing.tenant_login_view',
        {
            tenant: 'aktonz',
        },
        sessionId,
    );
});
</script>

<template>
    <section class="trial-form">
        <h2>Launch your Savarix trial</h2>
        <p>Spin up an agency environment with the upgraded onboarding and KYC workflow in under two minutes.</p>
        <form @submit.prevent="submit">
            <label>
                <span>Company name</span>
                <input v-model="form.company" type="text" required />
            </label>
            <label>
                <span>Your name</span>
                <input v-model="form.name" type="text" required />
            </label>
            <label>
                <span>Work email</span>
                <input v-model="form.email" type="email" required />
            </label>
            <label>
                <span>Password</span>
                <input v-model="form.password" type="password" minlength="8" required />
            </label>
            <label>
                <span>Confirm password</span>
                <input v-model="form.password_confirmation" type="password" minlength="8" required />
            </label>
            <div class="form-actions">
                <button type="submit" :disabled="loading">
                    <span v-if="! loading">Create trial agency</span>
                    <span v-else>Provisioning…</span>
                </button>
                <p v-if="error" class="error">{{ error }}</p>
                <div v-if="success" class="success">
                    <p>Your agency workspace is ready! Head to <a :href="loginUrl" target="_blank" rel="noopener">{{ loginUrl }}</a> to log in and finish KYC.</p>
                </div>
            </div>
        </form>
    </section>
</template>

<script setup>
import { computed, inject, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { post } from '../services/api';

const analytics = inject('analytics');
const sessionId = inject('marketingSession');
const route = useRoute();

const form = reactive({
    company: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const loading = ref(false);
const error = ref('');
const success = ref(false);
const loginUrl = ref('');

const source = computed(() => route.query.source || route.query.utm_source || null);

async function submit() {
    if (loading.value) {
        return;
    }

    analytics?.track('marketing.signup_attempt', {
        source: source.value,
    }, sessionId);

    loading.value = true;
    error.value = '';

    try {
        const response = await post('/onboarding/register', {
            ...form,
            tracking_session: sessionId,
            source: source.value,
            password_confirmation: form.password_confirmation,
        });

        success.value = true;
        loginUrl.value = `https://${response.domain}/login`;

        analytics?.track('marketing.signup_success', {
            tenant_id: response.tenant_id,
            domain: response.domain,
        }, sessionId);
    } catch (err) {
        error.value = err.message || 'We could not create the agency workspace. Please try again.';
        analytics?.track('marketing.signup_error', {
            message: error.value,
        }, sessionId);
    } finally {
        loading.value = false;
    }
}
</script>

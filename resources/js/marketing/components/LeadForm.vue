<template>
    <section id="demo" class="lead-form">
        <div class="lead-form__content">
            <h2>Book a live walkthrough</h2>
            <p>Tell us about your portfolio and we&apos;ll line up a 30 minute demo tailored to your workflow.</p>
            <form @submit.prevent="submit" id="demo-form">
                <div class="form-grid">
                    <label>
                        <span>Full name</span>
                        <input v-model="form.name" type="text" autocomplete="name" required />
                    </label>
                    <label>
                        <span>Work email</span>
                        <input v-model="form.email" type="email" autocomplete="email" required />
                    </label>
                    <label>
                        <span>Mobile number</span>
                        <input v-model="form.phone" type="tel" autocomplete="tel" />
                    </label>
                    <label>
                        <span>Company</span>
                        <input v-model="form.company" type="text" autocomplete="organization" required />
                    </label>
                    <label>
                        <span>Portfolio size</span>
                        <select v-model="form.company_size">
                            <option value="">Select size</option>
                            <option value="1-50 units">1-50 units</option>
                            <option value="51-250 units">51-250 units</option>
                            <option value="251-1000 units">251-1,000 units</option>
                            <option value="1000+ units">1,000+ units</option>
                        </select>
                    </label>
                    <label>
                        <span>Your role</span>
                        <input v-model="form.role" type="text" placeholder="e.g. Head of Property" />
                    </label>
                    <label>
                        <span>Preferred demo time</span>
                        <input v-model="form.preferred_demo_at" type="datetime-local" required />
                    </label>
                    <label>
                        <span>Timezone</span>
                        <select v-model="form.timezone">
                            <option v-for="zone in timezones" :key="zone" :value="zone">{{ zone }}</option>
                        </select>
                    </label>
                </div>
                <label class="full-width">
                    <span>What should we cover?</span>
                    <textarea v-model="form.notes" rows="3" placeholder="Integrations, workflows, migration support..." />
                </label>
                <div class="form-actions">
                    <button type="submit" :disabled="loading">
                        <span v-if="! loading">Schedule my demo</span>
                        <span v-else>Scheduling…</span>
                    </button>
                    <p v-if="error" class="error">{{ error }}</p>
                    <p v-if="success" class="success">Thanks! We&apos;ve locked the session and emailed the calendar invite.</p>
                </div>
            </form>
        </div>
    </section>
</template>

<script setup>
import { inject, reactive, ref } from 'vue';
import { post } from '../services/api';

const analytics = inject('analytics');
const sessionId = inject('marketingSession');

const detectedTimezone = (() => {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (error) {
        return 'UTC';
    }
})();

const timezones = [
    detectedTimezone,
    'UTC',
    'Europe/London',
    'Europe/Dublin',
    'Europe/Berlin',
    'America/New_York',
    'America/Chicago',
    'America/Los_Angeles',
    'Asia/Singapore',
    'Australia/Sydney',
].filter((value, index, array) => array.indexOf(value) === index);

const form = reactive({
    name: '',
    email: '',
    phone: '',
    company: '',
    company_size: '',
    role: '',
    notes: '',
    preferred_demo_at: '',
    timezone: detectedTimezone,
});

const loading = ref(false);
const error = ref('');
const success = ref(false);

async function submit() {
    if (loading.value || success.value) {
        return;
    }

    analytics?.track('marketing.lead_form_submit', {
        timezone: form.timezone,
        company_size: form.company_size,
    }, sessionId);

    loading.value = true;
    error.value = '';

    try {
        const response = await post('/api/marketing/leads', {
            ...form,
            tracking_session: sessionId,
        });
        success.value = true;
        analytics?.track('marketing.lead_form_success', {
            lead_id: response.lead_id,
            demo_id: response.demo_id,
        }, sessionId);
    } catch (err) {
        error.value = err.message || 'Something went wrong. Please try again.';
        analytics?.track('marketing.lead_form_error', {
            message: error.value,
        }, sessionId);
    } finally {
        loading.value = false;
    }
}
</script>

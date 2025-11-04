<template>
    <div class="signup">
        <section class="signup-hero">
            <h1>Start your Savirix trial</h1>
            <p>Provision a tenant sandbox instantly, invite your team, and experience the automated onboarding + KYC flow.</p>
        </section>
        <TrialSignupForm />
        <section class="signup-faq">
            <h2>What happens next?</h2>
            <ol>
                <li>We provision your tenant environment and seed it with demo data so you can explore.</li>
                <li>Kick off the live KYC workflow to understand how identity, AML, and document checks work.</li>
                <li>Invite teammates, connect your integrations, and upgrade when you&apos;re ready.</li>
            </ol>
        </section>
    </div>
</template>

<script setup>
import TrialSignupForm from '../components/TrialSignupForm.vue';
</script>

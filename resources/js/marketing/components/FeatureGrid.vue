<template>
    <div class="feature-grid">
        <article v-for="feature in features" :key="feature.title" class="feature-card">
            <div class="feature-icon" aria-hidden="true">{{ feature.emoji }}</div>
            <h3>{{ feature.title }}</h3>
            <p>{{ feature.description }}</p>
        </article>
    </div>
</template>

<script setup>
const props = defineProps({
    features: {
        type: Array,
        required: true,
    },
});
</script>

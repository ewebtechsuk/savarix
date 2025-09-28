import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import './styles.css';
import { analytics } from './services/analytics';

const app = createApp(App);

app.provide('analytics', analytics);
app.provide('marketingSession', analytics.sessionId);
app.use(router);

router.isReady().then(() => {
    app.mount('#marketing-app');
    analytics.track('marketing.page_view', {
        path: window.location.pathname,
        referrer: document.referrer || null,
    });
});

const SESSION_KEY = 'savirix_marketing_session';

function generateSessionId() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
        return crypto.randomUUID();
    }

    return Math.random().toString(36).slice(2) + Date.now().toString(36);
}

class AnalyticsClient {
    constructor() {
        const stored = typeof window !== 'undefined'
            ? window.localStorage.getItem(SESSION_KEY)
            : null;

        this.sessionId = stored || generateSessionId();

        if (! stored && typeof window !== 'undefined') {
            window.localStorage.setItem(SESSION_KEY, this.sessionId);
        }
    }

    track(event, metadata = {}, sessionId = this.sessionId) {
        if (typeof window === 'undefined') {
            return;
        }

        const payload = JSON.stringify({
            event,
            metadata,
            session: sessionId,
            occurred_at: new Date().toISOString(),
        });

        try {
            if (navigator.sendBeacon) {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon('/api/marketing/events', blob);
            } else {
                fetch('/api/marketing/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: payload,
                    keepalive: true,
                }).catch(() => {});
            }
        } catch (error) {
            console.warn('Analytics dispatch failed', error);
        }
    }
}

export const analytics = new AnalyticsClient();

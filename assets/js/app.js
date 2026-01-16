const cards = document.querySelectorAll('.card');
let startX = 0;
let currentCard = null;

cards.forEach((card) => {
    card.addEventListener('touchstart', (event) => {
        startX = event.touches[0].clientX;
        currentCard = card;
        card.style.transition = 'none';
    });

    card.addEventListener('touchmove', (event) => {
        if (!currentCard) return;
        const deltaX = event.touches[0].clientX - startX;
        currentCard.style.transform = `translateX(${deltaX}px) rotate(${deltaX / 20}deg)`;
    });

    card.addEventListener('touchend', () => {
        if (!currentCard) return;
        currentCard.style.transition = 'transform 0.3s ease';
        currentCard.style.transform = 'translateX(0)';
        currentCard = null;
    });
});

const pushSubscribeButton = document.querySelector('[data-push-subscribe]');
const pushUnsubscribeButton = document.querySelector('[data-push-unsubscribe]');

const base64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; i += 1) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
};

const sendSubscription = async (url, subscription) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: window.APP_CONFIG?.csrfToken,
            subscription
        })
    });

    return response.json();
};

const registerPush = async () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
    if (!window.APP_CONFIG?.vapidPublicKey) {
        alert('VAPID ключ не настроен.');
        return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        alert('Разрешение на уведомления не получено.');
        return;
    }

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: base64ToUint8Array(window.APP_CONFIG.vapidPublicKey)
    });

    await sendSubscription('/api/push/subscribe', subscription.toJSON());
};

const unregisterPush = async () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();
    if (!subscription) return;
    await sendSubscription('/api/push/unsubscribe', subscription.toJSON());
    await subscription.unsubscribe();
};

if (pushSubscribeButton) {
    pushSubscribeButton.addEventListener('click', () => {
        registerPush().catch(() => alert('Не удалось включить push.'));
    });
}

if (pushUnsubscribeButton) {
    pushUnsubscribeButton.addEventListener('click', () => {
        unregisterPush().catch(() => alert('Не удалось отключить push.'));
    });
}

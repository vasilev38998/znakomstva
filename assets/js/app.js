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

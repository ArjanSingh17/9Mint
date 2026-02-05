document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('checkoutExpiry');
    if (!el) return;

    const expiresAt = el.dataset.expiresAt ? new Date(el.dataset.expiresAt) : null;
    if (!expiresAt || Number.isNaN(expiresAt.getTime())) {
        el.textContent = '';
        return;
    }

    const form = el.closest('form');
    const submitButton = form ? form.querySelector('button[type="submit"]') : null;

    const update = () => {
        const now = new Date();
        const diffMs = expiresAt - now;
        if (diffMs <= 0) {
            el.textContent = 'Checkout expired. Please return to your cart.';
            if (submitButton) submitButton.disabled = true;
            return;
        }
        const totalSeconds = Math.floor(diffMs / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        el.textContent = `Checkout expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
    };

    update();
    setInterval(update, 1000);
});

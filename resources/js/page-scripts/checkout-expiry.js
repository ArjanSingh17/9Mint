document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('checkoutExpiry');
    if (!el) return;
    const header = document.querySelector('header');

    const expiresAt = el.dataset.expiresAt ? new Date(el.dataset.expiresAt) : null;
    if (!expiresAt || Number.isNaN(expiresAt.getTime())) {
        el.textContent = '';
        return;
    }

    const form = document.querySelector('.checkoutContainer form');
    const submitButton = form ? form.querySelector('button[type="submit"]') : null;

    const updateBannerOffset = () => {
        if (!header) {
            document.documentElement.style.setProperty('--checkout-expiry-top', '0px');
            return;
        }

        const rect = header.getBoundingClientRect();
        // Keep banner under header only near the top of the page.
        // Once user scrolls down, pin banner to viewport top to avoid transitional gaps.
        const nearTop = window.scrollY <= 12;
        const top = nearTop ? Math.max(0, Math.ceil(rect.height)) : 0;
        document.documentElement.style.setProperty('--checkout-expiry-top', `${top}px`);
    };

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
    updateBannerOffset();
    setInterval(update, 1000);
    window.addEventListener('scroll', updateBannerOffset, { passive: true });
    window.addEventListener('resize', updateBannerOffset);
});

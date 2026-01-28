// Collection page: size selection UX for "add to basket" forms.

document.addEventListener('DOMContentLoaded', () => {
    // Size selection
    document.querySelectorAll('.size-option').forEach((sizeContainer) => {
        const sizeButtons = sizeContainer.querySelectorAll('button');
        const nftInfo = sizeContainer.closest('.nft-info');
        const form = nftInfo ? nftInfo.querySelector('form') : null;

        if (!form) return;

        // Hidden input
        let sizeInput = form.querySelector('input[name="size"]');
        if (!sizeInput) {
            sizeInput = document.createElement('input');
            sizeInput.type = 'hidden';
            sizeInput.name = 'size';
            form.appendChild(sizeInput);
        }

        sizeButtons.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                sizeButtons.forEach((btn) => btn.classList.remove('selected'));
                button.classList.add('selected');
                sizeInput.value = (button.textContent || '').trim().toLowerCase();
            });
        });

        form.addEventListener('submit', (e) => {
            if (!sizeInput.value) {
                e.preventDefault();
                alert('Please select a size before adding to basket');
            }
        });
    });
});


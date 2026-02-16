// Products page: rotates collection preview images using the `data-images` attribute.

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.collection-preview[data-images]').forEach((img) => {
        let images = [];
        try {
            images = JSON.parse(img.dataset.images || '[]');
        } catch {
            images = [];
        }

        if (!Array.isArray(images) || images.length <= 1) return;

        let index = 0;
        setInterval(() => {
            index = (index + 1) % images.length;
            img.src = images[index];
        }, 3000); // rotation ms
    });
});


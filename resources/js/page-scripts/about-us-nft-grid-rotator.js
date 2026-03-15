// About-us page: rotates the NFT grid using `data-images` on the grid element.

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector('.nft-grid[data-images]');
    if (!grid) return;

    const slots = Array.from(grid.querySelectorAll('img'));
    if (slots.length === 0) return;

    let allImages = [];
    try {
        allImages = JSON.parse(grid.dataset.images || '[]');
    } catch {
        allImages = [];
    }

    if (!Array.isArray(allImages) || allImages.length === 0) return;
    const total = allImages.length;

    const pickUniqueIndices = (count, totalCount) => {
        const available = Array.from({ length: totalCount }, (_, i) => i);
        const result = [];
        const picks = Math.min(count, totalCount);
        for (let i = 0; i < picks; i++) {
            const idx = Math.floor(Math.random() * available.length);
            result.push(available[idx]);
            available.splice(idx, 1);
        }
        return result;
    };

    const applyIndices = (indices) => {
        indices.forEach((imgIdx, slotIdx) => {
            if (slots[slotIdx] && allImages[imgIdx]) {
                slots[slotIdx].src = allImages[imgIdx];
            }
        });
    };

    let currentIndices = pickUniqueIndices(slots.length, total);
    applyIndices(currentIndices);

    setInterval(() => {
        if (total <= slots.length) return;
        currentIndices = pickUniqueIndices(slots.length, total);
        applyIndices(currentIndices);
    }, 3000); // rotation ms
});


import { createRoot } from 'react-dom/client';
import MarketWidget from './MarketWidget';

const rootEl = document.getElementById('nft-market-root');
if (rootEl) {
    const currencies = JSON.parse(rootEl.dataset.currencies || '[]');
    const slug = rootEl.dataset.nftSlug;
    const defaultCurrency = rootEl.dataset.defaultCurrency || 'GBP';
    const csrfToken = rootEl.dataset.csrf;
    const isAuthed = rootEl.dataset.auth === '1';
    const viewerId = rootEl.dataset.viewerId ? Number(rootEl.dataset.viewerId) : null;

    createRoot(rootEl).render(
        <MarketWidget
            slug={slug}
            currencies={currencies}
            defaultCurrency={defaultCurrency}
            csrfToken={csrfToken}
            isAuthed={isAuthed}
            viewerId={viewerId}
        />
    );
}

import { createRoot } from 'react-dom/client';
import NftDiscoveryBoard from './NftDiscoveryBoard';

// Mount
const mountEl = document.getElementById('nft-discovery-board');
if (mountEl) {
    let nfts = [];
    let currencies = [];
    const csrfToken = mountEl.dataset.csrf;
    const isAuthed = mountEl.dataset.auth === '1';
    const loginUrl = mountEl.dataset.loginUrl || '/login';
    try {
        nfts = JSON.parse(mountEl.dataset.nfts || '[]');
        currencies = JSON.parse(mountEl.dataset.currencies || '[]');
    } catch (e) {
        console.error('Failed to parse NFT data:', e);
    }
    createRoot(mountEl).render(
        <NftDiscoveryBoard
            nfts={nfts}
            currencies={currencies}
            csrfToken={csrfToken}
            isAuthed={isAuthed}
            loginUrl={loginUrl}
        />
    );
}


import { createRoot } from 'react-dom/client';
import NftDiscoveryBoard from './NftDiscoveryBoard';

// Mount
const mountEl = document.getElementById('nft-discovery-board');
if (mountEl) {
    let nfts = [];
    try {
        nfts = JSON.parse(mountEl.dataset.nfts || '[]');
    } catch (e) {
        console.error('Failed to parse NFT data:', e);
    }
    createRoot(mountEl).render(<NftDiscoveryBoard nfts={nfts} />);
}


import { useEffect } from 'react';

// Header height var
export default function useHeaderHeightCssVar() {
    useEffect(() => {
        const updateHeaderHeightVar = () => {
            const header = document.querySelector('header');
            const h = header ? header.getBoundingClientRect().height : 0;
            document.documentElement.style.setProperty('--site-header-h', `${Math.ceil(h)}px`);
        };

        updateHeaderHeightVar();
        const onResize = () => requestAnimationFrame(updateHeaderHeightVar);
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);
}


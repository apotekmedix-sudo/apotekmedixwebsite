<script>
(() => {
    const lockUrl = <?php echo json_encode(route('shopping.access.lock', ['channel' => $channel]), 512) ?>;
    const csrfToken = <?php echo json_encode(csrf_token(), 15, 512) ?>;
    const channelPath = '/products-pbf';
    const logoutPath = '/products-pbf/logout';
    let keepAccessForNavigation = false;

    const isShoppingFlowPath = (pathname) =>
        pathname === channelPath
        || pathname === '/keranjang'
        || pathname === '/cart'
        || pathname === '/checkout';

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

        try {
            const target = new URL(link.href, window.location.href);
            keepAccessForNavigation = target.origin === window.location.origin && isShoppingFlowPath(target.pathname);
        } catch {
            keepAccessForNavigation = false;
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const target = new URL(form.action || window.location.href, window.location.href);
        keepAccessForNavigation = target.origin === window.location.origin
            && (isShoppingFlowPath(target.pathname) || target.pathname === logoutPath);
    }, true);

    window.addEventListener('pagehide', () => {
        if (keepAccessForNavigation) return;
        const payload = new URLSearchParams({ _token: csrfToken });
        navigator.sendBeacon(lockUrl, payload);
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) window.location.reload();
    });
})();
</script>
<?php /**PATH C:\Users\Ali Attaziri\APOTEK MEDIX\resources\views/partials/shopping-access-lock.blade.php ENDPATH**/ ?>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.wrapper-link-enabled').forEach(function (el) {
        try {
            const url = el.dataset.wrapperLinkUrl;
            const isExternal = el.dataset.wrapperLinkExternal === 'true';
            const nofollow = el.dataset.wrapperLinkNofollow === 'true';

            if (url) {
                el.style.cursor = 'pointer';

                el.addEventListener('click', function (e) {
                    if (e.target.closest('a, button, input, textarea')) return;

                    const a = document.createElement('a');
                    a.href = url;
                    if (isExternal) a.target = '_blank';
                    if (nofollow) a.rel = 'nofollow';
                    a.style.display = 'none';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                });
            }
        } catch (err) {
            // fail silently
            console.error('Wrapper Link error', err);
        }
    });
});

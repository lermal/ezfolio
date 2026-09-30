@if (!empty($portfolioConfig['googleAnalyticsId']) && $portfolioConfig['googleAnalyticsId'] != '')
<!-- Global site tag (gtag.js) - Google Analytics -->
{{-- gtag.js is loaded after the page, when the browser is idle; calls made before that are queued in dataLayer --}}
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', @json($portfolioConfig['googleAnalyticsId']));

    window.addEventListener('load', function () {
        var inject = function () {
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(@json($portfolioConfig['googleAnalyticsId']));
            document.head.appendChild(script);
        };

        if ('requestIdleCallback' in window) {
            requestIdleCallback(inject, { timeout: 3000 });
        } else {
            setTimeout(inject, 1500);
        }
    });
</script>
@endif

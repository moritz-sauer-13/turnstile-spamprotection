<div id="turnstile-{$ID}"
     class="cf-turnstile"
     data-sitekey="$SiteKey"
     data-callback="javascriptCallback"
     data-size="$Size"
     data-theme="$Theme"></div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        turnstile.ready(function () {
            turnstile.render("#turnstile-{$ID}", {
                sitekey: "$SiteKey",
                callback: function (token) {
                },
            });
        });
    })
</script>
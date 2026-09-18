<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Config generada por PHP, consumida por assets/app.js
    const APP_VERSION    = <?= json_encode(APP_VERSION) ?>;
    const VISITOR_SERVER = <?= json_encode([
        'ip'   => $visitorIp,
        'ua'   => $visitorUa,
        'lang' => $visitorLang,
        'ref'  => $visitorRef,
    ]) ?>;
</script>
<script src="assets/app.js?v=<?= APP_VERSION ?>"></script>

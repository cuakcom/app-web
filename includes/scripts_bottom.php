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
    // key (usado en enlaces ?tab=) -> id del botón de esa pestaña, generado desde menu.php
    const MENU_BTN_IDS = <?= json_encode(array_combine(
        array_column(MENU, 'key'),
        array_column(MENU, 'btn_id')
    )) ?>;
</script>
<script src="assets/app.js?v=<?= APP_VERSION ?>"></script>
<script src="assets/utilidades.js?v=<?= APP_VERSION ?>"></script>
<script src="assets/desarrollo.js?v=<?= APP_VERSION ?>"></script>

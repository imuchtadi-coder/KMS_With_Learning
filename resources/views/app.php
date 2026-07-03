<!DOCTYPE html>
<html lang="<?= str_replace('_', '-', app()->getLocale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/img/logok.png">
    <title inertia><?= config('app.name') ?></title>
    <?= \Illuminate\Support\Facades\Vite::__invoke(['resources/js/app.js']) ?>
<?php
    $__inertiaSsrResponse = app(\Inertia\Ssr\SsrState::class)->setPage($page)->dispatch();
    if ($__inertiaSsrResponse) {
        echo $__inertiaSsrResponse->head;
    }
?>
</head>
<body>
<?php
    if ($__inertiaSsrResponse) {
        echo $__inertiaSsrResponse->body;
    } else {
        ?><script data-page="app" type="application/json"><?= json_encode($page) ?></script><div id="app"></div><?php
    }
?>
</body>
</html>

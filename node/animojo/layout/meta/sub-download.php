<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= !$data_array ? 'Error' : ('Download ' . $title . ' subtitles') ?></title>
<link rel="shortcut icon" href="/images/favicon.png?v=<?= $version ?>" type="image/x-icon">
<style>
    <?php echo minify($_SERVER['DOCUMENT_ROOT'] . '/css/style.css') ?>
</style>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v7.0.0/css/all.css">
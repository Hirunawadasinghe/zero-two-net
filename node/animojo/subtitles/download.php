<?php
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/_config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/minify.php';
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/encrypt.php';

$file_expire_time = 60 * 60 * 24; // 1 day

if (!empty($_POST['b']) && !empty($_POST['f'])) {
    header('Content-Type: application/json');

    $de_base = decrypt_srt($_POST['b'], $encryption_key);
    $de_file = decrypt_srt($_POST['f'], $encryption_key);
    if (!$de_base || !$de_file)
        die(json_encode(['status' => false, 'msg' => 'Invalid token']));

    $data = json_decode($de_base, true);

    if (json_last_error() !== JSON_ERROR_NONE)
        die(json_encode(['status' => false, 'msg' => 'Token error']));
    if ($data['t'] + $file_expire_time < time())
        die(json_encode(['status' => false, 'msg' => 'Link expired']));

    $data['f'] = $de_file;

    $zip_folder = basename($data['p']) . '-e' . pathinfo($data['f'], PATHINFO_FILENAME) . '-' . strtolower($site_name) . '-' . $data['l'];
    $zip_file = "d/$zip_folder.zip";

    if (!file_exists($zip_file)) {
        $sub_data = [];

        $try = 3;
        while (!$sub_data && $try > 0) {
            $sub_data = json_decode(@file_get_contents($data['b'] . '/' . rawurlencode($data['f'])), true);
            if (!$sub_data)
                usleep(100000); // wait 100 miliseconds before retry
            $try--;
        }

        if (!$sub_data || !$sub_data['status'])
            die(json_encode(['status' => false, 'msg' => 'Download error']));

        if (!file_exists($zip_folder))
            mkdir($zip_folder, 0777, true);

        file_put_contents("$zip_folder/$zip_folder." . pathinfo($data['f'], PATHINFO_EXTENSION), $sub_data['data'], LOCK_EX);
        copy('read-me.html', "$zip_folder/Read Me - $site_name.html");

        require $_SERVER['DOCUMENT_ROOT'] . '/_inc/pclzip.lib.php';

        if (!file_exists('d'))
            mkdir('d', 0777, true);

        $archive = new PclZip($zip_file);
        if (!$archive->create($zip_folder))
            die(json_encode(['status' => false, 'msg' => 'Error zipping files']));

        array_map('unlink', glob("$zip_folder/*"));
        rmdir($zip_folder);
    }

    // cleanup expired zip files
    foreach (glob("d/*") as $f) {
        if ($f !== $zip_file && is_file($f) && (time() - filemtime($f) > $file_expire_time))
            unlink($f);
    }

    include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';
    get_sub_downloads($data['i'], ['folder' => $data['l'] . '/' . $data['p'], 'file' => $data['f']], 1);
    die(json_encode(['status' => true, 'd' => $zip_file]));
}

$data_array = null;
$en_base_str = empty($_GET['b']) ? null : $_GET['b'];
$en_file_str = empty($_GET['f']) ? null : $_GET['f'];

if ($en_base_str && $en_file_str) {
    $base_str = decrypt_srt($en_base_str, $encryption_key);
    $file_str = decrypt_srt($en_file_str, $encryption_key);

    $base_d = json_decode($base_str, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $data_array = $base_d;
        $data_array['f'] = $file_str;
    }
}

if (!$data_array)
    require $_SERVER['DOCUMENT_ROOT'] . '/error.php';

$title = basename($data_array['p']) . '-e' . pathinfo($data_array['f'], PATHINFO_FILENAME);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include $_SERVER["DOCUMENT_ROOT"] . '/layout/meta/sub-download.php'; ?>
    <style>
        <?php echo minify($_SERVER['DOCUMENT_ROOT'] . '/css/sub-download.css') ?>
    </style>
</head>

<body>
    <div class="header-c">
        <a href="/"><img src="https://i.ibb.co/HLS4JYY0/logo.png" alt="<?= $site_name ?>"></a>
    </div>

    <div class="content-w">
        <div class="content-c">
            <?php
            if (!$data_array || $data_array['t'] + $file_expire_time < time()) {
                echo '<h2 class="main-title">Link expired</h2>';
            } else {
                echo '<h2 class="main-title">Download Subtitle File</h2><button class="download-btn" title="' . $title . '"><i class="fa-solid fa-download bi i1"></i><span class="bi i1">' . $title . '</span></button>';
            }
            // include $_SERVER['DOCUMENT_ROOT'] . '/layout/ad/def.php';
            ?>
        </div>
    </div>

    <script>
        const d_link = <?= $data_array ? '"' . $data_array['f'] . '"' : false ?>;
        const sub_download_al = "<?= $ad_link['sub_download'] ?>";
        <?php
        echo minify($_SERVER['DOCUMENT_ROOT'] . '/js/sub_download.js');
        ?>
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/layout/footer/def.php' ?>
</body>

</html>
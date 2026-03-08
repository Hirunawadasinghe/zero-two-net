<?php
function get_db_net_cache($cache_path, $cache_file, $api_endpoint = false, $cach_time = null)
{
    $set_cache_path = $_SERVER['DOCUMENT_ROOT'] . '/cache/' . $cache_path;
    $cp = "$set_cache_path/$cache_file";

    if (!is_dir($set_cache_path))
        mkdir($set_cache_path, 0755, true);

    if ($api_endpoint === false)
        return file_exists($cp) ? include $cp : [];

    if (file_exists($cp) && filemtime($cp) + $cach_time > time())
        return include $cp;

    $data = [];

    $page = 1;
    while ($page) {
        $api_ep = $api_endpoint . (str_contains($api_endpoint, '?') ? '&' : '?') . "page=$page";
        $d = null;
        $try = 3;

        while (!$d && $try > 0) {
            $try--;
            $r = json_decode(@file_get_contents($api_ep), true);
            if (json_last_error() === JSON_ERROR_NONE && $r['status']) {
                $d = $r;
            } else {
                usleep(200000); // wait 200 miliseconds before retry
            }
        }

        if ($d) {
            $data = array_merge($data, $d['data']);
        } else {
            return [];
        }

        $page = $d['next_page'] ?? false;
    }

    if ($data) {
        $tmp = tempnam(dirname($cp), 'dbc__');
        file_put_contents($tmp, '<?php return ' . var_export($data, true) . ';');
        rename($tmp, $cp);
    }
    return $data;
}
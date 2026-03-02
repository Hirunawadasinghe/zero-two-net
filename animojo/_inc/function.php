<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_inc/db_cache.php';

// load main database
$main_data = get_db_net_cache('database', 'main');

$main_data_hash_map = [];
foreach ($main_data as $e)
    $main_data_hash_map[$e['movie_id']] = $e;

// load video database
$source_data = [];
$source_hash_map = [];
function load_source_data()
{
    global $source_data, $source_hash_map;
    if (empty($source_data)) {
        $source_data = get_db_net_cache('database', 'source');
        foreach ($source_data as $e)
            $source_hash_map[$e['id']] = $e['ep'];
    }
}

function get_sources($id)
{
    load_source_data();
    global $source_hash_map;
    return $source_hash_map[$id] ?? [];
}

// load subtitle database
$subtitle_data = [];
$subtitle_hash_map = [];
function load_subtitle_data()
{
    global $subtitle_data, $subtitle_hash_map;
    if (empty($subtitle_data)) {
        $subtitle_data = get_db_net_cache('database', 'subtitle');
        foreach ($subtitle_data['subtitles'] as $e) {
            foreach ($e['id'] as $i => $id) {
                if ($i === 0) {
                    $subtitle_hash_map[$id] = $e;
                } else {
                    $subtitle_hash_map[$id] = $e['id'][0];
                }
            }
        }
    }
}
function get_subtitle($id)
{
    load_subtitle_data();
    global $subtitle_hash_map;
    $r = $subtitle_hash_map[$id] ?? null;
    if (!$r)
        return null;
    return is_string($r) ? ['data' => $subtitle_hash_map[$r], 'id' => $r] : ['data' => $r, 'id' => $id];
}

function format_title($e)
{
    $t = $e['name'];
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t); // convert to ASCII
    $t = preg_replace('/[^a-zA-Z0-9\s-]/', '', $t); // remove unwanted characters
    $t = preg_replace('/[\s-]+/', '-', $t); // replace spaces & dashes with single dash
    $t = trim($t, '-');
    switch (strtolower($e['language'])) {
        case 'japanese':
            break;
        case 'english':
            $t .= '-dub';
            break;
        case 'dual audio':
            $t .= '-dual-audio';
            break;
        default:
            $t .= '-' . $e['language'] . '-dub';
            break;
    }
    $t .= '-' . $e['movie_id'];
    return strtolower($t);
}

function get_id_by_name($n)
{
    global $main_data;
    for ($i = count($main_data) - 1; $i >= 0; $i--) {
        if (format_title($main_data[$i]) === $n)
            return $main_data[$i]['movie_id'];
    }
    return false;
}

function get_by_mal_id($id)
{
    global $main_data;
    $r = [];
    foreach ($main_data as $e) {
        if ($e['mal_id'] && $e['mal_id'] === $id)
            $r[] = $e;
    }
    return $r;
}

function find_movie($id)
{
    global $main_data_hash_map;
    return $main_data_hash_map[$id] ?? null;
}

function format_url_str($t)
{
    return strtolower(str_replace(" ", "-", trim($t)));
}

function format_element($d, $include)
{
    $r = [];
    foreach ($d as $e) {
        $n = [];
        foreach ($include as $key) {
            switch ($key) {
                case 'link':
                    $n['link'] = format_title($e);
                    break;
                case 'episodes':
                    $src = get_sources($e['movie_id']);
                    if ($src) {
                        $n['episodes'] = array_key_last($src);
                    } else {
                        $n['episodes'] = $e['episodes'];
                    }
                    break;
                default:
                    if (key_exists($key, $e)) {
                        $n[$key] = $e[$key];
                    }
                    break;
            }
        }
        $r[] = $n;
    }
    return $r;
}

function filter_duplicates($d)
{
    $r = [];
    $tmp = [
        'name' => [],
        'mal_id' => []
    ];
    foreach ($d as $e) {
        if ($e['mal_id']) {
            if (!in_array($e['mal_id'], $tmp['mal_id'])) {
                $tmp['mal_id'][] = $e['mal_id'];
                $r[] = $e;
            }
        } else {
            if (!in_array($e['name'], $tmp['name'])) {
                $tmp['name'][] = $e['name'];
                $r[] = $e;
            }
        }
    }
    return $r;
}

function normalize_str($str)
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/[^\p{L}\p{N}\s]/u', '', $str); // remove punctuation
    return trim($str);
}

function getAllQueryParams()
{
    $p = [];
    if (isset($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $query);
        foreach ($query as $key => $value) {
            $p[] = [
                "type" => $key,
                "values" => $value
            ];
        }
    }
    return $p;
}

function searchDataBase($search_array, $search_keys, $data)
{
    $return = [];

    foreach ($data as $element) {
        $element_matched = false;

        foreach ($search_keys as $key) {
            $element_array = [];

            switch (strtolower($key)) {
                case 'name':
                    $element_array = explode(' ', normalize_str(strtolower($element['name'])));
                    break;
                case 'alt_name':
                    $element_array = explode(' ', normalize_str(strtolower($element['alt_name'])));
                    break;
                case 'year':
                    $element_array = explode(' ', normalize_str(strtolower($element['release_year'])));
                    break;
                case 'type':
                    $element_array = [strtolower($element['type'])];
                    break;
                case 'audio':
                    $element_array = [strtolower($element['language'])];
                    break;
                case 'tags':
                    foreach ($element['tags'] as $t)
                        $element_array[] = format_url_str($t);
                    break;
            }

            $common = array_intersect($search_array, $element_array);
            if (count($common) > 0) {
                $element['score'] = isset($element['score']) ? $element['score'] + count($common) : count($common);
                $element_matched = true;
            }
        }
        if ($element_matched)
            $return[] = $element;
    }
    return $return;
}

function main_search($search_attributes, $data)
{
    $sorted = false;

    foreach ($search_attributes as $attribute) {
        $attribute['values'] = strtolower($attribute['values']);

        switch ($attribute['type']) {
            case 'text':
                $data = metaphone_search($attribute['values'], ['name', 'alt_name', 'description'], $data);
                break;
            case 'sort':
                switch ($attribute['values']) {
                    case 'newest':
                        usort($data, fn($a, $b) => strtotime($b['release_year']) - strtotime($a['release_year']));
                        $sorted = true;
                        break;
                    case 'oldest':
                        usort($data, fn($a, $b) => strtotime($a['release_year']) - strtotime($b['release_year']));
                        $sorted = true;
                        break;
                    case 'name':
                        usort($data, fn($a, $b) => strcmp($a['name'], $b['name']));
                        $sorted = true;
                        break;
                }
                break;
            case 'tags':
            case 'type':
            case 'audio':
            case 'year':
                $data = searchDataBase([$attribute['values']], [$attribute['type']], $data);
                break;
        }
    }

    if (!$sorted && isset($data[0]['score']) && count($data) > 1)
        usort($data, fn($a, $b) => $b['score'] - $a['score']);

    return $data;
}

function get_similar($para, $id, $data)
{
    $r = main_search($para, $data);
    $r = array_filter($r, fn($e) => $e['movie_id'] !== $id);
    return filter_duplicates($r);
}

function get_suggestions($ids, $limit = 10, $prusice = 0.1)
{
    global $main_data, $advance_settings;
    $elements = filter_duplicates($main_data);

    $tag_data = [];
    $mal_ids = [];
    $has_vr_tags = false;

    foreach ($ids as $id) {
        $f = find_movie($id);
        if (!$f)
            continue;

        foreach ($f['tags'] as $t) {
            $tag = strtolower($t);
            if (key_exists($tag, $tag_data)) {
                $tag_data[$tag]++;
            } else {
                $tag_data[$tag] = 1;
            }
            if (!$has_vr_tags)
                $has_vr_tags = in_array($tag, $advance_settings['tags']['visibility_reduced']);
        }

        if ($f['mal_id']) {
            $mal_ids[] = $f['mal_id'];
        }
    }

    $result = [];
    foreach ($elements as $element) {
        if (in_array($element['movie_id'], $ids) || in_array($element['mal_id'], $mal_ids, true))
            continue;

        $score = 0;
        foreach ($element['tags'] as $t) {
            $tag = strtolower($t);
            if (key_exists($tag, $tag_data)) {
                $score += $tag_data[$tag];
            } else if ($prusice) {
                // reduce visibility acording to config
                if (in_array($tag, $advance_settings['tags']['visibility_reduced']) && !$has_vr_tags) {
                    $score -= $prusice * 2;
                } else {
                    $score -= $prusice;
                }
            }
        }

        if ($score > 0)
            $result[] = array_merge($element, ['score' => $score]);
    }

    usort($result, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return array_slice($result, 0, $limit);
}

function fuzzySearch($q, $data)
{
    $q = trim(preg_replace('/\s+/', ' ', $q));
    $temp_list = [];
    function find_match($t, $q)
    {
        $start = null;
        $match = 0;
        for ($c = 0; $c < strlen($t) && $c < strlen($q); $c++) {
            if (strtolower($t[$c]) === $q[$match]) {
                if ($match === 0)
                    $start = $c;
                $match++;
                if ($match === strlen($q))
                    break;
            } else {
                $match = 0;
                $start = null;
            }
        }
        if ($match === strlen($q) && $start !== null)
            return $match;
        return 0;
    }

    foreach ($data as $e) {
        $name_match = find_match(trim(preg_replace('/\s+/', ' ', $e['name'])), $q);
        $alt_name_match = find_match(trim(preg_replace('/\s+/', ' ', $e['alt_name'])), $q);
        $max_score = max($name_match, $alt_name_match);
        if ($max_score > 0)
            $temp_list[] = array_merge($e, ['score' => $max_score]);
    }

    if (count($temp_list) > 0) {
        usort($temp_list, function ($a, $b) {
            return $b['score'] - $a['score'];
        });
    }

    return $temp_list;
}

function search($query, $method, $data)
{
    $result = [];
    if ($method === 'metaphone') {
        $result = metaphone_search($query, ['name', 'alt_name'], $data);
    } else if ($method === 'main') {
        $result = main_search($query, $data);
    }
    return $result;
}

function print_bottom_nav($result, $max, $page, $path)
{
    $temp_page = $page - 1;
    $repeat = $temp_page + 3;
    if (0 - ($temp_page - 3) > 0)
        $repeat += 0 - ($temp_page - 3);

    if ($result / $max > 1) {
        echo '<style>' . minify($_SERVER['DOCUMENT_ROOT'] . '/css/bottom_nav.css') . '</style><div class="page-bottom-nav">';
        for ($i = $temp_page - 3; $i < $repeat && $i < $result / $max; $i++) {
            if ($i >= 0) {
                $url_parts = parse_url($path);
                parse_str($url_parts['query'] ?? '', $url_query);
                $url_query['page'] = $i + 1;
                $url = $url_parts['path'] . '?' . http_build_query($url_query);

                if ($i === $temp_page - 3) {
                    echo '<a href="' . $url . '" title="Page ' . ($i + 1) . '"><button><i class="fa-solid fa-caret-left"></i></button></a>';
                } elseif ($i === $repeat - 1) {
                    echo '<a href="' . $url . '" title="Page ' . ($i + 1) . '"><button><i class="fa-solid fa-caret-right"></i></button></a>';
                } else {
                    echo '<a href="' . $url . '" title="Page ' . ($i + 1) . '"><button' . ($page === $i + 1 ? ' class="select"' : '') . '>' . ($i + 1) . '</button></a>';
                }
            }
        }
        echo '</div>';
    }
}

function load_card($e, $ep_type = 'source')
{
    global $advance_settings;

    $nsfw = '<div></div>';
    if (array_intersect($advance_settings['tags']['adult_only'], array_map('strtolower', $e['tags'])))
        $nsfw = '<span class="tl">18+</span>';

    $ep_tag = '...';
    switch ($ep_type) {
        case 'source':
            $ep_f = get_sources($e['movie_id']);
            if ($ep_f) {
                $ep_tag = array_key_last($ep_f);
            }
            break;
        case 'est':
            $ep_tag = $e['episodes'];
            break;
        default:
            break;
    }

    switch (strtolower($e['language'])) {
        case 'japanese':
            $lang_tag = 'SUB';
            break;
        case 'english':
            $lang_tag = 'DUB';
            break;
        case 'dual audio':
            $lang_tag = 'Dual Audio';
            break;
        default:
            $lang_tag = $e['language'] . ' Dub';
            break;
    }

    $link = format_title($e);
    echo trim('
    <div class="movie-card">
        <a href="/watch/' . $link . '" class="thumbnail-container qtip-tag" data-id="' . $e['movie_id'] . '">
            <img src="' . ($e['images'] ? $e['images']['webp']['image_url'] : $e['thumbnail_image']) . '" alt="' . $e['name'] . '" class="movie-card-img" loading="lazy" onerror="imgError(this)">
            <div class="movie-card-top-row">' . $nsfw . '<span class="tr">' . $e['type'] . '</span></div>
            <div class="movie-card-bottom-row"><div class="tick"><span class="ep">Ep ' . $ep_tag . '</span>' . ($e['subtitle'] == 'hard' ? '<span class="sub"><i class="fa-solid fa-closed-captioning"></i></span>' : '') . '<span class="lan">' . $lang_tag . '</span></div></div>
            <i class="fa-solid fa-play play-btn"></i>
        </a>
        <div class="movie-card-t"><a href="/anime/' . $link . '" title="' . $e['name'] . '">' . $e['name'] . '</a></div>
    </div>');
}

$views_data = [];
function load_views_data()
{
    global $database_path, $views_data;
    if (!empty($views_data))
        return;

    $db_path = $database_path . '/action/ep_views';

    if (file_exists($db_path)) {
        $views_data = include $db_path;
    } else {
        file_put_contents($db_path, '<?php return ' . var_export([], true) . ';', LOCK_EX);
    }
}

function update_ep_views($id, $ep)
{
    update_ep_og_views($id, $ep);
    load_views_data();
    global $views_data, $database_path;

    $v = null;

    for ($i = 0; $i < count($views_data); $i++) {
        if ($views_data[$i]['id'] === $id) {
            if (isset($views_data[$i]['ep'][$ep])) {
                $v = $views_data[$i]['ep'][$ep] + 1;
            } else {
                $v = 1;
            }
            break;
        }
    }

    if ($v) {
        if ($v < 100)
            $v += mt_rand(500, 1200 - $v);
        $views_data[$i]['ep'][$ep] = $v;
    } else {
        $v = mt_rand(500, 1200);
        $views_data[] = ['id' => $id, 'ep' => [$ep => $v]];
    }

    $cache_file = $database_path . '/action/ep_views';

    $tmp = tempnam(dirname($cache_file), 'cache_');
    file_put_contents($tmp, '<?php return ' . var_export($views_data, true) . ';');
    rename($tmp, $cache_file);

    return $v ?? 1;
}

$og_views_data = [];
function load_og_views_data()
{
    global $database_path, $og_views_data;
    if (!empty($og_views_data))
        return;

    $db_path = $database_path . '/action/og_ep_views';

    if (file_exists($db_path)) {
        $og_views_data = include $db_path;
    } else {
        file_put_contents($db_path, '<?php return ' . var_export([], true) . ';', LOCK_EX);
    }
}
function update_ep_og_views($id, $ep)
{
    load_og_views_data();
    global $og_views_data, $database_path;

    $v = null;

    for ($i = 0; $i < count($og_views_data); $i++) {
        if ($og_views_data[$i]['id'] === $id) {
            if (isset($og_views_data[$i]['ep'][$ep])) {
                $v = $og_views_data[$i]['ep'][$ep] + 1;
            } else {
                $v = 1;
            }
            break;
        }
    }

    if ($v) {
        $og_views_data[$i]['ep'][$ep] = $v;
    } else {
        $og_views_data[] = ['id' => $id, 'ep' => [$ep => 1]];
    }

    $cache_file = $database_path . '/action/og_ep_views';

    $tmp = tempnam(dirname($cache_file), 'fn_');
    file_put_contents($tmp, '<?php return ' . var_export($og_views_data, true) . ';');
    rename($tmp, $cache_file);

    return $v ?? 1;
}


$sub_download_data = [];
function load_sub_download_data()
{
    global $database_path, $sub_download_data;
    if (!empty($sub_download_data))
        return;

    $db_path = $database_path . '/action/sub_download';

    if (file_exists($db_path)) {
        $sub_download_data = include $db_path;
    } else {
        file_put_contents($db_path, '<?php return ' . var_export([], true) . ';', LOCK_EX);
    }
}

function get_sub_downloads($id, $path, $update = null)
{
    load_sub_download_data();
    global $sub_download_data, $database_path;

    $d = $foundIndex = null;

    foreach ($sub_download_data as $i => $e) {
        if ($e['id'] === $id) {
            $foundIndex = $i;
            if (isset($e['d'][$path['folder']][$path['file']]))
                $d = $e['d'][$path['folder']][$path['file']];
            break;
        }
    }

    if ($update !== null) {
        if ($foundIndex === null) {
            $sub_download_data[] = [
                'id' => $id,
                'd' => [
                    $path['folder'] => [
                        $path['file'] => $update
                    ]
                ]
            ];
        } else {
            if (!isset($sub_download_data[$foundIndex]['d'][$path['folder']]))
                $sub_download_data[$foundIndex]['d'][$path['folder']] = [];

            if (!isset($sub_download_data[$foundIndex]['d'][$path['folder']][$path['file']]))
                $sub_download_data[$foundIndex]['d'][$path['folder']][$path['file']] = 0;

            $sub_download_data[$foundIndex]['d'][$path['folder']][$path['file']] += $update;
            $d = $sub_download_data[$foundIndex]['d'][$path['folder']][$path['file']];
        }

        $cache_file = $database_path . '/action/sub_download';

        $tmp = tempnam(dirname($cache_file), 'fn__');
        file_put_contents($tmp, '<?php return ' . var_export($sub_download_data, true) . ';');
        rename($tmp, $cache_file);
    }

    return $d;
}

function metaphone_search(string $query, array $key_names, array $data, int $tweek = 1)
{
    function str_metaphone($str)
    {
        $str = trim(preg_replace('/\s+/', ' ', $str)); // remove multiple spaces
        $words = explode(' ', $str);
        $sound = [];
        foreach ($words as $word)
            $sound[] = metaphone($word);
        return $sound;
    }

    $metaphone_query = str_metaphone($query);
    $result = [];

    foreach ($data as $entry) {
        $search_strings = [];
        foreach ($key_names as $key)
            $search_strings[] = $entry[$key];

        $score = [];
        foreach ($search_strings as $string) {
            $metaphone_value = str_metaphone($string);
            $matching_words = array_intersect($metaphone_query, $metaphone_value);
            $score[] = count($matching_words) / count($metaphone_value) * 100;
        }
        $max_score = max($score);

        if ($max_score >= $tweek)
            $result[] = array_merge($entry, ['score' => $max_score]);
    }

    usort($result, fn($a, $b) => $b['score'] - $a['score']);
    return $result;
}
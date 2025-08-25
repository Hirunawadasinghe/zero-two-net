<?php
function searchDataBase($search_array, $search_keys, $data)
{
    $return_array = [];
    foreach ($data as $element) {
        $element_matched = false;
        foreach ($search_keys as $key) {
            $element_array = [];
            $key = strtolower($key);
            switch ($key) {
                case "name":
                    $element_array = splitWords(strtolower($element['name']), [" ", "-", "–", ":", ",", "|", "/", "(", ")", "'"]);
                    break;
                case "alt_name":
                    $element_array = splitWords(strtolower($element['alt_name']), [" ", "-", "–", ":", ",", "|", "/", "(", ")", "'"]);
                    break;
                case "description":
                    $element_array = splitWords(strtolower($element['description']), [" ", "-", "–", ":", ",", "|", "/", "(", ")", "'"]);
                    break;
                case "year":
                    $element_array = splitWords(strtolower($element['release_year']), [" ", ","]);
                    break;
                case "type":
                    $element_array = [strtolower($element['type'])];
                    break;
                case "language":
                    $element_array = [strtolower($element['language'])];
                    break;
                case "tags":
                    foreach ($element['tags'] as $t) {
                        $element_array[] = strtolower(str_replace(" ", "-", trim($t)));
                    }
                    break;
            }
            $common = array_intersect($search_array, $element_array);
            if (count($common) > 0) {
                $element['score'] = isset($element['score']) ? $element['score'] + count($common) : count($common);
                $element_matched = true;
            }
        }
        if ($element_matched) {
            $return_array[] = $element;
        }
    }
    return $return_array;
}

function main_search($search_attributes, $data)
{
    $isSorted = false;
    foreach ($search_attributes as $attribute) {
        $attribute['values'] = strtolower($attribute['values']);
        if ($attribute['type'] === "text") {
            $temp = searchDataBase(splitWords($attribute['values'], [" ", "–", ":", ",", "|", "/", "(", ")", "'"]), ["name", "alt_name", "tags", "year"], $data);
            empty($temp) ? $data = fuzzySearch($attribute['values'], $data) : $data = $temp;
        } else if ($attribute['type'] === "sort") {
            $isSorted = true;
            if ($attribute['values'] === "newest") {
                usort($data, fn($a, $b) => strtotime($b['release_year']) - strtotime($a['release_year']));
            } elseif ($attribute['values'] === "oldest") {
                usort($data, fn($a, $b) => strtotime($a['release_year']) - strtotime($b['release_year']));
            } elseif ($attribute['values'] === "name") {
                usort($data, fn($a, $b) => strcmp($a['name'], $b['name']));
            }
        } else if ($attribute['type'] === "tags") {
            $data = searchDataBase(splitWords($attribute['values'], [',']), [$attribute['type']], $data);
        } else if (in_array($attribute['type'], ['type', 'language', 'year'])) {
            $data = searchDataBase([$attribute['values']], [$attribute['type']], $data);
        }
    }
    if (isset($data[0]['score']) && count($data) > 1 && !$isSorted) {
        usort($data, fn($a, $b) => $b['score'] - $a['score']);
    }
    return $data;
}

function get_suggestions($input_elements, $data)
{
    if (count($input_elements) > 0) {
        $tag_list = [];
        foreach ($input_elements as $e) {
            $element = find_movie($e);
            if ($element) {
                foreach ($element['tags'] as $new_tag) {
                    $past_tag = null;
                    foreach ($tag_list as $tag) {
                        if ($tag['name'] === $new_tag) {
                            $past_tag = $tag;
                            break;
                        }
                    }
                    if ($past_tag) {
                        $past_tag['count']++;
                    } else {
                        $t = [
                            "name" => $new_tag,
                            "count" => 1
                        ];
                        $tag_list[] = $t;
                    }
                }
            }
        }
        $tags_array = [];
        foreach ($tag_list as $tag) {
            $tags_array[] = strtolower($tag['name']);
        }
        $suggestions = searchDataBase($tags_array, ["tags"], $data);
        $suggestions = filter_duplicates($suggestions);
        usort($suggestions, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        return $suggestions;
    } else {
        return false;
    }
}

function get_similar($para, $id, $data)
{
    $r = main_search($para, $data);
    $r = array_filter($r, fn($e) => $e['movie_id'] !== $id);
    $r = filter_duplicates($r);
    return $r;
}

function calculate_fuzzy_score($item, $query)
{
    $item = str_replace(' ', '', $item);
    $query = str_replace(' ', '', $query);

    $score = 0;
    $queryIndex = 0;

    for ($i = 0; $i < strlen($item); $i++) {
        if ($queryIndex < strlen($query) && $item[$i] === $query[$queryIndex]) {
            $score++;
            $queryIndex++;
        }
    }

    return $score;
}

function fuzzySearch($query, $data)
{
    $query = strtolower(trim($query));
    if ($query === '') {
        return $data;
    }

    foreach ($data as &$e) {
        $text = $e['language'] === 'Japanese' ? $e['alt_name'] : $e['name'];
        $text = strtolower(trim($text));
        $e['score'] = calculate_fuzzy_score($text, $query);
    }

    // filter only elements with a score greater than 0
    $data = array_filter($data, function ($e) {
        return $e['score'] > 0;
    });

    // sort by score
    usort($data, function ($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return !empty($data) ? $data : [];
}


function search($query, $method, $data)
{
    $result = [];
    if ($method === 'fuzzy') {
        $result = fuzzySearch($query, $data);
        $result = array_slice($result, 0, 10);
    } else if ($method === 'main') {
        $result = main_search($query, $data);
    }
    return $result;
}

function format_title($e)
{
    $t = $e['alt_name'];
    $t = preg_replace("/[^a-zA-Z0-9\s-]/", "", $t);
    $t = preg_replace("/[\s-]+/", "-", $t);
    $t = trim($t, "-");
    if ($e['language'] !== 'Japanese') {
        if ($e['language'] !== 'English') {
            $t .= '-' . $e['language'];
        }
        $t .= '-dub';
    }
    $t .= '-' . $e['movie_id'];
    return strtolower($t);
}

function splitWords($str, $chars)
{
    $pattern = '/[' . preg_quote(implode('', $chars), '/') . ']+/';
    return preg_split($pattern, $str, -1, PREG_SPLIT_NO_EMPTY);
}

function format_tag($t)
{
    return strtolower(str_replace(" ", "-", trim($t)));
}

function filter_duplicates($d)
{
    $return = [];
    foreach ($d as $element) {
        $is_duplicate = false;
        foreach ($return as $existing_item) {
            if ($element['name'] === $existing_item['name']) {
                $is_duplicate = true;
                break;
            }
        }
        if (!$is_duplicate) {
            $return[] = $element;
        }
    }
    return $return;
}

function find_movie($id)
{
    global $main_data;
    for ($i = count($main_data) - 1; $i >= 0; $i--) {
        if ($main_data[$i]['movie_id'] === $id) {
            return $main_data[$i];
        }
    }
    return false;
}
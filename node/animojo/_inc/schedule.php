<?php
function get_schedule($tz, $dates = [])
{
    $cacheFile = $_SERVER['DOCUMENT_ROOT'] . '/cache/database/schedule';
    $cacheLifetime = 60 * 60 * 24; // 1 day

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
        $cachedData = include $cacheFile;
        if ($cachedData)
            return process_schedule_for_timezone($cachedData, $tz, $dates);
    }

    try {
        // Create a DateTimeZone object for UTC (AniList's default)
        $timezone = new DateTimeZone('UTC');
        $dateTime = new DateTime('now', $timezone);

        $currentMonth = (int) $dateTime->format('m');
        $currentYear = (int) $dateTime->format('Y');

        $firstDayOfMonth = new DateTimeImmutable("$currentYear-$currentMonth-01 00:00:00", $timezone);
        $lastDayOfMonth = new DateTimeImmutable("$currentYear-$currentMonth-" . $firstDayOfMonth->format('t') . " 23:59:59", $timezone);

        $monthStartUnix = $firstDayOfMonth->getTimestamp();
        $monthEndUnix = $lastDayOfMonth->getTimestamp();
    } catch (Exception $e) {
        die(json_encode(["status" => false, "msg" => "Error initializing date objects"]));
    }

    $graphqlQuery = '
    query ($page: Int, $perPage: Int, $airingAt_greater: Int, $airingAt_lesser: Int) {
        Page (page: $page, perPage: $perPage) {
            pageInfo {
                total
                currentPage
                lastPage
                hasNextPage
                perPage
            }
            airingSchedules (airingAt_greater: $airingAt_greater, airingAt_lesser: $airingAt_lesser, sort: [TIME]) {
                airingAt
                episode
                media {
                    idMal
                    title {
                        english
                        native # Japanese title
                        romaji
                    }
                }
            }
        }
    }';

    $page = 1;
    $hasNextPage = true;
    $tempAnimeSchedule = [];

    while ($hasNextPage) {
        $variables = [
            'page' => $page,
            'perPage' => 50,
            'airingAt_greater' => $monthStartUnix,
            'airingAt_lesser' => $monthEndUnix,
        ];

        $requestBody = json_encode([
            'query' => $graphqlQuery,
            'variables' => $variables,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graphql.anilist.co');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json',]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false)
            die(json_encode(["status" => false, "msg" => "request timed out"]));
        if ($httpCode !== 200)
            die(json_encode(["status" => false, "msg" => "HTTP error: $httpCode"]));

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE)
            die(["status" => false, "msg" => "API decode error"]);

        if (isset($data['errors']))
            die(json_encode(["status" => false, "msg" => "API returned errors"]));

        if (!isset($data['data']['Page']['airingSchedules']) || !is_array($data['data']['Page']['airingSchedules'])) {
            $hasNextPage = false;
            continue;
        }

        foreach ($data['data']['Page']['airingSchedules'] as $schedule) {
            try {
                // Store UTC timestamp directly in cache
                $tempAnimeSchedule[] = [
                    "mal_id" => $schedule['media']['idMal'],
                    "airingAt" => $schedule['airingAt'],
                    "episode" => $schedule['episode'] ?? null,
                    "name" => $schedule['media']['title']['english'] ?? $schedule['media']['title']['romaji'] ?? null,
                    "alt_name" => $schedule['media']['title']['native'] ?? null,
                ];
            } catch (Exception $e) {
                continue;
            }
        }

        $pageInfo = $data['data']['Page']['pageInfo'] ?? null;
        if ($pageInfo && $pageInfo['hasNextPage']) {
            $page++;
        } else {
            $hasNextPage = false;
        }
    }

    // save data to cache
    $tmp = tempnam(dirname($cacheFile), 'sch__');
    file_put_contents($tmp, '<?php return ' . var_export($tempAnimeSchedule, true) . ';');
    rename($tmp, $cacheFile);

    // process fetched data for the requested timezone
    return process_schedule_for_timezone($tempAnimeSchedule, $tz, $dates);
}

function process_schedule_for_timezone($scheduleData, $targetTimezone, $datesFilter)
{
    $animeSchedule = [];
    $targetTZ = new DateTimeZone($targetTimezone);

    // Get current month and year in the target timezone for filtering
    $currentDateTimeTargetTZ = new DateTime('now', $targetTZ);
    $currentMonthTargetTZ = (int) $currentDateTimeTargetTZ->format('m');
    $currentYearTargetTZ = (int) $currentDateTimeTargetTZ->format('Y');


    foreach ($scheduleData as $schedule) {
        try {
            // Convert UTC timestamp to the target timezone
            $airingDateTime = (new DateTimeImmutable('@' . $schedule['airingAt']))->setTimezone($targetTZ);
        } catch (Exception $e) {
            continue;
        }

        // Filter based on the target timezone's month and year
        if ((int) $airingDateTime->format('m') === $currentMonthTargetTZ && (int) $airingDateTime->format('Y') === $currentYearTargetTZ) {
            $dayOfMonth = (int) $airingDateTime->format('d');

            $animeData = [
                "mal_id" => $schedule['mal_id'],
                "name" => $schedule['name'],
                "alt_name" => $schedule['alt_name'],
                "episode" => $schedule['episode'],
                "time" => $airingDateTime->format('H:i')
            ];

            if (!isset($animeSchedule[$dayOfMonth]))
                $animeSchedule[$dayOfMonth] = [];
            $animeSchedule[$dayOfMonth][] = $animeData;
        }
    }

    $formattedSchedule = [];
    foreach ($animeSchedule as $day => $animeList) {
        if (empty($datesFilter) || in_array($day, $datesFilter)) {
            $formattedSchedule[] = [
                "day" => $day,
                "data" => $animeList
            ];
        }
    }

    usort($formattedSchedule, function ($a, $b) {
        return $a['day'] <=> $b['day'];
    });

    return $formattedSchedule;
}
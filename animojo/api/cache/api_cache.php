<?php
class ApiCache
{
    private $cacheDir;
    private $cacheExpire;

    public function __construct($cacheDir = null, $cacheExpire = 3600)
    {
        // Set cache directory (defaults to subdirectory of current file)
        $this->cacheDir = $cacheDir ?: $_SERVER['DOCUMENT_ROOT'] . '/cache/api/';
        $this->cacheExpire = $cacheExpire;

        // Create cache directory if it doesn't exist
        if (!file_exists($this->cacheDir))
            mkdir($this->cacheDir, 0755, true);
    }

    public function getCacheKey()
    {
        // Create a unique key based on the request
        $requestData = [
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'get' => $_GET,
            'post' => $_POST,
            'input' => file_get_contents('php://input')
        ];
        return md5(serialize($requestData));
    }

    public function get($key)
    {
        $cacheFile = $this->cacheDir . $key;

        if (file_exists($cacheFile)) {
            if ((time() - filemtime($cacheFile)) < $this->cacheExpire)
                return file_get_contents($cacheFile);
            unlink($cacheFile);
        }
        return false;
    }

    public function set($key, $data)
    {
        $cacheFile = $this->cacheDir . $key;
        $tmp = tempnam(dirname($cacheFile), 'tmp_');
        file_put_contents($tmp, $data, LOCK_EX);
        @unlink($cacheFile);
        rename($tmp, $cacheFile);
    }

    public function clear($key = null)
    {
        if ($key) {
            // Clear specific cache item
            $cacheFile = $this->cacheDir . $key;
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        } else {
            // Clear all cache
            $files = glob($this->cacheDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function getCacheInfo()
    {
        $files = glob($this->cacheDir . '*');
        $info = [
            'total_files' => count($files),
            'total_size' => 0,
            'files' => []
        ];

        foreach ($files as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                $info['total_size'] += $size;
                $info['files'][] = [
                    'name' => basename($file),
                    'size' => $size,
                    'last_modified' => filemtime($file),
                    'expires_in' => ($this->cacheExpire - (time() - filemtime($file)))
                ];
            }
        }

        return $info;
    }
}

// Helper function for quick implementation
function handleApiCaching($cacheExpire = 3600)
{
    $cache = new ApiCache(null, $cacheExpire);
    $cacheKey = $cache->getCacheKey();

    // Try to get cached response
    $cachedResponse = $cache->get($cacheKey);

    if ($cachedResponse !== false) {
        header('Content-Type: application/json');
        header('X-API-Cache: HIT');
        echo $cachedResponse;
        exit;
    }

    // Set header for cache miss
    header('X-API-Cache: MISS');

    // Return the cache object for later saving
    return $cache;
}
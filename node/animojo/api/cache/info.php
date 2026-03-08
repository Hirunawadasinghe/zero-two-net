<?php
require_once 'api_cache.php';
include $_SERVER['DOCUMENT_ROOT'] . '/_config.php';

$cache = new ApiCache();
$cacheInfo = $cache->getCacheInfo();
$currentTime = time();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Cache Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .btn {
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
            border-radius: 3px;
        }

        .btn-danger {
            background: #f44336;
            color: white;
            border: none;
        }

        .btn-primary {
            background: #2196F3;
            color: white;
            border: none;
        }

        .expired {
            color: #f44336;
            font-weight: bold;
        }

        .active {
            color: #4CAF50;
        }

        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>API Cache Dashboard</h1>

        <div class="stats">
            <h3>Cache Statistics</h3>
            <p>Total Cached Items: <?= $cacheInfo['total_files'] ?></p>
            <p>Total Cache Size: <?= formatBytes($cacheInfo['total_size']) ?></p>
            <div>
                <button onclick="clearAllCache()" class="btn btn-danger">Clear All Cache</button>
                <button onclick="refreshPage()" class="btn btn-primary">Refresh</button>
            </div>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search cache keys..." onkeyup="searchCache()">
        </div>

        <table id="cacheTable">
            <thead>
                <tr>
                    <th>Cache Key</th>
                    <th>Size</th>
                    <th>Last Modified</th>
                    <th>Expires In</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cacheInfo['files'] as $file): ?>
                    <tr>
                        <td><?= htmlspecialchars($file['name']) ?></td>
                        <td><?= formatBytes($file['size']) ?></td>
                        <td><?= date('Y-m-d H:i:s', $file['last_modified']) ?></td>
                        <td><?= formatSeconds($file['expires_in']) ?></td>
                        <td class="<?= $file['expires_in'] <= 0 ? 'expired' : 'active' ?>">
                            <?= $file['expires_in'] <= 0 ? 'EXPIRED' : 'ACTIVE' ?>
                        </td>
                        <td>
                            <button onclick="viewCache('<?= $file['name'] ?>')" class="btn btn-primary">View</button>
                            <button onclick="deleteCache('<?= $file['name'] ?>')" class="btn btn-danger">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        <?php
        echo 'const author_key = "' . $cache_author_key . '";';
        ?>

        function refreshPage() {
            location.reload();
        }

        function clearAllCache() {
            if (confirm('Are you sure you want to clear ALL cache?')) {
                fetch(`clear_cache.php?auth=${author_key}&all=1`)
                    .then(response => response.json())
                    .then(d => {
                        if (d.success) {
                            refreshPage();
                        } else {
                            console.log(d);
                        }
                    });
            }
        }

        function deleteCache(key) {
            if (confirm('Delete this cache item?')) {
                fetch(`clear_cache.php?auth=${author_key}&key=${encodeURIComponent(key)}`)
                    .then(response => response.json())
                    .then(d => {
                        if (d.success) {
                            refreshPage();
                        } else {
                            console.log(d);
                        }
                    });
            }
        }

        function viewCache(key) {
            window.open(`view_cache.php?auth=${author_key}&key=${encodeURIComponent(key)}`, '_blank');
        }

        function searchCache() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('cacheTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td')[0];
                if (td) {
                    const txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }
    </script>
</body>

</html>

<?php
// Helper functions
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function formatSeconds($seconds)
{
    if ($seconds <= 0)
        return 'Expired';
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    return sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);
}
?>
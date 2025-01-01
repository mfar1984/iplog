<?php
// Set the log file path
$logFile = 'ip_log.txt';

// Read the contents of the log file
$logEntries = [];
if (file_exists($logFile)) {
    $logEntries = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// Pagination settings
$entriesPerPageOptions = [10, 50, 100, 1000]; // Options for entries per page
$entriesPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10; // Default to 10
$totalEntries = count($logEntries);
$totalPages = ceil($totalEntries / $entriesPerPage);

// Get the current page from the URL, default to 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); // Ensure currentPage is within valid range

// Calculate the starting index for the current page
$startIndex = ($currentPage - 1) * $entriesPerPage;

// Prepare data for the table
$data = [];
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Filter log entries based on the search term
foreach ($logEntries as $entry) {
    // Check if the search term is present in the log entry
    if ($searchTerm === '' || stripos($entry, $searchTerm) !== false) {
        preg_match('/\[(.*?)\] IP: (.*)/', $entry, $matches);
        if (count($matches) === 3) {
            $dateTime = explode(' ', $matches[1]); // Split date and time
            $data[] = [
                'date' => $dateTime[0],
                'time' => $dateTime[1],
                'ip' => trim($matches[2]) // Trim any whitespace
            ];
        }
    }
}

// Apply pagination logic to the filtered data
$totalEntries = count($data);
$totalPages = ceil($totalEntries / $entriesPerPage);
$startIndex = ($currentPage - 1) * $entriesPerPage;
$data = array_slice($data, $startIndex, $entriesPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Log</title>
    <link rel="stylesheet" href="styles.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
    }

    h1 {
        text-align: center;
        color: #333;
    }

    table {
        width: 80%;
        margin: 0 auto;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    th, td {
        padding: 12px;
        text-align: center;
        border: 1px solid #ddd;
    }

    th {
        background-color: #4CAF50;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #ddd;
    }

    .pagination {
        text-align: right;
        margin-top: 20px;
    }

    .pagination a {
        margin: 0 5px;
        padding: 8px 12px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 4px;
    }

    .pagination a:hover {
        background-color: #45a049;
    }

    .flex-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 80%;
        margin: 20px auto;
    }

    .total-count {
        font-weight: bold;
    }

    .search-container {
        display: flex;
        align-items: center;
    }

    .search-container input {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-left: 10px;
        width: 200px;
    }

    .entries-dropdown {
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-right: 10px;
    }

    /* CSS untuk pautan IP */
    table td a {
        text-decoration: none; /* Tiada garis bawah secara default */
        color: inherit; /* Warna pautan sama dengan teks */
    }

    table td a:hover {
        text-decoration: underline; /* Garis bawah apabila mouse ditujukan ke pautan */
        color: #4CAF50; /* Warna pautan apabila mouse ditujukan */
    }

    @media (max-width: 600px) {
        table {
            width: 100%;
        }

        .flex-container {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-container {
            margin-top: 10px;
        }
    }
</style>
    <script>
        function liveSearch() {
            const input = document.getElementById('searchInput').value;
            window.location.href = `?entries=<?php echo $entriesPerPage; ?>&search=${input}&page=1`; // Reset to the first page
        }

        function updateEntriesPerPage() {
            const entriesPerPage = document.getElementById('entriesPerPage').value;
            window.location.href = `?entries=${entriesPerPage}&search=<?php echo htmlspecialchars($searchTerm); ?>&page=1`; // Reset to the first page
        }
    </script>
</head>
<body>

<h1>IP Log</h1>

<div class="flex-container">
    <div>
        <select id="entriesPerPage" class="entries-dropdown" onchange="updateEntriesPerPage()">
            <?php foreach ($entriesPerPageOptions as $option): ?>
                <option value="<?php echo $option; ?>" <?php if ($option === $entriesPerPage) echo 'selected'; ?>>
                    Show <?php echo $option; ?> entries
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="search-container">
        <input type="text" id="searchInput" onkeyup="liveSearch()" placeholder="Search IP..." value="<?php echo htmlspecialchars($searchTerm); ?>" />
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Date</th>
            <th>Time</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data)): ?>
            <tr>
                <td colspan="4">No entries found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($data as $index => $entry): ?>
                <tr>
                    <td><?php echo $startIndex + $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($entry['date']); ?></td>
                    <td><?php echo htmlspecialchars($entry['time']); ?></td>
                    <td>
                        <a href="https://www.iplocation.net/ip-lookup/<?php echo htmlspecialchars($entry['ip']); ?>" target="_blank">
                            <?php echo htmlspecialchars($entry['ip']); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="flex-container">
    <div class="total-count">Total IP Count: <?php echo $totalEntries; ?></div>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?entries=<?php echo $entriesPerPage; ?>&search=<?php echo htmlspecialchars($searchTerm); ?>&page=<?php echo $currentPage - 1; ?>">&laquo; Previous</a>
        <?php endif; ?>

        <?php 
        $range = 2; // Number of pages to show before and after the current page
        for ($i = 1; $i <= $totalPages; $i++):
            if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)):
        ?>
                <a href="?entries=<?php echo $entriesPerPage; ?>&search=<?php echo htmlspecialchars($searchTerm); ?>&page=<?php echo $i; ?>" <?php if ($i === $currentPage) echo 'style="font-weight: bold;"'; ?>>
                    <?php echo $i; ?>
                </a>
        <?php 
            elseif ($i == $currentPage - $range - 1 || $i == $currentPage + $range + 1):
                echo '<span>...</span>';
            endif;
        endfor; 
        ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="?entries=<?php echo $entriesPerPage; ?>&search=<?php echo htmlspecialchars($searchTerm); ?>&page=<?php echo $currentPage + 1; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

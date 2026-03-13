<?php
declare(strict_types=1);

/**
 * Mock DataGrid remote data endpoint.
 * Returns paginated, sortable JSON data for the DataGrid remote demo.
 */

$page     = isset($_GET['page'])     ? max(1, (int)$_GET['page'])      : (isset($_POST['page'])     ? max(1, (int)$_POST['page'])      : 1);
$pageSize = isset($_GET['pageSize']) ? max(1, (int)$_GET['pageSize'])  : (isset($_POST['pageSize']) ? max(1, (int)$_POST['pageSize'])  : 10);
$sortField = isset($_GET['sortField']) ? (string)$_GET['sortField'] : (isset($_POST['sortField']) ? (string)$_POST['sortField'] : '');
$sortDir   = isset($_GET['sortDir'])   ? (string)$_GET['sortDir']   : (isset($_POST['sortDir'])   ? (string)$_POST['sortDir']   : 'asc');

// Generate 100 rows of mock data
$statuses   = ['Pending', 'In Progress', 'Completed', 'On Hold', 'Cancelled'];
$priorities = ['Low', 'Medium', 'High', 'Critical'];
$owners     = ['Alice', 'Bob', 'Carol', 'Dave', 'Eve', 'Frank', 'Grace', 'Heidi'];
$tasks      = [
    'Refactor auth module', 'Write unit tests', 'Deploy to staging',
    'Update docs', 'Code review', 'Performance audit',
    'Security scan', 'Database migration', 'UI polish',
    'API integration', 'Bug triage', 'Sprint planning',
    'Release notes', 'Monitoring setup', 'Load testing',
];

$allData = [];
for ($i = 1; $i <= 100; $i++) {
    $allData[] = [
        'id'       => $i,
        'task'     => 'Task ' . $i . ': ' . $tasks[($i - 1) % count($tasks)],
        'owner'    => $owners[($i - 1) % count($owners)],
        'priority' => $priorities[($i - 1) % count($priorities)],
        'status'   => $statuses[($i - 1) % count($statuses)],
        'due_date' => date('Y-m-d', strtotime('+' . ($i * 2) . ' days')),
        'progress' => ($i * 7) % 101,
    ];
}

// Sort
$allowed = ['id', 'task', 'owner', 'priority', 'status', 'due_date', 'progress'];
if ($sortField !== '' && in_array($sortField, $allowed, true)) {
    usort($allData, static function (array $a, array $b) use ($sortField, $sortDir): int {
        $cmp = $a[$sortField] <=> $b[$sortField];
        return $sortDir === 'desc' ? -$cmp : $cmp;
    });
}

$total  = count($allData);
$offset = ($page - 1) * $pageSize;
$slice  = array_slice($allData, $offset, $pageSize);

echo json_encode(['data' => $slice, 'total' => $total]);

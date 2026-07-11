<?php
session_start();

if (!isset($_SESSION['is_admin'])) {
    die("دسترسی غیرمجاز");
}

$dataFile = __DIR__ . '/dashboard_data.json';

$managementNotice = $_POST['management_notice'] ?? '';
$activities = $_POST['activities'] ?? [];

$cleanActivities = [];

foreach ($activities as $activity) {
    $cleanActivities[] = [
        'icon'  => trim($activity['icon'] ?? ''),
        'title' => trim($activity['title'] ?? ''),
        'desc'  => trim($activity['desc'] ?? ''),
        'time'  => trim($activity['time'] ?? '')
    ];
}

$data = [
    'management_notice' => trim($managementNotice),
    'activities' => $cleanActivities
];

file_put_contents($dataFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

header("Location: admin.php?saved=1");
exit;

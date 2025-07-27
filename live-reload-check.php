<?php
// File change detection for live reload
header('Content-Type: application/json');

$files_to_watch = [
    'resources/views/layouts/admin.blade.php',
    'resources/views/admin/dashboard.blade.php',
    'public/css/responsive-admin.css',
    'app/Http/Controllers/Admin/DashboardController.php',
    'routes/web.php'
];

$latest_modified = 0;

foreach ($files_to_watch as $file) {
    if (file_exists($file)) {
        $modified = filemtime($file);
        if ($modified > $latest_modified) {
            $latest_modified = $modified;
        }
    }
}

echo json_encode([
    'modified' => $latest_modified,
    'timestamp' => date('Y-m-d H:i:s', $latest_modified),
    'status' => 'monitoring'
]);
?>
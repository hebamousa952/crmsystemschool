<?php
// Simple Live Reload Script for Laravel
?>
<!DOCTYPE html>
<html>
<head>
    <title>Live Reload Monitor</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .reload-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .reload-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔥 Laravel Live Reload Monitor</h1>
    
    <div class="status success">
        ✅ PHP Server: Running on http://localhost:8000
    </div>
    
    <div class="status info">
        📡 Monitoring files for changes...
    </div>
    
    <button class="reload-btn" onclick="location.reload()">🔄 Manual Reload</button>
    <button class="reload-btn" onclick="window.open('http://localhost:8000/admin', '_blank')">🚀 Open Admin Panel</button>
    
    <script>
        // Auto reload every 2 seconds to check for changes
        let lastModified = <?php echo filemtime(__DIR__ . '/resources/views/layouts/admin.blade.php'); ?>;
        
        setInterval(function() {
            fetch('live-reload-check.php')
                .then(response => response.json())
                .then(data => {
                    if (data.modified > lastModified) {
                        console.log('🔥 Files changed! Reloading...');
                        location.reload();
                    }
                })
                .catch(error => console.log('Monitoring...'));
        }, 1000);
        
        console.log('🔥 Live Reload Active!');
        console.log('📝 Edit any Blade file and see changes instantly!');
    </script>
</body>
</html>
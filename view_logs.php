<?php
require_once 'logger.php';

try {
    $logger = new Logger();
    
    if (!$logger->isDatabaseHealthy()) {
        throw new Exception("Database connection is not healthy");
    }
    
    $logs = $logger->getLogs(50); 
    $stats = $logger->getLogStats();

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Request Logs</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 1200px; 
                margin: 0 auto; 
                padding: 20px; 
                background-color: #f5f5f5;
            }
            .container {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 20px 0; 
            }
            th, td { 
                border: 1px solid #ddd; 
                padding: 12px; 
                text-align: left; 
                vertical-align: top;
            }
            th { 
                background-color: #2196F3; 
                color: white;
                font-weight: bold;
            }
            tr:nth-child(even) { 
                background-color: #f9f9f9; 
            }
            tr:hover {
                background-color: #e3f2fd;
            }
            .back-link { 
                display: inline-block; 
                margin: 10px 10px 10px 0; 
                padding: 10px 20px; 
                background: #4CAF50; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px; 
            }
            .back-link:hover { 
                background: #45a049; 
            }
            .clear-link {
                background: #f44336;
            }
            .clear-link:hover {
                background: #da190b;
            }
            h1 { 
                color: #333; 
                border-bottom: 3px solid #2196F3;
                padding-bottom: 10px;
            }
            .parameters {
                max-width: 250px;
                word-wrap: break-word;
                font-size: 0.9em;
            }
            .query-type {
                font-weight: bold;
                color: #1976D2;
            }
            .stats {
                background: #e3f2fd;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .db-info {
                background: #f3e5f5;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
                font-size: 0.9em;
            }
            .no-logs {
                text-align: center;
                color: #666;
                font-style: italic;
                padding: 40px;
            }
            .user-info {
                font-size: 0.8em;
                color: #666;
                max-width: 200px;
                word-wrap: break-word;
            }
        </style>
    </head>
    <body>";

    echo "<div class='container'>";
    echo "<h1>Request Logs (SQLite Database)</h1>";

    echo "<div class='db-info'>";
    echo "<strong>📁 Database Information:</strong><br>";
    echo "Location: " . htmlspecialchars($logger->getDatabasePath()) . "<br>";
    echo "Status: " . ($logger->isDatabaseHealthy() ? "✅ Healthy" : "❌ Unhealthy") . "<br>";
    echo "File exists: " . (file_exists($logger->getDatabasePath()) ? "✅ Yes" : "❌ No");
    echo "</div>";

    if (count($logs) > 0) {
        echo "<div class='stats'>";
        echo "<h3>📊 Statistics:</h3>";
        echo "<p><strong>Total requests logged:</strong> " . $stats['total'] . "</p>";
        if (!empty($stats['by_type'])) {
            echo "<p><strong>Query types:</strong></p>";
            echo "<ul>";
            foreach ($stats['by_type'] as $stat) {
                echo "<li>" . htmlspecialchars($stat['query_type']) . ": " . $stat['count'] . " requests</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
        
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Query Type</th>
                <th>Parameters</th>
                <th>User Info</th>
                <th>Timestamp</th>
              </tr>";
        
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($log['id']) . "</td>";
            echo "<td class='query-type'>" . htmlspecialchars($log['query_type']) . "</td>";

            $parameters = json_decode($log['parameters'], true);
            $paramStr = '';
            if ($parameters && is_array($parameters)) {
                $paramArray = [];
                foreach ($parameters as $key => $value) {
                    $paramArray[] = htmlspecialchars($key) . ': ' . htmlspecialchars($value);
                }
                $paramStr = implode('<br>', $paramArray);
            } else {
                $paramStr = htmlspecialchars($log['parameters']);
            }
            
            echo "<td class='parameters'>" . $paramStr . "</td>";

            $userInfo = '';
            if (isset($log['user_ip'])) {
                $userInfo .= "IP: " . htmlspecialchars($log['user_ip']);
            }
            if (isset($log['user_agent']) && $log['user_agent'] !== 'unknown') {
                if ($userInfo) $userInfo .= "<br>";
                $userInfo .= "UA: " . htmlspecialchars(substr($log['user_agent'], 0, 50)) . (strlen($log['user_agent']) > 50 ? '...' : '');
            }
            echo "<td class='user-info'>" . ($userInfo ?: 'N/A') . "</td>";
            
            echo "<td>" . htmlspecialchars($log['timestamp']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='no-logs'>No logs found. Start making requests to see them here!</div>";
    }

    echo "<a href='index.html' class='back-link'>Go back to main page</a>";
    echo "<a href='clear_logs.php' class='back-link clear-link' onclick='return confirm(\"Are you sure you want to clear all logs?\")'>Clear All Logs</a>";

    echo "</div>";
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Logs Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #f5c6cb; }
            .back-link { display: inline-block; margin: 10px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h1>Logging System Error</h1>
        <div class='error'>
            <h3>❌ Cannot access logs</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p><small>The SQLite database will be automatically created when the first request is logged.</small></p>
        </div>
        <a href='index.html' class='back-link'>Go back to main page</a>
    </body>
    </html>";
}
?>
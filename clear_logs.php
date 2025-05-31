<?php
require_once 'logger.php';

try {
    $logger = new Logger();

    if (!$logger->isDatabaseHealthy()) {
        throw new Exception("Database connection is not healthy");
    }

    $success = $logger->clearLogs();
    
    if ($success) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Logs Cleared</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px; 
                    text-align: center;
                }
                .success {
                    background: #d4edda;
                    color: #155724;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border: 1px solid #c3e6cb;
                }
                .info {
                    background: #e3f2fd;
                    color: #1565c0;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border: 1px solid #bbdefb;
                    font-size: 0.9em;
                }
                .back-link { 
                    display: inline-block; 
                    margin: 10px; 
                    padding: 10px 20px; 
                    background: #4CAF50; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 4px; 
                }
                .back-link:hover { 
                    background: #45a049; 
                }
                .logs-link {
                    background: #2196F3;
                }
                .logs-link:hover {
                    background: #1976D2;
                }
            </style>
        </head>
        <body>
            <h1>Logs Management</h1>
            <div class='success'>
                <h3>✅ All logs have been successfully cleared!</h3>
                <p>The request logs database has been emptied and reset.</p>
            </div>
            
            <div class='info'>
                <strong>Database Info:</strong><br>
                Location: " . htmlspecialchars($logger->getDatabasePath()) . "<br>
                Status: Healthy and operational
            </div>
            
            <a href='index.html' class='back-link'>Go back to main page</a>
            <a href='view_logs.php' class='back-link logs-link'>View Logs</a>
        </body>
        </html>";
    } else {
        throw new Exception("Failed to clear logs - check error logs for details");
    }
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Error</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                max-width: 600px; 
                margin: 0 auto; 
                padding: 20px; 
                text-align: center; 
            }
            .error { 
                background: #f8d7da; 
                color: #721c24; 
                padding: 20px; 
                border-radius: 8px; 
                margin: 20px 0; 
                border: 1px solid #f5c6cb;
            }
            .back-link { 
                display: inline-block; 
                margin: 10px; 
                padding: 10px 20px; 
                background: #4CAF50; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px; 
            }
            .back-link:hover { 
                background: #45a049; 
            }
        </style>
    </head>
    <body>
        <h1>Error</h1>
        <div class='error'>
            <h3>❌ Error clearing logs</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p><small>If this problem persists, the database file may need to be recreated.</small></p>
        </div>
        <a href='view_logs.php' class='back-link'>Back to logs</a>
        <a href='index.html' class='back-link'>Main page</a>
    </body>
    </html>";
}
?>
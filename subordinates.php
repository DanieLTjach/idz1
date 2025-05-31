<?php
require_once 'logger.php';

$host = 'localhost';
$dbname = 'lb_pdo_workers';
$username = 'test';
$password = '0000';
$chief = $_POST['chief_name'];

$logger = new Logger();
$logger->logRequest('subordinates_count', ['chief_name' => $chief]);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT COUNT(w.ID_Worker) AS subordinates 
                           FROM WORKER w 
                           JOIN DEPARTMENT d ON w.FID_Department = d.ID_Department 
                           WHERE d.chief = :chief");

    $stmt->bindParam(':chief', $chief, PDO::PARAM_STR);
    
    $stmt->execute();

    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Subordinates Result</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
            .result { background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
            .back-link:hover { background: #45a049; }
        </style>
    </head>
    <body>";

    if ($res) {
        echo "<div class='result'><h3>Result for chief: " . htmlspecialchars($chief) . "</h3>";
        echo "<p><strong>Number of subordinates: " . $res['subordinates'] . "</strong></p></div>";
    } else {
        echo "<div class='result'><p>No subordinates found for chief: " . htmlspecialchars($chief) . "</p></div>";
    }

    echo "<a href='index.html' class='back-link'>Go back</a>";
    echo "<a href='view_logs.php' class='back-link' style='background: #2196F3; margin-left: 10px;'>View Logs</a>";
    echo "</body></html>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
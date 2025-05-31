<?php
require_once 'logger.php';

$servername = "localhost"; 
$username = "test";       
$password = "0000";            
$dbname = "lb_pdo_workers";
$db_driver = 'mysql';
$dsn = "$db_driver:host=$servername;dbname=$dbname";

$options = array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

$project_name = $_POST['project_name'];

$logger = new Logger();
$logger->logRequest('project_time', ['project_name' => $project_name]);

try {
    $dbh = new PDO($dsn, $username, $password, $options);

    $stmt = $dbh->prepare("SELECT DATEDIFF(w.time_end, w.time_start) AS time_difference_days,
                           TIME(w.time_start) AS start_time,
                           TIME(w.time_end) AS end_time,
                           DATE(w.time_start) AS work_date
                            FROM PROJECT p
                            LEFT JOIN WORK w ON w.FID_PROJECTS = p.ID_PROJECTS
                            WHERE p.name = :project_name AND w.time_start IS NOT NULL AND w.time_end IS NOT NULL;");
    
    $stmt->bindParam(':project_name', $project_name, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Project Time Result</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
            .back-link:hover { background: #45a049; }
            h3 { color: #333; }
        </style>
    </head>
    <body>";
    
    echo "<h3>Time spent on project: " . htmlspecialchars($project_name) . "</h3>";
    
    if ($result && count($result) > 0) {
        echo "<table>";
        echo "<tr><th>Work Date</th><th>Start Time</th><th>End Time</th><th>Days Duration</th></tr>";
        
        $totalDays = 0;
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['work_date'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['start_time'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['end_time'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['time_difference_days'] ?? 'N/A') . "</td>";
            echo "</tr>";
            
            if (is_numeric($row['time_difference_days'])) {
                $totalDays += $row['time_difference_days'];
            }
        }
        echo "</table>";
        echo "<p><strong>Total days spent on project: " . $totalDays . "</strong></p>";
    } else {
        echo "<p>No time records found for project: " . htmlspecialchars($project_name) . "</p>";
    }
    
    echo "<a href='index.html' class='back-link'>Go back</a>";
    echo "<a href='view_logs.php' class='back-link' style='background: #2196F3; margin-left: 10px;'>View Logs</a>";
    echo "</body></html>";
    
} catch (PDOException $e) {
    echo "Error!: " . $e->getMessage() . "<br/>"; 
    die();
}
?>
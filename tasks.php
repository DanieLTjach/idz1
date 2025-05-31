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
$date = $_POST['task_date'];

$logger = new Logger();
$logger->logRequest('project_tasks_by_date', [
    'project_name' => $project_name,
    'task_date' => $date
]);

try {
    $dbh = new PDO($dsn, $username, $password, $options);
    
    $stmt = $dbh->prepare("SELECT w.FID_Worker, w.FID_Projects, w.time_start, w.time_end, w.description, w.date
                            FROM WORK w
                            JOIN PROJECT p ON w.FID_Projects = p.ID_Projects
                            WHERE p.name = :project_name AND DATE(w.date) = :date;");
    
    $stmt->bindParam(':project_name', $project_name, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Tasks Result</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #f2f2f2; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
            .back-link:hover { background: #45a049; }
            h3 { color: #333; }
            .description { max-width: 200px; word-wrap: break-word; }
        </style>
    </head>
    <body>";
    
    echo "<h3>Tasks for project '" . htmlspecialchars($project_name) . "' on " . htmlspecialchars($date) . "</h3>";
    
    if ($result && count($result) > 0) {
        echo "<table>";
        echo "<tr><th>Worker ID</th><th>Project ID</th><th>Date</th><th>Start Time</th><th>End Time</th><th>Description</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['FID_Worker']) . "</td>";
            echo "<td>" . htmlspecialchars($row['FID_Projects']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['time_start']) . "</td>";
            echo "<td>" . htmlspecialchars($row['time_end']) . "</td>";
            echo "<td class='description'>" . htmlspecialchars($row['description']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Total tasks found: " . count($result) . "</strong></p>";
    } else {
        echo "<p>No tasks found for project '" . htmlspecialchars($project_name) . "' on " . htmlspecialchars($date) . "</p>";
    }
    
    echo "<a href='index.html' class='back-link'>Go back</a>";
    echo "<a href='view_logs.php' class='back-link' style='background: #2196F3; margin-left: 10px;'>View Logs</a>";
    echo "</body></html>";
    
} catch (PDOException $e) {
    echo "Error!: " . $e->getMessage() . "<br/>"; 
    die();
}
?>
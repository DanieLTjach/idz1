<?php
class Logger {
    private $logDb;
    private $dbPath;
    
    public function __construct($dbPath = 'logs.db') {
        $this->dbPath = $dbPath;
        $this->initializeDatabase();
    }
    
    private function initializeDatabase() {
        try {
            $dir = dirname($this->dbPath);
            if ($dir !== '.' && !is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception("Cannot create directory: " . $dir);
                }
            }
            
            $dbExists = file_exists($this->dbPath);

            $this->logDb = new PDO('sqlite:' . $this->dbPath);
            $this->logDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->logDb->exec('PRAGMA foreign_keys = ON');

            $this->createLogTable();
    
            if (!$dbExists) {
                error_log("SQLite database created at: " . realpath($this->dbPath));
            }
            
        } catch (PDOException $e) {
            error_log("PDO Logger initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize logging database: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Logger initialization error: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function createLogTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS request_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                query_type TEXT NOT NULL,
                parameters TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                user_ip TEXT,
                user_agent TEXT
            )";
            
            $this->logDb->exec($sql);

            $this->logDb->exec("CREATE INDEX IF NOT EXISTS idx_timestamp ON request_logs(timestamp)");
            
            $this->logDb->exec("CREATE INDEX IF NOT EXISTS idx_query_type ON request_logs(query_type)");
            
        } catch (PDOException $e) {
            error_log("Error creating log table: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function logRequest($queryType, $parameters = []) {
        try {
            if (!$this->logDb) {
                throw new Exception("Database connection not available");
            }
            
            $stmt = $this->logDb->prepare("INSERT INTO request_logs (query_type, parameters, user_ip, user_agent) VALUES (?, ?, ?, ?)");
            
            $parametersJson = json_encode($parameters, JSON_UNESCAPED_UNICODE);
            $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->execute([$queryType, $parametersJson, $userIp, $userAgent]);
            
            return $this->logDb->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Logging error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Logging error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLogs($limit = 100) {
        try {
            if (!$this->logDb) {
                throw new Exception("Database connection not available");
            }
            
            $stmt = $this->logDb->prepare("SELECT * FROM request_logs ORDER BY timestamp DESC LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get logs error: " . $e->getMessage());
            return [];
        } catch (Exception $e) {
            error_log("Get logs error: " . $e->getMessage());
            return [];
        }
    }
    
    public function clearLogs() {
        try {
            if (!$this->logDb) {
                throw new Exception("Database connection not available");
            }
            
            $this->logDb->beginTransaction();

            $this->logDb->exec("DELETE FROM request_logs");
            
            $this->logDb->exec("DELETE FROM sqlite_sequence WHERE name='request_logs'");
            
            $this->logDb->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->logDb->rollBack();
            error_log("Clear logs error: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            if ($this->logDb && $this->logDb->inTransaction()) {
                $this->logDb->rollBack();
            }
            error_log("Clear logs error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLogStats() {
        try {
            if (!$this->logDb) {
                return ['total' => 0, 'by_type' => []];
            }

            $totalStmt = $this->logDb->query("SELECT COUNT(*) as total FROM request_logs");
            $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
 
            $typeStmt = $this->logDb->query("SELECT query_type, COUNT(*) as count FROM request_logs GROUP BY query_type ORDER BY count DESC");
            $byType = $typeStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $total,
                'by_type' => $byType
            ];
            
        } catch (PDOException $e) {
            error_log("Get stats error: " . $e->getMessage());
            return ['total' => 0, 'by_type' => []];
        }
    }
    
    public function getDatabasePath() {
        return realpath($this->dbPath) ?: $this->dbPath;
    }
    
    public function isDatabaseHealthy() {
        try {
            if (!$this->logDb) {
                return false;
            }

            $this->logDb->query("SELECT 1");
            return true;
            
        } catch (PDOException $e) {
            error_log("Database health check failed: " . $e->getMessage());
            return false;
        }
    }
}
?>
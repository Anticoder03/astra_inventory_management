<?php
require_once 'includes/config.php';

class DatabaseUpdater {
    private $conn;
    private $sqlDirectory;
    private $executedFiles = [];

    public function __construct($conn, $sqlDirectory = 'sql') {
        $this->conn = $conn;
        $this->sqlDirectory = $sqlDirectory;
        $this->createMigrationsTable();
        $this->loadExecutedFiles();
    }

    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('success', 'failed') NOT NULL,
            error_message TEXT
        )";
        
        try {
            $this->conn->query($sql);
        } catch (Exception $e) {
            die("Error creating migrations table: " . $e->getMessage());
        }
    }

    private function loadExecutedFiles() {
        $result = $this->conn->query("SELECT filename FROM migrations WHERE status = 'success'");
        while ($row = $result->fetch_assoc()) {
            $this->executedFiles[] = $row['filename'];
        }
    }

    public function update() {
        if (!is_dir($this->sqlDirectory)) {
            die("SQL directory not found: {$this->sqlDirectory}");
        }

        $files = glob($this->sqlDirectory . '/*.sql');
        sort($files); // Ensure files are executed in order

        foreach ($files as $file) {
            $filename = basename($file);
            
            if (in_array($filename, $this->executedFiles)) {
                echo "Skipping {$filename} - already executed\n";
                continue;
            }

            echo "Executing {$filename}...\n";
            
            try {
                $this->conn->begin_transaction();

                // Read and execute SQL file
                $sql = file_get_contents($file);
                if ($sql === false) {
                    throw new Exception("Could not read file: {$file}");
                }

                // Split SQL into individual statements
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) { return !empty($stmt); }
                );

                foreach ($statements as $statement) {
                    if (!$this->conn->query($statement)) {
                        throw new Exception("Error executing statement: " . $this->conn->error);
                    }
                }

                // Record successful execution
                $stmt = $this->conn->prepare("INSERT INTO migrations (filename, status) VALUES (?, 'success')");
                $stmt->bind_param("s", $filename);
                $stmt->execute();

                $this->conn->commit();
                echo "Successfully executed {$filename}\n";

            } catch (Exception $e) {
                $this->conn->rollback();
                
                // Record failed execution
                $stmt = $this->conn->prepare("INSERT INTO migrations (filename, status, error_message) VALUES (?, 'failed', ?)");
                $stmt->bind_param("ss", $filename, $e->getMessage());
                $stmt->execute();

                echo "Error executing {$filename}: " . $e->getMessage() . "\n";
            }
        }
    }

    public function getStatus() {
        $result = $this->conn->query("
            SELECT 
                filename,
                executed_at,
                status,
                error_message
            FROM migrations
            ORDER BY executed_at DESC
        ");

        echo "\nMigration Status:\n";
        echo "----------------\n";
        
        while ($row = $result->fetch_assoc()) {
            echo sprintf(
                "File: %s\nExecuted: %s\nStatus: %s\n%s\n\n",
                $row['filename'],
                $row['executed_at'],
                $row['status'],
                $row['status'] === 'failed' ? "Error: {$row['error_message']}" : ""
            );
        }
    }
}

// Create SQL directory if it doesn't exist
if (!is_dir('sql')) {
    mkdir('sql', 0777, true);
}

// Move SQL files to sql directory if they exist in root
$sqlFiles = ['database.sql', 'dummy_data.sql'];
foreach ($sqlFiles as $file) {
    if (file_exists($file) && !file_exists("sql/{$file}")) {
        copy($file, "sql/{$file}");
    }
}

// Execute database updates
$updater = new DatabaseUpdater($conn);
$updater->update();
$updater->getStatus(); 










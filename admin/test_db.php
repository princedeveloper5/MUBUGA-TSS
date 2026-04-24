<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

echo "<h1>Database Connection Test</h1>";

$pdo = getDatabaseConnection();

if ($pdo instanceof PDO) {
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    try {
        // Test basic query
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h2>Tables in database:</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        // Check if required tables exist
        $requiredTables = ['users', 'pages', 'news', 'gallery', 'programs', 'settings'];
        echo "<h2>Required tables check:</h2>";
        foreach ($requiredTables as $table) {
            if (in_array($table, $tables)) {
                echo "<p style='color: green;'>✓ $table table exists</p>";
            } else {
                echo "<p style='color: red;'>✗ $table table missing</p>";
            }
        }
        
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role IN ('admin', 'super_admin') AND is_active = 1");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        echo "<h2>Admin users:</h2>";
        echo "<p>Number of active admin users: " . (int)$adminCount . "</p>";
        
        if ($adminCount == 0) {
            echo "<p style='color: red;'>⚠️ No admin users found. You need to create an admin user.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error querying database: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Database connection failed!</p>";
    echo "<p>Please check your database configuration in config/db.php</p>";
}

echo "<p><a href='index.php'>← Back to Admin Login</a></p>";
?>

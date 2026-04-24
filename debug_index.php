<?php
declare(strict_types=1);

echo "<h1>Index Page Debug Tool</h1>";

// Test database connection
require_once __DIR__ . '/config/db.php';

$pdo = getDatabaseConnection();

if ($pdo instanceof PDO) {
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    try {
        // Test basic tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h2>Database Tables:</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        // Test required data
        $requiredTables = ['settings', 'programs', 'news', 'gallery', 'staff'];
        echo "<h2>Required Data Check:</h2>";
        
        foreach ($requiredTables as $table) {
            if (in_array($table, $tables)) {
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "<p style='color: green;'>✓ $table: $count records</p>";
            } else {
                echo "<p style='color: red;'>✗ $table: Table missing</p>";
            }
        }
        
        // Test settings data
        $settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        echo "<h2>Key Settings:</h2>";
        $keySettings = ['school_name', 'school_email', 'school_phone', 'theme_mode'];
        foreach ($keySettings as $key) {
            $value = $settings[$key] ?? 'Not set';
            echo "<p>$key: " . htmlspecialchars($value) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database connection failed!</p>";
    echo "<p>Check your database configuration in config/db.php</p>";
}

// Test file includes
echo "<h2>File Include Tests:</h2>";

$requiredFiles = [
    'includes/site_data.php',
    'includes/site_layout.php', 
    'portal/header.php',
    'portal/footer.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $file missing</p>";
    }
}

// Test site data loading
echo "<h2>Site Data Loading Test:</h2>";

try {
    require_once __DIR__ . '/includes/site_data.php';
    echo "<p style='color: green;'>✓ site_data.php loaded successfully</p>";
    
    // Check if key variables are defined
    $keyVars = ['schoolName', 'contacts', 'programs', 'gallery', 'news'];
    foreach ($keyVars as $var) {
        if (isset($$var)) {
            $count = is_array($$var) ? count($$var) : 'defined';
            echo "<p style='color: green;'>✓ \$$var: $count items</p>";
        } else {
            echo "<p style='color: red;'>✗ \$$var: not defined</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error loading site_data.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='/'>← Back to Homepage</a></p>";
?>

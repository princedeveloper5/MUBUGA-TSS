<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';

echo "<h1>Login System Debug Tool</h1>";

// Test database connection
$pdo = getDatabaseConnection();

if ($pdo instanceof PDO) {
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    try {
        // Check if users table exists
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('users', $tables)) {
            echo "<p style='color: green;'>✓ Users table exists</p>";
            
            // Check admin users
            $stmt = $pdo->prepare("SELECT id, full_name, email, role, is_active FROM users WHERE role IN ('admin', 'super_admin')");
            $stmt->execute();
            $adminUsers = $stmt->fetchAll();
            
            echo "<h2>Admin Users:</h2>";
            if (count($adminUsers) > 0) {
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th></tr>";
                foreach ($adminUsers as $user) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                    echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: red;'>✗ No admin users found in database!</p>";
                echo "<p>You need to create an admin user first.</p>";
            }
            
            // Check all users for debugging
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
            $totalUsers = $stmt->fetchColumn();
            echo "<p>Total users in database: " . (int)$totalUsers . "</p>";
            
        } else {
            echo "<p style='color: red;'>✗ Users table doesn't exist!</p>";
            echo "<p>You need to create the users table.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Database connection failed!</p>";
    echo "<p>Check your database configuration in config/db.php</p>";
}

// Test session
echo "<h2>Session Status:</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✓ Session is active</p>";
    echo "<p>Session ID: " . htmlspecialchars(session_id()) . "</p>";
    
    if (adminIsLoggedIn()) {
        echo "<p style='color: green;'>✓ Admin is logged in</p>";
        $admin = currentAdmin();
        echo "<p>Logged in as: " . htmlspecialchars($admin['full_name'] ?? 'Unknown') . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No admin logged in</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Session not active</p>";
}

// Create admin user form
echo "<h2>Create Admin User (if needed):</h2>";
echo "<form method='POST' style='margin: 20px 0;'>";
echo "<input type='hidden' name='create_admin' value='1'>";
echo "<table>";
echo "<tr><td>Email:</td><td><input type='email' name='email' required></td></tr>";
echo "<tr><td>Full Name:</td><td><input type='text' name='full_name' required></td></tr>";
echo "<tr><td>Password:</td><td><input type='password' name='password' required></td></tr>";
echo "<tr><td>Role:</td><td>";
echo "<select name='role'>";
echo "<option value='admin'>Admin</option>";
echo "<option value='super_admin'>Super Admin</option>";
echo "</select></td></tr>";
echo "</table>";
echo "<button type='submit'>Create Admin User</button>";
echo "</form>";

// Handle admin creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    
    if ($email && $fullName && $password && $pdo instanceof PDO) {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                echo "<p style='color: red;'>User with this email already exists!</p>";
            } else {
                // Create new admin user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, email, password_hash, role, is_active, created_at) 
                    VALUES (:full_name, :email, :password_hash, :role, 1, NOW())
                ");
                $stmt->execute([
                    'full_name' => $fullName,
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'role' => $role
                ]);
                echo "<p style='color: green;'>✓ Admin user created successfully!</p>";
                echo "<p>You can now login with: " . htmlspecialchars($email) . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error creating user: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

echo "<p><a href='index.php'>← Back to Login</a></p>";
?>

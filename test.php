<?php
// test.php - Run this to check if everything is set up correctly
require_once 'database.php';

echo "<h1>🔍 System Check</h1>";

// Check PHP version
echo "<h3>PHP Version: " . phpversion() . "</h3>";

// Check database connection
try {
    $pdo->query("SELECT 1");
    echo "✅ Database connected successfully<br>";
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES");
    $tableCount = 0;
    while($table = $tables->fetch()) {
        echo "📊 Found table: " . $table[0] . "<br>";
        $tableCount++;
    }
    
    if($tableCount == 2) {
        echo "<br>🎉 All systems go! Your application is ready to use.<br>";
        echo "<a href='index.php'>Go to Application</a>";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
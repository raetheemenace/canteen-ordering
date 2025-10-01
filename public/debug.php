<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

echo "<h2>Debug page</h2>";
echo "<p>PHP version: " . phpversion() . "</p>";
echo "<p>Document root: " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "</p>";
echo "<p>__DIR__: " . __DIR__ . "</p>";

echo "<h3>Files existence</h3>";
$files = [
  __DIR__.'/../src/database.php', // older path in examples
  __DIR__.'/../../src/database.php', // safer from public/auth
  __DIR__.'/src/database.php'
];
foreach ($files as $f) {
    echo "<div>$f : " . (file_exists($f) ? "<strong style='color:green'>FOUND</strong>" : "<strong style='color:red'>MISSING</strong>") . "</div>";
}

echo "<h3>Try DB connection using src/database.php (if available)</h3>";
if (file_exists(__DIR__.'/../../src/database.php')) {
    require_once __DIR__.'/../../src/database.php';
    try {
        $pdo = getPDO();
        echo "<div style='color:green'>DB connected OK.</div>";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
        echo "<pre>" . htmlspecialchars(json_encode($tables, JSON_PRETTY_PRINT)) . "</pre>";
    } catch (Exception $e) {
        echo "<div style='color:red'>DB connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    echo "<div style='color:orange'>Skipping DB connect — database.php not found at ../../src/database.php</div>";
}

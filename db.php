<?php
require_once __DIR__ . '/config.php';

$dbFile = __DIR__ . '/data/store.sqlite';
$initDb = !file_exists($dbFile);

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");

    if ($initDb) {
        $pdo->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                competitor_price DECIMAL(10,2) DEFAULT NULL,
                image VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $pdo->exec("
            CREATE TABLE admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['Voidxx', password_hash('admin123', PASSWORD_DEFAULT)]);

        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, competitor_price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Blue Denim Jacket', 'Stylish blue denim jacket, slim fit.', 2999.00, 3299.00, 'denim_jacket.jpg']);
        $stmt->execute(['Red Casual T-Shirt', 'Soft cotton t-shirt with a round neck.', 699.00, 799.00, 'red_tshirt.jpg']);
        $stmt->execute(['Black Chinos', 'Comfortable chinos for daily wear.', 1499.00, 1699.00, 'black_chinos.jpg']);
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>

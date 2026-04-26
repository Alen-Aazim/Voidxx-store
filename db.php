<?php
require_once __DIR__ . '/config.php';

$dbFile = __DIR__ . '/data/store.sqlite';
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) mkdir($dataDir, 0775, true);

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA foreign_keys = ON");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            competitor_price DECIMAL(10,2) DEFAULT NULL,
            image VARCHAR(255) DEFAULT NULL,
            category VARCHAR(100) DEFAULT 'General',
            stock INTEGER DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            status VARCHAR(40) DEFAULT 'Processing',
            note TEXT,
            placed_by VARCHAR(40) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            quantity INTEGER NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );
    ");

    $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM admins")->fetch()['c'];
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['Voidxx', password_hash('admin123', PASSWORD_DEFAULT)]);
    }

    $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'];
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, competitor_price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $samples = [
            ['Midnight Denim Jacket', 'Premium slim-fit denim with a structured shoulder. Built to outlast the trends.', 2999.00, 3299.00, 'denim_jacket.jpg', 'Outerwear', 25],
            ['Crimson Cotton Tee', 'Heavyweight 240gsm combed cotton. Soft hand-feel, zero shrink.', 699.00, 799.00, 'red_tshirt.jpg', 'T-Shirts', 80],
            ['Onyx Tapered Chinos', 'A modern tapered cut in stretch twill. Comfortable from desk to dinner.', 1499.00, 1699.00, 'black_chinos.jpg', 'Bottoms', 40],
            ['Ivory Oxford Shirt', 'Crisp Oxford weave with mother-of-pearl buttons. Office to weekend.', 1299.00, 1499.00, 'oxford_shirt.jpg', 'Shirts', 35],
            ['Charcoal Hoodie', 'Brushed-back fleece, double-lined hood, kangaroo pocket.', 1899.00, 2199.00, 'hoodie.jpg', 'Outerwear', 60],
            ['Sand Linen Trousers', 'Breathable European linen, drawstring waist. Made for warm days.', 1799.00, 1999.00, 'linen_trousers.jpg', 'Bottoms', 22],
            ['Olive Cargo Pants', 'Utility-inspired cargos with reinforced stitching and 6 pockets.', 1999.00, 2299.00, 'cargo_pants.jpg', 'Bottoms', 30],
            ['White Minimal Sneakers', 'Full-grain leather, vulcanized sole, hand-stitched details.', 2499.00, 2899.00, 'sneakers.jpg', 'Footwear', 18],
        ];
        foreach ($samples as $row) $stmt->execute($row);
    }

    $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute(['demo', 'demo@voidxx.test', password_hash('demo1234', PASSWORD_DEFAULT), 'Demo Customer']);
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>

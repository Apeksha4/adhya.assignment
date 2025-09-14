<?php
$pdo = new PDO("mysql:host=localhost;dbname=product_catalog", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle product insert
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);

    if (!empty($name) && $price > 0 && !empty($category)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, category) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $category]);
        echo "<p style='color:green'>Product added successfully!</p>";
    } else {
        echo "<p style='color:red'>All fields are required and price must be positive.</p>";
    }
}

// Category filter
$filter = isset($_GET['category']) ? $_GET['category'] : '';
$query = "SELECT * FROM products";
$params = [];
if ($filter) {
    $query .= " WHERE category = ?";
    $params[] = $filter;
}
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for dropdown
$catStmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Catalog</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f7f7f7; }
        form, table { background: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        input, select { padding: 5px; margin: 5px 0; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Product Catalog</h2>

    <!-- Filter Dropdown -->
    <form method="GET">
        <label>Filter by Category:</label>
        <select name="category" onchange="this.form.submit()">
            <option value="">All</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $filter === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Product Table -->
    <table>
        <tr><th>Name</th><th>Price</th><th>Category</th></tr>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td>₹<?= number_format($p['price'], 2) ?></td>
                <td><?= htmlspecialchars($p['category']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Add Product Form -->
    <h3>Add a Product</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="Product Name" required><br>
        <input type="number" step="0.01" name="price" placeholder="Price" required><br>
        <input type="text" name="category" placeholder="Category" required><br>
        <button type="submit">Add Product</button>
    </form>
</body>
</html>
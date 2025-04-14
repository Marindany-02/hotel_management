<?php
// manage_products.php
include '../db_connection.php';

$edit_mode = false;
$edit_product = null;
$search_term = '';

// Handle search
if (isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
}

// Fetch products
if ($search_term !== '') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE CONCAT('%', ?, '%') ORDER BY id DESC");
    $stmt->bind_param("s", $search_term);
} else {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $delete_id");
    header("Location: manage_products.php");
    exit;
}

// Handle edit
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM products WHERE id = $edit_id");
    $edit_product = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $category = $_POST['category'];
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $description = trim($_POST['description']);
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = uniqid('prod_') . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_path = 'img/products/' . $image_name;

        if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] < 5000000) {
            move_uploaded_file($image_tmp,  $image_path);
            $image = $image_path;
        }
    }

    if (isset($_POST['edit_id'])) {
        $edit_id = (int)$_POST['edit_id'];
        if ($image !== '') {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, quantity=?, description=?, image_url=? WHERE id=?");
            $stmt->bind_param("ssdiisi", $product_name, $category, $price, $quantity, $description, $image, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, quantity=?, description=? WHERE id=?");
            $stmt->bind_param("ssdiis", $product_name, $category, $price, $quantity, $description, $edit_id);
        }
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, quantity, description, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $product_name, $category, $price, $quantity, $description, $image);
        $stmt->execute();
    }

    header("Location: manage_products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            width: 250px;
            position: fixed;
            height: 100%;
            background-color: #343a40;
        }
        .main-container {
            margin-left: 250px;
            padding: 20px;
        }
        .product-form, .product-table {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Manage Products</h3>
            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#productForm" aria-expanded="true" aria-controls="productForm">
                <?php echo $edit_mode ? 'Switch to Add Product' : 'Switch to Edit Mode'; ?>
            </button>
        </div>

        <div class="row">
            <!-- Product List -->
            <div class="col-md-7 product-table">
                <form class="mb-3" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search product..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <button class="btn btn-outline-primary">Search</button>
                    </div>
                </form>
                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Cat</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><img src="<?php echo $p['image_url']; ?>" class="product-img"></td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td><?php echo htmlspecialchars($p['category']); ?></td>
                                <td><?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo $p['quantity']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                                    <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Delete?')" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($products) === 0): ?>
                            <tr><td colspan="6" class="text-center">No products found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Product Form -->
            <div class="col-md-5">
                <div class="collapse show" id="productForm">
                    <div class="product-form">
                        <h5><?php echo $edit_mode ? 'Edit Product' : 'Add New Product'; ?></h5>
                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="edit_id" value="<?php echo $edit_product['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="product_name" class="form-control" value="<?php echo $edit_mode ? $edit_product['name'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <?php
                                    $cats = ['Drinks', 'Food', 'Amenities', 'Services'];
                                    foreach ($cats as $cat) {
                                        $sel = ($edit_mode && $edit_product['category'] === $cat) ? 'selected' : '';
                                        echo "<option value=\"$cat\" $sel>$cat</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" name="price" step="0.01" class="form-control" value="<?php echo $edit_mode ? $edit_product['price'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" value="<?php echo $edit_mode ? $edit_product['quantity'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control"><?php echo $edit_mode ? $edit_product['description'] : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Image <?php echo $edit_mode ? '(optional)' : '(required)'; ?></label>
                                <input type="file" name="image" class="form-control" <?php echo $edit_mode ? '' : 'required'; ?>>
                            </div>
                            <button type="submit" class="btn btn-success w-100"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

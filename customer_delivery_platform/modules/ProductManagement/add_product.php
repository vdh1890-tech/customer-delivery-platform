<?php
require_once '../../includes/db_connect.php';
require_once '../AdminPortal/includes/auth_check.php';

// Handle Add Product Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];

    // Default Image Handling
    $image_url = 'assets/img/default.png';

    // Handle File Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $uploadDir = '../../assets/img/uploads/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777, true);

            $filename = uniqid('prod_') . '.' . $ext;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_url = 'assets/img/uploads/' . $filename;
            }
        }
    } else {
        // Fallback to Category Defaults if no image uploaded
        if ($category == 'sand')
            $image_url = 'assets/img/m-sand.png';
        if ($category == 'aggregate')
            $image_url = 'assets/img/blue-metal.png';
        if ($category == 'stone')
            $image_url = 'assets/img/gravel.png';
    }

    $stmt = $conn->prepare("INSERT INTO products (name, price_per_ton, category, description, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsss", $name, $price, $category, $description, $image_url);

    if ($stmt->execute()) {
        header("Location: index.php?msg=added");
        exit;
    } else {
        $error = "Error adding product: " . $stmt->error;
    }
}

require_once '../AdminPortal/includes/header.php';
?>

<div class="section-title flex-between">
    <h2>Add New Product</h2>
    <a href="index.php" class="btn btn-outline">Back to List</a>
</div>

<div class="admin-card"
    style="background: white; padding: 30px; border-radius: 2px; box-shadow: var(--shadow-sm); max-width: 600px;">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom: 8px; font-weight:600;">Product Name</label>
            <input type="text" name="name" class="form-control" required
                style="width:100%; padding: 10px; border:1px solid #ccc;" placeholder="e.g. M-Sand 20mm">
        </div>

        <div class="grid-2-col" style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="form-group">
                <label style="display:block; margin-bottom: 8px; font-weight:600;">Price (₹ per Ton)</label>
                <input type="number" name="price" class="form-control" required
                    style="width:100%; padding: 10px; border:1px solid #ccc;" placeholder="0.00">
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom: 8px; font-weight:600;">Category</label>
                <select name="category" class="form-control" style="width:100%; padding: 10px; border:1px solid #ccc;">
                    <option value="sand">M-Sand / P-Sand</option>
                    <option value="aggregate">Blue Metal / Aggregates</option>
                    <option value="stone">Gravel / Stones</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom: 8px; font-weight:600;">Description</label>
            <textarea name="description" rows="3" style="width:100%; padding: 10px; border:1px solid #ccc;" placeholder="Optional product description..."></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom: 8px; font-weight:600;">Product Image</label>
            <input type="file" name="image" accept="image/*"
                style="width:100%; padding: 10px; border:1px solid #ccc; background:white;">
            <small style="color:gray;">* Upload valid image (JPG, PNG, WEBP)</small>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">SAVE PRODUCT</button>
    </form>
</div>

<?php require_once '../AdminPortal/includes/footer.php'; ?>
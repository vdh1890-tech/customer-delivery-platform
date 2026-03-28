<?php
require_once '../../includes/db_connect.php';
header('Content-Type: application/json');

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = intval($_POST['id']);
$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$price = floatval($_POST['price']);
$stock_status = $_POST['stock_status'] ?? 'in_stock';

if (!$id || !$name || !$category || $price < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Handle Image Upload
$image_sql_part = "";
$params = [$name, $category, $stock_status, $price];
$types = "sssd";

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../assets/img/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $new_filename = "prod_" . time() . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Validate image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check === false) {
        echo json_encode(['success' => false, 'message' => 'File is not an image.']);
        exit;
    }
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $db_image_path = "assets/img/products/" . $new_filename;
        $image_sql_part = ", image_url = ?";
        $params[] = $db_image_path;
        $types .= "s";
    } else {
        echo json_encode(['success' => false, 'message' => 'Sorry, there was an error uploading your file.']);
        exit;
    }
}

// Add ID to params for WHERE clause
$params[] = $id;
$types .= "i";

$sql = "UPDATE products SET name = ?, category = ?, stock_status = ?, price_per_ton = ? $image_sql_part WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
?>

<?php
require_once 'includes/db_connect.php';

echo "<h2>Updating M-Sand Image...</h2>";

$url = 'assets/img/msand-double-washed.png';
$name = 'Double Washed M-Sand';

$sql = "UPDATE products SET image_url = '$url' WHERE name = '" . $conn->real_escape_string($name) . "'";
if ($conn->query($sql) === TRUE) {
    echo "✅ Updated image for: $name to $url<br>";
} else {
    echo "❌ Error updating $name: " . $conn->error . "<br>";
}

echo "<h3>Image Update Complete!</h3>";
?>

<?php
require_once 'includes/db_connect.php';

echo "<h2>Updating GSB Image...</h2>";

$url = 'assets/img/gsb.png';
$name = 'Granular Sub-Base (GSB)';

$sql = "UPDATE products SET image_url = '$url' WHERE name = '" . $conn->real_escape_string($name) . "'";
if ($conn->query($sql) === TRUE) {
    echo "✅ Updated image for: $name to $url<br>";
} else {
    echo "❌ Error updating $name: " . $conn->error . "<br>";
}

echo "<h3>Image Update Complete!</h3>";
?>

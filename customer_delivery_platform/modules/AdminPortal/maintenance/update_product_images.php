<?php
require_once 'includes/db_connect.php';

echo "<h2>Updating Product Images...</h2>";

$updates = [
    'Blue Metal (40mm)' => 'assets/img/blue-metal-40mm.png',
    'Blue Metal (12mm)' => 'assets/img/blue-metal-40mm.png', // Reusing 40mm image for now as they look similar enough
    'Blue Metal Chips (6mm)' => 'assets/img/gravel.png', // Keep existing or update if we had one
    'Stone Dust' => 'assets/img/stone-dust.png',
    'Filling Sand' => 'assets/img/filling-sand.png',
    'Double Washed M-Sand' => 'assets/img/m-sand.png', // Keep existing
    'Wet Mix Macadam (WMM)' => 'assets/img/soling-stone.png', // Close enough match
    'Granular Sub-Base (GSB)' => 'assets/img/gravel.png',
    'Soling Stone / Boulders' => 'assets/img/soling-stone.png',
    'Red Soil' => 'assets/img/red-soil.png',
    'Dalmia Cement (OPC 53)' => 'assets/img/dalmia-cement.jpg',
    'Ramco Supercrete' => 'assets/img/ramco-cement.jpg'
];

foreach ($updates as $name => $url) {
    $sql = "UPDATE products SET image_url = '$url' WHERE name = '" . $conn->real_escape_string($name) . "'";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Updated image for: $name<br>";
    } else {
        echo "❌ Error updating $name: " . $conn->error . "<br>";
    }
}

echo "<h3>Image Updates Complete!</h3>";
?>

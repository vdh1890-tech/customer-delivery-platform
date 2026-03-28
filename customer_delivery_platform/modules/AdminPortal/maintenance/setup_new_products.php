<?php
require_once 'includes/db_connect.php';

echo "<h2>Setting up New Products...</h2>";

// 1. Update Category ENUM to include 'cement'
try {
    $sql = "ALTER TABLE products MODIFY COLUMN category ENUM('sand','aggregate','stone','other','cement') NOT NULL DEFAULT 'aggregate'";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Category ENUM updated successfully.<br>";
    } else {
        // Warning if it fails (might already exist)
        echo "⚠️ Category ENUM update warning (might already exist): " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "⚠️ Error updating ENUM: " . $e->getMessage() . "<br>";
}

// 2. Insert New Products
$products = [
    // --- Aggregates ---
    [
        'name' => 'Blue Metal (40mm)',
        'description' => 'Coarse aggregate for heavy-duty foundations and road bases.',
        'price' => 900.00,
        'image' => 'assets/img/blue-metal.png', // Placeholder
        'category' => 'aggregate'
    ],
    [
        'name' => 'Blue Metal (12mm)',
        'description' => 'Medium aggregate for standard concrete mix.',
        'price' => 980.00,
        'image' => 'assets/img/blue-metal.png', // Placeholder
        'category' => 'aggregate'
    ],
    [
        'name' => 'Blue Metal Chips (6mm)',
        'description' => 'Fine chips for surface dressing and finishing.',
        'price' => 1050.00,
        'image' => 'assets/img/gravel.png', // Placeholder
        'category' => 'aggregate'
    ],
    [
        'name' => 'Stone Dust',
        'description' => 'Crushed stone fine dust for paver blocks and pathways.',
        'price' => 700.00,
        'image' => 'assets/img/m-sand.png', // Placeholder (looks like sand)
        'category' => 'aggregate'
    ],
    // --- Sand ---
    [
        'name' => 'Filling Sand',
        'description' => 'Cost-effective sand for basement filling and ground leveling.',
        'price' => 600.00,
        'image' => 'assets/img/p-sand.png', // Placeholder
        'category' => 'sand'
    ],
    [
        'name' => 'Double Washed M-Sand',
        'description' => 'Premium double-washed manufactured sand for high-strength concrete.',
        'price' => 1400.00,
        'image' => 'assets/img/m-sand.png', // Placeholder
        'category' => 'sand'
    ],
    // --- Stones ---
    [
        'name' => 'Wet Mix Macadam (WMM)',
        'description' => 'Graded crushed aggregate for road base layers.',
        'price' => 850.00,
        'image' => 'assets/img/gravel.png',
        'category' => 'stone'
    ],
    [
        'name' => 'Granular Sub-Base (GSB)',
        'description' => 'Natural or crushed material for road sub-base.',
        'price' => 650.00,
        'image' => 'assets/img/gravel.png',
        'category' => 'stone'
    ],
    [
        'name' => 'Soling Stone / Boulders',
        'description' => 'Large boulders for retaining walls and foundations.',
        'price' => 800.00,
        'image' => 'assets/img/blue-metal.png',
        'category' => 'stone'
    ],
    [
        'name' => 'Red Soil',
        'description' => 'Fertile red soil for landscaping and gardening.',
        'price' => 500.00,
        'image' => 'assets/img/p-sand.png', // Placeholder (reddish usually)
        'category' => 'other'
    ],
    // --- Cement ---
    [
        'name' => 'Dalmia Cement (OPC 53)',
        'description' => 'High strength cement for structural concrete.',
        'price' => 380.00, // Per Bag
        'image' => 'assets/img/m-sand.png', // Placeholder (need bag image ideally)
        'category' => 'cement'
    ],
    [
        'name' => 'Ramco Supercrete',
        'description' => 'Premium blended cement for crack-free concrete.',
        'price' => 410.00, // Per Bag
        'image' => 'assets/img/m-sand.png', // Placeholder
        'category' => 'cement'
    ]
];

foreach ($products as $p) {
    // Check if exists
    $check = $conn->query("SELECT id FROM products WHERE name = '" . $conn->real_escape_string($p['name']) . "'");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO products (name, description, price_per_ton, image_url, category) VALUES (
            '" . $conn->real_escape_string($p['name']) . "',
            '" . $conn->real_escape_string($p['description']) . "',
            {$p['price']},
            '{$p['image']}',
            '{$p['category']}'
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "✅ Added: {$p['name']}<br>";
        } else {
            echo "❌ Error adding {$p['name']}: " . $conn->error . "<br>";
        }
    } else {
        echo "ℹ️ Skipped (Already Exists): {$p['name']}<br>";
    }
}

echo "<h3>Setup Complete!</h3>";
?>

<?php

// Database connection (replace with your actual connection details)
$host = 'localhost';
$dbname = 'amitbookdepot';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all image paths from the database
$query = $pdo->query("SELECT featured, flash, gallery FROM product_image");
$imagesInDatabase = [];

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    foreach ($row as $image) {
        if ($image) {
            $imagesInDatabase[] = basename($image);
        }
    }
}
// echo'<pre>';
// print_r($imagesInDatabase);
// echo'</pre>';

// die;
// // Path to the upload directory
$uploadDir = __DIR__ . '/storage/photos/upload/';
$backupDir = __DIR__ . '/storage/photos/rejected/';

// Create backup directory if it doesn't exist
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Scan the upload directory for all images
$allImagesInDirectory = scandir($uploadDir);

// Loop through each file in the upload directory
foreach ($allImagesInDirectory as $file) {
    // Skip directories
    if ($file === '.' || $file === '..') {
        continue;
    }

    // Check if the file is not in the database
    if (!in_array($file, $imagesInDatabase)) {
        // Move the file to the backup directory
        rename($uploadDir . $file, $backupDir . $file);
        echo "Moved $file to backup folder.\n";
    }
}

echo "Image organization completed.";

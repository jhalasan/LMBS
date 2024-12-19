<?php
// getbooks.php
header("Content-Type: application/json");

// Path to JSON file
$jsonFilePath = "../data/books.json";

// Check if the file exists
if (file_exists($jsonFilePath)) {
    $books = json_decode(file_get_contents($jsonFilePath), true);
    if ($books) {
        echo json_encode(["success" => true, "books" => $books]);
    } else {
        echo json_encode(["success" => false, "message" => "No books found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Books file not found."]);
}

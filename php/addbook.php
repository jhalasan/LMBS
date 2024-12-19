<?php
// addBook.php
header("Content-Type: application/json");

// Path to JSON file for storing books
$jsonFilePath = "../data/books.json";

// Initialize hash table 
$booksHashTable = [];

// Check if JSON file exists and load data
if (file_exists($jsonFilePath)) {
    $booksHashTable = json_decode(file_get_contents($jsonFilePath), true) ?? [];
}

// Read input from request
$inputData = json_decode(file_get_contents("php://input"), true);

if (!$inputData || !isset($inputData['bookID'], $inputData['bookTitle'], $inputData['bookAuthor'])) {
    echo json_encode(["success" => false, "message" => "Invalid input data."]);
    exit;
}

$bookID = $inputData['bookID'];
$bookTitle = $inputData['bookTitle'];
$bookAuthor = $inputData['bookAuthor'];

// Check if book ID already exists
if (array_key_exists($bookID, $booksHashTable)) {
    echo json_encode(["success" => false, "message" => "Book ID already exists."]);
    exit;
}

// Add new book to the hash table with default status 'available'
$booksHashTable[$bookID] = [
    "title" => $bookTitle,
    "author" => $bookAuthor,
    "status" => "available"  // Default status for new books
];

// Save updated hash table to the JSON file
if (file_put_contents($jsonFilePath, json_encode($booksHashTable, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true, "message" => "Book added successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save book data."]);
}

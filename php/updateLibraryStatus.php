<?php
header("Content-Type: application/json");

$libraryFilePath = "../data/books.json";

// Read the current books from the JSON file
if (!file_exists($libraryFilePath)) {
    echo json_encode(["success" => false, "message" => "Library data not found."]);
    exit;
}

$books = json_decode(file_get_contents($libraryFilePath), true);

// Read incoming data (book status updates)
$inputData = json_decode(file_get_contents("php://input"), true);

// Validate the incoming data
if (!$inputData || !isset($inputData['books'])) {
    echo json_encode(["success" => false, "message" => "Invalid book data."]);
    exit;
}

$updatedBooks = $inputData['books'];

// Update the book status in the array
foreach ($updatedBooks as $bookID => $bookData) {
    if (isset($books[$bookID])) {
        // Only update the status if the book exists in the library
        if (isset($bookData['status'])) {
            $books[$bookID]['status'] = $bookData['status'];
        } else {
            echo json_encode(["success" => false, "message" => "Missing status for book ID $bookID"]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Book with ID $bookID not found in the library."]);
        exit;
    }
}

// Save the updated books data back to the JSON file
if (file_put_contents($libraryFilePath, json_encode($books, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true, "message" => "Book status updated successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update book status."]);
}
?>

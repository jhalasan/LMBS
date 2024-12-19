<?php
// searchbook.php
header("Content-Type: application/json");
require_once "BST.php";

// Load books data from JSON file
$jsonFilePath = "../data/books.json";
$books = file_exists($jsonFilePath) ? json_decode(file_get_contents($jsonFilePath), true) : [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputData = json_decode(file_get_contents("php://input"), true); // Parse JSON input

    if (!isset($inputData['query'], $inputData['searchOption'])) {
        echo json_encode(["success" => false, "message" => "Invalid input data."]);
        exit;
    }

    $query = strtolower($inputData['query']); // Convert query to lowercase
    $searchOption = $inputData['searchOption'];

    // Ensure the query is at least one character long
    if (strlen($query) < 1) {
        echo json_encode(["success" => false, "message" => "Query must be at least one character long."]);
        exit;
    }

    // Create a BST and populate it with book data
    $bst = new BST();
    foreach ($books as $id => $book) {
        $key = "";

        if ($searchOption === "bookID") {
            $key = strtolower(substr($id, 0, strlen($query))); // Extract the first part of bookID for comparison
        } elseif ($searchOption === "title") {
            $key = strtolower(substr($book['title'], 0, strlen($query))); // Extract the first part of title for comparison
        } elseif ($searchOption === "author") {
            $key = strtolower(substr($book['author'], 0, strlen($query))); // Extract the first part of author for comparison
        }

        // Only insert the book into the BST if the first part of the field matches the query exactly
        if ($key === $query) {
            $bst->insert($key, [
                "id" => $id,
                "title" => $book['title'],
                "author" => $book['author'],
                "status" => $book['status']
            ]);
        }
    }

    // Search the BST for matching books
    $matchingBooks = $bst->search($query);

    if ($matchingBooks) {
        // Prepare response with matching books
        $responseBooks = [];
        foreach ($matchingBooks as $book) {
            $responseBooks[$book['id']] = [
                "title" => $book["title"],
                "author" => $book["author"],
                "status" => $book["status"]
            ];
        }

        echo json_encode(["success" => true, "books" => $responseBooks]);
    } else {
        echo json_encode(["success" => false, "message" => "No matching books found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>

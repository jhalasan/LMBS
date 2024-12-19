<?php
header("Content-Type: application/json");

// Path to the JSON files
$requestFilePath = "../data/bookrequests.json";
$booksFilePath = "../data/books.json";

// Ensure the request file exists
if (!file_exists($requestFilePath)) {
    if (!file_put_contents($requestFilePath, json_encode([]))) {
        echo json_encode(["success" => false, "message" => "Failed to initialize request file."]);
        exit;
    }
}

// Ensure the books file exists
if (!file_exists($booksFilePath)) {
    echo json_encode(["success" => false, "message" => "Books file not found."]);
    exit;
}

// Read the current requests and books from their respective JSON files
$requestsContent = file_get_contents($requestFilePath);
$requests = json_decode($requestsContent, true);

$booksContent = file_get_contents($booksFilePath);
$books = json_decode($booksContent, true);

// Read incoming request data from the client
$inputData = json_decode(file_get_contents("php://input"), true);

// Validate incoming data
if (isset($inputData['action'])) {
    if ($inputData['action'] === 'confirm') {
        // Handle confirm request
        $bookID = htmlspecialchars(trim($inputData['bookID']));
        
        // Check if the book exists in the library
        if (!isset($books[$bookID])) {
            echo json_encode(["success" => false, "message" => "Book not found in the library."]);
            exit;
        }
        
        // Find the corresponding request in bookrequests.json
        $requestIndex = -1;
        foreach ($requests as $index => $request) {
            if ($request['bookID'] === $bookID && $request['status'] === 'requested') {
                $requestIndex = $index;
                break;
            }
        }

        // If the request doesn't exist or is already confirmed, return an error
        if ($requestIndex === -1) {
            echo json_encode(["success" => false, "message" => "Request not found or already confirmed."]);
            exit;
        }

        // Step 1: Update the book status to "borrowed"
        $books[$bookID]['status'] = 'borrowed';

        // Step 2: Update the request status to "confirmed"
        $requests[$requestIndex]['status'] = 'confirmed';

        // Step 3: Save the updated books and requests back to their respective JSON files
        $updateBooksResponse = file_put_contents($booksFilePath, json_encode($books, JSON_PRETTY_PRINT));
        $updateRequestsResponse = file_put_contents($requestFilePath, json_encode($requests, JSON_PRETTY_PRINT));

        if ($updateBooksResponse && $updateRequestsResponse) {
            echo json_encode(["success" => true, "message" => "Request confirmed and book borrowed."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update the book or request data."]);
        }

    } else {
        echo json_encode(["success" => false, "message" => "Invalid action."]);
    }
} else {
    // Handle adding a new request
    if (!isset($inputData['bookID'], $inputData['title'], $inputData['author'], $inputData['status'])) {
        echo json_encode(["success" => false, "message" => "Missing required fields: bookID, title, author, or status"]);
        exit;
    }

    $bookID = htmlspecialchars(trim($inputData['bookID']));
    $title = htmlspecialchars(trim($inputData['title']));
    $author = htmlspecialchars(trim($inputData['author']));
    $status = htmlspecialchars(trim($inputData['status']));

    // Check if the book is already in the request list
    foreach ($requests as $request) {
        if ($request['bookID'] === $bookID && $request['status'] === 'requested') {
            echo json_encode(["success" => false, "message" => "This book has already been requested."]);
            exit;
        }
    }

    // Prepare the new request data
    $requestData = [
        "bookID" => $bookID,
        "title" => $title,
        "author" => $author,
        "timestamp" => time(),
        "status" => 'requested' // Set the status to "requested" when the request is added
    ];

    // Add the new request to the requests array
    $requests[] = $requestData;

    // Step 1: Update the book status to "requested" if not already
    if (isset($books[$bookID])) {
        // Add the new book with status "requested"
        $books[$bookID] = [
            "title" => $title,
            "author" => $author,
            "status" => "requested"  // Set status as "requested" for new books
        ];
    }

    // Step 2: Save the updated requests and books arrays back to their respective JSON files
    $updateBooksResponse = file_put_contents($booksFilePath, json_encode($books, JSON_PRETTY_PRINT));
    $updateRequestsResponse = file_put_contents($requestFilePath, json_encode($requests, JSON_PRETTY_PRINT));

    if ($updateBooksResponse && $updateRequestsResponse) {
        echo json_encode(["success" => true, "message" => "Request added successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save request data."]);
    }
}
?>

<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$booksFile = "../data/books.json";

if (!file_exists($booksFile)) {
    echo json_encode(['success' => false, 'message' => 'Books data not found']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['bookID'], $data['newStatus'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$books = json_decode(file_get_contents($booksFile), true);

if (!isset($books[$data['bookID']])) {
    echo json_encode(['success' => false, 'message' => 'Book not found']);
    exit();
}

// Update the book's status
$books[$data['bookID']]['status'] = $data['newStatus'];

// Save the updated books array back to the file
$fp = fopen($booksFile, 'w');
if (flock($fp, LOCK_EX)) {
    fwrite($fp, json_encode($books, JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN);
    echo json_encode(['success' => true, 'message' => 'Book status updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'File lock error']);
}
fclose($fp);
?>

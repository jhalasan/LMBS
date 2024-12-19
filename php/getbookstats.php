<?php
// Path to your books.json file
$filePath = '../data/books.json';

// Check if the file exists
if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'message' => 'Books data file not found.']);
    exit;
}

// Read the JSON file
$data = file_get_contents($filePath);

// Decode the JSON data into an associative array
$books = json_decode($data, true);

// Check if decoding was successful
if ($books === null) {
    echo json_encode(['success' => false, 'message' => 'Error decoding JSON data.']);
    exit;
}

// Initialize counters
$totalBooks = count($books);
$booksBorrowed = 0;
$booksReturned = 0;
$availableBooks = 0;

// Loop through the books and count the statuses
foreach ($books as $book) {
    if (isset($book['status'])) {
        switch ($book['status']) {
            case 'borrowed':
                $booksBorrowed++;
                break;
            case 'returned':
                $booksReturned++;
                break;
            case 'available':
                $availableBooks++;
                break;
        }
    }
}

// Prepare the response with the stats
$response = [
    'success' => true,
    'totalBooks' => $totalBooks,
    'booksBorrowed' => $booksBorrowed,
    'booksReturned' => $booksReturned,
    'availableBooks' => $availableBooks
];

// Return the stats as a JSON response
echo json_encode($response);
?>

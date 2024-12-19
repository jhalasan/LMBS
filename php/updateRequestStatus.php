<?php
header("Content-Type: application/json");

$requestFilePath = "../data/bookrequests.json";

// Read the current requests from the JSON file
if (!file_exists($requestFilePath)) {
    echo json_encode(["success" => false, "message" => "No request data found."]);
    exit;
}

$requests = json_decode(file_get_contents($requestFilePath), true);

// Read incoming data (updated requests list)
$inputData = json_decode(file_get_contents("php://input"), true);

// Validate the incoming data
if (!$inputData || !isset($inputData['requests'])) {
    echo json_encode(["success" => false, "message" => "Invalid request data."]);
    exit;
}

$updatedRequests = $inputData['requests'];

// Save the updated requests back to the JSON file
if (file_put_contents($requestFilePath, json_encode($updatedRequests, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true, "message" => "Request queue updated."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update request queue."]);
}
?>
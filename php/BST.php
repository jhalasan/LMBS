<?php
class BSTNode {
    public $key;
    public $data;
    public $left;
    public $right;

    public function __construct($key, $data) {
        $this->key = $key;
        $this->data = [$data]; // Store multiple matching books in an array
        $this->left = null;
        $this->right = null;
    }
}

class BST {
    private $root;

    public function __construct() {
        $this->root = null;
    }

    // Insert a node into the BST
    public function insert($key, $data) {
        $this->root = $this->insertNode($this->root, $key, $data);
    }

    private function insertNode($node, $key, $data) {
        if ($node === null) {
            return new BSTNode($key, $data);
        }

        if ($key < $node->key) {
            $node->left = $this->insertNode($node->left, $key, $data);
        } elseif ($key > $node->key) {
            $node->right = $this->insertNode($node->right, $key, $data);
        } else {
            // If key already exists, add the data to the existing node
            $node->data[] = $data;
        }

        return $node;
    }

    // Search the BST for a key
    public function search($key) {
        return $this->searchNode($this->root, $key);
    }

    private function searchNode($node, $key) {
        if ($node === null) {
            return null; // Key not found
        }

        if (strpos($node->key, $key) === 0) { // Matches if key starts with the query
            return $node->data; // Return the data of the matching node
        }

        if ($key < $node->key) {
            return $this->searchNode($node->left, $key);
        } else {
            return $this->searchNode($node->right, $key);
        }
    }
}
?>

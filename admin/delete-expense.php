<?php
require_once('../config.php');

// DELETE logic
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM expenses WHERE id = $id");
    echo "<script>alert('Expense yasibwe neza!'); window.location='http://localhost/smart poultry farm/admin/?page=expenses'; </script>";
    exit;
}




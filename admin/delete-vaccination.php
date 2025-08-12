<?php
require_once('../config.php');

// Delete logic
if (isset($_GET['delete_id'])) {
  $id = intval($_GET['delete_id']);
  $conn->query("DELETE FROM vaccination WHERE id = $id");
  echo "<script>alert('Vaccination yakuweho neza!'); location.href='http://localhost/smart poultry farm/admin/?page=vaccination';</script>";
  exit;
}


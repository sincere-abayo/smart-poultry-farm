<?php
require_once('../config.php');

// DELETE logic
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM expenses WHERE id = $id");
    echo "<script>alert('Expense yasibwe neza!'); window.location='expenses.php';</script>";
    exit;
}

// UPDATE logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("UPDATE expenses SET expense_title=?, amount=?, description=? WHERE id=?");
    $stmt->bind_param("sdsi", $title, $amount, $desc, $id);
    $stmt->execute();
    echo "<script>alert('Expense yavuguruwe neza!'); window.location='expenses.php';</script>";
    exit;
}

// ADD logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_id'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO expenses (expense_title, amount, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $title, $amount, $desc);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Expense Added!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to add expense!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Expenses</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    @media print {
      body * {
        visibility: hidden;
      }
      #printableTable, #printableTable * {
        visibility: visible;
      }
      #printableTable {
        position: absolute;
        left: 0;
        top: 0;
      }
    }
  </style>
</head>
<body class="container py-4">

<h3>➕ Add Expense</h3>
<form method="POST" class="card card-body mb-4">
  <div class="form-group">
    <label>Title</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Amount</label>
    <input type="number" step="0.01" name="amount" class="form-control" required>
  </div>
  <div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control"></textarea>
  </div>
  <button type="submit" class="btn btn-primary">💾 Save</button>
</form>

<h4>📋 All Expenses</h4>

<div id="printableTable">
  <table class="table table-bordered" id="expensesTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Title</th>
        <th>Amount</th>
        <th>Description</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $res = $conn->query("SELECT * FROM expenses ORDER BY date_created DESC");
      $i = 1;
      while($row = $res->fetch_assoc()):
      ?>
      <tr data-id="<?= $row['id'] ?>">
        <td><?= $i++ ?></td>
        <td class="title"><?= htmlspecialchars($row['expense_title']) ?></td>
        <td class="amount"><?= $row['amount'] ?></td>
        <td class="description"><?= htmlspecialchars($row['description']) ?></td>
        <td><?= $row['date_created'] ?></td>
        <td>
          <button type="button" class="btn btn-warning btn-sm editBtn">✏️</button>
          <a href="expenses.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Urashaka koko gusiba iyi expense?')" class="btn btn-danger btn-sm">🗑️</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Buttons below table -->
<div class="mb-4">
  <button onclick="printTable()" class="btn btn-secondary">🖨️ Print Expenses</button>
  <button id="sendWhatsappBtn" class="btn btn-success">📲 Send All to WhatsApp</button>
</div>

<script>
function printTable() {
  window.print();
}

// Inline editing
document.querySelectorAll(".editBtn").forEach(function(button){
  button.addEventListener("click", function(){
    const row = button.closest("tr");
    const id = row.getAttribute("data-id");
    
    const title = row.querySelector(".title").innerText;
    const amount = row.querySelector(".amount").innerText;
    const desc = row.querySelector(".description").innerText;
    
    row.innerHTML = `
      <form method="POST" style="margin:0;">
        <td>${row.cells[0].innerText}</td>
        <td><input type="text" name="title" value="${title}" class="form-control" required></td>
        <td><input type="number" step="0.01" name="amount" value="${amount}" class="form-control" required></td>
        <td><input type="text" name="description" value="${desc}" class="form-control"></td>
        <td>${row.cells[4].innerText}</td>
        <td>
          <input type="hidden" name="update_id" value="${id}">
          <button type="submit" class="btn btn-success btn-sm">✔️ Save</button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()">❌ Cancel</button>
        </td>
      </form>
    `;
  });
});

// Send all table data to WhatsApp
document.getElementById('sendWhatsappBtn').addEventListener('click', function() {
  let rows = document.querySelectorAll('#expensesTable tbody tr');
  let message = 'Expenses List:%0A';

  rows.forEach((row, index) => {
    const title = encodeURIComponent(row.querySelector('.title').innerText);
    const amount = encodeURIComponent(row.querySelector('.amount').innerText);
    const desc = encodeURIComponent(row.querySelector('.description').innerText);
    const date = encodeURIComponent(row.cells[4].innerText);

    message += `${index + 1}. Title: ${title}, Amount: ${amount}, Description: ${desc}, Date: ${date}%0A`;
  });

  const phoneNumber = '250780041648'; // Nomero ya WhatsApp mu format countrycode + number (nta + imbere)
  const waLink = `https://wa.me/${phoneNumber}?text=${message}`;
  window.open(waLink, '_blank');
});
</script>

</body>
</html>

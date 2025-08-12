<?php
require_once('../config.php');

// Delete logic
if (isset($_GET['delete_id'])) {
  $id = intval($_GET['delete_id']);
  $conn->query("DELETE FROM vaccination WHERE id = $id");
  echo "<script>alert('Vaccination yakuweho neza!'); location.href='?page=vaccination';</script>";
  exit;
}

// Update logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_id'])) {
  $id = intval($_POST['update_id']);
  $stmt = $conn->prepare("UPDATE vaccination SET flock_name=?, vaccine_name=?, date_given=?, next_due_date=?, description=? WHERE id=?");
  $stmt->bind_param("sssssi", $_POST['flock_name'], $_POST['vaccine_name'], $_POST['date_given'], $_POST['next_due_date'], $_POST['description'], $id);
  $stmt->execute();
  echo "<script>alert('Vaccination yavuguruwe neza!'); location.href='?page=vaccination';</script>";
  exit;
}

// Add logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_id'])) {
  $stmt = $conn->prepare("INSERT INTO vaccination (flock_name, vaccine_name, date_given, next_due_date, description) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $_POST['flock_name'], $_POST['vaccine_name'], $_POST['date_given'], $_POST['next_due_date'], $_POST['description']);
  $stmt->execute();
  echo "<script>alert('Vaccination yashyizwe neza!'); location.href='?page=vaccination';</script>";
  exit;
}
?>

<!-- Add Vaccination Form -->
<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">➕ Add Vaccination</h3>
  </div>
  <div class="card-body">
    <form method="POST">
      <div class="form-group"><label>Flock Name</label><input type="text" name="flock_name" class="form-control"
          required></div>
      <div class="form-group"><label>Vaccine Name</label><input type="text" name="vaccine_name" class="form-control"
          required></div>
      <div class="form-group"><label>Date Given</label><input type="date" name="date_given" class="form-control"
          required></div>
      <div class="form-group"><label>Next Due Date</label><input type="date" name="next_due_date" class="form-control">
      </div>
      <div class="form-group"><label>Description</label><textarea name="description" class="form-control"></textarea>
      </div>
      <div class="form-group text-right"><button type="submit" class="btn btn-primary">💾 Save</button></div>
    </form>
  </div>
</div>

<!-- Vaccination Table -->
<div class="card card-outline card-success mt-4">
  <div class="card-header">
    <h3 class="card-title">📋 All Vaccinations</h3>
  </div>
  <div class="card-body" id="printableArea">
    <table class="table table-bordered" id="vaccinationTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Flock Name</th>
          <th>Vaccine</th>
          <th>Date Given</th>
          <th>Next Due</th>
          <th>Description</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 1;
        $vaccs = $conn->query("SELECT * FROM vaccination ORDER BY date_given DESC");
        while ($row = $vaccs->fetch_assoc()):
          ?>
          <tr data-id="<?= $row['id'] ?>">
            <td><?= $i++ ?></td>
            <td class="flock_name"><?= htmlspecialchars($row['flock_name']) ?></td>
            <td class="vaccine_name"><?= htmlspecialchars($row['vaccine_name']) ?></td>
            <td class="date_given"><?= $row['date_given'] ?></td>
            <td class="next_due_date"><?= $row['next_due_date'] ?></td>
            <td class="description"><?= htmlspecialchars($row['description']) ?></td>
            <td>
              <button type="button" class="btn btn-warning btn-sm editBtn">✏️</button>
              <a href="vaccination.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Urashaka koko gusiba?')"
                class="btn btn-danger btn-sm">🗑️</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <div class="text-right mt-3">
      <button onclick="printDiv('printableArea')" class="btn btn-info btn-sm">🖨️ Print</button>
      <!-- WhatsApp Button -->
      <button id="sendWhatsappBtn" class="btn btn-success btn-sm">📲 Send All to WhatsApp</button>
    </div>
  </div>
</div>

<!-- Print Script -->
<script>
  function printDiv(divId) {
    var printContents = document.getElementById(divId).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
  }
</script>

<!-- Inline Edit Script -->
<script>
  document.querySelectorAll(".editBtn").forEach(function (button) {
    button.addEventListener("click", function () {
      const row = button.closest("tr");
      const id = row.getAttribute("data-id");

      // Get existing values
      const flock = row.querySelector(".flock_name").innerText;
      const vaccine = row.querySelector(".vaccine_name").innerText;
      const date = row.querySelector(".date_given").innerText;
      const next = row.querySelector(".next_due_date").innerText;
      const desc = row.querySelector(".description").innerText;

      // Replace with input fields + form
      row.innerHTML = `
          <form method="POST">
            <td>${row.cells[0].innerText}</td>
            <td><input type="text" name="flock_name" value="${flock}" class="form-control" required></td>
            <td><input type="text" name="vaccine_name" value="${vaccine}" class="form-control" required></td>
            <td><input type="date" name="date_given" value="${date}" class="form-control" required></td>
            <td><input type="date" name="next_due_date" value="${next}" class="form-control"></td>
            <td><input type="text" name="description" value="${desc}" class="form-control"></td>
            <td>
              <input type="hidden" name="update_id" value="${id}">
              <button type="submit" class="btn btn-success btn-sm">✔️ Save</button>
              <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()">❌ Cancel</button>
            </td>
          </form>
        `;
    });
  });
</script>

<!-- WhatsApp Send Script -->
<script>
  document.getElementById('sendWhatsappBtn').addEventListener('click', function () {
    let rows = document.querySelectorAll('#vaccinationTable tbody tr');
    let message = 'Vaccination Records:%0A';

    rows.forEach((row, index) => {
      const flock = encodeURIComponent(row.querySelector('.flock_name').innerText);
      const vaccine = encodeURIComponent(row.querySelector('.vaccine_name').innerText);
      const dateGiven = encodeURIComponent(row.querySelector('.date_given').innerText);
      const nextDue = encodeURIComponent(row.querySelector('.next_due_date').innerText);
      const description = encodeURIComponent(row.querySelector('.description').innerText);

      message += `${index + 1}. Flock: ${flock}, Vaccine: ${vaccine}, Date Given: ${dateGiven}, Next Due: ${nextDue}, Description: ${description}%0A`;
    });

    const phoneNumber = '250780041648'; // WhatsApp number without "+" sign
    const waLink = `https://wa.me/${phoneNumber}?text=${message}`;
    window.open(waLink, '_blank');
  });
</script>
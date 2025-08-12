<?php
$conn = new mysqli("localhost", "root", "", "smart_poultry_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DELETE (with AJAX support)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM production WHERE id = $id");
    if (isset($_GET['ajax'])) {
        http_response_code(200);
        exit;
    } else {
        echo "<script>alert('Record deleted'); location.href='';</script>";
        exit;
    }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $stmt = $conn->prepare("UPDATE production SET production_date=?, production_type=?, chicken_type=?, quantity=?, description=? WHERE id=?");
    $stmt->bind_param("sssisi", $_POST['production_date'], $_POST['production_type'], $_POST['chicken_type'], $_POST['quantity'], $_POST['description'], $_POST['update_id']);
    $stmt->execute();
    echo "<script>alert('Updated successfully'); location.href='';</script>";
    exit;
}

// INSERT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_id'])) {
    $stmt = $conn->prepare("INSERT INTO production (production_date, production_type, chicken_type, quantity, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $_POST['production_date'], $_POST['production_type'], $_POST['chicken_type'], $_POST['quantity'], $_POST['description']);
    $stmt->execute();
    echo "<script>alert('Saved'); location.href='';</script>";
    exit;
}

$result = $conn->query("SELECT * FROM production ORDER BY production_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Production Manager</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #eee; }
        .noprint { margin-top: 20px; }
        @media print {
            body * { visibility: hidden; }
            #printableTable, #printableTable * { visibility: visible; }
            #printableTable { position: absolute; left: 0; top: 0; width: 100%; }
            .noprint { display: none !important; }
        }
        input[type="text"], input[type="number"], input[type="date"], select {
            width: 100%;
            box-sizing: border-box;
        }
        button.deleteBtn {
            background: none;
            border: none;
            color: red;
            cursor: pointer;
            font-size: 1.2em;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h2>Add New Production</h2>
<form method="POST">
    <label>Date:</label>
    <input type="date" name="production_date" required><br><br>
    <label>Production Type:</label>
    <select name="production_type">
        <option value="chicken">Chicken</option>
        <option value="egg">Egg</option>
    </select><br><br>
    <label>Chicken Type:</label>
    <input type="text" name="chicken_type" required><br><br>
    <label>Quantity:</label>
    <input type="number" name="quantity" required><br><br>
    <label>Description:</label>
    <input type="text" name="description"><br><br>
    <button type="submit">Save</button>
</form>

<hr>

<h2>Production Records</h2>

<table id="printableTable">
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Chicken Type</th>
        <th>Quantity</th>
        <th>Description</th>
        <th class="noprint">Actions</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr id="row<?= $row['id'] ?>"
        data-production_date="<?= htmlspecialchars($row['production_date']) ?>"
        data-production_type="<?= htmlspecialchars($row['production_type']) ?>"
        data-chicken_type="<?= htmlspecialchars($row['chicken_type']) ?>"
        data-quantity="<?= htmlspecialchars($row['quantity']) ?>"
        data-description="<?= htmlspecialchars($row['description']) ?>"
    >
        <td class="prod-date"><?= htmlspecialchars($row['production_date']) ?></td>
        <td class="prod-type"><?= ucfirst(htmlspecialchars($row['production_type'])) ?></td>
        <td class="prod-chicken"><?= htmlspecialchars($row['chicken_type']) ?></td>
        <td class="prod-qty"><?= htmlspecialchars($row['quantity']) ?></td>
        <td class="prod-desc"><?= htmlspecialchars($row['description']) ?></td>
        <td class="noprint">
            <button type="button" onclick="editRow(<?= $row['id'] ?>)">✏️ Edit</button>
            <button type="button" class="deleteBtn" data-id="<?= $row['id'] ?>" title="Delete">🗑️</button>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<div class="noprint" style="margin-top: 15px;">
    <button type="button" onclick="window.print()">🖨️ Print Records</button>
</div>

<script>
function editRow(id) {
    const row = document.getElementById('row' + id);
    const date = row.querySelector('.prod-date').innerText;
    const type = row.querySelector('.prod-type').innerText.toLowerCase();
    const chicken = row.querySelector('.prod-chicken').innerText;
    const qty = row.querySelector('.prod-qty').innerText;
    const desc = row.querySelector('.prod-desc').innerText;
    row.innerHTML = `
        <td><input type="date" class="form-control" value="${date}" id="edit-date"></td>
        <td><select class="form-control" id="edit-type">
            <option value="chicken" ${type === 'chicken' ? 'selected' : ''}>Chicken</option>
            <option value="egg" ${type === 'egg' ? 'selected' : ''}>Egg</option>
        </select></td>
        <td><input type="text" class="form-control" value="${chicken}" id="edit-chicken"></td>
        <td><input type="number" class="form-control" value="${qty}" id="edit-qty"></td>
        <td><input type="text" class="form-control" value="${desc}" id="edit-desc"></td>
        <td class="noprint">
            <button type="button" class="btn btn-success btn-sm saveEditBtn">✔️ Save</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="location.reload()">❌ Cancel</button>
        </td>
    `;
    row.querySelector(".saveEditBtn").addEventListener("click", function(){
        const newDate = row.querySelector('#edit-date').value;
        const newType = row.querySelector('#edit-type').value;
        const newChicken = row.querySelector('#edit-chicken').value;
        const newQty = row.querySelector('#edit-qty').value;
        const newDesc = row.querySelector('#edit-desc').value;
        const formData = new FormData();
        formData.append('update_id', id);
        formData.append('production_date', newDate);
        formData.append('production_type', newType);
        formData.append('chicken_type', newChicken);
        formData.append('quantity', newQty);
        formData.append('description', newDesc);
        fetch('production.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(html => {
            const toast = document.createElement('div');
            toast.className = 'alert alert-success';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = 9999;
            toast.innerText = 'Production updated successfully!';
            document.body.appendChild(toast);
            setTimeout(() => { toast.remove(); location.reload(); }, 1200);
        })
        .catch(() => {
            const toast = document.createElement('div');
            toast.className = 'alert alert-danger';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = 9999;
            toast.innerText = 'Failed to update production!';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        });
    });
}

$(document).ready(function(){
    $('.deleteBtn').click(function(){
        if (!confirm('Are you sure you want to delete this record?')) return;

        const id = $(this).data('id');
        const row = $('#row' + id);

        $.ajax({
            url: window.location.href.split('?')[0],
            type: 'GET',
            data: { delete_id: id, ajax: 1 },
            success: function() {
                alert('Record deleted');
                row.remove();
            },
            error: function() {
                alert('Error deleting record');
            }
        });
    });
});
</script>

</body>
</html>

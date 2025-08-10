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

<form method="POST" id="updateForm">
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
            <td>
                <span><?= htmlspecialchars($row['production_date']) ?></span>
                <input type="date" name="production_date" value="<?= htmlspecialchars($row['production_date']) ?>" style="display:none;">
            </td>
            <td>
                <span><?= ucfirst(htmlspecialchars($row['production_type'])) ?></span>
                <select name="production_type" style="display:none;">
                    <option value="chicken" <?= $row['production_type'] == 'chicken' ? 'selected' : '' ?>>Chicken</option>
                    <option value="egg" <?= $row['production_type'] == 'egg' ? 'selected' : '' ?>>Egg</option>
                </select>
            </td>
            <td>
                <span><?= htmlspecialchars($row['chicken_type']) ?></span>
                <input type="text" name="chicken_type" value="<?= htmlspecialchars($row['chicken_type']) ?>" style="display:none;">
            </td>
            <td>
                <span><?= htmlspecialchars($row['quantity']) ?></span>
                <input type="number" name="quantity" value="<?= htmlspecialchars($row['quantity']) ?>" style="display:none;">
            </td>
            <td>
                <span><?= htmlspecialchars($row['description']) ?></span>
                <input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>" style="display:none;">
            </td>
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

    <input type="hidden" name="update_id" id="update_id">
    <div class="noprint" style="margin-top: 10px;">
        <button type="submit" style="display:none;" id="saveBtn">✅ Save Update</button>
        <button type="button" onclick="cancelEdit()" style="display:none;" id="cancelBtn">❌ Cancel</button>
    </div>
</form>

<script>
function editRow(id) {
    const row = document.getElementById('row' + id);
    // Hide spans, show inputs
    const spans = row.querySelectorAll('span');
    const inputs = row.querySelectorAll('input, select');
    spans.forEach(s => s.style.display = 'none');
    inputs.forEach(i => i.style.display = 'inline');

    document.getElementById('saveBtn').style.display = 'inline';
    document.getElementById('cancelBtn').style.display = 'inline';
    document.getElementById('update_id').value = id;
}

function cancelEdit() {
    const id = document.getElementById('update_id').value;
    if (!id) return; // no edit ongoing

    const row = document.getElementById('row' + id);

    // Restore values from data attributes
    const original = {
        production_date: row.getAttribute('data-production_date'),
        production_type: row.getAttribute('data-production_type'),
        chicken_type: row.getAttribute('data-chicken_type'),
        quantity: row.getAttribute('data-quantity'),
        description: row.getAttribute('data-description'),
    };

    // Update input values & spans to original
    row.querySelector('input[name="production_date"]').value = original.production_date;
    row.querySelector('select[name="production_type"]').value = original.production_type;
    row.querySelector('input[name="chicken_type"]').value = original.chicken_type;
    row.querySelector('input[name="quantity"]').value = original.quantity;
    row.querySelector('input[name="description"]').value = original.description;

    row.querySelector('span:nth-child(1)').textContent = original.production_date;
    row.querySelector('td:nth-child(2) span').textContent = capitalize(original.production_type);
    row.querySelector('td:nth-child(3) span').textContent = original.chicken_type;
    row.querySelector('td:nth-child(4) span').textContent = original.quantity;
    row.querySelector('td:nth-child(5) span').textContent = original.description;

    // Show spans, hide inputs
    const spans = row.querySelectorAll('span');
    const inputs = row.querySelectorAll('input, select');
    spans.forEach(s => s.style.display = 'inline');
    inputs.forEach(i => i.style.display = 'none');

    // Hide save and cancel buttons, clear update_id
    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('cancelBtn').style.display = 'none';
    document.getElementById('update_id').value = '';
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
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

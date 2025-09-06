<?php
// Debugging: Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../classes/DBConnection.php';
$db = new DBConnection();
$conn = $db->conn;
if (!$conn) {
    die('DB Connection failed: ' . mysqli_connect_error());
}
$users = $conn->query("SELECT * FROM users ORDER BY date_added DESC");
if (!$users) {
    die('Query failed: ' . $conn->error);
}
$clients = $conn->query("SELECT * FROM clients ORDER BY date_created DESC");
if (!$clients) {
    die('Query failed: ' . $conn->error);
}
?>
<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">User Management</h3>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="userTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" id="users-tab" data-toggle="tab" href="#users" role="tab" aria-controls="users"
                    aria-selected="false">Staff/Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" id="clients-tab" data-toggle="tab" href="#clients" role="tab"
                    aria-controls="clients" aria-selected="true">Clients</a>
            </li>
        </ul>
        <div class="tab-content mt-3" id="userTabsContent">
            <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="mb-2 text-right">
                    <!-- Hide Create User button for staff/users -->
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Username</th>
                                <th>Type</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($row = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['firstname']) ?></td>
                                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= $row['type'] == 1 ? 'Admin' : 'User' ?></td>
                                    <td><?= $row['date_added'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info editUserBtn" data-id="<?= $row['id'] ?>"
                                            data-json='<?= json_encode($row) ?>'><i class="fa fa-edit"></i> Edit</button>
                                        <!-- Hide Delete for users -->
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade show active" id="clients" role="tabpanel" aria-labelledby="clients-tab">
                <div class="mb-2 text-right">
                    <button class="btn btn-primary btn-sm" id="createClientBtn"><i class="fa fa-plus"></i> Create
                        Client</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Gender</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($row = $clients->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['firstname']) ?></td>
                                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['contact']) ?></td>
                                    <td><?= htmlspecialchars($row['gender']) ?></td>
                                    <td><?= $row['date_created'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info editClientBtn" data-id="<?= $row['id'] ?>"
                                            data-json='<?= json_encode($row) ?>'><i class="fa fa-edit"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger deleteClientBtn" data-id="<?= $row['id'] ?>"
                                            data-email="<?= htmlspecialchars($row['email']) ?>"><i class="fa fa-trash"></i>
                                            Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- User/Client Modal Placeholder -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">User/Client Form</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form will be loaded here via JS -->
            </div>
        </div>
    </div>
</div>
<script>
    function getUserForm(data = {}) {
        return `
    <form id="userForm">
        <input type="hidden" name="id" value="${data.id || ''}">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="firstname" class="form-control" value="${data.firstname || ''}" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lastname" class="form-control" value="${data.lastname || ''}" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="${data.username || ''}" required autocomplete="off">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" value="" autocomplete="off" ${data.id ? '' : 'required'}>
            <small><i>${data.id ? 'Leave blank to keep current password.' : 'Set an initial password.'}</i></small>
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="0" ${data.type == 0 ? 'selected' : ''}>User</option>
                <option value="1" ${data.type == 1 ? 'selected' : ''}>Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label>Avatar</label>
            <input type="file" name="img" class="form-control-file">
        </div>
        <button type="submit" class="btn btn-primary">${data.id ? 'Update' : 'Create'} User</button>
    </form>
    `;
    }

    function getClientForm(data = {}) {
        return `
    <form id="clientForm">
        <input type="hidden" name="id" value="${data.id || ''}">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="firstname" class="form-control" value="${data.firstname || ''}" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="lastname" class="form-control" value="${data.lastname || ''}" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="${data.email || ''}" required autocomplete="off">
        </div>
        <div class="form-group">
            <label>Contact</label>
            <input type="text" name="contact" class="form-control" value="${data.contact || ''}" required>
        </div>
        <div class="form-group">
            <label>Gender</label>
            <select name="gender" class="form-control" required>
                <option value="" disabled ${!data.gender ? 'selected' : ''}>Select Gender</option>
                <option value="Male" ${data.gender == 'Male' ? 'selected' : ''}>Male</option>
                <option value="Female" ${data.gender == 'Female' ? 'selected' : ''}>Female</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" value="" autocomplete="off" ${data.id ? '' : 'required'}>
            <small><i>${data.id ? 'Leave blank to keep current password.' : 'Set an initial password.'}</i></small>
        </div>
        <button type="submit" class="btn btn-primary">${data.id ? 'Update' : 'Create'} Client</button>
    </form>
    `;
    }
    $(document).ready(function () {
        $('#createUserBtn').on('click', function () {
            $('#userModalLabel').text('Create User');
            $('#userModal .modal-body').html(getUserForm());
            $('#userModal').modal('show');
        });
        $('.editUserBtn').on('click', function () {
            var data = $(this).data('json');
            $('#userModalLabel').text('Edit User');
            $('#userModal .modal-body').html(getUserForm(data));
            $('#userModal').modal('show');
        });
        $('#createClientBtn').on('click', function () {
            $('#userModalLabel').text('Create Client');
            $('#userModal .modal-body').html(getClientForm());
            $('#userModal').modal('show');
        });
        $('.editClientBtn').on('click', function () {
            var data = $(this).data('json');
            $('#userModalLabel').text('Edit Client');
            $('#userModal .modal-body').html(getClientForm(data));
            $('#userModal').modal('show');
        });

        // AJAX for user form
        $(document).on('submit', '#userForm', function (e) {
            e.preventDefault();
            var form = $(this)[0];
            var formData = new FormData(form);
            if (typeof start_loader === 'function') start_loader();
            $.ajax({
                url: '../classes/Users.php?f=save',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                success: function (resp) {
                    if (typeof end_loader === 'function') end_loader();
                    if (resp == 1) {
                        if (typeof alert_toast === 'function') alert_toast(
                            'User saved successfully!', 'success');
                        else alert('User saved successfully!');
                        setTimeout(function () {
                            location.reload();
                        }, 1200);
                    } else if (resp == 2) {
                        if (typeof alert_toast === 'function') alert_toast(
                            'Username already exists!', 'error');
                        else alert('Username already exists!');
                    } else {
                        if (typeof alert_toast === 'function') alert_toast('Error: ' + resp,
                            'error');
                        else alert('Error: ' + resp);
                    }
                },
                error: function (xhr) {
                    if (typeof end_loader === 'function') end_loader();
                    if (typeof alert_toast === 'function') alert_toast('AJAX error: ' + xhr
                        .status + ' ' + xhr.statusText, 'error');
                    else alert('AJAX error: ' + xhr.status + ' ' + xhr.statusText);
                }
            });
        });
        // AJAX for client form
        $(document).on('submit', '#clientForm', function (e) {
            e.preventDefault();
            var form = $(this)[0];
            var formData = new FormData(form);
            if (typeof start_loader === 'function') start_loader();
            $.ajax({
                url: '../classes/handler.php?f=save_client',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                success: function (resp) {
                    if (typeof end_loader === 'function') end_loader();
                    try {
                        var data = typeof resp === 'string' ? JSON.parse(resp) : resp;
                        if (data.status === 'success') {
                            if (typeof alert_toast === 'function') alert_toast(
                                'Client saved successfully!', 'success');
                            else alert('Client saved successfully!');
                            setTimeout(function () {
                                location.reload();
                            }, 1200);
                        } else {
                            if (typeof alert_toast === 'function') alert_toast('Error: ' + (data
                                .msg || resp), 'error');
                            else alert('Error: ' + (data.msg || resp));
                        }
                    } catch (e) {
                        if (typeof alert_toast === 'function') alert_toast('Error: ' + resp,
                            'error');
                        else alert('Error: ' + resp);
                    }
                },
                error: function (xhr) {
                    if (typeof end_loader === 'function') end_loader();
                    if (typeof alert_toast === 'function') alert_toast('AJAX error: ' + xhr
                        .status + ' ' + xhr.statusText, 'error');
                    else alert('AJAX error: ' + xhr.status + ' ' + xhr.statusText);
                }
            });
        });

        $('.deleteClientBtn').on('click', function () {
            var id = $(this).data('id');
            var email = $(this).data('email');
            if (confirm('Are you sure you want to delete client: ' + email + '?')) {
                if (typeof start_loader === 'function') start_loader();
                $.ajax({
                    url: '../classes/handler.php?f=delete_client',
                    data: {
                        id: id
                    },
                    method: 'POST',
                    success: function (resp) {
                        if (typeof end_loader === 'function') end_loader();
                        try {
                            var data = typeof resp === 'string' ? JSON.parse(resp) : resp;
                            if (data.status === 'success') {
                                if (typeof alert_toast === 'function') alert_toast(
                                    'Client deleted successfully!', 'success');
                                else alert('Client deleted successfully!');
                                setTimeout(function () {
                                    location.reload();
                                }, 1200);
                            } else {
                                if (typeof alert_toast === 'function') alert_toast('Error: ' + (
                                    data.msg || resp), 'error');
                                else alert('Error: ' + (data.msg || resp));
                            }
                        } catch (e) {
                            if (typeof alert_toast === 'function') alert_toast('Error: ' + resp,
                                'error');
                            else alert('Error: ' + resp);
                        }
                    },
                    error: function (xhr) {
                        if (typeof end_loader === 'function') end_loader();
                        if (typeof alert_toast === 'function') alert_toast('AJAX error: ' + xhr
                            .status + ' ' + xhr.statusText, 'error');
                        else alert('AJAX error: ' + xhr.status + ' ' + xhr.statusText);
                    }
                });
            }
        });
    });
</script>
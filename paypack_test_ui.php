<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Paypack API Test UI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin-top: 40px;
        }

        .output-area {
            background: #222;
            color: #0f0;
            font-family: monospace;
            padding: 1em;
            border-radius: 8px;
            min-height: 120px;
        }

        .spinner-border {
            width: 2rem;
            height: 2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4">Paypack API Test UI</h2>
        <form id="paypack-form" autocomplete="off">
            <div class="mb-3">
                <label for="mtn_number" class="form-label">MTN Number</label>
                <input type="text" class="form-control" id="mtn_number" placeholder="07XXXXXXXX" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount (RWF)</label>
                <input type="number" class="form-control" id="amount" placeholder="100" min="1" required>
            </div>
            <div class="mb-3">
                <button type="button" class="btn btn-primary" id="btn-auth">1. Authenticate</button>
                <button type="button" class="btn btn-success" id="btn-create">2. Create Payment</button>
                <button type="button" class="btn btn-info" id="btn-status">3. Check Status</button>
            </div>
            <div class="mb-3">
                <label for="paypack_ref" class="form-label">Paypack Ref (for status check)</label>
                <input type="text" class="form-control" id="paypack_ref"
                    placeholder="Paste ref here after payment creation">
            </div>
        </form>
        <div class="output-area mt-3" id="output">Ready.</div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        function showOutput(msg) {
            $("#output").text(msg);
        }
        function showOutputObj(obj) {
            $("#output").text(JSON.stringify(obj, null, 2));
        }

        $('#btn-auth').click(function () {
            showOutput('Authenticating...');
            $.get('paypack_test_api.php', { action: 'auth' }, function (resp) {
                showOutputObj(resp);
            }, 'json').fail(function (xhr) {
                showOutput('Auth error: ' + xhr.responseText);
            });
        });

        $('#btn-create').click(function () {
            var number = $('#mtn_number').val().trim();
            var amount = $('#amount').val().trim();
            if (!/^07\d{8}$/.test(number)) {
                showOutput('Invalid MTN number.');
                return;
            }
            if (!amount || amount < 1) {
                showOutput('Invalid amount.');
                return;
            }
            showOutput('Creating payment...');
            $.post('paypack_test_api.php', { action: 'create', number: number, amount: amount }, function (resp) {
                showOutputObj(resp);
                if (resp.ref) {
                    $('#paypack_ref').val(resp.ref);
                }
            }, 'json').fail(function (xhr) {
                showOutput('Create error: ' + xhr.responseText);
            });
        });

        $('#btn-status').click(function () {
            var ref = $('#paypack_ref').val().trim();
            var number = $('#mtn_number').val().trim();
            if (!ref) {
                showOutput('Please enter a Paypack ref.');
                return;
            }
            showOutput('Checking status...');
            $.post('paypack_test_api.php', { action: 'status', ref: ref, number: number }, function (resp) {
                showOutputObj(resp);
            }, 'json').fail(function (xhr) {
                showOutput('Status error: ' + xhr.responseText);
            });
        });
    </script>
</body>

</html>
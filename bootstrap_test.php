<?php require_once('config.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap Test - Smart Poultry Farm</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo base_url ?>plugins/bootstrap/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo base_url ?>plugins/fontawesome-free/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-check-circle"></i> Bootstrap Test Page</h3>
                    </div>
                    <div class="card-body">
                        <h4 class="text-success">✅ Bootstrap CSS is Working!</h4>
                        <p class="text-muted">If you can see this styled card with proper Bootstrap classes, then the
                            CSS is loading correctly.</p>

                        <hr>

                        <h5>Test Components:</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <button class="btn btn-primary">Primary Button</button>
                                <button class="btn btn-success">Success Button</button>
                                <button class="btn btn-warning">Warning Button</button>
                                <button class="btn btn-danger">Danger Button</button>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> This is an info alert!
                                </div>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> This is a success alert!
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>Form Test:</h5>
                        <form>
                            <div class="form-group">
                                <label for="testInput">Test Input</label>
                                <input type="text" class="form-control" id="testInput" placeholder="Enter text here">
                            </div>
                            <div class="form-group">
                                <label for="testSelect">Test Select</label>
                                <select class="form-control" id="testSelect">
                                    <option>Option 1</option>
                                    <option>Option 2</option>
                                    <option>Option 3</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="alert alert-warning">
                        <strong>Note:</strong> If you see unstyled HTML (no colors, no spacing, no Bootstrap styling),
                        then there's still an issue with Bootstrap CSS loading.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="<?php echo base_url ?>plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="<?php echo base_url ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
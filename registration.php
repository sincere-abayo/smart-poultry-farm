<style>
    #uni_modal .modal-content>.modal-footer,
    #uni_modal .modal-content>.modal-header {
        display: none;
    }

    .registration-container {
        padding: var(--spacing-xl);
        background: var(--white);
        border-radius: var(--radius-lg);
        max-width: 800px;
        margin: 0 auto;
    }

    .registration-header {
        text-align: center;
        margin-bottom: var(--spacing-xl);
    }

    .registration-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--black);
        margin-bottom: var(--spacing-sm);
    }

    .registration-subtitle {
        color: var(--medium-gray);
        font-size: 1rem;
    }

    .registration-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-xl);
    }

    .form-section {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-lg);
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-weight: 600;
        color: var(--dark-gray);
        margin-bottom: var(--spacing-sm);
    }

    .form-control {
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-md);
        padding: var(--spacing-md);
        font-size: 1rem;
        transition: all var(--transition-fast);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
        outline: none;
    }

    .form-control::placeholder {
        color: var(--medium-gray);
    }

    .custom-select {
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-md);
        padding: var(--spacing-md);
        font-size: 1rem;
        background: var(--white);
        transition: all var(--transition-fast);
    }

    .custom-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
        outline: none;
    }

    .registration-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: var(--spacing-xl);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--light-gray);
        flex-wrap: wrap;
        gap: var(--spacing-md);
    }

    .login-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: color var(--transition-fast);
    }

    .login-link:hover {
        color: var(--primary-dark);
        text-decoration: none;
    }

    .register-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: var(--white);
        border: none;
        border-radius: var(--radius-md);
        padding: var(--spacing-md) var(--spacing-xl);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        min-width: 140px;
    }

    .register-btn:hover {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .close-btn {
        position: absolute;
        top: var(--spacing-md);
        right: var(--spacing-md);
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--medium-gray);
        cursor: pointer;
        transition: color var(--transition-fast);
        z-index: 10;
    }

    .close-btn:hover {
        color: var(--dark-gray);
    }

    @media (max-width: 768px) {
        .registration-form {
            grid-template-columns: 1fr;
            gap: var(--spacing-lg);
        }

        .registration-container {
            padding: var(--spacing-lg);
        }

        .registration-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .register-btn {
            width: 100%;
        }
    }
</style>

<div class="registration-container">
    <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>

    <div class="registration-header">
        <h2 class="registration-title">Create Account</h2>
        <p class="registration-subtitle">Join us and start shopping for fresh poultry products</p>
    </div>

    <form action="" id="registration" class="registration-form">
        <div class="form-section">
            <div class="form-group">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" class="form-control" name="firstname" id="firstname" required
                    placeholder="Enter your first name">
            </div>

            <div class="form-group">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" class="form-control" name="lastname" id="lastname" required
                    placeholder="Enter your last name">
            </div>

            <div class="form-group">
                <label for="contact" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" name="contact" id="contact" required
                    placeholder="Enter your phone number">
            </div>

            <div class="form-group">
                <label for="gender" class="form-label">Gender</label>
                <select name="gender" id="gender" class="custom-select" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>

        <div class="form-section">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" id="email" required
                    placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required
                    placeholder="Create a password">
            </div>

            <div class="form-group">
                <label for="default_delivery_address" class="form-label">Default Delivery Address</label>
                <textarea class="form-control" name="default_delivery_address" id="default_delivery_address" rows="3"
                    placeholder="Enter your default delivery address"></textarea>
            </div>
        </div>

        <div class="registration-actions">
            <a href="javascript:void()" id="login-show" class="login-link">
                <i class="fas fa-sign-in-alt"></i> Already have an Account?
            </a>
            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#login-show').click(function () {
            uni_modal("", "login.php")
        })
        $('#registration').submit(function (e) {
            e.preventDefault();
            start_loader()
            if ($('.err-msg').length > 0)
                $('.err-msg').remove();
            $.ajax({
                url: _base_url_ + "classes/handler.php?f=register",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                error: err => {
                    console.log(err)
                    alert_toast("Registration failed: " + err.responseText, 'error')
                    end_loader()
                },
                success: function (resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        alert_toast("Account succesfully registered", 'success')
                        setTimeout(function () {
                            location.reload()
                        }, 2000)
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        var _err_el = $('<div>')
                        _err_el.addClass("alert alert-danger err-msg").text(resp.msg)
                        $('[name="password"]').after(_err_el)
                        end_loader()

                    } else {
                        console.log(resp)
                        alert_toast("an error occured", 'error')
                        end_loader()
                    }
                }
            })
        })

    })
</script>
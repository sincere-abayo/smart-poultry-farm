<style>
    #uni_modal .modal-content>.modal-footer,
    #uni_modal .modal-content>.modal-header {
        display: none;
    }

    .login-container {
        padding: var(--spacing-xl);
        background: var(--white);
        border-radius: var(--radius-lg);
    }

    .login-header {
        text-align: center;
        margin-bottom: var(--spacing-xl);
    }

    .login-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--black);
        margin-bottom: var(--spacing-sm);
    }

    .login-subtitle {
        color: var(--medium-gray);
        font-size: 1rem;
    }

    .login-form {
        max-width: 400px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: var(--spacing-lg);
    }

    .form-label {
        font-weight: 600;
        color: var(--dark-gray);
        margin-bottom: var(--spacing-sm);
        display: block;
    }

    .form-control {
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-md);
        padding: var(--spacing-md);
        font-size: 1rem;
        transition: all var(--transition-fast);
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
        outline: none;
    }

    .login-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: var(--spacing-xl);
        flex-wrap: wrap;
        gap: var(--spacing-md);
    }

    .create-account-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        transition: color var(--transition-fast);
    }

    .create-account-link:hover {
        color: var(--primary-dark);
        text-decoration: none;
    }

    .login-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: var(--white);
        border: none;
        border-radius: var(--radius-md);
        padding: var(--spacing-md) var(--spacing-xl);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        min-width: 120px;
    }

    .login-btn:hover {
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

    @media (max-width: 576px) {
        .login-container {
            padding: var(--spacing-lg);
        }

        .login-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .login-btn {
            width: 100%;
        }
    }
</style>

<div class="login-container">
    <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>

    <div class="login-header">
        <h2 class="login-title">Welcome Back</h2>
        <p class="login-subtitle">Sign in to your account to continue</p>
    </div>

    <form action="" id="login-form" class="login-form">
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" id="email" required placeholder="Enter your email">
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" required
                placeholder="Enter your password">
        </div>

        <div class="login-actions">
            <a href="javascript:void()" id="create_account" class="create-account-link">
                <i class="fas fa-user-plus"></i> Create Account
            </a>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $('#create_account').click(function () {
            uni_modal("", "registration.php", "mid-large")
        })
        $('#login-form').submit(function (e) {
            e.preventDefault();
            start_loader()
            if ($('.err-msg').length > 0)
                $('.err-msg').remove();
            $.ajax({
                url: _base_url_ + "classes/Login.php?f=login_user",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                error: err => {
                    console.log(err)
                    alert_toast("an error occured", 'error')
                    end_loader()
                },
                success: function (resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        alert_toast("Login Successfully", 'success')
                        setTimeout(function () {
                            location.reload()
                        }, 2000)
                    } else if (resp.status == 'incorrect') {
                        var _err_el = $('<div>')
                        _err_el.addClass("alert alert-danger err-msg").text("Incorrect Credentials.")
                        $('#login-form').prepend(_err_el)
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
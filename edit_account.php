<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['auth_user']) && !isset($_SESSION['userdata'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
?>
<style>
    .edit-account-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .edit-account-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        color: var(--white);
        padding: var(--spacing-xl) 0;
        border-radius: var(--radius-lg);
        text-align: center;
        margin-bottom: var(--spacing-xl);
    }

    .edit-account-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: var(--spacing-sm);
    }

    .edit-account-subtitle {
        font-size: 1rem;
        opacity: 0.9;
    }

    .edit-account-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
        flex-wrap: wrap;
        gap: var(--spacing-md);
    }

    .back-btn {
        background: linear-gradient(135deg, var(--secondary-color), var(--secondary-light));
        color: var(--white);
        border: none;
        border-radius: var(--radius-md);
        padding: var(--spacing-md) var(--spacing-lg);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-sm);
        transition: all var(--transition-fast);
    }

    .back-btn:hover {
        background: linear-gradient(135deg, var(--secondary-dark), var(--secondary-color));
        color: var(--white);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .edit-account-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-xxl);
    }

    .edit-account-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .form-section {
        margin-bottom: var(--spacing-xl);
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-sm);
        border-bottom: 2px solid var(--light-gray);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }

    .form-group {
        margin-bottom: var(--spacing-lg);
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: var(--black);
        margin-bottom: var(--spacing-sm);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        width: 100%;
        padding: var(--spacing-md);
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-md);
        font-size: 1rem;
        transition: all var(--transition-fast);
        background: var(--white);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
    }

    .custom-select {
        width: 100%;
        padding: var(--spacing-md);
        border: 2px solid var(--light-gray);
        border-radius: var(--radius-md);
        font-size: 1rem;
        background: var(--white);
        transition: all var(--transition-fast);
    }

    .custom-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: var(--spacing-md);
        margin-top: var(--spacing-xl);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--light-gray);
    }

    .update-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: var(--white);
        border: none;
        border-radius: var(--radius-md);
        padding: var(--spacing-md) var(--spacing-xl);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .update-btn:hover {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .cancel-btn {
        background: var(--light-gray);
        color: var(--text-color);
        border: none;
        border-radius: var(--radius-md);
        padding: var(--spacing-md) var(--spacing-xl);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .cancel-btn:hover {
        background: var(--medium-gray);
        color: var(--white);
        text-decoration: none;
    }

    .password-note {
        background: rgba(46, 125, 50, 0.1);
        border: 1px solid rgba(46, 125, 50, 0.2);
        border-radius: var(--radius-md);
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        font-size: 0.875rem;
        color: var(--primary-color);
    }

    .password-note i {
        margin-right: var(--spacing-sm);
    }

    @media (max-width: 768px) {
        .edit-account-actions {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .edit-account-card {
            padding: var(--spacing-lg);
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<section class="py-5">
    <div class="container edit-account-container">
        <div class="edit-account-header">
            <h1 class="edit-account-title">Update Account Details</h1>
            <p class="edit-account-subtitle">Manage your personal information and account settings</p>
        </div>

        <div class="edit-account-actions">
            <h2 class="section-title" style="margin: 0; border: none; padding: 0;">Account Settings</h2>
            <a href="./?p=my_account" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to My Account
            </a>
        </div>

        <div class="edit-account-card">
            <form action="" id="update_account" class="edit-account-form">
                <input type="hidden" name="id" value="<?php echo $_settings->userdata('id') ?>">

                <div class="form-section">
                    <h3 class="section-title">Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" name="firstname" class="form-control"
                                value="<?php echo $_settings->userdata('firstname') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control"
                                value="<?php echo $_settings->userdata('lastname') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact" class="form-label">Phone Number</label>
                            <input type="tel" name="contact" class="form-control"
                                value="<?php echo $_settings->userdata('contact') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender</label>
                            <select name="gender" class="custom-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo $_settings->userdata('gender') == "Male" ? "selected" : '' ?>>Male</option>
                                <option value="Female" <?php echo $_settings->userdata('gender') == "Female" ? "selected" : '' ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo $_settings->userdata('email') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="default_delivery_address" class="form-label">Default Delivery Address</label>
                        <textarea class="form-control" rows="3" name="default_delivery_address"
                            placeholder="Enter your default delivery address"><?php echo $_settings->userdata('default_delivery_address') ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Security Settings</h3>
                    <div class="password-note">
                        <i class="fas fa-info-circle"></i>
                        Leave password fields empty if you don't want to change your password
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="Enter new password">
                        </div>
                        <div class="form-group">
                            <label for="cpassword" class="form-label">Current Password</label>
                            <input type="password" name="cpassword" class="form-control"
                                placeholder="Enter current password">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="./?p=my_account" class="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="update-btn">
                        <i class="fas fa-save"></i> Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
    $(function () {
        $('#update_account [name="password"],#update_account [name="cpassword"]').on('input', function () {
            if ($('#update_account [name="password"]').val() != '' || $('#update_account [name="cpassword"]').val() != '')
                $('#update_account [name="password"],#update_account [name="cpassword"]').attr('required', true);
            else
                $('#update_account [name="password"],#update_account [name="cpassword"]').attr('required', false);
        })
        $('#update_account').submit(function (e) {
            e.preventDefault();
            start_loader()
            if ($('.err-msg').length > 0)
                $('.err-msg').remove();
            $.ajax({
                url: _base_url_ + "classes/handler.php?f=update_account",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                xhrFields: {
                    withCredentials: true
                },
                error: err => {
                    console.log(err)
                    var msg = err.responseJSON ? err.responseJSON.msg : "An error occurred";
                    alert_toast(msg, 'error')
                    end_loader()
                },
                success: function (resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        alert_toast("Account succesfully updated", 'success');
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else if (resp.status == 'failed' && !!resp.msg) {
                        var _err_el = $('<div>')
                        _err_el.addClass("alert alert-danger err-msg").text(resp.msg)
                        $('#update_account').prepend(_err_el)
                        $('body, html').animate({ scrollTop: 0 }, 'fast')
                        end_loader()

                    } else {
                        console.log(resp)
                        alert_toast("an error occured", 'error')
                    }
                    end_loader()
                }
            })
        })
    })
</script>
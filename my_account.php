<style>
    .account-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .account-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        color: var(--white);
        padding: var(--spacing-xxl) 0;
        margin-bottom: var(--spacing-xl);
        border-radius: var(--radius-lg);
        text-align: center;
    }

    .account-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: var(--spacing-sm);
    }

    .account-subtitle {
        font-size: 1.125rem;
        opacity: 0.9;
    }

    .account-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-xl);
        flex-wrap: wrap;
        gap: var(--spacing-md);
    }

    .orders-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--black);
        margin: 0;
    }

    .manage-account-btn {
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

    .manage-account-btn:hover {
        background: linear-gradient(135deg, var(--secondary-dark), var(--secondary-color));
        color: var(--white);
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .orders-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table thead th {
        background: var(--light-gray);
        padding: var(--spacing-lg) var(--spacing-md);
        text-align: left;
        font-weight: 600;
        color: var(--black);
        border-bottom: 2px solid var(--light-gray);
    }

    .orders-table tbody td {
        padding: var(--spacing-lg) var(--spacing-md);
        border-bottom: 1px solid var(--light-gray);
        vertical-align: middle;
    }

    .orders-table tbody tr:hover {
        background: rgba(46, 125, 50, 0.05);
    }

    .order-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
        font-family: monospace;
        font-size: 0.9rem;
    }

    .order-link:hover {
        color: var(--primary-dark);
        text-decoration: none;
    }

    .status-badge {
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: rgba(108, 117, 125, 0.1);
        color: var(--medium-gray);
    }

    .status-packed {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info);
    }

    .status-delivery {
        background: rgba(255, 193, 7, 0.1);
        color: #856404;
    }

    .status-delivered {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success);
    }

    .status-cancelled {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger);
    }

    .no-orders {
        text-align: center;
        padding: var(--spacing-xxl);
        color: var(--medium-gray);
    }

    .no-orders-icon {
        font-size: 3rem;
        color: var(--light-gray);
        margin-bottom: var(--spacing-lg);
    }

    .no-orders-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: var(--spacing-sm);
    }

    .no-orders-text {
        margin-bottom: var(--spacing-lg);
    }

    @media (max-width: 768px) {
        .account-actions {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }

        .orders-table {
            font-size: 0.875rem;
        }

        .orders-table thead th,
        .orders-table tbody td {
            padding: var(--spacing-md) var(--spacing-sm);
        }

        .account-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .orders-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
    }
</style>

<section class="py-5">
    <div class="container account-container">
        <div class="account-header">
            <h1 class="account-title">My Account</h1>
            <p class="account-subtitle">Manage your orders and account settings</p>
        </div>

        <div class="account-actions">
            <h2 class="orders-title">Order History</h2>
            <a href="./?p=edit_account" class="manage-account-btn">
                <i class="fas fa-user-cog"></i> Manage Account
            </a>
        </div>

        <div class="orders-card">
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date Ordered</th>
                            <th>Transaction ID</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $qry = $conn->query("SELECT o.*,concat(c.firstname,' ',c.lastname) as client from `orders` o inner join clients c on c.id = o.client_id where o.client_id = '" . $_settings->userdata('id') . "' order by unix_timestamp(o.date_created) desc ");
                        $has_orders = false;
                        while ($row = $qry->fetch_assoc()):
                            $has_orders = true;
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++ ?></td>
                                <td><?php echo date("M d, Y h:i A", strtotime($row['date_created'])) ?></td>
                                <td><a href="javascript:void(0)" class="order-link view_order"
                                        data-id="<?php echo $row['id'] ?>"><?php echo md5($row['id']); ?></a></td>
                                <td class="text-right">₱<?php echo number_format($row['amount'], 2) ?></td>
                                <td class="text-center">
                                    <?php if ($row['status'] == 0): ?>
                                        <span class="status-badge status-pending">Pending</span>
                                    <?php elseif ($row['status'] == 1): ?>
                                        <span class="status-badge status-packed">Packed</span>
                                    <?php elseif ($row['status'] == 2): ?>
                                        <span class="status-badge status-delivery">Out for Delivery</span>
                                    <?php elseif ($row['status'] == 3): ?>
                                        <span class="status-badge status-delivered">Delivered</span>
                                    <?php else: ?>
                                        <span class="status-badge status-cancelled">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (!$has_orders): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="no-orders">
                                        <div class="no-orders-icon">
                                            <i class="fas fa-shopping-bag"></i>
                                        </div>
                                        <h3 class="no-orders-title">No Orders Yet</h3>
                                        <p class="no-orders-text">You haven't placed any orders yet. Start shopping to see
                                            your order history here.</p>
                                        <a href="./" class="btn btn-primary">
                                            <i class="fas fa-shopping-cart"></i> Start Shopping
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<script>
    function cancel_book($id) {
        start_loader()
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=update_book_status",
            method: "POST",
            data: { id: $id, status: 2 },
            dataType: "json",
            error: err => {
                console.log(err)
                alert_toast("an error occured", 'error')
                end_loader()
            },
            success: function (resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    alert_toast("Book cancelled successfully", 'success')
                    setTimeout(function () {
                        location.reload()
                    }, 2000)
                } else {
                    console.log(resp)
                    alert_toast("an error occured", 'error')
                }
                end_loader()
            }
        })
    }
    $(function () {
        $('.view_order').click(function () {
            uni_modal("Order Details", "./admin/orders/view_order.php?view=user&id=" + $(this).attr('data-id'), 'large')
        })
        $('table').dataTable();

    })
</script>

<?php include 'inc/footer.php' ?>
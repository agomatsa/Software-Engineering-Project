<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/site_header.php';

$pageTitle = 'Checkout';

$cart_items = get_cart_items();
$cart_total = get_cart_total();

if (empty($cart_items)) {
    redirect('/cart.php');
}

$flash_error = get_flash('error');
?>

<section class="container" style="padding: 2rem 0;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h1>Checkout</h1>

        <?php if ($flash_error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <?= esc($flash_error) ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Order Form -->
            <div>
                <h2>Billing Information</h2>
                <form method="post" action="/process_order.php">
                    <?= csrf_field() ?>

                    <div style="margin-bottom: 1rem;">
                        <label for="customer_name" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Full Name *</label>
                        <input type="text" id="customer_name" name="customer_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="customer_email" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Email Address *</label>
                        <input type="email" id="customer_email" name="customer_email" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="customer_phone" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Phone Number</label>
                        <input type="tel" id="customer_phone" name="customer_phone" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;">
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="customer_address" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Delivery Address *</label>
                        <textarea id="customer_address" name="customer_address" required rows="4" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>
                    </div>

                    <button type="submit" style="width: 100%; background: #28a745; color: white; padding: 1rem; border: none; border-radius: 4px; font-size: 1rem; font-weight: bold; cursor: pointer;">Place Order</button>
                </form>
            </div>

            <!-- Order Summary -->
            <div>
                <h2>Order Summary</h2>
                <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 4px; border: 1px solid #ddd;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                            <div>
                                <strong><?= esc($item['make'] . ' ' . $item['model']) ?></strong> (<?= esc((string) $item['year']) ?>)<br>
                                <small style="color: #666;">Qty: <?= $item['quantity'] ?></small>
                            </div>
                            <div style="text-align: right;">
                                <strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="padding: 1rem 0; margin-top: 1rem; border-top: 2px solid #ddd; display: flex; justify-content: space-between; font-size: 1.2rem;">
                        <strong>Total:</strong>
                        <strong style="color: #28a745;">$<?= number_format($cart_total, 2) ?></strong>
                    </div>
                </div>

                <div style="background: #e7f3ff; padding: 1rem; border-radius: 4px; margin-top: 1rem; font-size: 0.9rem; color: #0056b3;">
                    <strong>Note:</strong> This is a demo ordering system. Orders are recorded for reference.
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

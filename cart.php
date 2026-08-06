<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cart.php';
require_once __DIR__ . '/includes/site_header.php';

$pageTitle = 'Shopping Cart';

$cart_items = get_cart_items();
$cart_total = get_cart_total();
$flash_success = get_flash('success');
?>

<section class="container" style="padding: 2rem 0;">
    <div style="max-width: 900px; margin: 0 auto;">
        <h1>Shopping Cart</h1>

        <?php if ($flash_success): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                <?= esc($flash_success) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <p style="text-align: center; padding: 2rem; color: #666;">Your cart is empty.</p>
            <div style="text-align: center;">
                <a href="/index.php" style="display: inline-block; background: #333; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #ddd;">
                        <th style="text-align: left; padding: 1rem;">Car</th>
                        <th style="text-align: center; padding: 1rem;">Quantity</th>
                        <th style="text-align: right; padding: 1rem;">Price</th>
                        <th style="text-align: right; padding: 1rem;">Total</th>
                        <th style="text-align: center; padding: 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 1rem;">
                                <strong><?= esc($item['make'] . ' ' . $item['model']) ?></strong><br>
                                <small style="color: #666;"><?= esc((string) $item['year']) ?></small>
                            </td>
                            <td style="text-align: center; padding: 1rem;">
                                <form method="post" action="/process_cart.php?action=update&car_id=<?= $item['id'] ?>" style="display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="10" style="width: 60px; padding: 0.5rem;">
                                    <button type="submit" style="padding: 0.5rem 1rem; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer;">Update</button>
                                </form>
                            </td>
                            <td style="text-align: right; padding: 1rem;">$<?= number_format($item['price'], 2) ?></td>
                            <td style="text-align: right; padding: 1rem;"><strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                            <td style="text-align: center; padding: 1rem;">
                                <a href="/process_cart.php?action=remove&car_id=<?= $item['id'] ?>" style="color: #d32f2f; text-decoration: none; font-weight: bold;">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; background: #f5f5f5; border-radius: 4px; margin-bottom: 2rem;">
                <div>
                    <h3 style="margin: 0;">Cart Total: <strong>$<?= number_format($cart_total, 2) ?></strong></h3>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="/process_cart.php?action=clear" style="display: inline-block; background: #999; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px;">Clear Cart</a>
                    <a href="/index.php" style="display: inline-block; background: #666; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px;">Continue Shopping</a>
                    <a href="/checkout.php" style="display: inline-block; background: #28a745; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; font-weight: bold;">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/site_footer.php'; ?>

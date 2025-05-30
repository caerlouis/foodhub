<?php
session_start();
$total = 0;
// ... (same PHP logic as before)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - Caerskie Foodhub</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body, *, *:before, *:after { box-sizing: border-box; }
        body { background: #f4f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, .15);
            padding: 32px 24px 24px 24px;
            overflow-x: auto;
        }
        h2 { text-align: center; margin-bottom: 24px; }
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .cart-table th, .cart-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #eaeaea;
            text-align: center;
            max-width: 140px;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .cart-table th { background: #eef2fb; letter-spacing: 0.03em;}
        .cart-table img { max-width: 70px; max-height: 70px; border-radius: 8px; }
        .cart-table td { vertical-align: middle; }
        .cart-table td:last-child, .cart-table th:last-child {
            width: 1%;
            white-space: nowrap;
        }
        .cart-actions, .cart-bottom {
            display: flex;
            gap: 14px;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 10px;
        }
        .cart-actions { margin-top: 12px; }
        .cart-total {
            font-size: 1.25em;
            font-weight: bold;
            text-align: right;
            margin-bottom: 18px;
        }
        .btn {
            padding: 8px 22px;
            border: none;
            border-radius: 6px;
            background: #4e54c8;
            color: #fff;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
            box-sizing: border-box;
            white-space: nowrap;
            width: auto;
            min-width: 80px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-remove {
            background: #f44336;
            margin: 0 auto;
            padding: 7px 12px;
        }
        .btn:hover, .btn:focus { background: #6367d6; }
        .btn-remove:hover, .btn-remove:focus { background: #c62828; }
        .btn-secondary {
            background: #bbb;
            color: #222;
        }
        .btn-secondary:hover, .btn-secondary:focus { background: #999; }
        .qty-input {
            width: 60px;
            padding: 6px;
            border: 1px solid #bbb;
            border-radius: 4px;
            text-align: center;
        }
        @media (max-width: 700px) {
            .container { padding: 12px 4px; }
            .cart-table th, .cart-table td { padding: 8px 4px; font-size: 0.96em; }
            .cart-actions, .cart-bottom { flex-direction: column; align-items: stretch; gap: 8px; }
            .cart-table img { max-width: 48px; max-height: 48px; }
            .btn, .btn-remove { padding: 6px 10px; font-size: 0.9em; min-width: 50px; }
        }
    </style>
</head>
<div id="loader-overlay" style="display:none;">
  <video id="loader-video" src="assets/transition.mp4" autoplay muted playsinline style="width: 1500px;; height:auto; border-radius:14px; box-shadow:0 2px 16px #3333;">
    Sorry, your browser doesn't support embedded videos.
  </video>
</div>
<body>
    <div class="container">
        <h2>Your Cart</h2>
        <?php if (empty($_SESSION['cart']) || !array_filter($_SESSION['cart'], 'is_array')): ?>
            <p style="text-align:center; color:#888; font-size:1.1em;">🛒 Your cart is empty.</p>
            <div style="text-align:center; margin-top:30px;">
                <a href="user_dashboard.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="post" action="cart.php" id="cart-form">
                <table class="cart-table" id="cart-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                        <?php if (!is_array($item)) continue; ?>
                        <tr data-id="<?php echo $id; ?>">
                            <td>
                                <?php if (!empty($item['image'])): ?>
                                     <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <span style="color:#bbb;">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td class="price" data-price="<?php echo $item['price']; ?>">₱<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number"
                                       name="quantities[<?php echo $id; ?>]"
                                       value="<?php echo $item['quantity']; ?>"
                                       min="1"
                                       class="qty-input"
                                       aria-label="Quantity for <?php echo htmlspecialchars($item['name']); ?>"
                                >
                            </td>
                            <td class="subtotal">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $id; ?>"
                                   class="btn btn-remove"
                                   title="Remove"
                                   onclick="return confirm('Remove this item?');">
                                    Remove
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-bottom">
                    <div class="cart-total" id="cart-total">
                        Total: ₱<?php echo number_format($total, 2); ?>
                    </div>
                </div>
                <div class="cart-actions">
                    <a href="user_dashboard.php" class="btn btn-secondary">Continue Shopping</a>
                  
                    <a href="checkout.php" class="btn" style="background: #43b77d;">Proceed to Checkout</a>
                </div>
            </form>
            <script>
                // JS for real-time subtotal and total update
                function formatPHP(num) {
                    return "₱" + Number(num).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function recalculateTotals() {
                    let total = 0;
                    document.querySelectorAll('#cart-table tbody tr').forEach(function(row) {
                        const price = parseFloat(row.querySelector('.price').getAttribute('data-price'));
                        const qtyInput = row.querySelector('.qty-input');
                        const qty = parseInt(qtyInput.value);
                        const subtotal = price * qty;
                        row.querySelector('.subtotal').textContent = formatPHP(subtotal);
                        total += subtotal;
                    });
                    document.getElementById('cart-total').textContent = "Total: " + formatPHP(total);
                }

                document.querySelectorAll('.qty-input').forEach(function(input) {
                    input.addEventListener('input', function() {
                        if (this.value < 1) this.value = 1;
                        recalculateTotals();
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    <script>
const loader = document.getElementById('loader-overlay');
const loaderVideo = document.getElementById('loader-video');

// Show loader on page load
loader.style.display = 'flex';
loaderVideo.currentTime = 0;
loaderVideo.play();

window.addEventListener('load', function() {
    setTimeout(function() {
        loader.style.opacity = '0';
        setTimeout(function(){
            loader.style.display = 'none';
            loader.style.opacity = '';
        }, 300);
    }, 1000); // Keep it visible for a short fade out
});

// Show loader on navigation
document.querySelectorAll('a').forEach(function(link){
    if(link.target === '' && link.getAttribute('href') && !link.getAttribute('href').startsWith('#') && !link.getAttribute('href').startsWith('javascript')) {
        link.addEventListener('click', function(e){
            loader.style.display = 'flex';
            loader.style.opacity = '1';
            loaderVideo.currentTime = 0;
            loaderVideo.play();
        });
    }
});
document.querySelectorAll('form').forEach(function(form){
    form.addEventListener('submit', function(){
        loader.style.display = 'flex';
        loader.style.opacity = '1';
        loaderVideo.currentTime = 0;
        loaderVideo.play();
    });
});
</script>
</body>
</html>
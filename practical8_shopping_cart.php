<?php
// ============================================================
// Practical 8 - Shopping Cart Application using PHP Sessions
// ============================================================
// To run: php -S localhost:8000 practical8_shopping_cart.php
// Then open: http://localhost:8000/practical8_shopping_cart.php
// ============================================================

session_start();

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ===== PRODUCT CATALOG =====
$products = [
    1 => ['id' => 1, 'name' => 'Laptop',       'price' => 45999, 'emoji' => '💻', 'stock' => 10],
    2 => ['id' => 2, 'name' => 'Smartphone',    'price' => 12999, 'emoji' => '📱', 'stock' => 25],
    3 => ['id' => 3, 'name' => 'Headphones',    'price' => 2499,  'emoji' => '🎧', 'stock' => 30],
    4 => ['id' => 4, 'name' => 'Keyboard',      'price' => 1899,  'emoji' => '⌨️',  'stock' => 20],
    5 => ['id' => 5, 'name' => 'Mouse',         'price' => 899,   'emoji' => '🖱️',  'stock' => 40],
    6 => ['id' => 6, 'name' => 'USB Hub',       'price' => 599,   'emoji' => '🔌', 'stock' => 50],
    7 => ['id' => 7, 'name' => 'Webcam',        'price' => 2299,  'emoji' => '📷', 'stock' => 15],
    8 => ['id' => 8, 'name' => 'Monitor Stand', 'price' => 1299,  'emoji' => '🖥️',  'stock' => 12],
];

$message = '';

// ===== HANDLE ACTIONS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // -- ADD TO CART --
    if ($action === 'add') {
        $pid = (int)$_POST['product_id'];
        $qty = max(1, (int)$_POST['quantity']);

        if (isset($products[$pid])) {
            if (isset($_SESSION['cart'][$pid])) {
                $_SESSION['cart'][$pid]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$pid] = [
                    'id'    => $pid,
                    'name'  => $products[$pid]['name'],
                    'price' => $products[$pid]['price'],
                    'emoji' => $products[$pid]['emoji'],
                    'qty'   => $qty,
                ];
            }
            $message = "✅ {$products[$pid]['name']} added to cart!";
        }
    }

    // -- REMOVE FROM CART --
    elseif ($action === 'remove') {
        $pid = (int)$_POST['product_id'];
        if (isset($_SESSION['cart'][$pid])) {
            $name = $_SESSION['cart'][$pid]['name'];
            unset($_SESSION['cart'][$pid]);
            $message = "🗑️ {$name} removed from cart.";
        }
    }

    // -- UPDATE QUANTITY --
    elseif ($action === 'update') {
        $pid = (int)$_POST['product_id'];
        $qty = (int)$_POST['quantity'];
        if ($qty <= 0) {
            unset($_SESSION['cart'][$pid]);
            $message = "Item removed from cart.";
        } else {
            $_SESSION['cart'][$pid]['qty'] = $qty;
            $message = "✅ Quantity updated.";
        }
    }

    // -- CLEAR CART --
    elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
        $message = "🛒 Cart cleared!";
    }

    // -- CHECKOUT --
    elseif ($action === 'checkout') {
        if (!empty($_SESSION['cart'])) {
            $message = "🎉 Order placed successfully! Thank you for shopping.";
            $_SESSION['cart'] = [];
        } else {
            $message = "⚠️ Cart is empty!";
        }
    }
}

// ===== CALCULATE TOTALS =====
$cartTotal = 0;
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['qty'];
    $cartCount += $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Practical 8 - Shopping Cart (PHP Sessions)</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f7fa;
      color: #333;
    }

    /* NAVBAR */
    .navbar {
      background: linear-gradient(135deg, #1a237e, #283593);
      color: white;
      padding: 14px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar .brand { font-size: 20px; font-weight: 700; }
    .cart-badge {
      background: #ff1744;
      color: white;
      border-radius: 20px;
      padding: 6px 16px;
      font-size: 14px;
      font-weight: 600;
    }

    /* LAYOUT */
    .layout {
      display: grid;
      grid-template-columns: 1fr 360px;
      gap: 24px;
      max-width: 1200px;
      margin: 24px auto;
      padding: 0 20px;
    }

    /* MESSAGE */
    .msg-bar {
      background: #e8f5e9;
      border-left: 4px solid #43a047;
      padding: 12px 20px;
      border-radius: 0 8px 8px 0;
      margin-bottom: 16px;
      font-size: 14px;
      color: #1b5e20;
      font-weight: 500;
    }

    /* PRODUCT GRID */
    h2 { font-size: 18px; color: #1a237e; margin-bottom: 14px; }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 16px;
    }

    .product-card {
      background: white;
      border-radius: 12px;
      padding: 20px 16px;
      text-align: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.07);
      transition: transform 0.15s, box-shadow 0.15s;
    }

    .product-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }

    .product-emoji { font-size: 42px; margin-bottom: 8px; }

    .product-name {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .product-price {
      font-size: 16px;
      font-weight: 700;
      color: #1a237e;
      margin-bottom: 12px;
    }

    .product-card form {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .qty-row {
      display: flex;
      align-items: center;
      gap: 8px;
      justify-content: center;
    }

    .qty-row label { font-size: 12px; color: #888; }
    .qty-row input {
      width: 55px;
      border: 1.5px solid #ddd;
      border-radius: 6px;
      padding: 5px 8px;
      font-size: 14px;
      text-align: center;
    }

    .add-btn {
      background: #1a237e;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 9px 14px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }

    .add-btn:hover { background: #283593; }

    /* CART SECTION */
    .cart-box {
      background: white;
      border-radius: 14px;
      padding: 20px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      position: sticky;
      top: 20px;
    }

    .cart-title {
      font-size: 17px;
      font-weight: 700;
      color: #1a237e;
      margin-bottom: 16px;
      border-bottom: 2px solid #e8eaf6;
      padding-bottom: 10px;
    }

    .cart-empty {
      text-align: center;
      color: #aaa;
      padding: 30px 0;
      font-size: 15px;
    }

    .cart-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .cart-item .item-emoji { font-size: 24px; }
    .cart-item .item-info  { flex: 1; }
    .cart-item .item-name  { font-size: 13px; font-weight: 600; }
    .cart-item .item-sub   { font-size: 12px; color: #888; }

    .cart-item .qty-input {
      width: 46px;
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 4px 6px;
      font-size: 13px;
      text-align: center;
    }

    .update-btn {
      background: #e8eaf6;
      border: none;
      border-radius: 5px;
      padding: 5px 9px;
      font-size: 12px;
      cursor: pointer;
      color: #1a237e;
      font-weight: 600;
    }

    .remove-btn {
      background: none;
      border: none;
      color: #e53935;
      font-size: 18px;
      cursor: pointer;
      padding: 2px 6px;
    }

    .cart-total {
      font-size: 17px;
      font-weight: 700;
      color: #1a237e;
      margin: 16px 0 10px;
      display: flex;
      justify-content: space-between;
    }

    .checkout-btn {
      width: 100%;
      background: linear-gradient(135deg, #e53935, #ff1744);
      color: white;
      border: none;
      border-radius: 10px;
      padding: 13px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      margin-bottom: 8px;
      transition: opacity 0.2s;
    }

    .checkout-btn:hover { opacity: 0.9; }

    .clear-btn {
      width: 100%;
      background: #f5f5f5;
      color: #888;
      border: none;
      border-radius: 8px;
      padding: 10px;
      font-size: 13px;
      cursor: pointer;
    }

    .clear-btn:hover { background: #eeeeee; }

    .session-info {
      margin-top: 12px;
      background: #e8f5e9;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 12px;
      color: #2e7d32;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
  <div class="brand">🛍️ TechShop</div>
  <div class="cart-badge">🛒 Cart: <?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?></div>
</div>

<!-- MAIN LAYOUT -->
<div class="layout">

  <!-- LEFT: PRODUCTS -->
  <div>
    <?php if ($message): ?>
      <div class="msg-bar"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <h2>🖥️ Tech Products</h2>

    <div class="product-grid">
      <?php foreach ($products as $p): ?>
        <div class="product-card">
          <div class="product-emoji"><?= $p['emoji'] ?></div>
          <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="product-price">₹<?= number_format($p['price']) ?></div>
          <form method="POST">
            <input type="hidden" name="action"     value="add" />
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>" />
            <div class="qty-row">
              <label>Qty</label>
              <input type="number" name="quantity" value="1" min="1" max="10" />
            </div>
            <button type="submit" class="add-btn">🛒 Add to Cart</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- RIGHT: CART -->
  <div>
    <div class="cart-box">
      <div class="cart-title">🛒 Your Cart</div>

      <?php if (empty($_SESSION['cart'])): ?>
        <div class="cart-empty">
          <div style="font-size:40px;margin-bottom:8px;">🛒</div>
          <p>Your cart is empty</p>
        </div>
      <?php else: ?>

        <?php foreach ($_SESSION['cart'] as $item): ?>
          <div class="cart-item">
            <div class="item-emoji"><?= $item['emoji'] ?></div>
            <div class="item-info">
              <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
              <div class="item-sub">₹<?= number_format($item['price']) ?> × <?= $item['qty'] ?></div>
            </div>

            <!-- Update Quantity -->
            <form method="POST" style="display:flex;align-items:center;gap:4px;">
              <input type="hidden" name="action"     value="update" />
              <input type="hidden" name="product_id" value="<?= $item['id'] ?>" />
              <input class="qty-input" type="number" name="quantity"
                     value="<?= $item['qty'] ?>" min="0" max="10" />
              <button class="update-btn" type="submit">✓</button>
            </form>

            <!-- Remove -->
            <form method="POST">
              <input type="hidden" name="action"     value="remove" />
              <input type="hidden" name="product_id" value="<?= $item['id'] ?>" />
              <button class="remove-btn" type="submit" title="Remove">✕</button>
            </form>
          </div>
        <?php endforeach; ?>

        <!-- Total -->
        <div class="cart-total">
          <span>Total:</span>
          <span>₹<?= number_format($cartTotal) ?></span>
        </div>

        <!-- Checkout -->
        <form method="POST">
          <input type="hidden" name="action" value="checkout" />
          <button class="checkout-btn" type="submit">✅ Place Order</button>
        </form>

        <!-- Clear -->
        <form method="POST">
          <input type="hidden" name="action" value="clear" />
          <button class="clear-btn" type="submit">🗑 Clear Cart</button>
        </form>

      <?php endif; ?>

      <!-- Session Info -->
      <div class="session-info">
        🔐 Session ID: <?= substr(session_id(), 0, 12) ?>...<br>
        Cart stored in: <strong>$_SESSION['cart']</strong>
      </div>
    </div>
  </div>

</div>
</body>
</html>

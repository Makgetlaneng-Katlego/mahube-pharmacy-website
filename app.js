let cart = [];

// ----- Helper: get user email -----
function getUserEmail() {
    // 1. Check hidden input from PHP (logged‑in user)
    const hiddenEmail = document.getElementById('userEmail');
    if (hiddenEmail && hiddenEmail.value) {
        return hiddenEmail.value;
    }
    // 2. Check localStorage for guest email
    const stored = localStorage.getItem('guestEmail');
    if (stored) {
        return stored;
    }
    // 3. Prompt the user (guest)
    const email = prompt('Please enter your email address to receive order confirmations:');
    if (email && email.includes('@') && email.includes('.')) {
        localStorage.setItem('guestEmail', email);
        return email;
    }
    return null;
}

// ----- Send email after adding item -----
function sendAddToCartEmail(productName, productPrice, quantity) {
    const email = getUserEmail();
    if (!email) {
        console.warn('No email provided – skipping notification.');
        return;
    }

    const payload = {
        email: email,
        product: {
            name: productName,
            price: productPrice,
            quantity: quantity
        }
    };

    fetch('send_cart_add_email.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log('📧 Email sent:', data.message);
        } else {
            console.warn('⚠️ Email not sent:', data.message);
        }
    })
    .catch(err => console.error('❌ Email error:', err));
}

// ----- Original functions (with email hook added) -----
function loadCart() {
    try {
        const raw = localStorage.getItem('mahubeCart');
        if (raw) {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                cart = parsed.filter(item => item && typeof item.name === 'string' && typeof item.qty === 'number' && item.qty > 0)
                    .map(item => ({
                        id: Number(item.id) || 0,
                        name: item.name,
                        price: Number(item.price) || 0,
                        img: (typeof item.img === 'string' && (item.img.startsWith('images/') || item.img.startsWith('http'))) ? item.img : 'images/logo.svg',
                        qty: Number(item.qty) || 1
                    }));
            }
        }
    } catch(e) { cart = []; }
    saveCart();
}

function saveCart() {
    localStorage.setItem('mahubeCart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const total = cart.reduce((sum, i) => sum + (i.qty || 0), 0);
    document.querySelectorAll('.cart-count').forEach(el => el.textContent = total);
}

// ----- MODIFIED: addToCart now sends email -----
function addToCart(id, name, price, img) {
    const existing = cart.find(i => i.id === id);
    let qty = 1;
    if (existing) {
        existing.qty++;
        qty = existing.qty;
    } else {
        cart.push({
            id: Number(id),
            name: name,
            price: Number(price),
            img: (img && typeof img === 'string' && img.includes('/')) ? img : 'images/logo.svg',
            qty: 1
        });
    }
    saveCart();
    showMiniCartNotice(name);
    if (document.getElementById('cartItems')) renderCart();

    // --- Send email notification (asynchronously) ---
    sendAddToCartEmail(name, Number(price), qty);
}

function showMiniCartNotice(name) {
    let notice = document.getElementById('cartNotice');
    if (!notice) {
        notice = document.createElement('div');
        notice.id = 'cartNotice';
        notice.style.cssText = 'position:fixed; right:20px; bottom:20px; background:#002f6c; color:#fff; padding:14px 18px; border-radius:8px; z-index:9999; box-shadow:0 8px 20px rgba(0,0,0,0.18);';
        document.body.appendChild(notice);
    }
    notice.textContent = name + ' added to cart';
    notice.style.display = 'block';
    setTimeout(() => notice.style.display = 'none', 1200);
}

function renderCart() {
    const box = document.getElementById('cartItems');
    if (!box) return;
    if (!cart.length) {
        box.innerHTML = '<div class="content-card"><h2>Your cart is empty</h2><p>Add products from the Products page.</p></div>';
        totals();
        return;
    }
    let html = '';
    cart.forEach((item, idx) => {
        html += `
            <div class="content-card cart-item">
                <img src="${item.img}" style="width:80px" onerror="this.src='images/logo.svg'">
                <div>
                    <h2>${escapeHtml(item.name)}</h2>
                    <p class="price">R${item.price.toFixed(2)}</p>
                    <a href="#" onclick="removeItem(${idx}); return false;">× Remove</a>
                </div>
                <div class="qty">
                    <button onclick="changeQty(${idx}, -1)">−</button>
                    <span>${item.qty}</span>
                    <button onclick="changeQty(${idx}, 1)">+</button>
                </div>
            </div>
        `;
    });
    box.innerHTML = html;
    totals();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function changeQty(idx, delta) {
    const newQty = cart[idx].qty + delta;
    if (newQty < 1) {
        cart.splice(idx, 1);
    } else {
        cart[idx].qty = newQty;
    }
    saveCart();
    renderCart();
}

function removeItem(idx) {
    cart.splice(idx, 1);
    saveCart();
    renderCart();
}

function totals() {
    const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    const fee = cart.length ? 20 : 0;
    const total = subtotal + fee;
    const subEl = document.getElementById('subtotal');
    const feeEl = document.getElementById('serviceFee');
    const totalEl = document.getElementById('total');
    if (subEl) subEl.textContent = 'R' + subtotal.toFixed(2);
    if (feeEl) feeEl.textContent = 'R' + fee.toFixed(2);
    if (totalEl) totalEl.textContent = 'R' + total.toFixed(2);
}

// ----- NEW: Handle payment method selection -----
function handleCheckout() {
    const method = document.querySelector('input[name="payment_method"]:checked');
    if (!method) {
        showCartMessage('Please select a payment method.', 'error');
        return;
    }
    if (method.value === 'stripe') {
        payWithStripe();
    } else {
        checkoutOrder();  // Cash on Delivery
    }
}

// ----- NEW: Stripe Checkout -----
async function payWithStripe() {
    if (!cart.length) {
        showCartMessage('Your cart is empty.', 'error');
        return;
    }
    const isLoggedIn = document.body.getAttribute('data-logged-in') === 'true';
    if (!isLoggedIn) {
        showCartMessage('Please <a href="login.php">login</a> to place an order.', 'error');
        return;
    }

    try {
        const response = await fetch('create_checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart: cart })
        });
        const result = await response.json();
        if (result.success && result.sessionId) {
            // Redirect to Stripe Checkout
            const stripe = Stripe('[YOUR_STRIPE_PUBLISHABLE_KEY]'); // 👈 Replace with your test/live key
            await stripe.redirectToCheckout({ sessionId: result.sessionId });
        } else {
            showCartMessage(result.message || 'Payment initiation failed.', 'error');
        }
    } catch (err) {
        showCartMessage('Network error: ' + err.message, 'error');
    }
}

// ----- Existing COD checkout (modified to use COD) -----
async function checkoutOrder() {
    if (!cart.length) {
        showCartMessage('Your cart is empty.', 'error');
        return;
    }
    const isLoggedIn = document.body.getAttribute('data-logged-in') === 'true';
    if (!isLoggedIn) {
        showCartMessage('Please <a href="login.php">login</a> to place an order.', 'error');
        return;
    }
    const orderData = {
        cart: cart.map(item => ({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: item.qty
        }))
    };
    try {
        const response = await fetch('place_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        const result = await response.json();
        if (result.success) {
            showCartMessage(`Order #${result.order_id} placed successfully!`, 'success');
            cart = [];
            saveCart();
            renderCart();
            const receiptBox = document.getElementById('receiptBox');
            if (receiptBox) {
                receiptBox.innerHTML = `<div class="receipt-success">✅ Thank you! Your order #${result.order_id} has been received.</div>`;
                receiptBox.classList.add('show');
            }
        } else {
            showCartMessage(result.message, 'error');
        }
    } catch (err) {
        showCartMessage('Network error: ' + err.message, 'error');
    }
}

function showCartMessage(msg, type) {
    const msgDiv = document.getElementById('cartMsg');
    if (!msgDiv) return;
    msgDiv.innerHTML = msg;
    msgDiv.className = `msg ${type} show`;
    setTimeout(() => msgDiv.classList.remove('show'), 5000);
}

function filterProducts(v) {
    const term = v.toLowerCase();
    document.querySelectorAll('.product').forEach(p => {
        const name = p.querySelector('h3')?.innerText.toLowerCase() || '';
        p.style.display = name.includes(term) ? '' : 'none';
    });
}
function preparePayFastCheckout() {
    const cart = JSON.parse(localStorage.getItem('mahubeCart') || '[]');
    if (cart.length === 0) {
        alert('Your cart is empty.');
        return false;
    }
    document.getElementById('pf_cart_data').value = JSON.stringify(cart);
    return true;
}
loadCart();


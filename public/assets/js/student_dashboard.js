// public/assets/js/student_dashboard.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.BASE || '';
  const addButtons = document.querySelectorAll('.btn-add');
  const cartItemsEl = document.getElementById('cart-items');
  const cartTotalEl = document.getElementById('cart-total');
  const checkoutBtn = document.getElementById('checkoutBtn');

  function refreshCart() {
    fetch(`${BASE}/api/cart_get.php`)
      .then(r => r.json())
      .then(data => {
        if (!data.items) return;
        cartItemsEl.innerHTML = '';
        let total = 0;
        data.items.forEach(it => {
          const div = document.createElement('div');
          div.className = 'cart-item';
          div.innerHTML = `<div>${it.name} x ${it.qty}</div><div>₱${parseFloat(it.subtotal).toFixed(2)}</div>`;
          cartItemsEl.appendChild(div);
          total += parseFloat(it.subtotal);
        });
        cartTotalEl.textContent = total.toFixed(2);
      });
  }

  addButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const name = btn.dataset.name;
      const price = btn.dataset.price;
      const qtyInput = btn.closest('.menu-body').querySelector('.qty');
      const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value||1)) : 1;
      fetch(`${BASE}/api/cart_add.php`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id, qty})
      }).then(r => r.json()).then(res => {
        if (res.success) {
          refreshCart();
          showPopup(`${name} added to cart`);
        } else {
          showPopup(res.error || 'Could not add to cart', true);
        }
      });
    });
  });

  checkoutBtn.addEventListener('click', () => {
    if (!confirm('Proceed to place order?')) return;
    fetch(`${BASE}/api/place_order.php`, {method:'POST'})
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          refreshCart();
          showPopup('Order placed! Order#: ' + res.order_number);
          // optionally redirect to orders page
        } else {
          showPopup(res.error || 'Checkout failed', true);
        }
      });
  });

  // categories filtering
  document.getElementById('categories').addEventListener('click', (e) => {
    const li = e.target.closest('li');
    if (!li) return;
    document.querySelectorAll('#categories li').forEach(n => n.classList.remove('active'));
    li.classList.add('active');
    const cat = li.dataset.cat;
    document.querySelectorAll('#menu-grid .menu-card').forEach(card => {
      if (cat === 'all' || card.dataset.category === cat) card.style.display = '';
      else card.style.display = 'none';
    });
  });

  function showPopup(msg, isError){
    const p = document.createElement('div');
    p.className = 'popup' + (isError ? ' error' : '');
    p.textContent = msg;
    document.body.appendChild(p);
    setTimeout(()=>p.style.opacity=1,30);
    setTimeout(()=>{ p.style.opacity=0; setTimeout(()=>p.remove(),300); }, 1800);
  }

  // initial load
  refreshCart();
});

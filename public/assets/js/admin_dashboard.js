// public/assets/js/admin_dashboard.js
document.addEventListener('DOMContentLoaded', () => {
  const BASE = window.BASE || '';
  // tab switching
  document.querySelectorAll('.admin-nav .tab').forEach(el => {
    el.addEventListener('click', () => {
      document.querySelectorAll('.admin-nav .tab').forEach(t => t.classList.remove('active'));
      el.classList.add('active');
      const tab = el.dataset.tab;
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
      document.getElementById('tab-' + tab).classList.add('active');
      if (tab === 'orders') loadOrders();
      if (tab === 'inventory') loadInventory();
    });
  });

  function loadOrders() {
    const container = document.getElementById('orders-table');
    container.innerText = 'Loading...';
    fetch(`${BASE}/admin/api/fetch_orders.php`).then(r=>r.json()).then(data=>{
      if (!Array.isArray(data)) { container.innerText = 'Error loading orders'; return; }
      const table = document.createElement('table');
      table.className = 'orders-table';
      table.innerHTML = `<thead><tr><th>Order#</th><th>User</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>`;
      const tbody = document.createElement('tbody');
      data.forEach(o=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${o.order_number}</td><td>${o.username}</td><td>₱${parseFloat(o.total_amount).toFixed(2)}</td>
          <td>${o.status}</td>
          <td>
            <select data-id="${o.id}" class="status-select">
              <option ${o.status==='pending'?'selected':''} value="pending">pending</option>
              <option ${o.status==='confirmed'?'selected':''} value="confirmed">confirmed</option>
              <option ${o.status==='preparing'?'selected':''} value="preparing">preparing</option>
              <option ${o.status==='ready'?'selected':''} value="ready">ready</option>
              <option ${o.status==='completed'?'selected':''} value="completed">completed</option>
              <option ${o.status==='cancelled'?'selected':''} value="cancelled">cancelled</option>
            </select>
          </td>`;
        tbody.appendChild(tr);
      });
      table.appendChild(tbody);
      container.innerHTML = '';
      container.appendChild(table);

      container.querySelectorAll('.status-select').forEach(sel=>{
        sel.addEventListener('change', ()=>{
          const id = sel.dataset.id; const val = sel.value;
          fetch(`${BASE}/admin/api/update_order_status.php`, {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({id, status: val})
          }).then(r=>r.json()).then(res=>{
            if (res.success) showPopup('Order updated');
            else showPopup(res.error || 'Update failed', true);
          });
        });
      });
    });
  }

  function loadInventory(){
    const container = document.getElementById('inventory-list');
    container.innerText = 'Loading...';
    fetch(`${BASE}/admin/api/fetch_inventory.php`).then(r=>r.json()).then(data=>{
      if (!Array.isArray(data)) { container.innerText = 'Error'; return; }
      const ul = document.createElement('ul');
      data.forEach(i=>{
        const li = document.createElement('li');
        li.textContent = `${i.name}: ${i.stock} ${i.unit} (threshold ${i.threshold})`;
        ul.appendChild(li);
      });
      container.innerHTML = '';
      container.appendChild(ul);
    });
  }

  function showPopup(msg, isError=false){
    const p = document.createElement('div');
    p.className = 'popup' + (isError ? ' error' : '');
    p.textContent = msg;
    document.body.appendChild(p);
    setTimeout(()=>p.style.opacity=1,30);
    setTimeout(()=>{ p.style.opacity=0; setTimeout(()=>p.remove(),300); }, 1500);
  }

  // initial load
  // show overview by default
});

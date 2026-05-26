<?php
/*
Template Name: Dashboard
*/
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        .navbar { background: #1a1a2e; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; }
        .navbar h1 { color: #fff; font-size: 20px; }
        .navbar span { color: #aaa; font-size: 14px; }
        .container { max-width: 1100px; margin: 32px auto; padding: 0 24px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .card h3 { font-size: 13px; color: #888; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .card .amount { font-size: 28px; font-weight: 700; }
        .card.green .amount { color: #22c55e; }
        .card.red .amount { color: #ef4444; }
        .card.blue .amount { color: #3b82f6; }
        .section { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 24px; }
        .section h2 { font-size: 16px; font-weight: 600; margin-bottom: 20px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 12px; }
        input, select { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        button { background: #1a1a2e; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-size: 14px; }
        button:hover { background: #16213e; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 10px 12px; font-size: 12px; color: #888; text-transform: uppercase; border-bottom: 1px solid #f0f0f0; }
        td { padding: 12px; font-size: 14px; border-bottom: 1px solid #f9f9f9; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge.in { background: #dcfce7; color: #16a34a; }
        .badge.out { background: #fee2e2; color: #dc2626; }
        .msg { padding: 10px; border-radius: 8px; margin-top: 10px; font-size: 14px; display: none; }
        .msg.success { background: #dcfce7; color: #16a34a; display: block; }
        .msg.error { background: #fee2e2; color: #dc2626; display: block; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>Expense Tracker</h1>
    <span id="current-date"></span>
</div>

<div class="container">

    <!-- Summary Cards -->
    <div class="cards" id="summary-cards">
        <div class="card blue"><h3>Total In</h3><div class="amount" id="total-in">...</div></div>
        <div class="card red"><h3>Total Out</h3><div class="amount" id="total-out">...</div></div>
    </div>

    <!-- Source Cards -->
    <div class="cards" id="source-cards"></div>

    <!-- Add Transaction -->
    <div class="section">
        <h2>Add Transaction</h2>
        <div class="form-row">
            <select id="source-select"><option value="">Select Source</option></select>
            <select id="type-select">
                <option value="in">Income</option>
                <option value="out">Expense</option>
            </select>
            <input type="number" id="amount-input" placeholder="Amount">
            <input type="date" id="date-input">
            <input type="text" id="note-input" placeholder="Note (optional)">
        </div>
        <button onclick="addTransaction()">Add</button>
        <div class="msg" id="form-msg"></div>
    </div>

    <!-- Recent Transactions -->
    <div class="section">
        <h2>Recent Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Source</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody id="transactions-body">
                <tr><td colspan="5">Loading...</td></tr>
            </tbody>
        </table>
    </div>

</div>

<script>
const API = '/api';

document.getElementById('current-date').textContent = new Date().toDateString();
document.getElementById('date-input').valueAsDate = new Date();

async function loadSummary() {
    const res  = await fetch(API + '/summary');
    const data = await res.json();
    document.getElementById('total-in').textContent  = '৳' + Number(data.overall_in).toLocaleString();
    document.getElementById('total-out').textContent = '৳' + Number(data.overall_out).toLocaleString();

    const container = document.getElementById('source-cards');
    container.innerHTML = '';
    data.sources.forEach(s => {
        if (s.total_in > 0 || s.total_out > 0) {
            container.innerHTML += `
                <div class="card">
                    <h3>${s.source}</h3>
                    <div class="amount">৳${Number(s.balance).toLocaleString()}</div>
                    <small style="color:#888">In: ৳${s.total_in} | Out: ৳${s.total_out}</small>
                </div>`;
        }
    });
}

async function loadSources() {
    const res  = await fetch(API + '/sources');
    const data = await res.json();
    const sel  = document.getElementById('source-select');
    data.forEach(s => {
        sel.innerHTML += `<option value="${s.id}">${s.name}</option>`;
    });
}

async function loadTransactions() {
    const res  = await fetch(API + '/transactions');
    const data = await res.json();
    const tbody = document.getElementById('transactions-body');
    if (!data.length) { tbody.innerHTML = '<tr><td colspan="5">No transactions yet.</td></tr>'; return; }
    tbody.innerHTML = data.map(t => `
        <tr>
            <td>${t.date}</td>
            <td>${t.source ? t.source.name : 'N/A'}</td>
            <td><span class="badge ${t.type}">${t.type === 'in' ? 'Income' : 'Expense'}</span></td>
            <td>৳${Number(t.amount).toLocaleString()}</td>
            <td>${t.note || 'N/A'}</td>
        </tr>`).join('');
}

async function addTransaction() {
    const msg = document.getElementById('form-msg');
    msg.className = 'msg';
    msg.textContent = '';

    const body = {
        source_id: document.getElementById('source-select').value,
        type:      document.getElementById('type-select').value,
        amount:    document.getElementById('amount-input').value,
        date:      document.getElementById('date-input').value,
        note:      document.getElementById('note-input').value,
    };

    if (!body.source_id || !body.amount || !body.date) {
        msg.className = 'msg error';
        msg.textContent = 'Source, amount, and date are required.';
        return;
    }

    const res = await fetch(API + '/transactions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(body)
    });

    if (res.ok) {
        msg.className = 'msg success';
        msg.textContent = 'Transaction added successfully.';
        document.getElementById('amount-input').value = '';
        document.getElementById('note-input').value = '';
        loadSummary();
        loadTransactions();
    } else {
        msg.className = 'msg error';
        msg.textContent = 'Something went wrong.';
    }
}

loadSummary();
loadSources();
loadTransactions();
</script>

</body>
</html>
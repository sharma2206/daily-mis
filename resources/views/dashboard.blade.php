<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIS Dashboard - Hospital MIS</title>
    <meta name="description" content="Branch-wise MIS reporting dashboard with FTD and MTD analytics">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        :root {
            --bg: #f0f4f8;
            --surface: #ffffff;
            --card: #ffffff;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --text: #1e293b;
            --text-secondary: #475569;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-light: #dbeafe;
            --primary-soft: #eff6ff;
            --accent: #7c3aed;
            --accent-light: #ede9fe;
            --success: #059669;
            --success-light: #d1fae5;
            --error: #dc2626;
            --error-light: #fee2e2;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0891b2;
            --info-light: #cffafe;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, .05);
            --shadow: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, .07), 0 2px 4px -2px rgba(0, 0, 0, .05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, .07), 0 4px 6px -4px rgba(0, 0, 0, .05);
            --radius: 12px;
            --radius-sm: 8px;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Navbar ─── */
        .nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: .6rem
        }

        .nav-brand .logo {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
        }

        .nav-brand h1 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text)
        }

        .nav-badge {
            font-size: .6rem;
            padding: .15rem .5rem;
            border-radius: 6px;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            gap: .35rem
        }

        .nav-link {
            padding: .4rem .85rem;
            border-radius: var(--radius-sm);
            font-size: .8rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            transition: all .2s;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .nav-link .material-icons-round {
            font-size: 16px
        }

        .nav-link:hover {
            color: var(--primary);
            background: var(--primary-soft)
        }

        .nav-link.active {
            color: var(--primary);
            background: var(--primary-light);
            border-color: rgba(37, 99, 235, .15);
            font-weight: 600;
        }

        /* ─── Main ─── */
        .main { width: 100%; padding: 1.25rem 2rem }

        /* ─── Controls ─── */
        .controls {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }

        .branch-pills {
            display: flex;
            gap: 4px;
            background: var(--bg);
            border-radius: 10px;
            padding: 3px;
            border: 1px solid var(--border);
        }

        .branch-pill {
            padding: .45rem 1.1rem;
            border-radius: var(--radius-sm);
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--muted);
            transition: all .2s;
            font-family: inherit;
        }

        .branch-pill.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, .3);
        }

        .branch-pill:hover:not(.active) {
            color: var(--text);
            background: var(--surface)
        }

        .date-control {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin-left: auto
        }

        .date-nav {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            transition: all .15s;
        }

        .date-nav:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft)
        }

        .date-input {
            padding: .4rem .7rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-family: inherit;
            font-size: .8rem;
            font-weight: 500;
        }

        .date-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1)
        }

        .btn-load {
            padding: .45rem 1.1rem;
            border-radius: var(--radius-sm);
            background: var(--primary);
            color: #fff;
            border: none;
            font-weight: 600;
            font-size: .8rem;
            cursor: pointer;
            font-family: inherit;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .btn-load:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 12px rgba(37, 99, 235, .25)
        }

        .btn-load:disabled {
            opacity: .5;
            cursor: not-allowed;
            box-shadow: none
        }

        /* ─── Summary Cards ─── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .summary-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.1rem 1.15rem;
            position: relative;
            overflow: hidden;
            transition: all .25s;
            box-shadow: var(--shadow-sm);
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .summary-card.sales::before {
            background: linear-gradient(90deg, #2563eb, #0891b2)
        }

        .summary-card.collection::before {
            background: linear-gradient(90deg, #059669, #10b981)
        }

        .summary-card.discount::before {
            background: linear-gradient(90deg, #d97706, #f59e0b)
        }

        .summary-card.refund::before {
            background: linear-gradient(90deg, #7c3aed, #a78bfa)
        }

        .summary-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px)
        }

        .sc-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: .7rem;
        }

        .summary-card.sales .sc-icon {
            background: var(--primary-light);
            color: var(--primary)
        }

        .summary-card.collection .sc-icon {
            background: var(--success-light);
            color: var(--success)
        }

        .summary-card.discount .sc-icon {
            background: var(--warning-light);
            color: var(--warning)
        }

        .summary-card.refund .sc-icon {
            background: var(--accent-light);
            color: var(--accent)
        }

        .sc-label {
            font-size: .68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            margin-bottom: .6rem;
        }

        .sc-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end
        }

        .sc-block {
            text-align: left
        }

        .sc-block:last-child {
            text-align: right
        }

        .sc-period {
            font-size: .58rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .1rem;
            font-weight: 600;
        }

        .sc-value {
            font-size: 1.25rem;
            font-weight: 800;
            line-height: 1.1
        }

        .sc-value.ftd {
            color: var(--primary)
        }

        .sc-value.mtd {
            color: var(--accent)
        }

        .sc-unit {
            font-size: .58rem;
            color: var(--muted);
            margin-top: .1rem
        }

        /* ─── Sections ─── */
        .section {
            margin-bottom: 1.25rem
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .6rem;
        }

        .section-title {
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .section-title .material-icons-round {
            font-size: 18px;
            color: var(--primary)
        }

        .section-title .sub {
            font-size: .65rem;
            color: var(--muted);
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0
        }

        .export-btns {
            display: flex;
            gap: .35rem
        }

        .btn-export {
            padding: .3rem .7rem;
            border-radius: 6px;
            font-size: .68rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            font-family: inherit;
            transition: all .15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }

        .btn-export:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft)
        }

        .btn-export.pdf:hover {
            border-color: var(--error);
            color: var(--error);
            background: var(--error-light)
        }

        /* ─── Table ─── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem
        }

        .tbl thead th {
            background: #f8fafc;
            padding: .55rem .65rem;
            font-weight: 600;
            font-size: .67rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--muted);
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .tbl thead th:first-child {
            text-align: left
        }

        .tbl thead .super-header th {
            font-size: .7rem;
            color: var(--text-secondary);
            padding: .45rem .5rem;
        }

        .tbl thead .super-header .ftd-h {
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 700;
        }

        .tbl thead .super-header .mtd-h {
            background: var(--accent-light);
            color: var(--accent);
            font-weight: 700;
        }

        .tbl tbody td {
            padding: .5rem .65rem;
            text-align: right;
            border-bottom: 1px solid var(--border-light);
            transition: background .12s;
            color: var(--text-secondary);
        }

        .tbl tbody td:first-child {
            text-align: left;
            font-weight: 600;
            color: var(--text);
        }

        .tbl tbody tr:hover td {
            background: #f8fafc
        }

        .tbl .total-col {
            font-weight: 700;
            color: var(--text)
        }

        .tbl .ftd-total {
            background: rgba(37, 99, 235, .04)
        }

        .tbl .mtd-total {
            background: rgba(124, 58, 237, .04)
        }

        .tbl tbody tr:last-child td {
            border-bottom: none
        }

        /* ─── Volume Grid ─── */
        .vol-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: .65rem
        }

        .vol-card.wide {
            grid-column: span 2;
            min-width: 0
        }

        .vol-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: .9rem;
            text-align: center;
            transition: all .2s;
            box-shadow: var(--shadow-sm);
        }

        .vol-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px)
        }

        .vol-label {
            font-size: .62rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            margin-bottom: .5rem;
        }

        .vol-values {
            display: flex;
            justify-content: center;
            gap: 1.5rem
        }

        .vol-item {
            min-width: 0
        }

        .vol-item .vol-period {
            font-size: .52rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 600;
        }

        .vol-item .vol-num {
            font-size: 1.2rem;
            font-weight: 800;
            margin-top: .08rem;
            white-space: nowrap
        }

        .vol-item .vol-num.sm {
            font-size: .95rem
        }

        .vol-item .vol-num.ftd {
            color: var(--primary)
        }

        .vol-item .vol-num.mtd {
            color: var(--accent)
        }

        /* ─── Package Alert ─── */
        .pkg-alert {
            background: var(--warning-light);
            border: 1px solid rgba(217, 119, 6, .2);
            border-radius: var(--radius);
            padding: .65rem 1rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            font-size: .78rem;
            color: var(--warning);
            margin-bottom: 1rem;
        }

        .pkg-alert .material-icons-round {
            font-size: 20px
        }

        .pkg-alert .pkg-vals {
            display: flex;
            gap: 1.25rem;
            margin-left: auto;
            font-weight: 700;
            font-size: .8rem;
        }

        /* ─── Loading ─── */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, .7);
            backdrop-filter: blur(4px);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.show {
            display: flex
        }

        .loader {
            width: 38px;
            height: 38px;
            border: 3px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .65s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        /* ─── No Data ─── */
        .no-data {
            text-align: center;
            padding: 3.5rem 1.5rem;
            color: var(--muted)
        }

        .no-data .nd-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto .9rem;
            font-size: 28px;
        }

        .no-data p {
            font-size: .88rem;
            color: var(--text-secondary)
        }

        .no-data .sub {
            font-size: .75rem;
            margin-top: .3rem;
            color: var(--muted)
        }

        /* ─── Alert ─── */
        .alert {
            padding: .65rem .9rem;
            border-radius: var(--radius-sm);
            margin-bottom: .75rem;
            font-size: .78rem;
            display: none;
            align-items: center;
            gap: .5rem;
        }

        .alert.show {
            display: flex
        }

        .alert-error {
            background: var(--error-light);
            border: 1px solid rgba(220, 38, 38, .2);
            color: var(--error)
        }

        .alert .close-a {
            cursor: pointer;
            margin-left: auto;
            opacity: .7;
            font-size: 1rem
        }

        /* ─── Responsive ─── */
        @media(max-width:1024px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media(max-width:768px) {
            .controls {
                flex-direction: column;
                align-items: stretch
            }

            .date-control {
                margin-left: 0;
                flex-wrap: wrap
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr)
            }

            .vol-grid {
                grid-template-columns: repeat(2, 1fr)
            }

            .main {
                padding: 1rem
            }

            .nav {
                padding: 0 1rem
            }

            .tbl {
                font-size: .72rem
            }
        }

        @media(max-width:480px) {
            .summary-grid {
                grid-template-columns: 1fr
            }

            .vol-grid {
                grid-template-columns: 1fr 1fr
            }

            .branch-pills {
                width: 100%
            }

            .branch-pill {
                flex: 1;
                text-align: center
            }
        }
    </style>
</head>

<body>
    <div class="nav">
        <div class="nav-brand">
            <div class="logo"><span class="material-icons-round">local_hospital</span></div>
            <h1>Hospital MIS</h1>
            <span class="nav-badge">Dashboard</span>
        </div>
        <div class="nav-links">
            <a href="/dashboard" class="nav-link active">
                <span class="material-icons-round">bar_chart</span> Dashboard
            </a>
            <a href="/" class="nav-link">
                <span class="material-icons-round">upload_file</span> Upload
            </a>
        </div>
    </div>

    <div class="main">
        <div id="alertBox" class="alert alert-error">
            <span class="material-icons-round" style="font-size:18px">error_outline</span>
            <span id="alertMsg"></span>
            <span class="close-a" onclick="this.parentElement.classList.remove('show')">&times;</span>
        </div>

        <div class="controls">
            <div class="branch-pills">
                <button class="branch-pill active" data-branch="chromepet"
                    onclick="switchBranch('chromepet')">Chromepet</button>
                <button class="branch-pill" data-branch="oragadam" onclick="switchBranch('oragadam')">Oragadam</button>
            </div>
            <div class="date-control">
                <button class="date-nav" onclick="shiftDate(-1)" title="Previous day">
                    <span class="material-icons-round" style="font-size:16px">chevron_left</span>
                </button>
                <input type="date" class="date-input" id="reportDate">
                <button class="date-nav" onclick="shiftDate(1)" title="Next day">
                    <span class="material-icons-round" style="font-size:16px">chevron_right</span>
                </button>
                <button class="btn-load" id="loadBtn" onclick="loadReport()">
                    <span class="material-icons-round" style="font-size:16px">refresh</span>
                    <span id="loadText">Load Report</span>
                </button>
            </div>
        </div>

        <div id="content">
            <div class="no-data" id="noData">
                <div class="nd-icon"><span class="material-icons-round">analytics</span></div>
                <p>Select a branch and date to view MIS report</p>
                <p class="sub">Upload CSV files first if no data exists for the selected date</p>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
    </div>

    <script>
        const BED = {
            chromepet: 74,
            oragadam: 14
        };
        let branch = 'chromepet',
            reportData = null;

        const di = document.getElementById('reportDate');
        di.max = new Date().toISOString().split('T')[0];
        di.value = di.max;

        function switchBranch(b) {
            branch = b;
            document.querySelectorAll('.branch-pill').forEach(p => p.classList.toggle('active', p.dataset.branch === b));
            if (reportData) loadReport();
        }

        function shiftDate(d) {
            const dt = new Date(di.value);
            dt.setDate(dt.getDate() + d);
            const s = dt.toISOString().split('T')[0];
            if (s <= di.max) {
                di.value = s;
                loadReport();
            }
        }

        async function loadReport() {
            const date = di.value;
            if (!date) return;
            const btn = document.getElementById('loadBtn'),
                lt = document.getElementById('loadText'),
                lo = document.getElementById('loadingOverlay');
            btn.disabled = true;
            lt.textContent = 'Loading...';
            lo.classList.add('show');
            document.getElementById('alertBox').classList.remove('show');
            try {
                const r = await fetch(`/api/mis/${branch}/${date}`);
                const j = await r.json();
                if (j.success) {
                    reportData = j.data;
                    renderReport(j.data);
                } else {
                    showError(j.message || 'No data found');
                    document.getElementById('content').innerHTML =
                        `<div class="no-data"><div class="nd-icon"><span class="material-icons-round">warning</span></div><p>${j.message || 'No data for this date'}</p><p class="sub">Try uploading CSV files for this date first</p></div>`;
                }
            } catch (e) {
                showError('Network error: ' + e.message);
            } finally {
                btn.disabled = false;
                lt.textContent = 'Load Report';
                lo.classList.remove('show');
            }
        }

        function showError(m) {
            const a = document.getElementById('alertBox');
            document.getElementById('alertMsg').textContent = m;
            a.classList.add('show');
        }

        function lk(v) {
            return ((v || 0) / 100000).toFixed(2);
        }

        function fmtRupee(v) {
            return '₹' + Math.round(Number(v || 0));
        }

        function renderReport(d) {
            const s = d.sales || {},
                c = d.collection || {},
                dc = d.discount || {},
                r = d.refund || {},
                v = d.volume || {},
                m = d.mri || {},
                t = d.totals || {},
                pkg = d.pkg_adjustment || {
                    ftd: 0,
                    mtd: 0
                };
            const date = d.date || di.value;

            let html = `
    <div class="summary-grid">
        <div class="summary-card sales">
            <div class="sc-icon"><span class="material-icons-round">payments</span></div>
            <div class="sc-label">Total Sales</div>
            <div class="sc-row">
                <div class="sc-block"><div class="sc-period">FTD</div><div class="sc-value ftd">₹${lk(t.sales_ftd)}</div><div class="sc-unit">Lakhs</div></div>
                <div class="sc-block"><div class="sc-period">MTD</div><div class="sc-value mtd">₹${lk(t.sales_mtd)}</div><div class="sc-unit">Lakhs</div></div>
            </div>
        </div>
        <div class="summary-card collection">
            <div class="sc-icon"><span class="material-icons-round">account_balance</span></div>
            <div class="sc-label">Collection</div>
            <div class="sc-row">
                <div class="sc-block"><div class="sc-period">FTD</div><div class="sc-value ftd">₹${lk(t.collection_ftd)}</div><div class="sc-unit">Lakhs</div></div>
                <div class="sc-block"><div class="sc-period">MTD</div><div class="sc-value mtd">₹${lk(t.collection_mtd)}</div><div class="sc-unit">Lakhs</div></div>
            </div>
        </div>
        <div class="summary-card discount">
            <div class="sc-icon"><span class="material-icons-round">hotel</span></div>
            <div class="sc-label">Occupancy</div>
            <div class="sc-row">
                <div class="sc-block"><div class="sc-period">FTD</div><div class="sc-value ftd">${v.ftd?.occupancy||0}</div><div class="sc-unit">of ${BED[branch]} beds</div></div>
                <div class="sc-block"><div class="sc-period">FTD %</div><div class="sc-value mtd">${Number(v.ftd?.occupancy_pct||0).toFixed(1)}%</div><div class="sc-unit">Occupancy</div></div>
            </div>
        </div>
        <div class="summary-card refund">
            <div class="sc-icon"><span class="material-icons-round">person_add</span></div>
            <div class="sc-label">Admissions</div>
            <div class="sc-row">
                <div class="sc-block"><div class="sc-period">FTD</div><div class="sc-value ftd">${v.ftd?.admission||0}</div><div class="sc-unit">Admitted</div></div>
                <div class="sc-block"><div class="sc-period">MTD</div><div class="sc-value mtd">${v.mtd?.admission||0}</div><div class="sc-unit">Total</div></div>
            </div>
        </div>
    </div>`;

            // Package alert
            if ((pkg.ftd || 0) > 0 || (pkg.mtd || 0) > 0) {
                html +=
                    `<div class="pkg-alert"><span class="material-icons-round">inventory_2</span><span>Package Adjustment (Chromepet): Added to Pharmacy, subtracted from IP</span><div class="pkg-vals"><span>FTD: ₹${lk(pkg.ftd)} L</span><span>MTD: ₹${lk(pkg.mtd)} L</span></div></div>`;
            }

            // Branch-specific columns
            const isChromepet = branch === 'chromepet';
            const isOragadam = branch === 'oragadam';
            const revCols = ['op', 'ip', 'er', 'ph'];
            const colSpan = revCols.length + 1;

            // Revenue table
            html +=
                `<div class="section"><div class="section-head"><div class="section-title"><span class="material-icons-round">table_chart</span> Revenue Breakdown <span class="sub">(₹ in Lakhs)</span></div><div class="export-btns"><a class="btn-export" href="/api/mis/${branch}/${date}/export" target="_blank"><span class="material-icons-round" style="font-size:14px">download</span> Excel</a><a class="btn-export pdf" href="/api/mis/${branch}/${date}/export-pdf" target="_blank"><span class="material-icons-round" style="font-size:14px">picture_as_pdf</span> PDF</a></div></div>`;
            html +=
                `<div class="card"><table class="tbl"><thead><tr class="super-header"><th></th><th colspan="${colSpan}" class="ftd-h">FTD (${date})</th><th colspan="${colSpan}" class="mtd-h">MTD</th></tr><tr><th>Category</th>`;
            revCols.forEach(k => html += `<th>${k.toUpperCase()}</th>`);
            html += `<th>Total</th>`;
            revCols.forEach(k => html += `<th>${k.toUpperCase()}</th>`);
            html += `<th>Total</th></tr></thead><tbody>`;

            const rows = [{
                    label: 'Sales',
                    d: s,
                    k: revCols
                },
                {
                    label: 'Collection',
                    d: c,
                    k: revCols
                },
            ];
            rows.forEach(rw => {
                const ft = rw.d.ftd || {},
                    mt = rw.d.mtd || {};
                const ftT = rw.k.reduce((a, k) => a + (ft[k] || 0), 0);
                const mtT = rw.k.reduce((a, k) => a + (mt[k] || 0), 0);
                html += `<tr><td>${rw.label}</td>`;
                rw.k.forEach(k => html += `<td>${lk(ft[k])}</td>`);
                html += `<td class="total-col ftd-total">${lk(ftT)}</td>`;
                rw.k.forEach(k => html += `<td>${lk(mt[k])}</td>`);
                html += `<td class="total-col mtd-total">${lk(mtT)}</td></tr>`;
            });

            // Discount partial
            const dp = dc.ftd?.partial || {},
                dpm = dc.mtd?.partial || {};
            html += `<tr><td>Discount 99%</td>`;
            revCols.forEach(k => html += `<td>${lk(dp[k])}</td>`);
            const dpFtT = revCols.reduce((a, k) => a + (dp[k] || 0), 0);
            html += `<td class="total-col ftd-total">${lk(dpFtT)}</td>`;
            revCols.forEach(k => html += `<td>${lk(dpm[k])}</td>`);
            const dpMtT = revCols.reduce((a, k) => a + (dpm[k] || 0), 0);
            html += `<td class="total-col mtd-total">${lk(dpMtT)}</td></tr>`;

            // Discount full
            const df = dc.ftd?.full || {},
                dfm = dc.mtd?.full || {};
            html += `<tr><td>Discount 100%</td>`;
            revCols.forEach(k => html += `<td>${lk(df[k])}</td>`);
            const dfFtT = revCols.reduce((a, k) => a + (df[k] || 0), 0);
            html += `<td class="total-col ftd-total">${lk(dfFtT)}</td>`;
            revCols.forEach(k => html += `<td>${lk(dfm[k])}</td>`);
            const dfMtT = revCols.reduce((a, k) => a + (dfm[k] || 0), 0);
            html += `<td class="total-col mtd-total">${lk(dfMtT)}</td></tr>`;

            // Refund
            const rf = r.ftd || {},
                rm = r.mtd || {};
            html += `<tr><td>Refund</td>`;
            revCols.forEach(k => html += `<td>${lk(rf[k])}</td>`);
            const rfFtT = revCols.reduce((a, k) => a + (rf[k] || 0), 0);
            html += `<td class="total-col ftd-total">${lk(rfFtT)}</td>`;
            revCols.forEach(k => html += `<td>${lk(rm[k])}</td>`);
            const rfMtT = revCols.reduce((a, k) => a + (rm[k] || 0), 0);
            html += `<td class="total-col mtd-total">${lk(rfMtT)}</td></tr>`;

            html += `</tbody></table></div></div>`;

            // Volume section
            const volTitle = isChromepet ? 'Volume Indicators & MRI' : 'Volume Indicators';
            html +=
                `<div class="section"><div class="section-head"><div class="section-title"><span class="material-icons-round">trending_up</span> ${volTitle}</div></div><div class="vol-grid">`;

            const vols = [{
                    label: 'Occupancy',
                    ftd: v.ftd?.occupancy || 0,
                    mtd: v.mtd?.occupancy || 0
                },
                {
                    label: 'Occupancy %',
                    ftd: (v.ftd?.occupancy_pct || 0) + '%',
                    mtd: (v.mtd?.occupancy_pct || 0) + '%'
                },
                {
                    label: 'Admission',
                    ftd: v.ftd?.admission || 0,
                    mtd: v.mtd?.admission || 0
                },
                {
                    label: 'Discharge',
                    ftd: v.ftd?.discharge || 0,
                    mtd: v.mtd?.discharge || 0
                },
                {
                    label: 'Total OP',
                    ftd: v.ftd?.total_op || 0,
                    mtd: v.mtd?.total_op || 0
                },
            ];
            if (isOragadam) {
                vols.push({
                    label: 'Total ER',
                    ftd: v.ftd?.er_count || 0,
                    mtd: v.mtd?.er_count || 0
                });
            }
            if (isChromepet) {
                vols.push({
                    label: 'MRI OP (Count)',
                    ftd: m.ftd?.op?.count || 0,
                    mtd: m.mtd?.op?.count || 0
                }, {
                    label: 'MRI IP (Count)',
                    ftd: m.ftd?.ip?.count || 0,
                    mtd: m.mtd?.ip?.count || 0
                }, {
                    label: 'MRI OP Revenue',
                    ftd: fmtRupee(m.ftd?.op?.revenue),
                    mtd: fmtRupee(m.mtd?.op?.revenue),
                    wide: true
                }, {
                    label: 'MRI IP Revenue',
                    ftd: fmtRupee(m.ftd?.ip?.revenue),
                    mtd: fmtRupee(m.mtd?.ip?.revenue),
                    wide: true
                }, );
            }
            vols.forEach(vi => {
                const wideClass = vi.wide ? ' wide' : '';
                const numClass = vi.wide ? ' sm' : '';
                html +=
                    `<div class="vol-card${wideClass}"><div class="vol-label">${vi.label}</div><div class="vol-values"><div class="vol-item"><div class="vol-period">FTD</div><div class="vol-num ftd${numClass}">${vi.ftd}</div></div><div class="vol-item"><div class="vol-period">MTD</div><div class="vol-num mtd${numClass}">${vi.mtd}</div></div></div></div>`;
            });
            html += `</div></div>`;

            document.getElementById('content').innerHTML = html;
        }
    </script>
</body>

</html>

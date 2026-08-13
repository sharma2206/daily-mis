<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload MIS - Hospital MIS</title>
    <meta name="description" content="Import hospital branch daily transaction logs for MIS analytics.">
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
            --bg: #f4f6fa;
            --surface: #fff;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --text: #0f172a;
            --text-sec: #475569;
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
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, .05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, .05), 0 2px 4px -2px rgba(0, 0, 0, .05);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, .04), 0 4px 6px -4px rgba(0, 0, 0, .04);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, .05), 0 8px 10px -6px rgba(0, 0, 0, .03);
            --radius: 16px;
            --radius-sm: 10px;
            --gradient: linear-gradient(135deg, #2563eb, #7c3aed);
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased
        }

        /* Nav */
        .nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            text-decoration: none
        }

        .nav-brand .logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, .2)
        }

        .nav-brand h1 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text)
        }

        .nav-badge {
            font-size: .6rem;
            padding: .15rem .5rem;
            border-radius: 9999px;
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 600;
            border: 1px solid var(--primary-light)
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: .5rem
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem .85rem;
            border-radius: var(--radius-sm);
            font-size: .85rem;
            font-weight: 500;
            color: var(--text-sec);
            text-decoration: none;
            transition: all .2s;
            border: 1px solid transparent
        }

        .nav-link:hover {
            color: var(--primary);
            background: var(--primary-soft)
        }

        .nav-link.active {
            color: var(--primary);
            background: var(--primary-soft);
            border-color: var(--primary-light);
            font-weight: 600
        }

        .nav-link .material-icons-round {
            font-size: 18px
        }

        /* Layout */
        .container {
            max-width: 860px;
            margin: 2rem auto;
            padding: 0 1.5rem
        }

        .intro {
            text-align: center;
            margin-bottom: 2rem
        }

        .intro h2 {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -.03em;
            margin-bottom: .4rem
        }

        .intro p {
            font-size: .9rem;
            color: var(--muted)
        }

        /* Branch Tabs */
        .branch-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem
        }

        .branch-tab {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            cursor: pointer;
            transition: all .25s;
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: var(--shadow)
        }

        .branch-tab:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md)
        }

        .branch-tab.active {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg), 0 0 0 1px var(--primary)
        }

        .branch-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--border-light);
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s
        }

        .branch-tab.active .branch-icon {
            background: var(--primary-soft);
            color: var(--primary)
        }

        .branch-icon .material-icons-round {
            font-size: 22px
        }

        .branch-tab .name {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: .2rem
        }

        .branch-tab .info {
            font-size: .78rem;
            color: var(--muted)
        }

        .branch-tab .info span {
            color: var(--primary);
            font-weight: 700
        }

        .branch-tab::after {
            content: "check_circle";
            font-family: "Material Icons Round";
            position: absolute;
            top: .85rem;
            right: .85rem;
            font-size: 20px;
            color: var(--primary);
            opacity: 0;
            transform: scale(.8);
            transition: all .2s
        }

        .branch-tab.active::after {
            opacity: 1;
            transform: scale(1)
        }

        /* Cards */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            transition: box-shadow .2s
        }

        .card:hover {
            box-shadow: var(--shadow-md)
        }

        .card-title {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: .65rem
        }

        .card-title .material-icons-round {
            font-size: 20px;
            color: var(--primary)
        }

        /* Form */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1rem
        }

        .form-row.single {
            grid-template-columns: 1fr
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: .4rem
        }

        .form-group label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-sec)
        }

        .form-group label .req {
            color: var(--error);
            font-weight: 700
        }

        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group select {
            padding: .7rem .9rem;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: .88rem;
            font-family: inherit;
            transition: all .2s;
            box-shadow: var(--shadow-sm)
        }

        .form-group input:hover {
            border-color: #cbd5e1
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12)
        }

        .form-group .help {
            font-size: .72rem;
            color: var(--muted)
        }

        .form-group .calc-value {
            font-size: 1.05rem;
            color: var(--success);
            font-weight: 700;
            min-height: 1.4em;
            padding: .4rem 0
        }

        /* File Dropzone */
        .file-zone {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: var(--radius-sm);
            padding: 1.25rem 1rem;
            background: var(--border-light);
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .4rem
        }

        .file-zone:hover {
            background: var(--primary-soft);
            border-color: var(--primary-light)
        }

        .file-zone .zi {
            font-size: 28px;
            color: var(--muted);
            transition: color .2s
        }

        .file-zone:hover .zi {
            color: var(--primary)
        }

        .file-zone .zt {
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-sec)
        }

        .file-zone .zs {
            font-size: .7rem;
            color: var(--muted)
        }

        .file-zone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10
        }

        .file-zone.selected {
            border-color: var(--success);
            background: rgba(5, 150, 105, .03)
        }

        .fbadge {
            display: none;
            align-items: center;
            gap: .35rem;
            background: var(--success-light);
            color: var(--success);
            padding: .3rem .7rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 600;
            margin-top: .2rem;
            border: 1px solid rgba(5, 150, 105, .15)
        }

        .fbadge .material-icons-round {
            font-size: 15px
        }

        .fbadge.show {
            display: inline-flex
        }

        /* Package section */
        .package-section {
            transition: all .3s;
            overflow: hidden
        }

        .package-section.hidden {
            max-height: 0;
            opacity: 0;
            margin: 0;
            padding: 0;
            pointer-events: none
        }

        .package-section.visible {
            max-height: 300px;
            opacity: 1
        }

        /* Buttons */
        .submit-row {
            display: flex;
            gap: 1rem;
            margin-top: .75rem
        }

        .btn {
            padding: .8rem 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            box-shadow: var(--shadow-sm)
        }

        .btn-primary {
            background: var(--gradient);
            color: #fff;
            flex: 1
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, .25)
        }

        .btn-primary:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none
        }

        .btn-reset {
            background: #fff;
            color: var(--text-sec);
            border: 1px solid var(--border)
        }

        .btn-reset:hover {
            border-color: var(--error-light);
            background: var(--error-light);
            color: var(--error)
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.25rem;
            font-size: .88rem;
            font-weight: 500;
            display: none;
            align-items: flex-start;
            gap: .75rem;
            box-shadow: var(--shadow-sm)
        }

        .alert.show {
            display: flex
        }

        .alert-success {
            background: var(--success-light);
            border: 1px solid rgba(5, 150, 105, .2);
            color: var(--success)
        }

        .alert-error {
            background: var(--error-light);
            border: 1px solid rgba(220, 38, 38, .2);
            color: var(--error)
        }

        .alert .msg {
            flex: 1
        }

        .alert .close-btn {
            cursor: pointer;
            opacity: .6;
            font-size: 1.2rem;
            transition: opacity .2s
        }

        .alert .close-btn:hover {
            opacity: 1
        }

        /* Spinner */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            display: none
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        /* Response */
        .response-card {
            display: none;
            animation: slideUp .3s ease
        }

        .response-card.show {
            display: block
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(12px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .response-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: .75rem;
            margin-bottom: 1.25rem
        }

        .stat-box {
            background: var(--border-light);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 1rem;
            text-align: center;
            transition: transform .2s
        }

        .stat-box:hover {
            transform: translateY(-2px)
        }

        .stat-box .stat-label {
            font-size: .68rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700
        }

        .stat-box .stat-value {
            font-size: 1.25rem;
            font-weight: 800;
            margin-top: .3rem;
            color: var(--primary);
            letter-spacing: -.02em
        }

        @media(max-width:640px) {
            .form-row {
                grid-template-columns: 1fr
            }

            .branch-tabs {
                grid-template-columns: 1fr
            }

            .nav {
                padding: 0 1rem
            }

            .card {
                padding: 1.25rem
            }
        }
    </style>
</head>

<body>
    <nav class="nav">
        <a href="/dashboard" class="nav-brand">
            <div class="logo"><span class="material-icons-round">local_hospital</span></div>
            <h1>Hospital MIS</h1>
            <span class="nav-badge">v1.0</span>
        </a>
        <div class="nav-links">
            <a href="/dashboard" class="nav-link"><span class="material-icons-round">analytics</span>Dashboard</a>
            <a href="/" class="nav-link active"><span class="material-icons-round">cloud_upload</span>Upload</a>
        </div>
    </nav>

    <div class="container">
        <div class="intro">
            <h2>MIS Report Upload</h2>
            <p>Upload hospital branch daily transaction logs to generate MIS analytics</p>
        </div>

        <div id="alertSuccess" class="alert alert-success">
            <span class="material-icons-round">check_circle</span>
            <span class="msg"></span>
            <span class="close-btn" onclick="this.parentElement.classList.remove('show')">&times;</span>
        </div>
        <div id="alertError" class="alert alert-error">
            <span class="material-icons-round">error</span>
            <span class="msg"></span>
            <span class="close-btn" onclick="this.parentElement.classList.remove('show')">&times;</span>
        </div>

        <div class="branch-tabs">
            <div class="branch-tab active" data-branch="chromepet" onclick="selectBranch('chromepet')">
                <div class="branch-icon"><span class="material-icons-round">local_hospital</span></div>
                <div>
                    <div class="name">Chromepet</div>
                    <div class="info">Beds: <span>74</span> · 3 files required</div>
                </div>
            </div>
            <div class="branch-tab" data-branch="oragadam" onclick="selectBranch('oragadam')">
                <div class="branch-icon"><span class="material-icons-round">domain</span></div>
                <div>
                    <div class="name">Oragadam</div>
                    <div class="info">Beds: <span>14</span> · 2 files required</div>
                </div>
            </div>
        </div>

        <form id="uploadForm" enctype="multipart/form-data">
            <div class="card">
                <div class="card-title"><span class="material-icons-round">calendar_today</span>Report Date</div>
                <div class="form-row single">
                    <div class="form-group">
                        <label>Date <span class="req">*</span></label>
                        <input type="date" name="date" id="dateInput" required>
                        <span class="help">Select the date of transaction records</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><span class="material-icons-round">folder_open</span>CSV / Excel Files</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Bill Item File <span class="req">*</span></label>
                        <div class="file-zone" id="zone_bill_file">
                            <span class="material-icons-round zi">description</span>
                            <span class="zt">Bill Item Report</span>
                            <span class="zs">Click to browse (.csv, .xlsx)</span>
                            <input type="file" name="bill_file" accept=".csv,.txt,.xlsx,.xls" required
                                onchange="onFile(this)">
                        </div>
                        <span class="fbadge" id="badge_bill_file"><span
                                class="material-icons-round">task_alt</span><span class="fn"></span></span>
                    </div>
                    <div class="form-group">
                        <label>Cashier Collection File <span class="req">*</span></label>
                        <div class="file-zone" id="zone_cashier_file">
                            <span class="material-icons-round zi">payments</span>
                            <span class="zt">Cashier Collection</span>
                            <span class="zs">Click to browse (.csv, .xlsx)</span>
                            <input type="file" name="cashier_file" accept=".csv,.txt,.xlsx,.xls" required
                                onchange="onFile(this)">
                        </div>
                        <span class="fbadge" id="badge_cashier_file"><span
                                class="material-icons-round">task_alt</span><span class="fn"></span></span>
                    </div>
                </div>
                <div class="package-section visible" id="packageSection">
                    <div class="form-row single">
                        <div class="form-group">
                            <label>Package Consumption File <span class="req" id="pkgReqStar">*</span></label>
                            <div class="file-zone" id="zone_package_file">
                                <span class="material-icons-round zi">card_membership</span>
                                <span class="zt">Package Consumption</span>
                                <span class="zs">Click to browse (.csv, .xlsx)</span>
                                <input type="file" name="package_file" id="packageFileInput"
                                    accept=".csv,.txt,.xlsx,.xls" onchange="onFile(this)">
                            </div>
                            <span class="fbadge" id="badge_package_file"><span
                                    class="material-icons-round">task_alt</span><span class="fn"></span></span>
                            <span class="help">Chromepet only</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><span class="material-icons-round">leaderboard</span>Volume Indicators (FTD)
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Occupancy (Beds Occupied)</label>
                        <input type="number" name="occupancy" id="occupancyInput" min="0"
                            placeholder="e.g. 52" oninput="calcOccupancyPct()">
                    </div>
                    <div class="form-group">
                        <label>Occupancy %</label>
                        <div class="calc-value" id="occupancyPctDisplay">—</div>
                        <input type="hidden" name="occupancy_pct" id="occupancyPctInput">
                        <span class="help">Auto: (Occupancy ÷ <span id="bedCountLabel">74</span>) × 100</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Admission</label>
                        <input type="number" name="admission" min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Discharge</label>
                        <input type="number" name="discharge" min="0" placeholder="0">
                    </div>
                </div>
                <div id="erCountSection" class="form-row single" style="display:none;">
                    <div class="form-group">
                        <label>ER Count (Manual Entry) <span class="req">*</span></label>
                        <input type="number" name="er_count" id="erCountInput" min="0" placeholder="0">
                        <span class="help">Enter the ER count for this day (Oragadam only)</span>
                    </div>
                </div>
            </div>

            <div class="submit-row">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="submitText">Upload & Generate MIS</span>
                    <div class="spinner" id="submitSpinner"></div>
                </button>
                <button type="button" class="btn btn-reset" onclick="resetForm()">Reset</button>
            </div>
        </form>

        <div class="card response-card" id="responseCard">
            <div class="card-title"><span class="material-icons-round">assignment_turned_in</span>Import Summary</div>
            <div class="response-grid" id="importStats"></div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <a id="exportLink" class="btn btn-primary"
                    style="text-decoration:none;font-size:.82rem;padding:.55rem 1.1rem;">
                    <span class="material-icons-round" style="font-size:18px">table_view</span> Download Excel</a>
                <a id="exportPdfLink" class="btn"
                    style="text-decoration:none;font-size:.82rem;padding:.55rem 1.1rem;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;border-radius:10px;font-weight:600;transition:all .2s"
                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 20px rgba(239,68,68,.25)'"
                    onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                    <span class="material-icons-round" style="font-size:18px">picture_as_pdf</span> Download PDF</a>
            </div>
        </div>
    </div>

    <script>
        const BED_COUNTS = {
            chromepet: 74,
            oragadam: 14
        };
        let currentBranch = 'chromepet';
        document.getElementById('dateInput').max = new Date().toISOString().split('T')[0];

        function onFile(inp) {
            const b = document.getElementById('badge_' + inp.name);
            const z = inp.closest('.file-zone');
            if (inp.files && inp.files.length) {
                b.querySelector('.fn').textContent = inp.files[0].name;
                b.classList.add('show');
                z.classList.add('selected');
            } else {
                b.classList.remove('show');
                z.classList.remove('selected');
            }
        }

        function selectBranch(branch) {
            currentBranch = branch;
            document.querySelectorAll('.branch-tab').forEach(t => t.classList.toggle('active', t.dataset.branch ===
            branch));
            const pkg = document.getElementById('packageSection'),
                inp = document.getElementById('packageFileInput'),
                star = document.getElementById('pkgReqStar');
            if (branch === 'chromepet') {
                pkg.classList.remove('hidden');
                pkg.classList.add('visible');
                inp.required = true;
                star.style.display = 'inline';
            } else {
                pkg.classList.add('hidden');
                pkg.classList.remove('visible');
                inp.required = false;
                inp.value = '';
                star.style.display = 'none';
                const pb = document.getElementById('badge_package_file');
                if (pb) {
                    pb.classList.remove('show');
                }
                document.getElementById('zone_package_file').classList.remove('selected');
            }
            const erSection = document.getElementById('erCountSection'),
                erInput = document.getElementById('erCountInput');
            if (branch === 'oragadam') {
                erSection.style.display = 'block';
                erInput.required = true;
            } else {
                erSection.style.display = 'none';
                erInput.required = false;
                erInput.value = '';
            }
            document.getElementById('bedCountLabel').textContent = BED_COUNTS[branch];
            calcOccupancyPct();
        }

        function calcOccupancyPct() {
            const occ = parseInt(document.getElementById('occupancyInput').value) || 0;
            const beds = BED_COUNTS[currentBranch];
            const pct = beds > 0 ? ((occ / beds) * 100).toFixed(2) : 0;
            document.getElementById('occupancyPctDisplay').textContent = occ > 0 ? pct + '%' : '—';
            document.getElementById('occupancyPctInput').value = pct;
        }

        function resetForm() {
            document.getElementById('uploadForm').reset();
            document.getElementById('occupancyPctDisplay').textContent = '—';
            document.getElementById('responseCard').classList.remove('show');
            document.querySelectorAll('.file-zone').forEach(z => z.classList.remove('selected'));
            document.querySelectorAll('.fbadge').forEach(b => b.classList.remove('show'));
            hideAlerts();
            selectBranch('chromepet');
        }

        function hideAlerts() {
            document.getElementById('alertSuccess').classList.remove('show');
            document.getElementById('alertError').classList.remove('show');
        }

        function showAlert(type, msg) {
            hideAlerts();
            const el = document.getElementById(type === 'success' ? 'alertSuccess' : 'alertError');
            el.querySelector('.msg').textContent = msg;
            el.classList.add('show');
        }

        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideAlerts();
            const btn = document.getElementById('submitBtn'),
                spinner = document.getElementById('submitSpinner'),
                text = document.getElementById('submitText');
            btn.disabled = true;
            spinner.style.display = 'block';
            text.textContent = 'Processing...';
            const fd = new FormData(this);
            fd.append('branch', currentBranch);
            if (currentBranch !== 'chromepet') fd.delete('package_file');
            try {
                const r = await fetch('/api/mis/' + currentBranch + '/upload', {
                    method: 'POST',
                    body: fd
                });
                const j = await r.json();
                if (j.success) {
                    showAlert('success', j.message || 'Done!');
                    showResponse(j);
                } else {
                    showAlert('error', j.message || (j.errors ? Object.values(j.errors).flat().join(', ') :
                        'Failed.'));
                }
            } catch (err) {
                showAlert('error', 'Network error: ' + err.message);
            } finally {
                btn.disabled = false;
                spinner.style.display = 'none';
                text.textContent = 'Upload & Generate MIS';
            }
        });

        function showResponse(j) {
            const card = document.getElementById('responseCard'),
                stats = document.getElementById('importStats'),
                imp = j.imported || {};
            let h = '<div class="stat-box"><div class="stat-label">Bill Items</div><div class="stat-value">' + (imp
                .bill_items || 0).toLocaleString() + '</div></div>';
            h += '<div class="stat-box"><div class="stat-label">Collections</div><div class="stat-value">' + (imp
                .cashier_collections || 0).toLocaleString() + '</div></div>';
            if (currentBranch === 'chromepet') h +=
                '<div class="stat-box"><div class="stat-label">Packages</div><div class="stat-value">' + (imp
                    .package_consumptions || 0).toLocaleString() + '</div></div>';
            const d = j.data || {},
                s = d.sales || {},
                ftd = s.ftd || {};
            const tot = ((ftd.op || 0) + (ftd.ip || 0) + (ftd.er || 0) + (ftd.ph || 0)).toFixed(2);
            h += '<div class="stat-box"><div class="stat-label">FTD Sales</div><div class="stat-value">₹' + Number(tot)
                .toLocaleString() + '</div></div>';
            stats.innerHTML = h;
            card.classList.add('show');
            const date = document.getElementById('dateInput').value;
            document.getElementById('exportLink').href = '/api/mis/' + currentBranch + '/' + date + '/export';
            document.getElementById('exportPdfLink').href = '/api/mis/' + currentBranch + '/' + date + '/export-pdf';
        }
    </script>
</body>

</html>

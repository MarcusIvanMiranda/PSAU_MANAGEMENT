<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Document - PSAU Records System</title>
    <link rel="icon" href="PSAU.ico">
    <link rel="stylesheet" href="assets/css/psau-style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-950: #0d2b1e;
            --green-900: #1e5a3d;
            --green-800: #2d7a4f;
            --green-700: #3a9160;
            --green-600: #4aab72;
            --green-200: #a7d4b8;
            --green-100: #d4edde;
            --green-50:  #eef8f2;
            --gold:      #c9a84c;
            --white:     #ffffff;
            --gray-50:   #f8f9f8;
            --gray-100:  #f0f2f0;
            --gray-200:  #e2e5e2;
            --gray-300:  #cdd1cd;
            --gray-400:  #9bab9e;
            --gray-500:  #6d7d70;
            --gray-600:  #586a5c;
            --gray-700:  #3d4f40;
            --gray-900:  #1a2a1c;
            --red:       #dc2626;
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-50);
            padding: 24px;
            color: var(--gray-700);
        }

        /* Background */
        .bg-layer {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 70% 55% at 15% 10%, rgba(30,90,61,0.09) 0%, transparent 55%),
                radial-gradient(ellipse 55% 45% at 88% 90%, rgba(74,171,114,0.07) 0%, transparent 50%),
                var(--gray-50);
        }
        .bg-grid {
            position: fixed; inset: 0; z-index: 0;
            background-image: linear-gradient(rgba(30,90,61,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(30,90,61,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* Card */
        .card {
            position: relative; z-index: 1;
            width: 100%; max-width: 540px;
            background: var(--white);
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            box-shadow: 0 24px 64px rgba(14,43,30,0.12), 0 4px 16px rgba(14,43,30,0.07);
            overflow: hidden;
            animation: cardIn 0.5s cubic-bezier(0.22,1,0.36,1);
        }
        @keyframes cardIn { from { opacity:0; transform: translateY(20px) scale(0.98); } to { opacity:1; transform: translateY(0) scale(1); } }

        /* Card Header */
        .card-head {
            background: linear-gradient(145deg, var(--green-950) 0%, var(--green-900) 55%, var(--green-800) 100%);
            padding: 36px 36px 30px;
            position: relative; overflow: hidden;
        }
        .card-head::before {
            content: ''; position: absolute;
            width: 320px; height: 320px; border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.06);
            top: -140px; right: -80px;
        }
        .head-noise {
            position: absolute; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            opacity: 0.4; pointer-events: none;
        }
        .head-icon {
            position: relative; z-index: 1;
            width: 52px; height: 52px; background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2); border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px; backdrop-filter: blur(8px);
        }
        .head-icon svg { width: 24px; height: 24px; color: #fff; }
        .head-eyebrow {
            position: relative; z-index: 1;
            font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.15em;
            text-transform: uppercase; color: rgba(201,168,76,0.85); margin-bottom: 6px;
        }
        .head-title {
            position: relative; z-index: 1;
            font-family: 'Playfair Display', serif;
            font-size: 1.625rem; font-weight: 700; color: #fff; line-height: 1.2;
        }
        .head-sub {
            position: relative; z-index: 1;
            font-size: 0.8125rem; color: rgba(255,255,255,0.5); font-weight: 300; margin-top: 5px;
        }

        /* Serial badge */
        .serial-badge {
            position: relative; z-index: 1;
            display: inline-flex; align-items: center; gap: 8px;
            margin-top: 18px; padding: 7px 14px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 999px; backdrop-filter: blur(4px);
        }
        .serial-label { font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.5); font-weight: 600; }
        .serial-value { font-size: 0.875rem; font-weight: 700; color: rgba(201,168,76,0.9); letter-spacing: 0.06em; font-family: 'Courier New', monospace; }

        /* Card Body */
        .card-body { padding: 30px 36px; }

        /* Form elements */
        .form-group { margin-bottom: 20px; }
        .form-group:last-of-type { margin-bottom: 0; }
        .form-label {
            display: flex; align-items: center; gap: 6px;
            margin-bottom: 7px; font-size: 0.8125rem; font-weight: 600;
            color: var(--gray-700); letter-spacing: 0.01em;
        }
        .form-label .req { color: var(--red); }
        .form-control {
            display: block; width: 100%;
            padding: 10px 14px;
            font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
            color: var(--gray-900); background: var(--white);
            border: 1px solid var(--gray-200); border-radius: 10px;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
            appearance: none; -webkit-appearance: none;
        }
        .form-control:focus {
            outline: none; border-color: var(--green-700);
            box-shadow: 0 0 0 3px rgba(30,90,61,0.1);
            background: var(--green-50);
        }
        .form-control::placeholder { color: var(--gray-300); }

        /* Select arrow */
        .select-wrap { position: relative; }
        .select-wrap::after {
            content: '';
            position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
            width: 0; height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid var(--gray-400);
            pointer-events: none;
        }
        .select-wrap .form-control { padding-right: 36px; cursor: pointer; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 24px 0 20px;
        }
        .divider-line { flex: 1; height: 1px; background: var(--gray-200); }
        .divider-text { font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gray-400); font-weight: 600; white-space: nowrap; }

        /* Submit button */
        .btn-submit {
            width: 100%; margin-top: 8px;
            padding: 13px 20px;
            background: linear-gradient(145deg, var(--green-900), var(--green-800));
            color: #fff; border: none; border-radius: 10px;
            font-family: 'DM Sans', sans-serif; font-size: 0.9375rem; font-weight: 600;
            cursor: pointer; letter-spacing: 0.01em;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(30,90,61,0.3);
        }
        .btn-submit:hover { background: linear-gradient(145deg, var(--green-800), var(--green-700)); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(30,90,61,0.35); }
        .btn-submit:active { transform: translateY(0); box-shadow: 0 2px 8px rgba(30,90,61,0.3); }
        .btn-submit:disabled { background: var(--gray-200); color: var(--gray-400); cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-submit svg { width: 17px; height: 17px; }

        /* Card footer */
        .card-foot {
            padding: 16px 36px 20px;
            background: var(--gray-50); border-top: 1px solid var(--gray-100);
            text-align: center;
        }
        .card-foot p { font-size: 0.75rem; color: var(--gray-400); line-height: 1.5; }
        .card-foot .req { color: var(--red); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--green-600); border-radius: 99px; }

        /* Mobile */
        @media (max-width: 480px) {
            body { padding: 0; align-items: flex-start; }
            .card { border-radius: 0; min-height: 100vh; box-shadow: none; }
            .card-head, .card-body, .card-foot { padding-left: 22px; padding-right: 22px; }
        }
    </style>
</head>
<body>
<div class="bg-layer"></div>
<div class="bg-grid"></div>

<div class="card">

    <div class="card-head">
        <div class="head-noise"></div>
        <div class="head-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="head-eyebrow">Records Unit · DTS</div>
        <h1 class="head-title">Register New<br>Document</h1>
        <p class="head-sub">Pampanga State Agricultural University</p>

    </div>

    <form id="documentForm" action="savedocument.php" method="post">
        <div class="card-body">

            <div class="form-group">
                <label for="doc_title" class="form-label">
                    Document Title <span class="req">*</span>
                </label>
                <input
                    type="text"
                    id="doc_title"
                    name="doc_title"
                    class="form-control"
                    placeholder="Enter the document title"
                    required
                    onkeypress="return /[0-9a-zA-Z ]/i.test(event.key)"
                >
            </div>

            <div class="form-group">
                <label for="doc_type" class="form-label">
                    Document Type <span class="req">*</span>
                </label>
                <div class="select-wrap">
                    <select id="doc_type" name="doc_type" class="form-control" required>
                        <option value="INTERNAL">Internal</option>
                        <option value="EXTERNAL">External</option>
                        <option value="OFFICE ORDER">Office Order</option>
                        <option value="MEMORANDUM">Memorandum</option>
                        <option value="ANNOUNCEMENT">Announcement</option>
                        <option value="SPECIAL ORDER">Special Order</option>
                    </select>
                </div>
            </div>

            <div class="divider">
                <div class="divider-line"></div>
                <span class="divider-text">Sender Information</span>
                <div class="divider-line"></div>
            </div>

            <div class="form-group">
                <label for="employeedeatils" class="form-label">
                    Received From <span class="req">*</span>
                </label>
                <div class="select-wrap">
                    <select id="employeedeatils" name="employeedeatils" class="form-control" required>
                        <?php
                        include 'connect.php';
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
                        $query = "SELECT * FROM employees ORDER BY fname";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($data = mysqli_fetch_row($result)) {
                                $option_value = $data[6] . "-" . $data[3] . " " . $data[2];
                                $option_text  = strtoupper($data[3]) . " " . strtoupper($data[2]);
                                echo "<option value='" . $option_value . "'>" . $option_text . "</option>";
                            }
                        }
                        mysqli_close($conn);
                        ?>
                    </select>
                </div>
            </div>

            <input type="hidden" id="RandomSerial" name="RandomSerial">

            <button type="submit" class="btn-submit" id="submitBtn">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Register Document
            </button>

        </div>

        <div class="card-foot">
            <p>Fields marked <span class="req">*</span> are required &nbsp;·&nbsp; Serial number is auto-generated</p>
        </div>
    </form>
</div>

<script src="https://randojs.com/1.0.0.js"></script>
<script>
    function generateSerial() {
        let serial = '';
        for (let i = 0; i < 8; i++) serial = rando("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") + serial;
        return serial;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const serial = generateSerial();
        document.getElementById('RandomSerial').value = serial;
        document.getElementById('serialDisplay').textContent = serial;
    });

    document.getElementById('documentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const title = document.getElementById('doc_title').value.trim();
        const type  = document.getElementById('doc_type').value;
        const emp   = document.getElementById('employeedeatils').value;
        if (!title || !type || !emp) { alert('Please fill in all required fields.'); return; }
        const serial = generateSerial();
        document.getElementById('RandomSerial').value = serial;
        document.getElementById('serialDisplay').textContent = serial;
        btn.disabled = true;
        btn.innerHTML = `<svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m0 14v1m8-8h-1M5 12H4m13.657-6.343l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707"/></svg> Registering…`;
        this.submit();
    });

    if (window.history.replaceState) window.history.replaceState(null, null, window.location.href);
</script>
</body>
</html>
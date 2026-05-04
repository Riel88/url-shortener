<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>URL Shortener</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 24px;
        }

        .badge {
            background: rgba(255,255,255,0.1);
            color: #a0c4ff;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 999px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 16px;
            border: 1px solid rgba(255,255,255,0.15);
        }

        .headline {
            color: white;
            font-size: 42px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .headline span {
            background: linear-gradient(90deg, #4facfe, #00f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subheadline {
            color: #94a3b8;
            text-align: center;
            font-size: 16px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 580px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 14px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }

        input[type="url"] {
            width: 100%;
            padding: 16px 16px 16px 46px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
            transition: all 0.2s;
            color: #1a202c;
            background: #f8fafc;
        }

        input[type="url"]:focus {
            border-color: #4facfe;
            background: white;
            box-shadow: 0 0 0 4px rgba(79,172,254,0.12);
        }

        input[type="url"]::placeholder { color: #b0bec5; }

        .btn-shorten {
            width: 100%;
            padding: 16px;
            background: linear-gradient(90deg, #4facfe, #00f2fe);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.5px;
        }

        .btn-shorten:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(79,172,254,0.4);
        }

        .btn-shorten:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-top: 14px;
            display: none;
            align-items: center;
            gap: 8px;
        }

        .result {
            margin-top: 20px;
            background: linear-gradient(135deg, #f0fff4, #e6fffa);
            border: 1px solid #9ae6b4;
            border-radius: 16px;
            padding: 20px 24px;
            display: none;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .result-label {
            font-size: 12px;
            color: #68d391;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .result-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .result-url {
            font-size: 20px;
            font-weight: 800;
            color: #276749;
            text-decoration: none;
            word-break: break-all;
        }

        .result-url:hover { text-decoration: underline; }

        .copy-btn {
            padding: 8px 18px;
            background: #48bb78;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .copy-btn:hover { background: #38a169; }

        .original-label {
            margin-top: 12px;
            font-size: 12px;
            color: #a0aec0;
        }

        .original-text {
            font-size: 13px;
            color: #718096;
            word-break: break-all;
            margin-top: 2px;
        }

        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 24px 0;
        }

        .history-section { display: none; }

        .history-title {
            font-size: 13px;
            font-weight: 700;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            border-radius: 10px;
            transition: background 0.15s;
            margin-bottom: 4px;
        }

        .history-item:hover { background: #f7fafc; }

        .history-code a {
            color: #4facfe;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
        }

        .history-code a:hover { text-decoration: underline; }

        .history-original {
            color: #b0bec5;
            font-size: 12px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-top: 2px;
        }

        .history-arrow { color: #cbd5e0; font-size: 16px; }

        .footer {
            margin-top: 28px;
            color: rgba(255,255,255,0.3);
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="badge">✦ URL Shortener</div>
    <h1 class="headline">Persingkat URL kamu<br><span>dalam satu klik</span></h1>
    <p class="subheadline">Gratis, cepat, dan mudah digunakan</p>

    <div class="card">
        <div class="input-wrapper">
            <span class="input-icon">🔗</span>
            <input type="url" id="urlInput" placeholder="https://contoh.com/url-panjang-sekali..." />
        </div>

        <button class="btn-shorten" id="submitBtn" onclick="shortenUrl()">
            Persingkat Sekarang →
        </button>

        <div class="error" id="errorBox">
            <span>⚠️</span>
            <span id="errorMsg"></span>
        </div>

        <div class="result" id="resultBox">
            <div class="result-label">✓ Berhasil dipersingkat!</div>
            <div class="result-row">
                <a class="result-url" id="shortUrlLink" href="#" target="_blank"></a>
                <button class="copy-btn" onclick="copyUrl()">📋 Salin</button>
            </div>
            <div class="original-label">URL asli:</div>
            <div class="original-text" id="originalUrlText"></div>
        </div>

        <div class="history-section" id="historySection">
            <hr class="divider">
            <div class="history-title">📋 Riwayat sesi ini</div>
            <div id="historyList"></div>
        </div>
    </div>

    

    <script>
        const sessionHistory = [];

        async function shortenUrl() {
            const input = document.getElementById('urlInput');
            const btn = document.getElementById('submitBtn');
            const errorBox = document.getElementById('errorBox');
            const resultBox = document.getElementById('resultBox');
            const url = input.value.trim();

            errorBox.style.display = 'none';
            resultBox.style.display = 'none';

            if (!url) {
                showError('Masukkan URL terlebih dahulu.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Memproses...';

            try {
                const response = await fetch('/api/shorten', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ url })
                });

                const data = await response.json();

                if (!response.ok) {
                    const msg = data.errors?.url?.[0] || 'Terjadi kesalahan. Coba lagi.';
                    showError(msg);
                    return;
                }

                document.getElementById('shortUrlLink').href = data.short_url;
                document.getElementById('shortUrlLink').textContent = data.short_url;
                document.getElementById('originalUrlText').textContent = data.original_url;
                resultBox.style.display = 'block';

                addToHistory(data);
                input.value = '';

            } catch (e) {
                showError('Gagal terhubung ke server.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Persingkat Sekarang →';
            }
        }

        function showError(msg) {
            const errorBox = document.getElementById('errorBox');
            document.getElementById('errorMsg').textContent = msg;
            errorBox.style.display = 'flex';
        }

        function copyUrl() {
            const url = document.getElementById('shortUrlLink').textContent;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.textContent = '✓ Tersalin!';
                btn.style.background = '#276749';
                setTimeout(() => {
                    btn.textContent = '📋 Salin';
                    btn.style.background = '';
                }, 2000);
            });
        }

        function addToHistory(data) {
            sessionHistory.unshift(data);
            const section = document.getElementById('historySection');
            const list = document.getElementById('historyList');
            section.style.display = 'block';
            list.innerHTML = sessionHistory.map(item => `
                <div class="history-item">
                    <div>
                        <div class="history-code">
                            <a href="${item.short_url}" target="_blank">${item.short_url}</a>
                        </div>
                        <div class="history-original">${item.original_url}</div>
                    </div>
                    <span class="history-arrow">→</span>
                </div>
            `).join('');
        }

        document.getElementById('urlInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') shortenUrl();
        });
    </script>
</body>
</html>
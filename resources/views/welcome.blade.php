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
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            border-radius: 16px;
            padding: 48px;
            width: 100%;
            max-width: 560px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        .logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
        }

        .logo p {
            color: #718096;
            margin-top: 6px;
            font-size: 15px;
        }

        .form-group {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        input[type="url"] {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }

        input[type="url"]:focus { border-color: #4299e1; }

        button {
            padding: 12px 24px;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        button:hover { background: #3182ce; }
        button:disabled { background: #a0aec0; cursor: not-allowed; }

        .error {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
            display: none;
        }

        .result {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 12px;
            padding: 20px;
            display: none;
        }

        .result-label {
            font-size: 13px;
            color: #718096;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .result-url {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .result-url a {
            font-size: 18px;
            font-weight: 700;
            color: #2f855a;
            text-decoration: none;
            word-break: break-all;
        }

        .result-url a:hover { text-decoration: underline; }

        .copy-btn {
            padding: 6px 14px;
            background: #48bb78;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .copy-btn:hover { background: #38a169; }

        .original-url {
            margin-top: 12px;
            font-size: 13px;
            color: #a0aec0;
            word-break: break-all;
        }

        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 28px 0;
        }

        .history { display: none; }
        .history h3 { font-size: 15px; color: #4a5568; margin-bottom: 12px; font-weight: 600; }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f7fafc;
            font-size: 14px;
        }

        .history-item:last-child { border-bottom: none; }
        .history-code { color: #4299e1; font-weight: 600; }
        .history-original { color: #a0aec0; font-size: 12px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🔗 URL Shortener</h1>
            <p>Persingkat URL panjangmu dalam satu klik</p>
        </div>

        <div class="form-group">
            <input type="url" id="urlInput" placeholder="https://contoh.com/url-panjang-kamu" />
            <button id="submitBtn" onclick="shortenUrl()">Persingkat</button>
        </div>

        <div class="error" id="errorBox"></div>

        <div class="result" id="resultBox">
            <div class="result-label">Short URL kamu:</div>
            <div class="result-url">
                <a id="shortUrlLink" href="#" target="_blank"></a>
                <button class="copy-btn" onclick="copyUrl()">Salin</button>
            </div>
            <div class="original-url" id="originalUrlText"></div>
        </div>

        <div class="history" id="historyBox">
            <hr class="divider">
            <h3>Riwayat sesi ini</h3>
            <div id="historyList"></div>
        </div>
    </div>

    <script>
        const history = [];

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
                document.getElementById('originalUrlText').textContent = 'Dari: ' + data.original_url;
                resultBox.style.display = 'block';

                addToHistory(data);
                input.value = '';

            } catch (e) {
                showError('Gagal terhubung ke server.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Persingkat';
            }
        }

        function showError(msg) {
            const errorBox = document.getElementById('errorBox');
            errorBox.textContent = msg;
            errorBox.style.display = 'block';
        }

        function copyUrl() {
            const url = document.getElementById('shortUrlLink').textContent;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.textContent = 'Tersalin!';
                setTimeout(() => btn.textContent = 'Salin', 2000);
            });
        }

        function addToHistory(data) {
            history.unshift(data);
            const historyBox = document.getElementById('historyBox');
            const historyList = document.getElementById('historyList');
            historyBox.style.display = 'block';
            historyList.innerHTML = history.map(item => `
                <div class="history-item">
                    <div>
                        <div class="history-code"><a href="${item.short_url}" target="_blank">${item.short_url}</a></div>
                        <div class="history-original">${item.original_url}</div>
                    </div>
                </div>
            `).join('');
        }

        document.getElementById('urlInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') shortenUrl();
        });
    </script>
</body>
</html>
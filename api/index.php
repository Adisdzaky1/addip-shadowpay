<?php
// api/index.php

// Tangani preflight OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Daftar endpoint yang diizinkan
$endpoints = [
    'deposit/create',
    'deposit/status',
    'deposit/metode',
    'transaksi/create',
    'transaksi/status'
];

// Ambil URI dan metode
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Hapus query string
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = ltrim($requestPath, '/');

// Jika akses root (kosong) tampilkan dokumentasi
if ($requestPath === '' || $requestPath === 'api/index.php') {
    showDocumentation();
    exit;
}

// Cek apakah endpoint valid dan method POST
if ($requestMethod === 'POST' && in_array($requestPath, $endpoints)) {
    forwardToAtlantic($requestPath);
} else {
    http_response_code(404);
    echo json_encode(['status' => false, 'message' => 'Endpoint not found']);
}

/**
 * Meneruskan request ke API Atlantic
 */
function forwardToAtlantic($endpoint) {
    $targetUrl = "https://atlantich2h.com/" . $endpoint;

    // Ambil raw body
    $rawBody = file_get_contents('php://input');

    // Inisialisasi cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
   
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Curl error: ' . $error]);
    } else {
        http_response_code($httpCode);
        echo $response;
    }
}

/**
 * Tampilkan halaman dokumentasi
 */
function showDocumentation() {
    // Tentukan base URL (di Vercel bisa dari header)
    $baseUrl = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'http') . '://' . $_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atlantic API Proxy Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
    <style>
        .method-post { background-color: #3b82f6; color: white; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; text-transform: uppercase; }
        .copy-btn { transition: all 0.2s ease; }
        .copy-btn.copied { background-color: #10b981; color: white; border-color: #10b981; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-cloud-sun text-blue-500 text-2xl"></i>
                <span class="font-bold text-xl text-gray-900">Atlantic API Proxy</span>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">PHP on Vercel</span>
            </div>
            <div class="text-sm">
                <span class="text-gray-500 mr-2">Base URL:</span>
                <code class="bg-gray-100 px-3 py-1.5 rounded-lg text-gray-800 font-mono"><?= htmlspecialchars($baseUrl) ?></code>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Dokumentasi API</h1>
            <p class="text-gray-600">Server proxy untuk API Atlantic. Gunakan endpoint ini untuk menghindari pemblokiran. Semua request akan diteruskan ke <code class="bg-gray-100 px-1.5 py-0.5 rounded">https://atlantich2h.com</code> dengan metode POST dan format <code>x-www-form-urlencoded</code>. Anda wajib menyertakan parameter <code>api_key</code> (isi dengan API key asli Anda).</p>
        </div>

        <div class="space-y-8">
            <!-- Create Deposit -->
            <section id="create-deposit" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center gap-3">
                    <span class="method-post">POST</span>
                    <h2 class="text-lg font-semibold text-gray-900 font-mono">/deposit/create</h2>
                </div>
                <div class="p-6 space-y-6">
                    <p class="text-gray-700">Membuat permintaan deposit baru (QRIS).</p>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-paper-plane text-blue-500 mr-2 text-xs"></i> Contoh Request (axios)</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-javascript">var axios = require('axios');
var qs = require('qs');
var data = qs.stringify({
  'api_key': 'YOUR_API_KEY',
  'reff_id': 'testrevdep22',
  'nominal': '1',
  'type': 'ewallet',
  'metode': 'qris'
});
var config = {
  method: 'post',
  url: '<?= htmlspecialchars($baseUrl) ?>/deposit/create',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  data: data
};
axios(config).then(res => console.log(res.data)).catch(err => console.log(err));</code></pre>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-server text-green-500 mr-2 text-xs"></i> Contoh Respons</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-json">{
  "status": true,
  "data": {
    "id": "d4b8e3a2-1f5e-4c7b-9a6c-3d2f1e5a8b7c",
    "reff_id": "testrevdep22",
    "nominal": 1,
    "qr_string": "xxxxxxxxxxxxxxxxxxxxx",
    "qr_image": "https://atlantich2h.com/qr/...",
    "status": "pending",
    "created_at": "2025-03-01 10:30:00",
    "expired_at": "2025-03-01 11:30:00"
  },
  "code": 200
}</code></pre>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Parameter</h3>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-left">Tipe</th><th class="px-4 py-2 text-left">Deskripsi</th></tr></thead>
                            <tbody>
                                <tr><td class="px-4 py-2 font-mono">api_key</td><td>string</td><td>API Key asli Anda</td></tr>
                                <tr><td class="px-4 py-2 font-mono">reff_id</td><td>string</td><td>ID referensi unik</td></tr>
                                <tr><td class="px-4 py-2 font-mono">nominal</td><td>number</td><td>Jumlah deposit</td></tr>
                                <tr><td class="px-4 py-2 font-mono">type</td><td>string</td><td>Tipe (ewallet)</td></tr>
                                <tr><td class="px-4 py-2 font-mono">metode</td><td>string</td><td>Metode (qris, ovo, dll)</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Deposit Status -->
            <section id="deposit-status" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center gap-3">
                    <span class="method-post">POST</span>
                    <h2 class="text-lg font-semibold text-gray-900 font-mono">/deposit/status</h2>
                </div>
                <div class="p-6 space-y-6">
                    <p class="text-gray-700">Cek status deposit berdasarkan ID.</p>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-paper-plane text-blue-500 mr-2 text-xs"></i> Contoh Request</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-javascript">var axios = require('axios');
var qs = require('qs');
var data = qs.stringify({
  'api_key': 'YOUR_API_KEY',
  'id': 'txIsgoOtPklEOZ5w1N7A'
});
var config = {
  method: 'post',
  url: '<?= htmlspecialchars($baseUrl) ?>/deposit/status',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  data: data
};
axios(config).then(res => console.log(res.data));</code></pre>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-server text-green-500 mr-2 text-xs"></i> Contoh Respons</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-json">{
  "status": true,
  "data": {
    "id": "txIsgoOtPklEOZ5w1N7A",
    "reff_id": "testrevdep22",
    "nominal": "20000",
    "tambahan": "0",
    "fee": "300",
    "get_balance": "19700",
    "metode": "E-Wallet DANA",
    "status": "success",
    "created_at": "2024-02-13 14:25:22"
  },
  "code": 200
}</code></pre>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Parameter</h3>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-left">Tipe</th><th class="px-4 py-2 text-left">Deskripsi</th></tr></thead>
                            <tbody>
                                <tr><td class="px-4 py-2 font-mono">api_key</td><td>string</td><td>API Key asli</td></tr>
                                <tr><td class="px-4 py-2 font-mono">id</td><td>string</td><td>ID transaksi deposit</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Metode Deposit -->
            <section id="deposit-metode" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center gap-3">
                    <span class="method-post">POST</span>
                    <h2 class="text-lg font-semibold text-gray-900 font-mono">/deposit/metode</h2>
                </div>
                <div class="p-6 space-y-6">
                    <p class="text-gray-700">Mendapatkan daftar metode deposit.</p>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-paper-plane text-blue-500 mr-2 text-xs"></i> Contoh Request</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-javascript">var data = qs.stringify({
  'api_key': 'YOUR_API_KEY',
  'type': 'ewallet',
  'metode': 'qris'
});</code></pre>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-server text-green-500 mr-2 text-xs"></i> Contoh Respons</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-json">{
  "status": true,
  "data": [
    {
      "metode": "OVO",
      "type": "ewallet",
      "name": "OVO",
      "min": "2000",
      "max": "5000000",
      "fee": "0",
      "fee_persen": "1.65",
      "status": "aktif",
      "img_url": "https://s3.atlantic-pedia.co.id/..."
    }
  ],
  "code": 200
}</code></pre>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Parameter</h3>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-left">Tipe</th><th class="px-4 py-2 text-left">Deskripsi</th></tr></thead>
                            <tbody>
                                <tr><td class="px-4 py-2 font-mono">api_key</td><td>string</td><td>API Key</td></tr>
                                <tr><td class="px-4 py-2 font-mono">type</td><td>string</td><td>Filter tipe (ewallet)</td></tr>
                                <tr><td class="px-4 py-2 font-mono">metode</td><td>string</td><td>Filter metode (opsional)</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Create Transaksi -->
            <section id="create-transaksi" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center gap-3">
                    <span class="method-post">POST</span>
                    <h2 class="text-lg font-semibold text-gray-900 font-mono">/transaksi/create</h2>
                </div>
                <div class="p-6 space-y-6">
                    <p class="text-gray-700">Membuat transaksi prabayar.</p>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-paper-plane text-blue-500 mr-2 text-xs"></i> Contoh Request</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-javascript">var data = qs.stringify({
  'api_key': 'YOUR_API_KEY',
  'code': 'KODE1',
  'reff_id': 'reffexample123',
  'target': '0856123456789'
});</code></pre>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-server text-green-500 mr-2 text-xs"></i> Contoh Respons</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-json">{
  "status": true,
  "message": "Transaksi diproses",
  "data": {
    "id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
    "reff_id": "reffexample123",
    "layanan": "Data 1 GB / 30 Hari",
    "code": "KODE1",
    "target": "0856123456789",
    "price": "58507",
    "sn": null,
    "status": "pending",
    "created_at": "2025-03-01 10:30:00"
  },
  "code": 202
}</code></pre>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Parameter</h3>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-left">Tipe</th><th class="px-4 py-2 text-left">Deskripsi</th></tr></thead>
                            <tbody>
                                <tr><td class="px-4 py-2 font-mono">api_key</td><td>string</td><td>API Key</td></tr>
                                <tr><td class="px-4 py-2 font-mono">code</td><td>string</td><td>Kode layanan</td></tr>
                                <tr><td class="px-4 py-2 font-mono">reff_id</td><td>string</td><td>ID referensi</td></tr>
                                <tr><td class="px-4 py-2 font-mono">target</td><td>string</td><td>Nomor tujuan</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Transaksi Status -->
            <section id="transaksi-status" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center gap-3">
                    <span class="method-post">POST</span>
                    <h2 class="text-lg font-semibold text-gray-900 font-mono">/transaksi/status</h2>
                </div>
                <div class="p-6 space-y-6">
                    <p class="text-gray-700">Cek status transaksi.</p>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-paper-plane text-blue-500 mr-2 text-xs"></i> Contoh Request</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-javascript">var data = qs.stringify({
  'api_key': 'YOUR_API_KEY',
  'id': 'IDTrxExample123456789',
  'type': 'prabayar'
});</code></pre>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-server text-green-500 mr-2 text-xs"></i> Contoh Respons</h3>
                        <div class="relative bg-gray-50 rounded-lg border border-gray-200 p-4">
                            <button onclick="copyCode(this)" class="copy-btn absolute top-3 right-3 bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 rounded-lg text-sm px-3 py-1.5 flex items-center gap-1"><i class="far fa-clipboard"></i> <span>Salin</span></button>
                            <pre><code class="language-json">{
  "status": true,
  "data": {
    "id": "IDTrxExample123456789",
    "reff_id": "reffexample123",
    "layanan": "Data 10 GB / 30 Hari",
    "code": "KODE1",
    "target": "0856123456789",
    "price": "58507",
    "sn": "xxxxxxxxxxxx",
    "status": "success",
    "created_at": "2023-12-26 11:00:20"
  },
  "code": 200
}</code></pre>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Parameter</h3>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100"><tr><th class="px-4 py-2 text-left">Nama</th><th class="px-4 py-2 text-left">Tipe</th><th class="px-4 py-2 text-left">Deskripsi</th></tr></thead>
                            <tbody>
                                <tr><td class="px-4 py-2 font-mono">api_key</td><td>string</td><td>API Key</td></tr>
                                <tr><td class="px-4 py-2 font-mono">id</td><td>string</td><td>ID transaksi</td></tr>
                                <tr><td class="px-4 py-2 font-mono">type</td><td>string</td><td>Tipe (prabayar)</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; 2025 Atlantic API Proxy. Dibuat untuk keperluan dokumentasi dan proxy.
        </div>
    </footer>

    <script>
        function copyCode(btn) {
            const code = btn.parentElement.querySelector('code').innerText;
            navigator.clipboard.writeText(code).then(() => {
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> <span>Tersalin</span>';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = original;
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
    </script>
</body>
</html>
<?php
}
?>

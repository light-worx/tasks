{{-- resources/views/pwa-app/signin.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — {{ config('pwa.app_name') }}</title>
    <link href="{{ asset('pwa/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('pwa/css/app.css') }}" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: {{ config('pwa.theme.body_bg', '#f5f6f8') }};
            padding: 1.5rem;
        }
        .signin-card { max-width: 360px; width: 100%; }
        #pwa-toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(20px);
            background: #1f2937; color: #fff; padding: 10px 20px; border-radius: 24px;
            font-size: .875rem; opacity: 0; transition: opacity .25s ease, transform .25s ease;
            pointer-events: none; white-space: nowrap;
        }
        #pwa-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        #pwa-toast.error { background: #dc2626; }
    </style>
</head>
<body>
    <div class="signin-card">
        <h1 class="h4 mb-1 text-center">{{ config('pwa.app_name') }}</h1>
        <p class="text-muted text-center mb-4">Sign in with your email to see your tasks.</p>

        <div id="stepEmail">
            <label class="form-label small">Email address</label>
            <input type="email" id="emailInput" class="form-control mb-3" placeholder="you@example.com" autocomplete="email">
            <button class="btn btn-dark w-100" id="sendPinBtn">Send code</button>
        </div>

        <div id="stepPin" class="d-none">
            <p class="small text-muted">We sent a 4-digit code to <strong id="pinEmailLabel"></strong>.</p>
            <input type="text" id="pinInput" class="form-control mb-3 text-center" maxlength="4" inputmode="numeric" placeholder="0000">
            <button class="btn btn-dark w-100 mb-2" id="confirmPinBtn">Confirm</button>
            <button class="btn btn-link w-100 btn-sm" id="backBtn">Use a different email</button>
        </div>
    </div>

    <div id="pwa-toast"></div>

    <script>
    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function deviceId() {
            let id = localStorage.getItem('pwa_device_id');
            if (!id) {
                id = crypto.randomUUID?.() ?? (Date.now() + Math.random()).toString(36);
                localStorage.setItem('pwa_device_id', id);
            }
            document.cookie = `pwa_device_id=${encodeURIComponent(id)}; max-age=${60 * 60 * 24 * 365}; path=/; SameSite=Lax`;
            return id;
        }

        function toast(message, type = 'info') {
            const el = document.getElementById('pwa-toast');
            el.textContent = message;
            el.className = type === 'error' ? 'show error' : 'show';
            setTimeout(() => { el.className = ''; }, 3000);
        }

        async function post(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Something went wrong.');
            return data;
        }

        let currentEmail = '';

        document.getElementById('sendPinBtn').addEventListener('click', async () => {
            const email = document.getElementById('emailInput').value.trim();
            if (!email) return toast('Enter your email address.', 'error');

            try {
                await post('/signin/send-pin', { device_id: deviceId(), email });
                currentEmail = email;
                document.getElementById('pinEmailLabel').textContent = email;
                document.getElementById('stepEmail').classList.add('d-none');
                document.getElementById('stepPin').classList.remove('d-none');
                document.getElementById('pinInput').focus();
            } catch (e) {
                toast(e.message, 'error');
            }
        });

        document.getElementById('confirmPinBtn').addEventListener('click', async () => {
            const pin = document.getElementById('pinInput').value.trim();
            if (pin.length !== 4) return toast('Enter the 4-digit code.', 'error');

            try {
                await post('/signin/confirm-pin', { device_id: deviceId(), pin });
                window.location.href = '/';
            } catch (e) {
                toast(e.message, 'error');
            }
        });

        document.getElementById('backBtn').addEventListener('click', () => {
            document.getElementById('stepPin').classList.add('d-none');
            document.getElementById('stepEmail').classList.remove('d-none');
        });
    })();
    </script>
</body>
</html>
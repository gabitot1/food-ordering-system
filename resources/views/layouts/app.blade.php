<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module">
        import 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.12/dist/turbo.es2017-esm.min.js';
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- NAV LINK STYLE --}}
    <style>
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -6px;
            width: 0%;
            height: 2px;
            background: #16a34a;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #111827;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="font-sans antialiased">

<div class="min-h-screen bg-gray-100">

    @include('layouts.navigation')

    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main>
        {{ $slot }}
        {{-- {{ $slot ?? '' }}
        @yield('content') --}}
    </main>
    @php
        $serverFlash = [
            'success' => session('success'),
            'error' => session('error'),
            'status' => session('status'),
            'validation' => $errors->any() ? $errors->first() : null,
        ];
    @endphp
    <script id="server-flash" type="application/json">{!! json_encode($serverFlash) !!}</script>

    {{-- GLOBAL LOADING --}}
    {{-- <div id="globalCartLoading"
         class="fixed inset-0 bg-black/40 flex items-center justify-center hidden z-50">
        <div class="bg-white p-8 rounded-2xl shadow-xl flex flex-col items-center gap-4">
            <div class="spinner"></div>
            <p class="text-sm text-gray-600 font-medium">
                Adding to cart...
            </p>
        </div>
    </div> --}}

    {{-- GLOBAL TOAST --}}
    {{-- <div id="globalCartToast"
         class="fixed top-6 right-6 bg-gray-900 text-white px-6 py-3 rounded-2xl
                shadow-xl opacity-0 translate-y-[-20px]
                transition-all duration-300 z-50">
        <div class="flex items-center gap-3">
            <span>🛒</span>
            <span>Added to cart</span>
        </div>
    </div>

</div> --}}

{{-- ✅ SCRIPT MUST BE INSIDE BODY --}}
<script>
if (!window.__globalCartSubmitBound) {
window.__globalCartSubmitBound = true;

function showFallbackCartToast() {
    let toast = document.getElementById('fallbackCartToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'fallbackCartToast';
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.background = '#111827';
        toast.style.color = '#fff';
        toast.style.padding = '10px 14px';
        toast.style.borderRadius = '12px';
        toast.style.boxShadow = '0 10px 25px rgba(0,0,0,.2)';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-12px)';
        toast.style.transition = 'all .25s ease';
        toast.innerHTML = '<span style="margin-right:6px">🛒</span> Added to cart';
        document.body.appendChild(toast);
    }

    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-12px)';
    }, 1800);
}

document.addEventListener('submit', function(e) {

    if (e.target.classList.contains('global-add-to-cart')) {

        e.preventDefault();

        const form = e.target;
        if (form.dataset.submitting === '1') return;
        form.dataset.submitting = '1';
        const loading = document.getElementById('globalCartLoading');
        const toast = document.getElementById('globalCartToast');

        if (loading) {
            loading.classList.remove('hidden');
        }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(() => {

            if (loading) {
                loading.classList.add('hidden');
            }

            if (toast) {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-20px)';
                }, 2000);
            } else {
                showFallbackCartToast();
            }
        })
        .catch(() => {
            if (loading) {
                loading.classList.add('hidden');
            }
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Request failed',
                    text: 'Something went wrong.',
                    confirmButtonColor: '#16a34a'
                });
            }
        })
        .finally(() => {
            form.dataset.submitting = '0';
        });
    }
});
}
</script>
<script>
window.addEventListener('DOMContentLoaded', function () {

    let lastStatuses = {};
    let notificationCount = 0;

    function playSound() {
        let audio = new Audio("/notification.mp3");
        audio.volume = 1;
        audio.play().catch(() => {});
    }

    function showBadge() {
        let badge = document.getElementById('notifBadge');
        let count = document.getElementById('notifCount');

        if(badge && count){
            notificationCount++;
            badge.classList.remove('hidden');
            count.innerText = notificationCount;
        }
    }

    // Load initial state
    fetch("{{ route('check.order.status') }}")
    .then(res => res.json())
    .then(data => {
        data.forEach(order => {
            lastStatuses[order.id] = order.status;
        });
    });

    // Check every 3 seconds (faster for testing)
    setInterval(function(){

        fetch("{{ route('check.order.status') }}")
        .then(res => res.json())
        .then(data => {

            data.forEach(order => {

                if(lastStatuses[order.id] !== undefined &&
                   lastStatuses[order.id] != order.status){

                    console.log("Detected change:",
                        lastStatuses[order.id],
                        "→",
                        order.status
                    );

                    playSound();
                    showBadge();

                    lastStatuses[order.id] = order.status;
                }

            });

        });

    }, 3000);

});
</script>
<script>
function showGlobalFlashAlert() {
    if (!window.Swal) return;
    if (document.getElementById('disableGlobalFlash')) return;
    const flashNode = document.getElementById('server-flash');
    if (!flashNode) return;

    let flash = {};
    try {
        flash = JSON.parse(flashNode.textContent || '{}');
    } catch (e) {
        flash = {};
    }

    // Consume flash once so Turbo cache/restores won't replay old alerts.
    flashNode.remove();

    const flashSuccess = flash.success || null;
    const flashError = flash.error || null;
    const flashStatus = flash.status || null;
    const firstValidationError = flash.validation || null;

    if (flashSuccess) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: flashSuccess,
            confirmButtonColor: '#16a34a'
        });
        return;
    }

    if (flashError) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: flashError,
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    if (flashStatus) {
        Swal.fire({
            icon: 'info',
            title: 'Notice',
            text: flashStatus,
            confirmButtonColor: '#16a34a'
        });
        return;
    }

    if (firstValidationError) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation error',
            text: firstValidationError,
            confirmButtonColor: '#f59e0b'
        });
    }
}

document.addEventListener('DOMContentLoaded', showGlobalFlashAlert);
document.addEventListener('turbo:load', showGlobalFlashAlert);
document.addEventListener('turbo:before-cache', function () {
    const flashNode = document.getElementById('server-flash');
    if (flashNode) flashNode.remove();
    if (window.Swal) Swal.close();
});
</script>
<script>
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.hasAttribute('data-swal-confirm')) return;
    if (form.dataset.swalConfirmed === '1') return;

    e.preventDefault();
    if (!window.Swal) {
        form.submit();
        return;
    }

    const title = form.getAttribute('data-swal-title') || 'Are you sure?';
    const text = form.getAttribute('data-swal-text') || 'This action cannot be undone.';
    const confirmText = form.getAttribute('data-swal-confirm-text') || 'Yes, continue';
    const cancelText = form.getAttribute('data-swal-cancel-text') || 'Cancel';
    const icon = form.getAttribute('data-swal-icon') || 'warning';

    Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
    }).then((result) => {
        if (result.isConfirmed) {
            form.dataset.swalConfirmed = '1';
            form.submit();
        }
    });
});
</script>
</body>

</html>

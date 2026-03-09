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

        .swal2-popup.swal-mobile-popup {
            width: min(22rem, calc(100vw - 1.8rem));
            padding: 0.85rem;
            border-radius: 0.85rem;
        }

        .swal2-title.swal-mobile-title {
            font-size: 0.95rem;
            line-height: 1.35;
        }

        .swal2-html-container.swal-mobile-text {
            font-size: 0.8rem;
            line-height: 1.45;
            margin-top: 0.45rem;
        }

        .swal2-actions.swal-mobile-actions {
            gap: 0.5rem;
            margin-top: 0.9rem;
        }

        .swal2-confirm.swal-mobile-button,
        .swal2-cancel.swal-mobile-button {
            font-size: 0.75rem;
            padding: 0.45rem 0.8rem;
            border-radius: 0.65rem;
        }

        @media (min-width: 640px) {
            .swal2-popup.swal-mobile-popup {
                padding: 1rem;
            }

            .swal2-title.swal-mobile-title {
                font-size: 1rem;
            }
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
function getSwalBaseOptions(extra = {}) {
    const isSmallScreen = window.matchMedia('(max-width: 640px)').matches;

    return {
        width: isSmallScreen ? 'calc(100vw - 1.25rem)' : '24rem',
        padding: isSmallScreen ? '0.9rem' : '1rem',
        customClass: {
            popup: 'swal-mobile-popup',
            title: 'swal-mobile-title',
            htmlContainer: 'swal-mobile-text',
            actions: 'swal-mobile-actions',
            confirmButton: 'swal-mobile-button',
            cancelButton: 'swal-mobile-button',
        },
        ...extra,
    };
}

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
                Swal.fire(getSwalBaseOptions({
                    icon: 'error',
                    title: 'Request failed',
                    text: 'Something went wrong.',
                    confirmButtonColor: '#16a34a'
                }));
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
if (!window.__orderStatusMonitor) {
    window.__orderStatusMonitor = {
        started: false,
        pollTimer: null,
        inFlight: false,
        lastStatuses: {},
        notificationCount: 0,
        audioUnlocked: false,
        audio: null,
    };
}

function startOrderStatusMonitor() {
    const monitor = window.__orderStatusMonitor;
    const notificationWrapper = document.getElementById('notificationWrapper');

    if (!notificationWrapper) {
        clearTimeout(monitor.pollTimer);
        monitor.pollTimer = null;
        monitor.inFlight = false;
        return;
    }

    const statusColorMap = {
        pending: 'text-yellow-600',
        preparing: 'text-blue-600',
        out_of_delivery: 'text-purple-600',
        delivered: 'text-green-600',
        completed: 'text-green-600',
        cancelled: 'text-red-600',
    };

    if (!monitor.audio) {
        monitor.audio = new Audio('/notification.mp3');
        monitor.audio.preload = 'auto';
        monitor.audio.volume = 1;
    }

    function unlockAudio() {
        if (monitor.audioUnlocked || !monitor.audio) return;

        monitor.audio.muted = true;
        monitor.audio.play()
            .then(() => {
                monitor.audio.pause();
                monitor.audio.currentTime = 0;
                monitor.audio.muted = false;
                monitor.audioUnlocked = true;
            })
            .catch(() => {});
    }

    if (!monitor.started) {
        monitor.started = true;
        document.addEventListener('click', unlockAudio, { passive: true });
        document.addEventListener('touchstart', unlockAudio, { passive: true });
        document.addEventListener('keydown', unlockAudio, { passive: true });
    }

    function playSound() {
        if (!monitor.audio) return;

        monitor.audio.currentTime = 0;
        monitor.audio.play().catch(() => {});
    }

    function showBadge() {
        const badge = document.getElementById('notifBadge');
        const count = document.getElementById('notifCount');

        if (badge && count) {
            monitor.notificationCount++;
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
            count.innerText = monitor.notificationCount;
        }
    }

    function updateNotificationRow(order) {
        const statusNode = document.querySelector('[data-order-status="' + order.id + '"]');

        if (!statusNode) return;

        statusNode.className = 'text-[11px] sm:text-xs ' + (statusColorMap[order.status] || 'text-gray-600');
        statusNode.textContent = order.status_label;
    }

    async function pollStatuses() {
        if (monitor.inFlight || document.visibilityState === 'hidden') {
            scheduleNextPoll();
            return;
        }

        monitor.inFlight = true;

        try {
            const res = await fetch("{{ route('check.order.status') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                cache: 'no-store',
            });

            if (!res.ok) {
                throw new Error('Polling failed with status ' + res.status);
            }

            const data = await res.json();

            data.forEach(order => {
                const previousStatus = monitor.lastStatuses[order.id];

                if (previousStatus !== undefined && previousStatus !== order.status) {
                    playSound();
                    showBadge();
                    updateNotificationRow(order);
                }

                monitor.lastStatuses[order.id] = order.status;
            });
        } catch (error) {
            console.error('Order status polling error:', error);
        } finally {
            monitor.inFlight = false;
            scheduleNextPoll();
        }
    }

    function scheduleNextPoll() {
        clearTimeout(monitor.pollTimer);
        monitor.pollTimer = setTimeout(pollStatuses, 10000);
    }

    if (!monitor.pollTimer) {
        pollStatuses();
    }
}

document.addEventListener('DOMContentLoaded', startOrderStatusMonitor);
document.addEventListener('turbo:load', startOrderStatusMonitor);
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
        Swal.fire(getSwalBaseOptions({
            icon: 'success',
            title: 'Success',
            text: flashSuccess,
            confirmButtonColor: '#16a34a'
        }));
        return;
    }

    if (flashError) {
        Swal.fire(getSwalBaseOptions({
            icon: 'error',
            title: 'Error',
            text: flashError,
            confirmButtonColor: '#dc2626'
        }));
        return;
    }

    if (flashStatus) {
        Swal.fire(getSwalBaseOptions({
            icon: 'info',
            title: 'Notice',
            text: flashStatus,
            confirmButtonColor: '#16a34a'
        }));
        return;
    }

    if (firstValidationError) {
        Swal.fire(getSwalBaseOptions({
            icon: 'warning',
            title: 'Validation error',
            text: firstValidationError,
            confirmButtonColor: '#f59e0b'
        }));
    }
}

if (!window.__globalFlashHandlersBound) {
    window.__globalFlashHandlersBound = true;
    document.addEventListener('DOMContentLoaded', showGlobalFlashAlert);
    document.addEventListener('turbo:load', showGlobalFlashAlert);
    document.addEventListener('turbo:before-cache', function () {
        const flashNode = document.getElementById('server-flash');
        if (flashNode) flashNode.remove();

        document.querySelectorAll('form[data-swal-confirm]').forEach((form) => {
            delete form.dataset.swalConfirmed;
        });

        if (window.Swal) Swal.close();
    });
}
</script>
<script>
if (!window.__swalConfirmBound) {
    window.__swalConfirmBound = true;
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute('data-swal-confirm')) return;
        if (form.dataset.swalConfirmed === '1') return;

        e.preventDefault();
        if (!window.Swal) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }

        const title = form.getAttribute('data-swal-title') || 'Are you sure?';
        const text = form.getAttribute('data-swal-text') || 'This action cannot be undone.';
        const confirmText = form.getAttribute('data-swal-confirm-text') || 'Yes, continue';
        const cancelText = form.getAttribute('data-swal-cancel-text') || 'Cancel';
        const icon = form.getAttribute('data-swal-icon') || 'warning';

        Swal.fire(getSwalBaseOptions({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
        })).then((result) => {
            if (result.isConfirmed) {
                form.dataset.swalConfirmed = '1';
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    });
}
</script>
</body>

</html>

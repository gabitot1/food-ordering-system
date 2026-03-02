<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script type="module">
            import 'https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.12/dist/turbo.es2017-esm.min.js';
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @php
            $serverFlash = [
                'success' => session('success'),
                'error' => session('error'),
                'status' => session('status'),
                'validation' => $errors->any() ? $errors->first() : null,
            ];
        @endphp
        <script id="server-flash" type="application/json">{!! json_encode($serverFlash) !!}</script>
        <script>
            function showGuestFlashAlert() {
                if (!window.Swal) return;
                const flashNode = document.getElementById('server-flash');
                if (!flashNode) return;

                let flash = {};
                try {
                    flash = JSON.parse(flashNode.textContent || '{}');
                } catch (e) {
                    flash = {};
                }

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

            document.addEventListener('DOMContentLoaded', showGuestFlashAlert);
            document.addEventListener('turbo:load', showGuestFlashAlert);
            document.addEventListener('turbo:before-cache', function () {
                const flashNode = document.getElementById('server-flash');
                if (flashNode) flashNode.remove();
                if (window.Swal) Swal.close();
            });
        </script>
    </body>
</html>

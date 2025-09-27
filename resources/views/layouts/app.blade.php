<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Estados y Municipios COPOMEX'))</title>


    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .btn {
            font-weight: 500;
            transition: all 0.2s ease-in-out;
            border-radius: 0.375rem;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .table {
            background: white;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
        }

        .table-striped>tbody>tr:nth-of-type(odd)>td {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0b5ed7 100%);
            color: white;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        /* Loading states */
        .loading-spinner {
            display: none;
        }

        .btn.loading .loading-spinner {
            display: inline-block;
        }

        .btn.loading .btn-text {
            display: none;
        }

        /* DataTables customization */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #0b5ed7 !important;
            border-color: #0b5ed7 !important;
            color: white !important;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Status indicators */
        .status-success {
            color: var(--success-color);
        }

        .status-error {
            color: var(--danger-color);
        }

        .status-warning {
            color: var(--warning-color);
        }

        .status-info {
            color: var(--info-color);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                gap: 1rem;
            }

            .card-header .btn {
                width: 100%;
            }

            .modal-dialog {
                margin: 1rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
        }

        /* Loading overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .overlay.show {
            display: flex;
        }

        .overlay .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('estados.index') }}">
                <i class="fas fa-map-marker-alt me-2"></i>
                Estados y Municipios de México
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('estados.index') }}">
                            <i class="fas fa-home me-1"></i>
                            Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('health') }}" target="_blank">
                            <i class="fas fa-heartbeat me-1"></i>
                            Estado del Sistema
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            Herramientas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="limpiarCache('estados')">
                                    <i class="fas fa-broom me-2"></i>Limpiar Cache Estados
                                </a></li>
                            <li><a class="dropdown-item" href="#" onclick="limpiarCache('all')">
                                    <i class="fas fa-trash-alt me-2"></i>Limpiar Todo Cache
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('api.health') }}" target="_blank">
                                    <i class="fas fa-code me-2"></i>API Health
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('estados.index') }}">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                @yield('breadcrumb')
            </ol>
        </nav>

        <!-- Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-4 mt-5">
        <div class="container">
            <p class="mb-1">
                &copy; {{ date('Y') }} Estados y Municipios COPOMEX -
                <span class="text-primary">Laravel {{ app()->version() }}</span>
            </p>
            <p class="mb-0 small">
                Integración con API COPOMEX para obtener estados y municipios de México
            </p>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div class="overlay" id="loadingOverlay">
        <div class="text-center text-white">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Procesando...</p>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global JavaScript -->
    <script>
        $(document).ready(function() {
            // Configurar CSRF token para AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Inicializar tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Inicializar popovers
            $('[data-bs-toggle="popover"]').popover();

        });

        // Función global para mostrar notificaciones
        window.showNotification = function(type, title, message) {
            Swal.fire({
                icon: type,
                title: title,
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                showCloseButton: true
            });
        };

        // Función global para mostrar/ocultar loading
        window.showLoading = function(show = true) {
            if (show) {
                $('#loadingOverlay').addClass('show');
            } else {
                $('#loadingOverlay').removeClass('show');
            }
        };

        // Función para limpiar cache
        window.limpiarCache = function(tipo) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Se limpiará el cache de ${tipo}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading(true);

                    $.post('{{ route("estados.limpiar-cache") }}', {
                            tipo: tipo
                        })
                        .done(function(response) {
                            if (response.success) {
                                showNotification('success', '¡Éxito!', response.message);
                            } else {
                                showNotification('error', 'Error', response.message);
                            }
                        })
                        .fail(function(xhr) {
                            const message = xhr.responseJSON?.message || 'Error al limpiar cache';
                            showNotification('error', 'Error', message);
                        })
                        .always(function() {
                            showLoading(false);
                        });
                }
            });
        };

        // Manejo global de errores AJAX
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                url: settings.url,
                error: thrownError
            });

            showLoading(false);

            if (xhr.status !== 422) { // No mostrar para errores de validación
                let message = 'Error de conexión con el servidor';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    message = 'Sin conexión a internet';
                } else if (xhr.status === 404) {
                    message = 'Recurso no encontrado';
                } else if (xhr.status === 500) {
                    message = 'Error interno del servidor';
                }

                showNotification('error', 'Error', message);
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
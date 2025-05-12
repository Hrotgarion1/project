<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Denegado - Suspensión de Identidad</title>
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome CSS (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .card-header.bg-danger {
            font-weight: bold;
        }
        .lead {
            font-size: 1.25rem;
            color: #333;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <h5 class="mb-0">Acceso Denegado</h5>
                    </div>
                    <div class="card-body">
                        <h1 class="text-danger mb-3">403 - Prohibido</h1>
                        <p class="lead">{{ $message }}</p>
                        <p>Tu acceso a esta sección está restringido debido a la suspensión de la identidad <strong>{{ $identity_id }}</strong>.</p>

                        @if(isset($rule))
                            <div class="alert alert-warning" role="alert">
                                <h6 class="alert-heading">Detalles de la Restricción</h6>
                                <ul class="mb-0">
                                    <li><strong>Rol:</strong> {{ $rule->role_type }}</li>
                                    <li><strong>Tipo:</strong> {{ $rule->is_inviter ? 'Invitador' : 'Invitado' }}</li>
                                    <li><strong>Vista:</strong> {{ $rule->view }}</li>
                                    @if($rule->controller)
                                        <li><strong>Controlador:</strong> {{ $rule->controller }}</li>
                                    @endif
                                    @if($rule->function)
                                        <li><strong>Función:</strong> {{ $rule->function }}</li>
                                    @endif
                                </ul>
                            </div>
                        @endif

                        @if($identity = \App\Models\Identity::find($identity_id))
                            <p><strong>Motivo de la suspensión:</strong> {{ $identity->suspend_reason_code ?? 'No especificado' }}</p>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-home me-1"></i> Volver al Inicio
                            </a>
                            <a href="{{ route('identities.index') }}" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-id-card me-1"></i> Ver Mis Identidades
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (opcional, para interactividad básica) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
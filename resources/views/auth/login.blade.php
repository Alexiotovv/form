<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-card {
            max-width: 450px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background-color: white;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .logo-img {
            max-height: 70px;
            max-width: 180px;
            object-fit: contain;
        }
        .login-title {
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
        }
        .forgot-password {
            margin-top: 15px;
            text-align: center;
        }
        .forgot-password a {
            text-decoration: none;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .forgot-password a:hover {
            color: #0d6efd;
        }
        .modal-instructions {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
        }
        .copy-btn {
            cursor: pointer;
            transition: all 0.3s;
        }
        .copy-btn:hover {
            transform: scale(1.05);
        }
        .copied-message {
            font-size: 0.8rem;
            color: #198754;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .show-copied {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
<div class="container">
    <div class="login-card">
        <div class="logo-container">
            <!-- Cambia esta ruta por la de tu logo -->
            <img src="{{ asset('images/logo_diremid.png') }}" alt="Logo SISMED" class="logo-img">
        </div>
        
        <h3 class="login-title text-center">Iniciar Sesión</h3>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            @error('email')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-primary w-100 mt-3">Entrar</button>
            
            <div class="forgot-password">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Modal para contraseña olvidada -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Recuperar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Para recuperar tu contraseña, por favor envía un correo con la siguiente información:</p>
                
                <div class="modal-instructions">
                    <div class="mb-3">
                        <label class="form-label"><strong>Destinatario:</strong></label>
                        <div class="d-flex align-items-center">
                            <input type="text" class="form-control form-control-sm" id="emailTo" value="correo@gmail.com" readonly>
                            <button class="btn btn-sm btn-outline-secondary ms-2 copy-btn" onclick="copyToClipboard('emailTo')">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                            <span class="ms-2 copied-message" id="emailCopiedMsg"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Asunto:</strong></label>
                        <div class="d-flex align-items-center">
                            <input type="text" class="form-control form-control-sm" id="emailSubject" value="Olvidé mi contraseña" readonly>
                            <button class="btn btn-sm btn-outline-secondary ms-2 copy-btn" onclick="copyToClipboard('emailSubject')">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                            <span class="ms-2 copied-message" id="subjectCopiedMsg"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Contenido del correo:</strong></label>
                        <div class="d-flex">
                            <textarea class="form-control form-control-sm" id="emailContent" rows="4" readonly>Nombre del Establecimiento: [ESCRIBE AQUÍ EL NOMBRE]
Nombre del Responsable: [ESCRIBE AQUÍ EL NOMBRE]
Usuario: [ESCRIBE AQUÍ TU USUARIO O EMAIL]</textarea>
                            <button class="btn btn-sm btn-outline-secondary ms-2 copy-btn" onclick="copyToClipboard('emailContent')" style="height: fit-content;">
                                <i class="bi bi-clipboard"></i> Copiar
                            </button>
                        </div>
                        <span class="copied-message" id="contentCopiedMsg"></span>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="bi bi-info-circle"></i> 
                        <strong>Instrucciones:</strong> Copia cada sección y pégalas en tu cliente de correo. 
                        Completa la información entre corchetes [].
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="openEmailClient()">
                    <i class="bi bi-envelope"></i> Abrir Correo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons (para los iconos) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Función para copiar al portapapeles
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const messageId = elementId + 'CopiedMsg';
        const messageElement = document.getElementById(messageId);
        
        // Seleccionar el texto
        if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
            element.select();
            element.setSelectionRange(0, 99999); // Para dispositivos móviles
        }
        
        // Copiar al portapapeles
        navigator.clipboard.writeText(element.value).then(() => {
            // Mostrar mensaje de confirmación
            messageElement.textContent = '¡Copiado!';
            messageElement.classList.add('show-copied');
            
            // Ocultar mensaje después de 2 segundos
            setTimeout(() => {
                messageElement.classList.remove('show-copied');
            }, 2000);
        });
    }
    
    // Función para abrir cliente de correo
    function openEmailClient() {
        const emailTo = document.getElementById('emailTo').value;
        const emailSubject = document.getElementById('emailSubject').value;
        const emailContent = document.getElementById('emailContent').value;
        
        const mailtoLink = `mailto:${emailTo}?subject=${encodeURIComponent(emailSubject)}&body=${encodeURIComponent(emailContent)}`;
        
        window.location.href = mailtoLink;
        
        // Cerrar modal después de un breve retraso
        setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
            modal.hide();
        }, 500);
    }
    
    // Limpiar mensajes al cerrar el modal
    document.getElementById('forgotPasswordModal').addEventListener('hidden.bs.modal', function () {
        const messages = document.querySelectorAll('.copied-message');
        messages.forEach(msg => {
            msg.classList.remove('show-copied');
            msg.textContent = '';
        });
    });
</script>
</body>
</html>
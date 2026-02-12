<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Bejelentkezés - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shield-lock" style="font-size: 3rem;"></i>
            <h3 class="mt-3 mb-0">Admin Bejelentkezés</h3>
            <p class="mb-0 mt-2 opacity-75">Task Manager Rendszer</p>
        </div>
        <div class="login-body">
            <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="errorMessage"></span>
            </div>
            
            <form id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-2"></i>Email cím
                    </label>
                    <input type="email" class="form-control" id="email" required 
                           placeholder="pelda@email.com">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>Jelszó
                    </label>
                    <input type="password" class="form-control" id="password" required 
                           placeholder="Írja be a jelszavát">
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Bejelentkezés
                </button>
            </form>
            
            <div class="text-center mt-4 text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Csak adminisztrátorok számára
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = 'http://localhost:8000/api';

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Bejelentkezés...';
            
            // Hide error
            errorAlert.classList.add('d-none');
            
            try {
                const response = await fetch(`${API_URL}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Check if user is admin
                    if (data.user && (data.user.is_admin === 1 || data.user.is_admin === true)) {
                        // Save token to localStorage
                        localStorage.setItem('authToken', data.access_token);
                        localStorage.setItem('adminUser', JSON.stringify(data.user));
                        
                        // Redirect to admin users page
                        window.location.href = '/admin/users';
                    } else {
                        errorMessage.textContent = 'Csak adminisztrátorok jelentkezhetnek be!';
                        errorAlert.classList.remove('d-none');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Bejelentkezés';
                    }
                } else {
                    errorMessage.textContent = data.message || 'Hibás email vagy jelszó!';
                    errorAlert.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Bejelentkezés';
                }
            } catch (error) {
                errorMessage.textContent = 'Hálózati hiba történt. Kérjük próbálja újra!';
                errorAlert.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Bejelentkezés';
            }
        });
    </script>
</body>
</html>

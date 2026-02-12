<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard">
                <i class="bi bi-clipboard-check"></i> Task Manager Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/users">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/tasks">
                            <i class="bi bi-list-task"></i> Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/assignments">
                            <i class="bi bi-link-45deg"></i> Assignments
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-outline-light btn-sm ms-3" onclick="logout()">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = 'http://localhost:8000/api';
        let authToken = localStorage.getItem('authToken');
        const adminUser = JSON.parse(localStorage.getItem('adminUser') || '{}');
        
        // Check if user is logged in and is admin
        if (!authToken || !adminUser.is_admin) {
            localStorage.removeItem('authToken');
            localStorage.removeItem('adminUser');
            window.location.href = '/login';
        }

        function setAuthToken(token) {
            authToken = token;
            localStorage.setItem('authToken', token);
        }

        function logout() {
            if (confirm('Biztosan ki szeretnél jelentkezni?')) {
                fetch(`${API_BASE}/logout`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    localStorage.removeItem('authToken');
                    localStorage.removeItem('adminUser');
                    window.location.href = '/login';
                });
            }
        }

        // Check if user is authenticated
        if (!authToken) {
            alert('Nincs bejelentkezve! Kérem jelentkezzen be.');
            window.location.href = '/';
        }
    </script>
    @stack('scripts')
</body>
</html>

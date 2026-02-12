@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h1><i class="bi bi-people"></i> Felhasználók kezelése</h1>
        <hr>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-plus-circle"></i> Új felhasználó
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Név</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Phone</th>
                                <th>Admin</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Betöltés...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Új felhasználó létrehozása</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    <div class="mb-3">
                        <label class="form-label">Név *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jelszó *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" name="department">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_admin" id="isAdmin">
                        <label class="form-check-label" for="isAdmin">Admin jogosultság</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary" onclick="createUser()">Létrehozás</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Felhasználó szerkesztése</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label class="form-label">Név</label>
                        <input type="text" class="form-control" name="name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Új jelszó (opcionális)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" name="department">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_admin" id="isAdminEdit">
                        <label class="form-check-label" for="isAdminEdit">Admin jogosultság</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary" onclick="updateUser()">Mentés</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let users = [];
    let editUserModal, createUserModal;

    document.addEventListener('DOMContentLoaded', function() {
        editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        createUserModal = new bootstrap.Modal(document.getElementById('createUserModal'));
        loadUsers();
    });

    function loadUsers() {
        fetch(`${API_BASE}/admin/users`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            users = data.users;
            renderUsers();
        })
        .catch(err => {
            alert('Hiba történt a felhasználók betöltésekor!');
            console.error(err);
        });
    }

    function renderUsers() {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = users.map(user => `
            <tr>
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.department || '-'}</td>
                <td>${user.phone || '-'}</td>
                <td>${user.is_admin ? '<span class="badge bg-success">Admin</span>' : '<span class="badge bg-secondary">User</span>'}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="showEditUser(${user.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function createUser() {
        const form = document.getElementById('createUserForm');
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            password: formData.get('password'),
            department: formData.get('department'),
            phone: formData.get('phone'),
            is_admin: formData.get('is_admin') ? true : false
        };

        fetch(`${API_BASE}/admin/users`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            createUserModal.hide();
            form.reset();
            loadUsers();
            alert('Felhasználó sikeresen létrehozva!');
        })
        .catch(err => {
            alert('Hiba történt a felhasználó létrehozásakor!');
            console.error(err);
        });
    }

    function showEditUser(id) {
        const user = users.find(u => u.id === id);
        const form = document.getElementById('editUserForm');
        form.querySelector('[name="id"]').value = user.id;
        form.querySelector('[name="name"]').value = user.name;
        form.querySelector('[name="email"]').value = user.email;
        form.querySelector('[name="department"]').value = user.department || '';
        form.querySelector('[name="phone"]').value = user.phone || '';
        form.querySelector('[name="is_admin"]').checked = user.is_admin;
        editUserModal.show();
    }

    function updateUser() {
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);
        const id = formData.get('id');
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            department: formData.get('department'),
            phone: formData.get('phone'),
            is_admin: formData.get('is_admin') ? true : false
        };

        if (formData.get('password')) {
            data.password = formData.get('password');
        }

        fetch(`${API_BASE}/admin/users/${id}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            editUserModal.hide();
            loadUsers();
            alert('Felhasználó sikeresen frissítve!');
        })
        .catch(err => {
            alert('Hiba történt a felhasználó frissítésekor!');
            console.error(err);
        });
    }

    function deleteUser(id) {
        if (!confirm('Biztosan törölni szeretnéd ezt a felhasználót?')) return;

        fetch(`${API_BASE}/admin/users/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loadUsers();
            alert('Felhasználó sikeresen törölve!');
        })
        .catch(err => {
            alert('Hiba történt a felhasználó törlésekor!');
            console.error(err);
        });
    }
</script>
@endpush

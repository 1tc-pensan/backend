@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h1><i class="bi bi-link-45deg"></i> Feladat hozzárendelések</h1>
        <hr>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
            <i class="bi bi-plus-circle"></i> Új hozzárendelés
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
                                <th>Felhasználó</th>
                                <th>Feladat</th>
                                <th>Hozzárendelve</th>
                                <th>Befejezve</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody id="assignmentsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
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

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Új hozzárendelés létrehozása</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createAssignmentForm">
                    <div class="mb-3">
                        <label class="form-label">Felhasználó *</label>
                        <select class="form-select" name="user_id" required id="userSelect">
                            <option value="">Válassz...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feladat *</label>
                        <select class="form-select" name="task_id" required id="taskSelect">
                            <option value="">Válassz...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hozzárendelés dátuma</label>
                        <input type="datetime-local" class="form-control" name="assigned_at">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary" onclick="createAssignment()">Létrehozás</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hozzárendelés szerkesztése</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAssignmentForm">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label class="form-label">Felhasználó</label>
                        <select class="form-select" name="user_id" id="userSelectEdit">
                            <option value="">Válassz...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feladat</label>
                        <select class="form-select" name="task_id" id="taskSelectEdit">
                            <option value="">Válassz...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hozzárendelés dátuma</label>
                        <input type="datetime-local" class="form-control" name="assigned_at">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Befejezés dátuma</label>
                        <input type="datetime-local" class="form-control" name="completed_at">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary" onclick="updateAssignment()">Mentés</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let assignments = [];
    let users = [];
    let tasks = [];
    let editAssignmentModal, createAssignmentModal;

    document.addEventListener('DOMContentLoaded', function() {
        editAssignmentModal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
        createAssignmentModal = new bootstrap.Modal(document.getElementById('createAssignmentModal'));
        loadData();
    });

    async function loadData() {
        await Promise.all([loadUsers(), loadTasks()]);
        loadAssignments();
    }

    function loadUsers() {
        return fetch(`${API_BASE}/admin/users`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            users = data.users;
            populateUserSelects();
        });
    }

    function loadTasks() {
        return fetch(`${API_BASE}/admin/tasks`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            tasks = data.tasks;
            populateTaskSelects();
        });
    }

    function loadAssignments() {
        fetch(`${API_BASE}/admin/assignments`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            assignments = data.assignments;
            renderAssignments();
        })
        .catch(err => {
            alert('Hiba történt a hozzárendelések betöltésekor!');
            console.error(err);
        });
    }

    function populateUserSelects() {
        const html = users.map(u => `<option value="${u.id}">${u.name} (${u.email})</option>`).join('');
        document.getElementById('userSelect').innerHTML += html;
        document.getElementById('userSelectEdit').innerHTML += html;
    }

    function populateTaskSelects() {
        const html = tasks.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        document.getElementById('taskSelect').innerHTML += html;
        document.getElementById('taskSelectEdit').innerHTML += html;
    }

    function renderAssignments() {
        const tbody = document.getElementById('assignmentsTableBody');
        tbody.innerHTML = assignments.map(assignment => `
            <tr>
                <td>${assignment.id}</td>
                <td>${assignment.user?.name || 'N/A'}</td>
                <td>${assignment.task?.title || 'N/A'}</td>
                <td>${formatDateTime(assignment.assigned_at)}</td>
                <td>${assignment.completed_at ? formatDateTime(assignment.completed_at) : '<span class="badge bg-warning">Folyamatban</span>'}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="showEditAssignment(${assignment.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteAssignment(${assignment.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleString('hu-HU');
    }

    function createAssignment() {
        const form = document.getElementById('createAssignmentForm');
        const formData = new FormData(form);
        const data = {
            user_id: parseInt(formData.get('user_id')),
            task_id: parseInt(formData.get('task_id')),
            assigned_at: formData.get('assigned_at') || undefined
        };

        fetch(`${API_BASE}/admin/assignments`, {
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
            createAssignmentModal.hide();
            form.reset();
            loadAssignments();
            alert('Hozzárendelés sikeresen létrehozva!');
        })
        .catch(err => {
            alert('Hiba történt a hozzárendelés létrehozásakor!');
            console.error(err);
        });
    }

    function showEditAssignment(id) {
        const assignment = assignments.find(a => a.id === id);
        const form = document.getElementById('editAssignmentForm');
        form.querySelector('[name="id"]').value = assignment.id;
        form.querySelector('[name="user_id"]').value = assignment.user_id;
        form.querySelector('[name="task_id"]').value = assignment.task_id;
        form.querySelector('[name="assigned_at"]').value = assignment.assigned_at ? new Date(assignment.assigned_at).toISOString().slice(0, 16) : '';
        form.querySelector('[name="completed_at"]').value = assignment.completed_at ? new Date(assignment.completed_at).toISOString().slice(0, 16) : '';
        editAssignmentModal.show();
    }

    function updateAssignment() {
        const form = document.getElementById('editAssignmentForm');
        const formData = new FormData(form);
        const id = formData.get('id');
        const data = {
            user_id: parseInt(formData.get('user_id')),
            task_id: parseInt(formData.get('task_id')),
            assigned_at: formData.get('assigned_at') || undefined,
            completed_at: formData.get('completed_at') || null
        };

        fetch(`${API_BASE}/admin/assignments/${id}`, {
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
            editAssignmentModal.hide();
            loadAssignments();
            alert('Hozzárendelés sikeresen frissítve!');
        })
        .catch(err => {
            alert('Hiba történt a hozzárendelés frissítésekor!');
            console.error(err);
        });
    }

    function deleteAssignment(id) {
        if (!confirm('Biztosan törölni szeretnéd ezt a hozzárendelést?')) return;

        fetch(`${API_BASE}/admin/assignments/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loadAssignments();
            alert('Hozzárendelés sikeresen törölve!');
        })
        .catch(err => {
            alert('Hiba történt a hozzárendelés törlésekor!');
            console.error(err);
        });
    }
</script>
@endpush

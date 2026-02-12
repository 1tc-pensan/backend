@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <h1><i class="bi bi-list-task"></i> Feladatok kezelése</h1>
        <hr>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
            <i class="bi bi-plus-circle"></i> Új feladat
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
                                <th>Cím</th>
                                <th>Prioritás</th>
                                <th>Státusz</th>
                                <th>Határidő</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody id="tasksTableBody">
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

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Új feladat létrehozása</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createTaskForm">
                    <div class="mb-3">
                        <label class="form-label">Cím *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Leírás</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prioritás *</label>
                        <select class="form-select" name="priority" required>
                            <option value="low">Alacsony</option>
                            <option value="medium" selected>Közepes</option>
                            <option value="high">Magas</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Státusz *</label>
                        <select class="form-select" name="status" required>
                            <option value="pending" selected>Függőben</option>
                            <option value="in_progress">Folyamatban</option>
                            <option value="completed">Kész</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Határidő</label>
                        <input type="date" class="form-control" name="due_date">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary" onclick="createTask()">Létrehozás</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Feladat szerkesztése</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editTaskForm">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label class="form-label">Cím</label>
                        <input type="text" class="form-control" name="title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Leírás</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prioritás</label>
                        <select class="form-select" name="priority">
                            <option value="low">Alacsony</option>
                            <option value="medium">Közepes</option>
                            <option value="high">Magas</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Státusz</label>
                        <select class="form-select" name="status">
                            <option value="pending">Függőben</option>
                            <option value="in_progress">Folyamatban</option>
                            <option value="completed">Kész</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Határidő</label>
                        <input type="date" class="form-control" name="due_date">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                <button type="button" class="btn btn-primary" onclick="updateTask()">Mentés</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let tasks = [];
    let editTaskModal, createTaskModal;

    document.addEventListener('DOMContentLoaded', function() {
        editTaskModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
        createTaskModal = new bootstrap.Modal(document.getElementById('createTaskModal'));
        loadTasks();
    });

    function loadTasks() {
        fetch(`${API_BASE}/admin/tasks`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            tasks = data.tasks;
            renderTasks();
        })
        .catch(err => {
            alert('Hiba történt a feladatok betöltésekor!');
            console.error(err);
        });
    }

    function renderTasks() {
        const tbody = document.getElementById('tasksTableBody');
        tbody.innerHTML = tasks.map(task => `
            <tr>
                <td>${task.id}</td>
                <td>${task.title}</td>
                <td>${getPriorityBadge(task.priority)}</td>
                <td>${getStatusBadge(task.status)}</td>
                <td>${task.due_date || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="showEditTask(${task.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function getPriorityBadge(priority) {
        const badges = {
            'low': '<span class="badge bg-success">Alacsony</span>',
            'medium': '<span class="badge bg-warning">Közepes</span>',
            'high': '<span class="badge bg-danger">Magas</span>'
        };
        return badges[priority] || priority;
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-secondary">Függőben</span>',
            'in_progress': '<span class="badge bg-primary">Folyamatban</span>',
            'completed': '<span class="badge bg-success">Kész</span>'
        };
        return badges[status] || status;
    }

    function createTask() {
        const form = document.getElementById('createTaskForm');
        const formData = new FormData(form);
        const data = {
            title: formData.get('title'),
            description: formData.get('description'),
            priority: formData.get('priority'),
            status: formData.get('status'),
            due_date: formData.get('due_date')
        };

        fetch(`${API_BASE}/admin/tasks`, {
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
            createTaskModal.hide();
            form.reset();
            loadTasks();
            alert('Feladat sikeresen létrehozva!');
        })
        .catch(err => {
            alert('Hiba történt a feladat létrehozásakor!');
            console.error(err);
        });
    }

    function showEditTask(id) {
        const task = tasks.find(t => t.id === id);
        const form = document.getElementById('editTaskForm');
        form.querySelector('[name="id"]').value = task.id;
        form.querySelector('[name="title"]').value = task.title;
        form.querySelector('[name="description"]').value = task.description || '';
        form.querySelector('[name="priority"]').value = task.priority;
        form.querySelector('[name="status"]').value = task.status;
        form.querySelector('[name="due_date"]').value = task.due_date || '';
        editTaskModal.show();
    }

    function updateTask() {
        const form = document.getElementById('editTaskForm');
        const formData = new FormData(form);
        const id = formData.get('id');
        const data = {
            title: formData.get('title'),
            description: formData.get('description'),
            priority: formData.get('priority'),
            status: formData.get('status'),
            due_date: formData.get('due_date')
        };

        fetch(`${API_BASE}/admin/tasks/${id}`, {
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
            editTaskModal.hide();
            loadTasks();
            alert('Feladat sikeresen frissítve!');
        })
        .catch(err => {
            alert('Hiba történt a feladat frissítésekor!');
            console.error(err);
        });
    }

    function deleteTask(id) {
        if (!confirm('Biztosan törölni szeretnéd ezt a feladatot?')) return;

        fetch(`${API_BASE}/admin/tasks/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loadTasks();
            alert('Feladat sikeresen törölve!');
        })
        .catch(err => {
            alert('Hiba történt a feladat törlésekor!');
            console.error(err);
        });
    }
</script>
@endpush

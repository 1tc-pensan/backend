# Task Manager API - Postman Collection

## Importálás

1. Nyisd meg a Postmant
2. Kattints az **Import** gombra
3. Válaszd ki a `Task_Manager_API.postman_collection.json` fájlt
4. Kattints az **Import** gombra

## Environment beállítás

A collection két változót használ:
- `base_url` - Az API alap URL-je (alapértelmezett: `http://localhost:8000`)
- `auth_token` - Automatikusan beállítódik login után

### Environment létrehozása (opcionális)

1. Kattints a jobb felső sarokban lévő fogaskerékre
2. Add Hozzá: **Manage Environments** → **Add**
3. Név: `Task Manager Local`
4. Változók:
   - `base_url`: `http://localhost:8000`
   - `auth_token`: (üresen hagyható)

## API Végpontok

### 🌐 Public (Nem authentikált)

#### Ping Test
```
GET /api/ping
```
API működésének ellenőrzése

#### Register
```
POST /api/register
```
Új felhasználó regisztrálása. Automatikusan beállítja az `auth_token`-t.

**Body példa:**
```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Jelszo12",
    "password_confirmation": "Jelszo12",
    "department": "IT",
    "phone": "+36301234567"
}
```

#### Login - User
```
POST /api/login
```
Bejelentkezés normál felhasználóként. Automatikusan beállítja az `auth_token`-t.

**Body példa:**
```json
{
    "email": "test@example.com",
    "password": "Jelszo12"
}
```

#### Login - Admin
```
POST /api/login
```
Bejelentkezés admin felhasználóként. Automatikusan beállítja az `auth_token`-t.

**Body példa:**
```json
{
    "email": "Admin@taskmanger.hu",
    "password": "admin123"
}
```

---

### 🔐 Authenticated (Bejelentkezett felhasználók)

**Auth típus:** Bearer Token (`{{auth_token}}`)

#### Logout
```
POST /api/logout
```
Kijelentkezés (token törlése)

#### Get Profile
```
GET /api/profile
```
Saját profil adatok lekérése

#### Get My Tasks
```
GET /api/my-tasks
```
Saját feladatok listázása

#### Update Task Status
```
PATCH /api/tasks/{id}/status
```
Saját feladat státuszának frissítése (completed_at beállítása)

**Body példa:**
```json
{
    "completed_at": "2026-02-12 12:00:00"
}
```

---

### 👑 Admin - Users (Admin jogosultság szükséges)

**Auth típus:** Bearer Token (`{{auth_token}}`)

#### Get All Users
```
GET /api/admin/users
```
Összes felhasználó listázása (törölt felhasználókkal együtt)

#### Get User by ID
```
GET /api/admin/users/{id}
```
Adott felhasználó adatainak lekérése

#### Create User
```
POST /api/admin/users
```
Új felhasználó létrehozása

**Body példa:**
```json
{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "Jelszo12",
    "department": "Sales",
    "phone": "+36301234567",
    "is_admin": false
}
```

#### Update User
```
PUT /api/admin/users/{id}
```
Felhasználó adatainak módosítása

**Body példa:**
```json
{
    "name": "Updated Name",
    "department": "Marketing",
    "phone": "+36307654321"
}
```

#### Delete User
```
DELETE /api/admin/users/{id}
```
Felhasználó törlése (soft delete)

---

### 👑 Admin - Tasks (Admin jogosultság szükséges)

**Auth típus:** Bearer Token (`{{auth_token}}`)

#### Get All Tasks
```
GET /api/admin/tasks
```
Összes feladat listázása

#### Get Task by ID
```
GET /api/admin/tasks/{id}
```
Adott feladat adatainak lekérése

#### Create Task
```
POST /api/admin/tasks
```
Új feladat létrehozása

**Body példa:**
```json
{
    "title": "New Important Task",
    "description": "This is a detailed description of the task",
    "priority": "high",
    "due_date": "2026-02-20",
    "status": "pending"
}
```

**Priority értékek:** `low`, `medium`, `high`  
**Status értékek:** `pending`, `in_progress`, `completed`

#### Update Task
```
PUT /api/admin/tasks/{id}
```
Feladat módosítása

**Body példa:**
```json
{
    "title": "Updated Task Title",
    "status": "in_progress",
    "priority": "medium"
}
```

#### Delete Task
```
DELETE /api/admin/tasks/{id}
```
Feladat törlése (soft delete)

---

### 👑 Admin - Assignments (Admin jogosultság szükséges)

**Auth típus:** Bearer Token (`{{auth_token}}`)

#### Get All Assignments
```
GET /api/admin/assignments
```
Összes feladat-hozzárendelés listázása

#### Get Assignment by ID
```
GET /api/admin/assignments/{id}
```
Adott hozzárendelés adatainak lekérése

#### Create Assignment
```
POST /api/admin/assignments
```
Feladat hozzárendelése felhasználóhoz

**Body példa:**
```json
{
    "user_id": 1,
    "task_id": 1,
    "assigned_at": "2026-02-12 10:00:00"
}
```

#### Update Assignment
```
PUT /api/admin/assignments/{id}
```
Hozzárendelés módosítása

**Body példa:**
```json
{
    "completed_at": "2026-02-12 15:00:00"
}
```

#### Delete Assignment
```
DELETE /api/admin/assignments/{id}
```
Hozzárendelés törlése

#### Get Assignments by Task
```
GET /api/admin/tasks/{taskId}/assignments
```
Egy adott feladathoz tartozó összes hozzárendelés

#### Get Assignments by User
```
GET /api/admin/users/{userId}/assignments
```
Egy adott felhasználóhoz tartozó összes hozzárendelés

---

## Használati útmutató

### 1. Első lépések

1. **Indítsd el a Laravel szervert:**
   ```bash
   php artisan serve
   ```

2. **Importáld a collection-t** a Postmanbe

3. **Jelentkezz be Admin-ként:**
   - Használd a "Login - Admin" requestet
   - Email: `Admin@taskmanger.hu`
   - Jelszó: `admin123`
   - Az `auth_token` automatikusan beállítódik

### 2. Tesztelési folyamat

**Normál felhasználóként:**
1. Login - User
2. Get Profile
3. Get My Tasks
4. Update Task Status (ha van hozzárendelt task)
5. Logout

**Admin felhasználóként:**
1. Login - Admin
2. Nézd meg az összes felhasználót, tasket, assignmentet
3. Hozz létre új taskot
4. Rendelj hozzá taskot felhasználóhoz
4. Módosítsd a taskot
5. Logout

### 3. Response példák

**Sikeres login:**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "Admin@taskmanger.hu",
        "is_admin": true,
        ...
    },
    "access_token": "1|abc123...",
    "token_type": "Bearer"
}
```

**Hiba (401 Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

**Hiba (403 Forbidden - nem admin):**
```json
{
    "message": "Forbidden. Admin access required."
}
```

---

## Megjegyzések

- Minden admin endpoint Bearer Token authentikációt igényel
- Az admin endpointok csak `is_admin = true` felhasználók számára elérhetők
- A soft delete-elt elemek visszaállítására nincs endpoint (TODO)
- Az `auth_token` változó automatikusan frissül login után
- A collection tartalmazza a test script-eket az automatikus token beállításhoz

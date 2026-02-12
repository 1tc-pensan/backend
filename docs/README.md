# Task Manager API & Admin Panel

Teljes értékű Laravel 11 alapú feladatkezelő rendszer REST API-val és webes admin felülettel. Sanctum token-alapú autentikációval, szerepkör-alapú hozzáférés-kezeléssel és soft delete támogatással.

## 📋 Tartalomjegyzék

- [Áttekintés](#áttekintés)
- [Követelmények](#követelmények)
- [Telepítés](#telepítés)
- [Adatbázis Beállítás](#adatbázis-beállítás)
- [Használat](#használat)
- [API Dokumentáció](#api-dokumentáció)
- [Web Admin Felület](#web-admin-felület)
- [Tesztelés](#tesztelés)
- [Middleware](#middleware)
- [Modellek és Kapcsolatok](#modellek-és-kapcsolatok)

---

## 🎯 Áttekintés

A Task Manager egy modern, RESTful API-val rendelkező feladatkezelő alkalmazás, amely lehetővé teszi:

- **Felhasználók kezelése** (Admin jogosultsággal)
- **Feladatok létrehozása és nyomon követése**
- **Feladatok hozzárendelése felhasználókhoz**
- **Prioritások és státuszok kezelése**
- **Web-alapú admin felület** Bootstrap 5-tel
- **Token-alapú autentikáció** Laravel Sanctum-mal
- **Soft delete** minden entitáson

### Technológiai Stack

- **Backend:** Laravel 11
- **Autentikáció:** Laravel Sanctum
- **Adatbázis:** MySQL
- **Frontend:** Blade Templates + Bootstrap 5
- **Testing:** PHPUnit
- **API Documentation:** Postman Collection

---

## 💻 Követelmények

- PHP 8.2+
- Composer
- MySQL 5.7+ / MariaDB 10.3+
- XAMPP / Laragon / Herd (vagy hasonló PHP környezet)
- Node.js és NPM (opcionális, frontend asset-ekhez)

---

## 🚀 Telepítés

### 1. Repository Klónozása

```bash
cd c:\xampp\htdocs
git clone <repository-url> todo_sanctum
cd todo_sanctum
```

### 2. Függőségek Telepítése

```bash
composer install
```

### 3. Környezeti Változók Beállítása

Másold le a `.env.example` fájlt:

```bash
copy .env.example .env
```

Szerkeszd a `.env` fájlt:

```env
APP_NAME="Task Manager"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_sanctum
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Alkalmazás Kulcs Generálása

```bash
php artisan key:generate
```

---

## 🗄️ Adatbázis Beállítás

### 1. Adatbázis Létrehozása

Indítsd el az XAMPP-et és nyisd meg a phpMyAdmin-t:

```sql
CREATE DATABASE todo_sanctum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Migrációk Futtatása

```bash
php artisan migrate
```

Ez létrehozza a következő táblákat:
- `users` - Felhasználók
- `personal_access_tokens` - Sanctum token-ek
- `tasks` - Feladatok
- `task_assigments` - Feladatok hozzárendelései
- `cache`, `jobs`, `sessions` - Laravel rendszer táblák

### 3. Adatbázis Feltöltése Tesztadatokkal

```bash
php artisan db:seed
```

Ez létrehoz:
- **10 felhasználót** (9 regular + 1 admin)
- **10 feladatot** (különböző státuszokkal és prioritásokkal)
- **Hozzárendeléseket** (1-3 felhasználó per feladat)

### Alapértelmezett Admin Fiók

```
Email: Admin@taskmanger.hu
Jelszó: admin123
```

További admin fiók:
```
Email: admin2@taskmanger.hu
Jelszó: admin123
```

Regular felhasználók jelszava: `Jelszo12`

---

## 🎮 Használat

### Laravel Development Server Indítása

```bash
php artisan serve
```

Az alkalmazás elérhető lesz: `http://localhost:8000`

### Gyors Teszt (API Ping)

```bash
curl http://localhost:8000/api/ping
```

Válasz:
```json
{
    "message": "pong",
    "timestamp": "2026-02-12T10:30:00.000000Z"
}
```

---

## 📚 API Dokumentáció

### Base URL

```
http://localhost:8000/api
```

### Autentikáció

Az API Laravel Sanctum token-alapú autentikációt használ. Token megszerzése után minden védett endpoint-hoz add hozzá az `Authorization` headert:

```
Authorization: Bearer {token}
```

---

## 🔐 Autentikációs Végpontok

### 1. Regisztráció

```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "department": "IT",
    "phone": "+36201234567"
}
```

**Válasz:**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 11,
        "name": "John Doe",
        "email": "john@example.com",
        "is_admin": false,
        "department": "IT",
        "phone": "+36201234567"
    },
    "access_token": "1|abc123...",
    "token_type": "Bearer"
}
```

### 2. Bejelentkezés

```http
POST /api/login
Content-Type: application/json

{
    "email": "Admin@taskmanger.hu",
    "password": "admin123"
}
```

**Válasz:**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "Admin@taskmanger.hu",
        "is_admin": true
    },
    "access_token": "2|xyz789...",
    "token_type": "Bearer"
}
```

### 3. Kijelentkezés

```http
POST /api/logout
Authorization: Bearer {token}
```

**Válasz:**
```json
{
    "message": "Successfully logged out"
}
```

### 4. Profil Lekérése

```http
GET /api/user/profile
Authorization: Bearer {token}
```

---

## 👥 Felhasználó Végpontok (Auth Required)

### Saját Feladatok Lekérése

```http
GET /api/user/tasks
Authorization: Bearer {token}
```

Visszaadja az összes feladatot, amihez a felhasználó hozzá van rendelve.

### Feladat Státusz Frissítése

```http
PUT /api/user/tasks/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "in_progress"
}
```

Lehetséges státuszok: `pending`, `in_progress`, `completed`

---

## 🔧 Admin Végpontok

### Felhasználók Kezelése

#### Lista

```http
GET /api/admin/users
Authorization: Bearer {token}
X-Admin: true
```

#### Létrehozás

```http
POST /api/admin/users
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "is_admin": false,
    "department": "Sales",
    "phone": "+36301234567"
}
```

#### Részletek

```http
GET /api/admin/users/{id}
Authorization: Bearer {token}
```

#### Frissítés

```http
PUT /api/admin/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Name",
    "email": "updated@example.com",
    "department": "Marketing"
}
```

#### Törlés

```http
DELETE /api/admin/users/{id}
Authorization: Bearer {token}
```

**Megjegyzés:** Admin nem törölheti saját magát!

---

### Feladatok Kezelése (Admin)

#### Lista

```http
GET /api/admin/tasks
Authorization: Bearer {token}
```

#### Létrehozás

```http
POST /api/admin/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "New Task",
    "description": "Task description",
    "priority": "high",
    "status": "pending",
    "due_date": "2026-03-01"
}
```

**Prioritások:** `low`, `medium`, `high`
**Státuszok:** `pending`, `in_progress`, `completed`

#### Részletek

```http
GET /api/admin/tasks/{id}
Authorization: Bearer {token}
```

#### Frissítés

```http
PUT /api/admin/tasks/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Updated Task",
    "priority": "medium",
    "status": "in_progress"
}
```

#### Törlés

```http
DELETE /api/admin/tasks/{id}
Authorization: Bearer {token}
```

---

### Hozzárendelések Kezelése (Admin)

#### Lista

```http
GET /api/admin/task-assignments
Authorization: Bearer {token}
```

#### Létrehozás

```http
POST /api/admin/task-assignments
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 2,
    "task_id": 3,
    "assigned_date": "2026-02-12",
    "completed_date": null
}
```

#### Részletek

```http
GET /api/admin/task-assignments/{id}
Authorization: Bearer {token}
```

#### Frissítés

```http
PUT /api/admin/task-assignments/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "completed_date": "2026-02-15"
}
```

#### Törlés

```http
DELETE /api/admin/task-assignments/{id}
Authorization: Bearer {token}
```

#### Feladat Hozzárendeléseinek Lekérése

```http
GET /api/admin/task-assignments/task/{task_id}
Authorization: Bearer {token}
```

#### Felhasználó Hozzárendeléseinek Lekérése

```http
GET /api/admin/task-assignments/user/{user_id}
Authorization: Bearer {token}
```

---

## 🌐 Web Admin Felület

### Bejelentkezés

1. Nyisd meg: `http://localhost:8000/login`
2. Add meg az admin fiók adatait
3. Automatikus átirányítás az admin felületre

### Funkciók

#### Felhasználók Kezelése (`/admin/users`)

- Lista nézet minden felhasználóval
- Új felhasználó létrehozása (modal dialog)
- Felhasználó szerkesztése
- Felhasználó törlése (konfirmációval)
- Admin badge megjelenítés
- Keresés és szűrés

#### Feladatok Kezelése (`/admin/tasks`)

- Feladatok listája prioritás és státusz badge-ekkel
- Új feladat létrehozása
- Feladat szerkesztése
- Feladat törlése
- Színkódolt prioritások:
  - 🔴 High (piros)
  - 🟡 Medium (sárga)
  - 🟢 Low (zöld)

#### Hozzárendelések Kezelése (`/admin/assignments`)

- Hozzárendelések listája
- Új hozzárendelés létrehozása
- User és Task dropdown selectek
- Dátum kiválasztók
- Completion status tracking

### Technikai Részletek

- **Frontend:** Bootstrap 5.3.0
- **Icons:** Bootstrap Icons 1.11.0
- **AJAX:** Fetch API
- **Auth:** localStorage-ban tárolt Bearer token
- **Responsive:** Mobil-barát design

---

## 🧪 Tesztelés

### Teszt Futtatása

```bash
php artisan test
```

### Teszt Lefedettség

#### AuthTest (6 teszt)
- ✅ Sikeres regisztráció
- ✅ Regisztráció validációs hibákkal
- ✅ Sikeres bejelentkezés
- ✅ Sikertelen bejelentkezés rossz jelszóval
- ✅ Sikeres kijelentkezés
- ✅ Profil lekérése

#### UserControllerTest (11 teszt)
- ✅ Admin felhasználók listázása
- ✅ Admin felhasználó létrehozása
- ✅ Admin felhasználó megtekintése
- ✅ Admin felhasználó frissítése
- ✅ Admin felhasználó törlése
- ✅ Admin nem törölheti saját magát
- ✅ Regular user nem érheti el admin funkciókat
- ✅ Profile megtekintése
- ✅ Nem autentikált felhasználó nem érheti el protected endpoint-okat

#### TaskControllerTest (10 teszt)
- ✅ Felhasználó lekéri saját feladatait
- ✅ Felhasználó frissíti feladat státuszát
- ✅ Admin listázza az összes feladatot
- ✅ Admin létrehoz feladatot
- ✅ Admin megtekint feladatot
- ✅ Admin frissít feladatot
- ✅ Admin töröl feladatot
- ✅ Regular user nem érheti el admin funkciókat
- ✅ Validációs hibák kezelése

### Tesztkörnyezet

A tesztek SQLite in-memory adatbázist használnak, így nem érintik a development adatbázist.

---

## 🔒 Middleware

### IsAdmin Middleware

**Fájl:** `app/Http/Middleware/IsAdmin.php`

**Funkció:**
- Ellenőrzi, hogy a felhasználó be van-e jelentkezve
- Ellenőrzi az `is_admin` flag-et
- 401 válasz ha nincs bejelentkezve
- 403 válasz ha nem admin

**Használat:**

```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin-only routes
});
```

**Regisztráció:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\IsAdmin::class,
    ]);
})
```

---

## 📊 Modellek és Kapcsolatok

### User Model

**Fájl:** `app/Models/User.php`

**Mezők:**
- `id`, `name`, `email`, `password`
- `is_admin` (boolean)
- `department`, `phone`
- `email_verified_at`
- `deleted_at` (soft delete)

**Kapcsolatok:**
```php
hasMany(Task_assigment)
belongsToMany(Task)->through('task_assigments')
```

---

### Task Model

**Fájl:** `app/Models/Task.php`

**Mezők:**
- `id`, `title`, `description`
- `priority` (enum: low, medium, high)
- `status` (enum: pending, in_progress, completed)
- `due_date`
- `deleted_at` (soft delete)

**Kapcsolatok:**
```php
hasMany(Task_assigment)
belongsToMany(User)->through('task_assigments')
```

---

### Task_assigment Model

**Fájl:** `app/Models/Task_assigment.php`

**Mezők:**
- `id`, `user_id`, `task_id`
- `assigned_date`, `completed_date`
- `deleted_at` (soft delete)

**Kapcsolatok:**
```php
belongsTo(User)
belongsTo(Task)
```

---

## 📁 Projektstruktúra

```
todo_sanctum/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   ├── TaskController.php
│   │   │   ├── TaskAssignmentController.php
│   │   │   └── AdminController.php
│   │   └── Middleware/
│   │       └── IsAdmin.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Task.php
│   │   └── Task_assigment.php
│   └── Providers/
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── TaskFactory.php
│   │   └── AssigmentfactoryFactory.php
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_tasks_table.php
│   │   └── xxxx_create_task_assigments_table.php
│   └── seeders/
│       ├── UserSeeder.php
│       ├── TaskSeeder.php
│       └── Taskassigmentseeder.php
├── resources/
│   └── views/
│       ├── login.blade.php
│       ├── layouts/
│       │   └── admin.blade.php
│       └── admin/
│           ├── users.blade.php
│           ├── tasks.blade.php
│           └── assignments.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   └── Feature/
│       ├── AuthTest.php
│       ├── UserControllerTest.php
│       └── TaskControllerTest.php
└── docs/
    ├── README.md
    └── Task_Manager_API.postman_collection.json
```

---

## 🔧 Hibaelhárítás

### Adatbázis Kapcsolati Hiba

```bash
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Megoldás:** Ellenőrizd a `.env` fájlban az adatbázis hitelesítő adatokat.

---

### Token Not Found

Ha a web felületen nem működik a bejelentkezés:

1. Nyisd meg a Developer Tools (F12)
2. Console tab → Nézd meg a hibaüzeneteket
3. Application tab → Local Storage → Töröld az `authToken` és `adminUser` kulcsokat
4. Próbálj meg újra bejelentkezni

---

### Middleware Error

```
Route [login] not defined
```

**Megoldás:** A web route-ok nem használnak `auth:sanctum` middleware-t, helyette JavaScript ellenőrzi a tokent.

---

### CORS Error

Ha külső kliensről próbálsz csatlakozni:

1. Telepítsd a `fruitcake/laravel-cors` package-et
2. Publikáld a config-ot: `php artisan vendor:publish --tag="cors"`
3. Állítsd be a `config/cors.php` fájlban

---

## 📦 Postman Collection

A `docs/Task_Manager_API.postman_collection.json` fájl tartalmaz minden endpoint-ot példákkal.

**Import:**
1. Nyisd meg a Postman-t
2. File → Import
3. Válaszd ki a JSON fájlt
4. A collection automatikusan beállítja a token-t minden kérésnél

---

## 🚀 Production Deployment

### 1. Környezeti Változók

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### 2. Optimalizálás

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Biztonság

- Állíts be erős `APP_KEY`-t
- Használj HTTPS-t
- Állíts be rate limiting-et
- Védd meg az admin route-okat
- Használj environment-specific `.env` fájlokat

---

## 📝 License

Ez a projekt oktatási célra készült.

---

## 👨‍💻 Kapcsolat & Support

Ha kérdésed van vagy hibát találsz, nyiss egy issue-t a GitHub repository-ban.

---

## 🎉 Verzió Történet

### v1.0.0 (2026-02-12)
- ✅ Teljes REST API
- ✅ Laravel Sanctum autentikáció
- ✅ Admin middleware
- ✅ Web admin felület
- ✅ Soft delete támogatás
- ✅ 27+ PHPUnit teszt
- ✅ Postman collection
- ✅ Teljes dokumentáció

---

**Készült Laravel 11 + Sanctum + Bootstrap 5 technológiákkal** 🚀

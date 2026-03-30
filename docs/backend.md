# UFO Bejelentő Rendszer – Backend Programozói Dokumentáció

## 1. Áttekintés

A backend egy **Laravel 12** keretrendszerre épülő RESTful API, amely paranormális jelenségek (UFO-észlelések, kísértetek, stb.) bejelentését, moderálását és statisztikai elemzését szolgálja. Az autentikációt **Laravel Sanctum** token-alapú hitelesítés biztosítja.

| Jellemző           | Érték                       |
| ------------------ | --------------------------- |
| Keretrendszer      | Laravel 12                  |
| PHP verzió         | ≥ 8.2                       |
| Autentikáció       | Laravel Sanctum 4.x (Bearer token) |
| Adatbázis          | Relációs (MySQL/SQLite)     |
| Soft Delete        | Minden üzleti entitáson     |
| API prefix         | `/api`                      |
| CORS engedélyezett | `http://localhost:4200`, `http://10.1.47.9:4200` |

---

## 2. Telepítés és indítás

```bash
# Függőségek telepítése
composer install

# .env fájl létrehozása és kulcsgenerálás
cp .env.example .env
php artisan key:generate

# Migrációk futtatása
php artisan migrate

# Seeder-ek futtatása (teszt adatok)
php artisan db:seed

# Storage symlink (képfeltöltéshez)
php artisan storage:link

# Fejlesztői szerver indítása
php artisan serve
```

Vagy egyetlen paranccsal:

```bash
composer setup
```

---

## 3. Adatbázis séma

### 3.1. `users`

| Oszlop              | Típus                    | Leírás                           |
| ------------------- | ------------------------ | -------------------------------- |
| `id`                | `bigint` PK, auto-inc   | Elsődleges kulcs                 |
| `name`              | `string`                 | Felhasználónév                   |
| `email`             | `string` unique          | E-mail cím                       |
| `email_verified_at` | `timestamp` nullable     | E-mail megerősítés időpontja     |
| `password`          | `string`                 | Hashelt jelszó                   |
| `role`              | `enum('user','admin')`   | Szerep (alapértelmezett: `user`) |
| `is_banned`         | `boolean`                | Tiltott-e (alapértelmezett: `false`) |
| `remember_token`    | `string` nullable        | Remember token                   |
| `created_at`        | `timestamp`              | Létrehozás dátuma                |
| `updated_at`        | `timestamp`              | Módosítás dátuma                 |
| `deleted_at`        | `timestamp` nullable     | Soft delete                      |

### 3.2. `categories`

| Oszlop        | Típus                  | Leírás                         |
| ------------- | ---------------------- | ------------------------------ |
| `id`          | `bigint` PK            | Elsődleges kulcs               |
| `name`        | `string` unique        | Kategória neve                 |
| `description` | `text` nullable        | Kategória leírása              |
| `created_at`  | `timestamp`            | Létrehozás dátuma              |
| `updated_at`  | `timestamp`            | Módosítás dátuma               |
| `deleted_at`  | `timestamp` nullable   | Soft delete                    |

### 3.3. `reports`

| Oszlop        | Típus                                    | Leírás                                 |
| ------------- | ---------------------------------------- | -------------------------------------- |
| `id`          | `bigint` PK                              | Elsődleges kulcs                       |
| `user_id`     | `bigint` FK → `users.id` (CASCADE)       | Bejelentő felhasználó                  |
| `category_id` | `bigint` FK → `categories.id` (CASCADE)  | Kategória                              |
| `title`       | `string`                                 | Bejelentés címe                        |
| `description` | `text`                                   | Részletes leírás                       |
| `latitude`    | `decimal(10,7)` nullable                 | Szélességi fok                         |
| `longitude`   | `decimal(10,7)` nullable                 | Hosszúsági fok                         |
| `date`        | `date`                                   | Az esemény dátuma                      |
| `witnesses`   | `unsigned integer`                       | Tanúk száma (alapértelmezett: `0`)     |
| `status`      | `enum('pending','approved','rejected')`  | Státusz (alapértelmezett: `pending`)   |
| `created_at`  | `timestamp`                              | Létrehozás dátuma                      |
| `updated_at`  | `timestamp`                              | Módosítás dátuma                       |
| `deleted_at`  | `timestamp` nullable                     | Soft delete                            |

### 3.4. `report_images`

| Oszlop       | Típus                                   | Leírás                     |
| ------------ | --------------------------------------- | -------------------------- |
| `id`         | `bigint` PK                             | Elsődleges kulcs           |
| `report_id`  | `bigint` FK → `reports.id` (CASCADE)    | Kapcsolódó bejelentés      |
| `image_path` | `string`                                | Kép elérési útja (storage) |
| `created_at` | `timestamp` nullable                    | Feltöltés dátuma           |
| `deleted_at` | `timestamp` nullable                    | Soft delete                |

### 3.5. `votes`

| Oszlop       | Típus                                | Leírás                                     |
| ------------ | ------------------------------------ | ------------------------------------------ |
| `id`         | `bigint` PK                          | Elsődleges kulcs                           |
| `report_id`  | `bigint` FK → `reports.id` (CASCADE) | Szavazat célja                             |
| `user_id`    | `bigint` FK → `users.id` (CASCADE)   | Szavazó felhasználó                        |
| `vote_type`  | `enum('up','down')`                  | Szavazat típusa                            |
| `created_at` | `timestamp` nullable                 | Szavazat leadásának ideje                  |
| `deleted_at` | `timestamp` nullable                 | Soft delete                                |

> **Egyedi constraint:** `UNIQUE(report_id, user_id)` – egy felhasználó csak egyszer szavazhat egy bejelentésre.

### 3.6. ER-diagram (szöveges)

```
users 1───N reports N───1 categories
  │                  │
  │                  ├───N report_images
  │                  │
  └───N votes N──────┘
```

---

## 4. Modellek

### 4.1. `User` (`App\Models\User`)

- **Trait-ek:** `HasApiTokens`, `HasFactory`, `Notifiable`, `SoftDeletes`
- **Fillable:** `name`, `email`, `password`, `role`, `is_banned`
- **Hidden:** `password`, `remember_token`
- **Cast-ok:** `email_verified_at` → `datetime`, `password` → `hashed`, `is_banned` → `boolean`
- **Relációk:**
  - `reports(): HasMany → Report`
  - `votes(): HasMany → Vote`
- **Metódusok:**
  - `isAdmin(): bool` – `true` ha `role === 'admin'`

### 4.2. `Report` (`App\Models\Report`)

- **Trait-ek:** `HasFactory`, `SoftDeletes`
- **Fillable:** `user_id`, `category_id`, `title`, `description`, `latitude`, `longitude`, `date`, `witnesses`, `status`
- **Cast-ok:** `date` → `date`, `latitude` → `float`, `longitude` → `float`, `witnesses` → `integer`
- **Relációk:**
  - `user(): BelongsTo → User`
  - `category(): BelongsTo → Category`
  - `images(): HasMany → ReportImage`
  - `votes(): HasMany → Vote`
- **Accessor-ok:**
  - `credibility_score` – `upvotes - downvotes` (számított)

### 4.3. `Category` (`App\Models\Category`)

- **Trait-ek:** `HasFactory`, `SoftDeletes`
- **Fillable:** `name`, `description`
- **Relációk:**
  - `reports(): HasMany → Report`

### 4.4. `ReportImage` (`App\Models\ReportImage`)

- **Trait-ek:** `SoftDeletes`
- **Fillable:** `report_id`, `image_path`
- **Timestamps:** csak `created_at` (kézi definiálás)
- **Appended attribútumok:** `image_url`
- **Accessor-ok:**
  - `image_url` – teljes publikus URL a `Storage::disk('public')` segítségével
- **Relációk:**
  - `report(): BelongsTo → Report`

### 4.5. `Vote` (`App\Models\Vote`)

- **Trait-ek:** `SoftDeletes`
- **Fillable:** `report_id`, `user_id`, `vote_type`
- **Timestamps:** csak `created_at` (kézi definiálás)
- **Relációk:**
  - `report(): BelongsTo → Report`
  - `user(): BelongsTo → User`

---

## 5. Middleware

### 5.1. `AdminMiddleware` (`admin`)

Ellenőrzi, hogy a bejelentkezett felhasználó rendelkezik-e `admin` szereppel. Ha nem, `403 Forbidden` választ ad.

### 5.2. `CheckBanned` (`check.banned`)

Ellenőrzi, hogy a felhasználó tiltva van-e (`is_banned === true`). Ha igen, `403 Forbidden` választ ad.

### 5.3. Middleware regisztráció (`bootstrap/app.php`)

```php
$middleware->use([
    \Illuminate\Http\Middleware\HandleCors::class,
]);
$middleware->alias([
    'admin'        => \App\Http\Middleware\AdminMiddleware::class,
    'check.banned' => \App\Http\Middleware\CheckBanned::class,
]);
```

---

## 6. Form Request validáció

### 6.1. `RegisterRequest`

| Mező       | Szabályok                                |
| ---------- | ---------------------------------------- |
| `name`     | required, string, max:255               |
| `email`    | required, email, unique:users,email     |
| `password` | required, string, min:8, confirmed      |

### 6.2. `StoreReportRequest`

| Mező          | Szabályok                                     |
| ------------- | --------------------------------------------- |
| `category_id` | required, exists:categories,id               |
| `title`       | required, string, max:255                    |
| `description` | required, string                             |
| `latitude`    | nullable, numeric, between:-90,90            |
| `longitude`   | nullable, numeric, between:-180,180          |
| `date`        | required, date, before_or_equal:today        |
| `witnesses`   | nullable, integer, min:0                     |

> **prepareForValidation:** üres stringek → `null` konverzió a `witnesses`, `latitude`, `longitude` mezőkre.

### 6.3. `UpdateReportRequest`

Azonos az `StoreReportRequest`-tel, de minden mező `sometimes` (opcionális).

### 6.4. `StoreCategoryRequest`

| Mező          | Szabályok                                |
| ------------- | ---------------------------------------- |
| `name`        | required, string, max:255, unique:categories,name |
| `description` | nullable, string                         |

### 6.5. `UpdateCategoryRequest`

Azonos a `StoreCategoryRequest`-tel, de `name` → `sometimes` és a unique szabály ignorálja az aktuális kategóriát.

### 6.6. `VoteRequest`

| Mező        | Szabályok            |
| ----------- | -------------------- |
| `vote_type` | required, in:up,down |

---

## 7. API végpontok

Az összes végpont prefixe: `/api`

### 7.1. Publikus végpontok (autentikáció nélkül)

#### Autentikáció

| Metódus | Útvonal         | Controller                   | Leírás                   |
| ------- | --------------- | ----------------------------| ------------------------ |
| POST    | `/register`     | `AuthController@register`   | Regisztráció             |
| POST    | `/login`        | `AuthController@login`      | Bejelentkezés            |

**POST `/api/register`**

- **Body:** `{ name, email, password, password_confirmation }`
- **Válasz (201):** `{ message, user, token }`

**POST `/api/login`**

- **Body:** `{ email, password }`
- **Válasz (200):** `{ message, user, token }`
- **Hiba (403):** tiltott felhasználó; **(422):** hibás adatok

#### Bejelentések

| Metódus | Útvonal                              | Controller                    | Leírás                    |
| ------- | ------------------------------------ | ----------------------------- | ------------------------- |
| GET     | `/reports`                           | `ReportController@index`      | Jóváhagyott bejelentések  |
| GET     | `/reports/{report}`                  | `ReportController@show`       | Bejelentés részletei      |
| GET     | `/map/reports`                       | `ReportController@mapReports` | Térkép adatok             |
| GET     | `/reports/{report}/credibility`      | `VoteController@credibility`  | Hitelesség pontszám       |
| GET     | `/reports/{report}/images`           | `ImageController@index`       | Képek listája             |

**GET `/api/reports`** – Szűrési és rendezési paraméterek:

| Paraméter     | Típus    | Leírás                                    |
| ------------- | -------- | ----------------------------------------- |
| `category_id` | integer  | Szűrés kategória ID-ra                    |
| `date_from`   | date     | Szűrés: esemény dátuma ettől              |
| `date_to`     | date     | Szűrés: esemény dátuma eddig              |
| `sort_by`     | string   | `created_at` (alapértelmezett), `date`, `title`, `credibility` |
| `sort_dir`    | string   | `desc` (alapértelmezett) vagy `asc`       |

**GET `/api/reports/{report}`** – Nem jóváhagyott bejelentés csak a tulajdonos vagy admin számára elérhető.

**GET `/api/map/reports`** – Csak `approved` státuszú, koordinátákkal rendelkező bejelentéseket ad vissza: `id`, `title`, `latitude`, `longitude`, `date`, `category_id`, `status`.

**GET `/api/reports/{report}/credibility`** – Válasz: `{ upvotes, downvotes, credibility_score }`

#### Kategóriák

| Metódus | Útvonal                    | Controller                    | Leírás               |
| ------- | -------------------------- | ----------------------------- | -------------------- |
| GET     | `/categories`              | `CategoryController@index`    | Kategóriák listája   |
| GET     | `/categories/{category}`   | `CategoryController@show`     | Kategória részletei  |

#### Statisztikák

| Metódus | Útvonal        | Controller                     | Leírás                |
| ------- | -------------- | ------------------------------ | --------------------- |
| GET     | `/statistics`  | `StatisticsController@index`   | Publikus statisztikák |

**Válasz tartalmazza:** `total_reports`, `total_users`, `total_votes`, `by_status`, `by_category`, `top_credible` (top 5), `recent` (utolsó 5).

---

### 7.2. Védett végpontok (Bearer token szükséges)

> Middleware: `auth:sanctum`, `check.banned`
>
> Header: `Authorization: Bearer {token}`

#### Autentikáció

| Metódus | Útvonal    | Controller                 | Leírás            |
| ------- | ---------- | -------------------------- | ----------------- |
| POST    | `/logout`  | `AuthController@logout`    | Kijelentkezés     |
| GET     | `/user`    | `AuthController@user`      | Aktuális felhasználó |

#### Profil

| Metódus | Útvonal                     | Controller                        | Leírás                           |
| ------- | --------------------------- | --------------------------------- | -------------------------------- |
| GET     | `/profile`                  | `ProfileController@show`          | Saját profil (reports_count-tal) |
| PUT     | `/profile`                  | `ProfileController@update`        | Profil módosítás                 |
| GET     | `/users/{userId}/reports`   | `ProfileController@userReports`   | Adott felhasználó bejelentései   |

**PUT `/api/profile`** – Módosítható mezők:

| Mező       | Szabályok                                   |
| ---------- | ------------------------------------------- |
| `name`     | sometimes, string, max:255                 |
| `email`    | sometimes, email, unique (saját ID kizárva) |
| `password` | sometimes, string, min:8, confirmed        |

#### Bejelentések kezelése

| Metódus | Útvonal              | Controller                 | Leírás             |
| ------- | -------------------- | -------------------------- | ------------------ |
| POST    | `/reports`           | `ReportController@store`   | Új bejelentés      |
| PUT     | `/reports/{report}`  | `ReportController@update`  | Bejelentés szerkesztése |
| DELETE  | `/reports/{report}`  | `ReportController@destroy` | Bejelentés törlése |

> Szerkesztés és törlés: csak a tulajdonos vagy admin végezheti.
> Új bejelentés státusza automatikusan `pending`.

#### Képkezelés

| Metódus | Útvonal                        | Controller               | Leírás             |
| ------- | ------------------------------ | ------------------------ | ------------------ |
| POST    | `/reports/{report}/images`     | `ImageController@store`  | Képek feltöltése   |
| DELETE  | `/images/{image}`              | `ImageController@destroy`| Kép törlése        |

**POST `/api/reports/{report}/images`** – `multipart/form-data`

| Mező       | Szabályok                                                    |
| ---------- | ------------------------------------------------------------ |
| `images`   | required, array, max:10                                     |
| `images.*` | required, image, mimes:jpeg,png,jpg,gif,webp, max:5120 (5 MB) |

> Tárolás: `storage/app/public/report_images/`
> Publikus elérés: `storage/report_images/{filename}`

#### Szavazás

| Metódus | Útvonal                    | Controller              | Leírás          |
| ------- | -------------------------- | ----------------------- | --------------- |
| POST    | `/reports/{report}/vote`   | `VoteController@vote`   | Szavazás        |

**Szavazási logika:**
1. Ha a felhasználó még nem szavazott → szavazat létrehozása
2. Ha azonos típusú szavazat létezik → szavazat **visszavonása** (forceDelete)
3. Ha ellentétes szavazat létezik → szavazat **módosítása**

**Válasz:** `{ message, upvotes, downvotes, credibility_score }`

---

### 7.3. Admin végpontok

> Middleware: `auth:sanctum`, `check.banned`, `admin`
>
> Prefix: `/api/admin`

#### Bejelentés moderálás

| Metódus | Útvonal                            | Controller                       | Leírás                |
| ------- | ---------------------------------- | -------------------------------- | --------------------- |
| GET     | `/admin/reports`                   | `Admin\ReportController@index`   | Összes bejelentés     |
| DELETE  | `/admin/reports/{report}`          | `Admin\ReportController@destroy` | Bejelentés törlése    |
| PUT     | `/admin/reports/{report}/approve`  | `Admin\ReportController@approve` | Jóváhagyás            |
| PUT     | `/admin/reports/{report}/reject`   | `Admin\ReportController@reject`  | Elutasítás            |

**GET `/api/admin/reports`** – Opcionális szűrés: `?status=pending|approved|rejected`

#### Felhasználó kezelés

| Metódus | Útvonal                     | Controller                     | Leírás                   |
| ------- | --------------------------- | ------------------------------ | ------------------------ |
| GET     | `/admin/users`              | `Admin\UserController@index`   | Felhasználók listája     |
| PUT     | `/admin/users/{user}/ban`   | `Admin\UserController@ban`     | Felhasználó tiltása      |
| PUT     | `/admin/users/{user}/unban` | `Admin\UserController@unban`   | Tiltás feloldása         |

**GET `/api/admin/users`** – Keresés: `?search=` (név vagy email részleges egyezés)

> Admin felhasználó nem tiltható (`422` hiba).

#### Kategória kezelés (Admin)

| Metódus | Útvonal                          | Controller                      | Leírás            |
| ------- | -------------------------------- | ------------------------------- | ----------------- |
| POST    | `/admin/categories`              | `CategoryController@store`      | Kategória létrehozás |
| PUT     | `/admin/categories/{category}`   | `CategoryController@update`     | Kategória módosítás  |
| DELETE  | `/admin/categories/{category}`   | `CategoryController@destroy`    | Kategória törlés     |

#### Admin statisztikák

| Metódus | Útvonal              | Controller                         | Leírás                |
| ------- | -------------------- | ---------------------------------- | --------------------- |
| GET     | `/admin/statistics`  | `Admin\StatisticsController@index` | Részletes statisztika |

**Válasz:**
```json
{
  "reports": { "total", "pending", "approved", "rejected" },
  "users": { "total", "banned" },
  "total_votes": 0,
  "total_categories": 0,
  "top_reports": [],
  "reports_by_category": []
}
```

---

## 8. Autentikáció

### 8.1. Token kezelés

A rendszer **Laravel Sanctum** personal access token-eket használ.

- **Regisztráció / Bejelentkezés** után a válaszban `token` mező tartalmazza a Bearer tokent
- **Védett végpontok** elérése: `Authorization: Bearer {token}` header
- **Kijelentkezés:** az aktuális token törlése
- **Token név:** `api-token`

### 8.2. Middleware lánc

```
Publikus végpontok:
  → HandleCors → Controller

Védett végpontok:
  → HandleCors → auth:sanctum → check.banned → Controller

Admin végpontok:
  → HandleCors → auth:sanctum → check.banned → admin → Controller
```

---

## 9. Mappastruktúra

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php              # Alap controller
│   │   └── Api/
│   │       ├── AuthController.php      # Regisztráció, login, logout
│   │       ├── CategoryController.php  # Kategória CRUD
│   │       ├── ImageController.php     # Kép feltöltés/törlés
│   │       ├── ProfileController.php   # Profil kezelés
│   │       ├── ReportController.php    # Bejelentés CRUD + térkép
│   │       ├── StatisticsController.php# Publikus statisztikák
│   │       ├── VoteController.php      # Szavazás + hitelesség
│   │       └── Admin/
│   │           ├── ReportController.php    # Moderálás
│   │           ├── StatisticsController.php# Admin statisztikák
│   │           └── UserController.php      # Felhasználó kezelés
│   ├── Middleware/
│   │   ├── AdminMiddleware.php         # Admin jogosultság ellenőrzés
│   │   └── CheckBanned.php            # Tiltott felhasználó ellenőrzés
│   └── Requests/
│       ├── RegisterRequest.php
│       ├── StoreCategoryRequest.php
│       ├── StoreReportRequest.php
│       ├── UpdateCategoryRequest.php
│       ├── UpdateReportRequest.php
│       └── VoteRequest.php
├── Models/
│   ├── Category.php
│   ├── Report.php
│   ├── ReportImage.php
│   ├── User.php
│   └── Vote.php
└── Providers/
    └── AppServiceProvider.php
database/
├── migrations/                         # Adatbázis migrációk
├── seeders/
│   ├── DatabaseSeeder.php             # Fő seeder
│   ├── CategorySeeder.php            # 9 kategória
│   ├── UserSeeder.php                # Admin + teszt felhasználók
│   ├── ReportSeeder.php              # Teszt bejelentések
│   └── VoteSeeder.php                # Teszt szavazatok
routes/
└── api.php                            # Összes API útvonal
```

---

## 10. Seeder adatok

### 10.1. Kategóriák

| Kategória            | Leírás                                                  |
| -------------------- | ------------------------------------------------------- |
| UFO Észlelés         | Azonosítatlan repülő tárgyak és légi jelenségek         |
| Földönkívüli         | Idegen lények találkozásai                               |
| Kísértet / Szellem   | Természetfeletti entitások és kísértett helyek           |
| Crop Circle          | Rejtélyes búzamező alakzatok                             |
| Bigfoot / Sasquatch  | Nagy emberszabású lény észlelések                        |
| Tengeri Szörny       | Azonosítatlan vízi lények                                |
| Poltergeist          | Zajokkal és tárgyak mozgásával járó jelenségek           |
| Időhurok / Anomália  | Idővel kapcsolatos furcsa tapasztalatok                  |
| Egyéb Paranormális   | Minden más megmagyarázhatatlan jelenség                  |

### 10.2. Teszt felhasználók

| Név          | Email                | Jelszó     | Szerep |
| ------------ | -------------------- | ---------- | ------ |
| Admin        | admin@ufo.hu         | `password` | admin  |
| Patrik       | patrik@ufo.hu        | `password` | user   |
| Odett        | odett@ufo.hu         | `password` | user   |
| Kiss Péter   | kisspeter@ufo.hu     | `password` | user   |
| Horváth Éva  | horvatheva@ufo.hu    | `password` | user   |
| Soós Elemér  | soselemer@ufo.hu     | `password` | user   |
| Ali Mihály   | alimihaly@ufo.hu     | `password` | user   |

---

## 11. Hibakezelés

Az API egységes JSON válaszokat ad minden hibánál:

| HTTP kód | Jelentés                                            |
| -------- | --------------------------------------------------- |
| `200`    | Sikeres művelet                                     |
| `201`    | Sikeres létrehozás                                  |
| `403`    | Hozzáférés megtagadva (tiltott user / nem admin / nem tulajdonos) |
| `404`    | Erőforrás nem található / nem elérhető bejelentés   |
| `422`    | Validációs hiba                                     |

Validációs hiba válasz formátum:
```json
{
  "message": "...",
  "errors": {
    "mező": ["Hibaüzenet"]
  }
}
```

---

## 12. Fájltárolás

- **Disk:** `public` (`storage/app/public`)
- **Képek útvonala:** `report_images/{generált_fájlnév}`
- **Publikus elérés:** `{APP_URL}/storage/report_images/{fájlnév}` (szimbolikus link szükséges: `php artisan storage:link`)
- **Maximális fájlméret:** 5 MB/kép
- **Engedélyezett formátumok:** `jpeg`, `png`, `jpg`, `gif`, `webp`
- **Egyszerre feltölthető:** max 10 kép

---

## 13. CORS konfiguráció

```php
'paths'           => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:4200', 'http://10.1.47.9:4200'],
'allowed_headers' => ['*'],
```

---

## 14. Tesztelés

```bash
# Összes teszt futtatása
php artisan test

# Vagy közvetlenül PHPUnit-tal
./vendor/bin/phpunit
```

---

## 15. Hasznos Artisan parancsok

```bash
# Migrációk futtatása
php artisan migrate

# Migrációk visszavonása és újrafuttatás
php artisan migrate:fresh --seed

# Seeder-ek futtatása
php artisan db:seed

# Útvonalak listázása
php artisan route:list

# Konfiguráció cache-elés
php artisan config:cache

# Storage symlink létrehozása
php artisan storage:link
```

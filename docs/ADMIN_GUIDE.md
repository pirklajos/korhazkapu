# Admin útmutató

Az M1 admin-alap a `/login` címen érhető el. Fejlesztői fixture betöltése után a
közös, kizárólag demo jelszó: `Demo-Only-ChangeMe-2026!`.

| Szerep | Demo e-mail |
|---|---|
| Platformadmin | `platform.admin@demo.invalid` |
| Intézményi admin | `institution.admin@demo.invalid` |
| Telephelyadmin | `site.admin@demo.invalid` |
| Szerkesztő | `editor@demo.invalid` |
| Szakmai ellenőr | `medical.reviewer@demo.invalid` |
| Publikáló | `publisher@demo.invalid` |
| Auditor | `auditor@demo.invalid` |

A második intézmény elkülönített adminja:
`institution.admin@demo-duna.invalid`.

Platformadmin az `/admin` irányítópulton válthat aktív intézményt. Intézményi
felhasználó fejlesztésben a `/i/{slug}/admin` útvonalat használja; domainalapú
feloldásnál az `/admin` útvonal megfelelő.

Tervezett fő folyamatok:

1. intézményi profil és arculat kezelése;
2. telephelyek, osztályok és szolgáltatások szerkesztése;
3. tájékoztató létrehozása, szakmai ellenőrzése és publikálása;
4. közlemények, média, szinonimák és felhasználói tagságok kezelése;
5. verziók, lejáratok, visszajelzések és auditnapló áttekintése.

A tartalmi CRUD és publikálási modulok M2–M3-ban készülnek el.

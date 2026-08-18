# Implementation status

## Aktuális állapot

- Utolsó frissítés: 2026-08-18
- Aktuális mérföldkő: M2 – Struktúra és tartalomkezelés
- Következő konkrét lépés: térbeli és szervezeti entitások, migráció és demo
  struktúraadatok megvalósítása, majd tartalmi entitások és admin CRUD.
- Ismert blokkoló tényező: a jelenlegi hoston nincs Docker/Compose, Composer,
  illetve PHP DOM/XML bővítmény; a teljes konténeres build helyben itt nem
  futtatható. A repository Docker image-e ezeket biztosítja.

## Mérföldkövek

| Mérföldkő | Állapot | Ellenőrzés | Megjegyzés |
|---|---|---|---|
| M0 | completed | Composer-validáció, lint, PHP syntax és YAML ellenőrzés sikeres | A teljes Docker build host-eszköz hiányában CI-ben ellenőrizendő |
| M1 | completed | 2 migráció, fixture, schema sync, 6 teszt, login és két publikus tenant sikeres | Helyi PostgreSQL 16-on ellenőrizve |
| M2 | in_progress | | Struktúra és tartalomkezelés |
| M3 | pending | | Workflow, verziók és audit |
| M4 | pending | | Publikus reszponzív felület |
| M5 | pending | | Kereső, sablonok és visszajelzés |
| M6 | pending | | Minőségbiztosítás és átadás |

## Döntési napló

- 2026-08-18: A repositoryban nem volt alkalmazáskód vagy helyi `AGENTS.md`;
  ezért a specifikáció szerinti új alkalmazás inicializálása indokolt.
- 2026-08-18: Symfony 7.4 LTS és PHP 8.3 lett rögzítve. A host PHP 8.3-mal és a
  Symfony jelenlegi LTS-ével kompatibilis, hosszú támogatású alap.
- 2026-08-18: Twig + AssetMapper + fokozatos Turbo/Stimulus lett kiválasztva; nem
  készül külön SPA.
- 2026-08-18: PostgreSQL 16 és Doctrine migrációk képezik az adatkezelési alapot.
- 2026-08-18: Moduláris monolit készül központi, kötelező tenant-contexttel és
  tenantolt repository-határokkal.

## Elkészült funkciók

- Symfony 7.4 LTS alkalmazásváz és lockfile.
- Doctrine ORM/Migrations, Security, Forms, Validator, Twig, AssetMapper,
  Turbo/Stimulus és PHPUnit Bridge alapfüggőségek.
- PHP 8.3 alkalmazás- és PostgreSQL 16 adatbázis-konténer konfiguráció.
- Egységes `composer lint`, `test`, `build` és `check` parancsok.
- M0 architektúra-, telepítési-, admin- és biztonsági dokumentációs alap.
- UUID-alapú Institution, InstitutionTheme, User és Membership entitások,
  PostgreSQL migrációval.
- Domain- és `/i/{slug}` útvonalalapú tenantfeloldás, központi TenantContext és
  `TenantOwnedEntity`-alapú Doctrine SQL-filter.
- Tenant-határt ellenőrző intézményi voter és intézményi szerepkör-ellenőrzés.
- Rate-limitált, CSRF-védett form login és admin intézményváltó platformadminnak.
- Két eltérő arculatú demo intézmény és minden fő szerepkörhöz fejlesztői user.
- M2 tenantolt térbeli/szervezeti modell: Site, Building, Floor, Room,
  Department, Service és ContactPoint, kereszt-tenant kapcsolatvédelemmel.
- M2 tartalmi modell: InformationPage, ProcedureGuide, PatientJourney és
  JourneyStep, Announcement, MediaAsset, publikálási időablak és státuszok.
- Intézményenként 2 telephely, 3 épület/osztály, 5 szolgáltatás, 5 publikált
  tájékoztató, 2 betegút, aktív és lejárt közlemény demo adatai.
- Tenantjogosultsággal védett admin struktúra- és tartalomáttekintő.
- CSRF-védett, tenantellenőrzött létrehozás/szerkesztés/törlés telephelyhez,
  osztályhoz, szolgáltatáshoz, tájékoztatóhoz és közleményhez.
- Valódi lokális médiafeltöltés kötelező alt szöveggel, 5 MB korláttal és
  szerveroldali JPEG/PNG/WebP/PDF MIME-engedélylistával; tenantonkénti tárolás.
- Egységes, reszponzív admin UI: asztali oldalsáv, mobilmenü, intézményi
  kontextussáv, irányítópult-kártyák, rendezett űrlapok/listák és flash üzenetek.

## Nyitott feladatok

- Az összes M2–M6 funkció a specifikáció szerinti sorrendben.
- M2-ben még hátra van az épület/szint/helyiség/kapcsolati pont, betegút és
  ProcedureGuide részletes CRUD-ja, valamint a média törlési/kiszolgálási útja.
- Tailwind CSS integráció és vizuális rendszer.
- Fixture csomag és két demo intézmény.
- CI workflow véglegesítése a migrációs és funkcionális tesztekkel.

## Ismert hibák és technikai adósság

- Az M0 commit (`289b585`) pushát a GitHub visszautasította, mert a jelenlegi
  OAuth token nem rendelkezik `workflow` scope-pal az új CI workflow feltöltéséhez.
- A platformadmin tenantváltás teljes auditnaplózása az M3 AuditLog moduljával
  készül el; az M1-ben a jogosultság, CSRF és session-alapú váltás működik.
- A development konténer induláskor `composer install`-t futtat; később külön
  entrypointtal és cache-elt vendor volumennel finomítható.
- A PHP beépített webszervere csak helyi fejlesztési alap; productionben
  reverse proxy és dedikált alkalmazásszerver szükséges.
- A Tailwind még nincs telepítve, mert a tényleges komponensrendszer M1/M4 része.

## Legutóbb futtatott ellenőrzések

- 2026-08-18: a teljes 696 soros specifikáció elolvasva.
- 2026-08-18: repository-, Git-, runtime- és konfiguráció-feltárás elvégezve.
- 2026-08-18: Symfony függőségek PHP 8.3 célplatformra feloldva és lockolva.
- 2026-08-18: `composer validate --no-check-publish` – sikeres.
- 2026-08-18: `composer lint` – container, 24 YAML és 1 Twig fájl sikeres.
- 2026-08-18: minden saját PHP fájl `php -l` ellenőrzése – sikeres.
- 2026-08-18: Compose- és GitHub Actions YAML közvetlen parse – sikeres.
- 2026-08-18: `php bin/console about --env=test` – kernel sikeresen indul.
- 2026-08-18: `composer test` és `composer build` a host hiányzó DOM/XML
  bővítménye miatt nem futtatható; Dockerben/CI-ben a szükséges bővítmény
  deklarálva van. Ellenőrző parancs: `docker compose run --rm app composer check`.
- 2026-08-18: Docker/Compose parancs nem érhető el ezen a hoston, ezért
  `docker compose config` és image build nem futott.
- 2026-08-18: M1 PHP syntax, container-, 24 YAML- és 5 Twig-lint – sikeres.
- 2026-08-18: Doctrine entitásmapping `--skip-sync` ellenőrzése – sikeres.
- 2026-08-18: hét M1 útvonal felismerése – sikeres.
- 2026-08-18: tenant szerepkör-izoláció közvetlen domainellenőrzése – sikeres.
- 2026-08-18: PHP DOM/XML és `pdo_pgsql` telepítése után két migráció üres helyi
  PostgreSQL-adatbázison sikeres; fixture betöltve, schema mapping szinkronban.
- 2026-08-18: PHPUnit – 6 teszt, 7 assertion, sikeres.
- 2026-08-18: `composer lint` és production `composer build` – sikeres.
- 2026-08-18: platformoldal, login GET, két tenantoldal és platformadmin login +
  admin dashboard HTTP-ellenőrzése – sikeres.
- 2026-08-18: M2 két migrációja és kibővített fixture – sikeres; a Doctrine séma
  szinkronban, intézményenként 2 site/5 service/5 page/2 journey/2 announcement.
- 2026-08-18: M2 köztes ellenőrzés – 8 teszt, 11 assertion; lint és production
  build sikeres; az admin tartalomáttekintő HTTP 200 választ ad.
- 2026-08-18: mind az öt fő CRUD új-elem űrlap és a médiaoldal HTTP 200; site
  létrehozás–szerkesztőoldal–CSRF törlés végponttól végpontig sikeres.
- 2026-08-18: valós PNG feltöltés MIME/méret/alt ellenőrzéssel sikeres, a
  tesztadat és tesztfájl utána eltávolítva; 8 teszt és teljes lint sikeres.
- 2026-08-18: admin UI frissítés után dashboard/content/form/media autentikált
  HTTP 200, mobilmenü asset HTTP 200; Twig/container lint és 8 teszt sikeres.

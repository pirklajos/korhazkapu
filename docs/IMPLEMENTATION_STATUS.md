# Implementation status

## Aktuális állapot

- Utolsó frissítés: 2026-08-18
- Aktuális mérföldkő: M0 – elkészült; M1 következik
- Következő konkrét lépés: M0 ellenőrzése után az M1 intézmény-, téma-,
  felhasználó- és tagsági modelljének megvalósítása.
- Ismert blokkoló tényező: a jelenlegi hoston nincs Docker/Compose, Composer,
  illetve PHP DOM/XML bővítmény; a teljes konténeres build helyben itt nem
  futtatható. A repository Docker image-e ezeket biztosítja.

## Mérföldkövek

| Mérföldkő | Állapot | Ellenőrzés | Megjegyzés |
|---|---|---|---|
| M0 | completed | Composer-validáció, lint, PHP syntax és YAML ellenőrzés sikeres | A teljes Docker build host-eszköz hiányában CI-ben ellenőrizendő |
| M1 | pending | | Alkalmazásmag és tenantkezelés |
| M2 | pending | | Struktúra és tartalomkezelés |
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

## Nyitott feladatok

- Az összes M1–M6 funkció a specifikáció szerinti sorrendben.
- Tailwind CSS integráció és vizuális rendszer.
- Fixture csomag és két demo intézmény.
- CI workflow véglegesítése a migrációs és funkcionális tesztekkel.

## Ismert hibák és technikai adósság

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

# KórházKapu

White-label, többintézményes betegtájékoztató webalkalmazás. A projekt jelenleg az
alapozási mérföldkőnél tart; a részletes készültség a
[`docs/IMPLEMENTATION_STATUS.md`](docs/IMPLEMENTATION_STATUS.md) fájlban követhető.

## Gyorsindítás Dockerrel

Követelmény: Docker Engine és Docker Compose v2.

```bash
cp .env.example .env.local
docker compose up --build -d
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Az alkalmazás alapértelmezetten a <http://localhost:8000> címen érhető el.

## Fejlesztői parancsok

```bash
docker compose exec app composer install
docker compose exec app composer lint
docker compose exec app composer test
docker compose exec app composer build
docker compose exec app composer check
```

Adatbázis-migráció és a későbbi demo fixture-ek betöltése:

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction
```

Az AssetMapper production buildje a `composer build` része. A Tailwind CSS
integráció az M1/M4 során kerül be a végleges assetfolyamatba.

## Dokumentáció

- [Architektúra](docs/ARCHITECTURE.md)
- [Telepítés](docs/DEPLOYMENT.md)
- [Biztonsági alapok](docs/SECURITY.md)
- [Megvalósítási állapot](docs/IMPLEMENTATION_STATUS.md)
- [Admin útmutató](docs/ADMIN_GUIDE.md)

A részletes végrehajtási specifikáció a
[`KORHAZKAPU_CODEX_BUILD.md`](KORHAZKAPU_CODEX_BUILD.md) fájlban található.

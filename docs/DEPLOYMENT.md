# Telepítés

## Helyi fejlesztés

```bash
cp .env.example .env.local
docker compose up --build -d
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Az app a `APP_PORT` (alapérték: 8000), PostgreSQL a `POSTGRES_PORT` (alapérték:
5432) porton érhető el. A `.env.local` nem kerül verziókezelésbe.

Leállítás adatvesztés nélkül:

```bash
docker compose down
```

A `docker compose down --volumes` törli a helyi adatbázist és vendor volume-ot,
ezért csak tudatos újrainicializáláskor használandó.

## Production alapok

A `Dockerfile` `production` targetje reprodukálható alkalmazás-image-et épít.
Éles környezetben szükséges:

- valódi, secret store-ból átadott `APP_SECRET` és adatbázis-jelszó;
- TLS-t lezáró reverse proxy és megbízható proxybeállítás;
- perzisztens, mentett PostgreSQL és média-tároló;
- `APP_ENV=prod`, kikapcsolt debug és secure cookie-k;
- migráció külön release-lépésként, egy példányon futtatva;
- health check, centralizált log, mentés-visszaállítási próba és monitorozás.

A beépített PHP webszerver nem production kiszolgáló; a végleges deploymentben
FrankenPHP, nginx + PHP-FPM vagy az intézményi platform szabványos runtime-ja
használandó.

## Ellenőrző parancsok

```bash
docker compose config
docker compose build app
docker compose run --rm app composer check
```

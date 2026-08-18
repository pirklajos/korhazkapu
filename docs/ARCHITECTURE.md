# Architektúra

## Döntési összefoglaló

- **Alkalmazás:** moduláris Symfony 7.4 LTS monolit PHP 8.3-on. A hosszú támogatási
  idő és a szerveroldali renderelés illeszkedik az intézményi üzemeltetéshez.
- **Felület:** Twig, AssetMapper, fokozatos UX Turbo/Stimulus használat. A publikus
  tartalom JavaScript nélkül is használható marad. A Tailwind CSS az assetfolyamat
  része lesz, nem külön SPA.
- **Adatbázis:** PostgreSQL 16, Doctrine ORM és kizárólag verziózott migrációk.
- **Telepítés:** egy alkalmazás-image és PostgreSQL; külső SaaS nem szükséges.
- **Azonosítók:** az üzleti entitások UUID/ULID stabil azonosítót kapnak, az URL-ek
  intézményen belül egyedi slugot használnak.

## Komponenshatárok

Az alkalmazás egy deployolható egység, belül az alábbi felelősségi területekkel:

1. `Tenant` – intézményfeloldás, tenant-context és adatszigetelés.
2. `Identity` – felhasználó, tagság, szerepkör és telephely-hatókör.
3. `Structure` – telephely, épület, szint, helyiség, osztály és szolgáltatás.
4. `Content` – tájékoztatók, betegutak, közlemények és központi sablonok.
5. `Workflow` – állapotátmenetek, verziók és auditnapló.
6. `PublicSite` – témázható publikus nézetek, keresés és visszajelzés.
7. `Admin` – feladatorientált szerkesztői felület és média.

Az első iterációban ezek névterekkel és szolgáltatáshatárokkal különülnek el;
külön Symfony bundle vagy mikroszolgáltatás nem indokolt.

## Tenantmodell

Minden intézményi rekord kötelező `institution_id` idegen kulcsot kap. A kérés
elején egy központi `TenantContext` oldja fel az intézményt:

1. productionben az engedélyezett domain alapján;
2. fejlesztésben az `/i/{institutionSlug}` útvonalból;
3. adminban platformadmin által választott, auditált intézményből.

A tenantolt repository-k nem fogadnak opcionális intézményt: a context kötelező,
és a `TenantOwnedEntity` markerre épülő Doctrine SQL-filter ad második, központi
védelmi réteget. Íráskor voter és
entitásszintű invariáns is ellenőrzi a tenant-egyezést. A platformadmin explicit
tenantváltása nem kapcsolja ki a szűrést, csak a context értékét módosítja.

## Fő adatfolyamok

- **Publikus kérés:** host/útvonal → tenantfeloldás → csak publikált és aktív
  időablakú rekordok → Twig nézet → intézményi CSS custom property-k.
- **Szerkesztés:** bejelentkezés → tagság/szerepkör → voter → tenantolt mentés →
  revision/audit esemény.
- **Publikálás:** engedélyezett workflow-átmenet → immutable revision → publikált
  időablak → auditnapló.
- **Keresés:** tenantolt, publikált tartalom → PostgreSQL-alapú index/lekérdezés →
  intézményi szinonimák → rangsorolt találatok.

## Biztonsági és tartalmi alapelvek

Az MVP anonim tájékoztató rendszer, betegadatot nem tárol. A rich text
engedélylistás szanitizálást, a média MIME- és méretellenőrzést kap. A tenant-id
soha nem kizárólag kliensoldali mezőből származik. A részletes fenyegetésmodell a
[`SECURITY.md`](SECURITY.md) fájlban készül tovább.

## Következő architektúrafeladatok

- az M1 entitás- és tenant-context implementációja;
- a Symfony Workflow és audit eseménymodell véglegesítése M3-ban;
- a PostgreSQL keresési stratégia mérése a demo adatokon M5-ben;
- lokális médiaadapter kialakítása cserélhető tároló interfésszel M2-ben.

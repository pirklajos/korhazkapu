# Biztonság

## Hatókör és védendő értékek

Az MVP nyilvános betegtájékoztatást kezel, betegazonosítót és egészségügyi
dokumentációt nem. Védendő az adminfiók, a publikált tartalom integritása, az
intézmények adatszigetelése, a médiafájlok és az auditnapló.

## Fő fenyegetések és tervezett kontrollok

| Fenyegetés | Kontroll |
|---|---|
| Tenantok közötti jogosulatlan olvasás/írás | Kötelező tenant-context, tenantolt repository, voter, integrációs teszt |
| Jogosulatlan publikálás | Szerepkör és objektumszintű voter, explicit workflow, audit |
| XSS szerkesztett tartalomból | Twig auto-escape, engedélylistás HTML-szanitizálás, CSP |
| CSRF admin műveletnél | Symfony CSRF token minden állapotváltoztató űrlapon |
| Jelszópróbálgatás | Symfony login rate limiter és biztonságos jelszóhash |
| Kártékony feltöltés | Méret-, kiterjesztés- és MIME-vizsgálat; SVG tiltása az MVP-ben |
| Titok kiszivárgása | Környezeti változók, kizárt lokális env fájlok, nem-production fixture jelszavak |
| Audit megkerülése | Központi domain események és append-only auditbejegyzések |
| Botolt publikus visszajelzés | Alkalmazásszintű rate limit, külső szolgáltatás nélkül |

## Biztonsági alapkonfiguráció

Az M1–M6 során készül el a login rate limiter, secure/HttpOnly/SameSite cookie,
CSP és további HTTP headerek, jogosultsági voter-ek, feltöltésvalidáció és audit.
Production secret nem kerülhet a repositoryba vagy fixture-be.

## Adatvédelmi korlát

Űrlap és tartalommodell nem kér TAJ-számot, időpontadatot, leletet vagy más
különleges személyes adatot. Az anonim feedback csak az oldalazonosítót, választ,
opcionális általános megjegyzést és visszaélés elleni technikai metaadatot kezel.

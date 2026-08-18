# KórházKapu – Codex végrehajtási utasítás

## 0. Küldetés

Építs fel ebben a Git-repositoryban egy működő, reszponzív, több intézmény számára használható, white-label betegtájékoztató webalkalmazást **KórházKapu** munkanéven.

Az alkalmazás nem egyszerű kórházi honlap. A beteg konkrét kérdéseire adjon gyors választ:

- Hová kell mennem?
- Hogyan jutok oda?
- Mit kell magammal vinnem?
- Hogyan készüljek fel?
- Kell-e beutaló vagy előjegyzés?
- Kit kereshetek kérdés esetén?
- Van-e aktuális változás vagy korlátozás?

Az elsődleges mintaintézmény az **Észak-Pesti Centrumkórház – Honvédkórház (ÉPC-HK)**, de sem az intézmény neve, sem a logója, sem az arculata, sem a tartalma nem lehet a forráskódban fixen beégetve. Ugyanezt a rendszert a vezényelt kórházak is használhassák saját elkülönített intézményi térrel.

Ezt az állományt teljes projektmegbízásként kezeld. Ne csak tervezz: vizsgáld meg a repositoryt, hozz létre vagy egészíts ki egy működő alkalmazást, futtasd az ellenőrzéseket, és dokumentáld az eredményt.

---

## 1. Codex működési szabályai

1. Először olvasd végig ezt a fájlt, majd vizsgáld meg a repository teljes releváns tartalmát.
2. Ha van meglévő alkalmazás, annak architektúráját és konvencióit tartsd tiszteletben. Ne írj felül működő megoldást indokolatlanul.
3. Ellenőrizd a repositoryban található `AGENTS.md`, `README`, konfigurációs és függőségi állományokat.
4. Készíts rövid megvalósítási tervet, majd önállóan hajtsd végre. Csak olyan kérdésnél állj meg, amely nélkül biztonságosan nem folytatható a munka.
5. Ha a repository üres, a 3. fejezet szerinti alkalmazást inicializáld.
6. Ne állj meg statikus HTML-prototípusnál. Legyen működő adatbázis, autentikáció, adminisztráció és publikus felület.
7. Ne használj beégetett intézményi szöveget a seed/demo adatokon kívül.
8. Ne tárolj valós betegadatot, egészségügyi dokumentációt, TAJ-számot vagy időpontadatot. Az MVP anonim, tájékoztató rendszer.
9. Minden érdemi mérföldkő után futtasd a releváns teszteket, lintet és buildet.
10. A feladat vége előtt vizsgáld át a mobilos, asztali, akadálymentességi, biztonsági és többintézményes működést.
11. A `docs/IMPLEMENTATION_STATUS.md` fájlban folyamatosan vezesd a készültséget, a döntéseket, a nyitott pontokat és a következő lépést. Így megszakítás után is folytatható legyen a fejlesztés.
12. Ne jelölj késznek olyan funkciót, amely csak mock, nem ment adatot, nem elérhető a felületen, vagy nincs legalább alapvetően ellenőrizve.
13. Ne commitolj, ne pusholj és ne hozz létre pull requestet, hacsak erre külön felhatalmazást nem kapsz.

---

## 2. Kötelező eredmény

A végrehajtás végén legalább az alábbiak működjenek:

- reszponzív, mobil-first publikus kórházi oldal;
- több intézmény elkülönített kezelése;
- intézményenként szerkeszthető név, rövid név, logók, favicon, színek és kapcsolati adatok;
- telephelyek, épületek, szervezeti egységek és szolgáltatások adminisztrációja;
- vizsgálati/beavatkozási tájékoztatók;
- betegút és felkészülési ellenőrzőlista;
- aktuális közlemények és kiemelt riasztások;
- kereső;
- szerepköralapú adminisztráció;
- tartalomállapotok és alap jóváhagyási folyamat;
- verziózási/auditálási alapok;
- demo intézmény és demo tartalom;
- Docker-alapú helyi indítás;
- automata tesztek és fejlesztői dokumentáció.

---

## 3. Technológiai alap

Üres repository esetén az alábbi stacket használd:

- PHP, a környezettel kompatibilis stabil verzió;
- Symfony aktuális stabil/LTS kiadása;
- Twig szerveroldali renderelés;
- Symfony UX Turbo és Stimulus csak ott, ahol valódi interakció szükséges;
- PostgreSQL;
- Doctrine ORM és Doctrine Migrations;
- Symfony Security;
- Symfony Forms és Validator;
- Tailwind CSS;
- AssetMapper vagy a választott Symfony-verzióhoz illeszkedő egyszerű asset pipeline;
- PHPUnit;
- Symfony Panther csak akkor, ha a környezetben stabilan futtatható;
- Docker Compose a webalkalmazás és az adatbázis indításához.

Követelmények:

- Verziókat lockfile-ban rögzítsd.
- Ne vezess be külön SPA-frontendet csak a technológia kedvéért.
- A publikus oldalak JavaScript nélkül is maradjanak alapvetően használhatók.
- Külső SaaS-függőség nélkül lehessen helyben futtatni.
- Fejlesztői környezetben a feltöltött fájlok lokális tárolása elfogadható; a tárolóréteg később cserélhető legyen objektumtárra.
- Minden titok és környezetfüggő érték `.env`-ből érkezzen, példakonfigurációval.

Ha már létezik más, működő technológiai alap, ne migráld automatikusan Symfonyra. A meglévő stackben valósítsd meg ugyanezeket a képességeket, és dokumentáld az eltérést.

---

## 4. Többintézményes működés

Az alkalmazás közös adatbázist használhat, de minden intézményhez kötött rekord rendelkezzen kötelező `institution_id` kapcsolattal. A tenant-határokat alkalmazásszinten következetesen érvényesítsd.

### Tenant-feloldás

Támogasd az alábbiakat:

1. intézményi aldomain vagy domain alapján történő publikus feloldás;
2. fejlesztői környezetben útvonal-alapú feloldás, például `/i/epc-hk`;
3. platformadmin számára intézményváltó az admin felületen.

### Adatszigetelés

- Egy intézményi admin és szerkesztő kizárólag a saját intézménye rekordjait láthatja és módosíthatja.
- A lekérdezésekben ne lehessen véletlenül elhagyni a tenant-szűrést.
- Alakíts ki központi tenant-context szolgáltatást és újrafelhasználható szűrési megoldást.
- Írj tesztet arra, hogy az egyik intézmény felhasználója nem olvashatja és nem módosíthatja a másik intézmény tartalmát.
- A platformadmin tenantok között válthat, de minden ilyen művelet naplózandó.

### Központi sablonok

Az adatmodell készüljön fel arra, hogy platformszintű tájékoztató-sablonból intézményi példány készülhessen. Az MVP-ben elég:

- központi sablon létrehozása;
- sablon másolása intézményhez;
- eredeti sablonverzió hivatkozásának megőrzése;
- jelzés, ha a központi sablon óta új verzió jelent meg;
- helyi módosítás automatikus felülírásának tiltása.

---

## 5. Szerepkörök és jogosultságok

Legalább az alábbi szerepköröket valósítsd meg:

| Szerepkör | Hatókör |
|---|---|
| `ROLE_PLATFORM_ADMIN` | Minden intézmény, rendszerbeállítás és központi sablon |
| `ROLE_INSTITUTION_ADMIN` | Saját intézmény teljes konfigurációja |
| `ROLE_SITE_ADMIN` | Kijelölt telephelyek kezelése |
| `ROLE_EDITOR` | Saját intézményi tartalom létrehozása és szerkesztése |
| `ROLE_MEDICAL_REVIEWER` | Szakmai ellenőrzés és visszaküldés |
| `ROLE_PUBLISHER` | Jóváhagyás, publikálás és visszavonás |
| `ROLE_AUDITOR` | Csak olvasás, verziók és auditnapló megtekintése |

Használj Symfony Votereket vagy az adott stack megfelelő objektumszintű jogosultsági megoldását. A menüpontok elrejtése nem helyettesíti a szerveroldali jogosultságvizsgálatot.

---

## 6. Adatmodell

Az elnevezések változhatnak, de az alábbi fogalmakat modellezd.

### Intézmény és arculat

`Institution`

- név, rövid név, slug;
- státusz;
- elsődleges domain és opcionális domainek;
- központi cím, telefon, e-mail;
- fenntartó neve;
- alapértelmezett nyelv és időzóna;
- adatkezelési és jogi hivatkozások;
- létrehozási és módosítási idő.

`InstitutionTheme`

- elsődleges, másodlagos és kiemelő szín;
- szöveg- és háttérszín;
- veszély-, figyelmeztetés- és sikerszín;
- világos és sötét logó;
- favicon;
- opcionális hero kép;
- betűtípus-választás engedélyezett készletből;
- fejlécváltozat;
- saroklekerekítés és komponens-stílus előre definiált értékekből.

A színbeállításokból CSS custom propertyket generálj. Mentéskor ellenőrizd a kritikus előtér/háttér párok WCAG-kontrasztját, és jelezd a hibát az adminnak.

### Szervezeti és térbeli struktúra

- `Site` – telephely;
- `Building` – épület;
- `Floor` – szint;
- `Room` – helyiség;
- `Department` – osztály vagy szervezeti egység;
- `Service` – szakrendelés, vizsgálat vagy betegnek nyújtott szolgáltatás;
- `ContactPoint` – telefonszám, e-mail, ügytípus, elérhetőségi idő.

A kapcsolatok tegyék lehetővé, hogy egy szolgáltatás konkrét telephelyhez, épülethez, szinthez, helyiséghez és osztályhoz kapcsolható legyen.

### Tartalom

`InformationPage`

- cím, slug, rövid összefoglaló;
- strukturált tartalom;
- kategória és címkék;
- intézmény vagy központi sablon;
- szerkesztői állapot;
- szakmai felelős;
- felülvizsgálati határidő;
- SEO-cím és leírás;
- publikálási és lejárati idő.

`ProcedureGuide`

- vizsgálat/beavatkozás neve és célja;
- időtartam;
- fájdalommal vagy kellemetlenséggel kapcsolatos közérthető tájékoztatás;
- éhgyomri és folyadékfogyasztási szabályok;
- gyógyszerekkel kapcsolatos figyelmeztetés;
- szükséges dokumentumok és korábbi leletek;
- szükséges-e beutaló, előjegyzés vagy kísérő;
- lehet-e utána vezetni;
- felkészülési lépések;
- vizsgálat utáni teendők;
- eredmény átvételének módja;
- mikor kell segítséget kérni;
- kapcsolódó szolgáltatás és helyszín.

`PatientJourney`

- cím, célcsoport és rövid leírás;
- rendezett lépések;
- lépésenként cím, leírás, helyszín, teendő, szükséges dokumentum és kapcsolódó oldal;
- generálható felkészülési ellenőrzőlista.

`Announcement`

- normál közlemény, figyelmeztetés vagy sürgős riasztás;
- érintett intézmény, telephely, osztály vagy szolgáltatás;
- megjelenés kezdete és automatikus lejárat;
- prioritás;
- opcionális cselekvésgomb.

### Rendszeradatok

- `User` és intézményi tagság;
- `ContentRevision` vagy egyenértékű tartalomverzió;
- `AuditLog`;
- `MediaAsset` alt szöveggel, fájltípussal és intézményi tulajdonossal;
- `SearchSynonym` a közérthető és szakmai kifejezések összerendeléséhez;
- `Feedback` anonim „Megtaláltad, amit kerestél?” visszajelzéshez.

Minden releváns entitáshoz használj stabil azonosítót, időbélyegeket és szükség szerint soft delete-et. Adatbázis-migrációkat készíts, ne csak sémaszinkronizálást használj.

---

## 7. Szerkesztői munkafolyamat

Tartalomállapotok:

1. `draft` – piszkozat;
2. `medical_review` – szakmai ellenőrzés alatt;
3. `communication_review` – kommunikációs ellenőrzés alatt;
4. `approved` – jóváhagyva;
5. `published` – publikálva;
6. `expired` – lejárt vagy felülvizsgálandó;
7. `archived` – archivált.

Követelmények:

- csak megfelelő szerepkör válthasson állapotot;
- visszaküldésnél legyen kötelező megjegyzés;
- publikus oldalon csak az aktív időablakban lévő `published` tartalom jelenjen meg;
- minden publikálás előtt készüljön tartalomverzió;
- legyen verziólista és olvasható változás-összefoglaló;
- korábbi verzió visszaállítható legyen új verzió létrehozásával;
- a hamarosan lejáró tartalom jelenjen meg admin irányítópulton;
- minden érdemi állapotváltás kerüljön auditnaplóba.

---

## 8. Nyilvános weboldal

### Kezdőoldal

Az intézményi kezdőoldal tartalmazza:

- intézményi fejlécet és arculatot;
- jól látható keresőt;
- „Miben segíthetünk?” gyorsindító kártyákat;
- telephelyek és fontos ellátások elérését;
- aktuális riasztásokat és közleményeket;
- kiemelt betegutakat;
- gyors kapcsolati információkat;
- akadálymentességi hivatkozást;
- intézményi láblécet.

### Publikus oldaltípusok

- telephelylista és telephely adatlap;
- osztály/szakrendelés adatlap;
- szolgáltatás adatlap;
- vizsgálati tájékoztató;
- betegút lépésenként;
- felkészülési ellenőrzőlista;
- közleménylista és közlemény adatlap;
- kapcsolati/ügyintézési kereső;
- általános információs oldal;
- keresési találati oldal;
- érthető 404 és 500 oldal.

### Navigáció

- Betegközpontú információs architektúrát használj, ne kizárólag a kórházi szervezeti fát.
- Legfeljebb két fő navigációs szint legyen a mobil menüben.
- Legyen morzsanavigáció a mélyebb oldalakon.
- Helyszínnél jelenjen meg telephely, épület, emelet, helyiség, akadálymentes megközelítés és térképhivatkozás.
- Készüljön hely a későbbi 2D/3D beltéri térkép integrációjához, de az MVP-ben ne építs ál-3D megoldást.

### Kereső

Az MVP-ben adatbázis-alapú keresés megfelelő, de keressen legalább:

- címben;
- összefoglalóban;
- osztály- és szolgáltatásnévben;
- telephelynévben;
- címkékben;
- szinonimákban.

Támogassa a laikus keresőkifejezéseket, például a szakmai névhez rendelt közérthető szinonimával. Üres találatnál adjon hasznos következő lehetőséget.

---

## 9. Adminisztráció

Az admin ne nyers adatbázis-kezelő legyen, hanem feladatorientált szerkesztői felület.

### Irányítópult

Mutassa:

- piszkozatok és jóváhagyásra váró tartalmak;
- hamarosan lejáró tájékoztatók;
- aktív közlemények;
- hiányos telephely- vagy szolgáltatásadatok;
- legutóbbi szerkesztések;
- saját intézmény alapadatait és arculati állapotát.

### Admin modulok

- intézmények – csak platformadmin;
- intézményi profil és domainek;
- arculatszerkesztő élő előnézettel;
- felhasználók, tagságok és szerepkörök;
- telephelyek, épületek, szintek és helyiségek;
- osztályok és szolgáltatások;
- tájékoztatók és betegutak;
- közlemények és riasztások;
- központi sablonok;
- médiafájlok;
- szinonimák;
- visszajelzések;
- auditnapló.

### Arculatszerkesztő

Legyen szerkeszthető:

- intézménynév és rövid név;
- világos és sötét logó;
- favicon;
- borítókép;
- engedélyezett arculati színek;
- betűtípus az előre engedélyezett készletből;
- fejlécváltozat;
- kapcsolati és láblécadatok.

A feltöltéseknél ellenőrizd a MIME-típust és méretet. SVG esetén alkalmazz biztonságos szanitizálást vagy tiltsd az SVG-feltöltést az MVP-ben. Az előnézet mutassa a mobilos és asztali fejlécet, gombokat, kártyákat és riasztásokat.

### Kezdőoldal-szerkesztés

Használj korlátozott, biztonságos blokkrendszert. Legalább ezek legyenek kapcsolhatók és sorrendezhetők:

- hero/kereső;
- gyorsindító kártyák;
- aktív közlemények;
- kiemelt betegutak;
- telephelykártyák;
- fontos elérhetőségek;
- egyedi szöveges blokk.

Ne biztosíts korlátlan HTML-, JavaScript- vagy CSS-bevitelt.

---

## 10. Reszponzivitás és vizuális irány

Mobil-first módon dolgozz. Célképernyők:

- 360–430 px telefon;
- 768–1024 px tablet;
- 1280–1920 px asztali kijelző.

Vizuális elvek:

- bizalmat keltő, nyugodt, korszerű egészségügyi megjelenés;
- nagy, jól olvasható tipográfia;
- egyértelmű vizuális hierarchia;
- kevés, de határozott cselekvésgomb;
- kártyák csak akkor, ha valóban segítik a pásztázást;
- ne legyen zsúfolt portálhatás;
- a sürgős riasztás egyértelmű legyen, de ne keltsen indokolatlan pánikot;
- a márka színei CSS-változókból érkezzenek;
- a layout szélsőségesen hosszú magyar intézmény- és osztálynevekkel se törjön el.

Ellenőrizd legalább a kezdőoldalt, keresőt, telephelyoldalt, tájékoztató oldalt, bejelentkezést, admin irányítópultot és arculatszerkesztőt mindhárom képernyőcsoportban.

---

## 11. Akadálymentesség

Cél: WCAG 2.2 AA szinthez igazodó megvalósítás.

Kötelező minimum:

- szemantikus HTML;
- helyes címsorhierarchia;
- „Ugrás a tartalomra” hivatkozás;
- teljes billentyűzetes használhatóság;
- jól látható fókuszállapot;
- megfelelő színkontraszt;
- minden képhez értelmes alt szöveg vagy dekoratív jelölés;
- 44×44 px körüli érintési célok;
- a hibák ne csak színnel legyenek jelezve;
- 200%-os nagyításnál használható felület;
- `prefers-reduced-motion` figyelembevétele;
- űrlapmezők egyértelmű címkézése és hibaösszegzése;
- modális ablak csak indokolt esetben és megfelelő fókuszkezeléssel;
- nyomtatható tájékoztató nézet.

Ha automatizált accessibility-ellenőrző beilleszthető, add hozzá a tesztfolyamathoz, de az automata ellenőrzést ne tekintsd teljes auditnak.

---

## 12. Biztonság és adatvédelem

- Az MVP ne kezeljen különleges személyes adatot vagy betegazonosítót.
- Használd a framework CSRF-védelmét.
- Alkalmazz szerveroldali validációt minden bevitelnél.
- Védd a bejelentkezést rate limittel.
- Biztonságos jelszóhash-elést használj.
- Production környezetben secure, HttpOnly és megfelelő SameSite cookie-beállítás szükséges.
- Állíts be alap biztonsági fejléceket, köztük érdemi Content Security Policyt.
- A rich text tartalmat engedélylistás szanitizálással kezeld.
- Fájlfeltöltésnél méret-, kiterjesztés- és MIME-ellenőrzés szükséges.
- Auditáld a bejelentkezést, kijelentkezést, jogosultságváltozást, publikálást, visszaállítást és tenantváltást.
- Nyilvános visszajelzésnél használj botvédelmi/rate-limit alapot, de ne építs kötelező külső szolgáltatásra.
- Ne kerüljön titok, valódi jelszó vagy személyes adat fixture-be, logba vagy repositoryba.
- Készíts rövid fenyegetésmodell-dokumentumot a `docs/SECURITY.md` fájlban.

---

## 13. Demo adatok

Készíts fixture-t legalább két elkülönített demo intézménnyel:

1. ÉPC-HK jellegű, de egyértelműen **DEMO** jelölésű intézmény;
2. egy fiktív vezényelt kórház eltérő logóhelyettesítővel és színvilággal.

Mindkettőhöz legyen:

- legalább 2 telephely;
- legalább 3 épület/szervezeti egység;
- legalább 5 szolgáltatás;
- legalább 5 tájékoztató;
- legalább 2 betegút;
- aktív és lejárt közlemény;
- intézményenként eltérő arculat.

Hozz létre dokumentált fejlesztői felhasználókat minden fő szerepkörhöz, kizárólag nem-production fixture-ként. Productionben a fixture betöltése legyen tiltott vagy egyértelműen elkülönített.

---

## 14. Tesztelés

Legalább az alábbi automata teszteket készítsd el:

### Egység- és integrációs tesztek

- tenant feloldása domain és fejlesztői útvonal alapján;
- intézményi adatszigetelés;
- szerepkörök és tartalomállapot-váltások;
- publikálási időablak;
- lejárt közlemény elrejtése;
- arculati szín validálása és kontrasztellenőrzés;
- slug egyedisége intézményen belül;
- keresési szinonima;
- fájlfeltöltési szabályok.

### Funkcionális tesztek

- publikus kezdőoldal betöltése mindkét intézménynél eltérő arculattal;
- keresés és találati oldal;
- publikált tájékoztató megnyitása;
- nem publikált tájékoztató publikus tiltása;
- bejelentkezés;
- intézményi admin saját adatának módosítása;
- másik intézmény adatának tiltott elérése;
- szerkesztés–ellenőrzés–publikálás folyamat;
- logó és téma módosítása.

### Kézi/visual QA

- nincs vízszintes görgetés 360 px szélességen;
- menü billentyűzettel használható;
- fókuszállapot minden interaktív elemen látszik;
- hosszú tartalom és hosszú intézménynév nem töri el a layoutot;
- logó hiányában kulturált szöveges helyettesítő jelenik meg;
- nyomtatási nézetben a navigáció eltűnik, a lényegi tartalom megmarad.

---

## 15. Fejlesztési szakaszok

A sorrendet tartsd, kivéve ha a meglévő repository más függőségi sorrendet kíván.

### M0 – Feltárás és alapozás

- repository és meglévő konvenciók feltárása;
- architektúradöntések dokumentálása;
- fejlesztői környezet és Docker;
- CI-barát build/test parancsok;
- `docs/IMPLEMENTATION_STATUS.md` létrehozása.

### M1 – Alapalkalmazás és tenantkezelés

- Symfony/projekt inicializálása;
- adatbázis és migrációk;
- Institution, Theme, User, Membership;
- autentikáció és szerepkörök;
- tenant-context és adatszigetelés;
- két demo intézmény.

### M2 – Struktúra és tartalomkezelés

- telephely, épület, szint, helyiség;
- osztály, szolgáltatás, kapcsolat;
- tájékoztatók, betegutak, közlemények;
- médiafeltöltés;
- admin CRUD felületek.

### M3 – Szerkesztői workflow

- állapotváltások és jogosultságok;
- verziók;
- auditnapló;
- felülvizsgálati határidők;
- admin irányítópult.

### M4 – Publikus reszponzív felület

- témázható layout;
- kezdőoldal;
- listák és adatlapok;
- betegút és ellenőrzőlista;
- közlemények;
- kapcsolatok és helyszínek;
- nyomtatási stílus.

### M5 – Kereső, sablonok és visszajelzés

- keresés és szinonimák;
- központi sablon másolása és frissítési jelzése;
- anonim oldalszintű visszajelzés;
- üres állapotok és hibaoldalak.

### M6 – Minőségbiztosítás és átadás

- automata tesztek;
- reszponzív és akadálymentességi ellenőrzés;
- biztonsági áttekintés;
- dokumentáció;
- seed/demo folyamat;
- végső build és indítási próba.

---

## 16. Készültség követése

A `docs/IMPLEMENTATION_STATUS.md` tartalmazza legalább:

```markdown
# Implementation status

## Aktuális állapot
- Utolsó frissítés:
- Aktuális mérföldkő:
- Következő konkrét lépés:
- Ismert blokkoló tényező:

## Mérföldkövek
| Mérföldkő | Állapot | Ellenőrzés | Megjegyzés |
|---|---|---|---|
| M0 | pending | | |
| M1 | pending | | |
| M2 | pending | | |
| M3 | pending | | |
| M4 | pending | | |
| M5 | pending | | |
| M6 | pending | | |

## Döntési napló
## Elkészült funkciók
## Nyitott feladatok
## Ismert hibák és technikai adósság
## Legutóbb futtatott ellenőrzések
```

Engedélyezett állapotok: `pending`, `in_progress`, `blocked`, `completed`.

Megszakítás utáni folytatáskor először ezt a fájlt, a Git-státuszt és a releváns teszteredményeket ellenőrizd. Ne kezdd újra a már igazoltan elkészült munkát.

---

## 17. Dokumentáció

Készíts vagy frissíts legalább az alábbiakat:

- `README.md` – cél, képernyőképek helye, gyorsindítás, parancsok;
- `docs/ARCHITECTURE.md` – komponensek, tenantmodell, fő adatfolyamok;
- `docs/ADMIN_GUIDE.md` – intézmény, arculat, tartalom és publikálás kezelése;
- `docs/DEPLOYMENT.md` – fejlesztői és production telepítési alapok;
- `docs/SECURITY.md` – fenyegetésmodell és biztonsági kontrollok;
- `docs/IMPLEMENTATION_STATUS.md` – folytathatóság;
- `.env.example` – minden szükséges, de nem titkos konfiguráció;
- `docker-compose.yml` vagy `compose.yaml`.

A README tartalmazzon pontos, másolható parancsokat az indításhoz, migrációhoz, fixture betöltéshez, teszteléshez és asset buildhez.

---

## 18. Elfogadási feltételek

A feladat akkor tekinthető elkészült MVP-nek, ha:

1. tiszta checkoutból dokumentált módon elindítható;
2. az adatbázis migrációval létrejön és fixture-rel feltölthető;
3. két demo intézmény eltérő arculattal elérhető;
4. az intézmény neve, logója, színei és kapcsolati adatai adminból módosíthatók;
5. a módosítás a publikus oldalon kódmódosítás nélkül megjelenik;
6. az admin csak a jogosultságának megfelelő adatokat látja;
7. az egyik intézmény felhasználója nem fér hozzá a másik intézmény adataihoz;
8. tájékoztató létrehozható, ellenőrizhető és publikálható;
9. aktív közlemény megjelenik, lejárt közlemény eltűnik;
10. a kereső releváns demo találatot ad;
11. a kezdőoldal és fő oldaltípusok 360 px-en és asztali méretben is használhatók;
12. a fő folyamatok billentyűzettel kezelhetők;
13. a tesztek és a production build sikeresek;
14. az ismert hiányosságok őszintén szerepelnek a státuszfájlban.

---

## 19. Nem része az első MVP-nek

Ezekhez csak bővíthető architekturális helyet készíts, teljes implementációt ne:

- TAJ-alapú vagy EESZT-azonosítás;
- leletmegjelenítés;
- valós időpontfoglalás;
- várólista- vagy betegbehívási adatok;
- kórházi HIS-integráció;
- személyre szabott egészségügyi adatok;
- valódi beltéri pozíciómeghatározás;
- 3D épülettérkép;
- automatikus orvosi tanácsadás vagy diagnózis;
- natív mobilalkalmazás;
- teljes többnyelvű tartalomworkflow.

Az MVP adatmodellje azonban később tegye lehetővé a magyar mellett további nyelvek bevezetését.

---

## 20. Végső ellenőrzés és beszámoló

A munka végén:

1. futtasd a migrációkat üres adatbázison;
2. töltsd be a demo adatokat;
3. futtasd az összes tesztet;
4. futtasd a lintet, statikus elemzést és production asset buildet;
5. indítsd el az alkalmazást és ellenőrizd a fő publikus és admin útvonalakat;
6. nézd át a Git diffet, és távolítsd el a véletlen, generált vagy titkos fájlokat;
7. frissítsd az `IMPLEMENTATION_STATUS.md` állapotát;
8. adj rövid végső beszámolót:
   - mi készült el;
   - hogyan indítható;
   - milyen tesztek futottak és mi lett az eredményük;
   - hol találhatók a fontos admin funkciók;
   - mi maradt nyitva;
   - mi a következő ajánlott fejlesztési lépés.

Ha valamely ellenőrzés környezeti okból nem futtatható, ne hallgasd el: írd le a pontos okot és azt a parancsot, amellyel megfelelő környezetben ellenőrizhető.

---

## Indító utasítás Codexnek

**Olvasd végig a `KORHAZKAPU_CODEX_BUILD.md` fájlt, kezeld teljes végrehajtási specifikációként, majd kezdd el a megvalósítást az M0 mérföldkőtől. Önállóan haladj végig a mérföldköveken, minden szakaszban frissítsd a `docs/IMPLEMENTATION_STATUS.md` állományt, és csak valódi blokkoló kérdésnél állj meg.**

# e-demokrácia web applikációkból hívható - ügyfélkapu aláíráson alapuló - login modul

## Áttekintés

Ez egy web -es szolgáltatás. Az a célja, hogy e-demokrácia szoftverek az ügyfélkapus aláíráson alapuló regisztrációt és bejelentkezést használhassanak az ** oAuth2 ** szabvány szerint. 
A rendszer biztosítja, hogy egy személy egy alkalmazásba csak egyszer regisztrálhat.
Természetesen egy ügyfélkapú loginnal több alkalmazásba is lehet regisztrálni. 

A hívó web program iframe -be hívhatja be a regisztráló képernyőt vagy a login képernyőt. Szükség esetén css fájl segítségével az iframe -ben megjelenő formok megjelenését módosíthatja.

Az applikáció adminisztrátora az erre a célra szolgáló web felületen tudja az applikációt regisztrálni a rendszerbe.

A regisztrációs folyamatban használt aláírás szolgáltató:

https://niszavdh.gov.hu 


## Programnyelvek

 PHP, MYSQL, JQUERY, bootstrap
 
A program Szabó Simon Márk 2019 főpolgármester előválasztás 2. fordulójára készített programjában található ötletek és kód részletek felhasználásával készült.
 
Lásd: https://gitlab.com/mark.szabo-simon/elovalaszto-app?fbclid=IwAR2X4RlNDA4vHw5-4ABkDCzzuifNpE5-u9T7j1X-wuubag4ZY0fSvnifvMA

## Licensz

 GNU/GPL
 
## Programozó

Fogler Tibor (Utopszkij)

tibor.fogler@gmail.com 

https://github.com/utopszkij

## Működés

### Új applikáció regisztrálás 

Az applikációt web felületen lehet regisztrálni. A megadandó adatok:
- applikáció neve
- applikációt futtató domain
- sikeres login utáni visszahívandó url
- sikertelen user login limit
- css file url (lehet üres is)
- applikáció adminisztrátor username
- applikáció adminisztrátor jelszó (kétszer kell beirni)
- applikáció adminisztrátor email
- sikertelen admin login limit

A képernyőn van adatkezelés elfogadtatás és cookie engedélyeztetés is.

Annak igazolására, hogy az app. regisztrálását az adott rendszer rendszergazdája végzi, a megadott domainre fel kell tölteni egy "uklogin.html" fájlt, aminek tartalma egyetlen sor: "uklogin".

A sikeres app regisztrálás után a képernyőn megjelenik a ** client_id **  és ** client_secret ** adat. Ezeket gondosan meg kell örizni az app adminisztrálásához és a login/regist rendszer használatához szükség van rájuk.


Ezen adatok egy része az adminisztrátor által később is  módosítható, az app admin ugyancsak kezdeményezheti az app és saját adatainak együttes törlését is.
Az app adatok módosításához, törléséhez természetesen az admin login szükséges. Ha itt a megadott limitet túllépő sikertelen login kisérlet történik akkor az app admin login blokkolásra kerül, ezt ennek az "ügyfélkapus-login" rendszernek az "főadminisztrátora" tudja feloldani.

### login folyamat a felhasználó web applikációban:
```
<iframe ..... src="https://szeszt.tk/uklogin/oath2/loginform/client_id/<client_id>" />
```
Opcionálisan /redirect_uri/<url> és /state/xxxxx is megadható. A redirect_uri -csak az app adatoknál megadott domain-en lehet (urlencoded formában), a state tetszőleges kiegészítő infot tartalmazhat. 

Az iframe -ben egy szokásos login képernyő jelenik meg (nicknév és jelszó megadása). 
A login képernyőn a szokásos kiegészitő elemek is szerepelnek:
- elfelejtett jelszó
- még nincs fiókom, regisztrálok
- fiók törlése
- tárolt adataim lekérdezése
- adatkezelési tájékoztató
- cokkie kezelés elfogadtatása

Miután a user megadja usernevét és jelszavát a program ellenőrzi azokat, sikeres login esetén
meghívja az app adatokban beállított callback url -t, GET paraméterként küldve: "code", "state", "redirect_uri".

Ezután hívni kell a https://szeszt.tk/uklogin/oath2/access_token url-t, GET paraméterként küldve a "client_id", "client_secret" és "code" adatokat. Válaszként egy json stringet kapunk:
{"access_token":"xxxxxx"} vagy {"access_token":"", "error":"hibaüzenet"}

Következő lépésként hívni kell a https://szeszt.tk/uklogin/oath2/userinfo címet, GET paraméterként a
"access_token" értéket küldve. Válaszként a bejelentkezett user nicknevét kapjuk vagy az "error" stringet.

Sikertelen login esetén, az iframe-ben hibaüzenet jelenik meg és újra a login képernyő. az app -nál megadott számú sikertelen kisérlet után a fiók blokkolásra kerül, ez a user ebbe az applikációba a továbbiakban nem tud belépni. A blokkolt fiókokat az applikáció adminisztrátor tudja újra aktivizálni.

### Regisztráció hívása a felhasználó web applikációban
```
<iframe ..... src="https://szeszt.tk/uklogin/oauth2/registform/client_id/<client_id>" />
```
Sikeres regisztrálás után az iframe-ben a login képernyő jelenik meg. Sikertelen esetén hibaüzenet és újból a regisztrálás kezdő képernyője.


### Regisztráció folyamata

1. A megjelenő első képernyőről a felhasználónak le kell töltenie egy pdf fájlt (ez csak azt tartalmazza melyik app -be regisztrál). Ez a képernyő tartalmazza az adatkezelési tájékoztatót és a cookie használat engedélyeztetést is.
2. A user a letöltött pdf -et az ügyfélkapus ingyenes aláírás rendszerrel aláírja, és az aláírt pdf -et is letölti saját gépére.
3. az aláírt pdf -et feltölti ebbe az applikációba, az ezután megjelenő képernyőn usernevet és jelszót választ magának.
Mindezt részletes help segíti.

A rendszer ellenőrzi:
- a feltöltött pdf alá van írva és sértetlen?
- a feltöltött pdf tartalma az a client_id amibe regisztrálunk?
- az aláíró email hash szerepel már a regisztrált felhasználók között? (ha már szerepel akkor kiírja milyen nick nevet adott korábban meg)
- a választott nicknév egyedi az adott applikációban?

Hiba esetén hibaüzenet és a hiba jellegétől függően vagy
- a nicknév/jelszó megadó képernyő jelenik meg (nick név már létezik vagy formailag hibás nicknév/jelszó) vagy 
- a regsiztrálás kezdő képernyője jelenik meg (pdf aláírás hiba, pdf tartalom hiba) vagy 
- a login képernyő jelenik meg (ezzel az ügyfélkapu belépéssel már történt regisztráció ebbe az applikációba).

### Elfelejtett jelszó kezelés folyamata

A teljes regisztrációt kell a usernek megismételnie, azzal az egy különbséggel, hogy most nicknevet nem választhat, hanem a korábban használt marad meg. A rendszer ellenőrzi, hogy ugyanaz az ügyfélkapus aláírás szerepel-e a pdf -en mint ami korábban volt.


### GDPR megfelelés

#### Az app adminisztrátorokkal kapcsolatban a rendszer a következő adatokat tárolja:
- nicknév
- jelszó hash
- email
- kezelt app adatai

Mint látható az adminisztrátor valós személyt azonosító adat (név, lakcím, okmány azonosító) nincs tárolva. Mivel az email cím személyes adat, egyes értelmezések szerint ez így is a GDPR hatálya alá tartozik. Tehát erre vonatkozó tájékoztatás jelenik meg, és az admin -nak ezt el kell fogadnia. Lehetősége van a tárolt adatait lekérdezni, és azokat törölni is - ez utóbbi egyúttal az applikáció törlését is jelenti.

#### a "normál" felhasználókkal kapcsolatban tárolt adatok ("users" tábla):
- nick név
- jelszó hash
- melyik applikációba regisztrált
- ügyfélkapunál megadott email hash

Itt személyes adat nincs kezelve, tehát ez nem tartozik a GDPR hatálya alá,erről tájékoztatást írunk ki.

#### cookie kezelés
A működéshez egy darab un. "munkamenet cookie" használata szükséges, erről tájékoztatás jelenik meg és a felhasználónak ezt el kell fogadnia.

## Brute force támadás elleni védekezés

### user login brute force támadás
Az applikáció adatoknál beállított limitet elérő hibás kisérlet után a user fiók blokkolása, amit az applikáció adminisztrátor tud feloldani.

### oAuth access_token hívás brute force támadás
Az azonos IP címről érkező 10 egymást követő hibás hívás után az IP cím blokkolásra kerül. Ezt az "ügyfélkapus-login" rendszer főadminisztrátora tudja feloldani.

### oAuth userinfo hívás brute force támadás
Az azonos IP címről érkező 10 egymást követő hibás hívás után az IP cím blokkolásra kerül. Ezt az "ügyfélkapus-login" rendszer főadminisztrátora tudja feloldani.

## SQL táblák

### "apps" tábla
- ** id ** automatikusan képzett sorszám
- ** name ** applikáció neve
- ** domain ** applikációt futtató domain
- ** client_id ** (automatikusan képzett véletlenszerű string)
- ** client_secret ** (automatikusan képzett véletlenszerű string)
- ** callback ** sikeres login utáni visszahívandó url
- ** falseLoginLimit ** sikertelen user login limit
- ** cssurl ** css file url (lehet üres is)
- ** admin ** applikáció adminisztrátor username
- ** pswHash **  applikáció adminisztrátor jelszó "hash"
- ** email ** applikáció adminisztrátor email
- ** falseAdminLoginLimit **  sikertelen admin login limit
- ** errorCounter ** Hibás admin login kisérlet számláló
- ** enabled ** admin login engedélyezett?

### "users" tábla

- ** id ** automatikusan generált sorszám
- ** client_id ** melyik applikációba regisztrált
- ** user ** nick név
- ** pswhash ** jelszó hash
- ** emailHash ** ügyfélkapuban megadott email címének hash kódja
- ** errorCounter ** hibás login kisérlet számláló
- ** enabled ** login engedélyezett?
- ** code ** automatikusan generált véletlenszerű egyedi string ( hex(id - random()) )
- ** access_token ** automatikusan generált véletlenszerű, egyedi string ( hex(id - random()) )
- ** created ** code + access_token létrehozás időpontja

A code és az access_token csak 1 percig van tárolva, ezután automatikusan törlődnek. Ugyancsak törlődnek  miután "fel lettek használva"  "userinfo" vagy "access_token" kérés kiszolgálására.

### "hacker" tábla

- ** ip
- ** errorCount


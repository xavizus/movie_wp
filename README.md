Ni ska bygga en film-site i WordPress.
Inloggningsuppgifter:
Användarnamn: micke
Lösenord: micke

IMDB idn:
tt4728568 : Dungeon ni Deai wo Motomeru no wa Machigatteiru Darou ka  
tt0240493 : excel saga 
tt0845738 : When they cry

Möteid: 718 0695

# G

- [x] Siten ska ha ett lämpligt tema. Eget eller färdigt spelar ingen roll.
- [x] Det ska finnas en Movie-cpt som ska skapas i ett eget plugin.
- [x] Varje film ska ha ett eget fält (Custom Meta Box) där användaren kan fylla i IMDb-id för filmen och spara det. För filmen https://www.imdb.com/title/tt3896198/ är id:t alltså tt3896198.
- [x] Användaren ska kunna rejta filmer, antingen via egen eller tredjepartsplugin.
- [x] Siten ska kunna visa de högst rejtade filmerna på förstasidan. Det spelar ingen roll om man gör det som en arkivsida eller widget.

# VG

- [x] Om man sparar en film ska ditt plugin kolla om IMDb-id är ifyllt. Om så är fallet ska ditt plugin hämta information om filmen från t ex http://www.omdbapi.com/ , uppdatera titeln på posten och handlingen ("plot") ska sparas som content.
  - [x] Utgivningsår och skådespelare sparas som postmeta och visas om användaren tittar på en detaljsida (single post).
- [x] Pluginet ska skapa sina datastrukturer (som cpt och liknande) när det aktiveras och städa efter sig när det avaktiveras.
- [x] Metaboxen (för IMDb-id) ska skapas i ditt plugin, alltså inte via tredjepartsplugin som t ex ACF.
- [x] Det ska finnas en egengjord filmlista (archive). Du måste visa att du förstår WPs arkitektur tillräckligt bra för att göra det på ett beständigt sätt.
  - Kommentar från lärare __Jag tänker t ex att din lista inte ska försvinna om du uppdaterar temat. Annars tänker jag mig att man gör ett arkiv, men ja, att loopa filmer på en sida.__
  - [x] I filmlistan ska de filmer som har ett IMDb-id ifyllt indikeras med en liten ikon eller liknande.
- [x] Man kan bara få VG om man blir klar i tid.
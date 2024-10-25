let lastFetchedPrices = null
let lastFetchedMonth = null;
let heute = new Date();
let monat = heute.getMonth() + 1; // getMonth() gibt einen Wert von 0 (Januar) bis 11 (Dezember) zurück, daher fügen wir 1 hinzu
let jahr = heute.getFullYear();
const calendar = createCalendar((monat-1), jahr ,1);
document.getElementById('preise_festlegen').appendChild(calendar);
document.getElementById('item_categories_price_ItemCategory').parentNode.style.display ='none';
document.forms[0].style.display ='none';
let isMouseDown = false;
const months = document.querySelectorAll('.month');
getAllDays();
function getPriceforMonth(y, m, c, callback) {
  $.ajax({
    url: "/item/categories/price/" + c + "/ajax",
    type: "POST",
    data: {
      year: y,
      month: m,
      id: c,
    },
    success: function(response) {
      var formattedResponse = JSON.stringify(response, null, 2);
     // console.log(formattedResponse);
      callback(response); // Rufe die Callback-Funktion auf und übergebe den Response
    }
  });
}

function getAllDays() {
  // Array zum Speichern der Tage
  const daysArray = [];

  // Alle .month Elemente auswählen
  const months = document.querySelectorAll('.month');

  // Durch jeden Monat iterieren
  months.forEach(month => {
    const year = month.getAttribute('data-year');
    const monthNumber = month.getAttribute('data-month');

    // Beispielaufruf der Funktion
    getPriceforMonth(year, (parseInt(monthNumber) + 1).toString().padStart(2, '0'), getIdFromUrl(), function(response) {
      // Hier kannst du den Response verarbeiten
      const preise = response;
//console.log (monthNumber)
      // Weitere Aktionen mit dem Response durchführen
      weeksAndDays(month, preise, daysArray);
    });
  });

  // Das Array mit allen Tagen zurückgeben
  return daysArray;
}
function weeksAndDays(month, preise, daysArray) {
  // Alle .week Elemente innerhalb des aktuellen .month Elements auswählen
  const weeks = month.querySelectorAll('.week');

  // Durch jede Woche iterieren
  weeks.forEach(week => {
    // Alle .day Elemente innerhalb der aktuellen .week auswählen
    const days = week.querySelectorAll('.day[data-day]');

    // Durch jeden Tag iterieren
    days.forEach(day => {
      const dayNumber = day.getAttribute('data-day');
      const year = day.parentNode.parentNode.getAttribute('data-year');
      const monthNumber = day.parentNode.parentNode.getAttribute('data-month');
      // Datum im Format YYYY-MM-DD erstellen
      const dayToCheck = new Date(year + '-' + (parseInt(monthNumber) + 1).toString().padStart(2, '0') + '-' + dayNumber.padStart(2, '0'));
      let preisFuerTag = null;

      for (let preis of preise) {
        const startDate = new Date(preis.start);
        const endDate = new Date(preis.end);
        if (dayToCheck >= startDate && dayToCheck <= endDate) {
          preisFuerTag = preis.price;
          break; // Beende die Schleife, wenn der Preis gefunden wurde
        }
      }

      if (preisFuerTag !== null) {
        const PriceDiv = document.createElement('div');
        PriceDiv.classList.add('price','bg-info');
        PriceDiv.textContent = preisFuerTag+' €';
        day.appendChild(PriceDiv);
      }

      // Objekt mit dem Datum und dem Element erstellen und zum Array hinzufügen
      daysArray.push({ date: dayToCheck.toISOString().split('T')[0], element: day });
    });
  });
}
function getIdFromUrl() {
    const path = window.location.pathname;
    const lastSegment = path.split('/').pop();
    const number = parseInt(lastSegment, 10);
    return number;
}
function EventTracker (event){
  let issetStartYear = parseInt(document.getElementById('item_categories_price_start_year').value);
        let issetEndYear = parseInt(document.getElementById('item_categories_price_end_year').value);
        isMouseDown = false;
        if(issetStartYear != 2019 && issetEndYear != 2019){
            let clickedElements = document.getElementsByClassName("clicked");
            while (clickedElements.length) {
                clickedElements[0].classList.remove("clicked");
            }
            document.forms[0].style.display ='block';
            document.querySelectorAll('form[name="item_categories_price"] select').forEach(select => {
                select.parentNode.parentNode.parentNode.style.display = 'none';
            });
          document.getElementById('preise_festlegen').style.display = 'none';
        }
}
function createCalendar(startMonth, startYear, way) {
  const months = ["Januar", "Februar", "März", "April", "Mai", "Juni",
                 "Juli", "August", "September", "Oktober", "November", "Dezember"];
  const calendarContainer = document.createElement('div');
  calendarContainer.classList.add('calendar'); // Füge dem Kalender eine Klasse hinzu
  // Funktion zum Erstellen eines Monats-Divs
  function createMonthDiv(month, year) {
    const monthDiv = document.createElement('div');
    monthDiv.classList.add('month'); 
    monthDiv.addEventListener('mousemove', handleMouseMove);
    monthDiv.addEventListener('touchmove', handleMouseMove);
    monthDiv.addEventListener('mousedown', function(e) {
        isMouseDown = true;
    });
    monthDiv.addEventListener('touchstart', function(e) {
      isMouseDown = true;
      
    });  
    monthDiv.addEventListener('mouseup', EventTracker);
    monthDiv.addEventListener('touchend', EventTracker);
    monthDiv.setAttribute('data-month', `${month}`);  
    monthDiv.setAttribute('data-year', `${year}`); 
    const monthTitle = document.createElement('h2');
    monthTitle.textContent = `${months[month]} ${year}`;
    monthDiv.appendChild(monthTitle);
    function createWeekRow() {
        const weekRow = document.createElement('div');
        weekRow.classList.add('week');
        for (let i = 0; i < 7; i++) {
            const dayDiv = document.createElement('div');
            dayDiv.classList.add('day');
            weekRow.appendChild(dayDiv);
        }
        return weekRow;
    }
    function createWeekRowWeekday() {
        const weekRow = document.createElement('div');
        weekRow.classList.add('week');
        for (let i = 0; i < 7; i++) {
            const dayDiv = document.createElement('div');
            dayDiv.classList.add('weekday');
            weekRow.appendChild(dayDiv);
        }
        return weekRow;
    }
    //füge wochentage bezeichnung hinzu
    const firstWeekRow = createWeekRowWeekday();
    const daysOfWeek = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
    for (let i = 0; i < 7; i++) {
        const dayIndex = i % 7;
        firstWeekRow.childNodes[dayIndex].textContent = daysOfWeek[i];
    }
    monthDiv.appendChild(firstWeekRow);

    let date = new Date(year, month, 1);
    let weeks = [];
    while (date.getMonth() === month) {
        let week = [];
        do {
            week.push(new Date(date));
            date.setDate(date.getDate() + 1);
        } while (date.getDay() !== 1 && date.getMonth() === month);
        weeks.push(week);
    }
// füge den day div den richtigen tag hinzu
    for (let i = 0; i < weeks.length; i++) {
        let week = weeks[i];
        const weekRow = createWeekRow();
        for (let j = 0; j < 7; j++) {
            if(j < week.length){
                if( week.length < 7 && i === 0){
                    let position = (7-week.length)+j
                    weekRow.childNodes[position].textContent = week[j].getDate();  
                    weekRow.childNodes[position].setAttribute('data-day', week[j].getDate());  
                }else {
                    weekRow.childNodes[j].textContent = week[j].getDate(); 
                    weekRow.childNodes[j].setAttribute('data-day', week[j].getDate());
                }
            }
              
        }
        monthDiv.appendChild(weekRow);
    }
    
    return monthDiv;
}
  // Erstelle und füge die beiden Monate zum Kalender-Div hinzu
    let secondStartYear;
  let firstStartYear;
  if(startMonth === 11 && way === 1  ){
    firstStartYear = startYear;
    secondStartYear = startYear+1;
  }else if(startMonth === 0 && way === 1){
    secondStartYear = startYear+1;
    firstStartYear = startYear+1;
  }else if(startMonth === 11 && way === 0){
    secondStartYear = startYear;
    firstStartYear = startYear-1;
  }else{
    secondStartYear = startYear;
    firstStartYear = startYear;
  }
  const firstMonthDiv = createMonthDiv(startMonth, firstStartYear);
  calendarContainer.appendChild(firstMonthDiv);
  const secondMonthDiv = createMonthDiv((startMonth + 1) % 12, secondStartYear);
  calendarContainer.appendChild(secondMonthDiv);
  return calendarContainer;
}
function nextYear() {
  // Aktuelle Monate abrufen
  const currentMonthDivs = document.querySelectorAll('.month');
  const currentMonthString = currentMonthDivs[0].querySelector('h2').textContent.split(' ')[0]; // Ersten Monat ermitteln
  const currentYear = parseInt(currentMonthDivs[0].querySelector('h2').textContent.split(' ')[1])+1;
  const months = ["Januar", "Februar", "März", "April", "Mai", "Juni",
  "Juli", "August", "September", "Oktober", "November", "Dezember"];
  // Aktuellen Monat als Index im Array ermitteln
  const currentMonthIndex = months.indexOf(currentMonthString);
  // Kalender mit neuen Monaten neu erstellen und rendern
  const newCalendar = createCalendar(currentMonthIndex,currentYear, 1);
  document.querySelector('.calendar').remove()
  document.getElementById('preise_festlegen').appendChild(newCalendar);
  getAllDays()
}
function nextMonth() {
    // Aktuelle Monate abrufen
    const currentMonthDivs = document.querySelectorAll('.month');
    const currentMonthString = currentMonthDivs[0].querySelector('h2').textContent.split(' ')[0]; // Ersten Monat ermitteln
    const secendcurrentMonthString = currentMonthDivs[1].querySelector('h2').textContent.split(' ')[0]; // Zweiter Monat ermitteln
    const currentYear = parseInt(currentMonthDivs[0].querySelector('h2').textContent.split(' ')[1]);
 

  const months = ["Januar", "Februar", "März", "April", "Mai", "Juni",
  "Juli", "August", "September", "Oktober", "November", "Dezember"];

// Aktuellen Monat als Index im Array ermitteln
const currentMonthIndex = months.indexOf(currentMonthString);
// Nächsten Monat berechnen (mit Array-Index)
const Month = (currentMonthIndex + 1) % 12;
// Numerischen Wert des nächsten Monats bestimmen

    // Nächste Monate berechnen
    
  let nextMonth;
   
    if (Month > 11) {
        nextMonth = 0;

      } else {
        nextMonth = Month;
      }
    // Kalender mit neuen Monaten neu erstellen und rendern
    const newCalendar = createCalendar(nextMonth,currentYear, 1);
    document.querySelector('.calendar').remove()
    document.getElementById('preise_festlegen').appendChild(newCalendar);
    getAllDays()
}
function beforYear() {
  // Aktuelle Monate abrufen
  const currentMonthDivs = document.querySelectorAll('.month');
  const currentMonthString = currentMonthDivs[0].querySelector('h2').textContent.split(' ')[0]; // Ersten Monat ermitteln
  const currentYear = parseInt(currentMonthDivs[0].querySelector('h2').textContent.split(' ')[1])-1;
  const months = ["Januar", "Februar", "März", "April", "Mai", "Juni",
  "Juli", "August", "September", "Oktober", "November", "Dezember"];
  // Aktuellen Monat als Index im Array ermitteln
  const currentMonthIndex = months.indexOf(currentMonthString);
  // Kalender mit neuen Monaten neu erstellen und rendern
  const newCalendar = createCalendar(currentMonthIndex,currentYear, 1);
  document.querySelector('.calendar').remove()
  document.getElementById('preise_festlegen').appendChild(newCalendar);
  getAllDays()
}
function beforMonth() {
    // Aktuelle Monate abrufen
    const currentMonthDivs = document.querySelectorAll('.month');
    const currentMonthString = currentMonthDivs[0].querySelector('h2').textContent.split(' ')[0]; // Ersten Monat ermitteln
    const currentYear = parseInt(currentMonthDivs[0].querySelector('h2').textContent.split(' ')[1]);
  const months = ["Januar", "Februar", "März", "April", "Mai", "Juni",
  "Juli", "August", "September", "Oktober", "November", "Dezember"];
    // Aktuellen Monat als Index im Array ermitteln
    const currentMonthIndex = months.indexOf(currentMonthString);
    // Vorherigen Monat berechnen (mit Array-Index)
    const beforeMonthIndex = (currentMonthIndex - 1 + 12) % 12;
    const beforeMonthstr = months[beforeMonthIndex];
    // Numerischen Wert des vorherigen Monats bestimmen
    const Month = beforeMonthIndex; 
    let beforeMonth ;
    // Vorherige Monate berechnen
    if (Month  === 0) {
      beforeMonth = 11;
    }else{
        beforeMonth = Month;
    }
    // Kalender mit neuen Monaten neu erstellen und rendern
    const newCalendar = createCalendar(beforeMonth, currentYear, 0);
    document.querySelector('.calendar').remove()
    document.getElementById('preise_festlegen').appendChild(newCalendar);
    getAllDays()
}

function handleMouseMove(event) {
  let targetDay
  if (!isMouseDown && !event.touches) return; // Only handle if mouse is down
  if (event.touches){
    event.preventDefault();
    for (const touch of event.touches) {
      const x = touch.clientX;
      const y = touch.clientY;
       targetDay = document.elementFromPoint(x, y).closest('.day[data-day]');
      
    } 
  }else{
     targetDay = event.target.closest('.day[data-day]');
  }
  
  if (!targetDay) return;
  
  targetDay.classList.add('clicked')
  let clickedDay = document.querySelector('.clicked');
  const currentDay = targetDay;
 
  //übertragen der auswahl ins formular
  let Startday = clickedDay.getAttribute('data-day');

  let Startmonth = parseInt(clickedDay.parentNode.parentNode.getAttribute('data-month'))+1;
  let Startyear = clickedDay.parentNode.parentNode.getAttribute('data-year');
  let Endday = currentDay.getAttribute('data-day');
  let Endmonth = parseInt(currentDay.parentNode.parentNode.getAttribute('data-month'))+1;
  let Endyear = currentDay.parentNode.parentNode.getAttribute('data-year');
  let CategoryName = document.getElementById('price_headline').getAttribute('data-ItemCategory');
  selectElement('item_categories_price_start_day', Startday);
  selectElement('item_categories_price_start_month', Startmonth);
  selectElement('item_categories_price_start_year', Startyear);
  selectElement('item_categories_price_end_day', Endday);
  selectElement('item_categories_price_end_month', Endmonth);
  selectElement('item_categories_price_end_year', Endyear);
  document.getElementById('price_headline').innerHTML = 'Preis für '+CategoryName+' vom <br> '+ Startday+'.'+Startmonth+'.'+Startyear +' bis '+Endday+'.'+Endmonth+'.'+Endyear+' festlegen';
  //document.getElementById('item_categories_price_end_month').selectElement.value ='Apr';
  getDaysBetween(clickedDay, currentDay);
 if(parseInt(Startmonth)< parseInt(Endmonth)){
    const dayElements = currentDay.parentNode.parentNode.getElementsByClassName("day");
    for (const element of dayElements) {
        const dataDay = parseInt(element.getAttribute("data-day"));
        if (!isNaN(dataDay) && dataDay > Endday) {
            element.style.backgroundColor='transparent';
          //  console.log('transpren 1');
            // Hier kannst du mit dem ausgewählten Element arbeiten  element.style.backgroundColor='transparent';
        }
    }
 }else{
    const dayElements = clickedDay.parentNode.parentNode.getElementsByClassName("day");
    const elternelement = clickedDay.parentNode.parentNode.parentNode;
    const zweitesKind = elternelement.children[1];
    // Alle .day-Elemente im zweiten Kind durchlaufen und die Hintergrundfarbe setzen
    const dayElemente = zweitesKind.querySelectorAll('.day');
    if(parseInt(zweitesKind.getAttribute('data-month'))+1!=parseInt(Startmonth)){
      dayElemente.forEach(dayElement => {
      //  console.log(zweitesKind);
        dayElement.style.backgroundColor = 'transparent';
      });
    }
    
    for (const element of dayElements) {
        const dataDay = parseInt(element.getAttribute("data-day"));
        if (!isNaN(dataDay) && dataDay > Endday) {
            element.style.backgroundColor='transparent';
         //   console.log('transpren 3');
            // Hier kannst du mit dem ausgewählten Element arbeiten  element.style.backgroundColor='transparent';
        }
    }
 }
 


}
function selectElement(id, valueToSelect) {
    let element = document.getElementById(id);

    element.value = valueToSelect;
}
function getPosition(element){
    return Array.from(element.parentNode.children).indexOf(element);
}
function setzeHintergrundfarbe(startWert, endWert, month) {
    let alleDayElemente = document.getElementsByClassName('day');
   ;
    for (let i = 0; i < alleDayElemente.length; i++) {
        let aktuellesElement = alleDayElemente[i];
         
        if(aktuellesElement.parentNode.parentNode.getAttribute('data-month') === month){
          
            let dataDayWert = parseInt(aktuellesElement.getAttribute('data-day'));
           
            if (dataDayWert >= startWert && dataDayWert <= endWert) {
                    aktuellesElement.style.backgroundColor = 'yellow'; 
                  //  console.log(aktuellesElement);
            }
        }
    }
}
function getDaysBetween(startDay, endDay) {

    if (!startDay || !endDay) return [];
      setzeHintergrundfarbe(startDay.getAttribute('data-day'), endDay.getAttribute('data-day'),startDay.parentNode.parentNode.getAttribute('data-month') );
    if(startDay.parentNode.parentNode.getAttribute('data-month') != endDay.parentNode.parentNode.getAttribute('data-month')){
        setzeHintergrundfarbe(1, endDay.getAttribute('data-day'),endDay.parentNode.parentNode.getAttribute('data-month') );
        setzeHintergrundfarbe(startDay.getAttribute('data-day'), 31,startDay.parentNode.parentNode.getAttribute('data-month') );
    }
   
}
// Beispiel: Den Kalender im Dokumentbody anzeigen



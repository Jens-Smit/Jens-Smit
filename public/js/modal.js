

function convertTimestamp(timestamp) {
  var d = new Date(timestamp * 1000),
    yyyy = d.getFullYear(),
    mm = ('0' + (d.getMonth() + 1)).slice(-2),
    dd = ('0' + d.getDate()).slice(-2);
  return yyyy + '-' + mm + '-' + dd;
}
function openModal(userId) {
    $.ajax({
        url: "/user/"+userId+"/contrect/",
        type: "GET",
        success: function(response) {
            $("#modal-body").html(response);
            $("#modal").modal("show");
        },
        error: function() {
            alert("Es gab einen Fehler beim Laden des Vertragsdaten-Moduls");
        }
    });
}
function saveDienstplan(userId) {
    var formDataArray = $('#addDenstplan').serializeArray();
    console.log(formDataArray);
    // Neues Objekt mit dem gewünschten Wert hinzufügen
    formDataArray.push({name: 'user', value: userId});
    console.log(formDataArray);
    // Array in eine serialisierte Zeichenfolge konvertieren
    var formData = $.param(formDataArray);
    var url = "/dienstplan/dienstplan_save";
    $.ajax({
      url: url,
      type: "POST",
      data: formData,
      success: function(response) {
        if(response = true){
            closeModal();
         
            console.log("Erfolgreich gesendet: " + response) ;
        }
        else{
            
            console.log("fehler: " + response) ;
        }
      },
      error: function() {
        alert("Es gab einen Fehler beim Senden des Formulars");
      }
    });
  }
 
  function openModal1(userId) {
    $.ajax({
      url: "/user/"+userId+"/dienstplan/",
      type: "GET",
      success: function(response) {
        $("#modal-body").html(response);
        $("#modal").modal("show");
        $('#addDenstplan').submit(function(event){
          event.preventDefault();
          console.log("erste funktion");
          saveDienstplan(userId);
        });
      },
      error: function() {
        alert("Es gab einen Fehler beim Laden des Vertragsdaten-Moduls");
      }
    });
  }
function openModal_objekt_dienstplan(userId) {
    $.ajax({
        url: "/objekt/"+userId+"/dienstplan/",
        type: "GET",
        success: function(response) {
            $("#modal-body").html(response);
            $("#modal").modal("show");
        },
        error: function() {
            alert("Es gab einen Fehler beim Laden des Vertragsdaten-Moduls");
        }
    });
}
function closeModal() {
    $('#modal').modal('hide');
}
function send_data(event) {
    var value = $(this);
    
    var element = document.querySelector('.dienst_item');
    var kommen = element.dataset.kommen;
    var gehen = element.dataset.gehen;
    $('#dienste_kommen_time_hour').val(kommen.split(':')[0]);
    $('#dienste_kommen_time_minute').val(kommen.split(':')[1]);
    var minutes_kommen = kommen.split(':')[1];
    if (minutes_kommen != "00") {
      $('#dienste_kommen_time_minute').val(minutes_kommen);
    } else {
      $('#dienste_kommen_time_minute').val(00);
    }
    $('#dienste_gehen_time_hour').val(gehen.split(':')[0]);
    var minutes_gehen = gehen.split(':')[1];
    if (minutes_gehen != "00") {
      $('#dienste_gehen_time_minute').val(minutes_gehen);
    } else {
      $('#dienste_gehen_time_minute').val(00);
    }
    handleFormSubmit(event);
    
   
   
}
function handleFormSubmit(event) {
   
    event.preventDefault();
    const formData = $('form').serializeArray();
    let kommen = "";
    let gehen = "";
    let user = "";
    let dienstplan = "";
   
    for (let i = 0; i < formData.length; i++) {
        const name = formData[i].name;
        const value = formData[i].value;
          
        if (name === "dienste[kommen][date][year]") {
            const year = value;
            const month = formData[i + 3].value.toString().padStart(2, '0');
            const day = formData[i + 4].value.toString().padStart(2, '0');
            const hour = formData[i +1 ].value.toString().padStart(2, '0');
            let minute = formData[i +2].value.toString().padStart(2, '0');
            if (minute >= 1) {
                
            }else{
                minute = "00";
            }
            kommen = year+"-"+month+"-"+day+" "+hour+":"+minute+":00";
            

        }
       
        if (name === "dienste[user]"){
            user = value;
        }
         
        if (name === "dienste[dienstplan]"){
            dienstplan = value;
        }
         
        if (name === "dienste[gehen][date][year]") {
            const year = value;
            const month = formData[i -2].value.toString().padStart(2, '0');
            const day = formData[i - 1].value.toString().padStart(2, '0');
            const hour = formData[i + 1].value.toString().padStart(2, '0');
            let minute = formData[i + 2].value.toString().padStart(2, '0');
            if (minute >= 1) {
                
            }else{
                minute = "00";
            }
            gehen = year+"-"+month+"-"+day+" "+hour+":"+minute+":00";
        }
         
    }
    
    $.ajax({
        type: 'POST',
        url: "/dienstplan/dienste_save", // Gibt die Aktion des Formulars als URL an
        data: {
            "kommen": kommen,
            "gehen": gehen,
            "user": user,
            "dienstplan": dienstplan
        },
        success: function(response) {
            saveScrollPosition();  
           
        }
    });
}
// Funktion zur Berechnung der Entfernung zwischen zwei Koordinaten (in Kilometern)
function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius der Erde in km
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const d = R * c; // Entfernung in km
    return d;
}
// Funktion zur Konvertierung von Grad in Bogenmaß
function deg2rad(deg) {
    return deg * (Math.PI / 180)
}
function openModal__addDienste(dienstplanId,date, user) {
    $("#modal").modal("show");
    $("#modal-body").html("Lade...");
    
    
    $.ajax({
        type: "GET",
        url: "/dienstplan/dienste/",
        data: {
            'date': date,
            'user': user,
            'dienstplan': dienstplanId
        },
        dataType: "text",
        success: function(response) {
            $("#modal-body").html(response);
            $("#modal").modal("show");
            $('#dienste_kommen').children().slice(0, 2).hide();
            $('#dienste_gehen').children().slice(0, 2).hide();
            $('#dienste_user').hide();
            $('#dienste_dienstplan').hide();
            $('.dienst_delate').click(function() {
                saveScrollPosition();
                  
            });


        },
        error: function() {
            alert("Es gab einen Fehler beim Laden des Vertragsdaten-Moduls");
        }
    });

}
function dienst(dienstVorschlag) {
    var vorschlag = $(this).attr('id'); 
    const Times = {"1":{kommen:"10:00", gehen:"16:30"},"2":{kommen:"16:30",gehen:"23:00"}};
}
   
function scrollTable() {
   
   
      if (sessionStorage.getItem('pos') !== null) {
        var data = sessionStorage.getItem('pos');
        sessionStorage.clear();
        return data;
        
      } else {
        return 0;
      }
    
  
  
}


function saveScrollPosition() {
    
    var tableContainer = $('#table-div-container');
    var visibleWidth = tableContainer.width();
    var pos = 0;
    var btn_area = $('#btn_area').width();
    pos -= visibleWidth-btn_area-10;
    sessionStorage.setItem('pos', pos);
    location.reload();
}

function groupFieldsets() {
    const formDiv = document.querySelector('#form');
    const fieldsets = formDiv.querySelectorAll('fieldset');
    var count = 1
    var anzahl = fieldsets.length-2;
    for (let i = 0; i < fieldsets.length; i += 2) {
        const cardDiv = document.createElement('div');
        cardDiv.classList.add('card');
        cardDiv.classList.add('m-2');
        const cardHeaderDiv = document.createElement('div');
        cardHeaderDiv.classList.add('card-header');
        if(anzahl<= i){
            cardHeaderDiv.textContent = "Neuer Vorschlag";   
        }else{
        cardHeaderDiv.textContent = "Dienstvorschlag "+count;
    }
        cardDiv.appendChild(cardHeaderDiv);
        count++;
        const cardBodyDiv = document.createElement('div');
        cardBodyDiv.classList.add('card-body');

        cardBodyDiv.appendChild(fieldsets[i]);
        if (fieldsets[i + 1]) {
            cardBodyDiv.appendChild(fieldsets[i + 1]);
        }

        cardDiv.appendChild(cardBodyDiv);
        formDiv.appendChild(cardDiv);
    }
}



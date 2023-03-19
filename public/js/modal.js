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
function closeModal() {
    $('#modal').modal('hide');
}
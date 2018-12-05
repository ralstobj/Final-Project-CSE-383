$(document).ready(function(){
    $("#alert").hide();
});

function validateIdentity() {
    var dataFromForm = {};
    dataFromForm.user = $("#user").val();
    dataFromForm.password = $("#password").val();
    $.ajax({
            type: "POST",
            dataType: "json",
            url: "rest.php",
            contentType: 'application/json',
            data: JSON.stringify(dataFromForm),
            success: function(data){
                console.log(data);
                $("#alert").hide();
            },
            error: function(e){
                console.log(e.message);
                $("#alert").show();
            }
    });
}


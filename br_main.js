$(document).ready(function(){
    $("#alert").hide();
    $('#form').on('submit', function(e) {
        if(checkAll() == false){
         e.preventDefault(); 
         } else {
            validateIdentity();
         }
    });
    $("#user").change(function(){
        checkUserName();
    });
    $("#password").change(function(){
        checkPassword();
    });

});

function validateIdentity() {
    var dataFromForm = {};
    dataFromForm.user = $("#user").val();
    dataFromForm.password = $("#password").val();
    $.ajax({
            type: 'POST',
            url: 'rest.php/v1/user',
            contentType: 'application/json',
            data: JSON.stringify(dataFromForm),
            success: function(data){
                console.log(data);
                console.log(data[0].token);
                $("#alert").hide();
            },
            error: function( req, status, err ) {
    		console.log( 'something went wrong', status, err );
                $("#alert").text("Incorrect username or password.");
                $("#alert").show();
            }
    });
}
function checkUserName(){
    if($("#user").val().length == 0){
        return 1;
    }else{
        $("#alert").hide();
        return 0;
    }
}

function checkPassword(){
    if($("#password").val().length == 0){
        return 1;
    }else{
        $("#alert").hide();
        return 0;
    }
}

function checkAll(){
    var count = 0;
    count += checkUserName();
    count += checkPassword();

    if(count == 0){
        $("#alert").hide();
        return true;
    }
    else{
        $("#alert").text("Please enter a username and password.");
        $("#alert").show();
        return false;
    }
}
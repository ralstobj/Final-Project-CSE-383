var token;
$(document).ready(function(){
    $("#alert").hide();
    $('#recordText').hide();
    $('#summaryTable').hide();
    $('#historyTable').hide();
    $("#historyTable").removeClass("hidden");
    $("#summaryTable").removeClass("hidden");
    $("#alert").removeClass("hidden");
    $("#recordText").removeClass("hidden");
    
    $('#form').on('submit', function(e) {
        if(checkAll() == false){
         e.preventDefault(); 
         } else {
            e.preventDefault();
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
                token = data.token;
                $("#alert").hide();
                $("#form").hide();
                $("#authText").hide();
                $('#recordText').show();
                buttons();
                summary();
                history();
            },
            error: function( req, status, err ) {
    		console.log( 'something went wrong ', status, err );
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

function buttons(){
    $.ajax({
        type: 'GET',
        url: 'rest.php/v1/items',
        success: function(data){
            for (i=0; i < data.items.length; i++){
                var specificItem = data.items[i];
                var pk = specificItem.pk;
                var item = specificItem.item;
                var button = document.createElement("button");
                $(button).text(''+item+'').addClass('btn btn-primary');
                $(button).attr("onClick", 'addAndUpdate('+pk+')');
                console.log(button);
                $("#buttonDiv").append(button);
                $("#alert").hide();
            }
        },
        error: function( req, status, err ) {
            console.log( 'something went wrong ', status, err );
            $("#alert").text("Unable to retrieve item list").show();
        }
});
}
function history(){
    $.ajax({
        type: 'GET',
        url: 'rest.php/items/'+token,
        success: function(data){
            var tr;
            // for (var i = 0; i < data.items.length; i++) {
            //     var instance = data.items[i];
            //     tr = $('<tr/>');
            //     tr.append("<td>" + instance.item + "</td>");
            //     tr.append("<td>" + instance.timestamp + "</td>");
            //     $('#historyTable').append(tr);
            // }
            $('#historyTable').show();
            $("#alert").hide();
        },
        error: function( req, status, err ) {
            console.log( 'something went wrong ', status, err );
            $("#alert").text("Unable to retrieve history of diary.").show();
        }
});
}

function summary(){
    $.ajax({
        type: 'GET',
        url: 'rest.php/v1/itemsSummary/'+token,
        success: function(data){
            var tr;
            // for (var i = 0; i < data.items.length; i++) {
            //     var instance = data.items[i];
            //     tr = $('<tr/>');
            //     tr.append("<td>" + instance.item + "</td>");
            //     tr.append("<td>" + instance.count + "</td>");
            //     $('#summaryTable').append(tr);
            // }
            $('#summaryTable').show();
            $("#alert").hide();
        },
        error: function( req, status, err ) {
            console.log( 'something went wrong ', status, err );
            $("#alert").text("Unable to retrieve summary of diary.").show();
        }
});
}

function addAndUpdate(fk){
    var data = {};
    data.token = token;
    data.itemFK = fk;
    $.ajax({
            type: 'POST',
            url: 'rest.php/v1/items',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(data){
                clearTables();
                summary();
                history();
            },
            error: function( req, status, err ) {
    		    console.log( 'something went wrong ', status, err );
                $("#alert").text("Unable to add item.").show();
            }
    });
}

function clearTables(){
    $("#summaryTable").html('');
    $("#historyTable").html('');
}
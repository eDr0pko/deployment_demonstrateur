$(document).ready(function(){

    /*----Users----*/
    $("#users-btn").click(function(){
        $.post("request.php", { action: "load_users_data" }, function(response){
            $("#users").html(response);
        });
    });
    /*----Songs----*/
    $("#songs-btn").click(function(){
        $.post("request.php", { action: "load_songs_data" }, function(response){
            $("#songs").html(response);
        });
    });
    
});

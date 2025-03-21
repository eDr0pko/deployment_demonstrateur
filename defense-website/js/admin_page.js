$(document).ready(function(){
    /*----Live Search----*/
    $("#search").keyup(function() {
        var input = $(this).val();
        if (input != "") {
            $.post("lib/request.php", { action: "live_search", search: input }, function(response) {
                $("#search-result").html(response);
            });
        } else {
            $("#search-result").html("");
        }
    });

    $(document).on("click", ".search-result", function() {
        var table = $(this).data("table"); 
        var email = $(this).data("email");
        var id_song = $(this).data("id_song");
        var id_album = $(this).data("id_album");
    
        $.post("lib/request.php", { action: "add_details", table: table, email:email, id_song:id_song, id_album: id_album }, function(response) {
            $("#details-container").html(response);
        });
    });
    

    /*----tab choose----*/

    $("#tab-btn").click(function(){
        let table = $("#tableau-option").val();
        if (table === "") {
            alert("Veuillez sÃ©lectionner une table !");
            return;
        }
        
        $.post("lib/request.php", { action: "load_tab_data", "tableau-option": table }, function(response){
            $("#tab").html(response);
        });
    });

    /*----Users----*/
    $("#users-btn").click(function(){
        $.post("lib/request.php", { action: "load_users_data" }, function(response){
            $("#users").html(response);
        });
    });
    /*----Songs----*/
    $("#songs-btn").click(function(){
        $.post("lib/request.php", { action: "load_songs_data" }, function(response){
            $("#songs").html(response);
        });
    });
    /*----Playlist songs----*/
    $("#playlist-songs-btn").click(function(){
        $.post("lib/request.php", { action: "load_playlist_songs_data" }, function(response){
            $("#playlist-songs").html(response);
        });
    });
    /*----Playlist----*/
    $("#playlist-btn").click(function(){
        $.post("lib/request.php", { action: "load_playlist_data" }, function(response){
            $("#playlist").html(response);
        });
    });
    /*----Likes----*/
    $("#likes-btn").click(function(){
        $.post("lib/request.php", { action: "load_likes_data" }, function(response){
            $("#likes").html(response);
        });
    });
    /*----Comment----*/
    $("#comment-btn").click(function(){
        $.post("lib/request.php", { action: "load_comment_data" }, function(response){
            $("#comment").html(response);
        });
    });
    /*----Artist----*/
    $("#artist-btn").click(function(){
        $.post("lib/request.php", { action: "load_artist_data" }, function(response){
            $("#artist").html(response);
        });
    });
    /*----Appartient----*/
    $("#appartient-btn").click(function(){
        $.post("lib/request.php", { action: "load_appartient_data" }, function(response){
            $("#appartient").html(response);
        });
    });
    /*----Album----*/
    $("#album-btn").click(function(){
        $.post("lib/request.php", { action: "load_album_data" }, function(response){
            $("#album").html(response);
        });
    });
    /*----Admin----*/
    $("#admin-btn").click(function(){
        $.post("lib/request.php", { action: "load_admin_data" }, function(response){
            $("#admin").html(response);
        });
    });
});



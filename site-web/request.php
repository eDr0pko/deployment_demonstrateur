<?php
    include 'db.php';

    function load_users_data($conn){
        $sql = "SELECT * FROM users";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo "<table>";
            echo "<thead><tr><th>Mail (PK)</th><th>Lastname</th><th>firstname</th><th>password</th><th>profile_picture</th></tr></thead>"; 
            while ($row = mysqli_fetch_assoc($result)) { 
                echo "<tr>";            
                echo "<th>$row[mail]</th>";
                echo "<td> $row[lastname] </td>";
                echo "<td> $row[firstname] </td>";
                echo "<td> $row[password] </td>";
                echo "<td> $row[profile_picture] </td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function load_songs_data($conn){
        $sql = "SELECT * FROM songs";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo "<table>";
            echo "<thead><tr><th>Id_song</th><th>name</th><th>song</th><th>picture</th><th>Id_artist</th></tr></thead>";
            while ($row = mysqli_fetch_assoc($result)) {  
                echo "<tr>";            
                echo "<th scope=\"row\">$row[id_song]</th>";
                echo "<td> $row[name] </td>";
                echo "<td> $row[song] </td>";
                echo "<td> $row[picture] </td>";
                echo "<td> $row[id_artist] </td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    

    if (isset($_POST['action']) && $_POST['action'] == "load_users_data") {
        load_users_data($conn);
    }
    if (isset($_POST['action']) && $_POST['action'] == "load_songs_data") {
        load_songs_data($conn);
    }
?>

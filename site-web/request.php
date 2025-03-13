<?php
include 'db.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';

// Récupérer les artistes
if ($type == 'artists') {
  $sql = "SELECT username FROM artist JOIN users ON artist.mail = users.mail";
  $result = $conn->query($sql);
  $artists = [];
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $artists[] = $row;
    }
  }
  echo json_encode($artists);
}

// Récupérer les chansons
if ($type == 'songs') {
  $sql = "SELECT name, time FROM songs";
  $result = $conn->query($sql);
  $songs = [];
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $songs[] = $row;
    }
  }
  echo json_encode($songs);
}

$conn->close();
?>

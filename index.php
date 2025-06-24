<?php
$servername = "localhost";
$username = "sampleuser";
$password = "SamplePass123!";
$dbname = "sampledb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch data
$sql = "SELECT id, name FROM users";
$result = $conn->query($sql);

echo "<h2>User List</h2>";
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "id: " . $row["id"]. " - Name: " . $row["name"]. "<br>";
  }
} else {
  echo "0 results";
}

$conn->close();
?>

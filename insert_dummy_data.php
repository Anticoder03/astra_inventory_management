<?php
require_once 'includes/config.php';

// Read the SQL file
$sql = file_get_contents('dummy_data.sql');

// Execute multi query
if (mysqli_multi_query($conn, $sql)) {
    echo "Dummy data inserted successfully!";
} else {
    echo "Error inserting dummy data: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 
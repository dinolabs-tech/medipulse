<?php
session_start();
function log_action($conn, $user_id, $action)
{
    $sql = "INSERT INTO logs (user_id, action) VALUES ('$user_id', '$action')";
    $conn->query($sql);
}

?>
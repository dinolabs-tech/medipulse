<?php
session_start();
function log_action($conn, $user_id, $action)
{
    $branch_id = $_SESSION['branch_id'] ?? null;
    $sql = "INSERT INTO logs (user_id, branch_id, action) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $branch_id, $action);
    $stmt->execute();
    $stmt->close();
}

?>

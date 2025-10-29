<?php
session_start();
function log_action($conn, $user_id, $action, $branch_id = NULL)
{
    $branch_id_sql = $branch_id ? "'$branch_id'" : "NULL";
    $sql = "INSERT INTO logs (user_id, action, branch_id) VALUES ('$user_id', '$action', $branch_id_sql)";
    $conn->query($sql);
}

?>

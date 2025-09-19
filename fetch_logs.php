<?php
include 'database/db_connection.php';

$sql = "SELECT l.id, l.user_id, u.username, l.event_type, l.ip_address, l.user_agent, l.created_at
        FROM session_logs l
        LEFT JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => htmlspecialchars($row['id']),
        'username' => htmlspecialchars($row['username'] ?? 'Guest'),
        'event_type' => htmlspecialchars(ucfirst($row['event_type'])),
        'ip_address' => htmlspecialchars($row['ip_address']),
        'user_agent' => htmlspecialchars(substr($row['user_agent'], 0, 60)) . '...',
        'created_at' => htmlspecialchars($row['created_at'])
    ];
}

header('Content-Type: application/json');
echo json_encode($data);

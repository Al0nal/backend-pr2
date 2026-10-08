<?php

header('Content-Type: application/json; charset=utf-8');

$mysqli = new mysqli(
    "db",
    "user",
    "password",
    "appDB"
);

if ($mysqli->connect_error) {
    http_response_code(500);

    echo json_encode([
        "error" => "Database connection failed"
    ]);

    exit;
}

$mysqli->set_charset("utf8mb4");
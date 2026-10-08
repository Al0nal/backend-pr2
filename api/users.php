<?php

require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : null;


switch ($method) {

    case 'GET':

        if ($id !== null) {

            $stmt = $mysqli->prepare(
                "SELECT ID AS id, name, surname
                 FROM users
                 WHERE ID = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $result = $stmt->get_result();

            $user = $result->fetch_assoc();

            if ($user) {
                echo json_encode($user);
            } else {
                http_response_code(404);

                echo json_encode([
                    "error" => "User not found"
                ]);
            }

        } else {

            $result = $mysqli->query(
                "SELECT ID AS id, name, surname
                 FROM users"
            );

            $users = [];

            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            echo json_encode($users);
        }

        break;


    case 'POST':

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            !isset($data['name']) ||
            !isset($data['surname'])
        ) {
            http_response_code(400);

            echo json_encode([
                "error" => "name and surname are required"
            ]);

            break;
        }

        $stmt = $mysqli->prepare(
            "INSERT INTO users (name, surname)
             VALUES (?, ?)"
        );

        $stmt->bind_param(
            "ss",
            $data['name'],
            $data['surname']
        );

        $stmt->execute();

        http_response_code(201);

        echo json_encode([
            "id" => $mysqli->insert_id,
            "name" => $data['name'],
            "surname" => $data['surname']
        ]);

        break;


    case 'PUT':

        if ($id === null) {
            http_response_code(400);

            echo json_encode([
                "error" => "id is required"
            ]);

            break;
        }

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            !isset($data['name']) ||
            !isset($data['surname'])
        ) {
            http_response_code(400);

            echo json_encode([
                "error" => "name and surname are required"
            ]);

            break;
        }

        $stmt = $mysqli->prepare(
            "UPDATE users
             SET name = ?, surname = ?
             WHERE ID = ?"
        );

        $stmt->bind_param(
            "ssi",
            $data['name'],
            $data['surname'],
            $id
        );

        $stmt->execute();

        echo json_encode([
            "message" => "User updated"
        ]);

        break;


    case 'DELETE':

        if ($id === null) {
            http_response_code(400);

            echo json_encode([
                "error" => "id is required"
            ]);

            break;
        }

        $stmt = $mysqli->prepare(
            "DELETE FROM users
             WHERE ID = ?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();

        if ($stmt->affected_rows === 0) {

            http_response_code(404);

            echo json_encode([
                "error" => "User not found"
            ]);

        } else {

            echo json_encode([
                "message" => "User deleted"
            ]);
        }

        break;


    default:

        http_response_code(405);

        echo json_encode([
            "error" => "Method not allowed"
        ]);
}
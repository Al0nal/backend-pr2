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
                "SELECT
                    o.ID AS id,
                    o.user_id,
                    u.name AS user_name,
                    u.surname AS user_surname,
                    o.product,
                    o.quantity
                 FROM orders o
                 JOIN users u
                    ON u.ID = o.user_id
                 WHERE o.ID = ?"
            );

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $result = $stmt->get_result();

            $order = $result->fetch_assoc();

            if ($order) {
                echo json_encode($order);
            } else {
                http_response_code(404);

                echo json_encode([
                    "error" => "Order not found"
                ]);
            }

        } else {

            $result = $mysqli->query(
                "SELECT
                    o.ID AS id,
                    o.user_id,
                    u.name AS user_name,
                    u.surname AS user_surname,
                    o.product,
                    o.quantity
                 FROM orders o
                 JOIN users u
                    ON u.ID = o.user_id"
            );

            $orders = [];

            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }

            echo json_encode($orders);
        }

        break;


    case 'POST':

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (
            !isset($data['user_id']) ||
            !isset($data['product'])
        ) {
            http_response_code(400);

            echo json_encode([
                "error" => "user_id and product are required"
            ]);

            break;
        }

        $quantity = $data['quantity'] ?? 1;

        $stmt = $mysqli->prepare(
            "INSERT INTO orders
                (user_id, product, quantity)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "isi",
            $data['user_id'],
            $data['product'],
            $quantity
        );

        $stmt->execute();

        http_response_code(201);

        echo json_encode([
            "id" => $mysqli->insert_id,
            "user_id" => $data['user_id'],
            "product" => $data['product'],
            "quantity" => $quantity
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
            !isset($data['user_id']) ||
            !isset($data['product']) ||
            !isset($data['quantity'])
        ) {
            http_response_code(400);

            echo json_encode([
                "error" =>
                    "user_id, product and quantity are required"
            ]);

            break;
        }

        $stmt = $mysqli->prepare(
            "UPDATE orders
             SET user_id = ?,
                 product = ?,
                 quantity = ?
             WHERE ID = ?"
        );

        $stmt->bind_param(
            "isii",
            $data['user_id'],
            $data['product'],
            $data['quantity'],
            $id
        );

        $stmt->execute();

        echo json_encode([
            "message" => "Order updated"
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
            "DELETE FROM orders
             WHERE ID = ?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();

        if ($stmt->affected_rows === 0) {

            http_response_code(404);

            echo json_encode([
                "error" => "Order not found"
            ]);

        } else {

            echo json_encode([
                "message" => "Order deleted"
            ]);
        }

        break;


    default:

        http_response_code(405);

        echo json_encode([
            "error" => "Method not allowed"
        ]);
}
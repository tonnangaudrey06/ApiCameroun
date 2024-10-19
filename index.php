<?php
header('Content-Type: application/json');

$file = 'ville.json';

// Lire les données JSON
function readData() {
    global $file;
    return json_decode(file_get_contents($file), true);
}

// Écrire les données JSON
function writeData($data) {
    global $file;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $cities = readData();

        if (isset($_GET['id'])) {
            // Récupérer une ville par ID
            $city = array_filter($cities, fn($c) => $c['id'] == $_GET['id']);
            echo json_encode(array_values($city));

        } elseif (isset($_GET['region'])) {
            // Récupérer les villes d'une région spécifique
            $region = $_GET['region'];
            $citiesByRegion = array_filter($cities, fn($c) => $c['region'] == $region);
            echo json_encode(array_values($citiesByRegion));

        } else {
            // Récupérer toutes les villes
            echo json_encode($cities);
        }
        break;

    case 'POST':
        // Ajouter une nouvelle ville
        $data = json_decode(file_get_contents("php://input"), true);
        $cities = readData();
        $newId = end($cities)['id'] + 1;
        $data['id'] = $newId;
        $cities[] = $data;
        writeData($cities);
        echo json_encode(['id' => $newId]);
        break;

    case 'PUT':
        // Modifier une ville
        $data = json_decode(file_get_contents("php://input"), true);
        $cities = readData();
        foreach ($cities as &$city) {
            if ($city['id'] == $data['id']) {
                $city['name'] = $data['name'];
                $city['region'] = $data['region'];
            }
        }
        writeData($cities);
        echo json_encode(['message' => 'City updated']);
        break;

    case 'DELETE':
        // Supprimer une ville
        $id = $_GET['id'];
        $cities = readData();
        $cities = array_filter($cities, fn($c) => $c['id'] != $id);
        writeData(array_values($cities));
        echo json_encode(['message' => 'City deleted']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}
<?php
require_once 'db_config.php';
header('Content-Type: application/json');

$kod = $_GET['kod'] ?? '';

if (preg_match('/^\d{2}-\d{3}$/', $kod)) {
    // 1. Próba pobrania z gotowej lokalnej bazy danych na produkcji
    try {
        // Zakładamy, że tabela może nazywać się kody_pocztowe, kody, lub podobnie
        // i zawiera kolumny kod / miasto / miejscowosc
        $stmt = $conn->prepare("SELECT miejscowosc as miasto FROM kody_pocztowe WHERE kod = ? LIMIT 1");
        $stmt->execute([$kod]);
        $row = $stmt->fetch();
        if ($row && !empty($row['miasto'])) {
            echo json_encode(['success' => true, 'miasto' => $row['miasto'], 'source' => 'db']);
            exit;
        }
    } catch (PDOException $e) {
        // Tabela kody_pocztowe nie istnieje lub inna struktura - ignorujemy błąd i używamy API
    }

    // 2. Fallback na zewnętrzne API (Zippopotamus), jeśli nie ma w bazie lokalnej
    $url = "https://api.zippopotam.us/pl/" . $kod;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['places'][0])) {
            $city = $data['places'][0]['place name'];
            $lat = $data['places'][0]['latitude'];
            $lng = $data['places'][0]['longitude'];
            echo json_encode([
                'success' => true, 
                'miasto' => $city,
                'lat' => $lat,
                'lng' => $lng,
                'source' => 'api'
            ]);
            exit;
        }
    }
}

echo json_encode(['success' => false]);

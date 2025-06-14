<?php
header('Content-Type: application/json');
include 'connessione.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email richiesta']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Indirizzo email non valido']);
    exit();
}

try {
    // Find bookings for this email that are not cancelled
    $stmt = $conn->prepare("
        SELECT p.id, p.nome, p.servizio, p.data_prenotazione, p.orario, p.stato,
               CONCAT(o.nome, ' ', o.cognome) as operatore_nome
        FROM prenotazioni p 
        LEFT JOIN operatori o ON p.operatore_id = o.id
        WHERE p.email = ? AND (p.stato IS NULL OR p.stato != 'Cancellata') 
        ORDER BY p.data_prenotazione DESC, p.orario DESC
    ");
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        // Format date and time for display
        $row['data_prenotazione'] = date('d/m/Y', strtotime($row['data_prenotazione']));
        $row['orario'] = date('H:i', strtotime($row['orario']));
        $bookings[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'count' => count($bookings)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Errore del server: ' . $e->getMessage()
    ]);
}
?>
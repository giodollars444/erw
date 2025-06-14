<?php
session_start();
if (!isset($_SESSION['logged'])) {
    header("Location: login.php");
    exit();
}
include 'connessione.php';

// Handle booking status changes
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE prenotazioni SET stato = 'Confermata' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'cancel') {
        $stmt = $conn->prepare("UPDATE prenotazioni SET stato = 'Cancellata' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: admin.php");
    exit();
}

// Handle data deletion
if (isset($_POST['delete_all_data'])) {
    // First delete from cancellation_tokens (child table)
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("DELETE FROM cancellation_tokens");
    $conn->query("DELETE FROM prenotazioni");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    header("Location: admin.php");
    exit();
}

// Get statistics
$statistiche = ['Confermata' => 0, 'In attesa' => 0, 'Cancellata' => 0];
$stato_exists = $conn->query("SHOW COLUMNS FROM prenotazioni LIKE 'stato'")->num_rows > 0;

if ($stato_exists) {
    $totali = $conn->query("SELECT stato, COUNT(*) as totale FROM prenotazioni GROUP BY stato");
    if ($totali) {
        while ($row = $totali->fetch_assoc()) {
            $stato = $row['stato'] ?? 'In attesa';
            $statistiche[$stato] = $row['totale'];
        }
    }
} else {
    $total_result = $conn->query("SELECT COUNT(*) as totale FROM prenotazioni");
    if ($total_result) {
        $total_row = $total_result->fetch_assoc();
        $statistiche['In attesa'] = $total_row['totale'];
    }
}

// Calculate total revenue
$totale_ricavi = 0;
$servizi_exists = $conn->query("SHOW TABLES LIKE 'servizi'")->num_rows > 0;
$escludi_exists = $conn->query("SHOW COLUMNS FROM prenotazioni LIKE 'escludi_ricavi'")->num_rows > 0;

if ($servizi_exists && $stato_exists) {
    $where_condition = "p.stato = 'Confermata'";
    if ($escludi_exists) {
        $where_condition .= " AND (p.escludi_ricavi = 0 OR p.escludi_ricavi IS NULL)";
    }
    
    $entrate = $conn->query("SELECT SUM(s.prezzo) as totale FROM prenotazioni p JOIN servizi s ON p.servizio = s.nome WHERE $where_condition");
    if ($entrate) {
        $entrate_row = $entrate->fetch_assoc();
        $totale_ricavi = $entrate_row['totale'] ?? 0;
    }
}

// Get booking dates for filter
$date_prenotazioni = array();
$query = "SELECT DISTINCT data_prenotazione FROM prenotazioni ORDER BY data_prenotazione DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (isset($row['data_prenotazione'])) {
            $date_prenotazioni[] = [
                'raw' => $row['data_prenotazione'],
                'formatted' => date('d/m/Y', strtotime($row['data_prenotazione']))
            ];
        }
    }
}

// Get unique dates for calendar
$date_uniche = array_unique(array_column($date_prenotazioni, 'formatted'));
sort($date_uniche);

// Get all bookings for the table
$prenotazioni_query = "SELECT p.*, CONCAT(o.nome, ' ', o.cognome) as operatore_nome FROM prenotazioni p LEFT JOIN operatori o ON p.operatore_id = o.id ORDER BY p.data_prenotazione DESC, p.id DESC LIMIT 100";
$prenotazioni = $conn->query($prenotazioni_query);

// Get last booking ID for real-time updates
$last_booking_id = 0;
$last_id_query = $conn->query("SELECT MAX(id) as max_id FROM prenotazioni");
if ($last_id_query) {
    $last_id_row = $last_id_query->fetch_assoc();
    $last_booking_id = $last_id_row['max_id'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard Admin - Old School Barber</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: #ffffff;
            min-height: 100vh;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: #d4af37;
            border: none;
            border-radius: 8px;
            color: #1a1a2e;
            padding: 0.8rem;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .mobile-menu-btn:hover {
            background: #ffd700;
            transform: scale(1.05);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 80px;
            height: 100vh;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar.expanded {
            width: 280px;
        }

        .sidebar-header {
            padding: 0 1.5rem;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            justify-content: center;
        }

        .sidebar.expanded .sidebar-header {
            padding: 0 2rem;
            justify-content: flex-start;
        }

        .sidebar-logo {
            font-size: 2rem;
            color: #d4af37;
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            transition: opacity 0.3s ease;
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .sidebar.expanded .sidebar-title {
            opacity: 1;
            width: auto;
        }

        .sidebar-toggle {
            position: absolute;
            top: 1rem;
            right: -15px;
            width: 30px;
            height: 30px;
            background: #d4af37;
            border: none;
            border-radius: 50%;
            color: #1a1a2e;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: #ffd700;
            transform: scale(1.1);
        }

        .sidebar-nav {
            list-style: none;
            padding: 0 1rem;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0.5rem;
            color: #a0a0a0;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            justify-content: center;
        }

        .sidebar.expanded .sidebar-nav a {
            padding: 1rem;
            justify-content: flex-start;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            transform: translateX(5px);
        }

        .sidebar-nav i {
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav span {
            display: none;
        }

        .sidebar.expanded .sidebar-nav span {
            display: inline;
        }

        /* Main Content */
        .main {
            margin-left: 80px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .main.expanded {
            margin-left: 280px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem 2rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            text-decoration: none;
            border-radius: 12px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-2px);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-color, #d4af37);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-card.confirmed::before { background: #22c55e; }
        .stat-card.pending::before { background: #f59e0b; }
        .stat-card.cancelled::before { background: #ef4444; }
        .stat-card.revenue::before { background: #d4af37; }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .stat-card.confirmed .stat-icon { color: #22c55e; }
        .stat-card.pending .stat-icon { color: #f59e0b; }
        .stat-card.cancelled .stat-icon { color: #ef4444; }
        .stat-card.revenue .stat-icon { color: #d4af37; }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #a0a0a0;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .content-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .content-card h3 i {
            color: #d4af37;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            scrollbar-width: thin;
            scrollbar-color: #d4af37 rgba(255, 255, 255, 0.1);
        }

        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #d4af37;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            padding: 1rem 0.8rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            white-space: nowrap;
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            color: #d4af37;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            color: #e0e0e0;
            font-weight: 400;
            font-size: 0.9rem;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Status Badges */
        .status {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .status.confermata {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status.in-attesa {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .status.cancellata {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Action Buttons */
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin: 0 0.2rem;
            white-space: nowrap;
        }

        .action-btn.confirm {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .action-btn.cancel {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Filter Controls */
        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            color: #a0a0a0;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .filter-group select {
            padding: 0.6rem 1rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #ffffff;
            font-size: 0.9rem;
            min-width: 150px;
            cursor: pointer;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #d4af37;
            background: rgba(255, 255, 255, 0.12);
        }

        .filter-group select option {
            background: #1a1a2e;
            color: #ffffff;
        }

        .toggle-filters {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .toggle-btn {
            padding: 0.6rem 1rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: #ffffff;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-btn.active {
            background: rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.3);
            color: #d4af37;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        /* Management Links */
        .management-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .management-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .management-card:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .management-card h4 {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .management-card h4 i {
            color: #d4af37;
            font-size: 1.3rem;
        }

        .management-card p {
            color: #a0a0a0;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* Danger Zone */
        .danger-zone {
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .danger-zone h4 {
            color: #f87171;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .danger-btn {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .danger-btn:hover {
            background: rgba(239, 68, 68, 0.3);
            transform: translateY(-2px);
        }

        /* Real-time indicator */
        .realtime-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #4ade80;
            margin-left: auto;
        }

        .realtime-dot {
            width: 8px;
            height: 8px;
            background: #4ade80;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .management-links {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main {
                margin-left: 0;
                padding: 1rem;
                padding-top: 4rem;
            }

            .main.expanded {
                margin-left: 0;
            }

            .header {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
            }

            .header-title {
                font-size: 1.6rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .content-card {
                padding: 1.5rem;
            }

            .table-container {
                margin: 0 -1rem;
                border-radius: 0;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .toggle-filters {
                justify-content: center;
            }
            
            th, td {
                padding: 0.8rem 0.5rem;
                font-size: 0.8rem;
            }
            
            .action-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.7rem;
                margin: 0.1rem;
            }
        }

        @media (max-width: 480px) {
            .main {
                padding: 0.5rem;
                padding-top: 3.5rem;
            }
            
            .header {
                padding: 0.8rem;
                margin-bottom: 1rem;
            }
            
            .header-title {
                font-size: 1.4rem;
            }
            
            .stat-card {
                padding: 1.5rem;
            }
            
            .stat-value {
                font-size: 2rem;
            }
            
            .content-card {
                padding: 1rem;
            }
            
            .content-card h3 {
                font-size: 1.1rem;
            }
            
            .management-card {
                padding: 1rem;
            }
            
            .action-btn {
                display: block;
                margin: 0.2rem 0;
                text-align: center;
                width: 100%;
            }
            
            th, td {
                padding: 0.6rem 0.3rem;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 360px) {
            .main {
                padding: 0.3rem;
                padding-top: 3rem;
            }
            
            .header-title {
                font-size: 1.2rem;
            }
            
            .stat-value {
                font-size: 1.8rem;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .action-btn, .toggle-btn, .danger-btn {
                min-height: 44px;
                min-width: 44px;
            }
            
            .sidebar-toggle {
                width: 40px;
                height: 40px;
            }
            
            .mobile-menu-btn {
                padding: 1rem;
                min-height: 44px;
                min-width: 44px;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

<button class="mobile-menu-btn" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <div class="sidebar-header">
        <i class="fas fa-cut sidebar-logo"></i>
        <span class="sidebar-title">Admin Panel</span>
    </div>
    
    <ul class="sidebar-nav">
        <li><a href="#" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li><a href="gestione_prenotazioni.php"><i class="fas fa-calendar-alt"></i><span>Prenotazioni</span></a></li>
        <li><a href="gestione_operatori.php"><i class="fas fa-scissors"></i><span>Operatori</span></a></li>
        <li><a href="impostazioni.php"><i class="fas fa-cog"></i><span>Impostazioni</span></a></li>
        <li><a href="index.php"><i class="fas fa-arrow-left"></i><span>Torna al sito</span></a></li>
    </ul>
</div>

<div class="main" id="main">
    <div class="header">
        <h1 class="header-title">Dashboard Amministratore</h1>
        <div class="header-actions">
            <div class="realtime-indicator">
                <div class="realtime-dot"></div>
                <span>Aggiornamento automatico</span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card confirmed">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value" id="stat-confirmed"><?php echo $statistiche['Confermata']; ?></div>
            <div class="stat-label">Confermate</div>
        </div>

        <div class="stat-card pending">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-value" id="stat-pending"><?php echo $statistiche['In attesa']; ?></div>
            <div class="stat-label">In Attesa</div>
        </div>

        <div class="stat-card cancelled">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <div class="stat-value" id="stat-cancelled"><?php echo $statistiche['Cancellata']; ?></div>
            <div class="stat-label">Cancellate</div>
        </div>

        <div class="stat-card revenue">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-euro-sign"></i>
                </div>
            </div>
            <div class="stat-value" id="stat-revenue">€<?php echo number_format($totale_ricavi, 2); ?></div>
            <div class="stat-label">Ricavi Totali</div>
        </div>
    </div>

    <!-- Management Links -->
    <div class="management-links">
        <a href="gestione_prenotazioni.php" class="management-card">
            <h4><i class="fas fa-calendar-alt"></i>Gestione Prenotazioni</h4>
            <p>Configura giorni lavorativi, fasce orarie e limiti di prenotazione</p>
        </a>
        
        <a href="gestione_operatori.php" class="management-card">
            <h4><i class="fas fa-scissors"></i>Gestione Operatori</h4>
            <p>Aggiungi, modifica e gestisci gli operatori del barbiere</p>
        </a>
        
        <a href="impostazioni.php" class="management-card">
            <h4><i class="fas fa-cog"></i>Impostazioni Sistema</h4>
            <p>Configura le impostazioni generali e cambia la password</p>
        </a>
        
        <a href="index.php" class="management-card">
            <h4><i class="fas fa-globe"></i>Visualizza Sito</h4>
            <p>Vai al sito pubblico per vedere come appare ai clienti</p>
        </a>
    </div>

    <!-- Recent Bookings -->
    <div class="content-grid">
        <div class="content-card">
            <h3><i class="fas fa-list"></i>Ultime Prenotazioni</h3>
            
            <div class="filter-controls">
                <div class="filter-group">
                    <label for="dateFilter">Filtra per data:</label>
                    <select id="dateFilter">
                        <option value="">Tutte le date</option>
                        <?php foreach ($date_prenotazioni as $data): ?>
                            <option value="<?php echo $data['raw']; ?>"><?php echo $data['formatted']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="toggle-filters">
                    <button class="toggle-btn active" id="showAll">Tutte</button>
                    <button class="toggle-btn" id="excludeCancelled">Escludi Cancellate</button>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Telefono</th>
                            <th>Data</th>
                            <th>Orario</th>
                            <th>Servizio</th>
                            <th>Operatore</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsTableBody">
                        <?php
                        if ($prenotazioni && $prenotazioni->num_rows > 0) {
                            $i = 1;
                            while ($row = $prenotazioni->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>'.$i++.'</td>';
                                echo '<td>'.htmlspecialchars($row['nome'] ?? 'N/A').'</td>';
                                echo '<td>'.htmlspecialchars($row['telefono'] ?? 'N/A').'</td>';
                                
                                if (isset($row['data_prenotazione']) && $row['data_prenotazione']) {
                                    $data = date_create($row['data_prenotazione']);
                                    echo '<td>'.($data ? date_format($data, 'd/m/Y') : 'N/A').'</td>';
                                } else {
                                    echo '<td>N/A</td>';
                                }
                                
                                echo '<td>'.(isset($row['orario']) ? date('H:i', strtotime($row['orario'])) : 'N/A').'</td>';
                                echo '<td>'.htmlspecialchars($row['servizio'] ?? 'N/A').'</td>';
                                echo '<td>'.htmlspecialchars($row['operatore_nome'] ?? 'Non assegnato').'</td>';
                                
                                if (isset($row['stato'])) {
                                    $stato = $row['stato'] ?? 'In attesa';
                                    $statusClass = '';
                                    if ($stato === 'Confermata') $statusClass = 'confermata';
                                    elseif ($stato === 'In attesa') $statusClass = 'in-attesa';
                                    elseif ($stato === 'Cancellata') $statusClass = 'cancellata';
                                    
                                    echo '<td><span class="status '.$statusClass.'">'.htmlspecialchars($stato).'</span></td>';
                                    
                                    echo '<td>';
                                    if ($stato !== 'Cancellata') {
                                        echo '<a href="?action=confirm&id='.$row['id'].'" class="action-btn confirm" onclick="return confirm(\'Confermare questa prenotazione?\')">';
                                        echo '<i class="fas fa-check"></i>Conferma</a>';
                                        
                                        echo '<a href="?action=cancel&id='.$row['id'].'" class="action-btn cancel" onclick="return confirm(\'Cancellare questa prenotazione?\')">';
                                        echo '<i class="fas fa-times"></i>Cancella</a>';
                                    } else {
                                        echo 'Nessuna azione';
                                    }
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="9" style="text-align: center; color: #a0a0a0;">Nessuna prenotazione trovata</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="danger-zone">
        <h4><i class="fas fa-exclamation-triangle"></i>Zona Pericolosa</h4>
        <p style="color: #a0a0a0; margin-bottom: 1rem;">
            Attenzione: questa azione eliminerà TUTTI i dati delle prenotazioni in modo permanente.
        </p>
        <form method="POST" onsubmit="return confirm('ATTENZIONE: Questa azione eliminerà TUTTI i dati delle prenotazioni in modo permanente. Sei sicuro di voler continuare?')">
            <button type="submit" name="delete_all_data" class="danger-btn">
                <i class="fas fa-trash-alt"></i>
                Elimina Tutti i Dati
            </button>
        </form>
    </div>
</div>

<script>
let sidebarCollapsed = true;
let mobileOpen = false;
let lastBookingId = <?php echo $last_booking_id; ?>;
let excludeCancelled = false;
let selectedDate = '';

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main');
    const toggleIcon = document.querySelector('.sidebar-toggle i');
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        mobileOpen = !mobileOpen;
        sidebar.classList.toggle('mobile-open');
    } else {
        sidebarCollapsed = !sidebarCollapsed;
        sidebar.classList.toggle('expanded');
        main.classList.toggle('expanded');
        toggleIcon.className = sidebarCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
    }
}

// Real-time updates
function checkForNewBookings() {
    fetch(`check_new_bookings.php?lastId=${lastBookingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.hasNewBookings) {
                lastBookingId = data.newLastId;
                updateStats();
                updateBookingsTable();
                updateDateFilter();
            }
        })
        .catch(error => console.error('Error checking for new bookings:', error));
}

function updateStats() {
    fetch('get_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-confirmed').textContent = data.stats.Confermata || 0;
                document.getElementById('stat-pending').textContent = data.stats['In attesa'] || 0;
                document.getElementById('stat-cancelled').textContent = data.stats.Cancellata || 0;
                document.getElementById('stat-revenue').textContent = '€' + (data.totalRevenue ? parseFloat(data.totalRevenue).toFixed(2) : '0.00');
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

function updateBookingsTable() {
    const params = new URLSearchParams();
    if (excludeCancelled) params.append('exclude_cancelled', '1');
    if (selectedDate) params.append('date', selectedDate);
    
    fetch(`get_bookings_table.php?${params.toString()}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('bookingsTableBody').innerHTML = html;
        })
        .catch(error => console.error('Error updating bookings table:', error));
}

function updateDateFilter() {
    fetch('get_booking_dates.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dateFilter = document.getElementById('dateFilter');
                const currentValue = dateFilter.value;
                
                // Clear existing options except "Tutte le date"
                dateFilter.innerHTML = '<option value="">Tutte le date</option>';
                
                // Add new options
                data.dates.forEach(date => {
                    const option = document.createElement('option');
                    option.value = date.raw;
                    option.textContent = date.formatted;
                    dateFilter.appendChild(option);
                });
                
                // Restore selected value if it still exists
                if (currentValue) {
                    const optionExists = Array.from(dateFilter.options).some(option => option.value === currentValue);
                    if (optionExists) {
                        dateFilter.value = currentValue;
                    } else {
                        // If the selected date no longer exists, reset to "Tutte le date"
                        dateFilter.value = '';
                        selectedDate = '';
                        updateBookingsTable();
                    }
                }
            }
        })
        .catch(error => console.error('Error updating date filter:', error));
}

// Filter controls
document.getElementById('dateFilter').addEventListener('change', function() {
    selectedDate = this.value;
    updateBookingsTable();
    
    // If no bookings found for selected date, check and remove if necessary
    if (selectedDate) {
        setTimeout(() => {
            const tableBody = document.getElementById('bookingsTableBody');
            const noDataRow = tableBody.querySelector('td[colspan="9"]');
            if (noDataRow && noDataRow.textContent.includes('Nessuna prenotazione trovata')) {
                // Remove the date from filter and reset
                this.value = '';
                selectedDate = '';
                updateBookingsTable();
                updateDateFilter();
            }
        }, 500);
    }
});

document.getElementById('showAll').addEventListener('click', function() {
    excludeCancelled = false;
    this.classList.add('active');
    document.getElementById('excludeCancelled').classList.remove('active');
    updateBookingsTable();
});

document.getElementById('excludeCancelled').addEventListener('click', function() {
    excludeCancelled = true;
    this.classList.add('active');
    document.getElementById('showAll').classList.remove('active');
    updateBookingsTable();
});

// Start real-time updates
setInterval(checkForNewBookings, 5000);

// Close mobile sidebar when clicking outside
document.addEventListener('click', (e) => {
    const sidebar = document.getElementById('sidebar');
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    
    if (window.innerWidth <= 768 && mobileOpen && 
        !sidebar.contains(e.target) && 
        !mobileBtn.contains(e.target)) {
        toggleSidebar();
    }
});

// Handle window resize
window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-open');
        mobileOpen = false;
    }
});

// Touch device optimizations
if ('ontouchstart' in window) {
    document.body.classList.add('touch-device');
}

// Viewport height fix for mobile browsers
function setViewportHeight() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

setViewportHeight();
window.addEventListener('resize', setViewportHeight);
window.addEventListener('orientationchange', () => {
    setTimeout(setViewportHeight, 100);
});
</script>
</body>
</html>
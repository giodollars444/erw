<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Old School Barber - Prenota il tuo taglio</title>
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
            min-height: 100vh;
            color: #ffffff;
            overflow-x: hidden;
        }

        /* Background Animation */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo i {
            font-size: 2rem;
            color: #d4af37;
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: #a0a0a0;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .nav-links a:hover {
            color: #d4af37;
            background: rgba(212, 175, 55, 0.1);
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            gap: 3rem;
        }

        /* Section Styles */
        .section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
        }

        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 70px -12px rgba(0, 0, 0, 0.35);
        }

        .section-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .section-icon {
            font-size: 3rem;
            color: #d4af37;
            margin-bottom: 1rem;
            filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.3));
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: #a0a0a0;
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #e0e0e0;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a0a0;
            font-size: 1rem;
            z-index: 2;
        }

        input, select, textarea {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        input::placeholder, textarea::placeholder {
            color: #a0a0a0;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #d4af37;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
            transform: translateY(-1px);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2214%22%20height%3D%2210%22%20viewBox%3D%220%200%2014%2010%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M1%200l6%206%206-6%22%20stroke%3D%22%23a0a0a0%22%20stroke-width%3D%222%22%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 14px 10px;
        }

        select option {
            background: #1a1a2e;
            color: #ffffff;
            padding: 0.5rem;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 1.2rem 2rem;
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            border: none;
            border-radius: 12px;
            color: #1a1a2e;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(212, 175, 55, 0.4);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Availability Display */
        .availability-info {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .availability-info.available {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }

        .availability-info.limited {
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            color: #fbbf24;
        }

        .availability-info.unavailable {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .availability-info.hidden {
            display: none;
        }

        /* Verification Form Styles */
        .verification-form {
            max-width: 400px;
            margin: 0 auto;
        }

        .verification-results {
            margin-top: 2rem;
        }

        .booking-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .booking-service {
            font-size: 1.1rem;
            font-weight: 600;
            color: #d4af37;
        }

        .booking-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .booking-status.confermata {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }

        .booking-status.in-attesa {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            color: #a0a0a0;
            font-size: 0.9rem;
        }

        .booking-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .booking-detail i {
            color: #d4af37;
            width: 16px;
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .message.success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .message.info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(212, 175, 55, 0.3);
            border-top: 3px solid #d4af37;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Footer */
        .footer {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            padding: 0 2rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #a0a0a0;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #d4af37;
        }

        .footer-text {
            color: #666;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 1.5rem;
            }

            .section {
                padding: 2rem 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                padding: 0 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .container {
                padding: 1rem;
                gap: 2rem;
            }

            .section {
                padding: 1.5rem 1rem;
            }

            .section-title {
                font-size: 1.6rem;
            }

            .section-icon {
                font-size: 2.5rem;
            }

            input, select, textarea {
                padding: 0.9rem 0.9rem 0.9rem 2.8rem;
                font-size: 0.95rem;
            }

            .submit-btn {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }

            .booking-details {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .logo h1 {
                font-size: 1.4rem;
            }

            .logo i {
                font-size: 1.6rem;
            }

            .section-title {
                font-size: 1.4rem;
            }

            .section-subtitle {
                font-size: 1rem;
            }

            .nav-links {
                flex-direction: column;
                gap: 0.5rem;
            }

            input, select, textarea {
                padding: 0.8rem 0.8rem 0.8rem 2.5rem;
                font-size: 0.9rem;
            }

            .submit-btn {
                padding: 0.9rem 1.2rem;
                font-size: 0.95rem;
            }

            .booking-card {
                padding: 1rem;
            }

            .booking-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 360px) {
            .container {
                padding: 0.5rem;
            }

            .section {
                padding: 1.2rem 0.8rem;
            }

            .section-title {
                font-size: 1.2rem;
            }

            input, select, textarea {
                padding: 0.7rem 0.7rem 0.7rem 2.2rem;
                font-size: 0.85rem;
            }

            .submit-btn {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            input, select, textarea, .submit-btn {
                min-height: 44px;
            }

            .nav-links a {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }

        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .bg-animation::before {
                animation: none;
            }
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .section {
                border: 2px solid #ffffff;
            }

            input, select, textarea {
                border: 2px solid #ffffff;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>

    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-cut"></i>
                <h1>Old School Barber</h1>
            </div>
            <nav class="nav-links">
                <a href="#prenota">Prenota</a>
                <a href="#verifica">Verifica</a>
                <a href="cancel_booking.php">Cancella</a>
                <a href="admin.php">Admin</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Booking Section -->
        <section id="prenota" class="section">
            <div class="section-header">
                <i class="fas fa-scissors section-icon"></i>
                <h2 class="section-title">Prenota il tuo taglio</h2>
                <p class="section-subtitle">Scegli il servizio, la data e l'orario che preferisci</p>
            </div>

            <form method="POST" action="prenota.php" id="bookingForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome completo *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="nome" id="nome" placeholder="Il tuo nome completo" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="email" placeholder="la-tua-email@esempio.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Telefono</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="telefono" id="telefono" placeholder="+39 123 456 7890">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="servizio">Servizio *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-cut"></i>
                            <select name="servizio" id="servizio" required>
                                <option value="">Seleziona un servizio</option>
                                <?php
                                include 'connessione.php';
                                $servizi = $conn->query("SELECT nome FROM servizi ORDER BY nome");
                                if ($servizi && $servizi->num_rows > 0) {
                                    while ($row = $servizi->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['nome']) . '">' . htmlspecialchars($row['nome']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="operatore_id">Operatore (opzionale)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-tie"></i>
                            <select name="operatore_id" id="operatore_id">
                                <option value="">Nessuna preferenza</option>
                                <?php
                                $operatori = $conn->query("SELECT id, nome, cognome FROM operatori WHERE attivo = 1 ORDER BY nome, cognome");
                                if ($operatori && $operatori->num_rows > 0) {
                                    while ($row = $operatori->fetch_assoc()) {
                                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome'] . ' ' . $row['cognome']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="data_prenotazione">Data *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar"></i>
                            <input type="date" name="data_prenotazione" id="data_prenotazione" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="orario">Orario *</label>
                        <div class="input-wrapper">
                            <i class="fas fa-clock"></i>
                            <select name="orario" id="orario" required disabled>
                                <option value="">Prima seleziona una data</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="availability-info" class="availability-info hidden"></div>

                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    <i class="fas fa-calendar-plus"></i>
                    Prenota Appuntamento
                </button>
            </form>
        </section>

        <!-- Verification Section -->
        <section id="verifica" class="section">
            <div class="section-header">
                <i class="fas fa-search section-icon"></i>
                <h2 class="section-title">Verifica Prenotazioni</h2>
                <p class="section-subtitle">Controlla lo stato delle tue prenotazioni inserendo la tua email</p>
            </div>

            <div class="verification-form">
                <form id="verificationForm">
                    <div class="form-group">
                        <label for="verify_email">Indirizzo Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="verify_email" placeholder="la-tua-email@esempio.com" required>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-search"></i>
                        Cerca Prenotazioni
                    </button>
                </form>

                <div id="verification-loading" class="loading">
                    <div class="spinner"></div>
                    <p>Ricerca in corso...</p>
                </div>

                <div id="verification-results" class="verification-results"></div>
            </div>
        </section>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="#prenota">Prenota</a>
                <a href="#verifica">Verifica</a>
                <a href="cancel_booking.php">Cancella Prenotazione</a>
                <a href="admin.php">Area Admin</a>
            </div>
            <p class="footer-text">© 2024 Old School Barber. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('data_prenotazione');
            const timeSelect = document.getElementById('orario');
            const availabilityInfo = document.getElementById('availability-info');
            const submitBtn = document.getElementById('submitBtn');
            const verificationForm = document.getElementById('verificationForm');
            const verificationResults = document.getElementById('verification-results');
            const verificationLoading = document.getElementById('verification-loading');

            // Set minimum date to today
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateInput.min = tomorrow.toISOString().split('T')[0];

            // Date change handler
            dateInput.addEventListener('change', function() {
                const selectedDate = this.value;
                if (selectedDate) {
                    checkWorkingDay(selectedDate);
                } else {
                    resetTimeSlots();
                }
            });

            // Time change handler
            timeSelect.addEventListener('change', function() {
                const selectedDate = dateInput.value;
                const selectedTime = this.value;
                
                if (selectedDate && selectedTime) {
                    checkAvailability(selectedDate, selectedTime);
                } else {
                    hideAvailabilityInfo();
                    updateSubmitButton();
                }
            });

            function checkWorkingDay(date) {
                fetch(`check_working_day.php?date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.isWorkingDay) {
                            loadTimeSlots();
                        } else {
                            showAvailabilityInfo('Giorno non lavorativo. Seleziona un altro giorno.', 'unavailable');
                            resetTimeSlots();
                        }
                    })
                    .catch(error => {
                        console.error('Error checking working day:', error);
                        resetTimeSlots();
                    });
            }

            function loadTimeSlots() {
                fetch('get_time_slots.php')
                    .then(response => response.json())
                    .then(data => {
                        timeSelect.innerHTML = '<option value="">Seleziona un orario</option>';
                        
                        if (data.length > 0) {
                            data.forEach(slot => {
                                const option = document.createElement('option');
                                option.value = slot.orario;
                                option.textContent = slot.orario.substring(0, 5);
                                timeSelect.appendChild(option);
                            });
                            timeSelect.disabled = false;
                            hideAvailabilityInfo();
                        } else {
                            timeSelect.innerHTML = '<option value="">Nessun orario disponibile</option>';
                            showAvailabilityInfo('Nessun orario disponibile per questo giorno.', 'unavailable');
                        }
                        updateSubmitButton();
                    })
                    .catch(error => {
                        console.error('Error loading time slots:', error);
                        resetTimeSlots();
                    });
            }

            function checkAvailability(date, time) {
                fetch(`check_availability.php?date=${date}&time=${time}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            if (data.limit === 'unlimited') {
                                showAvailabilityInfo('✓ Disponibile', 'available');
                            } else if (data.remaining_spots > 1) {
                                showAvailabilityInfo(`✓ Disponibile (${data.remaining_spots} posti rimasti)`, 'available');
                            } else {
                                showAvailabilityInfo('✓ Disponibile (ultimo posto)', 'limited');
                            }
                        } else {
                            showAvailabilityInfo(`✗ ${data.message}`, 'unavailable');
                        }
                        updateSubmitButton();
                    })
                    .catch(error => {
                        console.error('Error checking availability:', error);
                        showAvailabilityInfo('Errore nel controllo disponibilità', 'unavailable');
                        updateSubmitButton();
                    });
            }

            function showAvailabilityInfo(message, type) {
                availabilityInfo.textContent = message;
                availabilityInfo.className = `availability-info ${type}`;
            }

            function hideAvailabilityInfo() {
                availabilityInfo.className = 'availability-info hidden';
            }

            function resetTimeSlots() {
                timeSelect.innerHTML = '<option value="">Prima seleziona una data</option>';
                timeSelect.disabled = true;
                hideAvailabilityInfo();
                updateSubmitButton();
            }

            function updateSubmitButton() {
                const isFormValid = document.getElementById('nome').value && 
                                  document.getElementById('servizio').value && 
                                  dateInput.value && 
                                  timeSelect.value && 
                                  !availabilityInfo.classList.contains('unavailable');
                
                submitBtn.disabled = !isFormValid;
            }

            // Form validation
            document.querySelectorAll('#bookingForm input, #bookingForm select').forEach(element => {
                element.addEventListener('change', updateSubmitButton);
                element.addEventListener('input', updateSubmitButton);
            });

            // Verification form handler
            verificationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const email = document.getElementById('verify_email').value;
                if (!email) return;

                verificationLoading.style.display = 'block';
                verificationResults.innerHTML = '';

                const formData = new FormData();
                formData.append('email', email);

                fetch('verify_booking.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    verificationLoading.style.display = 'none';
                    
                    if (data.success) {
                        if (data.bookings.length > 0) {
                            displayBookings(data.bookings);
                        } else {
                            verificationResults.innerHTML = `
                                <div class="message info">
                                    <i class="fas fa-info-circle"></i>
                                    Nessuna prenotazione attiva trovata per questo indirizzo email.
                                </div>
                            `;
                        }
                    } else {
                        verificationResults.innerHTML = `
                            <div class="message error">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    verificationLoading.style.display = 'none';
                    verificationResults.innerHTML = `
                        <div class="message error">
                            <i class="fas fa-exclamation-triangle"></i>
                            Errore durante la ricerca. Riprova più tardi.
                        </div>
                    `;
                });
            });

            function displayBookings(bookings) {
                let html = `
                    <div class="message success">
                        <i class="fas fa-check-circle"></i>
                        Trovate ${bookings.length} prenotazione/i attive.
                    </div>
                `;

                bookings.forEach(booking => {
                    const statusClass = booking.stato ? booking.stato.toLowerCase().replace(' ', '-') : 'in-attesa';
                    const statusText = booking.stato || 'In attesa';
                    
                    html += `
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-service">${booking.servizio}</div>
                                <div class="booking-status ${statusClass}">${statusText}</div>
                            </div>
                            <div class="booking-details">
                                <div class="booking-detail">
                                    <i class="fas fa-user"></i>
                                    <span>${booking.nome}</span>
                                </div>
                                <div class="booking-detail">
                                    <i class="fas fa-calendar"></i>
                                    <span>${booking.data_prenotazione}</span>
                                </div>
                                <div class="booking-detail">
                                    <i class="fas fa-clock"></i>
                                    <span>${booking.orario}</span>
                                </div>
                                ${booking.operatore_nome ? `
                                <div class="booking-detail">
                                    <i class="fas fa-user-tie"></i>
                                    <span>${booking.operatore_nome}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });

                verificationResults.innerHTML = html;
            }

            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
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
        });
    </script>
</body>
</html>
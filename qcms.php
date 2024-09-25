<?php
// Define the time limit (18h30) in 24-hour format
$timeLimit = "18:30";

// Get the current server time in "H:i" format (24-hour clock)
$currentTime = date("H:i");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $filePath = __DIR__ . "/qcms/{$id}.html";
    
     // Check if the current time is after 18h30
     if ($currentTime >= $timeLimit) {
        // Serve the corrected QCM content if the time is after 18h30
        if (file_exists($filePath)) {
            // Read and display the content of the corrected QCM
            echo file_get_contents($filePath);
        } else {
            echo "<h1>QCM non trouvé</h1>";
        }
    } else {
        // If before 18h30, show a friendly message
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Correction non disponible</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Poppins', sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background-color: #f0f8ff;
                    text-align: center;
                }
                .message-container {
                    max-width: 600px;
                    padding: 20px;
                    background-color: #ffffff;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    border-radius: 10px;
                }
                h1 {
                    font-size: 2.5em;
                    color: #333;
                    margin-bottom: 10px;
                }
                p {
                    font-size: 1.2em;
                    color: #666;
                    margin-bottom: 20px;
                }
                .emoji {
                    font-size: 3em;
                    margin-bottom: 20px;
                }
                .footer {
                    margin-top: 20px;
                    font-size: 0.9em;
                    color: #999;
                }
                @media (max-width: 768px) {
                    h1 {
                        font-size: 2em;
                    }
                    p {
                        font-size: 1em;
                    }
                }
            </style>
        </head>
        <body>
            <div class="message-container">
                <div class="emoji">⏳📚👩‍🎓👨‍🎓</div>
                <h1>Patience, chers élèves !</h1>
                <p>Les corrections ne seront disponibles qu'après <strong>18h30</strong> tous les jours pour éviter la fraude pendant l'examen.</p>
                <p>Revenez plus tard pour consulter vos réponses corrigées.</p>
                <div class="footer">Merci de votre compréhension 💡</div>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    echo "<h1>ID du QCM non fourni.</h1>";
}
?>
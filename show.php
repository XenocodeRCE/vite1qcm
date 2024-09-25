<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données JSON et le nombre de copies
    $jsonInput = $_POST['json_input'];
    $numCopies = intval($_POST['copies']);
    $maxQuestionsPerPage = intval($_POST['max_questions']); // Nombre de questions max par page

    
    /**
     * Fonction pour vérifier si le format est "text2quizz"
     */
    function isText2Quizz($input) {
        return strpos($input, 'QCM ||') !== false;
    }

    /**
     * Conversion du format "text2quizz" en JSON
     */
    function convertText2QuizzToJson($input) {
        $lines = explode("\n", trim($input));
        $questions = [];

        foreach ($lines as $line) {
            // Vérifier si la ligne commence par "QCM ||"
            if (strpos($line, 'QCM ||') === 0) {
                // Séparer la question et les réponses
                $parts = explode(' || ', $line);
                if (count($parts) === 3) {
                    $questionText = trim($parts[1]);
                    $answersText = trim($parts[2]);

                    // Séparer les réponses par "|"
                    $answers = explode('|', $answersText);
                    $choices = [];
                    $correctAnswers = [];

                    foreach ($answers as $answer) {
                        $answer = trim($answer);
                        if (strpos($answer, 'V:') === 0) {
                            // C'est une réponse correcte
                            $correctAnswer = substr($answer, 2); // Retirer le "V:"
                            $choices[] = $correctAnswer;
                            $correctAnswers[] = $correctAnswer;
                        } else {
                            // Réponse incorrecte
                            $choices[] = $answer;
                        }
                    }

                    // Ajouter la question au tableau
                    $questions[] = [
                        'question' => $questionText,
                        'choices' => $choices,
                        'correct_answer' => implode(', ', $correctAnswers) // Si plusieurs réponses correctes
                    ];
                }
            }
        }

        return json_encode($questions, JSON_PRETTY_PRINT);
    }

    /**
     * Si l'entrée est au format "text2quizz", la convertir en JSON
     */
    if (isText2Quizz($jsonInput)) {
        $jsonInput = convertText2QuizzToJson($jsonInput);
    }

    // Décoder le JSON
    $questions = json_decode($jsonInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Format JSON invalide. Veuillez vérifier vos données.');
    }


    // Fonction pour marquer les réponses correctes et générer le QCM corrigé
    function generateCorrectedQCM($questions, $uniqueId) {
        ob_start(); // Démarrer la capture du contenu HTML

        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>QCM Corrigé</title>
            <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    padding: 20px;
                }
                .question {
                    margin-bottom: 20px;
                }
                .choix {
                    margin-left: 20px;
                }
            </style>
        </head>
        <body>

        <h1>QCM Corrigé</h1>

        <div class="questionnaire">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question">
                    <p><strong><?= ($index + 1) ?> - <?= htmlentities($question['question']) ?></strong></p>
                    <?php foreach ($question['choices'] as $choice): ?>
                        <?php if ($choice == $question['correct_answer']): ?>
                            <p class="choix">☑ <?= htmlentities($choice) ?></p>
                        <?php else: ?>
                            <p class="choix">☐ <?= htmlentities($choice) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        </body>
        </html>
        <?php

        $htmlContent = ob_get_clean(); // Fin de la capture et retour du contenu
        $filePath = "../qcms/{$uniqueId}.html";

        // Sauvegarder le contenu HTML dans un fichier
        file_put_contents($filePath, $htmlContent);
    }

    // Générer un identifiant unique pour chaque copie
    function generateUniqueId() {
        return time() . rand(1000, 9999);
    }

    ?>
    
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCM à imprimer</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            /* Masquer le bouton d'impression lors de l'impression */
            .print-button {
                display: none;
            }

            /* Forcer un saut de page après chaque copie */
            .page {
                page-break-after: always;
            }
        }

        /* Conteneur principal pour l'entête et le questionnaire */
        .copy {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 100%;
        }

        /* Positionner le QR code en haut à droite */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
            margin-bottom: 10px; /* Réduire l'espace sous l'en-tête */
        }

        .header-left {
            width: 70%;
        }

        .header-right {
            width: 30%;
            text-align: right;
        }

        /* Questions en deux colonnes */
        .questionnaire {
            display: flex;
            flex-wrap: wrap;
            font-size: 12px; /* Réduire un peu la taille de la police */
            line-height: 1.2em; /* Réduire l'interligne */
        }

        /* Empêcher qu'une question soit coupée entre deux pages */
        .question {
            width: 50%;
            padding: 5px; /* Réduire l'espacement autour des questions */
            page-break-inside: avoid;
        }

        /* Réduire l'espacement entre les choix et la question */
        .question p:first-child {
            margin-bottom: 5px; /* Réduire l'espace entre la question et les choix */
            font-weight: bold;
        }

        /* Réduire l'espacement entre les choix */
        .choix {
            margin-left: 20px;
            margin-bottom: 3px; /* Réduire l'espace entre chaque choix */
        }

        /* Réduire la taille de l'image du QR code pour économiser de la place */
        .header-right img {
            width: 120px;
            height: 120px;
        }
    </style>
</head>
<body>

<div class="container my-4">
    <h1></h1>
    <!-- Bouton d'impression (sera masqué lors de l'impression) -->
    <button class="btn btn-primary mb-4 print-button" onclick="window.print()">Imprimer</button>

    <?php for ($i = 0; $i < $numCopies; $i++): ?>
        <div class="page">
            <!-- Générer un identifiant unique pour chaque copie -->
            <?php $uniqueId = generateUniqueId(); ?>

            <div class="copy">
                <!-- En-tête avec le QR code à droite -->
                <div class="header">
                    <div class="header-left">
                        <h2 style="font-size: 16px;">Merci d'écrire en CAPITALES</h2>
                        <label>Nom :</label> ________________________________________________<br>
                        <label>Prénom :</label> _____________________________________________<br>
                        <label>Classe :</label> ______________________________________________<br>
                        <p style="margin-top: 5px;">Coloriez en NOIR les bonnes réponses</p>
                    </div>
                    <div class="header-right">
                        <!-- Generating a QR code that links to the corrected version -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=https://philo-lycée.fr/qcms.php?id=<?= $uniqueId ?>" alt="QR Code">
                    </div>
                </div>

                <!-- Section des questions -->
                <div class="questionnaire">
                    <?php
                    // Mélanger les questions pour chaque copie
                    shuffle($questions);  // Mélange les questions de manière aléatoire

                    // Pagination des questions en fonction du nombre maximum par page
                    $totalQuestions = count($questions);
                    $currentQuestion = 0;

                    while ($currentQuestion < $totalQuestions):
                        // Afficher un groupe de questions par page
                        for ($q = 0; $q < $maxQuestionsPerPage && $currentQuestion < $totalQuestions; $q++, $currentQuestion++):
                            $question = $questions[$currentQuestion];
                            // Mélanger les choix de la question
                            shuffle($question['choices']);
                            ?>
                            <div class="question">
                                <p><strong><?= ($currentQuestion + 1) ?> - <?= htmlentities($question['question']) ?></strong></p>
                                <?php foreach ($question['choices'] as $choice): ?>
                                    <p class="choix">☐ <?= htmlentities($choice) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endfor; ?>
                        <!-- Ajouter un saut de page pour la prochaine page de questions -->
                        <?php if ($currentQuestion < $totalQuestions): ?>
                            <div class="page-break"></div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <?php
        // Générer la version corrigée et l'enregistrer dans un fichier HTML
        generateCorrectedQCM($questions, $uniqueId);
        ?>
    <?php endfor; ?>
</div>

</body>
</html>

<?php
}
?>
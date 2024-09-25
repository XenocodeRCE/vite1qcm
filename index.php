<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un QCM</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>

        textarea {
            width: 100%;
            height: 250px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 18px;
            resize: none;
            margin-bottom: 20px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
        }

        button {
            font-size: 18px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            width: 48%;
            cursor: pointer;
        }

        /* Green button for generate */
        .btn-generate {
            background-color: #28a745;
            color: white;
        }

        .btn-generate:disabled {
            background-color: #ccc;
        }

        /* Blue button for save */
        .btn-save {
            background-color: #007bff;
            color: white;
        }

        .modal-content {
            text-align: center;
            border-radius: 12px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <h1 class="my-4">Créer un QCM</h1>

    <form action="show.php" method="POST" id="qcm-form">
        <div class="form-group">
            <label for="json-input">Entrez vos questions au format JSON / <a href="https://text2quiz.vercel.app/">TEXT2QUIZZ</a>) / Écrivez un sujet et demandez à l'I.A.</label>
            <textarea class="form-control" id="json-input" name="json_input" rows="10" required placeholder='[
  {
    "question": "Quel est le plus grand océan de la Terre ?",
    "choices": [
      "Océan Atlantique",
      "Océan Pacifique",
      "Océan Indien",
      "Océan Arctique"
    ],
    "correct_answer": "Océan Pacifique"
  },
  {
    "question": "Quelle est la capitale de la France ?",
    "choices": [
      "Paris",
      "Londres",
      "Berlin",
      "Madrid"
    ],
    "correct_answer": "Paris"
  },
  {
    "question": "Quel est le plus haut sommet du monde ?",
    "choices": [
      "Mont Blanc",
      "Mont Everest",
      "K2",
      "Kangchenjunga"
    ],
    "correct_answer": "Mont Everest"
  }
]'>[
  {
    "question": "Quel est le plus grand océan de la Terre ?",
    "choices": [
      "Océan Atlantique",
      "Océan Pacifique",
      "Océan Indien",
      "Océan Arctique"
    ],
    "correct_answer": "Océan Pacifique"
  },
  {
    "question": "Quelle est la capitale de la France ?",
    "choices": [
      "Paris",
      "Londres",
      "Berlin",
      "Madrid"
    ],
    "correct_answer": "Paris"
  },
  {
    "question": "Quel est le plus haut sommet du monde ?",
    "choices": [
      "Mont Blanc",
      "Mont Everest",
      "K2",
      "Kangchenjunga"
    ],
    "correct_answer": "Mont Everest"
  }
]</textarea>
        </div>

        <div class="form-group">
            <label for="copies">Nombre de copies :</label>
            <input type="number" class="form-control" id="copies" name="copies" min="1" value="1" required>
        </div>

        <div class="form-group">
            <label for="max-questions">Nombre maximum de questions par page :</label>
            <input type="number" class="form-control" id="max-questions" name="max_questions" min="1" value="20" required>
        </div>

        <button type="submit" class="btn btn-primary">Imprimer le QCM</button>
        <button id="generateButton" class="btn btn-success" onclick="generateQCM()">🤖 Générer un QCM</button>
    </form>
</div>


<!-- Modal for loading status -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <p>Génération en cours, veuillez patienter...</p>
                <img src="https://media.giphy.com/media/3oEjI6SIIHBdRxXI40/giphy.gif" alt="Loading" width="100" height="100">
            </div>
        </div>
    </div>
</div>


<script>
    let loadingModalInstance;

    function generateSecureSessionId() {
        return crypto.randomUUID();  // UUID uique
    }

    function GET(pValue) {
        return fetch('https://xn--philo-lyce-j7a.fr/api/get.php?x=0&p=' + pValue, {
            method: 'GET',
        })
        .then(response => response.text());
    }

    function WAITANDGET(pValue, textarea) {
        GET(pValue).then(result => {
            if (result === "wait") {
                setTimeout(() => WAITANDGET(pValue, textarea), 3000);
            } else {
                // On receiving final response, hide the modal and display result in textarea
                result = result.replace("```json","");
                result = result.replace("```","");
                textarea.value = result;
                loadingModalInstance.hide(); // Properly hide the modal
            }
        });
    }

    async function ASK(prompt, sessionId) {
        try {
            const response = await fetch('https://xn--philo-lyce-j7a.fr/api/ask.php?x=0', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'p': prompt,
                    'session_id': sessionId
                })
            });

            const data = await response.text();
            return data;

        } catch (error) {
            console.error('Error:', error);
        }
    }

    function generateQCM() {
        const textarea = document.getElementById('json-input');
        const userInput = textarea.value.trim();

        if (userInput === "") {
            alert('Veuillez entrer des informations pour générer un QCM.');
            return;
        }

        const xmlExample = `
[
  {
    "question": "Quel est le plus grand océan de la Terre ?",
    "choices": [
      "Océan Atlantique",
      "Océan Pacifique",
      "Océan Indien",
      "Océan Arctique"
    ],
    "correct_answer": "Océan Pacifique"
  },
  {
    "question": "Quelle est la capitale de la France ?",
    "choices": [
      "Paris",
      "Londres",
      "Berlin",
      "Madrid"
    ],
    "correct_answer": "Paris"
  },
  {
    "question": "Quel est le plus haut sommet du monde ?",
    "choices": [
      "Mont Blanc",
      "Mont Everest",
      "K2",
      "Kangchenjunga"
    ],
    "correct_answer": "Mont Everest"
  }
]
        `;

        const finalPrompt = JSON.stringify(`Tu dois générer un QCM au format JSON basé sur le sujet suivant : ${userInput}\n\n Tu dois générer un QCM avec 10-20 questions. Voici le format à suivre, ce n'est qu'un exemple : ${xmlExample}\n\n`);

        const generateButton = document.getElementById('generateButton');
        generateButton.disabled = true;

        // Show loading modal
        loadingModalInstance = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModalInstance.show();

        const sessionId = generateSecureSessionId();

        ASK(finalPrompt, sessionId).then(result => {
            WAITANDGET(result, textarea);
        }).finally(() => {
            generateButton.disabled = false;
        });
    }

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>
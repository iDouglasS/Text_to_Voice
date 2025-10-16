<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Texto para Fala</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

</head>
<body>
    <h1>Texto para Fala</h1>

    <form id="ttsForm">
        @csrf
        <textarea name="text" id="text" placeholder="Digite seu texto aqui..."></textarea><br>
        <button type="submit">Falar</button>
    </form>

    <p id="error" class="error" style="display:none;"></p>

    <div id="result" style="display:none;">
        <h2>Texto:</h2>
        <p id="resultText"></p>

        <h2>Reprodução:</h2>
        <audio id="audioPlayer" controls autoplay></audio>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('ttsForm');
            const errorElem = document.getElementById('error');
            const resultDiv = document.getElementById('result');
            const resultText = document.getElementById('resultText');
            const audioPlayer = document.getElementById('audioPlayer');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                errorElem.style.display = 'none';
                resultDiv.style.display = 'none';

                const text = document.getElementById('text').value.trim();
                if (!text) {
                    errorElem.textContent = 'O texto é obrigatório.';
                    errorElem.style.display = 'block';
                    return;
                }

                const formData = new FormData(form);

                try {
                    const response = await fetch("{{ route('speak') }}", {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: formData,
                    });

                    const textResponse = await response.text();

                    if (!response.ok) {
                        try {
                            const errorData = JSON.parse(textResponse);
                            throw new Error(errorData.error || 'Erro desconhecido');
                        } catch {
                            throw new Error('Erro inesperado do servidor');
                        }
                    }

                    const data = JSON.parse(textResponse);
                    resultText.textContent = data.text;
                    audioPlayer.src = `data:audio/mp3;base64,${data.audio_base64}`;
                    resultDiv.style.display = 'block';

                } catch (error) {
                    errorElem.textContent = error.message;
                    errorElem.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>

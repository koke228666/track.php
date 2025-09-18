<?php

/*
alert(`$service_list = ${"[" + [...document.querySelectorAll('[service-code]')].map(a => `"${a.getAttribute('service-code')}"`).join(",") + "]"};
$language_list = [${availibleLanguageArray.map(item => `"${item}"`).join(", ")}];
`);
*/

$service_list = ["CDEK","EXP4PX","ASN","BOXBERRY","BESTEXP","CAINIAO","CHRONOFR","CSE","DHLGR","DHLRU","DPD","DPDRU","DPDPL","ELTEXP","ESHOP","8EXP","GBS","GLS","HHEXP","JOOM","KIT","LBCEXP","MYHERMDE","NPS","OCOURIER","PONYEXP","RUCDEK","SKY56","SKYNETEXP","CNZEX","SYTRACK","YW","XRU","FEDEX","ONEWRLD","MAXI","NEXP","NOVA","NOVAEXT","SL","CZRS","WINIT","XFWULIU","ZTO"];
$language_list = ["ru", "en", "uk", "pt", "it", "es", "fr", "de", "pl", "nl"];

$domain_list = ["net", "ru"];

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>retrack24</title>
    <style>
        #results {
            margin-top: 20px;
        }
        .event {
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        .event p {
            margin: 5px 0;
        }
        .event-date {
            font-size: 14px;
            color: #888;
        }
        #loading {
            margin-top: 20px;
            font-size: 18px;
            display: none;
        }
        #error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>reTrack24</h1>
    <br>
    <form id="trackForm">
        <label for="code">track code:</label>
        <input type="text" id="code" name="code" required>

        <label for="service">service:</label>
        <select id="service" name="service">
            <option value="auto">
                auto
            </option>
            <?php foreach ($service_list as $service): ?>
                <option value="<?= htmlspecialchars($service); ?>">
                    <?= htmlspecialchars($service); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="language">language:</label>
        <select id="language" name="language">
            <?php foreach ($language_list as $language): ?>
                <option value="<?= htmlspecialchars($language); ?>">
                    <?= htmlspecialchars($language); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="domain">domain:</label>
        <select id="domain" name="domain">
            <?php foreach ($domain_list as $domain): ?>
                <option value="<?= htmlspecialchars($domain); ?>">
                    <?= htmlspecialchars($domain); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">track</button>
    </form>

    <div id="loading">loading</div>
    <div id="results"></div>
<script>
    document.getElementById('trackForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();

        const resultsDiv = document.getElementById('results');
        const loadingDiv = document.getElementById('loading');
        
        resultsDiv.innerHTML = '';
        loadingDiv.style.display = 'block';

        fetch(`/track.php?${params}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('network error');
                }
                return response.json();
            })
            .then(data => {
                loadingDiv.style.display = 'none';

                if (data.status === 'ok' && data.data && data.data.events) {
                    const events = data.data.events;
                    if (events.length > 0) {
                        const trackCode = data.data.trackCode;
                        resultsDiv.innerHTML += `<h3>status for ${trackCode}</h3>`;

                        events.forEach(event => {
                            let eventHtml = '<div class="event">';
                            if (event.operationDateTime) {
                                eventHtml += `<p class="event-date">${event.operationDateTime}</p>`;
                            }
                            if (event.operationAttributeTranslated) {
                                eventHtml += `<p>${event.operationAttributeTranslated}</p>`;
                            }
                            if (event.operationPlaceNameTranslated) {
                                eventHtml += `<p>${event.operationPlaceNameTranslated}</p>`;
                            }
                            eventHtml += '</div>';
                            resultsDiv.innerHTML += eventHtml;
                        });
                    } else {
                        resultsDiv.innerHTML = '<p id="error">no data</p>';
                    }
                } else {
                    resultsDiv.innerHTML = '<p id="error">no data</p>';
                }
            })
            .catch(error => {
                loadingDiv.style.display = 'none';
                resultsDiv.innerHTML = `<p id="error">fail: ${error.message}</p>`;
            });
    });
</script>
</body>
</html>
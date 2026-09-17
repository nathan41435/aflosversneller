<?php
$show_results = false;
$active_tab = isset($_POST['tab']) ? $_POST['tab'] : 'calculator';

// Aflos-logica
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['calc_submit'])) {
    $schuld = floatval($_POST['schuld']);
    $rente_perc = floatval($_POST['rente']);
    $maandbedrag = floatval($_POST['maandbedrag']);
    $extra_aflossing = !empty($_POST['extra_aflossing']) ? floatval($_POST['extra_aflossing']) : 0;

    $maand_rente_factor = ($rente_perc / 100) / 12;

    function berekenAflossing($startSchuld, $maandRente, $aflossingPerMaand) {
        $restSchuld = $startSchuld;
        $totaleRente = 0;
        $maanden = 0;

        while ($restSchuld > 0 && $maanden < 600) {
            $renteMaand = $restSchuld * $maandRente;
            $totaleRente += $renteMaand;
            $restSchuld = $restSchuld + $renteMaand - $aflossingPerMaand;
            $maanden++;
        }
        return ['maanden' => $maanden, 'rente' => $totaleRente];
    }

    $standaard = berekenAflossing($schuld, $maand_rente_factor, $maandbedrag);
    $metExtra = $extra_aflossing > 0 ? berekenAflossing($schuld, $maand_rente_factor, $maandbedrag + $extra_aflossing) : null;
    $show_results = true;
}

// Quiz-logica
$quiz_score = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quiz_submit'])) {
    $active_tab = 'quiz';
    $score = 0;
    if (isset($_POST['q1']) && $_POST['q1'] === 'b') $score++;
    if (isset($_POST['q2']) && $_POST['q2'] === 'a') $score++;
    if (isset($_POST['q3']) && $_POST['q3'] === 'c') $score++;
    $quiz_score = $score;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Geld Campus & Aflosversneller</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0f4f8; margin: 0; padding: 20px; color: #333; }
        .main-card { max-width: 650px; margin: 0 auto; background: white; border-radius: 12px; padding: 25px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        h1 { text-align: center; color: #1e293b; margin-bottom: 5px; }
        p.subtitle { text-align: center; color: #64748b; margin-top: 0; margin-bottom: 25px; }
        
        /* Navigation Tabs */
        .nav-tabs { display: flex; gap: 8px; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        .tab-btn { flex: 1; padding: 12px 8px; background: #f1f5f9; border: none; border-radius: 6px; font-weight: bold; color: #475569; cursor: pointer; transition: 0.2s; text-align: center; }
        .tab-btn.active { background: #2563eb; color: white; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Form & Inputs */
        label { display: block; font-weight: 600; margin-top: 15px; margin-bottom: 5px; color: #334155; }
        input[type="number"], select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; }
        .btn-submit { width: 100%; background: #2563eb; color: white; padding: 14px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        .btn-submit:hover { background: #1d4ed8; }
        
        /* Info Cards & Modals */
        .info-box { background: #eff6ff; border-left: 4px solid #2563eb; padding: 15px; border-radius: 4px; margin-top: 15px; }
        .tip-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 12px; }
        .tip-card h3 { margin-top: 0; color: #0f172a; }
        
        /* Quiz Styling */
        .quiz-q { margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; }
        .quiz-q p { font-weight: bold; margin-top: 0; }
        .quiz-q label { font-weight: normal; margin-top: 8px; cursor: pointer; }
        
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #e0e7ff; color: #3730a3; margin-bottom: 8px; }
    </style>
</head>
<body>

<div class="main-card">
    <h1>Student Geld Hub 🎓</h1>
    <p class="subtitle">Aflossen, besparen en slim met je geld omgaan</p>

    <!-- Navigatie Knoppen -->
    <div class="nav-tabs">
        <button class="tab-btn <?= $active_tab == 'calculator' ? 'active' : '' ?>" onclick="switchTab('calculator')">🧮 Aflosversneller</button>
        <button class="tab-btn <?= $active_tab == 'quiz' ? 'active' : '' ?>" onclick="switchTab('quiz')">🧠 Geld Quiz</button>
        <button class="tab-btn <?= $active_tab == 'tips' ? 'active' : '' ?>" onclick="switchTab('tips')">💡 Bespaartips</button>
    </div>

    <!-- TAB 1: CALCULATOR -->
    <div id="calculator" class="tab-content <?= $active_tab == 'calculator' ? 'active' : '' ?>">
        <?php if (!$show_results): ?>
            <form method="post">
                <input type="hidden" name="tab" value="calculator">
                <label>Huidige studieschuld (€):</label>
                <input type="number" name="schuld" step="0.01" required placeholder="bijv. 15000">

                <label>Rentepercentage (%):</label>
                <input type="number" name="rente" step="0.01" required placeholder="bijv. 2.56">

                <label>Verplichte maandelijkse aflossing (€):</label>
                <input type="number" name="maandbedrag" step="0.01" required placeholder="bijv. 100">

                <label>Extra maandelijkse aflossing (€) [Optioneel]:</label>
                <input type="number" name="extra_aflossing" step="0.01" placeholder="bijv. 50">

                <button type="submit" name="calc_submit" class="btn-submit">Bereken mijn Voordeel</button>
            </form>
        <?php else: ?>
            <h2>Aflosresultaten 📊</h2>
            <p>Schuld: €<?= number_format($schuld, 2, ',', '.') ?> | Rente: <?= number_format($rente_perc, 2, ',', '.') ?>%</p>
            <hr>
            <p><strong>Standaard looptijd:</strong> <?= round($standaard['maanden'] / 12, 1) ?> jaar (<?= $standaard['maanden'] ?> maanden)</p>
            <p><strong>Totale rente standaard:</strong> €<?= number_format($standaard['rente'], 2, ',', '.') ?></p>

            <?php if ($metExtra !== null): ?>
                <?php 
                    $bespaardeMaanden = $standaard['maanden'] - $metExtra['maanden'];
                    $bespaardeRente = $standaard['rente'] - $metExtra['rente'];
                ?>
                <div class="info-box">
                    <h3 style="margin-top:0; color:#2563eb;">Met extra aflossing (€<?= number_format($extra_aflossing, 2, ',', '.') ?>/mnd):</h3>
                    <p>Nieuwe looptijd: <?= round($metExtra['maanden'] / 12, 1) ?> jaar (<?= $metExtra['maanden'] ?> maanden)</p>
                    <p><strong>Je bent <?= $bespaardeMaanden ?> maanden sneller schuldenvrij!</strong></p>
                    <p>Totale rentebesparing: <strong>€<?= number_format($bespaardeRente, 2, ',', '.') ?></strong></p>
                </div>
            <?php endif; ?>
            <button onclick="window.location.href='index.php'" class="btn-submit" style="background:#64748b;">Nieuwe berekening</button>
        <?php endif; ?>
    </div>

    <!-- TAB 2: QUIZ -->
    <div id="quiz" class="tab-content <?= $active_tab == 'quiz' ? 'active' : '' ?>">
        <h2>Test je Financiële Kennis 🧠</h2>
        
        <?php if ($quiz_score !== null): ?>
            <div class="info-box" style="text-align: center;">
                <h3>Jouw Score: <?= $quiz_score ?> / 3</h3>
                <?php if ($quiz_score == 3): ?>
                    <p>🎉 Uitstekend! Jij bent een echte financiële expert.</p>
                <?php else: ?>
                    <p>Goed geprobeerd! Bekijk de bespaartips om je kennis uit te breiden.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="tab" value="quiz">
            
            <div class="quiz-q">
                <p>1. Wat gebeurt er met je rente als je extra aflost op je studieschuld?</p>
                <label><input type="radio" name="q1" value="a"> A) De rente wordt hoger</label><br>
                <label><input type="radio" name="q1" value="b" required> B) Je betaalt in totaal minder rente</label><br>
                <label><input type="radio" name="q1" value="c"> C) Maakt niks uit voor het totaalbedrag</label>
            </div>

            <div class="quiz-q">
                <p>2. Welke toeslag kun je als student aanvragen voor je zorgverzekering?</p>
                <label><input type="radio" name="q2" value="a" required> A) Zorgtoeslag</label><br>
                <label><input type="radio" name="q2" value="b"> B) Huurtoeslag</label><br>
                <label><input type="radio" name="q2" value="c"> C) Studiebeurs extra</label>
            </div>

            <div class="quiz-q">
                <p>3. Wat is de 'anomiteit-regel' van de OV-studentenkaart op feestdagen?</p>
                <label><input type="radio" name="q3" value="a"> A) Je reist altijd gratis</label><br>
                <label><input type="radio" name="q3" value="b"> B) Je kaart werkt helemaal niet</label><br>
                <label><input type="radio" name="q3" value="c" required> C) Bepaalde abonnementen (zoals week) reizen met korting/tarief</label>
            </div>

            <button type="submit" name="quiz_submit" class="btn-submit">Controleer Antwoorden</button>
        </form>
    </div>

    <!-- TAB 3: BESPAARTIPS -->
    <div id="tips" class="tab-content <?= $active_tab == 'tips' ? 'active' : '' ?>">
        <h2>Goud Waard: Studententips 💡</h2>
        
        <div class="tip-card">
            <span class="badge">Abonnementen</span>
            <h3>Opzeggen & Delen</h3>
            <p>Zeg ongebruikte streamingdiensten op of deel een gezinsabonnement met huisgenoten om maandelijkse kosten te halveren.</p>
        </div>

        <div class="tip-card">
            <span class="badge">Boodschappen</span>
            <h3>Boodschappen Hacks</h3>
            <p>Koop B-merken, maak een weekmenu en maak gebruik van apps zoals <i>Too Good To Go</i> om goedkoop maaltijden op te halen.</p>
        </div>

        <div class="tip-card">
            <span class="badge">Belastingen</span>
            <h3>Toeslagen Checken</h3>
            <p>Controleer via de Belastingdienst of je recht hebt op **zorgtoeslag** en/of **huurtoeslag**. Veel studenten laten hier geld liggen!</p>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById(tabName).classList.add('active');
        event.currentTarget.classList.add('active');
    }
</script>

</body>
</html>

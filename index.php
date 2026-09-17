<?php
$show_results = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schuld = floatval($_POST['schuld']);
    $rente_perc = floatval($_POST['rente']);
    $maandbedrag = floatval($_POST['maandbedrag']);
    $extra_aflossing = isset($_POST['extra_aflossing']) && $_POST['extra_aflossing'] !== '' ? floatval($_POST['extra_aflossing']) : 0;

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
    $metExtra = null;

    if ($extra_aflossing > 0) {
        $metExtra = berekenAflossing($schuld, $maand_rente_factor, $maandbedrag + $extra_aflossing);
    }

    $show_results = true;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studieschuld & Aflosversneller</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #555;
        }
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .highlight {
            color: #28a745;
            font-weight: bold;
        }
        hr {
            border: 0;
            height: 1px;
            background: #eee;
            margin: 15px 0;
        }
        a.btn-back {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <h1>Studieschuld & Aflosversneller</h1>

    <div class="container">
        <?php if (!$show_results): ?>
            <form action="index.php" method="post">
                <label for="schuld">Huidige studieschuld (€):</label>
                <input type="number" id="schuld" name="schuld" step="0.01" required placeholder="bijv. 15000">

                <label for="rente">Rentepercentage (%):</label>
                <input type="number" id="rente" name="rente" step="0.01" required placeholder="bijv. 2.56">

                <label for="maandbedrag">Verplichte maandelijkse aflossing (€):</label>
                <input type="number" id="maandbedrag" name="maandbedrag" step="0.01" required placeholder="bijv. 100">

                <label for="extra_aflossing">Extra maandelijkse aflossing (€) [Optioneel]:</label>
                <input type="number" id="extra_aflossing" name="extra_aflossing" step="0.01" placeholder="bijv. 50">

                <input type="submit" value="Bereken Besparing">
            </form>
        <?php else: ?>
            <h2>Aflosresultaten</h2>
            <p>Startschuld: €<?= number_format($schuld, 2, ',', '.') ?></p>
            <p>Rente: <?= number_format($rente_perc, 2, ',', '.') ?>%</p>
            <hr>
            <p><strong>Standaard looptijd:</strong> <?= round($standaard['maanden'] / 12, 1) ?> jaar (<?= $standaard['maanden'] ?> maanden)</p>
            <p><strong>Totale rente standaard:</strong> €<?= number_format($standaard['rente'], 2, ',', '.') ?></p>

            <?php if ($metExtra !== null): ?>
                <?php 
                    $bespaardeMaanden = $standaard['maanden'] - $metExtra['maanden'];
                    $bespaardeRente = $standaard['rente'] - $metExtra['rente'];
                ?>
                <hr>
                <p class="highlight">Met extra aflossing (€<?= number_format($extra_aflossing, 2, ',', '.') ?>/mnd):</p>
                <p>Nieuwe looptijd: <?= round($metExtra['maanden'] / 12, 1) ?> jaar (<?= $metExtra['maanden'] ?> maanden)</p>
                <p><strong>Je bent <?= $bespaardeMaanden ?> maanden sneller schuldenvrij!</strong></p>
                <p class="highlight">Totale rentebesparing: €<?= number_format($bespaardeRente, 2, ',', '.') ?></p>
            <?php endif; ?>

            <a href="index.php" class="btn-back">← Nieuwe berekening maken</a>
        <?php endif; ?>
    </div>

</body>
</html>

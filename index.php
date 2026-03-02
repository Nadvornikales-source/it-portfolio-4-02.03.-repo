<?php
// === Nastavení ===
$jsonFile = __DIR__ . "/profile.json";

$message = "";
$messageType = ""; // "success" | "error"

// === Načti data z JSON ===
$data = [
  "name" => "Neznámý",
  "interests" => []
];

if (file_exists($jsonFile)) {
  $json = file_get_contents($jsonFile);
  $decoded = json_decode($json, true);

  if (is_array($decoded)) {
    $data["name"] = $decoded["name"] ?? $data["name"];
    $data["interests"] = $decoded["interests"] ?? [];
    if (!is_array($data["interests"])) $data["interests"] = [];
  }
}

// === Zpracuj POST ===
if (isset($_POST["new_interest"])) {
  $newInterest = trim($_POST["new_interest"]);

  // 1) prázdné pole
  if ($newInterest === "") {
    $message = "Pole nesmí být prázdné.";
    $messageType = "error";
  } else {
    // 2) kontrola duplicity bez ohledu na velikost písmen
    $newLower = mb_strtolower($newInterest);
    $existingLower = array_map(fn($x) => mb_strtolower(trim((string)$x)), $data["interests"]);

    if (in_array($newLower, $existingLower, true)) {
      $message = "Tento zájem už existuje.";
      $messageType = "error";
    } else {
      // 3) přidej a ulož
      $data["interests"][] = $newInterest;

      $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      $ok = file_put_contents($jsonFile, $encoded);

      if ($ok === false) {
        $message = "Nepodařilo se uložit do profile.json (zkontroluj práva).";
        $messageType = "error";
      } else {
        $message = "Zájem byl úspěšně přidán.";
        $messageType = "success";
      }
    }
  }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>IT Profil 4.0</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="card">
    <h1>IT Profil 4.0</h1>

    <p class="muted">Jméno: <strong><?= htmlspecialchars($data["name"]) ?></strong></p>

    <h2>Zájmy</h2>
    <?php if (count($data["interests"]) === 0): ?>
      <p class="muted">Zatím žádné zájmy.</p>
    <?php else: ?>
      <ul class="list">
        <?php foreach ($data["interests"] as $interest): ?>
          <li><?= htmlspecialchars($interest) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <hr />

    <h2>Přidat nový zájem</h2>

    <?php if (!empty($message)): ?>
      <p class="msg <?= htmlspecialchars($messageType) ?>">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>

    <form method="POST" class="form">
      <input type="text" name="new_interest" placeholder="např. Web" required />
      <button type="submit">Přidat zájem</button>
    </form>
  </div>
</body>
</html>
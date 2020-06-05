<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
  <div class="jumbotron" style="margin-top:100px">
<?php
session_start();

// define default values
$lowestNumber = 0;
$highestNumber = 100;
$form1 = '';
$form2 = '';
$form3 = '';
$msg = '';


//Hilfsfunktion zum vollständigen Löschen aller Sessions
function deleteSessions()
{
    // Löschen aller Session-Variablen.
    $_SESSION = array();

    // Falls die Session gelöscht werden soll, löschen Sie auch das
    // Session-Cookie.
    // Achtung: Damit wird die Session gelöscht, nicht nur die Session-Daten!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    // Zum Schluß, löschen der Session.
    session_destroy();
}


if (!isset($_SESSION['zahlauswahl'])) {
    //create random integer
    $_SESSION['zahlauswahl'] =  random_int($lowestNumber, $highestNumber);
}

//start time measurement
if (isset($_POST['submit1'])) {
    $_SESSION['startzeit'] = new DateTime("now");
    $form1 = '';
} else {
    $form1 = <<<EOT
  <h1>Errate die Zahl</h1>
  <form method="POST">
  <div class="form-group">
    <label>Bitte klicken sie den Button, um das Spiel starten</label>
    <div><input class="btn btn-primary" type="submit" value="Spiel starten" name="submit1"></div>
  </div>
  <p>Sie müssen eine Zahl zwischen $lowestNumber und $highestNumber schätzen, welche durch einen Zufallsgenerator ausgewählt wird. Sie bekommen dann einen Hinweis, ob die gesuchte Zahl höher oder niedriger ist.<br />
  Sie müssen dann so lange weiter raten, bis sie die richtige Zahl erraten haben.</p>
  </form>
  EOT;
}


if (isset($_SESSION['zahlauswahl'])) {
    if (isset($_SESSION['startzeit'])) {
        $form1 = '';
        //Schätzforumular
        $form2 = <<<EOT
  <form id="estimateForm" method="POST">
  <div class="form-group">
    <input id="estimate" class="form-control" type="number" name="estimate" required autofocus step="1" min="'.$lowestNumber.'" max="'.$highestNumber.'">
  </div>
  <div class="form-group">
    <input class="btn btn-primary" type="submit" value="Schätzung abgeben" name="submit2">
  </div>
  </form>
  EOT;

        $form3 = <<<EOT
  <form method="POST">
  <div class="form-group text-right">
    <input class="btn btn-warning" type="submit" value="Neues Spiel starten" name="submit3">
  </div>
  </form>
  EOT;
    }
}

// Alle Sessions löschen wenn neues Spiel starten Button gedrückt wird
if ($_POST['submit3']) {
    deleteSessions();
    header('Location: '.$_SERVER['REQUEST_URI']);
}

// Schätzformular absenden und Ausgabe der Meldungen
if (isset($_POST['submit2'])) {
    //Speichere Startzeit in einer Session
    $schaetzzahl = intval($_POST['estimate']);
    switch ($schaetzzahl) {
    case($schaetzzahl === $_SESSION['zahlauswahl']):
        $ende = new DateTime("now");
        $dauer = $_SESSION['startzeit']->diff($ende);
        $diff = $dauer->format('%H:%I:%S');
        if (isset($_SESSION['gewaehlteZahlen'])) {
            $numberofAttempts = count($_SESSION['gewaehlteZahlen']) + 1;
        } else {
            $numberofAttempts;
        }
        $msg = '<div class="alert alert-success" role="alert">'.sprintf('Herzlichen Glückwunsch! Sie haben die gesuchte Zahl ('.$schaetzzahl.') erraten. Sie haben dafür %d Versuche und eine Zeit von %s benötigt.', $numberofAttempts, $diff).'</div>';
        $form2 = $form1 = '';
        deleteSessions();
    break;
    case($schaetzzahl < $_SESSION['zahlauswahl']):
      $msg = '<div class="alert alert-primary" role="alert">Die gesuchte Zahl ist größer als '.$schaetzzahl.'.</div>';
      $_SESSION['gewaehlteZahlen'][] = $schaetzzahl;
    break;
    case($schaetzzahl > $_SESSION['zahlauswahl']):
      $msg = '<div class="alert alert-primary" role="alert">Die gesuchte Zahl ist kleiner als '.$schaetzzahl.'.</div>';
      $_SESSION['gewaehlteZahlen'][] = $schaetzzahl;
    break;
 }
}

// Ausgabe:
echo $form1;
echo $form2;

echo $msg;
if (isset($_SESSION['gewaehlteZahlen'])) {
    echo '<p>Bisher gewählte Zahlen: '.implode(', ', $_SESSION['gewaehlteZahlen']).'</p>';
}

echo $form3;
?>
</div>
</div>
</body>
</html>

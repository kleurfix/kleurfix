<?php
// ===============================
//  CORS (OPTIONS preflight)
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

// Debug logging (optional)
file_put_contents(__DIR__ . '/debug-method.log',
    date('c') . " " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI'] . PHP_EOL,
    FILE_APPEND
);

// Block non-POST direct access
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed");
    header("Allow: POST");
    echo "405 Not Allowed. Use POST.";
    exit;
}

// ===============================
//  Helper functions
// ===============================
function raw_field($name) {
    return $_POST[$name] ?? "";
}
function sanitize_for_header($str) {
    return trim(preg_replace("/[\r\n]+/", " ", $str));
}
function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// ===============================
//  Read all fields
// ===============================
$naam         = sanitize_for_header(raw_field("naam"));
$emailFrom    = sanitize_for_header(raw_field("email"));
$telefoon     = sanitize_for_header(raw_field("telefoon"));
$locatie      = sanitize_for_header(raw_field("locatie"));
$soort        = sanitize_for_header(raw_field("soort"));
$ruimte       = sanitize_for_header(raw_field("ruimte"));
$planning     = sanitize_for_header(raw_field("planning"));
$omschrijving = sanitize_for_header(raw_field("omschrijving"));

// Required fields check
$required = [
    "naam" => $naam,
    "email" => $emailFrom,
    "telefoon" => $telefoon,
    "locatie" => $locatie,
    "soort" => $soort,
    "omschrijving" => $omschrijving,
];

foreach ($required as $fieldName => $value) {
    if (trim($value) === "") {
        echo "Er ging iets mis. Vul alle verplichte velden in aub. (Ontbreekt: $fieldName)";
        exit;
    }
}

// Email validation
if (!filter_var($emailFrom, FILTER_VALIDATE_EMAIL)) {
    echo "Ongeldig e-mailadres opgegeven.";
    exit;
}

// ===============================
//  PHPMailer initialization
// ===============================
require __DIR__ . '/vendor/autoload.php';       // <-- adjust path!
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

    // Use sendmail (same as `mail()` behind the scenes)
    $mail->isSendmail();

    // FROM
    $mail->setFrom('info@kleurfix.nl', 'Kleurfix Offerte');  // change if needed

    // TO (your receiving address)
    $mail->addAddress('info@kleurfix.nl', 'Kleurfix');

    // Reply-to customer
    $mail->addReplyTo($emailFrom, $naam);

    // Subject
    $mail->Subject = "Nieuwe offerte-aanvraag via de website";

    // ===============================
    //  Build HTML body
    // ===============================
    $htmlBody = "
        <h3>Nieuwe offerte-aanvraag</h3>
        <p><strong>Naam:</strong> " . escape_html($naam) . "</p>
        <p><strong>E-mail:</strong> " . escape_html($emailFrom) . "</p>
        <p><strong>Telefoon:</strong> " . escape_html($telefoon) . "</p>
        <p><strong>Locatie klus:</strong> " . escape_html($locatie) . "</p>
        <p><strong>Soort schilderwerk:</strong> " . escape_html($soort) . "</p>
        <p><strong>Ruimte / onderdelen:</strong> " . escape_html($ruimte) . "</p>
        <p><strong>Planning:</strong> " . escape_html($planning) . "</p>
        <p><strong>Omschrijving:</strong><br>" . nl2br(escape_html($omschrijving)) . "</p>
    ";

    // Text fallback
    $plainBody =
        "Nieuwe offerte-aanvraag:\n\n" .
        "Naam: $naam\n" .
        "E-mail: $emailFrom\n" .
        "Telefoon: $telefoon\n\n" .
        "Locatie: $locatie\n" .
        "Soort: $soort\n" .
        "Ruimte: $ruimte\n" .
        "Planning: $planning\n\n" .
        "Omschrijving:\n$omschrijving\n";

    $mail->isHTML(true);
    $mail->Body = $htmlBody;
    $mail->AltBody = $plainBody;

    // Send it
    $mail->send();

    echo "Bedankt, " . escape_html($naam) . ". We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact op.";

} catch (Exception $e) {
    echo "Verzenden is niet gelukt. Fout: " . escape_html($mail->ErrorInfo);
}
?>

<?php
// optional: allow OPTIONS for CORS / AJAX preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

// debug log (remove later)
file_put_contents(__DIR__ . '/debug-method.log', date('c') . " " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI'] . PHP_EOL, FILE_APPEND);

// Only allow POST (block direct access in browser)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed");
    header("Allow: POST");
    echo "405 Not Allowed. Use POST.";
    exit;
}

// ... continue processing the POST


// Helper to safely read a field and remove CR/LF (prevent header injection)
function raw_field($name) {
    return $_POST[$name] ?? "";
}
function sanitize_for_header($str) {
    // remove CR and LF characters which could be used for header injection
    return trim(preg_replace("/[\r\n]+/", " ", $str));
}
function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

$naam         = sanitize_for_header(raw_field("naam"));
$emailFrom    = sanitize_for_header(raw_field("email"));
$telefoon     = sanitize_for_header(raw_field("telefoon"));
$locatie      = sanitize_for_header(raw_field("locatie"));
$soort        = sanitize_for_header(raw_field("soort"));
$ruimte       = sanitize_for_header(raw_field("ruimte"));
$planning     = sanitize_for_header(raw_field("planning"));
$omschrijving = sanitize_for_header(raw_field("omschrijving"));

// Basic required check
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

// Validate email format
if (!filter_var($emailFrom, FILTER_VALIDATE_EMAIL)) {
    echo "Ongeldig e-mailadres opgegeven.";
    exit;
}

// Receiving address - change to your real address
$mailTo = "-f info@kleurfix.nl";
$subject = "Nieuwe offerte-aanvraag via de website";

// Build body (use the original raw values for content if you want HTML later)
$body = "Naam: " . $naam . "\n";
$body .= "E-mail: " . $emailFrom . "\n";
$body .= "Telefoon: " . $telefoon . "\n\n";
$body .= "Locatie klus: " . $locatie . "\n";
$body .= "Soort schilderwerk: " . $soort . "\n";
$body .= "Ruimte / onderdelen: " . $ruimte . "\n";
$body .= "Planning: " . $planning . "\n\n";
$body .= "Omschrijving:\n" . $omschrijving . "\n";

// wrap long lines according to mail() recommendations
$body = wordwrap($body, 70);

// Build headers safely
$headers  = "From: offerte-form <-f info@kleurfix.nl>\r\n";
$headers .= "Reply-To: " . $emailFrom . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Try to send mail
$sent = mail($mailTo, $subject, $body, $headers, '-f info@kleurfix.nl');

if ($sent) {
    echo "Bedankt, " . escape_html($naam) . ". We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact op.";
} else {
    echo "Verzenden is niet gelukt. U kunt ons ook mailen op " . escape_html($mailTo) . ".";
}
?>

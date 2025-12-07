<?php
// ------------------------------------------------------------
// send-offerte.php — Production version (no debug)
// ------------------------------------------------------------

// Your email address (recipient)
$mailTo = "info@kleurfix.nl";

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    http_response_code(405);
    echo "405 Not Allowed. Use POST.";
    exit;
}

// Helpers
function sanitize_header($s) {
    return trim(preg_replace("/[\r\n]+/", " ", $s));
}

// Read fields
$naam         = sanitize_header($_POST['naam'] ?? '');
$emailFrom    = sanitize_header($_POST['email'] ?? '');
$telefoon     = sanitize_header($_POST['telefoon'] ?? '');
$locatie      = sanitize_header($_POST['locatie'] ?? '');
$soort        = sanitize_header($_POST['soort'] ?? '');
$ruimte       = sanitize_header($_POST['ruimte'] ?? '');
$planning     = sanitize_header($_POST['planning'] ?? '');
$omschrijving = trim($_POST['omschrijving'] ?? '');

// Basic validation
$errors = [];
if ($naam === '') $errors[] = 'naam';
if ($emailFrom === '') $errors[] = 'email';
if ($telefoon === '') $errors[] = 'telefoon';
if ($locatie === '') $errors[] = 'locatie';
if ($soort === '') $errors[] = 'soort';
if ($omschrijving === '') $errors[] = 'omschrijving';

if (!empty($errors)) {
    http_response_code(400);
    echo "Ontbrekende velden: " . implode(', ', $errors);
    exit;
}

// Validate email format
if (!filter_var($emailFrom, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Ongeldig e-mailadres.";
    exit;
}

// Build email content
$subject = "Nieuwe offerte-aanvraag: $naam";

$body  = "Naam: $naam\n";
$body .= "E-mail: $emailFrom\n";
$body .= "Telefoon: $telefoon\n\n";
$body .= "Locatie klus: $locatie\n";
$body .= "Soort schilderwerk: $soort\n";
$body .= "Ruimte / onderdelen: $ruimte\n";
$body .= "Planning: $planning\n\n";
$body .= "Omschrijving:\n$omschrijving\n";

$body = wordwrap($body, 70);

// Email headers
$headers  = "From: KleurFix Offerte <info@kleurfix.nl>\r\n";
$headers .= "Reply-To: $emailFrom\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
$sent = mail($mailTo, $subject, $body, $headers);

// Response
if ($sent) {
    echo "Bedankt, $naam. We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact op.";
} else {
    http_response_code(500);
    echo "Er is een fout opgetreden bij het verzenden van uw aanvraag. Probeer het later opnieuw.";
}
?>

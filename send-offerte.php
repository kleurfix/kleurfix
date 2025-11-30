<?php
// Simple debug + safe send-offerte script
// IMPORTANT: remove/limit debugging in production

// --- Configuration ---
$mailTo = "info@kleurfix.nl"; // change if needed
$debug_file = '/tmp/send_offerte_debug.txt'; // ensure PHP can write here (or change path)

// --- Helpers ---
function dbg($msg) {
    global $debug_file;
    // prefix timestamp, avoid huge writes
    file_put_contents($debug_file, "[".date('Y-m-d H:i:s')."] " . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}
function sanitize_header($s) {
    return trim(preg_replace("/[\r\n]+/", " ", $s));
}
function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Log request method and raw input for debugging
dbg("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . " METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') );
if (!empty($_POST)) {
    dbg("POST: " . json_encode($_POST));
} else {
    // capture raw body (for non-form posts)
    $raw = file_get_contents('php://input');
    if ($raw !== '') dbg("RAW: " . substr($raw,0,2000));
}

// If GET, show friendly debug page instead of 405 (so you can open URL in browser)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(200);
    ?>
    <!doctype html>
    <html>
    <head><meta charset="utf-8"><title>send-offerte.php</title></head>
    <body>
      <h2>send-offerte.php</h2>
      <p>This script accepts <strong>POST</strong> requests from the offerte form.</p>
      <p>If you see a 405 here, your form is not using <code>method="POST"</code> or the action URL is wrong.</p>
      <p>Quick test with curl (run from your computer or server):</p>
      <pre>
curl -v -X POST -F "naam=Test" -F "email=test@example.com" -F "telefoon=0612345678" -F "locatie=Assen" -F "soort=binnen" -F "omschrijving=Test" https://kleurfix.nl/send-offerte.php
      </pre>
      <p>Server debug file: <code><?php echo esc($debug_file); ?></code></p>
    </body>
    </html>
    <?php
    exit;
}

// Allow only POST for real processing
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    http_response_code(405);
    echo "405 Not Allowed. Use POST.";
    exit;
}

// --- Read & sanitize fields ---
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
    $msg = "Ontbrekende velden: " . implode(', ', $errors);
    dbg("Validation failed: " . $msg);
    echo $msg;
    exit;
}

// validate email format
if (!filter_var($emailFrom, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    dbg("Invalid email: " . $emailFrom);
    echo "Ongeldig e-mailadres.";
    exit;
}

// Build email body
$body  = "Naam: $naam\n";
$body .= "E-mail: $emailFrom\n";
$body .= "Telefoon: $telefoon\n\n";
$body .= "Locatie klus: $locatie\n";
$body .= "Soort schilderwerk: $soort\n";
$body .= "Ruimte / onderdelen: $ruimte\n";
$body .= "Planning: $planning\n\n";
$body .= "Omschrijving:\n$omschrijving\n";
$body = wordwrap($body, 70);

// Build headers
$headers  = "From: offerte-form <no-reply@kleurfix.nl>\r\n";
$headers .= "Reply-To: " . $emailFrom . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Try to send mail (keep commented while debugging if your server cannot send)
$sent = false;
try {
    // Uncomment the next line to actually call mail() when you're ready
    // $sent = mail($mailTo, "Nieuwe offerte-aanvraag: " . $naam, $body, $headers);
    dbg("Prepared mail to $mailTo. Body length: " . strlen($body));
} catch (Exception $e) {
    dbg("Mail exception: " . $e->getMessage());
}

// For debugging, always write the final prepared email to debug file
dbg("EMAIL SUBJECT: " . "Nieuwe offerte-aanvraag: " . $naam);
dbg("EMAIL HEADERS: " . $headers);
dbg("EMAIL BODY: " . substr($body,0,4000));

// Respond to client
if ($sent) {
    echo "Bedankt, " . esc($naam) . ". We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact op.";
} else {
    // If your mail() is not enabled, give friendly confirmation and mention debug file
    echo "Ontvangen (debug). Als deze site nog geen e-mail kan versturen, controleer het debugbestand op de server: " . esc($debug_file);
}
?>

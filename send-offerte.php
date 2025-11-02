<?php
// Only allow POST (block direct access in browser)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "405 Not Allowed";
    exit;
}

// Helper to safely read a field
function field($name) {
    return htmlspecialchars($_POST[$name] ?? "");
}

$naam         = field("naam");
$email        = field("email");
$telefoon     = field("telefoon");
$locatie      = field("locatie");
$soort        = field("soort");
$ruimte       = field("ruimte");
$planning     = field("planning");
$omschrijving = field("omschrijving");

// Basic validation
if (
    $naam === "" ||
    $email === "" ||
    $telefoon === "" ||
    $locatie === "" ||
    $soort === "" ||
    $omschrijving === ""
) {
    echo "Er ging iets mis. Vul alle verplichte velden in aub.";
    exit;
}

// TODO: change this to your receiving address
$to = "info@kleurfix.nl";

$subject = "Nieuwe offerte-aanvraag via de website";

$body =
"Naam: $naam
E-mail: $email
Telefoon: $telefoon

Locatie klus: $locatie
Soort schilderwerk: $soort
Ruimte / onderdelen: $ruimte
Planning: $planning

Omschrijving:
$omschrijving
";

$headers = "From: offerte-form <no-reply@kleurfix.nl>\r\n";
$headers .= "Reply-To: $email\r\n";

// Try to send mail
$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo "Bedankt, $naam. We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact op.";
} else {
    echo "Verzenden is niet gelukt. U kunt ons ook mailen op $to.";
}
?>

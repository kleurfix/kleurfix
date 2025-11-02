<?php
// Simple spam protection: only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  exit("Method not allowed");
}

// Get values safely
$naam         = htmlspecialchars($_POST["naam"] ?? "");
$email        = htmlspecialchars($_POST["email"] ?? "");
$telefoon     = htmlspecialchars($_POST["telefoon"] ?? "");
$locatie      = htmlspecialchars($_POST["locatie"] ?? "");
$soort        = htmlspecialchars($_POST["soort"] ?? "");
$ruimte       = htmlspecialchars($_POST["ruimte"] ?? "");
$planning     = htmlspecialchars($_POST["planning"] ?? "");
$omschrijving = htmlspecialchars($_POST["omschrijving"] ?? "");

// Basic required validation
if (!$naam || !$email || !$telefoon || !$locatie || !$soort || !$omschrijving) {
  exit("Er ging iets mis. Vul alle verplichte velden in aub.");
}

// Where should it go?
$to = "info@kleurfix.nl"; // <-- change this to your address

$subject = "Nieuwe offerte-aanvraag via de website";

$body = "
Naam: $naam
E-mail: $email
Telefoon: $telefoon

Locatie klus: $locatie
Soort schilderwerk: $soort
Ruimte / onderdelen: $ruimte
Planning: $planning

Omschrijving:
$omschrijving
";

// Extra headers so you can reply directly
$headers = "From: offerte-form <no-reply@kleurfix.nl>\r\n";
$headers .= "Reply-To: $email\r\n";

// Send mail
$sent = mail($to, $subject, $body, $headers);

if ($sent) {
  // Simple thank-you page
  echo "Bedankt, $naam. We hebben uw aanvraag ontvangen en nemen contact met u op.";
} else {
  echo "Sorry, verzenden is niet gelukt. U kunt ons ook mailen op $to.";
}
?>

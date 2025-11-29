<?php

$message_sent = false;

if (isset($_POST['email']) && $_POST['email'] !== '') {

    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

        // Collect fields safely
        $userName        = htmlspecialchars($_POST['naam'] ?? '');
        $userEmail       = htmlspecialchars($_POST['email'] ?? '');
        $userTelefoon    = htmlspecialchars($_POST['telefoon'] ?? '');
        $userLocatie     = htmlspecialchars($_POST['locatie'] ?? '');
        $userSoort       = htmlspecialchars($_POST['soort'] ?? '');
        $userRuimte      = htmlspecialchars($_POST['ruimte'] ?? '');
        $userPlanning    = htmlspecialchars($_POST['planning'] ?? '');
        $userMessage     = htmlspecialchars($_POST['omschrijving'] ?? '');

        // REQUIRED fields check
        if (
            $userName === '' ||
            $userEmail === '' ||
            $userTelefoon === '' ||
            $userLocatie === '' ||
            $userSoort === '' ||
            $userMessage === ''
        ) {
            echo "Vul alle verplichte velden in a.u.b.";
            exit;
        }

        // Receiver
        $to = "info@kleurfix.nl";

        // Email subject
        $subject = "Nieuwe offerte-aanvraag via de website";

        // Build message body
        $body  = "Naam: " . $userName . "\r\n";
        $body .= "Email: " . $userEmail . "\r\n";
        $body .= "Telefoon: " . $userTelefoon . "\r\n\r\n";

        $body .= "Locatie klus: " . $userLocatie . "\r\n";
        $body .= "Soort schilderwerk: " . $userSoort . "\r\n";
        $body .= "Ruimte / onderdelen: " . $userRuimte . "\r\n";
        $body .= "Planning: " . $userPlanning . "\r\n\r\n";

        $body .= "Omschrijving:\r\n" . $userMessage . "\r\n";

        // Headers
        $headers  = "From: Kleurfix Offerte <info@kleurfix.nl>\r\n";
        $headers .= "Reply-To: " . $userEmail . "\r\n";

        // Send email
        // ⚠️ Make sure mail() is enabled on your server
        $sent = mail($to, $subject, $body, $headers);

        if ($sent) {
            echo "Bedankt, $userName. We nemen zo snel mogelijk contact op.";
            $message_sent = true;
        } else {
            echo "Fout: de e-mail kon niet verzonden worden.";
        }
    }
}

?>

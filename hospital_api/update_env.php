<?php
$env = file_get_contents('.env');
$lines = explode("\n", $env);
$newLines = [];
foreach ($lines as $line) {
    if (strpos($line, 'MAIL_') === 0)
        continue;
    $newLines[] = trim($line);
}
$newLines[] = "MAIL_MAILER=smtp";
$newLines[] = "MAIL_HOST=smtp.gmail.com";
$newLines[] = "MAIL_PORT=465";
$newLines[] = "MAIL_USERNAME=agentprovatofficial127@gmail.com";
$newLines[] = "MAIL_PASSWORD=jvpbghnjlgxgviuw";
$newLines[] = "MAIL_ENCRYPTION=ssl";
$newLines[] = "MAIL_FROM_ADDRESS=agentprovatofficial127@gmail.com";
$newLines[] = "MAIL_FROM_NAME='Telehealth BD'";

file_put_contents('.env', implode("\n", $newLines));
echo "ENV updated";

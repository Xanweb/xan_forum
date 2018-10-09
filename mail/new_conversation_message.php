<?php defined('C5_EXECUTE') or die('Access Denied.');

$subject = t('New Message on Topic: %s', $title);

$body = t("
Hallo %s,

%s hat auf ein Thema geantwortet, das du bei Concrete5 Forum abonniert hast. Das Thema trägt den Titel: %s.

Hier ist ein Ausschnitt der Nachricht:
------------------------------------------
%s…
------------------------------------------

Um das Thema zu besuchen, kannst du auf den folgenden Link klicken:
%s

Es könnte bereits auch weitere Antworten zu diesem Thema geben. Du erhältst jedoch keine weitere Benachrichtigung, bevor du das Forum besucht hast.

Vielen Dank,
Concrete5 Forum-Team", $username, $poster, $title, $body, $link);

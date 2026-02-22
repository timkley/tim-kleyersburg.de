<?php

declare(strict_types=1);

$primaryUtterances = [
    'ListQuests' => [
        'Zeig mir meine offenen Quests.',
        'Welche Aufgaben sind heute faellig?',
        'Liste alle erledigten Aufgaben.',
        'Zeig meine taeglichen Quests.',
        'Welche Notiz-Quests habe ich?',
        'Gib mir eine Uebersicht aller Quests.',
    ],
    'SearchQuests' => [
        'Finde die Quest mit Docker im Titel.',
        'Suche nach Aufgaben zu Steuererklaerung.',
        'Welche Quest enthaelt API?',
        'Finde etwas zu Roadmap.',
        'Suche Quests mit dem Wort Refactor.',
        'Welche Aufgabe erwaehnt Migration?',
    ],
    'SearchQuestComments' => [
        'Suche in Quest-Kommentaren nach blocker.',
        'Gibt es Notizen mit Rollback in Quest-Kommentaren?',
        'Finde Kommentare zu Kundenfeedback.',
        'Welche Quest-Notizen erwaehnen deployment?',
        'Suche Kommentare mit dem Wort risiko.',
        'Zeig mir Quest-Kommentare zu testing.',
    ],
    'GetQuest' => [
        'Zeig Details zu Quest 101.',
        'Was steht in Quest 22?',
        'Hole die Infos von Aufgabe 77.',
        'Gib mir alle Details zu Quest 15.',
        'Zeig Unteraufgaben fuer Quest 41.',
        'Lade Quest 300 mit Notizen.',
    ],
    'CreateQuest' => [
        'Erstelle eine neue Quest namens Steuererklaerung.',
        'Lege eine Aufgabe API Refactor an.',
        'Erstelle eine Notiz-Quest mit dem Titel Ideen.',
        'Neue Quest: Server Migration fuer morgen.',
        'Lege eine Aufgabe fuer Freitag an: Sprint Review.',
        'Erstelle Quest Datenbank Cleanup.',
    ],
    'CompleteQuest' => [
        'Markiere Quest 81 als erledigt.',
        'Schliesse Aufgabe 19 ab.',
        'Setze Quest 45 auf abgeschlossen.',
        'Quest 12 ist fertig, bitte abschliessen.',
        'Bitte Aufgabe 205 erledigen.',
        'Complete Quest 999.',
    ],
    'AddNoteToQuest' => [
        'Fuege zu Quest 51 die Notiz Kunde hat zugesagt hinzu.',
        'Kommentar auf Aufgabe 71: Rollback vorbereitet.',
        'Notiz zu Quest 44: Warte auf Feedback.',
        'Ergaenze Quest 38 um den Hinweis API ist live.',
        'Fuge bei Quest 8 hinzu: Daily Sync erledigt.',
        'Hinterlege bei Aufgabe 143 den Kommentar Dokumentation fehlt.',
    ],
    'BrowseNotes' => [
        'Zeig mir die Ordner der Wissensdatenbank.',
        'Welche Verzeichnisse liegen unter /?',
        'Browse bitte den Notes Root.',
        'Liste Dateien und Ordner in /Areas.',
        'Navigiere in der Knowledge Base zu /Resources.',
        'Was ist im Verzeichnis /Projects?',
    ],
    'ReadNote' => [
        'Lies die Datei Areas/Health/sleep.md.',
        'Oeffne Resources/Tech/vim.md.',
        'Zeig den Inhalt von Projects/Launch/plan.md.',
        'Bitte read Areas/Fitness/routine.md.',
        'Lade Archive/2025/retro.md.',
        'Gib mir den Text aus Resources/Health/macros.md.',
    ],
    'WriteNote' => [
        'Erstelle die Datei Areas/Health/walking.md mit Inhalt Heute 10k Schritte.',
        'Aktualisiere Resources/Tech/docker.md mit neuen Tipps.',
        'Schreibe eine Notiz nach Projects/Launch/checklist.md.',
        'Ueberschreibe Areas/Work/meeting-notes.md mit den folgenden Punkten.',
        'Create note in Resources/Cooking/protein.md with full content.',
        'Lege Archive/2026/review.md an und schreibe den kompletten Text hinein.',
    ],
    'SearchNotes' => [
        'Suche in meinen Notizen nach Schlaf.',
        'Finde alle Notes mit Supplements.',
        'Welche Markdown Dateien enthalten OpenClaw?',
        'Search notes for Laravel.',
        'Suche nach dem Begriff meal prep in Notizen.',
        'Finde Notizen mit dem Wort Konzentration.',
    ],
    'LogMeal' => [
        'Logge Mahlzeit: 700 kcal, 50 protein, 20 fett, 60 carbs.',
        'Trage Fruehstueck fuer heute ein: 450 kcal.',
        'Ich habe gegessen: Chicken Bowl 620 kcal 48 protein 18 fett 55 carbs.',
        'Fuege Meal hinzu fuer 2026-02-22 mit 800 kcal.',
        'Erfasse Mittagessen 13:00, 560 kcal, 40 protein, 16 fett, 58 carbs.',
        'Bitte log meal Burger 900 kcal 45 protein 50 fett 70 carbs.',
    ],
    'QueryNutrition' => [
        'Zeig meine Ernaehrung von heute.',
        'Gib mir die Makros fuer 2026-02-20.',
        'Wochenuebersicht fuer die letzten 7 Tage.',
        'Wie ist mein 7-Tage-Durchschnitt bei den Makros?',
        'Nutrition report fuer heute bitte.',
        'Vergleiche meinen Durchschnitt mit den Targets.',
    ],
];

$mustNotCall = [
    'ListQuests' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'LogMeal', 'QueryNutrition'],
    'SearchQuests' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'LogMeal', 'QueryNutrition'],
    'SearchQuestComments' => ['SearchNotes', 'ReadNote', 'WriteNote', 'LogMeal', 'QueryNutrition'],
    'GetQuest' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'LogMeal', 'QueryNutrition'],
    'CreateQuest' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'LogMeal', 'QueryNutrition'],
    'CompleteQuest' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'LogMeal', 'QueryNutrition'],
    'AddNoteToQuest' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'LogMeal', 'QueryNutrition'],
    'BrowseNotes' => ['SearchQuests', 'GetQuest', 'CompleteQuest', 'LogMeal', 'QueryNutrition'],
    'ReadNote' => ['SearchQuests', 'GetQuest', 'CompleteQuest', 'LogMeal', 'QueryNutrition'],
    'WriteNote' => ['SearchQuests', 'GetQuest', 'CompleteQuest', 'LogMeal', 'QueryNutrition'],
    'SearchNotes' => ['SearchQuests', 'GetQuest', 'CompleteQuest', 'LogMeal', 'QueryNutrition'],
    'LogMeal' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'SearchQuests', 'ListQuests'],
    'QueryNutrition' => ['BrowseNotes', 'ReadNote', 'SearchNotes', 'SearchQuests', 'ListQuests'],
];

$cases = [];

foreach ($primaryUtterances as $tool => $utterances) {
    foreach ($utterances as $utterance) {
        $cases[] = [
            'utterance' => $utterance,
            'expected_primary_tool' => $tool,
            'allowed_secondary_tools' => [],
            'must_not_call' => $mustNotCall[$tool] ?? [],
            'requires_disambiguation' => false,
            'requires_name_resolution' => false,
            'target_follow_up_tool' => null,
        ];
    }
}

$nameResolutionCases = [
    [
        'utterance' => 'Schliesse bitte Steuererklaerung ab.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => ['CompleteQuest'],
        'must_not_call' => ['AddNoteToQuest', 'WriteNote'],
        'requires_disambiguation' => false,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'CompleteQuest',
    ],
    [
        'utterance' => 'Markiere API Refactor als erledigt.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => ['CompleteQuest'],
        'must_not_call' => ['AddNoteToQuest', 'WriteNote'],
        'requires_disambiguation' => false,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'CompleteQuest',
    ],
    [
        'utterance' => 'Fuege zu Onboarding Flow eine Notiz hinzu: Kunde hat bestaetigt.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => ['AddNoteToQuest'],
        'must_not_call' => ['CompleteQuest', 'WriteNote'],
        'requires_disambiguation' => false,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'AddNoteToQuest',
    ],
    [
        'utterance' => 'Ergaenze bei Server Migration den Kommentar Rollback vorbereitet.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => ['AddNoteToQuest'],
        'must_not_call' => ['CompleteQuest', 'WriteNote'],
        'requires_disambiguation' => false,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'AddNoteToQuest',
    ],
    [
        'utterance' => 'Zeig Details zu Datenbank Cleanup.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => ['GetQuest'],
        'must_not_call' => ['CompleteQuest', 'AddNoteToQuest', 'WriteNote'],
        'requires_disambiguation' => false,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'GetQuest',
    ],
    [
        'utterance' => 'Was steht in Roadmap Q2?',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => ['GetQuest'],
        'must_not_call' => ['CompleteQuest', 'AddNoteToQuest', 'WriteNote'],
        'requires_disambiguation' => false,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'GetQuest',
    ],
    [
        'utterance' => 'Schliesse die Quest Planung ab, ich habe mehrere davon.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => [],
        'must_not_call' => ['CompleteQuest', 'AddNoteToQuest', 'WriteNote', 'CreateQuest'],
        'requires_disambiguation' => true,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'CompleteQuest',
    ],
    [
        'utterance' => 'Fuege bei Sprint Review eine Notiz hinzu, ich meine die von letzter Woche.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => [],
        'must_not_call' => ['AddNoteToQuest', 'CompleteQuest', 'WriteNote', 'CreateQuest'],
        'requires_disambiguation' => true,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'AddNoteToQuest',
    ],
    [
        'utterance' => 'Zeig mir Testing, aber ich habe davon mehrere.',
        'expected_primary_tool' => 'SearchQuests',
        'allowed_secondary_tools' => [],
        'must_not_call' => ['GetQuest', 'CompleteQuest', 'AddNoteToQuest', 'WriteNote'],
        'requires_disambiguation' => true,
        'requires_name_resolution' => true,
        'target_follow_up_tool' => 'GetQuest',
    ],
];

return array_merge($cases, $nameResolutionCases);

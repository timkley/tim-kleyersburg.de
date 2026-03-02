<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\AddNoteToQuest;
use App\Ai\Tools\BrowseNotes;
use App\Ai\Tools\CompleteQuest;
use App\Ai\Tools\CreateQuest;
use App\Ai\Tools\GetQuest;
use App\Ai\Tools\ListQuests;
use App\Ai\Tools\LogBodyMeasurement;
use App\Ai\Tools\QueryBodyMeasurements;
use App\Ai\Tools\ReadNote;
use App\Ai\Tools\SearchConversationHistory;
use App\Ai\Tools\SearchNotes;
use App\Ai\Tools\SearchQuestComments;
use App\Ai\Tools\SearchQuests;
use App\Ai\Tools\WriteNote;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenRouter)]
#[Model('google/gemini-3-flash-preview')]
#[MaxSteps(30)]
class ChopperAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $date = now()->format('l, j. F Y');
        $time = now()->toTimeString();

        return <<<EOT
        Du bist Chopper, ein hilfreicher Assistent basierend auf dem Droiden C1-10P aus Star Wars Rebels.
        Heute ist $date, es ist $time Uhr.

        Du bist in ein Aufgaben- und Notizverwaltungssystem integriert. Du kannst Aufgaben (Quests) suchen, auflisten, erstellen und abschließen. Du kannst auch Notizen zu Aufgaben hinzufügen und durchsuchen.

        Du hast Zugriff auf eine Wissensdatenbank (Knowledge Base) mit Markdown-Notizen, organisiert nach dem PARA-Prinzip (Projects, Areas, Resources, Archive). Du kannst Notizen durchsuchen, lesen, erstellen und bearbeiten.

        Du kannst auch Ernährungsdaten tracken und abfragen. Du kannst Mahlzeiten loggen und Ernährungsübersichten abrufen.

        Regeln:
        - Antworte immer auf Deutsch, es sei denn, der Benutzer schreibt auf Englisch.
        - Sei humorvoll und motivierend, aber bleibe hilfreich und präzise.
        - Verwende deine Tools aktiv, aber gezielt, um dem Benutzer bestmoeglich zu helfen.
        - Nutzer nennen Quests fast immer ueber Namen, nicht ueber IDs.
        - Bei Quest-Namen zuerst SearchQuests oder ListQuests nutzen.
        - ID-basierte Quest-Tools (GetQuest, CompleteQuest, AddNoteToQuest) erst nach Aufloesung verwenden.
        - Wenn mehrere Quests passen, frage nach, bevor du eine schreibende Aktion ausfuehrst.
        - Nutze maximal ein Tool pro Anfrage, ausser die Quest-Aufloesung braucht den zweiten Schritt.
        - Bei Aufgabenfragen mit Listenwunsch zuerst ListQuests, bei Stichworten oder Namen zuerst SearchQuests.
        - Bei Notizen der Knowledge Base zuerst SearchNotes oder BrowseNotes und nur bei konkretem Pfad ReadNote.
        - Bei Koerpermesswerten: neue Messung = LogBodyMeasurement, Auswertung/Verlauf = QueryBodyMeasurements.
        - Formatiere deine Antworten mit Markdown.
        - Nutze SearchConversationHistory proaktiv, wann immer vergangener Kontext deine Antwort bereichern koennte. Wenn ein Thema moeglicherweise schon besprochen wurde, wenn der Benutzer auf etwas Vergangenes verweist, oder wenn Kontinuitaet mit frueheren Gespraechen helfen wuerde — suche danach. Warte nicht auf ein explizites "erinnerst du dich" — wenn es auch nur eine Chance gibt, dass vergangener Kontext relevant ist, nutze das Tool.
        - Halte deine Antworten kurz und fokussiert.
        EOT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new SearchQuests,
            new SearchQuestComments,
            new ListQuests,
            new GetQuest,
            new CreateQuest,
            new CompleteQuest,
            new AddNoteToQuest,
            new BrowseNotes,
            new ReadNote,
            new WriteNote,
            new SearchNotes,
            new LogBodyMeasurement,
            new QueryBodyMeasurements,
            new SearchConversationHistory,
        ];
    }
}

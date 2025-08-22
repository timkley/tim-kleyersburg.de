@use(Modules\Holocron\Quest\Models\Quest)
@use(Modules\Holocron\Quest\Enums\QuestStatus)

<div>
    <div class="space-y-4">
        <livewire:holocron.quest.components.accepted-quests/>

        <livewire:holocron.quest.components.next-quests/>

        <livewire:holocron.quest.components.main-quests/>
    </div>
</div>

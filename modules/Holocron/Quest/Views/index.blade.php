@use(Modules\Holocron\Quest\Models\Quest)
@use(Modules\Holocron\Quest\Enums\QuestStatus)

<div class="space-y-4">
    <div class="space-y-4">
        <livewire:holocron.quest.components.todays-quests/>

        <livewire:holocron.quest.components.next-quests/>

        <livewire:holocron.quest.components.main-quests/>
    </div>
</div>

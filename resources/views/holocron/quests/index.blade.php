@use(App\Models\Holocron\Quest\Quest)
@use(App\Enums\Holocron\QuestStatus)

<div>
    <div class="space-y-4">
        <livewire:holocron.quests.components.accepted-quests />

        <livewire:holocron.quests.components.next-quests />

        <livewire:holocron.quests.components.main-quests />
    </div>
</div>

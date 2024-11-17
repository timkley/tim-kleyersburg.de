<x-slot:title>Einmaleins</x-slot:title>
<x-slot:metaDescription>Das Einmaleins ist die Grundlage für das Rechnen. Hier kannst du ganz easy das Einmaleins
    lernen.
</x-slot:metaDescription>

<x-slot:header></x-slot:header>
<x-slot:footer>
    <hr class="mx-auto my-12 max-w-sm border-blue-100">
    <div class="mx-auto flex w-fit items-center gap-1.5">
        <span class="text-sm">Made by</span>
        <x-logo/>
    </div>
</x-slot:footer>

@php
    $tileClass = 'flex h-16 w-16 items-center justify-center rounded bg-sky-100 text-3xl font-bold dark:bg-slate-700'
@endphp
<div class="mt-6 text-center sm:mt-12" x-data="multi">
    <template x-if="status === 'finished'">
        <div>
            <h1 class="mt-12 mb-12 text-4xl font-bold tracking-wide dark:text-white">
                Das hast du ganz toll gemacht! 🥳 💯
            </h1>

            <p class="mb-1 text-lg">
                Du hattest <span class="font-bold" x-text="errors"></span> Fehler.
            </p>
            <p class="text-lg">
                Du hast <span class="font-bold" x-text="duration"></span> gebraucht.
            </p>
        </div>
    </template>
    <template x-if="status === 'playing'">
        <div>
            <h1 class="mb-12 text-4xl font-bold tracking-wide dark:text-white">🤖 Einmaleins 💯</h1>
            <div class="mb-3 flex items-center justify-center"
                 :class="{'flex-row-reverse': Math.random() > 0.5}">
                <div
                    class="{{ $tileClass }} relative overflow-hidden !bg-cyan-400/30"
                    x-text="term[0]">
                </div>
                <div class="w-10 text-center text-2xl font-bold">×</div>
                <div
                    class="{{ $tileClass }} relative overflow-hidden !bg-pink-400/30"
                    x-text="term[1]">
                </div>
            </div>
            <div class="mb-2 text-gray-500">
                <span>noch</span>
                <span x-text="decks.initial.length + decks.lost.length"></span>
                <span>Aufgaben</span>
            </div>

            <div>
                <input
                    class="w-24 rounded border-2 px-3 py-2 text-center text-3xl font-bold focus:border-gray-400 focus:outline-none dark:border-gray-600 dark:bg-gray-700"
                    type="text"
                    x-model="guess"
                    autofocus
                    @keyup.enter="solve">
            </div>
            <div class="mx-auto mt-4 grid w-fit grid-cols-3 grid-rows-4 justify-items-center gap-3 sm:hidden">
                @for($i = 1; $i <= 9; $i++)
                    <button
                        class="{{ $tileClass }} focus:bg-gray-200"
                        @click="handleKey({{ $i }})"
                    >
                        {{ $i }}
                    </button>
                @endfor
                <button
                    class="{{ $tileClass }}"
                    @click="handleKey('backspace')"
                >
                    ⌫
                </button>
                <button
                    class="{{ $tileClass }}"
                    @click="handleKey(0)"
                >
                    0
                </button>
                <button
                    class="{{ $tileClass }}"
                    @click="handleKey('enter')"
                >
                    ⏎
                </button>
            </div>
            <img x-show="no"
                 x-transition
                 class="absolute bottom-0 left-0 mb-4 ml-4 h-80 w-80 rounded-full bg-red-300 object-cover"
                 src="{{ asset('img/einmaleins/ohno.png') }}">
            <img x-show="yes"
                 x-transition
                 class="absolute bottom-0 left-0 mb-4 ml-4 h-80 w-80 rounded-full bg-green-300 object-cover"
                 src="{{ asset('img/einmaleins/ohyes.png') }}">
        </div>
    </template>
</div>

<x-slot:scripts>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('multi', () => ({
                guess: '',
                status: 'playing',
                yes: false,
                no: false,
                deck: 'initial',
                decks: {
                    initial: [],
                    won: [],
                    lost: [],
                },
                errors: 0,
                gameStart: undefined,
                gameEnd: undefined,
                index: undefined,
                init () {
                    this.generateDataSet()
                    this.randomDeckIndex()

                    this.gameStart = new Date()
                },
                solve () {
                    if (parseInt(this.guess) === this.solution) {
                        this.decks.won.push(this.currentDeck[this.index])
                        this.yes = true

                        setTimeout(() => {
                            this.yes = false
                        }, 1200)
                    } else {
                        this.errors++
                        this.decks.lost.push(this.currentDeck[this.index])
                        this.no = true

                        setTimeout(() => {
                            this.no = false
                        }, 1200)
                    }

                    this.currentDeck.splice(this.index, 1)
                    this.guess = ''

                    if (this.decks.initial.length === 0) {
                        if (this.decks.lost.length === 0) {
                            this.status = 'finished'
                            this.gameEnd = new Date()
                        } else {
                            this.deck = 'lost'
                        }
                    }

                    this.randomDeckIndex()
                },
                handleKey (key) {
                    switch (key) {
                        case 'backspace':
                            this.guess = this.guess.slice(0, -1)
                            break
                        case 'enter':
                            this.solve()
                            break
                        default:
                            this.guess += key
                    }
                },
                randomDeckIndex () {
                    this.index = Math.floor(Math.random() * this.currentDeck.length)
                },
                generateDataSet () {
                    let i = 2
                    let j = 2
                    while (i <= 9) {
                        while (j <= 9) {
                            this.decks.initial.push(`${i}x${j}`)
                            j++
                        }
                        i++
                        j = i
                    }
                },
                get term () {
                    return this.currentDeck[this.index]?.split('x') ?? [null, null]
                },
                get solution () {
                    return this.term[0] * this.term[1]
                },
                get currentDeck () {
                    return this.decks[this.deck]
                },
                get duration () {
                    if (!this.gameStart || !this.gameEnd) {
                        return
                    }

                    let time = (this.gameEnd - this.gameStart) / 1000

                    let minutes = Math.floor(time / 60)
                    let seconds = Math.floor(time % 60)

                    return `${minutes} Minuten und ${seconds} Sekunden`
                }
            }))
        })
    </script>
</x-slot:scripts>

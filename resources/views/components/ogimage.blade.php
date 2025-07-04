<x-layouts.app>
    <x-slot:header></x-slot:header>
    <x-slot:footer></x-slot:footer>
    <div class="h-lvh flex-1 rounded-sm bg-sky-50 px-6 py-4 text-slate-900 selection:bg-blue-200 sm:rounded-md md:rounded-lg dark:bg-slate-800 dark:text-slate-300">
        <div class="relative z-10 pt-10 pl-16 max-w-5xl prose">
            <h1 class="text-7xl mb-6">
                {{ $fm->title }}
            </h1>
            <div class="text-3xl flex items-center space-x-3">
                <div class="flex items-center">
                    Tim Kleyersburg
                    <div class="-mt-1 mr-1 w-12 h-12">
                        <svg xmlns="http://www.w3.org/2000/svg" data-source="https://thenounproject.com/icon/castle-4322875/" viewBox="0 0 20 20">
                            <path d="M15.33 6.797v9.921a.5.5 0 0 1-1 0v-9.82h-.93v.8a.901.901 0 0 1-.9.9h-1.13a.901.901 0 0 1-.9-.9v-.8h-.93v.8a.901.901 0 0 1-.9.9H7.51a.901.901 0 0 1-.9-.9v-.8h-.94v9.82a.5.5 0 0 1-1 0v-9.92a.901.901 0 0 1 .9-.9h1.14a.901.901 0 0 1 .9.9v.8h.93v-.8a.901.901 0 0 1 .9-.9h.06V3.281a.5.5 0 0 1 .641-.48l1.43.42a.5.5 0 0 1 .055.94l-1.126.48v1.255h.07a.901.901 0 0 1 .9.9v.801h.93v-.8a.901.901 0 0 1 .9-.9h1.13a.901.901 0 0 1 .9.9Zm-3.493 5.678v1.032a1.001 1.001 0 0 1-1 1H9.163a1.001 1.001 0 0 1-1-1v-1.032a1.837 1.837 0 0 1 3.674 0Zm-1 0a.837.837 0 0 0-1.675 0v1.032h1.675Z"></path>
                        </svg>
                    </div>
                </div>
                <span class="text-gray-500">
                    on {{ $fm->date->format('F j, Y') }}
                </span>
            </div>

            <p class="text-5xl text-gray-600 mt-16 leading-tight">
                {{ $fm->excerpt }}
            </p>
        </div>

        <svg class="fixed inset-0 opacity-25 h-screen translate-x-1/3" xmlns="http://www.w3.org/2000/svg" width="1440" height="560" preserveAspectRatio="none" viewBox="0 0 1440 560">
            <path d="M720 667.63C835.77 669.06 959.94 682.97 1048.88 608.88 1143.67 529.93 1194.57 403.05 1185.43 280 1176.76 163.62 1090.06 74.71 1003.29-3.29 922.4-76.01 828.7-134.63 720-138.37 607.63-142.21 490.84-107.84 416.58-23.42 345.63 57.3 355.11 172.56 352.12 280 348.99 392.34 321.79 519.36 399.14 600.86 477.31 683.24 606.44 666.24 720 667.63" fill="rgba(147, 197, 253, 1)"></path>
            <path d="M720 530.82C794.91 531.75 875.25 540.74 932.81 492.81 994.14 441.72 1027.08 359.62 1021.16 280 1015.55 204.69 959.45 147.16 903.3 96.7 850.97 49.64 790.33 11.71 720 9.29 647.29 6.8 571.72 29.05 523.67 83.67 477.76 135.9 483.9 210.48 481.96 280 479.94 352.69 462.34 434.88 512.39 487.61 562.96 540.92 646.52 529.92 720 530.82" fill="rgba(100, 150, 238, 1)"></path>
            <path d="M720 394.01C754.05 394.43 790.57 398.52 816.73 376.73 844.61 353.51 859.58 316.19 856.89 280 854.34 245.77 828.84 219.62 803.32 196.68 779.53 175.29 751.97 158.05 720 156.95 686.95 155.82 652.6 165.93 630.76 190.76 609.89 214.5 612.68 248.4 611.8 280 610.88 313.04 602.88 350.4 625.63 374.37 648.62 398.6 686.6 393.6 720 394.01" fill="rgba(29, 78, 216, 1)"></path>
        </svg>


    </div>
</x-layouts.app>

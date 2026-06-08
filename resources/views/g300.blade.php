<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>G-300 Downloads</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=B612:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Figtree:400,400i|Adamina:400|spline-sans-mono:300|aldrich:400|audiowide:400|engagement:400|alumni-sans-inline-one:400" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body>
<div class="bg-gray-900 relative min-h-screen w-full">

    <header x-data="{ open: false }" class="absolute inset-x-0 top-0 z-50">
        <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
            <div class="flex lg:flex-1">

            </div>
            <div class="flex lg:hidden">
                <button
                        type="button"
                        @click="open = true"
                        class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-400"
                >
                    <span class="sr-only">Open main menu</span>
                    <svg class="size-6 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
            <div class="hidden lg:flex lg:gap-x-10">
{{--                <img class="h-30 w-30" src="https://assets.douglascockerham.com/EM/EMLogo.png" alt="" />--}}
            </div>
            <div class="hidden lg:flex lg:flex-1 lg:justify-end">

            </div>
        </nav>
    </header>

    <main class="relative isolate py-20">
        <!-- Background -->
        <div class="absolute inset-x-0 top-4 -z-10 flex transform-gpu justify-center overflow-hidden blur-3xl" aria-hidden="true">
            <div class="aspect-1108/632 w-277 flex-none bg-linear-to-r from-[#80caff] to-[#4f46e5] opacity-25" style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)"></div>
        </div>

        <!-- Header section -->
        <div class="px-6 pt-14 lg:px-8">
            <div class="mx-auto max-w-2xl pt-24 text-center sm:pt-40">
                <h1 class="text-5xl font-semibold tracking-tight text-white sm:text-7xl">G-300</h1>
                <p class="mt-8 text-lg font-medium text-pretty text-gray-400 sm:text-xl/8">Intermediate Incident Command System for Expanding Incidents</p>
{{--                <p class="mt-8 text-lg font-medium text-pretty text-gray-400 sm:text-xl/8">It’s quiet here. That is on purpose.</p>--}}
            </div>
        </div>

        <!-- Content section -->
        <div class="mx-auto mt-20 max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <h2 class="text-4xl font-semibold tracking-tight text-pretty text-white sm:text-5xl">Class materials and downloads</h2>
            </div>
            <div class="py-10 space-y-3">
                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/ICS300_Complete_SM.pdf" class="text-gray-400 hover:text-gray-300">Student manual</a>
                    </dt>
                </div>

                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/Unit%202%20Student%20Activity.docx" class="text-gray-400 hover:text-gray-300">Unit 2 - Student Activity</a>
                    </dt>
                </div>

                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/Unit%203%20Student%20Activity.docx" class="text-gray-400 hover:text-gray-300">Unit 3 - Student Activity</a>
                    </dt>
                </div>

                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/Unit%204%20Student%20Activity.docx" class="text-gray-400 hover:text-gray-300">Unit 4 - Student Activity</a>
                    </dt>
                </div>

                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/Unit%205%20Student%20Activity.docx" class="text-gray-400 hover:text-gray-300">Unit 5 - Student Activity</a>
                    </dt>
                </div>

                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/Unit%206%20Student%20Activity.docx" class="text-gray-400 hover:text-gray-300">Unit 6 - Student Activity</a>
                    </dt>
                </div>

                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/Unit%207%20Student%20Activity.docx" class="text-gray-400 hover:text-gray-300">Unit 7 - Student Activity</a>
                    </dt>
                </div>

                <div class="relative pl-9">
                    <dt class="inline font-semibold text-white">
                        <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                            <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                        </svg>
                        <a href="https://assets.douglascockerham.com/EM/g300/G-300%20ICS%20Forms.pdf" class="text-gray-400 hover:text-gray-300">ICS Forms</a>
                    </dt>
                </div>
            </div>
        </div>


        <!-- CTA section -->
        <div class="relative isolate -z-10 mt-32 sm:mt-40">

        </div>
    </main>

    <!-- Footer -->
    <footer class="p-10">
        <div>
            <div class="mt-16 border-t border-white/10 pt-8 sm:mt-20 lg:mt-24">
                <p class="text-sm/6 text-gray-400">&copy; 2025 Eloquence Digital, LLC. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>

</body>
</html>

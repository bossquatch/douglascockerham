<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
        <link href="https://fonts.googleapis.com/css2?family=B612:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Figtree:400,400i|Adamina:400|spline-sans-mono:300|aldrich:400|audiowide:400|engagement:400|alumni-sans-inline-one:400" rel="stylesheet"/>

        <!-- Styles -->
{{--        <link rel="stylesheet" href="/build/assets/app-D2VEQ5sV.css"/>--}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])


    </head>

    <body>
    <div class="bg-gray-900">
        <!-- Header -->
        <header class="absolute inset-x-0 top-0 z-50">
            <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                    <a href="#" class="-m-1.5 p-1.5">
                        <span class="sr-only">Your Company</span>
                        <img class="h-10 w-auto" src="{{ URL::asset('storage/images/1.png') }}" alt="" />
                    </a>
                </div>
                <div class="flex lg:hidden">
                    <button type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-400">
                        <span class="sr-only">Open main menu</span>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
                <div class="hidden lg:flex lg:gap-x-12">
                    <a href="#" class="text-sm/6 font-semibold text-white">Developer</a>
                    <a href="#" class="text-sm/6 font-semibold text-white">Technomancer</a>
                    <a href="#" class="text-sm/6 font-semibold text-white">Overqualified</a>
                    <a href="#" class="text-sm/6 font-semibold text-white">Resilientish</a>
                </div>
                <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                    <a href="#" class="text-sm/6 font-semibold text-white">Log in <span aria-hidden="true">&rarr;</span></a>
                </div>
            </nav>
            <!-- Mobile menu, show/hide based on menu open state. -->
{{--            <div class="lg:hidden" role="dialog" aria-modal="true">--}}
{{--                <!-- Background backdrop, show/hide based on slide-over state. -->--}}
{{--                <div class="fixed inset-0 z-50"></div>--}}
{{--                <div class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-gray-900 p-6 sm:max-w-sm sm:ring-1 sm:ring-white/10">--}}
{{--                    <div class="flex items-center justify-between">--}}
{{--                        <a href="#" class="-m-1.5 p-1.5">--}}
{{--                            <span class="sr-only">You found me</span>--}}
{{--                            <img class="h-8 w-auto" src="{{ URL::asset('storage/images/IMG_4371.jpeg') }}" alt="" />--}}
{{--                        </a>--}}
{{--                        <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-400">--}}
{{--                            <span class="sr-only">Close menu</span>--}}
{{--                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">--}}
{{--                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />--}}
{{--                            </svg>--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                    <div class="mt-6 flow-root">--}}
{{--                        <div class="-my-6 divide-y divide-gray-500/25">--}}
{{--                            <div class="space-y-2 py-6">--}}
{{--                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-gray-800">Developer</a>--}}
{{--                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-gray-800">Technomancer</a>--}}
{{--                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-gray-800">Overqualified</a>--}}
{{--                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-gray-800">Resilientish</a>--}}
{{--                            </div>--}}
{{--                            <div class="py-6">--}}
{{--                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-gray-800">Log in</a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </header>

        <main class="relative isolate">
            <!-- Background -->
            <div class="absolute inset-x-0 top-4 -z-10 flex transform-gpu justify-center overflow-hidden blur-3xl" aria-hidden="true">
                <div class="aspect-1108/632 w-277 flex-none bg-linear-to-r from-[#80caff] to-[#4f46e5] opacity-25" style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)"></div>
            </div>

            <!-- Header section -->
            <div class="px-6 pt-14 lg:px-8">
                <div class="mx-auto max-w-2xl pt-24 text-center sm:pt-40">
                    <h1 class="text-5xl font-semibold tracking-tight text-white sm:text-7xl">You found me</h1>
                    <p class="mt-8 text-lg font-medium text-pretty text-gray-400 sm:text-xl/8">Took you long enough. I was starting to worry.</p>
                    <p class="mt-8 text-lg font-medium text-pretty text-gray-400 sm:text-xl/8">It’s quiet here. That is on purpose.</p>
                </div>
            </div>

            <!-- Content section -->
            <div class="mx-auto mt-20 max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:mx-0 lg:max-w-none">
                    <div class="grid max-w-xl grid-cols-1 gap-8 text-base/7 text-gray-300 lg:max-w-none lg:grid-cols-2">
                        <div>
                            <p>You found me. That already puts you ahead of the curve.  It also means that either you were looking for me—or the internet played a practical joke. Either way, welcome.</p>
                            <p class="mt-8">I believe in practical solutions, clear communication, and tools that work under pressure. Whether it’s building a custom shelter staffing app, developing training modules, or refining logistics workflows, my focus is always on function over flash—though a little polish never hurts.</p>
                        </div>
                        <div>
                            <p>This site isn’t here to sell you anything. It’s here because sometimes, when you build weirdly specific skills, you want to point to something and say: “Yeah. I made that. It works.”</p>
                            <p class="mt-8">This space is a placeholder for ideas that needed somewhere to land. Sometimes it’s training content, sometimes it’s code, and sometimes it’s just me thinking out loud.</p>
                        </div>
                    </div>
                    <dl class="mt-16 grid grid-cols-1 gap-x-8 gap-y-12 sm:mt-20 sm:grid-cols-2 sm:gap-y-16 lg:mt-28 lg:grid-cols-4">
                        <div class="flex flex-col-reverse gap-y-3 border-l border-white/20 pl-6">
                            <dt class="text-base/7 text-gray-300">Meetings that could have been emails</dt>
                            <dd class="text-3xl font-semibold tracking-tight text-white">3,279</dd>
                        </div>
                        <div class="flex flex-col-reverse gap-y-3 border-l border-white/20 pl-6">
                            <dt class="text-base/7 text-gray-300">Number of times told "you can't do that"</dt>
                            <dd class="text-3xl font-semibold tracking-tight text-white">6,000+</dd>
                        </div>
                        <div class="flex flex-col-reverse gap-y-3 border-l border-white/20 pl-6">
                            <dt class="text-base/7 text-gray-300">Number of times that I ignored that advice</dt>
                            <dd class="text-3xl font-semibold tracking-tight text-white">5,999</dd>
                        </div>
                        <div class="flex flex-col-reverse gap-y-3 border-l border-white/20 pl-6">
                            <dt class="text-base/7 text-gray-300">Lines of code deleted only to be written again</dt>
                            <dd class="text-3xl font-semibold tracking-tight text-white">8M+</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Image section -->
            <div class="mt-32 sm:mt-40 xl:mx-auto xl:max-w-7xl xl:px-8">
                <img src="{{ URL::asset('storage/images/lightning_crashes_wide.jpeg') }}" alt="" class="aspect-9/4 w-full object-cover xl:rounded-3xl" />
                <p class="pl-10 text-base/7 text-gray-200">(yes, I took this)</p>
            </div>

            <!-- Values section -->
            <div class="mx-auto mt-32 max-w-7xl px-6 sm:mt-40 lg:px-8">
                <div class="mx-auto max-w-2xl lg:mx-0">
                    <h2 class="text-4xl font-semibold tracking-tight text-pretty text-white sm:text-5xl">Principles</h2>
                    <p class="mt-6 text-lg/8 text-gray-300">What is behind all of this awesomeness?</p>
                </div>
                <dl class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 text-base/7 text-gray-300 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 lg:gap-x-16">
                    <div class="relative pl-9">
                        <dt class="inline font-semibold text-white">
                            <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M4.606 12.97a.75.75 0 0 1-.134 1.051 2.494 2.494 0 0 0-.93 2.437 2.494 2.494 0 0 0 2.437-.93.75.75 0 1 1 1.186.918 3.995 3.995 0 0 1-4.482 1.332.75.75 0 0 1-.461-.461 3.994 3.994 0 0 1 1.332-4.482.75.75 0 0 1 1.052.134Z" clip-rule="evenodd" />
                                <path fill-rule="evenodd" d="M5.752 12A13.07 13.07 0 0 0 8 14.248v4.002c0 .414.336.75.75.75a5 5 0 0 0 4.797-6.414 12.984 12.984 0 0 0 5.45-10.848.75.75 0 0 0-.735-.735 12.984 12.984 0 0 0-10.849 5.45A5 5 0 0 0 1 11.25c.001.414.337.75.751.75h4.002ZM13 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" clip-rule="evenodd" />
                            </svg>
                            Automate the boring stuff.
                        </dt>
                        <dd class="inline">I don't do busy work and sure as hell don't like wasting my time. Twice is a pattern. Three times is a job for code.</dd>
                    </div>
                    <div class="relative pl-9">
                        <dt class="inline font-semibold text-white">
                            <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M11 2a1 1 0 1 0-2 0v6.5a.5.5 0 0 1-1 0V3a1 1 0 1 0-2 0v5.5a.5.5 0 0 1-1 0V5a1 1 0 1 0-2 0v7a7 7 0 1 0 14 0V8a1 1 0 1 0-2 0v3.5a.5.5 0 0 1-1 0V3a1 1 0 1 0-2 0v5.5a.5.5 0 0 1-1 0V2Z" clip-rule="evenodd" />
                            </svg>
                            Challenge the StatusQuo.
                        </dt>
                        <dd class="inline">"We've always done it this way" is not a reason. It's a warning.</dd>
                    </div>
                    <div class="relative pl-9">
                        <dt class="inline font-semibold text-white">
                            <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM6 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM1.49 15.326a.78.78 0 0 1-.358-.442 3 3 0 0 1 4.308-3.516 6.484 6.484 0 0 0-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 0 1-2.07-.655ZM16.44 15.98a4.97 4.97 0 0 0 2.07-.654.78.78 0 0 0 .357-.442 3 3 0 0 0-4.308-3.517 6.484 6.484 0 0 1 1.907 3.96 2.32 2.32 0 0 1-.026.654ZM18 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM5.304 16.19a.844.844 0 0 1-.277-.71 5 5 0 0 1 9.947 0 .843.843 0 0 1-.277.71A6.975 6.975 0 0 1 10 18a6.974 6.974 0 0 1-4.696-1.81Z" />
                            </svg>
                            Simplify relentlessly.
                        </dt>
                        <dd class="inline">Clarity beats cleverness. Every time.</dd>
                    </div>
                    <div class="relative pl-9">
                        <dt class="inline font-semibold text-white">
                            <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path fill-rule="evenodd" d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.739.914a.75.75 0 1 0 1.06 1.06c.37-.369.69-.77.96-1.193a26.61 26.61 0 0 1 3.095 2.348.75.75 0 0 0 .992 0 26.547 26.547 0 0 1 5.93-3.95.75.75 0 0 0 .42-.739 41.053 41.053 0 0 0-.39-3.114 29.925 29.925 0 0 0-5.199 2.801 2.25 2.25 0 0 1-2.514 0c-.41-.275-.826-.541-1.25-.797a6.985 6.985 0 0 1-1.084 3.45 26.503 26.503 0 0 0-1.281-.78A5.487 5.487 0 0 0 6 12v-.54Z" clip-rule="evenodd" />
                            </svg>
                            First, know what you are doing.
                        </dt>
                        <dd class="inline">Assume everything is connected. Especially the thing that you <i class="font-bold italic">thought</i> you knew.</dd>
                    </div>
                    <div class="relative pl-9">
                        <dt class="inline font-semibold text-white">
                            <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path d="M15.98 1.804a1 1 0 0 0-1.96 0l-.24 1.192a1 1 0 0 1-.784.785l-1.192.238a1 1 0 0 0 0 1.962l1.192.238a1 1 0 0 1 .785.785l.238 1.192a1 1 0 0 0 1.962 0l.238-1.192a1 1 0 0 1 .785-.785l1.192-.238a1 1 0 0 0 0-1.962l-1.192-.238a1 1 0 0 1-.785-.785l-.238-1.192ZM6.949 5.684a1 1 0 0 0-1.898 0l-.683 2.051a1 1 0 0 1-.633.633l-2.051.683a1 1 0 0 0 0 1.898l2.051.684a1 1 0 0 1 .633.632l.683 2.051a1 1 0 0 0 1.898 0l.683-2.051a1 1 0 0 1 .633-.633l2.051-.683a1 1 0 0 0 0-1.898l-2.051-.683a1 1 0 0 1-.633-.633L6.95 5.684ZM13.949 13.684a1 1 0 0 0-1.898 0l-.184.551a1 1 0 0 1-.632.633l-.551.183a1 1 0 0 0 0 1.898l.551.183a1 1 0 0 1 .633.633l.183.551a1 1 0 0 0 1.898 0l.184-.551a1 1 0 0 1 .632-.633l.551-.183a1 1 0 0 0 0-1.898l-.551-.184a1 1 0 0 1-.633-.632l-.183-.551Z" />
                            </svg>
                            Elegance is the shortest path between clarity and function.
                        </dt>
                        <dd class="inline">It's not always what you can add; most times it is what you can take away and still make your point.</dd>
                    </div>
                    <div class="relative pl-9">
                        <dt class="inline font-semibold text-white">
                            <svg class="absolute top-1 left-1 size-5 text-indigo-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.061 1.06l1.06 1.06Z" />
                            </svg>
                            Have fun with it.
                        </dt>
                        <dd class="inline">It's going to break anyway, you might as well enjoy the ride.</dd>
                    </div>
                </dl>
            </div>

            <!-- Team section -->
{{--            <div class="mx-auto mt-32 max-w-7xl px-6 sm:mt-40 lg:px-8">--}}
{{--                <div class="mx-auto max-w-2xl lg:mx-0">--}}
{{--                    <h2 class="text-4xl font-semibold tracking-tight text-pretty text-white sm:text-5xl">Our team</h2>--}}
{{--                    <p class="mt-6 text-lg/8 text-gray-300">We’re a dynamic group of individuals who are passionate about what we do and dedicated to delivering the best results for our clients.</p>--}}
{{--                </div>--}}
{{--                <ul role="list" class="mx-auto mt-20 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-14 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 xl:grid-cols-4">--}}
{{--                    <li>--}}
{{--                        <img class="aspect-14/13 w-full rounded-2xl object-cover" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=1024&h=1024&q=80" alt="" />--}}
{{--                        <h3 class="mt-6 text-lg/8 font-semibold tracking-tight text-white">Leslie Alexander</h3>--}}
{{--                        <p class="text-base/7 text-gray-300">Co-Founder / CEO</p>--}}
{{--                        <p class="text-sm/6 text-gray-500">Toronto, Canada</p>--}}
{{--                    </li>--}}

{{--                    <!-- More people... -->--}}
{{--                </ul>--}}
{{--            </div>--}}

            <!-- CTA section -->
            <div class="relative isolate -z-10 mt-32 sm:mt-40">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="mx-auto flex max-w-2xl flex-col gap-16 bg-white/5 px-6 py-16 ring-1 ring-white/10 sm:rounded-3xl sm:p-8 lg:mx-0 lg:max-w-none lg:flex-row lg:items-center lg:py-20 xl:gap-x-20 xl:px-20">
                        <img class="h-96 w-full flex-none rounded-2xl object-cover shadow-xl lg:aspect-square lg:h-auto lg:max-w-sm" src="https://images.unsplash.com/photo-1519338381761-c7523edc1f46?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=800&q=80" alt="" />
                        <div class="w-full flex-auto">
                            <h2 class="text-4xl font-semibold tracking-tight text-pretty text-white sm:text-5xl">Join our team</h2>
                            <p class="mt-6 text-lg/8 text-pretty text-gray-300">We do serious work without taking ourselves too seriously. If you're the kind of person who enjoys solving impossible or messy problems with sharp thinking and better tools - we want to hear from you.</p>
                            <ul role="list" class="mt-10 grid grid-cols-1 gap-x-8 gap-y-3 text-base/7 text-white sm:grid-cols-2">
                                <li class="flex gap-x-3">
                                    <svg class="h-7 w-5 flex-none" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    Endless opportunities to reconsider your career path
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-7 w-5 flex-none" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    Flexible work hours as long as it's all the time
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-7 w-5 flex-none" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    Serve a higher purpose, align logic with impact
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-7 w-5 flex-none" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    We love what you do!
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-7 w-5 flex-none" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    Weekly sarcasm and sarcasm training
                                </li>
                                <li class="flex gap-x-3">
                                    <svg class="h-7 w-5 flex-none" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                    The money ain't great, but come to thing of it, neither is the work
                                </li>
                            </ul>
                            <div class="mt-10 flex">
                                <a href="#" class="text-sm/6 font-semibold text-indigo-400">See our job postings <span aria-hidden="true">&rarr;</span></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute inset-x-0 -top-16 -z-10 flex transform-gpu justify-center overflow-hidden blur-3xl" aria-hidden="true">
                    <div class="aspect-1318/752 w-329.5 flex-none bg-linear-to-r from-[#80caff] to-[#4f46e5] opacity-25" style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)"></div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="">
            <div class="mx-auto max-w-7xl px-6 pt-32 pb-8 lg:px-8 lg:pt-40">
                <div class="xl:grid xl:grid-cols-3 xl:gap-8">
                    <div class="space-y-8 justify-center">
                        <div class="flex flex-row items-center gap-x-4">
                            <img class="h-10 w-auto" src="{{ URL::asset('storage/images/2.png') }}" alt="" />
                            <p class="px-4 text-sm/6 text-balance text-gray-300 italic font-fun">"They said I couldn’t.  So I did.  Then I took a nap."</p>
                        </div>
                        <div class="flex gap-x-6">
                            <a href="{{ config('app.social_media.facebook') }}" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">Facebook</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="{{ config('app.social_media.instagram') }}" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">Instagram</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="{{ config('app.social_media.x') }}" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">X</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z" />
                                </svg>
                            </a>
                            <a href="{{ config('app.social_media.github') }}" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">GitHub</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="{{ config('app.social_media.youtube') }}" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">YouTube</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="{{ config('app.social_media.linkedin') }}" class="text-gray-400 hover:text-gray-300">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="size-6" fill="currentColor" viewBox="0 0 448 512">
                                    <path d="M100.3 448H7.4V148.9h92.9zM53.8 108.1C24.1 108.1 0 83.5 0 53.8a53.8 53.8 0 0 1 107.6 0c0 29.7-24.1 54.3-53.8 54.3zM447.9 448h-92.7V302.4c0-34.7-.7-79.2-48.3-79.2-48.3 0-55.7 37.7-55.7 76.7V448h-92.8V148.9h89.1v40.8h1.3c12.4-23.5 42.7-48.3 87.9-48.3 94 0 111.3 61.9 111.3 142.3V448z" />
                                </svg>
                            </a>
                        </div>
                    </div>
{{--                    <div class="mt-16 grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0">--}}
{{--                        <div class="md:grid md:grid-cols-2 md:gap-8">--}}
{{--                            <div>--}}
{{--                                <h3 class="text-sm/6 font-semibold text-white">Solutions</h3>--}}
{{--                                <ul role="list" class="mt-6 space-y-4">--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Marketing</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Analytics</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Automation</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Commerce</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Insights</a>--}}
{{--                                    </li>--}}
{{--                                </ul>--}}
{{--                            </div>--}}
{{--                            <div class="mt-10 md:mt-0">--}}
{{--                                <h3 class="text-sm/6 font-semibold text-white">Support</h3>--}}
{{--                                <ul role="list" class="mt-6 space-y-4">--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Submit ticket</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Documentation</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Guides</a>--}}
{{--                                    </li>--}}
{{--                                </ul>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="md:grid md:grid-cols-2 md:gap-8">--}}
{{--                            <div>--}}
{{--                                <h3 class="text-sm/6 font-semibold text-white">Company</h3>--}}
{{--                                <ul role="list" class="mt-6 space-y-4">--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">About</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Blog</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Jobs</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Press</a>--}}
{{--                                    </li>--}}
{{--                                </ul>--}}
{{--                            </div>--}}
{{--                            <div class="mt-10 md:mt-0">--}}
{{--                                <h3 class="text-sm/6 font-semibold text-white">Legal</h3>--}}
{{--                                <ul role="list" class="mt-6 space-y-4">--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Terms of service</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">Privacy policy</a>--}}
{{--                                    </li>--}}
{{--                                    <li>--}}
{{--                                        <a href="#" class="text-sm/6 text-gray-400 hover:text-white">License</a>--}}
{{--                                    </li>--}}
{{--                                </ul>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
                <div class="mt-16 border-t border-white/10 pt-8 sm:mt-20 lg:mt-24">
                    <p class="text-sm/6 text-gray-400">&copy; 2025 Eloquence Digital, LLC. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>


    {{--    <div @class(['flex flex-col h-screen justify-between'])>--}}
{{--        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">--}}
{{--            @if (Route::has('login'))--}}
{{--                <nav class="flex items-center justify-end gap-4">--}}
{{--                    @auth--}}
{{--                        <a--}}
{{--                            href="{{ url('/dashboard') }}"--}}
{{--                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"--}}
{{--                        >--}}
{{--                            Dashboard--}}
{{--                        </a>--}}
{{--                    @else--}}
{{--                        <a--}}
{{--                            href="{{ route('login') }}"--}}
{{--                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"--}}
{{--                        >--}}
{{--                            Log in--}}
{{--                        </a>--}}

{{--                        @if (Route::has('register'))--}}
{{--                            <a--}}
{{--                                href="{{ route('register') }}"--}}
{{--                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">--}}
{{--                                Register--}}
{{--                            </a>--}}
{{--                        @endif--}}
{{--                    @endauth--}}
{{--                </nav>--}}
{{--            @endif--}}
{{--        </header>--}}
{{--        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">--}}
{{--            <main class="flex w-full flex-col-reverse lg:max-w-4xl lg:flex-row">--}}
{{--                <div @class(['flex flex-col items-center justify-center mt-40'])>--}}
{{--                    <div class="backdrop-blur-sm bg-white/20 text-white/100 font-fun text-6xl px-10 py-4 rounded-2xl">douglascockerham.com</div>--}}
{{--                </div>--}}

{{--            </main>--}}
{{--        </div>--}}

{{--        @if (Route::has('login'))--}}
{{--            <div class="h-14.5 hidden lg:block"></div>--}}
{{--        @endif--}}
{{--    </div>--}}
    </body>
</html>

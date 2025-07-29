<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>douglascockerham.com</title>

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
    {{--        <link rel="stylesheet" href="/build/assets/app-D2VEQ5sV.css"/>--}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<!-- Summary Header -->
<header class="bg-white shadow p-6 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Polk County Shelter Status</h1>
                <p class="text-sm text-gray-500">Last updated: <span id="lastUpdated">Jul 14, 2025 10:30 AM</span></p>
            </div>
            <div class="mt-4 md:mt-0 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-xl shadow text-center">
                    <div class="text-sm text-gray-500">Shelters Open</div>
                    <div class="text-xl font-semibold text-blue-700" id="openShelters">12</div>
                </div>
                <div class="bg-green-50 p-4 rounded-xl shadow text-center">
                    <div class="text-sm text-gray-500">Total Census</div>
                    <div class="text-xl font-semibold text-green-700" id="totalCensus">538</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-xl shadow text-center">
                    <div class="text-sm text-gray-500">Total Capacity</div>
                    <div class="text-xl font-semibold text-yellow-700" id="totalCapacity">820</div>
                </div>
                <div class="bg-red-50 p-4 rounded-xl shadow text-center">
                    <div class="text-sm text-gray-500">Issues Reported</div>
                    <div class="text-xl font-semibold text-red-700" id="issueCount">3</div>
                </div>
            </div>
        </div>

        <!-- Mini Chart.js Graph -->
{{--        <div class="mt-6">--}}
{{--            <canvas id="occupancyChart" height="80"></canvas>--}}
{{--        </div>--}}
    </div>
</header>

<!-- Detailed Shelter List -->
<main class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-semibold mb-4">Shelter Details</h2>
        <div class="overflow-x-auto bg-white shadow rounded-xl">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shelter Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Census / Capacity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issues</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">Ridge Community HS</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">Special Needs</span></td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center">
                            <div>120 / 845 (14%)</div>
                            <div><canvas id="occupancyChart" height="5"></canvas></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-green-600 font-semibold">Open</td>
                    <td class="px-6 py-4">Jane Doe</td>
                    <td class="px-6 py-4 text-yellow-600">Generator check pending</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">Lake Region High School</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded-full">Pet</span></td>
                    <td class="px-6 py-4">352 / 672 (52%)</td>
                    <td class="px-6 py-4 text-green-600 font-semibold">Open</td>
                    <td class="px-6 py-4">Massey Fergusson</td>
                    <td class="px-6 py-4 text-green-600">No issues</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">Polk Specialty Care Unit</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">Special Needs</span></td>
                    <td class="px-6 py-4">---</td>
                    <td class="px-6 py-4 text-red-600 font-semibold">Closed</td>
                    <td class="px-6 py-4">Jane Doe</td>
                    <td class="px-6 py-4 text-green-600">No issues</td>
                </tr>
                <td class="px-6 py-4">87 / 655 (13%)</td>
                <td class="px-6 py-4 text-green-600 font-semibold">Open</td>
                <td class="px-6 py-4">Jane Doe</td>
                <td class="px-6 py-4 text-green-600">No issues</td>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">Pinewood Elementary School</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full">General Population</span></td>
                    <td class="px-6 py-4">87 / 655 (13%)</td>
                    <td class="px-6 py-4 text-green-600 font-semibold">Open</td>
                    <td class="px-6 py-4">Jane Doe</td>
                    <td class="px-6 py-4 text-green-600">No issues</td>
                </tr>
                <!-- Add more rows dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Chart.js Script -->
<script>
    const ctx = document.getElementById('occupancyChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Available'],
            datasets: [{
                label: 'Shelter Occupancy',
                data: [120, 845],
                backgroundColor: ['#3b82f6', '#d1d5db'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

</body>
</html>
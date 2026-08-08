<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Events Management') }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center gap-6">
                <div class="flex-shrink-0">
                    <img src="/assets/images/logo.png" class="h-16 w-auto" alt="Logo"
                        onerror="this.style.display='none'">
                </div>
                <div>
                    <h1 class="text-3xl font-serif font-bold text-gray-900 tracking-tight">
                        Events Management
                    </h1>
                    <p class="text-lg text-gray-500 font-sans">
                        Mutiara Harapan Islamic School
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="h-1 w-full bg-gradient-to-r from-amber-600 via-amber-400 to-amber-200"></div>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alert Success --}}
            @if (session('success'))
                <div
                    class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                    <button type="button" class="text-green-500 hover:text-green-700"
                        onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-t-8 border-amber-500">
                <div class="p-8">
                    {{-- Header Card: Title & Actions --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 font-serif">Event List</h3>
                            <p class="text-gray-500 mt-1">Manage school event registrations and details.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            <!--<a href="{{ route('google.connect') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">-->
                            <!--    <i class="fab fa-google mr-2"></i> Connect Google-->
                            <!--</a>-->
                            <a href="{{ route('dashboard.events.create') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 focus:bg-amber-700 active:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                <i class="fas fa-plus mr-2"></i> Create New Event
                            </a>
                        </div>
                    </div>

                    {{-- Table Section --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Event Title
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Owner
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Slug / URL
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($events as $event)
                                    <tr class="hover:bg-amber-50/30 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $event->title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $event->owner->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-mono font-medium text-gray-600 bg-gray-100 rounded border border-gray-200">
                                                {{ $event->slug }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if ($event->status == 'PUBLISHED' || $event->status == 'active')
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200 shadow-sm">
                                                    <span class="w-2 h-2 mr-1 bg-green-500 rounded-full"></span> Active
                                                </span>
                                            @elseif($event->status == 'DRAFT')
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 shadow-sm">
                                                    <span class="w-2 h-2 mr-1 bg-gray-500 rounded-full"></span> Draft
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                                    <span class="w-2 h-2 mr-1 bg-red-500 rounded-full"></span> Closed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-center space-x-2">
                                                {{-- Detail --}}
                                                <a target="_blank" href="{{ route('public.event.show', $event->slug) }}"
                                                    class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-full transition-colors"
                                                    title="View Detail">
                                                    <i class="fas fa-eye text-lg"></i>
                                                </a>

                                                {{-- Edit --}}
                                                <a href="{{ route('dashboard.events.edit', $event) }}"
                                                    class="text-amber-600 hover:text-amber-900 p-2 hover:bg-amber-50 rounded-full transition-colors"
                                                    title="Edit Event">
                                                    <i class="fas fa-edit text-lg"></i>
                                                </a>

                                                {{-- Pendaftar --}}
                                                <a href="{{ route('dashboard.events.registrations.index', $event) }}"
                                                    class="text-purple-600 hover:text-purple-900 p-2 hover:bg-purple-50 rounded-full transition-colors"
                                                    title="Registrations">
                                                    <i class="fas fa-users text-lg"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="bg-gray-100 p-4 rounded-full mb-3">
                                                    <i class="fas fa-folder-open text-4xl text-gray-400"></i>
                                                </div>
                                                <p class="text-lg font-medium text-gray-900">No events found</p>
                                                <p class="text-sm text-gray-500 mb-4">Get started by creating a new
                                                    event for the school.</p>
                                                <a href="{{ route('dashboard.events.create') }}"
                                                    class="text-amber-600 hover:text-amber-700 font-medium hover:underline">
                                                    Create your first event &rarr;
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if (method_exists($events, 'links'))
                        <div class="mt-6 border-t border-gray-200 pt-4">
                            {{ $events->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

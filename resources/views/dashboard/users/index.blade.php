<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-semibold">Users</h1>
                <p class="text-sm text-gray-500">Manajemen akun (khusus admin).</p>
            </div>

            <a href="{{ route('dashboard.users.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                + Buat User
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="p-4 border-b text-sm text-gray-600">
                Total: <span class="font-semibold text-gray-900">{{ $users->total() }}</span> user
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left p-3">Nama</th>
                            <th class="text-left p-3">Email</th>
                            <th class="text-left p-3">Role</th>
                            <th class="text-left p-3">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($users as $u)
                            <tr>
                                <td class="p-3 font-medium text-gray-900">{{ $u->name }}</td>
                                <td class="p-3 text-gray-700">{{ $u->email }}</td>
                                <td class="p-3">
                                    @php
                                        $badge = $u->role === 'admin'
                                            ? 'bg-purple-100 text-purple-800'
                                            : 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs {{ $badge }}">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-600">{{ optional($u->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-6 text-gray-500" colspan="4">Belum ada user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

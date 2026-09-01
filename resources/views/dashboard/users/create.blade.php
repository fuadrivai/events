<x-app-layout>
    <div class="max-w-xl mx-auto p-6">
        <div class="mb-5">
            <h1 class="text-xl font-semibold">Buat Akun User</h1>
            <p class="text-sm text-gray-500">Akun ini otomatis bertipe <b>user</b> (bukan admin).</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-sm font-medium">Nama</label>
                <input name="name" value="{{ old('name') }}"
                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="text-sm font-medium">Cabang</label>
                @if ($branches->isEmpty())
                    <p class="mt-1 text-sm text-gray-500">Belum ada cabang yang tersedia.</p>
                @else
                    <select name="branch_id"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Pilih cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div>
                <label class="text-sm font-medium">Role</label>
                <select name="role"
                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required>
                    <option value="user" {{ old('role', 'user') == 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    Simpan
                </button>

                <a href="{{ route('dashboard.users.index') }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>

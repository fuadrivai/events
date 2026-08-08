@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6">
  <div class="p-6 rounded-2xl border bg-white">
    <h1 class="text-xl font-semibold">Pembayaran Berhasil</h1>
    <p class="mt-2 text-sm text-gray-600">
      Terima kasih. Status resmi tetap diproses melalui webhook.
    </p>
    <a class="inline-block mt-4 px-4 py-2 rounded-lg bg-indigo-600 text-white" href="/">
      Kembali
    </a>
  </div>
</div>
@endsection

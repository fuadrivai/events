@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6">
  <div class="p-6 rounded-2xl border bg-white">
    <h1 class="text-xl font-semibold">Pembayaran Belum Berhasil</h1>
    <p class="mt-2 text-sm text-gray-600">
      Jika kamu sudah membayar, mohon tunggu webhook. Jika belum, kamu bisa coba lagi.
    </p>
    <a class="inline-block mt-4 px-4 py-2 rounded-lg border bg-white" href="/">
      Kembali
    </a>
  </div>
</div>
@endsection

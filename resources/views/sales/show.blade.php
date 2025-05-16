@extends('layouts.app')
@section('content')

<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
  <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl p-6">
    <div class="mb-6 border-b border-gray-200 pb-3">
      <h4 class="text-xl font-semibold text-blue-600">Detalhes da Venda #{{ $sale->id }}</h4>
    </div>

    <div class="space-y-3 text-gray-700">
      <p><strong class="font-semibold">Cliente:</strong> {{ $sale->client->name ?? 'N/A' }}</p>
      <p><strong class="font-semibold">Vendedor:</strong> {{ $sale->user->name }}</p>
      <p><strong class="font-semibold">Forma de Pagamento:</strong> {{ $sale->payment_method }}</p>
      <p><strong class="font-semibold">Total:</strong> R$ {{ number_format($sale->total_amount, 2, ',', '.') }}</p>
    </div>

    <div class="mt-6">
      <h5 class="text-lg font-semibold text-blue-600 mb-2">Produtos</h5>
      <ul class="list-disc list-inside space-y-1 text-gray-700">
        @foreach($sale->items as $item)
          <li>
            {{ $item->product->name }} - Quantidade: {{ $item->quantity }} - 
            R$ {{ number_format($item->price, 2, ',', '.') }}
          </li>
        @endforeach
      </ul>
    </div>

    <div class="mt-6">
      <h5 class="text-lg font-semibold text-blue-600 mb-2">Parcelas</h5>
      <ul class="list-disc list-inside space-y-1 text-gray-700">
        @foreach($sale->installments as $inst)
          <li>
            {{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }} - 
            R$ {{ number_format($inst->amount, 2, ',', '.') }}
          </li>
        @endforeach
      </ul>
    </div>

    <div class="mt-8">
      <a href="{{ route('sales.index') }}" 
         class="inline-block px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
        Voltar
      </a>
    </div>
  </div>
</div>

@endsection

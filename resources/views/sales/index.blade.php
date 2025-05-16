@extends('layouts.app')
@section('content')

<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
  <div class="bg-white shadow-lg rounded-lg w-full max-w-4xl p-6">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
      <h2 class="text-2xl font-semibold text-blue-600 mb-4 md:mb-0">Vendas</h2>
      <a href="{{ route('sales.create') }}" 
         class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
         </svg>
         Nova Venda
      </a>
    </div>

    <div class="mb-6">
      <input type="search" placeholder="Buscar cliente..." 
             class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-blue-400" />
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full bg-white">
        <thead>
          <tr class="bg-blue-100 text-blue-700 uppercase text-sm leading-normal">
            <th class="py-3 px-6 text-left">ID</th>
            <th class="py-3 px-6 text-left">Cliente</th>
            <th class="py-3 px-6 text-left">Vendedor</th>
            <th class="py-3 px-6 text-left">Total</th>
            <th class="py-3 px-6 text-left">Data</th>
            <th class="py-3 px-6 text-center">Ações</th>
          </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
          @forelse ($sales as $sale)
            <tr class="border-b border-gray-200 hover:bg-gray-100">
              <td class="py-3 px-6 text-left whitespace-nowrap font-semibold">{{ $sale->id }}</td>
              <td class="py-3 px-6 text-left whitespace-nowrap">{{ $sale->client->name ?? 'Não informado' }}</td>
              <td class="py-3 px-6 text-left whitespace-nowrap">{{ $sale->user->name }}</td>
              <td class="py-3 px-6 text-left whitespace-nowrap font-semibold">
                R$ {{ number_format($sale->total_amount ?? $sale->total, 2, ',', '.') }}
              </td>
              <td class="py-3 px-6 text-left whitespace-nowrap">
                {{ $sale->created_at->format('d/m/Y') }}
              </td>
              <td class="py-3 px-6 text-center whitespace-nowrap space-x-2">
                <a href="{{ route('sales.show', $sale->id) }}" 
                   class="text-blue-500 hover:text-blue-700" title="Ver detalhes">
                  <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19c-4 0-7-4-7-7s3-7 7-7 7 4 7 7-3 7-7 7z" />
                  </svg>
                </a>

                <a href="{{ route('sales.pdf', $sale->id) }}" target="_blank" 
                   class="text-gray-500 hover:text-gray-700" title="Gerar PDF">
                  <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zM13 3.5L18.5 9H13V3.5z" />
                  </svg>
                </a>

                <a href="{{ route('sales.edit', $sale->id) }}" 
                   class="text-indigo-600 hover:text-indigo-800" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7-7h14" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 7.5l-9 9" />
                  </svg>
                </a>

                <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Tem certeza que deseja excluir essa venda?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-800" title="Excluir">
                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-10 text-center text-gray-400 italic">
                Nenhuma venda registrada.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

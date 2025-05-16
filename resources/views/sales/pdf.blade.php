<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Resumo da Venda</title></head>
<body>
    <h1>Resumo da Venda #{{ $sale->id }}</h1>
    <p><strong>Cliente:</strong> {{ $sale->client->name ?? 'N/A' }}</p>
    <p><strong>Vendedor:</strong> {{ $sale->user->name }}</p>
    <p><strong>Total:</strong> R$ {{ number_format($sale->total_amount, 2, ',', '.') }}</p>

    <h3>Itens</h3>
    <ul>
        @foreach($sale->items as $item)
            <li>{{ $item->product->name }} - Quantidade: {{ $item->quantity }} - R$ {{ number_format($item->price, 2, ',', '.') }}</li>
        @endforeach
    </ul>

    <h3>Parcelas</h3>
    <ul>
        @foreach($sale->installments as $installment)
            <li>{{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }} - R$ {{ number_format($installment->amount, 2, ',', '.') }}</li>
        @endforeach
    </ul>
</body>
</html>

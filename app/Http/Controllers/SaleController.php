<?php

namespace App\Http\Controllers;

use App\Models\{Sale, Client, Product, SaleItem, Installment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::with('client', 'user')->when($request->search, function ($q) use ($request) {
            $q->whereHas('client', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        })->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $clients = Client::all();
        $products = Product::all();
        return view('sales.create', compact('clients', 'products'));
    }

    public function downloadPdf(Sale $sale)
    {
        $sale->load('client', 'user', 'items.product', 'installments');
        $pdf = Pdf::loadView('sales.pdf', compact('sale'));
        return $pdf->download("venda_{$sale->id}.pdf");
    }

    public function store(Request $request)
    {
        DB::beginTransaction();


        // Calcular total
        $total = 0;
        foreach ($request->products as $product) {
            $total += $product['quantity'] * $product['price'];
        }

        // dd($request->products);
        // dd($request->installments);

        // Criar a venda
        $sale = Sale::create([
            'user_id' => Auth::id(),
            'client_id' => $request->client_id,
            'payment_method' => $request->payment_method,
            'total_amount' => $total,
        ]);


        // dd($sale);

        // Inserir itens da venda
        foreach ($request->products as $product) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            ]);
        }

        // Inserir parcelas
        foreach ($request->installments as $installment) {
            Installment::create([
                'sale_id' => $sale->id,
                'due_date' => $installment['due_date'],
                'amount' => $installment['amount'],
            ]);
        }

        DB::commit();
        return redirect()->route('sales.index')->with('success', 'Venda salva com sucesso!');
    }




    public function destroy(Sale $sale)
    {
        $sale->delete();
        return back()->with('success', 'Venda excluída.');
    }

    public function show(Sale $sale)
    {
        $sale->load('client', 'user', 'items.product', 'installments');
        return view('sales.show', compact('sale'));
    }

    public function edit($id)
    {
        $sale = Sale::with('items.product', 'installments')->findOrFail($id);
        $clients = Client::all();
        $products = Product::all();

        // Garantir que as datas das parcelas são objetos Carbon
        $sale->installments->transform(function ($installment) {
            $installment->due_date = \Carbon\Carbon::parse($installment->due_date);
            return $installment;
        });

        $existingProducts = $sale->items->map(function ($item) {
            return [
                'id' => $item->product->id,
                'pivot' => [
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ],
            ];
        });

        return view('sales.edit', compact('sale', 'clients', 'products', 'existingProducts'));
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $sale = Sale::findOrFail($id);

        // Calcular total atualizado
        $total = 0;
        foreach ($request->products as $product) {
            $total += $product['quantity'] * $product['price'];
        }

        // Atualizar dados da venda
        $sale->update([
            'client_id' => $request->client_id,
            'payment_method' => $request->payment_method,
            'total_amount' => $total,
        ]);

        // Apagar os itens e parcelas antigos para inserir os novos (simplificação)
        $sale->items()->delete();
        $sale->installments()->delete();

        // Inserir itens atualizados
        foreach ($request->products as $product) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            ]);
        }

        // Inserir parcelas atualizadas
        foreach ($request->installments as $installment) {
            Installment::create([
                'sale_id' => $sale->id,
                'due_date' => $installment['due_date'],
                'amount' => $installment['amount'],
            ]);
        }

        DB::commit();

        return redirect()->route('sales.index')->with('success', 'Venda atualizada com sucesso!');
    }
}

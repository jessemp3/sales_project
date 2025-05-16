@extends('layouts.app')
@section('content')
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl p-8">
            <h1 class="text-2xl font-semibold text-center text-blue-600 mb-8">Nova Venda</h1>

            <form method="POST" action="{{ route('sales.store') }}" onsubmit="return validateSale()">
                @csrf

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-gray-700">Cliente</label>
                    <select name="client_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required>
                        <option value="">Selecione um cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-gray-700">Forma de Pagamento</label>
                    <select name="payment_method"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required>
                        <option value="">Selecione uma forma de pagamento</option>
                        <option value="Crédito">Crédito</option>
                        <option value="Boleto">Boleto</option>
                    </select>
                </div>

                <h4 class="text-xl font-semibold text-blue-600 mb-3">Produtos</h4>
                <table class="min-w-full border border-gray-300 mb-3">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">Produto</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Quantidade</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Valor</th>
                            <th class="border border-gray-300 px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody id="products-table" class="text-gray-700"></tbody>
                </table>
                <button type="button" onclick="addProductRow()"
                    class="mb-6 px-4 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 transition">
                    + Adicionar Produto
                </button>

                <h4 class="text-xl font-semibold text-blue-600 mb-3">Parcelas</h4>
                <table class="min-w-full border border-gray-300 mb-3">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">Data de Vencimento</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Valor</th>
                            <th class="border border-gray-300 px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody id="installments-table" class="text-gray-700"></tbody>
                </table>
                <button type="button" onclick="addInstallmentRow()"
                    class="mb-8 px-4 py-2 border border-gray-600 text-gray-600 rounded hover:bg-gray-100 transition">
                    + Adicionar Parcela
                </button>

                <div class="text-center">
                    <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">
                        Salvar Venda
                    </button>
                </div>
                <div class="text-right mt-4">
                    <a href="{{ route('sales.index') }}"
                        class="inline-block px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const products = @json($products);
        let productIndex = 0;

        function addProductRow() {
            const row = `
            <tr>
                <td class="border border-gray-300 px-3 py-2">
                    <select name="products[${productIndex}][id]" class="w-full border border-gray-300 rounded px-2 py-1" onchange="updateProductPrice(this)">
                        ${products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('')}
                    </select>
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="number" name="products[${productIndex}][quantity]" class="w-full border border-gray-300 rounded px-2 py-1" value="1" min="1" oninput="updateProductPriceFromInput(this)">
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="text" name="products[${productIndex}][price]" class="w-full border border-gray-300 rounded px-2 py-1 price-field" readonly>
                </td>
                <td class="border border-gray-300 px-3 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 font-bold" onclick="removeRow(this)">X</button>
                </td>
            </tr>`;
            document.querySelector('#products-table').insertAdjacentHTML('beforeend', row);
            updateLastPriceField();
            productIndex++;
        }

        function updateProductPrice(select) {
            const row = select.closest('tr');
            const quantityInput = row.querySelector('input[name*="[quantity]"]');
            const priceField = row.querySelector('.price-field');
            const unitPrice = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 0;
            const quantity = parseInt(quantityInput.value) || 1;
            priceField.value = (unitPrice * quantity).toFixed(2);
            calculateInstallments();
        }

        function updateProductPriceFromInput(input) {
            const row = input.closest('tr');
            const select = row.querySelector('select');
            const quantity = parseFloat(input.value) || 1;
            const unitPrice = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 0;
            row.querySelector('.price-field').value = (quantity * unitPrice).toFixed(2);
            calculateInstallments();
        }


        function addInstallmentRow() {
            const table = document.querySelector('#installments-table');
            const rowCount = table.querySelectorAll('tr').length;

            let baseDate = new Date();
            const firstRowDate = table.querySelector('tr:first-child input[type="date"]');
            if (firstRowDate && firstRowDate.value) {
                baseDate = new Date(firstRowDate.value);
            }

            const nextDate = new Date(baseDate);
            nextDate.setMonth(nextDate.getMonth() + rowCount);

            const yyyy = nextDate.getFullYear();
            const mm = String(nextDate.getMonth() + 1).padStart(2, '0');
            const dd = String(nextDate.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;

            const index = rowCount;

            const row = `
    <tr>
        <td class="border border-gray-300 px-3 py-2">
            <input class="w-full border border-gray-300 rounded px-2 py-1" type="date" name="installments[${index}][due_date]" value="${formattedDate}" required>
        </td>
        <td class="border border-gray-300 px-3 py-2">
            <input class="w-full border border-gray-300 rounded px-2 py-1 installment-value" type="number" step="0.01" name="installments[${index}][amount]" required>
        </td>
        <td class="border border-gray-300 px-3 py-2 text-center">
            <button type="button" class="text-red-600 hover:text-red-800 font-bold" onclick="removeRow(this); recalculateInstallments()">X</button>
        </td>
    </tr>`;

            table.insertAdjacentHTML('beforeend', row);
            recalculateInstallments();
        }



        function recalculateInstallments() {
            const total = getTotalFromProducts();
            const rows = document.querySelectorAll('#installments-table tr');
            const count = rows.length;

            if (count === 0) return;

            const baseAmount = Math.floor((total / count) * 100) / 100;
            let remaining = (total - baseAmount * (count - 1)).toFixed(2);

            rows.forEach((row, index) => {
                const input = row.querySelector('.installment-value');
                input.value = index === count - 1 ? remaining : baseAmount.toFixed(2);
            });

            calculateInstallments();
        }

        function getTotalFromProducts() {
            let total = 0;
            document.querySelectorAll('#products-table tr').forEach(row => {
                const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
                total += price;
            });
            return total;
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateInstallments();
            recalculateInstallments();
        }

        function updateLastPriceField() {
            const rows = document.querySelectorAll('#products-table tr');
            if (rows.length === 0) return;
            const lastRow = rows[rows.length - 1];
            const select = lastRow.querySelector('select');
            const priceField = lastRow.querySelector('.price-field');
            const selectedOption = select.options[select.selectedIndex];
            priceField.value = parseFloat(selectedOption.getAttribute('data-price')).toFixed(2);
            calculateInstallments();
        }

        function validateSale() {
            let totalProdutos = 0;
            document.querySelectorAll('#products-table tr').forEach(row => {
                const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
                totalProdutos += price;
            });

            let totalParcelas = 0;
            document.querySelectorAll('#installments-table tr').forEach(row => {
                const amount = parseFloat(row.querySelector('input[name*="[amount]"]').value) || 0;
                totalParcelas += amount;
            });

            const diff = Math.abs(totalProdutos - totalParcelas);
            if (diff > 0.01) {
                alert(
                    `A soma das parcelas (R$ ${totalParcelas.toFixed(2)}) deve ser igual ao total dos produtos (R$ ${totalProdutos.toFixed(2)})`);
                return false;
            }
            return true;
        }

        function calculateInstallments() {
            const total = getTotalFromProducts();
            const rows = document.querySelectorAll('#installments-table tr');
            const count = rows.length;

            if (count === 0 || total === 0) return;

            const base = total / count;
            const baseRounded = Math.floor(base * 100) / 100;
            const remainder = (total - baseRounded * count).toFixed(2);

            rows.forEach((row, index) => {
                const input = row.querySelector('input[name*="[amount]"]');
                input.value = index === 0 ? (baseRounded + parseFloat(remainder)).toFixed(2) : baseRounded.toFixed(
                    2);
            });
        }
    </script>
@endsection

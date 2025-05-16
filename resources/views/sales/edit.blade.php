{{-- @extends('layouts.app')
@section('content')
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl p-8">
            <h1 class="text-2xl font-semibold text-center text-blue-600 mb-8">Editar Venda #{{ $sale->id }}</h1>

            <form method="POST" action="{{ route('sales.update', $sale->id) }}" onsubmit="return validateSale()">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-gray-700">Cliente</label>
                    <select name="client_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required>
                        <option value="">Selecione um cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @if ($client->id == $sale->client_id) selected @endif>
                                {{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-gray-700">Forma de Pagamento</label>
                    <select name="payment_method"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required>
                        <option value="">Selecione uma forma de pagamento</option>
                        <option value="Crédito" @if ($sale->payment_method == 'Crédito') selected @endif>Crédito</option>
                        <option value="Boleto" @if ($sale->payment_method == 'Boleto') selected @endif>Boleto</option>
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
                        Atualizar Venda
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

        const existingProducts = @json($existingProducts);

        function addProductRow(product = null) {
            const idx = productIndex++;
            const productOptions = products.map(p => {
                const selected = product && p.id === product.id ? 'selected' : '';
                return `<option value="${p.id}" data-price="${p.price}" ${selected}>${p.name}</option>`;
            }).join('');

            const quantityValue = product ? product.pivot.quantity : 1;

            // Corrige o acesso ao preço:
            // Se existir price no pivot e for número, usa ele, senão pega do produto principal
            let priceRaw = 0;
            if (product && product.pivot && product.pivot.price != null && !isNaN(parseFloat(product.pivot.price))) {
                priceRaw = parseFloat(product.pivot.price);
            } else if (product) {
                priceRaw = parseFloat(product.price);
            } else if (products.length) {
                priceRaw = parseFloat(products[0].price);
            }
            const priceValue = priceRaw.toFixed(2);

            const row = `
        <tr>
            <td class="border border-gray-300 px-3 py-2">
                <select name="products[${idx}][id]" class="w-full border border-gray-300 rounded px-2 py-1" onchange="updateProductPrice(this)">
                    ${productOptions}
                </select>
            </td>
            <td class="border border-gray-300 px-3 py-2">
                <input type="number" name="products[${idx}][quantity]" class="w-full border border-gray-300 rounded px-2 py-1" value="${quantityValue}" min="1" oninput="updateProductPriceFromInput(this)">
            </td>
            <td class="border border-gray-300 px-3 py-2">
                <input type="text" name="products[${idx}][price]" class="w-full border border-gray-300 rounded px-2 py-1 price-field" readonly value="${priceValue}">
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center">
                <button type="button" class="text-red-600 hover:text-red-800 font-bold" onclick="removeRow(this)">X</button>
            </td>
        </tr>
    `;
            document.querySelector('#products-table').insertAdjacentHTML('beforeend', row);
            calculateInstallments();
        }

        // Carrega as parcelas já existentes no formulário
        const existingInstallments = @json(
            $sale->installments->map(function ($i) {
                return [
                    'due_date' => $i->due_date->format('Y-m-d'),
                    'amount' => $i->amount,
                ];
            }));

        function addInstallmentRow(installment = null) {
            const table = document.querySelector('#installments-table');
            const rowCount = table.querySelectorAll('tr').length;
            const index = rowCount;

            let dueDate = '';
            let amount = '';

            if (installment) {
                dueDate = installment.due_date;
                amount = parseFloat(installment.amount).toFixed(2);
            } else {
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
                dueDate = `${yyyy}-${mm}-${dd}`;
                amount = '';
            }

            const row = `
    <tr>
        <td class="border border-gray-300 px-3 py-2">
            <input class="w-full border border-gray-300 rounded px-2 py-1" 
                   type="date" 
                   name="installments[${index}][due_date]" 
                   value="${dueDate}" 
                   required>
        </td>
        <td class="border border-gray-300 px-3 py-2">
            <input class="w-full border border-gray-300 rounded px-2 py-1 installment-value" 
                   type="number" step="0.01" 
                   name="installments[${index}][amount]" 
                   value="${amount}" 
                   required>
        </td>
        <td class="border border-gray-300 px-3 py-2 text-center">
            <button type="button" class="text-red-600 hover:text-red-800 font-bold" onclick="removeRow(this); recalculateInstallments()">X</button>
        </td>
    </tr>
`;

            table.insertAdjacentHTML('beforeend', row);
            recalculateInstallments();
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

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateInstallments();
            recalculateInstallments();
        }

        function calculateInstallments() {
            // Atualiza valor total para facilitar cálculo de parcelas
            let total = 0;
            document.querySelectorAll('#products-table .price-field').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            // Atualizar parcelas se quiser implementar automática
        }

        function recalculateInstallments() {
            // Pode ser implementado para checar soma das parcelas e comparar com valor total
        }

        function validateSale() {
            const productsCount = document.querySelectorAll('#products-table tr').length;
            const installmentsCount = document.querySelectorAll('#installments-table tr').length;
            if (productsCount === 0) {
                alert('Adicione pelo menos um produto.');
                return false;
            }
            if (installmentsCount === 0) {
                alert('Adicione pelo menos uma parcela.');
                return false;
            }
            return true;
        }

        // Inicializa as linhas já existentes na edição
        window.onload = function() {
            // Preenche produtos
            if (existingProducts.length > 0) {
                existingProducts.forEach(prod => addProductRow(prod));
            } else {
                addProductRow();
            }

            // Preenche parcelas
            if (existingInstallments.length > 0) {
                existingInstallments.forEach(inst => addInstallmentRow(inst));
            } else {
                addInstallmentRow();
            }
        };
    </script>
@endsection --}}


@extends('layouts.app')
@section('content')
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="bg-white shadow-lg rounded-lg w-full max-w-3xl p-8">
            <h1 class="text-2xl font-semibold text-center text-blue-600 mb-8">Editar Venda #{{ $sale->id }}</h1>

            <form method="POST" action="{{ route('sales.update', $sale->id) }}" onsubmit="return validateSale()">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-gray-700">Cliente</label>
                    <select name="client_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required>
                        <option value="">Selecione um cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @if ($client->id == $sale->client_id) selected @endif>
                                {{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block mb-1 font-semibold text-gray-700">Forma de Pagamento</label>
                    <select name="payment_method"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        required>
                        <option value="">Selecione uma forma de pagamento</option>
                        <option value="Crédito" @if ($sale->payment_method == 'Crédito') selected @endif>Crédito</option>
                        <option value="Boleto" @if ($sale->payment_method == 'Boleto') selected @endif>Boleto</option>
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
                        Atualizar Venda
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

        const existingProducts = @json($existingProducts);

        function addProductRow(product = null) {
            const idx = productIndex++;
            const productOptions = products.map(p => {
                const selected = product && p.id === product.id ? 'selected' : '';
                return `<option value="${p.id}" data-price="${p.price}" ${selected}>${p.name}</option>`;
            }).join('');

            const quantityValue = product ? product.pivot.quantity : 1;

            let priceRaw = 0;
            if (product && product.pivot && product.pivot.price != null && !isNaN(parseFloat(product.pivot.price))) {
                priceRaw = parseFloat(product.pivot.price);
            } else if (product) {
                priceRaw = parseFloat(product.price);
            } else if (products.length) {
                priceRaw = parseFloat(products[0].price);
            }
            const priceValue = priceRaw.toFixed(2);

            const row = `
        <tr>
            <td class="border border-gray-300 px-3 py-2">
                <select name="products[${idx}][id]" class="w-full border border-gray-300 rounded px-2 py-1" onchange="updateProductPrice(this)">
                    ${productOptions}
                </select>
            </td>
            <td class="border border-gray-300 px-3 py-2">
                <input type="number" name="products[${idx}][quantity]" class="w-full border border-gray-300 rounded px-2 py-1" value="${quantityValue}" min="1" oninput="updateProductPriceFromInput(this)">
            </td>
            <td class="border border-gray-300 px-3 py-2">
                <input type="text" name="products[${idx}][price]" class="w-full border border-gray-300 rounded px-2 py-1 price-field" readonly value="${priceValue}">
            </td>
            <td class="border border-gray-300 px-3 py-2 text-center">
                <button type="button" class="text-red-600 hover:text-red-800 font-bold" onclick="removeRow(this)">X</button>
            </td>
        </tr>
    `;
            document.querySelector('#products-table').insertAdjacentHTML('beforeend', row);
            calculateInstallments();
        }

        const existingInstallments = @json(
            $sale->installments->map(function ($i) {
                return [
                    'due_date' => $i->due_date->format('Y-m-d'),
                    'amount' => $i->amount,
                ];
            }));

        function addInstallmentRow(installment = null) {
            const table = document.querySelector('#installments-table');
            const rowCount = table.querySelectorAll('tr').length;
            const index = rowCount;

            let dueDate = '';
            let amount = '';

            if (installment) {
                dueDate = installment.due_date;
                amount = parseFloat(installment.amount).toFixed(2);
            } else {
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
                dueDate = `${yyyy}-${mm}-${dd}`;
                amount = '';
            }

            const row = `
    <tr>
        <td class="border border-gray-300 px-3 py-2">
            <input class="w-full border border-gray-300 rounded px-2 py-1" 
                   type="date" 
                   name="installments[${index}][due_date]" 
                   value="${dueDate}" 
                   required>
        </td>
        <td class="border border-gray-300 px-3 py-2">
            <input class="w-full border border-gray-300 rounded px-2 py-1 installment-value" 
                   type="number" step="0.01" 
                   name="installments[${index}][amount]" 
                   value="${amount}" 
                   required>
        </td>
        <td class="border border-gray-300 px-3 py-2 text-center">
            <button type="button" class="text-red-600 hover:text-red-800 font-bold" onclick="removeRow(this); recalculateInstallments()">X</button>
        </td>
    </tr>
`;

            table.insertAdjacentHTML('beforeend', row);
            recalculateInstallments();
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

        function removeRow(btn) {
            btn.closest('tr').remove();
            calculateInstallments();
            recalculateInstallments();
        }

        function calculateInstallments() {
            // Calcula o total dos produtos
            let total = 0;
            document.querySelectorAll('#products-table .price-field').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            // Pega as parcelas
            const installmentInputs = document.querySelectorAll('#installments-table .installment-value');
            const count = installmentInputs.length;

            if (count === 0) return; // Nenhuma parcela, nada a fazer

            // Calcula valor igual para cada parcela (divisão simples)
            const valuePerInstallment = (total / count);

            // Distribui o valor para as parcelas, corrigindo arredondamento na última parcela
            let accumulated = 0;
            installmentInputs.forEach((input, index) => {
                if (index === count - 1) {
                    // Última parcela pega o que resta para evitar erro de centavos
                    input.value = (total - accumulated).toFixed(2);
                } else {
                    input.value = valuePerInstallment.toFixed(2);
                    accumulated += valuePerInstallment;
                }
            });
        }

        function recalculateInstallments() {
            // Aqui você pode adicionar validação entre soma parcelas e total se quiser
            // Ou deixar vazio se não precisar
        }

        function validateSale() {
            const productsCount = document.querySelectorAll('#products-table tr').length;
            const installmentsCount = document.querySelectorAll('#installments-table tr').length;
            if (productsCount === 0) {
                alert('Adicione pelo menos um produto.');
                return false;
            }
            if (installmentsCount === 0) {
                alert('Adicione pelo menos uma parcela.');
                return false;
            }
            return true;
        }

        window.onload = function() {
            // Preenche produtos existentes
            if (existingProducts.length > 0) {
                existingProducts.forEach(prod => addProductRow(prod));
            } else {
                addProductRow();
            }

            // Preenche parcelas existentes
            if (existingInstallments.length > 0) {
                existingInstallments.forEach(inst => addInstallmentRow(inst));
            } else {
                addInstallmentRow();
            }

            // Atualiza as parcelas com base nos produtos carregados
            calculateInstallments();
        };
    </script>
@endsection


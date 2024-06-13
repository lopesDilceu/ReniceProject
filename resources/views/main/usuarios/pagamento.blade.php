@extends('layouts.frame')

@section('titulo', 'Pagamento')

@section('content')
    <h1 class="h1 mb-4">Pagamento</h1>

    <!-- Tabela de Dados da Compra -->
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped ">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Preço Unitário</th>
                    <th scope="col">Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Linha da Compra - Produto 1 -->
                <tr>
                    <td>Produto 1</td>
                    <td>2</td>
                    <td>R$ 10,00</td>
                    <td>R$ 20,00</td>
                </tr>
                <!-- Linha da Compra - Produto 2 -->
                <tr>
                    <td>Produto 2</td>
                    <td>1</td>
                    <td>R$ 15,00</td>
                    <td>R$ 15,00</td>
                </tr>
                <!-- Adicione mais linhas conforme necessário para outros produtos -->
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <td>R$ 35,00</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Botão de Finalizar Compra -->
    <div class="text-end mt-3">
        <a href="{{ route('usuarios.carrinho')}}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        <button class="btn btn-lg btn-outline-success">Finalizar Compra</button>
    </div>
@endsection

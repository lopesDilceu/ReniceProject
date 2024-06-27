@extends('layouts.frame')

@section('titulo', 'Pagamento')

@section('content')
    <h1 class="h1 mb-4">Pagamento</h1>
    <!-- Tabela de Carrinho -->
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped ">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Preço Unitário</th>
                    <th scope="col">Total</th>
                </tr>
            </thead>
            <tbody >
                @forelse($itenscarrinho as $itemcarrinho)
                <tr>
                    <td>
                        @foreach ($produtos as $produto)
                            @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                <img src="{{ asset($produto->pr_foto) }}" alt="" class="rounded-circle" width="60" height="60"> 
                                @break
                            @endif
                        @endforeach
                        @foreach ($produtos as $produto)
                            @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                {{ $produto->pr_nome }}
                                @break
                            @endif
                        @endforeach
                    </td>
                    <td>{{ $itemcarrinho->ic_quantidade }}</td>
                    <td>R$@foreach ($produtos as $produto)
                            @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                {{ $produto->pr_preco }}
                                @break
                            @endif
                        @endforeach
                    </td>
                    <td>
                        R$ {{ number_format($itemcarrinho->ic_quantidade * $produto->pr_preco, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">Não há itens no carrinho.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="1">Total</th>
                    <td>
                        {{-- Calcular o total da quantidade --}}
                        @php
                            $totalQuantidade = 0;
                            $totalValor = 0;
                        @endphp

                        @foreach($itenscarrinho as $itemcarrinho)
                            @foreach ($produtos as $produto)
                                @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                    @php
                                        $totalQuantidade += $itemcarrinho->ic_quantidade;
                                        $totalValor += $itemcarrinho->ic_quantidade * $produto->pr_preco;
                                    @endphp
                                @endif
                            @endforeach
                        @endforeach

                        {{-- Exibir o total da quantidade --}}
                        {{ $totalQuantidade }}
                    </td>
                    <th colspan="1">-</th>
                    <td>
                        {{-- Exibir o total do valor formatado --}}
                        R$ {{ number_format($totalValor, 2, ',', '.') }}
                    </td>
                    <th ></th>
                </tr>
            </tfoot>

        </table>
    </div>

    <!-- Botão de Pagamento -->
    <div class="text-end mt-3">
    <a href="{{ route('usuario.carrinho')}}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    <a href="{{ route('usuario.finalizar', $carrinho->ca_id) }}" class="btn btn-lg btn-outline-dark">Finalizar Compra</a>
</div>
@endsection

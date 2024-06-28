@extends('layouts.frame')

@section('titulo', 'Pagamento')

@section('content')
<h1 class="h1 mb-4">PAGAMENTO</h1>
<!-- Tabela de Carrinho -->
<div class="row mb-4">
    <div class="table-responsive border  rounded col-md-8" style="height: 680px; overflow-y: auto;">
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
                @forelse($itenscarrinho as $itemcarrinho)
                    <tr>
                        <td class="align-middle">
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
                        <td class="align-middle">{{ $itemcarrinho->ic_quantidade }}</td>
                        <td class="align-middle">R$@foreach ($produtos as $produto)
                            @if ($produto->pr_id === $itemcarrinho->ic_id_produto)
                                {{ $produto->pr_preco }}
                                @break
                            @endif
                        @endforeach
                        </td>
                        <td class="align-middle">
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
                    <td class="align-middle">
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
                    <td class="align-middle">
                        {{-- Exibir o total do valor formatado --}}
                        R$ {{ number_format($totalValor, 2, ',', '.') }}
                    </td>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- Botão de Pagamento -->
    <div class="text-end col-md-4">
    <div class="mb-2 border rounded p-3">
        <h2 class="h4 mb-3 text-center">Endereço de entrega</h2>
        <div class="row mb-2">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <label for="rua" class="form-label text-start"><b>Rua:</b></label>
                <span class="flex-grow-1 mx-2 border-bottom "></span>
                <p id="rua" class="text-end">{{ $endereco->en_logradouro }}</p>
            </div>
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <label for="numero" class="form-label text-start"><b>Número:</b></label>
                <span class="flex-grow-1 mx-2 border-bottom "></span>
                <p id="numero" class="text-end">{{ $endereco->en_numero }}</p>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <label for="bairro" class="form-label text-start"><b>Bairro:</b></label>
                <span class="flex-grow-1 mx-2 border-bottom "></span>
                <p id="bairro" class="text-end">{{ $endereco->en_bairro }}</p>
            </div>
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <label for="cidade" class="form-label text-start"><b>Cidade:</b></label>
                <span class="flex-grow-1 mx-2 border-bottom "></span>
                <p id="cidade" class="text-end">{{ $endereco->en_cidade }}</p>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <label for="estado" class="form-label text-start"><b>Estado:</b></label>
                <span class="flex-grow-1 mx-2 border-bottom "></span>
                <p id="estado" class="text-end">{{ $endereco->en_estado }}</p>
            </div>
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <label for="cep" class="form-label text-start"><b>CEP:</b></label>
                <span class="flex-grow-1 mx-2 border-bottom "></span>
                <p id="cep" class="text-end">{{ formatar_cep($endereco->en_cep) }}</p>
            </div>
        </div>
    </div>
    <div class="text-center" style="height: 330px;">
        <a href="{{ route('usuario.carrinho')}}" class="btn btn-outline-secondary "><i class="bi bi-arrow-left"></i>
            Voltar</a>
        <a href="{{ route('usuario.finalizar', $carrinho->ca_id) }}" class="btn btn-lg btn-outline-dark">Finalizar
            Compra</a>
    </div>
</div>
@endsection
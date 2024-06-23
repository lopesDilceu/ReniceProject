@section('titulo', 'Compras')

@extends('layouts.frame')
@section('content')
@include('layouts.components.nova-compra-modal')

<div class="text-center">
    <h1 class="h1 mb-4">COMPRAS</h1>
</div>

<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{route('adm.home')}}" class="btn btn-outline-secondary">Voltar</a>
        <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#nova-compra-modal">Nova Compra</button>
    </div>

    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Cód. Produto</th>
                    <th scope="col">Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Fornecedor</th>
                    <th scope="col">Preço uni.</th>
                    <th scope="col">Data</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $compra)
                <tr>
                    <td>#0{{ $compra->co_id }}</td>
                    <td>#0{{ $compra->co_id_produto }}</td>
                    <td>
                        @foreach ($produtos as $produto)
                            @if ($produto->pr_id === $compra->co_id_produto)
                                {{ $produto->pr_nome }}
                                @break
                            @endif
                        @endforeach
                    </td>
                    <td>{{ $compra->co_quantidade }}</td>
                    <td>{{ $compra->co_fornecedor }}</td>
                    <td>R${{ $compra->co_preco_unitario }}</td>
                    <td>{{ $compra->created_at }}</td>
                    <td>
                    <form action="{{ route('adm.compras.destroy', $compra->co_id) }}" method="post" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-light mb-1 mb-md-0" onclick="return confirm('Tem certeza que deseja excluir?')"><img src="{{asset('/images/icons/deletar.png')}}" alt="Editar" width="24px" height="24px"></button>
                    </form>


                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

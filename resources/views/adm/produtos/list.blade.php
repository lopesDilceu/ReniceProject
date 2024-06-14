@section('titulo', 'Administração')

@extends('layouts.frame')
@section('content')
@include('layouts.components.novo-produto-modal')
@include('layouts.components.produto-modal')
@include('layouts.components.edit-produto-modal')
<div class="text-center">
    <h1 class="h1 mb-4">PRODUTOS</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{route('adm.home')}}" class="btn btn-outline-secondary">Voltar</a>
        <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#novo-produto-modal">Novo Produto</button>
    </div>
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código do Produto</th>
                    <th scope="col">Nome do Produto</th>
                    <th scope="col">Preço</th>
                    <th scope="col">Data de Cadastro</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produtos as $produto)
                <tr>
                    <td>00{{ $produto->pr_id }}</td>
                    <td>{{ $produto->pr_nome }}</td>
                    <td>R${{ $produto->pr_preco }}</td>
                    <td>{{ $produto->pr_data_criacao }}</td>
                    <td>
                    <button class="btn btn-secondary" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal">Ver</button>
                        <a href="#" class="btn btn-dark ">Editar</a>
                    <form action="{{ route('adm.produtos.destroy', $produto->pr_id) }}" method="post" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mb-1 mb-md-0" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                    </form>


                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>



@endsection
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
                    <button class="btn btn-secondary ver-produto" data-bs-toggle="modal"
                            data-bs-target="#produto-modal" data-id="{{$produto->pr_id}}">Ver</button>
                    <button class="btn btn-dark edit-produto" data-bs-toggle="modal"
                        data-bs-target="#edit-produto-modal" data-id="{{$produto->pr_id}}">Editar</button>
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

@push('script')
    <script>
        $('.edit-produto').on('click', function() {
            const pr_id = $(this).data('id');

            const produtos = @json($produtos);

            let produto = produtos.filter(produto => produto.pr_id === pr_id)[0];

            let modal = $('#edit-produto-modal');

            let pr_nome = modal.find('#editNomeProduto').val(produto.pr_nome);

            let pr_descricao = modal.find('#editDescricaoProduto').val(produto.pr_descricao);

            let pr_preco = modal.find('#editPrecoProduto').val(produto.pr_preco);

            let form = modal.find('form').attr('action', '{{ route('adm.produto.edit') }}/' + pr_id);
        });

        $('.ver-produto').on('click', function() {
            const pr_id = $(this).data('id');

            const produtos = @json($produtos);

            let produto = produtos.filter(produto => produto.pr_id === pr_id)[0];

            let modal = $('#produto-modal');

            let pr_nome = modal.find('#NomeProduto').val(produto.pr_nome);

            let pr_descricao = modal.find('#DescricaoProduto').val(produto.pr_descricao);

            let pr_preco = modal.find('#PrecoProduto').val(produto.pr_preco);

        });

    </script>
@endpush

@endsection
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
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
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
                    <td>R${{number_format($produto->pr_preco, 2, ',', '.')}}</td>
                    <td>{{ $produto->pr_data_criacao }}</td>
                    <td>
                    <button class="btn btn-outline-light ver-produto" data-bs-toggle="modal"
                            data-bs-target="#produto-modal" data-id="{{$produto->pr_id}}"><img src="{{asset('/images/icons/ver.png')}}" alt="Editar" width="24px" height="24px"></button>
                    <button class="btn btn-outline-light edit-produto" data-bs-toggle="modal"
                        data-bs-target="#edit-produto-modal" data-id="{{$produto->pr_id}}"><img src="{{asset('/images/icons/editar.png')}}" alt="Editar" width="24px" height="24px"></button>
                    <form action="{{ route('adm.produtos.destroy', $produto->pr_id) }}" method="post" style="display: inline;">
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
</div>

@push('script')
    <script>
        $('.edit-produto').on('click', function() {
            const pr_id = $(this).data('id');

            const produtos = @json($produtos);

            let produto = produtos.filter(produto => produto.pr_id === pr_id)[0];

            let modal = $('#edit-produto-modal');

            let pr_nome = modal.find('#editNomeProduto').val(produto.pr_nome);

            //modal.find('.modal-body img').attr('src', '{{asset('')}}' + produto.pr_foto);

            let pr_descricao = modal.find('#editDescricaoProduto').val(produto.pr_descricao);

            let pr_preco = modal.find('#editPrecoProduto').val(produto.pr_preco);

            let form = modal.find('form').attr('action', '{{ route('adm.produto.edit') }}/' + pr_id);
        });

        $('.ver-produto').on('click', function() {
            const pr_id = $(this).data('id');

            // Obtém a lista de produtos do PHP convertida para JavaScript
            const produtos = @json($produtos);

            // Filtra o produto com base no pr_id
            let produto = produtos.find(produto => produto.pr_id === pr_id);

            // Seleciona o modal e atualiza os spans com os valores do produto
            let modal = $('#produto-modal');

            // Atualiza o nome do produto
            modal.find('#NomeProduto').text(produto.pr_nome);

            modal.find('.modal-body img').attr('src', '{{asset('')}}' + produto.pr_foto);
    
            modal.find('.modal-body img').attr('alt', produto.pr_nome);

            // Atualiza a descrição do produto
            modal.find('#DescricaoProduto').text(produto.pr_descricao);

            // Atualiza o preço do produto
            modal.find('#PrecoProduto').text(produto.pr_preco);

            // Abre o modal
            modal.modal('show');
        });

    </script>
@endpush

@endsection
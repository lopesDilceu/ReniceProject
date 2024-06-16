@section('titulo', 'Administração')

@extends('layouts.frame')
@section('content')
@include('layouts.components.avaliacao-modal')
<div class="text-center">
    <h1 class="h1 mb-4">AVALIAÇÕES</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{route('adm.home')}}" class="btn btn-outline-secondary">Voltar</a>
    </div>
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Usuário</th>
                    <th scope="col">Produto</th>
                    <th scope="col">Nota</th>
                    <th scope="col">Data</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avaliacoes as $avaliacao)
                    <tr>
                        <td>
                            @foreach ($users as $user)
                                @if ($user->us_id === $avaliacao->av_id_usuario)
                                    {{ $user->name }}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach ($produtos as $produto)
                                @if ($produto->pr_id === $avaliacao->av_id_produto)
                                    {{ $produto->pr_nome }}
                                    @break
                                @endif
                            @endforeach
                        </td>
                        <td>{{ $avaliacao->av_nota }}/5</td>
                        <td>{{ $avaliacao->av_data_avaliacao }}</td>
                        <td>
                            <form action="{{ route('adm.produtos.destroy', $avaliacao->av_id) }}" method="post" style="display: inline;">
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



@endsection
@section('titulo', 'Usuários')


@extends('layouts.frame')
@section('content')
@include('layouts.components.dados-usuario-modal')
@include('layouts.components.edit-usuario-modal')
<div class="text-center">
    <h1 class="h1 mb-4">USUÁRIOS</h1>
</div>
<div class="container mb-4">
    <div class="d-flex justify-content-between mb-3">
        <a href="{{route('adm.home')}}" class="btn btn-outline-secondary">Voltar</a>
    </div>
    <div class="table-responsive" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped ">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Código</th>
                    <th scope="col">Nome</th>
                    <th scope="col">CPF</th>
                    <th scope="col">E-mail</th>
                    <th scope="col" class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>00{{ $user->us_id }}</td>
                    <td>{{ $user->name}}</td>
                    <td>{{ $user->us_cpf }}</td>
                    <td>{{ $user->email }}</td>
                    <td class="text-center">
                    <button class="btn btn-secondary ver-usuario" data-bs-toggle="modal"
                            data-bs-target="#dados-usuario-modal" data-id="{{$user->us_id}}"><img src="{{asset('/images/icons/ver.png')}}" alt="Editar" width="24px" height="24px"></button>
                    <button class="btn btn-dark edit-usuario" data-bs-toggle="modal"
                        data-bs-target="#edit-usuario-modal" data-id="{{$user->us_id}}"><img src="{{asset('/images/icons/editar.png')}}" alt="Editar" width="24px" height="24px"></button>
                    <form action="{{ route('adm.usuarios.destroy', $user->us_id) }}" method="post" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mb-1 mb-md-0" onclick="return confirm('Tem certeza que deseja excluir?')"><img src="{{asset('/images/icons/deletar.png')}}" alt="Editar" width="24px" height="24px"></button>
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
        $('.edit-usuario').on('click', function() {
            const us_id = $(this).data('id');


            const users = @json($users);

            let user = users.filter(user => user.us_id === us_id)[0];


            console.log(user)
   
            let modal = $('#edit-usuario-modal');

            modal.find('#CodigoUsuario').text(user.us_id);

            let name = modal.find('#editNomeUsuario').val(user.name);
            let us_cpf = modal.find('#editCpfUsuario').val(user.us_cpf);
            let us_data_nasc = modal.find('#editNascUsuario').val(user.us_data_nasc);
            let email = modal.find('#editEmailUsuario').val(user.email);
            let password = modal.find('#editSenhaUsuario').val(user.password);
            let us_adm = modal.find('#editAdmUsuario').val(user.us_adm);
            modal.find('#CriacaoUsuario').text(user.us_data_criacao);
            

            let form = modal.find('form').attr('action', '{{ route('adm.usuario.edit') }}/' + us_id);
        });

        $('.ver-usuario').on('click', function() {
            const us_id = $(this).data('id');

            // Obtém a lista de users do PHP convertida para JavaScript
            const users = @json($users);

            // Filtra o produto com base no us_id
            let user = users.find(user => user.us_id === us_id);

            // Seleciona o modal e atualiza os spans com os valores do produto
            let modal = $('#dados-usuario-modal');

            modal.find('#CodigoUsuario').text(user.us_id);
            modal.find('#NomeUsuario').text(user.name);
            modal.find('#CpfUsuario').text(user.us_cpf);
            modal.find('#NascimentoUsuario').text(user.us_data_nasc);
            modal.find('#EmailUsuario').text(user.email);
            modal.find('#SenhaUsuario').text(user.password);
            modal.find('#AdmUsuarioo').text(user.us_adm);
            modal.find('#CriacaoUsuario').text(user.us_data_criacao);

            modal.modal('show');
        });

    </script>
@endpush


@endsection
@extends('layouts.frame')

@section('titulo', 'Usuários')

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
    <div class="table-responsive border  rounded" style="height: 620px; overflow-y: auto;">
        <table class="table table-striped">
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
                    <td class="align-middle">00{{ $user->us_id }}</td>
                    <td class="align-middle">
                        @if (isset($user->us_foto))
                            <img src="{{ asset($user->us_foto) }}" alt="" class="rounded-circle" width="50" height="50">
                        @else
                            <img src="{{ asset('/images/perfil.png') }}" alt="" class="" width="50" height="50">
                        @endif
                        
                        {{ $user->name}}
                    </td>
                    <td class="align-middle">{{formatar_cpf($user->us_cpf)}}</td>
                    <td class="align-middle">{{ $user->email }}</td>
                    <td class="text-center align-middle">
                        <button class="btn btn-outline-light ver-usuario" data-bs-toggle="modal"
                                data-bs-target="#dados-usuario-modal" data-id="{{$user->us_id}}">
                            <img src="{{asset('/images/icons/ver.png')}}" alt="Ver" width="24px" height="24px">
                        </button>
                        <button class="btn btn-outline-light edit-usuario" data-bs-toggle="modal"
                                data-bs-target="#edit-usuario-modal" data-id="{{$user->us_id}}">
                            <img src="{{asset('/images/icons/editar.png')}}" alt="Editar" width="24px" height="24px">
                        </button>
                        <form action="{{ route('adm.usuarios.destroy', $user->us_id) }}" method="post" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-light mb-1 mb-md-0" onclick="return confirm('Tem certeza que deseja excluir?')">
                                <img src="{{asset('/images/icons/deletar.png')}}" alt="Deletar" width="24px" height="24px">
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum usuário encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('script')
<script>
    $(document).ready(function() {
        // Função para abrir o modal de edição e aplicar máscara de CPF
        $('.edit-usuario').on('click', function() {
            const us_id = $(this).data('id');
            const users = @json($users);
            let user = users.filter(user => user.us_id === us_id)[0];

            let modal = $('#edit-usuario-modal');

            modal.find('#CodigoUsuario').text(user.us_id);
            modal.find('#editNomeUsuario').val(user.name);
            modal.find('#editCpfUsuario').val(user.us_cpf).mask('000.000.000-00');
            modal.find('#editNascUsuario').val(user.us_data_nasc);
            modal.find('#editEmailUsuario').val(user.email);
            modal.find('#editAdmUsuario').val(user.us_adm);
            modal.find('#CriacaoUsuario').text(user.us_data_criacao);

            modal.find('form').attr('action', '{{ route('adm.usuario.edit') }}/' + us_id);

            modal.modal('show');
        });
        
        $('#formEditUser').submit(function() {
            $('#editCpfUsuario').unmask();
        });

        // Função para abrir o modal de visualização
        $('.ver-usuario').on('click', function() {
            const us_id = $(this).data('id');
            const users = @json($users);
            let user = users.find(user => user.us_id === us_id);

            let modal = $('#dados-usuario-modal');

            modal.find('#CodigoUsuario').text(user.us_id);
            modal.find('#NomeUsuario').text(user.name);
            modal.find('#CpfUsuario').text(user.us_cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')); // Aplica a máscara de CPF
            modal.find('#NascimentoUsuario').text(user.us_data_nasc);
            modal.find('#EmailUsuario').text(user.email);
            modal.find('#AdmUsuario').text(user.us_adm ? 'Sim' : 'Não');
            modal.find('#CriacaoUsuario').text(user.us_data_criacao);

            console.log(user.us_foto);
            if (user.us_foto) {
                modal.find('#UserProfileImage').attr('src', '{{ asset('') }}' + user.us_foto);
            } else {
                modal.find('#UserProfileImage').attr('src', '{{ asset('images/logo/renice-logo-down.png') }}');
            }

            modal.modal('show');
        });

    });
</script>
@endpush


@endsection

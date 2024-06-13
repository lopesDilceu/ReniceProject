@section('titulo', 'Avaliações')
@section('avaliacoes', 'active')
@extends('layouts.frame')

@section('content')
    <h1 class="h1 mb-4">AVALIAÇÕES</h1>
    <div class="row">
        <!-- Card de Avaliação 1 -->
        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Avaliação do Produto 1</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Nota: 4/5</h6>
                    <p class="card-text">Comentário: Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
            </div>
        </div>

        <!-- Card de Avaliação 2 -->
        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Avaliação do Produto 2</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Nota: 5/5</h6>
                    <p class="card-text">Comentário: Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                </div>
            </div>
        </div>

        <!-- Card de Avaliação 3 -->
        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Avaliação do Produto 3</h5>
                    <h6 class="card-subtitle mb-2 text-muted">Nota: 3/5</h6>
                    <p class="card-text">Comentário: Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                </div>
            </div>
        </div>
        <!-- Adicione mais cards conforme necessário para exibir outras avaliações -->
    </div>
@endsection

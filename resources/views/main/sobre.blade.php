@section('titulo', 'Sobre')


@extends('layouts.frame')
@section('sobre', 'active')
@section('content')
    
    <div style="height: 730px">
        <h1 class="h1 mb-4 text-center">SOBRE</h1>
        <p>Esse é um sistema desenvolvido para um trabalho do curso de Sistemas de Informação da Universidade Estadual de Montes Claros. A ideia do sistema, inicialmente, era apenas um trabalho de outra disciplina que não envolvia programação. Este trabalho consistia em criar uma empresa fictícia para trabalhar os conhecimentos em contabilidade.</p>
        <br>
        <p>Alguns meses depois da apresentação bem sucedida deste trabalho anterior, surgiram os trabalhos finais das disciplinas de Programação II e Banco de Dados II. Surgiu assim, portanto, a ideia de dar continuidade ao "projeto" e desenvolver o sistema virtual com esta temática.</p>
        <br>
        <p>O sistema em foi desenvolvido utilizando o Framework Laravel 11. Foi utilizado, também, Bootstrap e JQuery. O projeto em si nos permitiu trabalhar nossas habilidades de programação e entendimento de algoritmos e estrutura de dados, permitindo assim uma grande evolução como programadores.</p>
        <div class="text-center p-3">
            <img src="{{asset('images/logo/renice-logo-side.png')}}" alt="ReniceLogo" width="10%">
        </div>
    </div>

@endsection

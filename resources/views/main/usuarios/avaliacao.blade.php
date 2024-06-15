@extends('layouts.frame')

@section('titulo', 'Avaliar')

@push('style')
    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            font-size: 2em;
            color: lightgray;
            cursor: pointer;
        }

        .star-rating input[type="radio"]:checked ~ label {
            color: gold;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: gold;
        }

        .star-rating input[type="radio"]:checked + label,
        .star-rating input[type="radio"]:checked + label ~ label {
            color: gold;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <h1>Avaliar Produto</h1>
        <form action="{{ route('salvar.avaliacao') }}" method="POST">
            @csrf
            <input type="hidden" name="av_id_produto" value="{{ $pr_id }}">
            <div class="mb-3">
                <label class="form-label"><b>Nota do Produto (1 a 5)</b></label>
                <div class="star-rating text-start">
                    @for ($i = 5; $i >= 1; $i--)
                        <input type="radio" id="star{{ $i }}" name="av_nota" value="{{ $i }}">
                        <label for="star{{ $i }}" title="{{ $i }} estrelas">&#9733;</label>
                    @endfor
                </div>
            </div>
            <div class="mb-3">
                <label for="comentario" class="form-label"><b>Comentário</b></label>
                <textarea class="form-control" id="comentario" name="av_comentario" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
        </form>
    </div>
@endsection

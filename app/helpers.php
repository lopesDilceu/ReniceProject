<?php

if (!function_exists('formatar_cpf')) {
    function formatar_cpf($cpf)
    {
        // Remove tudo que não for número
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Formata o CPF
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
}

if (!function_exists('formatar_cep')) {
    function formatar_cep($cep)
    {
        // Remove tudo que não for número
        $cep = preg_replace('/[^0-9]/', '', $cep);

        // Formata o CPF
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
    }
}

if (!function_exists('formatar_tel')) {
    function formatar_tel($telefone)
    {
        // Remove tudo que não for número
        $telefone = preg_replace('/[^0-9]/', '', $telefone);

        // Formata o CPF
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    }
}
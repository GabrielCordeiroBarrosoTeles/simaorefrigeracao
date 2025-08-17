<?php

namespace App\Domain\Entity;

enum AgendamentoStatus: string
{
    case PENDENTE = 'pendente';
    case CONCLUIDO = 'concluido';
    case CANCELADO = 'cancelado';
}
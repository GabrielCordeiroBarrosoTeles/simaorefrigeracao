<?php

namespace App\Domain\Entity;

enum TecnicoStatus: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
}
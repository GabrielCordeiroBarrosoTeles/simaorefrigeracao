<?php

namespace App\Domain\Entity;

enum UsuarioNivel: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case TECNICO = 'tecnico';
    case TECNICO_ADM = 'tecnico_adm';
}
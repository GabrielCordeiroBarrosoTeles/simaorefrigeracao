<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Email;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'usuarios')]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nome;

    #[ORM\Embedded(class: Email::class)]
    private Email $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $senha;

    #[ORM\Column(type: 'string', enumType: UsuarioNivel::class)]
    private UsuarioNivel $nivel;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $ultimoLogin = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeImmutable $dataCriacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $dataAtualizacao = null;

    public function __construct(string $nome, Email $email, string $senha, UsuarioNivel $nivel)
    {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = password_hash($senha, PASSWORD_DEFAULT);
        $this->nivel = $nivel;
        $this->dataCriacao = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): Email { return $this->email; }
    public function getNivel(): UsuarioNivel { return $this->nivel; }
    public function getUltimoLogin(): ?\DateTimeImmutable { return $this->ultimoLogin; }

    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senha);
    }

    public function atualizarSenha(string $novaSenha): self
    {
        $this->senha = password_hash($novaSenha, PASSWORD_DEFAULT);
        return $this;
    }

    public function registrarLogin(): self
    {
        $this->ultimoLogin = new \DateTimeImmutable();
        return $this;
    }
}
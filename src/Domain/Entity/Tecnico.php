<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Telefone;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tecnicos')]
class Tecnico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nome;

    #[ORM\Embedded(class: Email::class)]
    private Email $email;

    #[ORM\Embedded(class: Telefone::class)]
    private Telefone $telefone;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $especialidade = null;

    #[ORM\Column(type: 'string', length: 7)]
    private string $cor = '#3b82f6';

    #[ORM\Column(type: 'string', enumType: TecnicoStatus::class)]
    private TecnicoStatus $status;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeImmutable $dataCriacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $dataAtualizacao = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $usuario = null;

    public function __construct(string $nome, Email $email, Telefone $telefone)
    {
        $this->nome = $nome;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->status = TecnicoStatus::ATIVO;
        $this->dataCriacao = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): Email { return $this->email; }
    public function getTelefone(): Telefone { return $this->telefone; }
    public function getEspecialidade(): ?string { return $this->especialidade; }
    public function getCor(): string { return $this->cor; }
    public function getStatus(): TecnicoStatus { return $this->status; }
    public function getUsuario(): ?Usuario { return $this->usuario; }

    public function setEspecialidade(?string $especialidade): self { $this->especialidade = $especialidade; return $this; }
    public function setCor(string $cor): self { $this->cor = $cor; return $this; }
    public function setStatus(TecnicoStatus $status): self { $this->status = $status; return $this; }
    public function setUsuario(?Usuario $usuario): self { $this->usuario = $usuario; return $this; }
}
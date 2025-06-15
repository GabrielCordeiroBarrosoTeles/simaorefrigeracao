<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'usuarios')]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nome;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $senha;

    #[ORM\Column(type: 'string', enumType: 'string')]
    private string $nivel = 'editor';

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $ultimo_login = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $data_criacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $data_atualizacao = null;

    #[ORM\OneToMany(targetEntity: Tecnico::class, mappedBy: 'usuario')]
    private Collection $tecnicos;

    public function __construct()
    {
        $this->tecnicos = new ArrayCollection();
        $this->data_criacao = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): self
    {
        $this->senha = $senha;
        return $this;
    }

    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senha);
    }

    public function getNivel(): string
    {
        return $this->nivel;
    }

    public function setNivel(string $nivel): self
    {
        $this->nivel = $nivel;
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->nivel === 'admin';
    }

    public function isTecnico(): bool
    {
        return $this->nivel === 'tecnico' || $this->nivel === 'tecnico_adm';
    }

    public function getUltimoLogin(): ?\DateTime
    {
        return $this->ultimo_login;
    }

    public function setUltimoLogin(): self
    {
        $this->ultimo_login = new \DateTime();
        return $this;
    }

    public function getDataCriacao(): \DateTime
    {
        return $this->data_criacao;
    }

    public function getDataAtualizacao(): ?\DateTime
    {
        return $this->data_atualizacao;
    }

    public function setDataAtualizacao(): self
    {
        $this->data_atualizacao = new \DateTime();
        return $this;
    }

    public function getTecnicos(): Collection
    {
        return $this->tecnicos;
    }

    public function getTecnico(): ?Tecnico
    {
        return $this->tecnicos->first() ?: null;
    }
}
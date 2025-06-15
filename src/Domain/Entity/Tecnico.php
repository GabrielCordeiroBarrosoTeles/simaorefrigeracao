<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tecnicos')]
class Tecnico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nome;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20)]
    private string $telefone;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $especialidade = null;

    #[ORM\Column(type: 'string', length: 7)]
    private string $cor = '#3b82f6';

    #[ORM\Column(type: 'string', enumType: 'string')]
    private string $status = 'ativo';

    #[ORM\Column(type: 'datetime')]
    private \DateTime $data_criacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $data_atualizacao = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: true)]
    private ?Usuario $usuario = null;

    #[ORM\OneToMany(targetEntity: Agendamento::class, mappedBy: 'tecnico')]
    private Collection $agendamentos;

    public function __construct()
    {
        $this->agendamentos = new ArrayCollection();
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

    public function getTelefone(): string
    {
        return $this->telefone;
    }

    public function setTelefone(string $telefone): self
    {
        $this->telefone = $telefone;
        return $this;
    }

    public function getEspecialidade(): ?string
    {
        return $this->especialidade;
    }

    public function setEspecialidade(?string $especialidade): self
    {
        $this->especialidade = $especialidade;
        return $this;
    }

    public function getCor(): string
    {
        return $this->cor;
    }

    public function setCor(string $cor): self
    {
        $this->cor = $cor;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isAtivo(): bool
    {
        return $this->status === 'ativo';
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

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getAgendamentos(): Collection
    {
        return $this->agendamentos;
    }

    public function addAgendamento(Agendamento $agendamento): self
    {
        if (!$this->agendamentos->contains($agendamento)) {
            $this->agendamentos[] = $agendamento;
            $agendamento->setTecnico($this);
        }
        return $this;
    }

    public function removeAgendamento(Agendamento $agendamento): self
    {
        if ($this->agendamentos->contains($agendamento)) {
            $this->agendamentos->removeElement($agendamento);
        }
        return $this;
    }
}
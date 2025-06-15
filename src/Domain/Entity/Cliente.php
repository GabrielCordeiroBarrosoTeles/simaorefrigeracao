<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'clientes')]
class Cliente
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $endereco = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $cidade = null;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private ?string $estado = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $cep = null;

    #[ORM\Column(type: 'string', enumType: 'string')]
    private string $tipo = 'residencial';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $data_criacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $data_atualizacao = null;

    #[ORM\OneToMany(targetEntity: Agendamento::class, mappedBy: 'cliente')]
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

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function setEndereco(?string $endereco): self
    {
        $this->endereco = $endereco;
        return $this;
    }

    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    public function setCidade(?string $cidade): self
    {
        $this->cidade = $cidade;
        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): self
    {
        $this->estado = $estado;
        return $this;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function setCep(?string $cep): self
    {
        $this->cep = $cep;
        return $this;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getObservacoes(): ?string
    {
        return $this->observacoes;
    }

    public function setObservacoes(?string $observacoes): self
    {
        $this->observacoes = $observacoes;
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

    public function getAgendamentos(): Collection
    {
        return $this->agendamentos;
    }

    public function addAgendamento(Agendamento $agendamento): self
    {
        if (!$this->agendamentos->contains($agendamento)) {
            $this->agendamentos[] = $agendamento;
            $agendamento->setCliente($this);
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
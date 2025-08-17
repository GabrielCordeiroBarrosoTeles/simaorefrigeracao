<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Telefone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'clientes')]
#[ORM\HasLifecycleCallbacks]
class Cliente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private string $nome;

    #[ORM\Embedded(class: Email::class)]
    private Email $email;

    #[ORM\Embedded(class: Telefone::class)]
    private Telefone $telefone;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $endereco = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $cidade = null;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private ?string $estado = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $cep = null;

    #[ORM\Column(type: 'string', enumType: ClienteTipo::class)]
    private ClienteTipo $tipo;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $dataCriacao;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dataAtualizacao = null;

    #[ORM\OneToMany(targetEntity: Agendamento::class, mappedBy: 'cliente')]
    private Collection $agendamentos;

    public function __construct(string $nome, Email $email, Telefone $telefone, ClienteTipo $tipo = ClienteTipo::RESIDENCIAL)
    {
        $this->nome = $nome;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->tipo = $tipo;
        $this->agendamentos = new ArrayCollection();
        $this->dataCriacao = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dataAtualizacao = new \DateTimeImmutable();
    }

    public function getId(): ?int
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

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTelefone(): Telefone
    {
        return $this->telefone;
    }

    public function setTelefone(Telefone $telefone): self
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

    public function getTipo(): ClienteTipo
    {
        return $this->tipo;
    }

    public function setTipo(ClienteTipo $tipo): self
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

    public function getDataCriacao(): \DateTimeImmutable
    {
        return $this->dataCriacao;
    }

    public function getDataAtualizacao(): ?\DateTimeImmutable
    {
        return $this->dataAtualizacao;
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
        $this->agendamentos->removeElement($agendamento);
        return $this;
    }
}
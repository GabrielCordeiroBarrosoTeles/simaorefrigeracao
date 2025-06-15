<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'servicos')]
class Servico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $titulo;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $icone = null;

    #[ORM\Column(type: 'text')]
    private string $descricao;

    #[ORM\Column(type: 'json')]
    private array $itens = [];

    #[ORM\Column(type: 'datetime')]
    private \DateTime $data_criacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $data_atualizacao = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $garantia_meses = 3;

    #[ORM\OneToMany(targetEntity: Agendamento::class, mappedBy: 'servico')]
    private Collection $agendamentos;

    #[ORM\OneToMany(targetEntity: Contato::class, mappedBy: 'servico')]
    private Collection $contatos;

    public function __construct()
    {
        $this->agendamentos = new ArrayCollection();
        $this->contatos = new ArrayCollection();
        $this->data_criacao = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(?string $icone): self
    {
        $this->icone = $icone;
        return $this;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function getItens(): array
    {
        return $this->itens;
    }

    public function setItens(array $itens): self
    {
        $this->itens = $itens;
        return $this;
    }

    public function addItem(string $item): self
    {
        if (!in_array($item, $this->itens)) {
            $this->itens[] = $item;
        }
        return $this;
    }

    public function removeItem(string $item): self
    {
        $key = array_search($item, $this->itens);
        if ($key !== false) {
            unset($this->itens[$key]);
            $this->itens = array_values($this->itens);
        }
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

    public function getGarantiaMeses(): ?int
    {
        return $this->garantia_meses;
    }

    public function setGarantiaMeses(?int $garantia_meses): self
    {
        $this->garantia_meses = $garantia_meses;
        return $this;
    }

    public function getAgendamentos(): Collection
    {
        return $this->agendamentos;
    }

    public function getContatos(): Collection
    {
        return $this->contatos;
    }
}
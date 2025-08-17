<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'servicos')]
class Servico
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $titulo;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $icone = null;

    #[ORM\Column(type: 'text')]
    private string $descricao;

    #[ORM\Column(type: 'json')]
    private array $itens = [];

    #[ORM\Column(type: 'integer')]
    private int $garantiaMeses = 3;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeImmutable $dataCriacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $dataAtualizacao = null;

    public function __construct(string $titulo, string $descricao, array $itens = [])
    {
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->itens = $itens;
        $this->dataCriacao = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getIcone(): ?string { return $this->icone; }
    public function getDescricao(): string { return $this->descricao; }
    public function getItens(): array { return $this->itens; }
    public function getGarantiaMeses(): int { return $this->garantiaMeses; }

    public function setIcone(?string $icone): self { $this->icone = $icone; return $this; }
    public function setItens(array $itens): self { $this->itens = $itens; return $this; }
    public function setGarantiaMeses(int $garantiaMeses): self { $this->garantiaMeses = $garantiaMeses; return $this; }
}
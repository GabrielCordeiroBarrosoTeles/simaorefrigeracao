<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contatos')]
class Contato
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nome;

    #[ORM\Column(type: 'string', length: 100)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20)]
    private string $telefone;

    #[ORM\ManyToOne(targetEntity: Servico::class, inversedBy: 'contatos')]
    #[ORM\JoinColumn(name: 'servico_id', referencedColumnName: 'id', nullable: true)]
    private ?Servico $servico = null;

    #[ORM\Column(type: 'text')]
    private string $mensagem;

    #[ORM\Column(type: 'string', enumType: 'string')]
    private string $status = 'novo';

    #[ORM\Column(type: 'datetime')]
    private \DateTime $data_criacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $data_atualizacao = null;

    public function __construct()
    {
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

    public function getServico(): ?Servico
    {
        return $this->servico;
    }

    public function setServico(?Servico $servico): self
    {
        $this->servico = $servico;
        return $this;
    }

    public function getMensagem(): string
    {
        return $this->mensagem;
    }

    public function setMensagem(string $mensagem): self
    {
        $this->mensagem = $mensagem;
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

    public function isNovo(): bool
    {
        return $this->status === 'novo';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'em_andamento';
    }

    public function isRespondido(): bool
    {
        return $this->status === 'respondido';
    }

    public function isFinalizado(): bool
    {
        return $this->status === 'finalizado';
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
}
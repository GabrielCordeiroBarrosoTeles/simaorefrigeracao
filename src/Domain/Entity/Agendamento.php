<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agendamentos')]
class Agendamento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $titulo;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: 'agendamentos')]
    #[ORM\JoinColumn(name: 'cliente_id', referencedColumnName: 'id', nullable: false)]
    private Cliente $cliente;

    #[ORM\ManyToOne(targetEntity: Servico::class, inversedBy: 'agendamentos')]
    #[ORM\JoinColumn(name: 'servico_id', referencedColumnName: 'id', nullable: false)]
    private Servico $servico;

    #[ORM\ManyToOne(targetEntity: Tecnico::class, inversedBy: 'agendamentos')]
    #[ORM\JoinColumn(name: 'tecnico_id', referencedColumnName: 'id', nullable: false)]
    private Tecnico $tecnico;

    #[ORM\Column(type: 'date')]
    private \DateTime $data_agendamento;

    #[ORM\Column(type: 'time')]
    private \DateTime $hora_inicio;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTime $hora_fim = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes = null;

    #[ORM\Column(type: 'string', enumType: 'string')]
    private string $status = 'pendente';

    #[ORM\Column(type: 'datetime')]
    private \DateTime $data_criacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $data_atualizacao = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $valor = 0.0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $valor_pendente = 0.0;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $data_garantia = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes_tecnicas = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $local_servico = null;

    public function __construct()
    {
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

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function setCliente(Cliente $cliente): self
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getServico(): Servico
    {
        return $this->servico;
    }

    public function setServico(Servico $servico): self
    {
        $this->servico = $servico;
        return $this;
    }

    public function getTecnico(): Tecnico
    {
        return $this->tecnico;
    }

    public function setTecnico(Tecnico $tecnico): self
    {
        $this->tecnico = $tecnico;
        return $this;
    }

    public function getDataAgendamento(): \DateTime
    {
        return $this->data_agendamento;
    }

    public function setDataAgendamento(\DateTime $data_agendamento): self
    {
        $this->data_agendamento = $data_agendamento;
        return $this;
    }

    public function getHoraInicio(): \DateTime
    {
        return $this->hora_inicio;
    }

    public function setHoraInicio(\DateTime $hora_inicio): self
    {
        $this->hora_inicio = $hora_inicio;
        return $this;
    }

    public function getHoraFim(): ?\DateTime
    {
        return $this->hora_fim;
    }

    public function setHoraFim(?\DateTime $hora_fim): self
    {
        $this->hora_fim = $hora_fim;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isConcluido(): bool
    {
        return $this->status === 'concluido';
    }

    public function isCancelado(): bool
    {
        return $this->status === 'cancelado';
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

    public function getValor(): float
    {
        return $this->valor;
    }

    public function setValor(float $valor): self
    {
        $this->valor = $valor;
        return $this;
    }

    public function getValorPendente(): float
    {
        return $this->valor_pendente;
    }

    public function setValorPendente(float $valor_pendente): self
    {
        $this->valor_pendente = $valor_pendente;
        return $this;
    }

    public function getDataGarantia(): ?\DateTime
    {
        return $this->data_garantia;
    }

    public function setDataGarantia(?\DateTime $data_garantia): self
    {
        $this->data_garantia = $data_garantia;
        return $this;
    }

    public function calcularDataGarantia(): self
    {
        if ($this->servico && $this->servico->getGarantiaMeses() > 0) {
            $data = new \DateTime();
            $data->modify('+' . $this->servico->getGarantiaMeses() . ' months');
            $this->data_garantia = $data;
        }
        return $this;
    }

    public function getObservacoesTecnicas(): ?string
    {
        return $this->observacoes_tecnicas;
    }

    public function setObservacoesTecnicas(?string $observacoes_tecnicas): self
    {
        $this->observacoes_tecnicas = $observacoes_tecnicas;
        return $this;
    }

    public function getLocalServico(): ?string
    {
        return $this->local_servico;
    }

    public function setLocalServico(?string $local_servico): self
    {
        $this->local_servico = $local_servico;
        return $this;
    }
}
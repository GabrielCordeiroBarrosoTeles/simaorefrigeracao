<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agendamentos')]
class Agendamento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $titulo;

    #[ORM\ManyToOne(targetEntity: Cliente::class)]
    #[ORM\JoinColumn(name: 'cliente_id', referencedColumnName: 'id', nullable: false)]
    private Cliente $cliente;

    #[ORM\ManyToOne(targetEntity: Servico::class)]
    #[ORM\JoinColumn(name: 'servico_id', referencedColumnName: 'id', nullable: false)]
    private Servico $servico;

    #[ORM\ManyToOne(targetEntity: Tecnico::class)]
    #[ORM\JoinColumn(name: 'tecnico_id', referencedColumnName: 'id', nullable: false)]
    private Tecnico $tecnico;

    #[ORM\Column(type: 'date')]
    private \DateTime $dataAgendamento;

    #[ORM\Column(type: 'time')]
    private \DateTime $horaInicio;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTime $horaFim = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoes = null;

    #[ORM\Column(type: 'string', enumType: AgendamentoStatus::class)]
    private AgendamentoStatus $status;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeImmutable $dataCriacao;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $dataAtualizacao = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $valor = 0.0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $valorPendente = 0.0;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $dataGarantia = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observacoesTecnicas = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $localServico = null;

    public function __construct(
        string $titulo,
        Cliente $cliente,
        Servico $servico,
        Tecnico $tecnico,
        \DateTime $dataAgendamento,
        \DateTime $horaInicio
    ) {
        $this->titulo = $titulo;
        $this->cliente = $cliente;
        $this->servico = $servico;
        $this->tecnico = $tecnico;
        $this->dataAgendamento = $dataAgendamento;
        $this->horaInicio = $horaInicio;
        $this->status = AgendamentoStatus::PENDENTE;
        $this->dataCriacao = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getCliente(): Cliente { return $this->cliente; }
    public function getServico(): Servico { return $this->servico; }
    public function getTecnico(): Tecnico { return $this->tecnico; }
    public function getDataAgendamento(): \DateTime { return $this->dataAgendamento; }
    public function getHoraInicio(): \DateTime { return $this->horaInicio; }
    public function getHoraFim(): ?\DateTime { return $this->horaFim; }
    public function getObservacoes(): ?string { return $this->observacoes; }
    public function getStatus(): AgendamentoStatus { return $this->status; }
    public function getValor(): float { return $this->valor; }
    public function getValorPendente(): float { return $this->valorPendente; }
    public function getDataGarantia(): ?\DateTime { return $this->dataGarantia; }
    public function getObservacoesTecnicas(): ?string { return $this->observacoesTecnicas; }
    public function getLocalServico(): ?string { return $this->localServico; }

    public function setHoraFim(?\DateTime $horaFim): self { $this->horaFim = $horaFim; return $this; }
    public function setObservacoes(?string $observacoes): self { $this->observacoes = $observacoes; return $this; }
    public function setStatus(AgendamentoStatus $status): self { $this->status = $status; return $this; }
    public function setValor(float $valor): self { $this->valor = $valor; return $this; }
    public function setValorPendente(float $valorPendente): self { $this->valorPendente = $valorPendente; return $this; }
    public function setDataGarantia(?\DateTime $dataGarantia): self { $this->dataGarantia = $dataGarantia; return $this; }
    public function setObservacoesTecnicas(?string $observacoesTecnicas): self { $this->observacoesTecnicas = $observacoesTecnicas; return $this; }
    public function setLocalServico(?string $localServico): self { $this->localServico = $localServico; return $this; }
}
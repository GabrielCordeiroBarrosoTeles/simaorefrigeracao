<?php
namespace App\Service;

use App\Entity\Agendamento;
use App\Entity\Cliente;
use App\Entity\Servico;
use App\Entity\Tecnico;
use App\Repository\AgendamentoRepository;
use App\Repository\ClienteRepository;
use App\Repository\ServicoRepository;
use App\Repository\TecnicoRepository;

class AgendamentoService
{
    private AgendamentoRepository $agendamentoRepository;
    private ClienteRepository $clienteRepository;
    private ServicoRepository $servicoRepository;
    private TecnicoRepository $tecnicoRepository;

    public function __construct(
        AgendamentoRepository $agendamentoRepository,
        ClienteRepository $clienteRepository,
        ServicoRepository $servicoRepository,
        TecnicoRepository $tecnicoRepository
    ) {
        $this->agendamentoRepository = $agendamentoRepository;
        $this->clienteRepository = $clienteRepository;
        $this->servicoRepository = $servicoRepository;
        $this->tecnicoRepository = $tecnicoRepository;
    }

    public function listarTodos(): array
    {
        return $this->agendamentoRepository->findAll();
    }

    public function buscarPorId(int $id): ?Agendamento
    {
        return $this->agendamentoRepository->findById($id);
    }

    public function buscarPorCliente(int $clienteId): array
    {
        $cliente = $this->clienteRepository->findById($clienteId);
        if (!$cliente) {
            return [];
        }
        
        return $this->agendamentoRepository->findByCliente($cliente);
    }

    public function buscarPorTecnico(int $tecnicoId): array
    {
        $tecnico = $this->tecnicoRepository->findById($tecnicoId);
        if (!$tecnico) {
            return [];
        }
        
        return $this->agendamentoRepository->findByTecnico($tecnico);
    }

    public function buscarPorData(\DateTime $data): array
    {
        return $this->agendamentoRepository->findByData($data);
    }

    public function buscarPorPeriodo(\DateTime $inicio, \DateTime $fim): array
    {
        return $this->agendamentoRepository->findByPeriodo($inicio, $fim);
    }

    public function buscarPorStatus(string $status): array
    {
        return $this->agendamentoRepository->findByStatus($status);
    }

    public function criar(array $dados): ?Agendamento
    {
        // Validar dados obrigatórios
        if (!isset($dados['titulo']) || !isset($dados['cliente_id']) || 
            !isset($dados['servico_id']) || !isset($dados['tecnico_id']) || 
            !isset($dados['data_agendamento']) || !isset($dados['hora_inicio'])) {
            return null;
        }
        
        // Buscar entidades relacionadas
        $cliente = $this->clienteRepository->findById($dados['cliente_id']);
        $servico = $this->servicoRepository->findById($dados['servico_id']);
        $tecnico = $this->tecnicoRepository->findById($dados['tecnico_id']);
        
        if (!$cliente || !$servico || !$tecnico) {
            return null;
        }
        
        // Criar agendamento
        $agendamento = new Agendamento();
        $agendamento->setTitulo($dados['titulo']);
        $agendamento->setCliente($cliente);
        $agendamento->setServico($servico);
        $agendamento->setTecnico($tecnico);
        
        // Converter strings para objetos DateTime
        $dataAgendamento = new \DateTime($dados['data_agendamento']);
        $horaInicio = new \DateTime($dados['hora_inicio']);
        
        $agendamento->setDataAgendamento($dataAgendamento);
        $agendamento->setHoraInicio($horaInicio);
        
        if (isset($dados['hora_fim'])) {
            $horaFim = new \DateTime($dados['hora_fim']);
            $agendamento->setHoraFim($horaFim);
        }
        
        if (isset($dados['observacoes'])) {
            $agendamento->setObservacoes($dados['observacoes']);
        }
        
        if (isset($dados['status'])) {
            $agendamento->setStatus($dados['status']);
        }
        
        if (isset($dados['valor'])) {
            $agendamento->setValor((float) $dados['valor']);
        }
        
        if (isset($dados['valor_pendente'])) {
            $agendamento->setValorPendente((float) $dados['valor_pendente']);
        }
        
        if (isset($dados['observacoes_tecnicas'])) {
            $agendamento->setObservacoesTecnicas($dados['observacoes_tecnicas']);
        }
        
        if (isset($dados['local_servico'])) {
            $agendamento->setLocalServico($dados['local_servico']);
        }
        
        // Calcular data de garantia com base no serviço
        $agendamento->calcularDataGarantia();
        
        $this->agendamentoRepository->save($agendamento);
        return $agendamento;
    }

    public function atualizar(Agendamento $agendamento, array $dados): Agendamento
    {
        if (isset($dados['titulo'])) {
            $agendamento->setTitulo($dados['titulo']);
        }
        
        if (isset($dados['cliente_id'])) {
            $cliente = $this->clienteRepository->findById($dados['cliente_id']);
            if ($cliente) {
                $agendamento->setCliente($cliente);
            }
        }
        
        if (isset($dados['servico_id'])) {
            $servico = $this->servicoRepository->findById($dados['servico_id']);
            if ($servico) {
                $agendamento->setServico($servico);
                // Recalcular garantia se o serviço mudar
                $agendamento->calcularDataGarantia();
            }
        }
        
        if (isset($dados['tecnico_id'])) {
            $tecnico = $this->tecnicoRepository->findById($dados['tecnico_id']);
            if ($tecnico) {
                $agendamento->setTecnico($tecnico);
            }
        }
        
        if (isset($dados['data_agendamento'])) {
            $dataAgendamento = new \DateTime($dados['data_agendamento']);
            $agendamento->setDataAgendamento($dataAgendamento);
        }
        
        if (isset($dados['hora_inicio'])) {
            $horaInicio = new \DateTime($dados['hora_inicio']);
            $agendamento->setHoraInicio($horaInicio);
        }
        
        if (isset($dados['hora_fim'])) {
            $horaFim = new \DateTime($dados['hora_fim']);
            $agendamento->setHoraFim($horaFim);
        }
        
        if (isset($dados['observacoes'])) {
            $agendamento->setObservacoes($dados['observacoes']);
        }
        
        if (isset($dados['status'])) {
            $agendamento->setStatus($dados['status']);
        }
        
        if (isset($dados['valor'])) {
            $agendamento->setValor((float) $dados['valor']);
        }
        
        if (isset($dados['valor_pendente'])) {
            $agendamento->setValorPendente((float) $dados['valor_pendente']);
        }
        
        if (isset($dados['observacoes_tecnicas'])) {
            $agendamento->setObservacoesTecnicas($dados['observacoes_tecnicas']);
        }
        
        if (isset($dados['local_servico'])) {
            $agendamento->setLocalServico($dados['local_servico']);
        }
        
        $this->agendamentoRepository->save($agendamento);
        return $agendamento;
    }

    public function excluir(Agendamento $agendamento): void
    {
        $this->agendamentoRepository->remove($agendamento);
    }

    public function contarPorStatus(string $status): int
    {
        return $this->agendamentoRepository->countByStatus($status);
    }
}
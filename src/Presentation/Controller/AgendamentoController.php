<?php
namespace App\Controller;

use App\Service\AgendamentoService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AgendamentoRepository;
use App\Repository\ClienteRepository;
use App\Repository\ServicoRepository;
use App\Repository\TecnicoRepository;

class AgendamentoController
{
    private AgendamentoService $agendamentoService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $agendamentoRepository = new AgendamentoRepository($entityManager);
        $clienteRepository = new ClienteRepository($entityManager);
        $servicoRepository = new ServicoRepository($entityManager);
        $tecnicoRepository = new TecnicoRepository($entityManager);
        
        $this->agendamentoService = new AgendamentoService(
            $agendamentoRepository,
            $clienteRepository,
            $servicoRepository,
            $tecnicoRepository
        );
    }

    public function index(): array
    {
        $agendamentos = $this->agendamentoService->listarTodos();
        return $this->formatarAgendamentos($agendamentos);
    }

    public function show(int $id): ?array
    {
        $agendamento = $this->agendamentoService->buscarPorId($id);
        
        if (!$agendamento) {
            return null;
        }
        
        return $this->formatarAgendamento($agendamento);
    }

    public function create(array $dados): ?array
    {
        $agendamento = $this->agendamentoService->criar($dados);
        
        if (!$agendamento) {
            return null;
        }
        
        return [
            'id' => $agendamento->getId(),
            'titulo' => $agendamento->getTitulo(),
            'mensagem' => 'Agendamento criado com sucesso!'
        ];
    }

    public function update(int $id, array $dados): ?array
    {
        $agendamento = $this->agendamentoService->buscarPorId($id);
        
        if (!$agendamento) {
            return null;
        }
        
        $agendamento = $this->agendamentoService->atualizar($agendamento, $dados);
        
        return [
            'id' => $agendamento->getId(),
            'titulo' => $agendamento->getTitulo(),
            'mensagem' => 'Agendamento atualizado com sucesso!'
        ];
    }

    public function delete(int $id): bool
    {
        $agendamento = $this->agendamentoService->buscarPorId($id);
        
        if (!$agendamento) {
            return false;
        }
        
        $this->agendamentoService->excluir($agendamento);
        return true;
    }

    public function findByCliente(int $clienteId): array
    {
        $agendamentos = $this->agendamentoService->buscarPorCliente($clienteId);
        return $this->formatarAgendamentos($agendamentos);
    }

    public function findByTecnico(int $tecnicoId): array
    {
        $agendamentos = $this->agendamentoService->buscarPorTecnico($tecnicoId);
        return $this->formatarAgendamentos($agendamentos);
    }

    public function findByData(string $data): array
    {
        $dataObj = new \DateTime($data);
        $agendamentos = $this->agendamentoService->buscarPorData($dataObj);
        return $this->formatarAgendamentos($agendamentos);
    }

    public function findByPeriodo(string $inicio, string $fim): array
    {
        $inicioObj = new \DateTime($inicio);
        $fimObj = new \DateTime($fim);
        $agendamentos = $this->agendamentoService->buscarPorPeriodo($inicioObj, $fimObj);
        return $this->formatarAgendamentos($agendamentos);
    }

    public function findByStatus(string $status): array
    {
        $agendamentos = $this->agendamentoService->buscarPorStatus($status);
        return $this->formatarAgendamentos($agendamentos);
    }

    public function countByStatus(string $status): int
    {
        return $this->agendamentoService->contarPorStatus($status);
    }

    private function formatarAgendamentos(array $agendamentos): array
    {
        $resultado = [];
        foreach ($agendamentos as $agendamento) {
            $resultado[] = $this->formatarAgendamento($agendamento);
        }
        return $resultado;
    }

    private function formatarAgendamento($agendamento): array
    {
        return [
            'id' => $agendamento->getId(),
            'titulo' => $agendamento->getTitulo(),
            'cliente' => [
                'id' => $agendamento->getCliente()->getId(),
                'nome' => $agendamento->getCliente()->getNome()
            ],
            'servico' => [
                'id' => $agendamento->getServico()->getId(),
                'titulo' => $agendamento->getServico()->getTitulo()
            ],
            'tecnico' => [
                'id' => $agendamento->getTecnico()->getId(),
                'nome' => $agendamento->getTecnico()->getNome()
            ],
            'data_agendamento' => $agendamento->getDataAgendamento()->format('Y-m-d'),
            'hora_inicio' => $agendamento->getHoraInicio()->format('H:i'),
            'hora_fim' => $agendamento->getHoraFim() ? $agendamento->getHoraFim()->format('H:i') : null,
            'observacoes' => $agendamento->getObservacoes(),
            'status' => $agendamento->getStatus(),
            'valor' => $agendamento->getValor(),
            'valor_pendente' => $agendamento->getValorPendente(),
            'data_garantia' => $agendamento->getDataGarantia() ? $agendamento->getDataGarantia()->format('Y-m-d') : null,
            'observacoes_tecnicas' => $agendamento->getObservacoesTecnicas(),
            'local_servico' => $agendamento->getLocalServico(),
            'data_criacao' => $agendamento->getDataCriacao()->format('Y-m-d H:i:s'),
            'data_atualizacao' => $agendamento->getDataAtualizacao() ? $agendamento->getDataAtualizacao()->format('Y-m-d H:i:s') : null
        ];
    }
}
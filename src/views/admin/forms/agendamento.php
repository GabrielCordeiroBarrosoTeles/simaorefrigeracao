<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <?= isset($agendamento) ? 'Editar Agendamento' : 'Novo Agendamento' ?>
        </h6>
    </div>
    <div class="card-body">
        <form action="admin-save.php" method="post">
            <input type="hidden" name="table" value="agendamentos">
            <?php if (isset($agendamento)): ?>
                <input type="hidden" name="id" value="<?= $agendamento['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cliente_id">Cliente</label>
                    <select class="form-control" id="cliente_id" name="cliente_id" required>
                        <option value="">Selecione um cliente...</option>
                        <?php
                        $db = db_connect();
                        $query = "SELECT id, nome FROM clientes ORDER BY nome";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($clientes as $cliente) {
                            $selected = (isset($agendamento['cliente_id']) && $agendamento['cliente_id'] == $cliente['id']) ? 'selected' : '';
                            echo "<option value=\"{$cliente['id']}\" $selected>{$cliente['nome']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="servico_id">Serviço</label>
                    <select class="form-control" id="servico_id" name="servico_id" required>
                        <option value="">Selecione um serviço...</option>
                        <?php
                        $query = "SELECT id, titulo FROM servicos WHERE ativo = 1 ORDER BY titulo";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($servicos as $servico) {
                            $selected = (isset($agendamento['servico_id']) && $agendamento['servico_id'] == $servico['id']) ? 'selected' : '';
                            echo "<option value=\"{$servico['id']}\" $selected>{$servico['titulo']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="tecnico_id">Técnico</label>
                    <select class="form-control" id="tecnico_id" name="tecnico_id" required>
                        <option value="">Selecione um técnico...</option>
                        <?php
                        $query = "SELECT id, nome FROM tecnicos ORDER BY nome";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($tecnicos as $tecnico) {
                            $selected = (isset($agendamento['tecnico_id']) && $agendamento['tecnico_id'] == $tecnico['id']) ? 'selected' : '';
                            echo "<option value=\"{$tecnico['id']}\" $selected>{$tecnico['nome']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <?php
                        $status_options = ['pendente' => 'Pendente', 'confirmado' => 'Confirmado', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];
                        
                        foreach ($status_options as $value => $label) {
                            $selected = (isset($agendamento['status']) && $agendamento['status'] == $value) ? 'selected' : '';
                            if (!isset($agendamento['status']) && $value == 'pendente') $selected = 'selected';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="data_agendamento">Data</label>
                    <input type="date" class="form-control" id="data_agendamento" name="data_agendamento" value="<?= isset($agendamento['data_agendamento']) ? date('Y-m-d', strtotime($agendamento['data_agendamento'])) : date('Y-m-d') ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="hora_agendamento">Hora</label>
                    <input type="time" class="form-control" id="hora_agendamento" name="hora_agendamento" value="<?= isset($agendamento['hora_agendamento']) ? date('H:i', strtotime($agendamento['hora_agendamento'])) : '09:00' ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco" value="<?= $agendamento['endereco'] ?? '' ?>">
                <small class="form-text text-muted">Deixe em branco para usar o endereço do cliente.</small>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= $agendamento['observacoes'] ?? '' ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="admin-table.php?table=agendamentos" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

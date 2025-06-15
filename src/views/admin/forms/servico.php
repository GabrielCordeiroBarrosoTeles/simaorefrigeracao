<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <?= isset($servico) ? 'Editar Serviço' : 'Novo Serviço' ?>
        </h6>
    </div>
    <div class="card-body">
        <form action="admin-save.php" method="post">
            <input type="hidden" name="table" value="servicos">
            <?php if (isset($servico)): ?>
                <input type="hidden" name="id" value="<?= $servico['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="titulo">Título</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?= $servico['titulo'] ?? '' ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="preco">Preço (R$)</label>
                    <input type="text" class="form-control" id="preco" name="preco" value="<?= $servico['preco'] ?? '' ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?= $servico['descricao'] ?? '' ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="duracao">Duração (minutos)</label>
                    <input type="number" class="form-control" id="duracao" name="duracao" value="<?= $servico['duracao'] ?? '60' ?>" min="15" step="15">
                </div>
                <div class="form-group col-md-6">
                    <label for="garantia">Garantia (dias)</label>
                    <input type="number" class="form-control" id="garantia" name="garantia" value="<?= $servico['garantia'] ?? '90' ?>" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="requisitos">Requisitos</label>
                <textarea class="form-control" id="requisitos" name="requisitos" rows="2"><?= $servico['requisitos'] ?? '' ?></textarea>
                <small class="form-text text-muted">Requisitos necessários para a realização do serviço.</small>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="2"><?= $servico['observacoes'] ?? '' ?></textarea>
            </div>
            
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="destaque" name="destaque" value="1" <?= (isset($servico['destaque']) && $servico['destaque'] == 1) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="destaque">Destacar no site</label>
                </div>
            </div>
            
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" value="1" <?= (!isset($servico['ativo']) || $servico['ativo'] == 1) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="ativo">Ativo</label>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="admin-table.php?table=servicos" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

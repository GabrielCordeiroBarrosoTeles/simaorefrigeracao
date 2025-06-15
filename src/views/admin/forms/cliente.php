<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <?= isset($cliente) ? 'Editar Cliente' : 'Novo Cliente' ?>
        </h6>
    </div>
    <div class="card-body">
        <form action="admin-save.php" method="post">
            <input type="hidden" name="table" value="clientes">
            <?php if (isset($cliente)): ?>
                <input type="hidden" name="id" value="<?= $cliente['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nome">Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?= $cliente['nome'] ?? '' ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $cliente['email'] ?? '' ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="telefone">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?= $cliente['telefone'] ?? '' ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="cpf">CPF</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?= $cliente['cpf'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="endereco">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" value="<?= $cliente['endereco'] ?? '' ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="numero">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" value="<?= $cliente['numero'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="bairro">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" value="<?= $cliente['bairro'] ?? '' ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="cidade">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" value="<?= $cliente['cidade'] ?? '' ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="">Selecione...</option>
                        <?php
                        $estados = [
                            'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas', 'BA' => 'Bahia',
                            'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo', 'GO' => 'Goiás',
                            'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
                            'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco', 'PI' => 'Piauí',
                            'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul',
                            'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina', 'SP' => 'São Paulo',
                            'SE' => 'Sergipe', 'TO' => 'Tocantins'
                        ];
                        
                        foreach ($estados as $sigla => $nome) {
                            $selected = (isset($cliente['estado']) && $cliente['estado'] == $sigla) ? 'selected' : '';
                            echo "<option value=\"$sigla\" $selected>$nome</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="cep">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" value="<?= $cliente['cep'] ?? '' ?>">
                </div>
                <div class="form-group col-md-8">
                    <label for="complemento">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento" value="<?= $cliente['complemento'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= $cliente['observacoes'] ?? '' ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="admin-table.php?table=clientes" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

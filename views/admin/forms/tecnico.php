<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <?= isset($tecnico) ? 'Editar Técnico' : 'Novo Técnico' ?>
        </h6>
    </div>
    <div class="card-body">
        <form action="admin-save.php" method="post">
            <input type="hidden" name="table" value="tecnicos">
            <?php if (isset($tecnico)): ?>
                <input type="hidden" name="id" value="<?= $tecnico['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nome">Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?= $tecnico['nome'] ?? '' ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $tecnico['email'] ?? '' ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="telefone">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?= $tecnico['telefone'] ?? '' ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="cpf">CPF</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" value="<?= $tecnico['cpf'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="especialidade">Especialidade</label>
                    <input type="text" class="form-control" id="especialidade" name="especialidade" value="<?= $tecnico['especialidade'] ?? '' ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="registro">Registro Profissional</label>
                    <input type="text" class="form-control" id="registro" name="registro" value="<?= $tecnico['registro'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="endereco">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" value="<?= $tecnico['endereco'] ?? '' ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="numero">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" value="<?= $tecnico['numero'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="bairro">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" value="<?= $tecnico['bairro'] ?? '' ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="cidade">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" value="<?= $tecnico['cidade'] ?? '' ?>">
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
                            $selected = (isset($tecnico['estado']) && $tecnico['estado'] == $sigla) ? 'selected' : '';
                            echo "<option value=\"$sigla\" $selected>$nome</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="cep">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" value="<?= $tecnico['cep'] ?? '' ?>">
                </div>
                <div class="form-group col-md-8">
                    <label for="complemento">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento" value="<?= $tecnico['complemento'] ?? '' ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= $tecnico['observacoes'] ?? '' ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="admin-table.php?table=tecnicos" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

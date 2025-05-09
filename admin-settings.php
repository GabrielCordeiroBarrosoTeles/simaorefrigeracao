<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

// Verificar se o usuário tem permissão de administrador
if ($_SESSION['user_nivel'] !== 'admin') {
    set_flash_message('danger', 'Você não tem permissão para acessar esta página.');
    header('Location: admin-dashboard.php');
    exit;
}

// Conectar ao banco de dados
$db = db_connect();

// Obter configurações atuais
try {
    $query = "SELECT * FROM configuracoes WHERE id = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        // Se não existir, criar registro padrão
        $query = "INSERT INTO configuracoes (id, nome_empresa, descricao_empresa, data_atualizacao) 
                  VALUES (1, 'Simão Refrigeração', 'Empresa especializada em soluções de climatização', NOW())";
        $db->exec($query);
        
        // Buscar novamente
        $query = "SELECT * FROM configuracoes WHERE id = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar configurações: ' . $e->getMessage());
    $config = [];
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar dados
    $nome_empresa = sanitize($_POST['nome_empresa']);
    $descricao_empresa = sanitize($_POST['descricao_empresa']);
    $telefone = sanitize($_POST['telefone']);
    $email = sanitize($_POST['email']);
    $endereco = sanitize($_POST['endereco']);
    $titulo_hero = sanitize($_POST['titulo_hero']);
    $subtitulo_hero = sanitize($_POST['subtitulo_hero']);
    $facebook = sanitize($_POST['facebook']);
    $instagram = sanitize($_POST['instagram']);
    $linkedin = sanitize($_POST['linkedin']);
    $whatsapp = sanitize($_POST['whatsapp']);
    
    // Validar campos obrigatórios
    if (empty($nome_empresa)) {
        set_flash_message('danger', 'O nome da empresa é obrigatório.');
    } else {
        try {
            // Atualizar configurações
            $query = "UPDATE configuracoes SET 
                      nome_empresa = :nome_empresa,
                      descricao_empresa = :descricao_empresa,
                      telefone = :telefone,
                      email = :email,
                      endereco = :endereco,
                      titulo_hero = :titulo_hero,
                      subtitulo_hero = :subtitulo_hero,
                      facebook = :facebook,
                      instagram = :instagram,
                      linkedin = :linkedin,
                      whatsapp = :whatsapp,
                      data_atualizacao = NOW()
                      WHERE id = 1";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome_empresa', $nome_empresa);
            $stmt->bindParam(':descricao_empresa', $descricao_empresa);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':titulo_hero', $titulo_hero);
            $stmt->bindParam(':subtitulo_hero', $subtitulo_hero);
            $stmt->bindParam(':facebook', $facebook);
            $stmt->bindParam(':instagram', $instagram);
            $stmt->bindParam(':linkedin', $linkedin);
            $stmt->bindParam(':whatsapp', $whatsapp);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Configurações atualizadas com sucesso!');
                
                // Processar upload de imagens
                $upload_dir = 'uploads/';
                
                // Imagem Hero
                if (isset($_FILES['imagem_hero']) && $_FILES['imagem_hero']['error'] === UPLOAD_ERR_OK) {
                    $imagem_hero = process_image_upload($_FILES['imagem_hero'], $upload_dir, 'hero');
                    if ($imagem_hero) {
                        $query = "UPDATE configuracoes SET imagem_hero = :imagem_hero WHERE id = 1";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':imagem_hero', $imagem_hero);
                        $stmt->execute();
                    }
                }
                
                // Imagem Sobre
                if (isset($_FILES['imagem_sobre']) && $_FILES['imagem_sobre']['error'] === UPLOAD_ERR_OK) {
                    $imagem_sobre = process_image_upload($_FILES['imagem_sobre'], $upload_dir, 'sobre');
                    if ($imagem_sobre) {
                        $query = "UPDATE configuracoes SET imagem_sobre = :imagem_sobre WHERE id = 1";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':imagem_sobre', $imagem_sobre);
                        $stmt->execute();
                    }
                }
                
                // Recarregar configurações
                $query = "SELECT * FROM configuracoes WHERE id = 1";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $config = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                set_flash_message('danger', 'Erro ao atualizar configurações.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }
}

// Função para processar upload de imagens
function process_image_upload($file, $upload_dir, $prefix = '') {
    // Verificar se o diretório existe, se não, criar
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Gerar nome único para o arquivo
    $filename = $prefix . '_' . time() . '_' . basename($file['name']);
    $target_file = $upload_dir . $filename;
    
    // Verificar tipo de arquivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        set_flash_message('danger', 'Apenas imagens JPG, PNG, GIF e WEBP são permitidas.');
        return false;
    }
    
    // Verificar tamanho do arquivo (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        set_flash_message('danger', 'O tamanho máximo permitido para imagens é 5MB.');
        return false;
    }
    
    // Mover arquivo para o diretório de uploads
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $filename;
    } else {
        set_flash_message('danger', 'Erro ao fazer upload da imagem.');
        return false;
    }
}

// Definir título da página
$page_title = 'Configurações do Site';

// Incluir o cabeçalho
include 'views/admin/includes/header.php';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-cogs mr-2"></i> <?= $page_title ?>
    </h1>
</div>

<?php display_flash_message(); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Configurações Gerais</h6>
    </div>
    <div class="card-body">
        <form action="admin-settings.php" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">Informações da Empresa</h5>
                    
                    <div class="form-group">
                        <label for="nome_empresa">Nome da Empresa *</label>
                        <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" value="<?= $config['nome_empresa'] ?? '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao_empresa">Descrição da Empresa</label>
                        <textarea class="form-control" id="descricao_empresa" name="descricao_empresa" rows="3"><?= $config['descricao_empresa'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" value="<?= $config['telefone'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $config['email'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="endereco">Endereço</label>
                        <textarea class="form-control" id="endereco" name="endereco" rows="2"><?= $config['endereco'] ?? '' ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5 class="mb-3">Seção Hero</h5>
                    
                    <div class="form-group">
                        <label for="titulo_hero">Título do Hero</label>
                        <input type="text" class="form-control" id="titulo_hero" name="titulo_hero" value="<?= $config['titulo_hero'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subtitulo_hero">Subtítulo do Hero</label>
                        <textarea class="form-control" id="subtitulo_hero" name="subtitulo_hero" rows="2"><?= $config['subtitulo_hero'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagem_hero">Imagem do Hero</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="imagem_hero" name="imagem_hero">
                            <label class="custom-file-label" for="imagem_hero">Escolher arquivo</label>
                        </div>
                        <?php if (!empty($config['imagem_hero'])): ?>
                        <div class="mt-2">
                            <img src="uploads/<?= $config['imagem_hero'] ?>" alt="Imagem Hero" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagem_sobre">Imagem da Seção Sobre</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="imagem_sobre" name="imagem_sobre">
                            <label class="custom-file-label" for="imagem_sobre">Escolher arquivo</label>
                        </div>
                        <?php if (!empty($config['imagem_sobre'])): ?>
                        <div class="mt-2">
                            <img src="uploads/<?= $config['imagem_sobre'] ?>" alt="Imagem Sobre" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h5 class="mb-3">Redes Sociais</h5>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="facebook">Facebook</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                            </div>
                            <input type="text" class="form-control" id="facebook" name="facebook" value="<?= $config['facebook'] ?? '' ?>" placeholder="URL do Facebook">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="instagram">Instagram</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                            </div>
                            <input type="text" class="form-control" id="instagram" name="instagram" value="<?= $config['instagram'] ?? '' ?>" placeholder="URL do Instagram">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="linkedin">LinkedIn</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                            </div>
                            <input type="text" class="form-control" id="linkedin" name="linkedin" value="<?= $config['linkedin'] ?? '' ?>" placeholder="URL do LinkedIn">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="whatsapp">WhatsApp</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                            </div>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp" value="<?= $config['whatsapp'] ?? '' ?>" placeholder="Número com DDD">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Salvar Configurações
                </button>
                <a href="admin-dashboard.php" class="btn btn-secondary ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
    // Atualizar nome do arquivo selecionado no input file
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || "Escolher arquivo");
    });
    
    // Máscara para telefone
    $('#telefone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        } else {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });
    
    // Máscara para WhatsApp
    $('#whatsapp').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        } else {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });
</script>

<?php include 'views/admin/includes/footer.php'; ?>

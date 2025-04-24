<?php require 'includes/header.php'; ?>

<!-- Hero Section -->
<section id="inicio" class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><?= $configuracoes['titulo_hero'] ?? 'Soluções completas em ar condicionado' ?></h1>
                <p><?= $configuracoes['subtitulo_hero'] ?? 'Oferecemos serviços de instalação, manutenção e projetos para garantir o conforto térmico ideal para sua casa ou empresa.' ?></p>
                <div class="hero-buttons">
                    <a href="#contato" class="btn btn-primary">Solicitar orçamento</a>
                    <a href="#servicos" class="btn btn-outline">Nossos serviços <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="hero-image">
                <img src="<?= $configuracoes['imagem_hero'] ?? 'assets/images/hero-image.jpg' ?>" alt="Técnico instalando ar condicionado">
            </div>
        </div>
    </div>
    <div class="hero-wave"></div>
</section>

<!-- Serviços Section -->
<section id="servicos" class="services">
    <div class="container">
        <div class="section-header">
            <h2>Nossos Serviços</h2>
            <p>Oferecemos soluções completas para climatização, desde a instalação até a manutenção.</p>
        </div>
        
        <div class="services-grid">
            <?php foreach ($servicos as $servico): ?>
            <div class="service-card">
                <div class="service-header">
                    <i class="fas fa-<?= $servico['icone'] ?>"></i>
                    <h3><?= $servico['titulo'] ?></h3>
                    <p><?= $servico['descricao'] ?></p>
                </div>
                <div class="service-content">
                    <ul>
                        <?php 
                        $itens = json_decode($servico['itens'], true);
                        if (is_array($itens)):
                            foreach ($itens as $item): 
                        ?>
                        <li><?= $item ?></li>
                        <?php 
                            endforeach; 
                        endif;
                        ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Sobre Section -->
<section id="sobre" class="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-image">
                <img src="<?= $configuracoes['imagem_sobre'] ?? 'assets/images/about-image.jpg' ?>" alt="Nossa equipe">
            </div>
            <div class="about-content">
                <h2>Sobre a <?= $configuracoes['nome_empresa'] ?? 'FrioCerto' ?></h2>
                <p><?= $configuracoes['descricao_empresa'] ?? 'Somos uma empresa especializada em soluções de climatização, com anos de experiência no mercado. Nossa equipe é formada por profissionais qualificados e comprometidos com a excelência.' ?></p>
                
                <div class="stats-grid">
                    <?php foreach ($estatisticas as $estatistica): ?>
                    <div class="stat-card">
                        <div class="stat-number"><?= $estatistica['valor'] ?></div>
                        <div class="stat-label"><?= $estatistica['descricao'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <a href="<?= $configuracoes['link_sobre'] ?? '#' ?>" class="btn btn-primary">Conheça nossa história</a>
            </div>
        </div>
    </div>
</section>

<!-- Depoimentos Section -->
<section id="depoimentos" class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2>O que nossos clientes dizem</h2>
            <p>A satisfação dos nossos clientes é nossa prioridade.</p>
        </div>
        
        <div class="testimonials-grid">
            <?php foreach ($depoimentos as $depoimento): ?>
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar">
                        <?php if (!empty($depoimento['foto'])): ?>
                        <img src="uploads/<?= $depoimento['foto'] ?>" alt="<?= $depoimento['nome'] ?>">
                        <?php else: ?>
                        <span><?= substr($depoimento['nome'], 0, 1) . substr(strrchr($depoimento['nome'], ' '), 1, 1) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="testimonial-author">
                        <h4><?= $depoimento['nome'] ?></h4>
                        <p><?= $depoimento['tipo'] ?></p>
                    </div>
                </div>
                <p class="testimonial-text">"<?= $depoimento['texto'] ?>"</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contato Section -->
<section id="contato" class="contact">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Entre em contato</h2>
                <p>Estamos prontos para atender suas necessidades de climatização. Entre em contato conosco para um orçamento sem compromisso.</p>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Telefone</h4>
                            <p><?= $configuracoes['telefone'] ?? '(85) 98810-6463' ?></p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p><?= $configuracoes['email'] ?? 'simaorefrigeracao2@gmail.com' ?></p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Endereço</h4>
                            <p><?= $configuracoes['endereco'] ?? 'Av. Sabino Monte, 3878 - São João do Tauape, Fortaleza - CE' ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <h3>Solicite um orçamento</h3>
                
                <?php
                // Exibir mensagem flash
                $flash_message = get_flash_message();
                if ($flash_message) {
                    echo '<div class="alert alert-' . $flash_message['type'] . '">' . $flash_message['message'] . '</div>';
                }
                
                // Exibir erros do formulário
                if (isset($_SESSION['form_data']['errors'])) {
                    echo '<div class="alert alert-danger"><ul>';
                    foreach ($_SESSION['form_data']['errors'] as $error) {
                        echo '<li>' . $error . '</li>';
                    }
                    echo '</ul></div>';
                }
                ?>
                
                <form method="POST" action="/enviar-contato">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" placeholder="Seu nome" required value="<?= $_SESSION['form_data']['nome'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="seu@email.com" required value="<?= $_SESSION['form_data']['email'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required value="<?= $_SESSION['form_data']['telefone'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="servico">Serviço</label>
                        <select id="servico" name="servico">
                            <option value="">Selecione um serviço</option>
                            <?php foreach ($servicos as $servico): ?>
                            <option value="<?= $servico['id'] ?>" <?= (isset($_SESSION['form_data']['servico']) && $_SESSION['form_data']['servico'] == $servico['id']) ? 'selected' : '' ?>>
                                <?= $servico['titulo'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mensagem">Mensagem</label>
                        <textarea id="mensagem" name="mensagem" placeholder="Descreva sua necessidade" required><?= $_SESSION['form_data']['mensagem'] ?? '' ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Enviar mensagem</button>
                </form>
                
                <?php
                // Limpar dados do formulário após exibição
                if (isset($_SESSION['form_data'])) {
                    unset($_SESSION['form_data']);
                }
                ?>
            </div>
        </div>
    </div>
</section>

<?php require 'includes/footer.php'; ?>

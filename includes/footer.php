
<?php
// Verificar se estamos em uma subpasta
$base_url = (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../' : '';
?>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3B82F6 0%, #8B5CF6 100%);" class="me-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-arrow-left-right text-white"></i>
                    </div>
                    <span class="h5 mb-0 fw-bold">EconomiaShare</span>
                </div>
                <p class="text-muted mb-4" style="max-width: 400px;">
                    Conectamos pessoas e oportunidades através da economia compartilhada, 
                    promovendo colaboração e sustentabilidade.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-muted hover-text-white">
                        <i class="bi bi-facebook" style="font-size: 1.2rem;"></i>
                    </a>
                    <a href="#" class="text-muted hover-text-white">
                        <i class="bi bi-instagram" style="font-size: 1.2rem;"></i>
                    </a>
                    <a href="#" class="text-muted hover-text-white">
                        <i class="bi bi-twitter" style="font-size: 1.2rem;"></i>
                    </a>
                    <a href="#" class="text-muted hover-text-white">
                        <i class="bi bi-linkedin" style="font-size: 1.2rem;"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <h6 class="fw-semibold mb-3">Plataforma</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo $base_url; ?>index.php?page=explore_orders" class="text-muted text-decoration-none hover-text-white">Explorar Serviços</a></li>
                    <li class="mb-2"><a href="<?php echo $base_url; ?>index.php?page=create_order" class="text-muted text-decoration-none hover-text-white">Criar Serviço</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="mb-2"><a href="<?php echo $base_url; ?>index.php?page=profile" class="text-muted text-decoration-none hover-text-white">Meu Perfil</a></li>
                        <li class="mb-2"><a href="<?php echo $base_url; ?>index.php?page=chat" class="text-muted text-decoration-none hover-text-white">Mensagens</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="col-md-3 mb-4">
                <h6 class="fw-semibold mb-3">Suporte</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none hover-text-white">Central de Ajuda</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none hover-text-white">Termos de Uso</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none hover-text-white">Privacidade</a></li>
                    <li class="mb-2"><a href="#" class="text-muted text-decoration-none hover-text-white">Contato</a></li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4 border-secondary">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> EconomiaShare. Todos os direitos reservados.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted mb-0">Desenvolvido com <i class="bi bi-heart-fill text-danger"></i> para conectar pessoas</p>
            </div>
        </div>
    </div>
</footer>

<style>
.hover-text-white:hover {
    color: white !important;
}
</style>

<?php
$title = '404 - Página não encontrada';
ob_start();
?>

<div class="error-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="error-content">
                    <h1 class="error-code">404</h1>
                    <h2 class="error-title">Página não encontrada</h2>
                    <p class="error-description">
                        A página que você está procurando não existe ou foi removida.
                    </p>
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>
                        Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8fafc;
}

.error-code {
    font-size: 6rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0;
}

.error-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 1rem;
}

.error-description {
    font-size: 1.1rem;
    color: var(--secondary-color);
    margin-bottom: 2rem;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

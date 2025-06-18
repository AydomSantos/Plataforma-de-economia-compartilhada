
<?php
require_once __DIR__ . '/../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar informações do usuário
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Buscar contratos com valores
$contracts_query = "
    SELECT c.*, s.title as service_title, 
           CASE WHEN c.provider_id = ? THEN 'receber' ELSE 'pagar' END as payment_type,
           CASE WHEN c.provider_id = ? THEN u_client.name ELSE u_provider.name END as other_party
    FROM contracts c
    JOIN services s ON c.service_id = s.id
    LEFT JOIN users u_client ON c.client_id = u_client.id
    LEFT JOIN users u_provider ON c.provider_id = u_provider.id
    WHERE (c.provider_id = ? OR c.client_id = ?) 
    AND c.status IN ('aceito', 'em_andamento', 'concluido')
    AND c.agreed_price IS NOT NULL
    ORDER BY c.created_at DESC
";
$contracts_stmt = $conn->prepare($contracts_query);
$contracts_stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$contracts = $contracts_stmt->fetchAll();

// Calcular totais
$total_a_receber = 0;
$total_a_pagar = 0;
$total_recebido = 0;
$total_pago = 0;

foreach ($contracts as $contract) {
    $valor = $contract['agreed_price'];
    if ($contract['status'] == 'concluido') {
        if ($contract['payment_type'] == 'receber') {
            $total_recebido += $valor;
        } else {
            $total_pago += $valor;
        }
    } else {
        if ($contract['payment_type'] == 'receber') {
            $total_a_receber += $valor;
        } else {
            $total_a_pagar += $valor;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0">
                    <i class="bi bi-wallet2"></i> Financeiro
                </h1>
                <p class="text-muted">Gerencie seus pagamentos e recebimentos</p>
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">R$ <?php echo number_format($total_recebido, 2, ',', '.'); ?></h4>
                                <p class="card-text">Total Recebido</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-down-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">R$ <?php echo number_format($total_a_receber, 2, ',', '.'); ?></h4>
                                <p class="card-text">A Receber</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-down fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></h4>
                                <p class="card-text">Total Pago</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-up-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title">R$ <?php echo number_format($total_a_pagar, 2, ',', '.'); ?></h4>
                                <p class="card-text">A Pagar</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-arrow-up fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações de Pagamento -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-credit-card"></i> Configurações de Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <h6>Métodos de Pagamento</h6>
                                <div class="list-group mb-3">
                                    <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#pixModal">
                                        <i class="bi bi-qr-code text-primary"></i>
                                        <strong>PIX</strong>
                                        <span class="text-muted float-end">Instant</span>
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#cardModal">
                                        <i class="bi bi-credit-card text-success"></i>
                                        <strong>Cartão de Crédito/Débito</strong>
                                        <span class="text-muted float-end">Mercado Pago</span>
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#paypalModal">
                                        <i class="bi bi-paypal text-info"></i>
                                        <strong>PayPal</strong>
                                        <span class="text-muted float-end">Internacional</span>
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#boletoModal">
                                        <i class="bi bi-file-text text-warning"></i>
                                        <strong>Boleto Bancário</strong>
                                        <span class="text-muted float-end">3 dias úteis</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <h6>Dados para Recebimento</h6>
                                <form id="paymentConfigForm">
                                    <div class="mb-3">
                                        <label for="bank_account" class="form-label">Conta Bancária</label>
                                        <input type="text" class="form-control" id="bank_account" placeholder="Banco, Agência, Conta">
                                    </div>
                                    <div class="mb-3">
                                        <label for="pix_key" class="form-label">Chave PIX</label>
                                        <input type="text" class="form-control" id="pix_key" placeholder="CPF, E-mail, Telefone ou Chave">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Salvar Configurações
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Histórico de Transações -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul"></i> Histórico de Transações
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($contracts)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-file-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Nenhuma transação encontrada</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Serviço</th>
                                            <th>Contraparte</th>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contracts as $contract): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($contract['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($contract['service_title']); ?></td>
                                                <td><?php echo htmlspecialchars($contract['other_party']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $contract['payment_type'] == 'receber' ? 'success' : 'primary'; ?>">
                                                        <?php echo $contract['payment_type'] == 'receber' ? 'Receber' : 'Pagar'; ?>
                                                    </span>
                                                </td>
                                                <td>R$ <?php echo number_format($contract['agreed_price'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($contract['status']) {
                                                            'aceito' => 'info',
                                                            'em_andamento' => 'warning',
                                                            'concluido' => 'success',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $contract['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($contract['status'] == 'concluido' && $contract['payment_type'] == 'pagar'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="processPayment(<?php echo $contract['id']; ?>, <?php echo $contract['agreed_price']; ?>)">
                                                            <i class="bi bi-credit-card"></i> Pagar
                                                        </button>
                                                    <?php elseif ($contract['status'] == 'concluido' && $contract['payment_type'] == 'receber'): ?>
                                                        <span class="text-success">
                                                            <i class="bi bi-check-circle"></i> Recebido
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Aguardando</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal PIX -->
    <div class="modal fade" id="pixModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-qr-code"></i> Pagamento via PIX
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div id="qr-code" class="mb-3">
                            <!-- QR Code será gerado aqui -->
                            <div class="bg-light p-4 rounded">
                                <i class="bi bi-qr-code" style="font-size: 5rem;"></i>
                                <p class="mt-2">QR Code do PIX</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="pix-code" class="form-label">Código PIX Copia e Cola</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="pix-code" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyPixCode()">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cartão -->
    <div class="modal fade" id="cardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card"></i> Pagamento com Cartão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="cardForm">
                        <div class="mb-3">
                            <label for="card-number" class="form-label">Número do Cartão</label>
                            <input type="text" class="form-control" id="card-number" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="card-expiry" class="form-label">Validade</label>
                                <input type="text" class="form-control" id="card-expiry" placeholder="MM/AA">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="card-cvc" class="form-label">CVC</label>
                                <input type="text" class="form-control" id="card-cvc" placeholder="123">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="card-name" class="form-label">Nome no Cartão</label>
                            <input type="text" class="form-control" id="card-name" placeholder="Nome como no cartão">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-lock"></i> Pagar com Segurança
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal PayPal -->
    <div class="modal fade" id="paypalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-paypal"></i> Pagamento via PayPal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <p>Você será redirecionado para o PayPal para completar o pagamento.</p>
                        <button class="btn btn-info btn-lg">
                            <i class="bi bi-paypal"></i> Continuar no PayPal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Boleto -->
    <div class="modal fade" id="boletoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text"></i> Pagamento via Boleto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        O boleto tem vencimento em 3 dias úteis. Após o pagamento, a compensação pode levar até 2 dias úteis.
                    </div>
                    <button class="btn btn-warning w-100">
                        <i class="bi bi-download"></i> Gerar Boleto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function processPayment(contractId, amount) {
            // Aqui você implementaria a lógica de pagamento
            alert(`Processando pagamento de R$ ${amount.toFixed(2).replace('.', ',')} para o contrato ${contractId}`);
        }

        function copyPixCode() {
            const pixCode = document.getElementById('pix-code');
            pixCode.select();
            document.execCommand('copy');
            alert('Código PIX copiado!');
        }

        // Configurar formulário de pagamento
        document.getElementById('paymentConfigForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Configurações salvas com sucesso!');
        });

        // Configurar formulário de cartão
        document.getElementById('cardForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Processando pagamento...');
        });
    </script>
</body>
</html>

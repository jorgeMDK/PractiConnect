<?php
session_start();

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'empresa') {
    header('Location: login.php');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'PractiConnect';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get company data
$stmt = $pdo->prepare("
    SELECT u.*, em.nombre_empresa, em.descripcion, em.telefono_contacto 
    FROM usuarios u 
    JOIN empresas em ON u.id = em.id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();

// Get company's internship offers
$stmt = $pdo->prepare("
    SELECT * FROM ofertas_practica 
    WHERE empresa_id = ? 
    ORDER BY fecha_publicacion DESC
");
$stmt->execute([$_SESSION['user_id']]);
$offers = $stmt->fetchAll();

// Get applications for company's offers
$stmt = $pdo->prepare("
    SELECT p.*, op.titulo, u.nombre as estudiante_nombre, u.correo as estudiante_email, e.universidad, e.carrera, e.semestre
    FROM postulaciones p 
    JOIN ofertas_practica op ON p.oferta_id = op.id 
    JOIN usuarios u ON p.estudiante_id = u.id
    JOIN estudiantes e ON u.id = e.id
    WHERE op.empresa_id = ? 
    ORDER BY p.fecha_postulacion DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_status') {
        $application_id = $_POST['application_id'];
        $new_status = $_POST['new_status'];
        
        try {
            $stmt = $pdo->prepare("UPDATE postulaciones SET estado = ? WHERE id = ?");
            $stmt->execute([$new_status, $application_id]);
            $success_message = "Estado actualizado exitosamente.";
        } catch(PDOException $e) {
            $error_message = "Error al actualizar el estado: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empresa - PractiConnect</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1rem 0;
        }
        
        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #4fc3f7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .logout-btn {
            background: #4fc3f7;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .dashboard-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .sidebar {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .main-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #1e3c72;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1e3c72, #4fc3f7);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .offer-card {
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .offer-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .offer-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .offer-title {
            color: #1e3c72;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .offer-details {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #4fc3f7;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #29b6f6;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .application-card {
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .student-info h4 {
            color: #1e3c72;
            margin-bottom: 0.5rem;
        }
        
        .student-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .status-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 0.5rem;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e1e5e9;
            margin-bottom: 2rem;
        }
        
        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            border-bottom-color: #4fc3f7;
            color: #4fc3f7;
            font-weight: 600;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .success-message {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #2e7d32;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #c62828;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="dashboard-nav">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($company['nombre_empresa'], 0, 1)); ?>
                    </div>
                    <div>
                        <div>Bienvenido, <?php echo htmlspecialchars($company['nombre_empresa']); ?></div>
                        <small>Panel de Empresa</small>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </div>
        </header>
        
        <div class="dashboard-content">
            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-grid">
                <aside class="sidebar">
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-building"></i>
                            Perfil de Empresa
                        </h3>
                        <div class="profile-info">
                            <label>Empresa:</label>
                            <span><?php echo htmlspecialchars($company['nombre_empresa']); ?></span>
                        </div>
                        <div class="profile-info">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($company['correo']); ?></span>
                        </div>
                        <div class="profile-info">
                            <label>Teléfono:</label>
                            <span><?php echo htmlspecialchars($company['telefono_contacto']); ?></span>
                        </div>
                        <div class="profile-info">
                            <label>Descripción:</label>
                            <span><?php echo htmlspecialchars($company['descripcion']); ?></span>
                        </div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($offers); ?></div>
                            <div>Ofertas Activas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($applications); ?></div>
                            <div>Aplicaciones</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count(array_filter($applications, function($app) { return $app['estado'] === 'pendiente'; })); ?></div>
                            <div>Pendientes</div>
                        </div>
                    </div>
                    
                    <a href="nueva_oferta.php" class="btn-primary" style="width: 100%; text-align: center; margin-top: 1rem;">
                        <i class="fas fa-plus"></i>
                        Publicar Nueva Oferta
                    </a>
                </aside>
                
                <main class="main-content">
                    <div class="tabs">
                        <div class="tab active" onclick="showTab('offers')">
                            <i class="fas fa-briefcase"></i>
                            Mis Ofertas
                        </div>
                        <div class="tab" onclick="showTab('applications')">
                            <i class="fas fa-users"></i>
                            Aplicaciones
                        </div>
                    </div>
                    
                    <div id="offers" class="tab-content active">
                        <h2 class="section-title">
                            <i class="fas fa-briefcase"></i>
                            Mis Ofertas de Práctica
                        </h2>
                        
                        <?php if (empty($offers)): ?>
                            <div class="empty-state">
                                <i class="fas fa-briefcase"></i>
                                <h3>No tienes ofertas publicadas</h3>
                                <p>Comienza publicando tu primera oferta de práctica para atraer talento universitario.</p>
                                <a href="nueva_oferta.php" class="btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Publicar Oferta
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($offers as $offer): ?>
                                <div class="offer-card">
                                    <div class="offer-header">
                                        <div>
                                            <div class="offer-title"><?php echo htmlspecialchars($offer['titulo']); ?></div>
                                            <div class="company-name"><?php echo htmlspecialchars($company['nombre_empresa']); ?></div>
                                        </div>
                                        <div>
                                            <a href="editar_oferta.php?id=<?php echo $offer['id']; ?>" class="btn-secondary">
                                                <i class="fas fa-edit"></i>
                                                Editar
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <p><?php echo htmlspecialchars(substr($offer['descripcion'], 0, 150)) . '...'; ?></p>
                                    
                                    <div class="offer-details">
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($offer['ubicacion']); ?>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($offer['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($offer['fecha_fin'])); ?>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-laptop-house"></i>
                                            <?php echo ucfirst($offer['modalidad']); ?>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-users"></i>
                                            <?php 
                                            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM postulaciones WHERE oferta_id = ?");
                                            $stmt->execute([$offer['id']]);
                                            $count = $stmt->fetch()['count'];
                                            echo $count . ' aplicaciones';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div id="applications" class="tab-content">
                        <h2 class="section-title">
                            <i class="fas fa-users"></i>
                            Aplicaciones Recibidas
                        </h2>
                        
                        <?php if (empty($applications)): ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h3>No hay aplicaciones aún</h3>
                                <p>Las aplicaciones de los estudiantes aparecerán aquí cuando publiques ofertas.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($applications as $application): ?>
                                <div class="application-card">
                                    <div class="application-header">
                                        <div class="student-info">
                                            <h4><?php echo htmlspecialchars($application['estudiante_nombre']); ?></h4>
                                            <div class="student-details">
                                                <div><?php echo htmlspecialchars($application['estudiante_email']); ?></div>
                                                <div><?php echo htmlspecialchars($application['universidad']); ?> - <?php echo htmlspecialchars($application['carrera']); ?> (Semestre <?php echo $application['semestre']; ?>)</div>
                                                <div><strong>Oferta:</strong> <?php echo htmlspecialchars($application['titulo']); ?></div>
                                            </div>
                                        </div>
                                        <div>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                <select name="new_status" class="status-select">
                                                    <option value="pendiente" <?php echo $application['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="aceptado" <?php echo $application['estado'] == 'aceptado' ? 'selected' : ''; ?>>Aceptado</option>
                                                    <option value="rechazado" <?php echo $application['estado'] == 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                                                </select>
                                                <button type="submit" class="btn-primary">
                                                    <i class="fas fa-save"></i>
                                                    Actualizar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <?php if ($application['mensaje']): ?>
                                        <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                                            <strong>Mensaje del estudiante:</strong>
                                            <p><?php echo nl2br(htmlspecialchars($application['mensaje'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
                                        <i class="fas fa-calendar"></i>
                                        Aplicado: <?php echo date('d/m/Y H:i', strtotime($application['fecha_postulacion'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html> 
<?php
session_start();
include(__DIR__ . '/config/config.php');

try {
    $results = [];
    $table = isset($_GET['table']) ? $_GET['table'] : 'analysis_results';
    
    if (isset($_SESSION['user_id'])) {
        // If the user is logged in, query records corresponding to user_id
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll();
    } else {
        // If the user is not logged in, query records corresponding to session_id
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE session_id = ? ORDER BY created_at DESC");
        $stmt->execute([session_id()]);
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Analysis Records</title>
    <link rel="stylesheet" href="assets/css/style3.css">
    <script>
        function confirmDelete(table, id) {
            if (confirm("Are you sure you want to delete this record?")) {
                // User confirms deletion, submit the form
                var form = document.createElement('form');
                form.action = 'delete_record.php';
                form.method = 'POST';
                form.style.display = 'none';
                
                var tableInput = document.createElement('input');
                tableInput.type = 'hidden';
                tableInput.name = 'table';
                tableInput.value = table;
                
                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                
                form.appendChild(tableInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body>
    <h2>Analysis History</h2>
    
    <!-- Add buttons to switch between different tables -->
    <div class="div-button-container">
        <a href="view_results2.php?table=analysis_results">All Analysis Records</a> | 
        <a href="conservation_results.php">Conservation Analysis Records</a> | 
        <a href="generate_similarity_matrix_results.php">Similarity Matrix Records</a> | 
        <a href="multiple_sequence_alignment_results.php">Multiple Sequence Alignment Records</a> | 
        <a href="pattern_scan_results.php">Pattern Scan Records</a>
        <a href="index2.php">Return to Home</a>
    </div>

    <?php if (empty($results)): ?>
        <p>No records found<?php if (!isset($_SESSION['user_id'])) echo ", please log in to view account records"; ?></p>
    <?php else: ?>
        <table>
            <tr>
                <th>Timestamp</th>
                <th>Protein Family</th>
                <th>Taxonomy</th>
                <th>Actions</th>
                <th>Delete</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= htmlspecialchars($row['protein_family']) ?></td>
                <td><?= htmlspecialchars($row['taxonomy']) ?></td>
                <td>
                    <?php if (!empty($row['fasta_file'])): ?>
                        <a href="<?= htmlspecialchars($row['fasta_file']) ?>">FASTA Sequence</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['patterns_file'])): ?>
                        <a href="<?= htmlspecialchars($row['patterns_file']) ?>">Pattern Scan Results</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['plotcon_file'])): ?>
                        <a href="<?= htmlspecialchars($row['plotcon_file']) ?>">Conservation Analysis Plot</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['aligned_file'])): ?>
                        <a href="<?= htmlspecialchars($row['aligned_file']) ?>">Multiple Sequence Alignment Results</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['similarity_matrix'])): ?>
                        <a href="<?= htmlspecialchars($row['similarity_matrix']) ?>">Similarity Matrix</a><br>
                    <?php endif; ?>
                    <?php if (!empty($row['heatmap_file'])): ?>
                        <a href="<?= htmlspecialchars($row['heatmap_file']) ?>">Similarity Heatmap</a>
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="confirmDelete('<?= $table ?>', <?= $row['id'] ?>)">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>

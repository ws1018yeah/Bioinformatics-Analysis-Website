<?php
session_start();
include(__DIR__ . '/config/config.php');

try {
    $results = [];
    
    if (isset($_SESSION['user_id'])) {
        // If the user is logged in, query the records corresponding to the user_id
        $stmt = $pdo->prepare("SELECT * FROM multiple_sequence_alignment_results WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll();
    } else {
        // If the user is not logged in, query the records corresponding to the session_id
        $stmt = $pdo->prepare("SELECT * FROM multiple_sequence_alignment_results WHERE session_id = ? ORDER BY created_at DESC");
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
    <title>Multiple Sequence Alignment Records</title>
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
    <h2>Multiple Sequence Alignment Records</h2>
    
    <div class="div-button-container">
        <a href="view_results2.php">Back to All Analysis Records</a>
        <a href="index2.php">Back to Homepage</a>
    </div>

    <?php if (empty($results)): ?>
        <p>No multiple sequence alignment records found<?php if (!isset($_SESSION['user_id'])) echo ", please log in to view your account records"; ?></p>
    <?php else: ?>
        <table>
            <tr>
                <th>Time</th>
                <th>FASTA Sequence</th>
                <th>Multiple Sequence Alignment Results</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <?php if (!empty($row['fasta_file'])): ?>
                        <a href="<?= htmlspecialchars($row['fasta_file']) ?>" target="_blank">FASTA Sequence</a>
                    <?php else: ?>
                        <span>FASTA Sequence not provided</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($row['aligned_file'])): ?>
                        <a href="<?= htmlspecialchars($row['aligned_file']) ?>" target="_blank">Multiple Sequence Alignment Results</a>
                    <?php else: ?>
                        <span>Multiple Sequence Alignment Results not provided</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="confirmDelete('multiple_sequence_alignment_results', <?= $row['id'] ?>)">Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>

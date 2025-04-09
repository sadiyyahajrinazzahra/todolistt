<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['edit_subtask'])) {
    $subtaskid = $_POST['subtaskid'];
    $subtask_label = $_POST['subtask_label'];
    $subtask_date = $_POST['subtask_date'];
    $subtask_priority = $_POST['subtask_priority'];

    $q_update_sub = "UPDATE subtasks SET subtask_label = :subtask_label, subtask_date = :subtask_date, subtask_priority = :subtask_priority WHERE subtask_id = :subtaskid";
    $stmt = $pdo->prepare($q_update_sub);
    $stmt->execute([
        ':subtaskid' => $subtaskid,
        ':subtask_label' => $subtask_label,
        ':subtask_date' => $subtask_date,
        ':subtask_priority' => $subtask_priority
    ]);
    header('Location: index.php');
}

if (isset($_GET['subtaskid'])) {
    $subtaskid = $_GET['subtaskid'];
    $q_select = "SELECT * FROM subtasks WHERE subtask_id = :subtaskid";
    $stmt = $pdo->prepare($q_select);
    $stmt->execute([':subtaskid' => $subtaskid]);
    $subtask = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subtask</title>
    <link rel="stylesheet" href="editsub.css">
</head>
<body>

<div class="container">
    <div class="header">Edit Subtask</div>
    <div class="content">
        <form action="" method="post">
            <input type="hidden" name="subtaskid" value="<?= $subtask['subtask_id'] ?>">
            
            <label for="subtask_label">Nama Subtask:</label>
            <input type="text" id="subtask_label" name="subtask_label" class="input-control" 
                   value="<?= htmlspecialchars($subtask['subtask_label']) ?>" required>

            <label for="subtask_date">Tanggal:</label>
            <input type="date" id="subtask_date" name="subtask_date" class="input-control" 
                   value="<?= $subtask['subtask_date'] ?>" required>

            <label for="subtask_priority">Prioritas:</label>
            <select id="subtask_priority" name="subtask_priority" class="input-control">
                <option value="Low" <?= ($subtask['subtask_priority'] == 'Low') ? 'selected' : '' ?>>Low</option>
                <option value="Medium" <?= ($subtask['subtask_priority'] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                <option value="High" <?= ($subtask['subtask_priority'] == 'High') ? 'selected' : '' ?>>High</option>
            </select>

            <div class="button-container">
                <a href="index.php" class="back-link">Kembali</a>
                <button type="submit" name="edit_subtask">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

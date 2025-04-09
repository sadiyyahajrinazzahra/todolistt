<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['edit_task'])) {
    $taskid = $_POST['taskid'];
    $tasklabel = $_POST['tasklabel'];
    $taskdate = $_POST['taskdate'];

    $q_update = "UPDATE tasks SET tasklabel = :tasklabel, taskdate = :taskdate WHERE taskid = :taskid";
    $stmt = $pdo->prepare($q_update);
    $stmt->execute([
        ':taskid' => $taskid,
        ':tasklabel' => $tasklabel,
        ':taskdate' => $taskdate
    ]);
    header('Location: index.php');
}

if (isset($_GET['taskid'])) {
    $taskid = $_GET['taskid'];
    $q_select = "SELECT * FROM tasks WHERE taskid = :taskid";
    $stmt = $pdo->prepare($q_select);
    $stmt->execute([':taskid' => $taskid]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Edit Task</title>
    <link rel="stylesheet" href="edittask.css">
</head>
<body>

<div class="container">
    <div class="header">Edit Task</div>
    <div class="content">
        <form action="" method="post">
            <input type="hidden" name="taskid" value="<?= $task['taskid'] ?>">
            
            <label for="tasklabel">Nama Tugas:</label>
            <input type="text" id="tasklabel" name="tasklabel" class="input-control" 
                   value="<?= htmlspecialchars($task['tasklabel']) ?>" required>

            <label for="taskdate">Tanggal:</label>
            <input type="date" id="taskdate" name="taskdate" class="input-control" 
                   value="<?= $task['taskdate'] ?>" required>

            <div class="button-container">
                <a href="index.php" class="back-link">Kembali</a>
                <button type="submit" name="edit_task">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>


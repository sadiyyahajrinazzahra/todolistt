<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Tambah Task
if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $date = $_POST['taskdate'];

    $stmt = $pdo->prepare("INSERT INTO tasks (tasklabel, taskstatus, taskdate, usersid) 
                           VALUES (:task, 'open', :taskdate, :userid)");
    $stmt->execute([
        ':task' => $task,
        ':taskdate' => $date,
        ':userid' => $user_id
    ]);
    header('Location: index.php');
}

// Tambah Subtask
if (isset($_POST['add_subtask'])) {
    $subtask = $_POST['subtask'];
    $subtask_date = $_POST['subtask_date'];
    $subtask_priority = $_POST['subtask_priority'];
    $taskid = $_POST['taskid'];

    $check = $pdo->prepare("SELECT * FROM tasks WHERE taskid = :taskid AND usersid = :userid");
    $check->execute([':taskid' => $taskid, ':userid' => $user_id]);
    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("INSERT INTO subtasks (taskid, subtask_label, subtask_date, subtask_priority, subtask_status) 
                               VALUES (:taskid, :subtask, :subtask_date, :subtask_priority, 'open')");
        $stmt->execute([
            ':taskid' => $taskid,
            ':subtask' => $subtask,
            ':subtask_date' => $subtask_date,
            ':subtask_priority' => $subtask_priority
        ]);
    }
    header('Location: index.php');
}

// Hapus Task
if (isset($_GET['delete_task'])) {
    $taskid = $_GET['delete_task'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE taskid = :taskid AND usersid = :userid");
    $stmt->execute([':taskid' => $taskid, ':userid' => $user_id]);
    header('Location: index.php');
}

// Hapus Subtask
if (isset($_GET['delete_subtask'])) {
    $subtask_id = $_GET['delete_subtask'];
    $stmt = $pdo->prepare("DELETE subtasks FROM subtasks 
                           JOIN tasks ON subtasks.taskid = tasks.taskid 
                           WHERE subtasks.subtask_id = :subtaskid AND tasks.usersid = :userid");
    $stmt->execute([':subtaskid' => $subtask_id, ':userid' => $user_id]);
    header('Location: index.php');
}

// Update status subtask (completed/open)
if (isset($_POST['subtaskid']) && isset($_POST['mark_done'])) {
    $subtask_id = $_POST['subtaskid'];
    $new_status = $_POST['mark_done'] == '1' ? 'completed' : 'open';

    $stmt = $pdo->prepare("SELECT subtasks.* FROM subtasks 
                           JOIN tasks ON subtasks.taskid = tasks.taskid 
                           WHERE subtasks.subtask_id = :subtaskid AND tasks.usersid = :userid");
    $stmt->execute([':subtaskid' => $subtask_id, ':userid' => $user_id]);

    if ($stmt->rowCount() > 0) {
        $update = $pdo->prepare("UPDATE subtasks SET subtask_status = :status WHERE subtask_id = :subtaskid");
        $update->execute([':status' => $new_status, ':subtaskid' => $subtask_id]);
    }
    header('Location: index.php');
    exit();
}

// Ambil semua task
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE usersid = :userid ORDER BY taskid DESC");
$stmt->execute([':userid' => $user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua subtask
$stmt = $pdo->prepare("SELECT subtasks.* FROM subtasks 
                       JOIN tasks ON subtasks.taskid = tasks.taskid 
                       WHERE tasks.usersid = :userid 
                       ORDER BY subtasks.taskid DESC, subtasks.subtask_id DESC");
$stmt->execute([':userid' => $user_id]);
$subtasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_subtasks = [];
foreach ($subtasks as $subtask) {
    $grouped_subtasks[$subtask['taskid']][] = $subtask;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>To Do List</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="title">
            <i class='bx bx-sun'></i>
            <span>To Do List</span>
        </div>
        <div class="description">
            <?= date("l, d M Y") ?>
        </div>
        <div class="user-info">
            <p>Halo, <?= htmlspecialchars($username) ?>!</p>
            <div class="logout">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <form action="" method="post">
                <input type="text" name="task" class="input-control" placeholder="Tambahkan Tugas" required>
                <input type="date" name="taskdate" class="input-control" required>
                <div class="text-right">
                    <button type="submit" name="add_task">Tambah Tugas</button>
                </div>
            </form>
        </div>

        <div class="card">
            <form action="" method="post">
                <select name="taskid" class="input-control" required>
                    <option value="">-- Pilih Task --</option>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?= $task['taskid'] ?>"><?= htmlspecialchars($task['tasklabel']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="subtask" class="input-control" placeholder="Tambahkan Subtask" required>
                <input type="date" name="subtask_date" class="input-control" required>
                <select name="subtask_priority" class="input-control" required>
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
                <button type="submit" name="add_subtask">Tambah Subtask</button>
            </form>
        </div>

        <?php if (count($tasks) > 0): ?>
            <?php foreach ($tasks as $task): ?>
                <div class="card">
                    <div class="task-item">
                        <div>
                            <span><?= htmlspecialchars($task['tasklabel']) ?></span> - 
                            <span><?= date('d M Y', strtotime($task['taskdate'])) ?></span>
                        </div>
                        <div>
                            <a href="edittask.php?taskid=<?= $task['taskid'] ?>">edit</a>
                            <a href="?delete_task=<?= $task['taskid'] ?>" class="text-red" onclick="return confirm('Yakin ingin menghapus?')"><i class="bx bx-trash"></i></a>
                        </div>
                    </div>

                    <?php if (!empty($grouped_subtasks[$task['taskid']])): ?>
                        <?php foreach ($grouped_subtasks[$task['taskid']] as $subtask): ?>
                            <form method="post" style="display: flex; align-items: center; gap: 10px; margin-left: 20px;">
                                <input type="hidden" name="subtaskid" value="<?= $subtask['subtask_id'] ?>">
                                <input type="hidden" name="mark_done" value="0">
                                <input type="checkbox" name="mark_done" value="1" onchange="this.form.submit()" <?= $subtask['subtask_status'] === 'completed' ? 'checked' : '' ?>>
                                <span style="<?= $subtask['subtask_status'] === 'completed' ? 'text-decoration: line-through; color: gray;' : '' ?>">
                                    - <?= htmlspecialchars($subtask['subtask_label']) ?> (<?= date('d M Y', strtotime($subtask['subtask_date'])) ?>)
                                </span>
                                <span class="text-orange">Prioritas: <?= htmlspecialchars($subtask['subtask_priority']) ?></span>
                                <a href="editsub.php?subtaskid=<?= $subtask['subtask_id'] ?>">edit</a>
                                <a href="?delete_subtask=<?= $subtask['subtask_id'] ?>" class="text-red" onclick="return confirm('Hapus subtask?')"><i class="bx bx-trash"></i></a>
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">Tidak ada tugas tersedia</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

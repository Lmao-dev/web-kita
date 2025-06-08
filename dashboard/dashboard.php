<?php
include "../lib/db.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Hapus otomatis event yang sudah lewat
mysqli_query($connection, "DELETE FROM events WHERE event_date < CURDATE()");

// Folder Upload
$upload_folder = '../uploads/';
$upload_status = is_dir($upload_folder) && is_writable($upload_folder);

// Tambah Event
if (isset($_POST["add_event"])) {
    $event_name = mysqli_real_escape_string($connection, $_POST["event_name"]);
    $event_description = mysqli_real_escape_string($connection, $_POST["event_description"]);
    $event_location = mysqli_real_escape_string($connection, $_POST["event_location"]);
    $event_date = mysqli_real_escape_string($connection, $_POST["event_date"]);
    $event_time = mysqli_real_escape_string($connection, $_POST["event_time"]);
    $event_host = mysqli_real_escape_string($connection, $_POST["event_host"]);

    $imageName = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0 && $upload_status) {
        $ext = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
        $imageName = 'event_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_folder . $imageName);
    }

    $query = mysqli_query($connection, "INSERT INTO events (event_name, event_description, location, event_date, event_time, event_image, event_host)
        VALUES ('$event_name', '$event_description', '$event_location', '$event_date', '$event_time', '$imageName', '$event_host')");

    $notif = $query 
        ? '<div class="alert alert-success mt-3">✅ Berhasil tambah data</div>' 
        : '<div class="alert alert-danger mt-3">❌ Gagal tambah data</div>';
}

// Hapus Event
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $query = mysqli_query($connection, "DELETE FROM events WHERE id_event=$id");
    $notifDelete = $query
        ? '<div class="alert alert-success mt-3">🗑️ Berhasil hapus data</div>'
        : '<div class="alert alert-danger mt-3">❌ Gagal menghapus data</div>';
}

$events = mysqli_query($connection, "SELECT * FROM events ORDER BY event_date ASC, event_time ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Event</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body style="background-color: #f4f6f9;">
<div class="container mt-4">
  <h2 class="text-center fw-bold text-dark mb-4">📊 Dashboard Event</h2>

  <?php if (isset($notif)) echo $notif; ?>
  <?php if (isset($notifDelete)) echo $notifDelete; ?>

  <?php if (!$upload_status): ?>
    <div class="alert alert-warning">
      ⚠️ Folder <code>uploads/</code> tidak bisa ditulis. Periksa izin folder agar gambar bisa diupload.
    </div>
  <?php endif; ?>

  <div class="row">
    <!-- Kolom Event -->
    <div class="col-md-8">
      <div class="card border-0 shadow-lg">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalevents">➕ Tambah Event</button>
            <a href="../index.php" class="btn btn-dark">🌐 Lihat Website</a>
          </div>

          <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary text-center">
              <tr>
                <th>Gambar</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Lokasi</th>
                <th>Tanggal & Waktu</th>
                <th>Pembawa Acara</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($events) > 0): ?>
                <?php while($event = mysqli_fetch_assoc($events)): ?>
                  <tr>
                    <td class="text-center">
                      <?php if (!empty($event['event_image'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($event['event_image']) ?>" width="80" alt="Event">
                      <?php else: ?>
                        <span class="text-muted">-</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($event['event_name']) ?></td>
                    <td><?= htmlspecialchars($event['event_description']) ?></td>
                    <td><?= htmlspecialchars($event['location']) ?></td>
                    <td><?= htmlspecialchars($event['event_date']) ?> <?= htmlspecialchars(substr($event['event_time'], 0, 5)) ?></td>
                    <td><?= htmlspecialchars($event['event_host']) ?></td>
                    <td class="text-center">
                      <div class="btn-group">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">⚙️</button>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="edit.php?edit=<?= $event['id_event'] ?>">✏️ Edit</a></li>
                          <li><a class="dropdown-item text-danger" href="?delete=<?= $event['id_event'] ?>" onclick="return confirm('Yakin ingin menghapus event ini?')">🗑️ Hapus</a></li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted">Belum ada event.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Kolom Pendaftar -->
    <div class="col-md-4">
      <div class="card border-0 shadow-lg">
        <div class="card-body">
          <h4 class="fw-bold mb-3">📋 Pendaftar Event</h4>
          <ul class="list-group list-group-flush">
            <?php 
            $query = mysqli_query($connection,"SELECT user_event.*, events.event_name FROM user_event INNER JOIN events ON events.id_event=user_event.event_id");
            if (mysqli_num_rows($query) > 0):
              while($data = mysqli_fetch_array($query)):
            ?>
              <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                  <div class="fw-bold"><?= htmlspecialchars($data['fullname']) ?></div>
                  <div class="text-muted small"><?= htmlspecialchars($data['email']) ?></div>
                  <div><span class="badge bg-info text-dark me-2"><?= htmlspecialchars($data['event_name']) ?></span></div>
                </div>
                <span class="badge bg-success text-white p-2 rounded">🎟️ <?= htmlspecialchars($data['ticket_code']) ?></span>
              </li>
            <?php endwhile; else: ?>
              <li class="list-group-item text-center text-muted">Belum ada pendaftar.</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Event -->
<div class="modal fade" id="modalevents" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="" method="post" enctype="multipart/form-data">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold">Tambah Event</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nama Event</label><input type="text" name="event_name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="event_description" class="form-control" required></textarea></div>
          <div class="mb-3"><label class="form-label">Lokasi</label><input type="text" name="event_location" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" name="event_date" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Waktu</label><input type="time" name="event_time" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Pembawa Acara</label><input type="text" name="event_host" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Gambar</label><input type="file" name="event_image" class="form-control" accept="image/*"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="add_event" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
  setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
      el.style.transition = "opacity 0.5s ease";
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 3000);
</script>
</body>
</html>

<?php
include "../lib/db.php";

// Ambil data event berdasarkan ID
if (isset($_GET['edit'])) {
    $id = mysqli_real_escape_string($connection, $_GET['edit']);
    $result = mysqli_query($connection, "SELECT * FROM events WHERE id_event = '$id'");
    $event = mysqli_fetch_assoc($result);

    if (!$event) {
        echo "<div class='alert alert-danger'>Event tidak ditemukan!</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger'>ID event tidak diberikan!</div>";
    exit;
}

// Proses update data
if (isset($_POST["update_event"])) {
    $event_name = mysqli_real_escape_string($connection, $_POST["event_name"]);
    $event_description = mysqli_real_escape_string($connection, $_POST["event_description"]);
    $event_location = mysqli_real_escape_string($connection, $_POST["event_location"]);
    $event_date = mysqli_real_escape_string($connection, $_POST["event_date"]);
    $event_host = mysqli_real_escape_string($connection, $_POST["event_host"]);

    $query = mysqli_query($connection, "UPDATE events 
        SET event_name='$event_name', event_description='$event_description', location='$event_location', event_date='$event_date', event_host='$event_host'
        WHERE id_event='$id'");

    if ($query) {
        $notif = '<div class="alert alert-success mt-3">✅ Berhasil memperbarui event</div>';
        $result = mysqli_query($connection, "SELECT * FROM events WHERE id_event = '$id'");
        $event = mysqli_fetch_assoc($result);
    } else {
        $notif = '<div class="alert alert-danger mt-3">❌ Gagal memperbarui event</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Event</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
</head>
<body>
<div class="container">
  <div class="row mt-5">
    <div class="col-md-8 offset-md-2">
      <div class="card border-0 shadow-lg">
        <div class="card-body">
          <h3 class="text-center mb-4">✏️ Edit Event</h3>

          <?php if (isset($notif)) echo $notif; ?>

          <form action="" method="POST">
            <div class="mb-3">
              <label for="event_name" class="form-label">Nama Event</label>
              <input type="text" class="form-control" id="event_name" name="event_name" value="<?= htmlspecialchars($event['event_name']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="event_description" class="form-label">Deskripsi</label>
              <textarea class="form-control" id="event_description" name="event_description" rows="3" required><?= htmlspecialchars($event['event_description']) ?></textarea>
            </div>
            <div class="mb-3">
              <label for="event_location" class="form-label">Lokasi</label>
              <input type="text" class="form-control" id="event_location" name="event_location" value="<?= htmlspecialchars($event['location']) ?>" required>
            </div>
            <div class="mb-3">
              <label for="event_date" class="form-label">Tanggal</label>
              <input type="date" class="form-control" id="event_date" name="event_date" value="<?= $event['event_date'] ?>" required>
            </div>
            <div class="mb-3">
              <label for="event_host" class="form-label">Pembawa Acara</label>
              <input type="text" class="form-control" id="event_host" name="event_host" value="<?= htmlspecialchars($event['event_host']) ?>">
            </div>
            <div class="d-flex justify-content-between">
              <a href="dashboard.php" class="btn btn-secondary">⬅️ Kembali</a>
              <button type="submit" name="update_event" class="btn btn-success">💾 Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

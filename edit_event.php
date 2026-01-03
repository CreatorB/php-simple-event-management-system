<?php 
include 'config.php';
if($_SESSION['role'] != 'admin') header("Location: index.php");

$id_event = $_GET['id'] ?? 0;

// --- PROSES HAPUS SESI ---
if(isset($_GET['hapus_sesi'])){
    $id_sesi = $_GET['hapus_sesi'];
    // Cek apakah ada data absen di sesi ini?
    $cek = mysqli_query($conn, "SELECT * FROM attendance WHERE session_id='$id_sesi'");
    if(mysqli_num_rows($cek) > 0){
        echo "<script>alert('Gagal! Sesi ini sudah ada data absensi pesertanya. Hapus data absen dulu jika ingin menghapus sesi ini.'); window.location='edit_event.php?id=$id_event';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM event_sessions WHERE id='$id_sesi'");
        header("Location: edit_event.php?id=$id_event");
    }
    exit();
}

// --- PROSES UPDATE EVENT & SESI ---
if(isset($_POST['update_event'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tgl  = $_POST['tanggal'];
    $mode = $_POST['qr_mode'];

    // 1. Update Data Event Utama
    mysqli_query($conn, "UPDATE events SET nama_event='$nama', tanggal='$tgl', qr_mode='$mode' WHERE id='$id_event'");

    // 2. Loop Data Sesi (Update yang lama / Insert yang baru)
    $sesi_id      = $_POST['sesi_id'];      // Array ID (Kalo kosong berarti baru)
    $sesi_nama    = $_POST['sesi_nama'];    // Array Nama
    $jam_mulai    = $_POST['jam_mulai'];    // Array Jam Mulai
    $jam_selesai  = $_POST['jam_selesai'];  // Array Jam Selesai

    for($i=0; $i < count($sesi_nama); $i++){
        if(!empty($sesi_nama[$i])){
            $sid   = $sesi_id[$i]; // ID Sesi (bisa kosong atau ada isinya)
            $nm    = mysqli_real_escape_string($conn, $sesi_nama[$i]);
            $start = $jam_mulai[$i];
            $end   = $jam_selesai[$i];

            if(!empty($sid)){
                // Kalo ID ada, berarti UPDATE data lama
                mysqli_query($conn, "UPDATE event_sessions SET nama_sesi='$nm', jam_mulai='$start', jam_selesai='$end' WHERE id='$sid'");
            } else {
                // Kalo ID kosong, berarti INSERT data baru
                mysqli_query($conn, "INSERT INTO event_sessions (event_id, nama_sesi, jam_mulai, jam_selesai) VALUES ('$id_event', '$nm', '$start', '$end')");
            }
        }
    }
    
    echo "<script>alert('Perubahan Berhasil Disimpan!'); window.location='dashboard_admin.php';</script>";
}

// --- AMBIL DATA EVENT UTAMA ---
$q_event = mysqli_query($conn, "SELECT * FROM events WHERE id='$id_event'");
$data = mysqli_fetch_assoc($q_event);
if(!$data) die("Event tidak ditemukan.");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5" style="max-width: 800px;">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Edit Event: <?= $data['nama_event'] ?></h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nama Event</label>
                        <input type="text" name="nama_event" class="form-control" value="<?= $data['nama_event'] ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Mode QR</label>
                        <select name="qr_mode" class="form-select">
                            <option value="static" <?= ($data['qr_mode']=='static')?'selected':'' ?>>Statis</option>
                            <option value="dynamic" <?= ($data['qr_mode']=='dynamic')?'selected':'' ?>>Dinamis</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                
                <h5 class="mb-3">Jadwal Sesi</h5>
                <div class="alert alert-warning py-2 small">
                    <i class="fa fa-info-circle"></i> Jika mengubah jam, pastikan tidak bertabrakan dengan sesi lain.
                </div>
                
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Sesi</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th width="50px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sesiContainer">
                        <?php 
                        // Ambil Data Sesi yang ada
                        $q_sesi = mysqli_query($conn, "SELECT * FROM event_sessions WHERE event_id='$id_event'");
                        while($s = mysqli_fetch_assoc($q_sesi)): 
                        ?>
                        <tr>
                            <input type="hidden" name="sesi_id[]" value="<?= $s['id'] ?>">
                            
                            <td><input type="text" name="sesi_nama[]" class="form-control" value="<?= $s['nama_sesi'] ?>" required></td>
                            <td><input type="time" name="jam_mulai[]" class="form-control" value="<?= $s['jam_mulai'] ?>" required></td>
                            <td><input type="time" name="jam_selesai[]" class="form-control" value="<?= $s['jam_selesai'] ?>" required></td>
                            <td>
                                <a href="edit_event.php?id=<?= $id_event ?>&hapus_sesi=<?= $s['id'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin hapus sesi ini?')">X</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <button type="button" class="btn btn-success btn-sm mb-3" onclick="tambahSesi()">+ Tambah Sesi Baru</button>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="update_event" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                    <a href="dashboard_admin.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function tambahSesi(){
        // Menambah baris baru (ID Sesi dikosongkan value-nya)
        var row = `<tr>
            <input type="hidden" name="sesi_id[]" value=""> 
            <td><input type="text" name="sesi_nama[]" class="form-control" placeholder="Sesi Baru" required></td>
            <td><input type="time" name="jam_mulai[]" class="form-control" required></td>
            <td><input type="time" name="jam_selesai[]" class="form-control" required></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisJS(this)">X</button></td>
        </tr>`;
        document.getElementById('sesiContainer').insertAdjacentHTML('beforeend', row);
    }

    // Fungsi hapus baris yang baru ditambahkan (belum disimpan ke DB)
    function hapusBarisJS(btn){
        btn.closest('tr').remove();
    }
</script>
</body>
</html>
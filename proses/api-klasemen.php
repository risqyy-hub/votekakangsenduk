<?php
/**
 * ============================================================
 * SISTEM INFORMASI VOTING KAKANG SENDUK PONOROGO
 * File: proses/api-klasemen.php
 * Endpoint API Real-time Data Klasemen & Statistik Suara
 * ============================================================
 */

require_once __DIR__ . '/../config/koneksi.php';

// Set header response JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // 1. Hitung total perolehan suara
    $total_kakang_suara = 0;
    $res_kakang = $conn->query("SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Kakang'");
    if ($r = $res_kakang->fetch_assoc()) $total_kakang_suara = (int)$r['total'];

    $total_senduk_suara = 0;
    $res_senduk = $conn->query("SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Senduk'");
    if ($r = $res_senduk->fetch_assoc()) $total_senduk_suara = (int)$r['total'];

    $total_semua_suara = $total_kakang_suara + $total_senduk_suara;

    // 2. Query kontestan Kakang beserta akumulasi suara
    $query_kakang = "
        SELECT 
            k.id, k.nomor_peserta, k.nama, k.foto, k.asal_instansi, k.kategori,
            COUNT(v.id) AS total_suara
        FROM kontestan k
        LEFT JOIN voting v ON k.id = v.kontestan_id
        WHERE k.kategori = 'Kakang'
        GROUP BY k.id
        ORDER BY total_suara DESC, k.nomor_peserta ASC
    ";
    $result_kakang = $conn->query($query_kakang);
    $list_kakang = [];
    $rank_kakang = 1;

    while ($row = $result_kakang->fetch_assoc()) {
        $suara = (int)$row['total_suara'];
        $persentase = ($total_kakang_suara > 0) ? round(($suara / $total_kakang_suara) * 100, 1) : 0;
        $row['rank'] = $rank_kakang++;
        $row['total_suara'] = $suara;
        $row['persentase'] = $persentase;
        $row['foto_url'] = BASE_URL . 'uploads/' . $row['foto'];
        $list_kakang[] = $row;
    }

    // 3. Query kontestan Senduk beserta akumulasi suara
    $query_senduk = "
        SELECT 
            k.id, k.nomor_peserta, k.nama, k.foto, k.asal_instansi, k.kategori,
            COUNT(v.id) AS total_suara
        FROM kontestan k
        LEFT JOIN voting v ON k.id = v.kontestan_id
        WHERE k.kategori = 'Senduk'
        GROUP BY k.id
        ORDER BY total_suara DESC, k.nomor_peserta ASC
    ";
    $result_senduk = $conn->query($query_senduk);
    $list_senduk = [];
    $rank_senduk = 1;

    while ($row = $result_senduk->fetch_assoc()) {
        $suara = (int)$row['total_suara'];
        $persentase = ($total_senduk_suara > 0) ? round(($suara / $total_senduk_suara) * 100, 1) : 0;
        $row['rank'] = $rank_senduk++;
        $row['total_suara'] = $suara;
        $row['persentase'] = $persentase;
        $row['foto_url'] = BASE_URL . 'uploads/' . $row['foto'];
        $list_senduk[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'timestamp' => format_tanggal_indo(date('Y-m-d H:i:s'), true),
        'summary' => [
            'total_suara' => $total_semua_suara,
            'total_kakang' => $total_kakang_suara,
            'total_senduk' => $total_senduk_suara
        ],
        'kakang' => $list_kakang,
        'senduk' => $list_senduk
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memuat data klasemen: ' . $e->getMessage()
    ]);
}
exit;

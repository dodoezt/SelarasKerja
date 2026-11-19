<?php
declare(strict_types=1);

/** Daftar accommodation_id yang dicentang pelamar. */
function seeker_needs(PDO $pdo, int $seekerId): array
{
    $stmt = $pdo->prepare('SELECT accommodation_id FROM seeker_accommodations WHERE seeker_id = ?');
    $stmt->execute([$seekerId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/** Fasilitas tiap lowongan: [job_id => [accommodation_id, ...]]. */
function job_facilities_map(PDO $pdo, array $jobIds): array
{
    if ($jobIds === []) {
        return [];
    }
    $in = implode(',', array_fill(0, count($jobIds), '?'));
    $stmt = $pdo->prepare("SELECT job_id, accommodation_id FROM job_accommodations WHERE job_id IN ($in)");
    $stmt->execute($jobIds);

    $map = array_fill_keys($jobIds, []);
    foreach ($stmt->fetchAll() as $row) {
        $map[(int) $row['job_id']][] = (int) $row['accommodation_id'];
    }
    return $map;
}

/** Hitung irisan kebutuhan pelamar dengan fasilitas lowongan. */
function match_stats(array $needs, array $facilities): array
{
    $matched = array_intersect($needs, $facilities);
    $total   = count($needs);
    return [
        'matched' => $matched,
        'count'   => count($matched),
        'total'   => $total,
        'percent' => $total > 0 ? (int) round(count($matched) / $total * 100) : 0,
    ];
}

/**
 * Lowongan terbuka + skor kecocokan pelamar, terurut skor tertinggi.
 * $filter: ['q' => string, 'type' => string, 'acc' => int]
 */
function jobs_with_score(PDO $pdo, array $needs, array $filter = []): array
{
    $sql = 'SELECT j.*, c.company_name, c.location
            FROM jobs j
            JOIN companies c ON c.id = j.company_id
            WHERE j.status = ?';
    $params = ['open'];

    if (($filter['q'] ?? '') !== '') {
        $sql .= ' AND j.title LIKE ?';
        $params[] = '%' . $filter['q'] . '%';
    }
    if (($filter['type'] ?? '') !== '') {
        $sql .= ' AND j.job_type = ?';
        $params[] = $filter['type'];
    }
    if (!empty($filter['acc'])) {
        $sql .= ' AND j.id IN (SELECT job_id FROM job_accommodations WHERE accommodation_id = ?)';
        $params[] = (int) $filter['acc'];
    }

    $stmt = $pdo->prepare($sql . ' ORDER BY j.created_at DESC');
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();

    $facilities = job_facilities_map($pdo, array_map(fn ($j) => (int) $j['id'], $jobs));

    foreach ($jobs as &$job) {
        $stats = match_stats($needs, $facilities[(int) $job['id']] ?? []);
        $job['match_count']   = $stats['count'];
        $job['match_total']   = $stats['total'];
        $job['match_percent'] = $stats['percent'];
        $job['facility_ids']  = $facilities[(int) $job['id']] ?? [];
    }
    unset($job);

    usort($jobs, fn ($a, $b) => $b['match_percent'] <=> $a['match_percent']);
    return $jobs;
}

/** Nama fasilitas untuk sekumpulan id: [id => ['name'=>, 'category'=>]]. */
function accommodation_names(PDO $pdo): array
{
    $rows = $pdo->query('SELECT id, name, category FROM accommodations ORDER BY category, name')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[(int) $r['id']] = ['name' => $r['name'], 'category' => $r['category']];
    }
    return $out;
}
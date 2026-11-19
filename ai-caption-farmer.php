<?php
// ============================================================
//  AI CAPTION FARMER — Auto Farming Caption Sosmed GRATIS
//  Pakai API AI uncensored GRATIS (tanpa key, tanpa daftar)
//  Base URL : https://api.aiapifreekey.com/v1
//  Model    : free-ai (9B, uncensored)
//  Limit    : 10.000 request / 10jt token per hari (per IP)
//  Dibuat untuk : Mr Revolt — https://github.com/ImRevoltexee
// ============================================================
//  CARA PAKAI (Termux / PHP CLI):
//    php ai-caption-farmer.php
//  Hasil caption tersimpan di: hasil-caption.txt
// ============================================================

error_reporting(0);
$API = "https://api.aiapifreekey.com/v1/chat/completions";

// ---------- banner ----------
echo "\n";
echo "  ==============================================\n";
echo "   AI CAPTION FARMER — Auto Farming Caption\n";
echo "   API GRATIS, tanpa key, tanpa daftar\n";
echo "  ==============================================\n\n";

// ---------- input ----------
echo "Topik caption (contoh: meme kucing, motivasi, ai gratis): ";
$topik = trim(fgets(STDIN));
if ($topik === "") { $topik = "meme lucu"; }

echo "Mau berapa caption? (contoh: 10): ";
$jumlah = (int) trim(fgets(STDIN));
if ($jumlah < 1)  { $jumlah = 10; }
if ($jumlah > 50) { $jumlah = 50; echo "Maks 50 per jalan, ya. Di-set ke 50.\n"; }

echo "Gaya caption (1 = santai, 2 = meme/njir, 3 = jualan/promo): ";
$gaya = trim(fgets(STDIN));
$gayaText = [
    "1" => "santai dan natural seperti orang biasa nulis",
    "2" => "gaya meme, pakai kata gw, rek, njir, gaskan, singkat dan lucu",
    "3" => "gaya jualan/promo, ajak orang klik, tetap singkat",
];
$gayaPick = $gayaText[$gaya] ?? $gayaText["2"];

echo "\nLagi farming $jumlah caption soal \"$topik\"...\n";
echo "------------------------------------------------\n";

// ---------- farming loop ----------
$hasil = [];
for ($i = 1; $i <= $jumlah; $i++) {
    $prompt = "Buatkan SATU caption media sosial berbahasa Indonesia soal \"$topik\", "
        . "gaya $gayaPick. "
        . "Syarat: maksimal 2 kalimat pendek, tanpa hashtag, tanpa penjelasan, "
        . "langsung captionnya saja. Variasikan dari caption sebelumnya, jangan mengulang.";

    $payload = json_encode([
        "model"    => "free-ai",
        "messages" => [["role" => "user", "content" => $prompt]],
        "max_tokens" => 120,
        "temperature" => 0.9,
    ]);

    $ch = curl_init($API);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            "Content-Type: application/json",
            "Authorization: Bearer free",
        ],
        CURLOPT_TIMEOUT        => 60,
    ]);
    $res  = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $caption = "";
    if ($http === 200 && $res) {
        $j = json_decode($res, true);
        $caption = trim($j["choices"][0]["message"]["content"] ?? "");
    }

    if ($caption === "") {
        echo "[$i/$jumlah] GAGAL (HTTP $http), coba lagi...\n";
        $i--;               // ulangi nomor yang gagal
        sleep(2);
        continue;
    }

    // bersihkan kalau AI masih nambah tanda kutip / label
    $caption = trim($caption, "\"' \n");
    $caption = preg_replace('/^(Caption|Keterangan)\s*:\s*/i', '', $caption);

    $hasil[] = $caption;
    echo "[$i/$jumlah] $caption\n";

    // rate limit: 1 request/detik
    if ($i < $jumlah) { sleep(1); }
}

// ---------- simpan ----------
$file = "hasil-caption.txt";
file_put_contents($file, implode("\n", $hasil) . "\n");

echo "------------------------------------------------\n";
echo "SELESAI. $jumlah caption tersimpan di: $file\n";
echo "Tinggal copy-paste ke Facebook / IG / Threads, rek.\n\n";

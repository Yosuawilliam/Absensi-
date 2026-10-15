<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TwibbonContoh extends Command
{
    protected $signature = 'twibbon:contoh';

    protected $description = 'Membuat file twibbon contoh (PNG transparan) di public/uploads/twibbon/contoh.png';

    public function handle(): int
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->error('Ekstensi GD belum aktif. Jalankan: php -d extension=gd artisan twibbon:contoh');

            return self::FAILURE;
        }

        $w = 720;
        $h = 960;
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        $a = [30, 58, 138];   // biru tua
        $b = [20, 184, 166];  // teal

        $mix = function (float $t) use ($a, $b) {
            return [
                (int) ($a[0] + ($b[0] - $a[0]) * $t),
                (int) ($a[1] + ($b[1] - $a[1]) * $t),
                (int) ($a[2] + ($b[2] - $a[2]) * $t),
            ];
        };

        // Bingkai tepi dengan gradien diagonal
        $tebal = 34;
        $banner = 190;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x += 1) {
                $dalamBingkai = $x < $tebal || $x >= $w - $tebal || $y < $tebal || $y >= $h - $banner;
                if (! $dalamBingkai) {
                    // lompat ke sisi kanan bingkai agar cepat
                    $x = $w - $tebal - 1;
                    continue;
                }
                [$r, $g, $bl] = $mix((($x / $w) + ($y / $h)) / 2);
                imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, $r, $g, $bl, 8));
            }
        }

        // Sudut membulat pada jendela foto (potong ke transparan)
        $this->cekungSudut($im, $tebal, $tebal, 40, 1, 1, $mix);
        $this->cekungSudut($im, $w - $tebal - 1, $tebal, 40, -1, 1, $mix);
        $this->cekungSudut($im, $tebal, $h - $banner - 1, 40, 1, -1, $mix);
        $this->cekungSudut($im, $w - $tebal - 1, $h - $banner - 1, 40, -1, -1, $mix);

        // Tulisan banner
        $putih = imagecolorallocate($im, 255, 255, 255);
        $font = 'C:\\Windows\\Fonts\\arialbd.ttf';
        if (! is_file($font)) {
            $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        }

        if (function_exists('imagettftext') && is_file($font)) {
            $this->teksTengah($im, 'HADIR', 64, $font, $putih, $h - 112, $w);
            $this->teksTengah($im, 'UNIVERSITAS TERBUKA', 26, $font, $putih, $h - 58, $w);
        } else {
            imagestring($im, 5, $w / 2 - 20, $h - 130, 'HADIR', $putih);
            imagestring($im, 4, $w / 2 - 70, $h - 80, 'UNIVERSITAS TERBUKA', $putih);
        }

        $dir = public_path('uploads/twibbon');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        imagepng($im, $dir.'/contoh.png', 6);
        imagedestroy($im);

        $this->info('Twibbon contoh dibuat: public/uploads/twibbon/contoh.png');

        return self::SUCCESS;
    }

    private function teksTengah($im, string $teks, int $ukuran, string $font, int $warna, int $baseline, int $lebar): void
    {
        $box = imagettfbbox($ukuran, 0, $font, $teks);
        $x = (int) (($lebar - ($box[2] - $box[0])) / 2);
        imagettftext($im, $ukuran, 0, $x, $baseline, $warna, $font, $teks);
    }

    /** Mengisi sudut bingkai agar jendela foto terlihat membulat. */
    private function cekungSudut($im, int $cx, int $cy, int $r, int $dx, int $dy, callable $mix): void
    {
        $w = imagesx($im);
        $h = imagesy($im);
        for ($i = 0; $i < $r; $i++) {
            for ($j = 0; $j < $r; $j++) {
                $x = $cx + $dx * $i;
                $y = $cy + $dy * $j;
                // titik di sudut, di luar lingkaran berpusat (cx + dx*r, cy + dy*r)
                $px = $i - $r;
                $py = $j - $r;
                if ($px * $px + $py * $py > $r * $r) {
                    [$rr, $gg, $bb] = $mix((($x / $w) + ($y / $h)) / 2);
                    imagesetpixel($im, $x, $y, imagecolorallocatealpha($im, $rr, $gg, $bb, 8));
                }
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Membaca file CSV master di database/seeders/docs (pemisah titik koma, UTF-8 dengan/tanpa BOM).
 */
trait BacaCsv
{
    /**
     * Baris CSV sebagai array asosiatif berdasarkan header; nilai kosong menjadi null.
     *
     * @return list<array<string, string|null>>
     */
    protected function bacaCsv(string $namaFile): array
    {
        $path = database_path('seeders/docs/'.$namaFile);
        $handle = @fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException("File CSV tidak ditemukan: {$path}");
        }

        $header = null;
        $rows = [];

        while (($kolom = fgetcsv($handle, separator: ';', escape: '')) !== false) {
            if ($kolom === [null]) {
                continue;
            }

            $kolom = array_map(fn (?string $nilai) => ($nilai = trim((string) $nilai)) === '' ? null : $nilai, $kolom);

            if ($header === null) {
                $header = array_map(fn (?string $nama) => preg_replace('/^\x{FEFF}/u', '', (string) $nama), $kolom);

                continue;
            }

            $rows[] = array_combine($header, array_pad($kolom, count($header), null));
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Isi hanya kolom yang masih kosong pada model yang sudah ada; nilai yang sudah terisi tidak ditimpa.
     *
     * @param  array<string, mixed>  $data
     * @return bool true bila ada kolom yang diisi
     */
    protected function isiKolomKosong(Model $model, array $data): bool
    {
        foreach ($data as $kolom => $nilai) {
            if ($nilai !== null && ($model->getAttribute($kolom) === null || $model->getAttribute($kolom) === '')) {
                $model->setAttribute($kolom, $nilai);
            }
        }

        if (! $model->isDirty()) {
            return false;
        }

        $model->save();

        return true;
    }
}

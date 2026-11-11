<?php
declare(strict_types=1);

namespace App\Service;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Http\Message\UploadedFileInterface;

final class SpreadsheetUpload
{
    public static function rows(mixed $file): array
    {
        if (!$file instanceof UploadedFileInterface || $file->getError() !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('ファイルを選択してください。');
        }
        $extension = strtolower(pathinfo($file->getClientFilename() ?? '', PATHINFO_EXTENSION));
        $readerType = ['csv' => 'Csv', 'xlsx' => 'Xlsx', 'xls' => 'Xls', 'ods' => 'Ods'][$extension] ?? null;
        if (!$readerType) {
            throw new InvalidArgumentException('CSV / Excel / ODS ファイルを選択してください。');
        }
        $reader = IOFactory::createReader($readerType);
        if ($extension === 'csv') {
            $content = (string)$file->getStream();
            $reader->setInputEncoding(mb_check_encoding($content, 'UTF-8') ? 'UTF-8' : 'SJIS-win');
        }
        $book = $reader->load($file->getStream()->getMetadata('uri'));
        try {
            return $book->getSheet(0)->toArray(null, false, true, false);
        } finally {
            $book->disconnectWorksheets();
        }
    }
}

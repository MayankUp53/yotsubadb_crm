<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\LegacyCrm;
use App\Service\LegacyCrmImporter;
use App\Service\LegacyPhoneExport;
use App\Service\SpreadsheetUpload;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Http\Message\UploadedFileInterface;

class ImportsController extends AppController
{
    private function importType(): string
    {
        return $this->request->getData('type', $this->request->getQuery('type')) === 'contacts' ? 'contacts' : 'customers';
    }

    public function index()
    {
        $this->set(['type' => $this->importType(), 'warning' => 0]);
    }

    public function template()
    {
        $type = $this->importType();
        return $this->downloadTemplate(array_values(LegacyCrm::importFields($type)), $type . '_import.xlsx');
    }

    public function phoneTemplate()
    {
        return $this->downloadTemplate(['電話番号（「-」無）'], 'phone_list.xlsx');
    }

    private function downloadTemplate(array $headers, string $filename)
    {
        $book = new Spreadsheet();
        $book->getActiveSheet()->fromArray([$headers]);
        $book->getActiveSheet()->freezePane('A2');
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $book->getActiveSheet()->getStyle('A2:' . $lastColumn . '1000')->getNumberFormat()->setFormatCode('@');
        ob_start();
        (new Xlsx($book))->save('php://output');
        $body = ob_get_clean();
        $book->disconnectWorksheets();
        return $this->response->withType('xlsx')->withDownload($filename)->withStringBody($body);
    }

    public function import()
    {
        $this->request->allowMethod(['get', 'post']);
        if (!$this->request->is('post')) {
            return $this->redirect(['action' => 'index', '?' => ['type' => $this->importType()]]);
        }
        $type = $this->importType();
        if (!$this->getCurrentUser() || (string)$this->getCurrentUser()->auth === '0') {
            throw new \Cake\Http\Exception\ForbiddenException('取込権限がありません。');
        }
        $file = $this->request->getData('result') ?? $this->request->getData('Import.result');
        if ($type === 'contacts' && (string)$this->getCurrentUser()?->auth !== '9') {
            throw new \Cake\Http\Exception\ForbiddenException('対応履歴の取込権限がありません。');
        }
        if (!$file instanceof UploadedFileInterface || $file->getError() !== UPLOAD_ERR_OK) {
            $this->Flash->error('ファイルを選択してください。');
            return $this->redirect(['action' => 'index', '?' => ['type' => $type]]);
        }
        $extension = strtolower(pathinfo($file->getClientFilename() ?? '', PATHINFO_EXTENSION));
        if (!in_array($extension, ['csv', 'xlsx', 'xls', 'ods'], true)) {
            $this->Flash->error('CSV / Excel / ODS ファイルを選択してください。');
            return $this->redirect(['action' => 'index', '?' => ['type' => $type]]);
        }
        try {
            $rows = SpreadsheetUpload::rows($file);
            $user = $this->getCurrentUser();
            $report = (new LegacyCrmImporter($this->fetchTable('Customers'), $this->fetchTable('Contacts')))->import($rows, $type, (int)($user?->id ?? 0), $user?->belong === null ? null : (int)$user->belong);
            $this->set(compact('type', 'report') + ['warning' => 0]);
            $this->viewBuilder()->setTemplate('index');
        } catch (\Throwable $e) {
            $this->Flash->error($e instanceof \InvalidArgumentException ? $e->getMessage() : 'ファイルを読み込めません。形式と内容を確認してください。');
            return $this->redirect(['action' => 'index', '?' => ['type' => $type]]);
        }
    }

    public function getphonedata()
    {
        if (!$this->request->is('post')) {
            return $this->redirect(['controller' => 'Settings', 'action' => 'index']);
        }
        $file = $this->request->getData('result') ?? $this->request->getData('Import.result');
        try {
            $rows = SpreadsheetUpload::rows($file);
            $user = $this->getCurrentUser();
            $exporter = new LegacyPhoneExport($this->fetchTable('Customers'));
            $stream = fopen('php://temp', 'w+');
            try {
                fputcsv($stream, LegacyPhoneExport::headers(), ',', '"', '');
                foreach ($exporter->exportRows($rows, $user?->belong === null ? null : (int)$user->belong) as $row) {
                    fputcsv($stream, $row, ',', '"', '');
                }
                rewind($stream);
                $body = stream_get_contents($stream);
            } finally {
                fclose($stream);
            }
            return $this->response->withHeader('Content-Type', 'text/csv; charset=Shift_JIS')
                ->withDownload('customer_phone_lookup_' . date('Y-m-d_His') . '.csv')
                ->withStringBody(mb_convert_encoding($body, 'SJIS-win', 'UTF-8'));
        } catch (\Throwable $e) {
            $this->Flash->error($e instanceof \InvalidArgumentException ? $e->getMessage() : 'ファイルを読み込めません。形式と内容を確認してください。');
            return $this->redirect(['controller' => 'Settings', 'action' => 'index']);
        }
    }
}

<?

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetFactory
{
    public $spreadsheet;
    public $sheet;
    public $writer;
    public $reader;

    public function __construct()
    {
    }

    public function downloadSheet(array $headers, array $data, string $name)
    {
        $this->createSheet();
        $this->fillSheet('A', 1, $headers, $data);
        $this->writeSheet('Csv', $name);
    }

    public function createSheet()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    public function fillSheet(string $startColumn = 'A', int $startRow = 1, array $headers, array $body)
    {
        $headerColumn = $startColumn;
        $headerRow = (string)$startRow;
        foreach ($headers as $header) {
            $this->sheet->setCellValue($headerColumn++ . $headerRow, $header);
        }

        $bodyColumn = $startColumn;
        $bodyRow = (string)($startRow + 1);
        if (count($body) > 0) {
            $this->sheet->fromArray($body, NULL, $bodyColumn . $bodyRow);
        }
    }

    public function writeSheet(string $type, string $fileName)
    {
        try {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . urlencode($fileName . '_' . date('Y-m-d') . '.' . strtolower($type)) . '"');

            if ($type === 'Xlsx') {
                $this->writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
            } else {
                $this->writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($this->spreadsheet);
                $this->writer->setUseBOM(true);
            }

            $this->writer->save('php://output');
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            error(503, $e->getMessage(), '');
        }
    }

    public function readSheet($serverFilename, $pcFilename)
    {
        $fileType = pathinfo($pcFilename, PATHINFO_EXTENSION);

        if ($fileType === 'xlsx') {
            $this->reader = new Xlsx();
        } else if ($fileType === 'xls') {
            $this->reader = new Xls();
        } else if ($fileType === 'csv') {
            $encoding = mb_detect_encoding(file_get_contents($serverFilename), 'EUC-KR, UTF-8');
            $this->reader = new Csv();
            if ($encoding === 'EUC-KR') {
                $this->reader->setInputEncoding('CP949');
            }
        } else {
            return result(442, '처리할 수 있는 파일 형식이 아닙니다.', [$pcFilename]);
        }

        $this->spreadsheet = $this->reader->load($serverFilename);
        $spreadData = $this->spreadsheet->getActiveSheet()->toArray();

        return result(200, $pcFilename . ' 파일을 정상적으로 불러왔습니다.', $spreadData);
    }
}
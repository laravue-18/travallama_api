<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\TrawickGpr;
use App\Models\Country;

class ExcelController extends Controller
{
    public function index(){
        // $reader = IOFactory::createReader('Xlsx');
        // $reader->setReadDataOnly(TRUE);
        // $spreadsheet = $reader->load("test.xlsx");

        // $worksheet = $spreadsheet->getSheet(1);

        // $highestRow = $worksheet->getHighestRow(); // e.g. 10
        // $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        // $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5

        // for ($row = 955; $row <= 1014; ++$row) {
        //     for ($col = 2; $col <= 19; ++$col) {
        //         $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
        //         TrawickGpr::create([
        //             'product_id' => 9,
        //             'age' => $worksheet->getCellByColumnAndRow($col, 9)->getValue(),
        //             'days' => $worksheet->getCellByColumnAndRow(1, $row)->getValue(),
        //             'destination' => 'Unknown',
        //             'table' => '01',
        //             'percent' => $value
        //         ]);
        //     }
        // }
        // dd('done');

    }
}

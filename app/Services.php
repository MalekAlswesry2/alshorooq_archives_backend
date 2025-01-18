<?php

namespace App\Services;

use Mpdf\Mpdf;

class PdfService
{
    // public function generatePdf($view, $data = [])
    // {
    //     $html = view($view, $data)->render();

    //     $mpdf = new Mpdf([
    //         'default_font' => 'dejavusans', // يمكنك تغيير الخط الافتراضي
    //         'mode' => 'utf-8', // لضمان دعم UTF-8
    //         'format' => 'A4',
    //     ]);

    //     $mpdf->WriteHTML($html);

    //     return $mpdf->Output('', 'I'); // لعرض الملف في المتصفح
    // }
}

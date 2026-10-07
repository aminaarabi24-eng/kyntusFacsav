<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FactureController extends Controller
{
    public function processExcel(Request $request)
    {
        // =========================================================
        // 1. VALIDATION DES 3 FICHIERS
        // =========================================================

        $request->validate([
            'excel_file'  => 'required|mimes:xlsx,xls,csv|max:10240',
            'sav_mc_file' => 'nullable|file|mimes:xlsx,xls,csv|max:10240',
            'third_file'  => 'nullable|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        // =========================================================
        // 2. LECTURE DU FICHIER PRINCIPAL
        // =========================================================

        $primaryRows = Excel::toArray(
            [],
            $request->file('excel_file')
        )[0];

        if (empty($primaryRows)) {
            return back()->with(
                'error',
                'Le fichier principal est vide.'
            );
        }

        // =========================================================
        // 3. LECTURE SAV MC
        // ID RDV -> colonne juste aprÃ¨s ID RDV
        // =========================================================

        $savRows = $request->hasFile('sav_mc_file')
    ? Excel::toArray([], $request->file('sav_mc_file'))[0]
    : [];

        $savMap = [];

        if (!empty($savRows)) {

            $savHeader = array_map(
                fn($v) => trim((string) $v),
                $savRows[0]
            );

            $colSavIdRdv = array_search(
                'ID RDV',
                $savHeader,
                true
            );

            if ($colSavIdRdv !== false) {

                $colSavValue = $colSavIdRdv + 1;

                foreach (
                    array_slice($savRows, 1)
                    as $savRow
                ) {

                    $id = isset($savRow[$colSavIdRdv])
                        ? trim((string) $savRow[$colSavIdRdv])
                        : '';

                    if ($id === '') {
                        continue;
                    }

                    // Valeur absente ou vide = #N/A
                    if (
                        !array_key_exists(
                            $colSavValue,
                            $savRow
                        ) ||
                        $savRow[$colSavValue] === null ||
                        trim((string) $savRow[$colSavValue]) === ''
                    ) {
                        $savMap[$id] = '#N/A';
                    } else {
                        $savMap[$id] =
                            $savRow[$colSavValue];
                    }
                }
            }
        }

        // =========================================================
        // 4. LECTURE PBO
        // ID RDV -> colonne juste aprÃ¨s ID RDV
        // =========================================================

        $thirdRows = $request->hasFile('third_file')
    ? Excel::toArray([], $request->file('third_file'))[0]
    : [];

        $thirdMap = [];

        if (!empty($thirdRows)) {

            $thirdHeader = array_map(
                fn($v) => trim((string) $v),
                $thirdRows[0]
            );

            $colThirdIdRdv = array_search(
                'ID RDV',
                $thirdHeader,
                true
            );

            if ($colThirdIdRdv !== false) {

                $colThirdValue =
                    $colThirdIdRdv + 1;

                foreach (
                    array_slice($thirdRows, 1)
                    as $thirdRow
                ) {

                    $id = isset($thirdRow[$colThirdIdRdv])
                        ? trim((string) $thirdRow[$colThirdIdRdv])
                        : '';

                    if ($id === '') {
                        continue;
                    }

                    if (
                        !array_key_exists(
                            $colThirdValue,
                            $thirdRow
                        ) ||
                        $thirdRow[$colThirdValue] === null ||
                        trim((string) $thirdRow[$colThirdValue]) === ''
                    ) {
                        $thirdMap[$id] = '#N/A';
                    } else {
                        $thirdMap[$id] =
                            $thirdRow[$colThirdValue];
                    }
                }
            }
        }

        // =========================================================
        // 5. HEADER DU FICHIER PRINCIPAL
        // =========================================================

        $originalHeader = array_map(
            fn($v) => trim((string) $v),
            $primaryRows[0]
        );

        // =========================================================
        // 6. COLONNES ORIGINALES
        // =========================================================

        $colInstallation = array_search(
            'Installation',
            $originalHeader,
            true
        );

        $colDeplacement = array_search(
            'Déplacement',
            $originalHeader,
            true
        );

        $colCodeCloture = array_search(
            'Code Clôture',
            $originalHeader,
            true
        );

        $colTotal = array_search(
            'Total',
            $originalHeader,
            true
        );

        $colIdRdv = array_search(
            'ID RDV',
            $originalHeader,
            true
        );

        $colCodePartenaire = array_search(
            'Code Partenaire',
            $originalHeader,
            true
        );

        if (
            $colInstallation === false ||
            $colDeplacement === false ||
            $colCodeCloture === false ||
            $colTotal === false ||
            $colIdRdv === false
        ) {
            return back()->with(
                'error',
                'Les colonnes ID RDV, Installation, Déplacement, Code Clôture et Total sont obligatoires.'
            );
        }

        // =========================================================
        // 7. CRÃ‰ATION DU HEADER FINAL
        //
        // ON NE DÃ‰PLACE PAS TOTAL.
        //
        // Si Total est la derniÃ¨re colonne :
        //
        // Total | Non Facturable | SAV MC | PBO_PHOTO_NACELLE
        // =========================================================

        $specialColumns = [
            'Non Facturable',
            'SAV MC',
            'PBO_PHOTO_NACELLE'
        ];

        $cleanHeader = [];

        foreach ($originalHeader as $oldIndex => $columnName) {

            if (
                !in_array(
                    $columnName,
                    $specialColumns,
                    true
                )
            ) {
                $cleanHeader[] = [
                    'name' => $columnName,
                    'index' => $oldIndex,
                ];
            }
        }

        $header = [];

        foreach ($cleanHeader as $column) {

            $columnName = $column['name'];
            $header[] = $columnName;

            if ($column['name'] === 'Total') {

                $header[] = 'Non Facturable';
                $header[] = 'SAV MC';
                $header[] = 'PBO_PHOTO_NACELLE';
            }
        }

        // =========================================================
        // 8. RECONSTRUCTION DES LIGNES
        // =========================================================

        $rows = [];

        $rows[] = $header;

        foreach (
            array_slice($primaryRows, 1)
            as $oldRow
        ) {

            $newRow = [];

            foreach ($cleanHeader as $column) {

                $oldIndex = $column['index'];
                $newRow[] = $oldRow[$oldIndex] ?? '';

                // Ajouter uniquement aprÃ¨s Total
                if ($column['name'] === 'Total') {

                    $newRow[] = '';
                    $newRow[] = '';
                    $newRow[] = '';
                }
            }

            $rows[] = $newRow;
        }

        $primaryRows = $rows;

        // =========================================================
        // 9. NOUVEAUX INDEX
        // =========================================================

        $colInstallation = array_search(
            'Installation',
            $header,
            true
        );

        $colDeplacement = array_search(
            'Déplacement',
            $header,
            true
        );

        $colCodeCloture = array_search(
            'Code Clôture',
            $header,
            true
        );

        $colTotal = array_search(
            'Total',
            $header,
            true
        );

        $colIdRdv = array_search(
            'ID RDV',
            $header,
            true
        );

        $colCodePartenaire = array_search(
            'Code Partenaire',
            $header,
            true
        );

        $colNonFacturable = array_search(
            'Non Facturable',
            $header,
            true
        );

        $colSavMc = array_search(
            'SAV MC',
            $header,
            true
        );

        $colPbo = array_search(
            'PBO_PHOTO_NACELLE',
            $header,
            true
        );

        // =========================================================
        // 10. SUPPRESSION DES LIGNES SANS ID RDV
        // =========================================================

        $cleanRows = [];

        $cleanRows[] = $header;

        foreach (
            array_slice($primaryRows, 1)
            as $row
        ) {

            $idRdv = isset($row[$colIdRdv])
                ? trim((string) $row[$colIdRdv])
                : '';

            if ($idRdv === '') {
                continue;
            }

            $cleanRows[] = $row;
        }

        $primaryRows = $cleanRows;

        // =========================================================
        // 11. RÃˆGLE 1
        //
        // Installation = 35.4
        // Installation = 0
        // si Déplacement = 0 => 49
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            if (
                !isset($row[$colInstallation]) ||
                trim((string) $row[$colInstallation]) === '' ||
                !is_numeric($row[$colInstallation])
            ) {
                $row[$colInstallation] = 0;
            }

            if (
                !isset($row[$colDeplacement]) ||
                trim((string) $row[$colDeplacement]) === '' ||
                !is_numeric($row[$colDeplacement])
            ) {
                $row[$colDeplacement] = 0;
            }

            if (
                (float) $row[$colInstallation] == 35.4
            ) {

                $row[$colInstallation] = 0;

                if (
                    (float) $row[$colDeplacement] == 0
                ) {
                    $row[$colDeplacement] = 49;
                }
            }
        }

        unset($row);

        // =========================================================
        // 12. RÃˆGLE 2
        //
        // Installation = 59 => 68
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            if (
                (float) $row[$colInstallation] == 59
            ) {
                $row[$colInstallation] = 68;
            }
        }

        unset($row);

        // =========================================================
        $nonFacturableCodes = [
            'SNR4e',
            'SNR4f',
            'INR1c',
            'INR1b',
            'INR1a',
            'INR2c',
            'INR2b',
            'SNR4g',
        ];
        // 13. RÃˆGLE 3
        //
        // Installation > 68
        // sauf les codes Non Facturables
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            $codeCloture =
                isset($row[$colCodeCloture])
                    ? trim((string) $row[$colCodeCloture])
                    : '';

            if (
                (float) $row[$colInstallation] > 68 &&
                !in_array($codeCloture, $nonFacturableCodes, true)
            ) {
                $row[$colInstallation] = 68;
            }
        }

        unset($row);

        // =========================================================
        // 14. RÃˆGLE 4
        //
        // Les codes de la liste => Non Facturable = le code lui-même
        // sinon #N/A
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            $codeCloture =
                isset($row[$colCodeCloture])
                    ? trim((string) $row[$colCodeCloture])
                    : '';

            if (in_array($codeCloture, $nonFacturableCodes, true)) {

                $row[$colNonFacturable] = $codeCloture;

            } else {

                $row[$colNonFacturable] = '#N/A';
            }
        }

        unset($row);

        // =========================================================
        // 15. RÃˆGLE 5
        //
        // Non Facturable != #N/A
        // Installation = 0
        // Déplacement = 0
        // Total = 0
        //
        // PAS DE continue.
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            if (
                $row[$colNonFacturable] !== '#N/A'
            ) {

                $row[$colInstallation] = 0;
                $row[$colDeplacement] = 0;
                $row[$colTotal] = 0;
            }
        }

        unset($row);

        // =========================================================
        // 16. RÃˆGLE 6
        //
        // Non Facturable = #N/A
        // Installation = 68
        // Total sera recalculÃ© Ã  la fin.
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            if (
                $row[$colNonFacturable] === '#N/A' &&
                is_numeric($row[$colTotal]) &&
                (float) $row[$colTotal] == 0
            ) {

                $row[$colInstallation] = 68;
                $row[$colTotal] = 0;
            }
        }

        unset($row);

        // =========================================================
        // 17. RET4A
        //
        // Déplacement = 30
        // => Installation = 0
        // => Déplacement reste 30
        //
        // Déplacement = 0
        // => Installation = 0
        // => Déplacement = 49
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            $codeCloture =
                isset($row[$colCodeCloture])
                    ? trim((string) $row[$colCodeCloture])
                    : '';

            if ($codeCloture === 'RET4a') {

                if (
                    is_numeric(
                        $row[$colDeplacement]
                    ) &&
                    (float)
                    $row[$colDeplacement] == 30
                ) {

                    $row[$colInstallation] = 0;

                } elseif (
                    is_numeric(
                        $row[$colDeplacement]
                    ) &&
                    (float)
                    $row[$colDeplacement] == 0
                ) {

                    $row[$colInstallation] = 0;
                    $row[$colDeplacement] = 49;
                }
            }
        }

        unset($row);

        // =========================================================
        // 18. RÃˆGLE 7
        //
        // SAV MC + PBO
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            $idRdv =
                isset($row[$colIdRdv])
                    ? trim((string) $row[$colIdRdv])
                    : '';

            // -----------------------------------------------------
            // SAV MC
            // -----------------------------------------------------

            if (
                $idRdv !== '' &&
                array_key_exists($idRdv, $savMap)
            ) {

                $savValue = $savMap[$idRdv];

                if (
                    $savValue === null ||
                    trim((string) $savValue) === ''
                ) {
                    $row[$colSavMc] = '#N/A';
                } else {
                    $row[$colSavMc] = $savValue;
                }

            } else {

                // IMPORTANT :
                // ID RDV non trouvÃ© => #N/A
                $row[$colSavMc] = '#N/A';
            }

            // -----------------------------------------------------
            // PBO
            // -----------------------------------------------------

            if (
                $idRdv !== '' &&
                array_key_exists($idRdv, $thirdMap)
            ) {

                $pboValue = $thirdMap[$idRdv];

                if (
                    $pboValue === null ||
                    trim((string) $pboValue) === ''
                ) {
                    $row[$colPbo] = '#N/A';
                } else {
                    $row[$colPbo] = $pboValue;
                }

            } else {

                $row[$colPbo] = '#N/A';
            }
        }

        unset($row);

        // =========================================================
        // 19. IMC + SAV MC
        //
        // #N/A => SAV MC = 0 + Déplacement = 0
        //
        // 0-1   => 68
        // 2-3   => 110
        // 4-9   => 180
        // 10-<32 => 220
        // 32-64 => 580
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            $codePartenaire =
                $colCodePartenaire !== false &&
                isset($row[$colCodePartenaire])
                    ? strtoupper(
                        trim(
                            (string)
                            $row[$colCodePartenaire]
                        )
                    )
                    : '';

            if (
                str_contains(
                    $codePartenaire,
                    'IMC'
                )
            ) {

                if (
                    $row[$colSavMc] === '#N/A'
                ) {

                    $row[$colSavMc] = 0;
                    $row[$colDeplacement] = 0;
                }

                if (
                    is_numeric(
                        $row[$colSavMc]
                    )
                ) {

                    $sav = (float)
                        $row[$colSavMc];

                    if (
                        $sav >= 0 &&
                        $sav <= 1
                    ) {

                        $row[$colInstallation] = 68;

                    } elseif (
                        $sav >= 2 &&
                        $sav <= 3
                    ) {

                        $row[$colInstallation] = 110;

                    } elseif (
                        $sav >= 4 &&
                        $sav <= 9
                    ) {

                        $row[$colInstallation] = 180;

                    } elseif (
                        $sav >= 10 &&
                        $sav < 32
                    ) {

                        $row[$colInstallation] = 220;

                    } elseif (
                        $sav >= 32 &&
                        $sav <= 64
                    ) {

                        $row[$colInstallation] = 580;
                    }
                    
                }
            }
        }

        unset($row);

        // =========================================================
        // 20. PBO
        //
        // PBO non vide et != #N/A
        // + Installation = 68
        // => Installation = 113
        // =========================================================

        foreach (
            $primaryRows as $index => &$row
        ) {

            if ($index === 0) {
                continue;
            }

            $pbo =
                isset($row[$colPbo])
                    ? trim((string) $row[$colPbo])
                    : '';

            if (
                $pbo !== '' &&
                strtoupper($pbo) !== '#N/A'
            ) {

                if (
                    is_numeric(
                        $row[$colInstallation]
                    ) &&
                    (float)
                    $row[$colInstallation] == 68
                ) {

                    $row[$colInstallation] = 113;
                }
            }
        }

        unset($row);

        // =========================================================
        // 21. CONTRÃ”LE FINAL
        //
        // Installation jamais vide
        // Déplacement jamais vide
        // Total jamais vide
        // SAV MC jamais vide
        // PBO jamais vide
        // Non Facturable jamais vide
        // =========================================================

        $finalRows = [];

        $finalRows[] = $header;

        foreach (
            array_slice($primaryRows, 1)
            as $row
        ) {

            // Taille exacte
            while (
                count($row) < count($header)
            ) {
                $row[] = '';
            }

            if (
                count($row) > count($header)
            ) {
                $row = array_slice(
                    $row,
                    0,
                    count($header)
                );
            }

            // -----------------------------------------------------
            // ID RDV obligatoire
            // -----------------------------------------------------

            $idRdv =
                isset($row[$colIdRdv])
                    ? trim((string) $row[$colIdRdv])
                    : '';

            if ($idRdv === '') {
                continue;
            }

            // -----------------------------------------------------
            // Installation
            // -----------------------------------------------------

            if (
                !isset($row[$colInstallation]) ||
                trim(
                    (string)
                    $row[$colInstallation]
                ) === '' ||
                !is_numeric(
                    $row[$colInstallation]
                )
            ) {
                $row[$colInstallation] = 0;
            }

            // -----------------------------------------------------
            // Déplacement
            // -----------------------------------------------------

            if (
                !isset($row[$colDeplacement]) ||
                trim(
                    (string)
                    $row[$colDeplacement]
                ) === '' ||
                !is_numeric(
                    $row[$colDeplacement]
                )
            ) {
                $row[$colDeplacement] = 0;
            }

            // -----------------------------------------------------
            // SAV MC
            // -----------------------------------------------------

            if (
                !isset($row[$colSavMc]) ||
                trim(
                    (string)
                    $row[$colSavMc]
                ) === ''
            ) {
                $row[$colSavMc] = '#N/A';
            }

            // -----------------------------------------------------
            // PBO
            // -----------------------------------------------------

            if (
                !isset($row[$colPbo]) ||
                trim(
                    (string)
                    $row[$colPbo]
                ) === ''
            ) {
                $row[$colPbo] = '#N/A';
            }

            // -----------------------------------------------------
            // Non Facturable
            // -----------------------------------------------------

            if (
                !isset($row[$colNonFacturable]) ||
                trim(
                    (string)
                    $row[$colNonFacturable]
                ) === ''
            ) {
                $row[$colNonFacturable] = '#N/A';
            }

            // -----------------------------------------------------
            // TOTAL FINAL
            //
            // Toujours Installation + Déplacement
            // -----------------------------------------------------

            $row[$colTotal] =
                (float)
                $row[$colInstallation]
                +
                (float)
                $row[$colDeplacement];

            $finalRows[] = $row;
        }

        $primaryRows = $finalRows;

        // =========================================================
        // 22. EXPORT
        // =========================================================

        $fileName =
            'facture_complete_' .
            date('Y-m-d_H-i-s') .
            '.xlsx';

        return Excel::download(
            new class($primaryRows)
                implements FromArray, WithEvents, WithStrictNullComparison {

                protected $data;

                public function __construct(
                    array $data
                ) {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }

                public function registerEvents(): array
                {
                    return [

                        AfterSheet::class =>
                            function (
                                AfterSheet $event
                            ) {

                                $sheet =
                                    $event
                                        ->sheet
                                        ->getDelegate();

                                $header =
                                    $this->data[0];

                                foreach (
                                    [
                                        'Installation',
                                        'Déplacement',
                                        'Total'
                                    ]
                                    as $columnName
                                ) {

                                    $index =
                                        array_search(
                                            $columnName,
                                            $header,
                                            true
                                        );

                                    if (
                                        $index !== false
                                    ) {

                                        $letter =
                                            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
                                                $index + 1
                                            );

                                        $sheet
                                            ->getStyle(
                                                $letter .
                                                '2:' .
                                                $letter .
                                                $sheet->getHighestRow()
                                            )
                                            ->getNumberFormat()
                                            ->setFormatCode(
                                                '0.00'
                                            );
                                    }
                                }
                            },
                    ];
                }
            },
            $fileName
        );
    }
}